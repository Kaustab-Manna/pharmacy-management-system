<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class BatchesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $medicineId = $this->request->get('medicine_id');
        $filter = $this->request->get('filter'); // 'near_expiry', 'expired', 'active'

        $query = "SELECT b.*, m.name as medicine_name, m.brand_name, m.dosage_form, m.strength, m.pack_size, m.unit,
                  COALESCE((SELECT SUM(si.quantity) FROM sale_items si WHERE si.batch_id = b.id), 0) as total_sold
                  FROM batches b
                  JOIN medicines m ON b.medicine_id = m.id
                  WHERE 1=1";

        $params = [];

        if (!empty($medicineId)) {
            $query .= " AND b.medicine_id = ?";
            $params[] = (int)$medicineId;
        }

        if ($filter === 'near_expiry') {
            $query .= " AND b.status = 'active' AND b.expiry_date <= ? AND b.expiry_date >= ?";
            $params[] = date('Y-m-d', strtotime('+30 days'));
            $params[] = date('Y-m-d');
        } elseif ($filter === 'expired') {
            $query .= " AND (b.status = 'expired' OR b.expiry_date < ?)";
            $params[] = date('Y-m-d');
        }

        $query .= " ORDER BY b.expiry_date ASC"; // FEFO order
        $batches = Database::raw($query, $params);
        $medicines = Database::table('medicines')->where('is_active', 1)->orderBy('name', 'ASC')->get();

        $this->render('batches.index', [
            'pageTitle'    => 'Batch Management & FEFO Tracking - INFOSOF',
            'activeModule' => 'batches',
            'batches'      => $batches,
            'medicines'    => $medicines,
            'medicineId'   => $medicineId,
            'filter'       => $filter
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('batches', 'create');

        if ($this->request->isPost()) {
            $medicineId = (int)$this->request->post('medicine_id');
            $batchNumber = trim($this->request->post('batch_number'));
            $mfgDate = trim($this->request->post('mfg_date'));
            $expiryDate = trim($this->request->post('expiry_date'));
            $qty = (int)$this->request->post('quantity', 0);
            $purchasePrice = (float)$this->request->post('purchase_price', 0);
            $sellingPrice = (float)$this->request->post('selling_price', 0);
            $mrp = (float)$this->request->post('mrp', 0);
            $barcode = trim($this->request->post('barcode', ''));

            if (empty($barcode)) {
                $barcode = '890' . rand(100000000, 999999999);
            }

            $id = Database::table('batches')->insert([
                'medicine_id'      => $medicineId,
                'batch_number'     => $batchNumber,
                'mfg_date'         => $mfgDate,
                'expiry_date'      => $expiryDate,
                'quantity'         => $qty,
                'initial_quantity' => $qty,
                'purchase_price'   => $purchasePrice,
                'selling_price'    => $sellingPrice ?: ($mrp * 0.95),
                'mrp'              => $mrp,
                'barcode'          => $barcode,
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            // Record opening stock movement
            Database::table('stock_movements')->insert([
                'medicine_id'   => $medicineId,
                'batch_id'      => $id,
                'movement_type' => 'opening',
                'quantity'      => $qty,
                'previous_qty'  => 0,
                'new_qty'       => $qty,
                'user_id'       => $this->getUser()['id'] ?? null,
                'notes'         => 'Initial Batch Entry: ' . $batchNumber,
                'created_at'    => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_batch', 'batches', $id, "Added batch {$batchNumber} for medicine #{$medicineId}");
            $this->redirect(App::baseURL() . '/batches', 'success', "Batch {$batchNumber} added successfully.");
        }
    }

    public function adjustStock(int $id): void
    {
        $this->checkPermission('batches', 'edit');

        if ($this->request->isPost()) {
            $newQty = (int)$this->request->post('quantity');
            $reason = trim($this->request->post('reason', 'Physical reconciliation'));

            $batch = Database::table('batches')->where('id', $id)->first();
            if ($batch) {
                $oldQty = (int)$batch['quantity'];
                Database::table('batches')->where('id', $id)->update(['quantity' => $newQty]);

                Database::table('stock_movements')->insert([
                    'medicine_id'   => $batch['medicine_id'],
                    'batch_id'      => $id,
                    'movement_type' => 'stock_adjustment',
                    'quantity'      => $newQty - $oldQty,
                    'previous_qty'  => $oldQty,
                    'new_qty'       => $newQty,
                    'user_id'       => $this->getUser()['id'] ?? null,
                    'notes'         => $reason,
                    'created_at'    => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('adjust_stock', 'batches', $id, "Stock adjusted from {$oldQty} to {$newQty} ({$reason})");
                $this->redirect(App::baseURL() . '/batches', 'success', 'Batch stock adjusted successfully.');
            }
        }
        $this->redirect(App::baseURL() . '/batches');
    }
}
