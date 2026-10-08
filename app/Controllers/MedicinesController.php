<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class MedicinesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $search = trim($this->request->get('search', ''));
        $categoryId = $this->request->get('category_id');

        $query = "SELECT m.*, c.name as category_name, 
                  COALESCE((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as current_stock,
                  COALESCE((SELECT MIN(b.expiry_date) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.quantity > 0), NULL) as earliest_expiry,
                  COALESCE((SELECT b.mrp FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' ORDER BY b.expiry_date ASC LIMIT 1), 0) as current_mrp
                  FROM medicines m 
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE 1=1";

        $params = [];

        if ($search !== '') {
            $query .= " AND (m.name LIKE ? OR m.brand_name LIKE ? OR m.composition LIKE ? OR m.code LIKE ? OR m.manufacturer LIKE ?)";
            $wildcard = "%{$search}%";
            $params = array_merge($params, [$wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
        }

        if (!empty($categoryId)) {
            $query .= " AND m.category_id = ?";
            $params[] = (int)$categoryId;
        }

        $query .= " ORDER BY m.name ASC";
        $medicines = Database::raw($query, $params);
        $categories = Database::table('categories')->where('is_active', 1)->get();

        $this->render('medicines.index', [
            'pageTitle'    => 'Medicine Master Management - INFOSOF',
            'activeModule' => 'medicines',
            'medicines'    => $medicines,
            'categories'   => $categories,
            'search'       => $search,
            'categoryId'   => $categoryId
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('medicines', 'create');

        if ($this->request->isPost()) {
            $code = trim($this->request->post('code'));
            $name = trim($this->request->post('name'));
            $brandName = trim($this->request->post('brand_name'));
            $categoryId = (int)$this->request->post('category_id');
            $dosageForm = trim($this->request->post('dosage_form', 'Tablet'));
            $strength = trim($this->request->post('strength', ''));
            $composition = trim($this->request->post('composition', ''));
            $manufacturer = trim($this->request->post('manufacturer', ''));
            $packSize = trim($this->request->post('pack_size', '1x10'));
            $unit = trim($this->request->post('unit', 'Strip'));
            $hsnCode = trim($this->request->post('hsn_code', '300490'));
            $gstRate = (float)$this->request->post('gst_rate', 12.00);
            $requiresPrescription = (int)$this->request->post('requires_prescription', 0);
            $minStock = (int)$this->request->post('min_stock_level', 20);
            $reorderLevel = (int)$this->request->post('reorder_level', 50);
            $description = trim($this->request->post('description', ''));

            if (empty($code)) {
                $code = 'MED' . rand(1000, 9999);
            }

            $id = Database::table('medicines')->insert([
                'code'                  => $code,
                'name'                  => $name,
                'brand_name'            => $brandName,
                'category_id'           => $categoryId,
                'dosage_form'           => $dosageForm,
                'strength'              => $strength,
                'composition'           => $composition,
                'manufacturer'          => $manufacturer,
                'pack_size'             => $packSize,
                'unit'                  => $unit,
                'hsn_code'              => $hsnCode,
                'gst_rate'              => $gstRate,
                'requires_prescription' => $requiresPrescription,
                'min_stock_level'       => $minStock,
                'reorder_level'         => $reorderLevel,
                'description'           => $description,
                'is_active'             => 1,
                'created_at'            => date('Y-m-d H:i:s')
            ]);

            // Optional Initial Opening Batch if entered
            $batchNo = trim($this->request->post('initial_batch', ''));
            $qty = (int)$this->request->post('initial_qty', 0);
            $purchasePrice = (float)$this->request->post('initial_purchase_price', 0);
            $mrp = (float)$this->request->post('initial_mrp', 0);
            $expiryDate = trim($this->request->post('initial_expiry', ''));

            if (!empty($batchNo) && $qty > 0 && !empty($expiryDate)) {
                Database::table('batches')->insert([
                    'medicine_id'      => $id,
                    'batch_number'     => $batchNo,
                    'mfg_date'         => date('Y-m-d', strtotime('-3 months')),
                    'expiry_date'      => $expiryDate,
                    'quantity'         => $qty,
                    'initial_quantity' => $qty,
                    'purchase_price'   => $purchasePrice,
                    'selling_price'    => $mrp > 0 ? ($mrp * 0.95) : $purchasePrice * 1.2,
                    'mrp'              => $mrp,
                    'barcode'          => '890' . rand(100000000, 999999999),
                    'status'           => 'active',
                    'created_at'       => date('Y-m-d H:i:s')
                ]);
            }

            $this->logAudit('create_medicine', 'medicines', $id, "Added medicine {$name} ({$brandName})");
            $this->redirect(App::baseURL() . '/medicines', 'success', "Medicine '{$name}' created successfully.");
        }
    }

    public function edit(int $id): void
    {
        $this->checkPermission('medicines', 'edit');

        $medicine = Database::table('medicines')->where('id', $id)->first();
        if (!$medicine) {
            $this->redirect(App::baseURL() . '/medicines', 'error', 'Medicine not found.');
        }

        if ($this->request->isPost()) {
            Database::table('medicines')->where('id', $id)->update([
                'name'                  => trim($this->request->post('name')),
                'brand_name'            => trim($this->request->post('brand_name')),
                'category_id'           => (int)$this->request->post('category_id'),
                'dosage_form'           => trim($this->request->post('dosage_form')),
                'strength'              => trim($this->request->post('strength')),
                'composition'           => trim($this->request->post('composition')),
                'manufacturer'          => trim($this->request->post('manufacturer')),
                'pack_size'             => trim($this->request->post('pack_size')),
                'unit'                  => trim($this->request->post('unit')),
                'hsn_code'              => trim($this->request->post('hsn_code')),
                'gst_rate'              => (float)$this->request->post('gst_rate'),
                'requires_prescription' => (int)$this->request->post('requires_prescription'),
                'min_stock_level'       => (int)$this->request->post('min_stock_level'),
                'reorder_level'         => (int)$this->request->post('reorder_level'),
                'description'           => trim($this->request->post('description')),
                'updated_at'            => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('update_medicine', 'medicines', $id, "Updated medicine {$medicine['name']}");
            $this->redirect(App::baseURL() . '/medicines', 'success', "Medicine updated successfully.");
        }
    }

    public function delete(int $id): void
    {
        $this->checkPermission('medicines', 'delete');
        $med = Database::table('medicines')->where('id', $id)->first();
        if ($med) {
            Database::table('medicines')->where('id', $id)->delete();
            $this->logAudit('delete_medicine', 'medicines', $id, "Deleted medicine {$med['name']}");
            $this->redirect(App::baseURL() . '/medicines', 'success', 'Medicine deleted.');
        }
        $this->redirect(App::baseURL() . '/medicines');
    }
}
