<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class BarcodeController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.dosage_form, m.pack_size 
                                  FROM batches b 
                                  JOIN medicines m ON b.medicine_id = m.id 
                                  WHERE b.status = 'active' AND b.quantity > 0 
                                  ORDER BY b.id DESC LIMIT 50");

        $this->render('barcode.index', [
            'pageTitle'    => 'Barcode Generation & Scanner Center - INFOSOF',
            'activeModule' => 'barcode',
            'batches'      => $batches
        ]);
    }

    public function lookup(): void
    {
        $code = trim($this->request->get('code', ''));
        if (empty($code)) {
            $this->json(['success' => false, 'message' => 'No barcode provided'], 400);
        }

        // Look up by batch barcode or medicine code
        $batch = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.code as medicine_code, 
                                m.dosage_form, m.strength, m.pack_size, m.unit, m.gst_rate, m.requires_prescription
                                FROM batches b
                                JOIN medicines m ON b.medicine_id = m.id
                                WHERE (b.barcode = ? OR b.batch_number = ? OR m.code = ?)
                                AND b.status = 'active' AND b.quantity > 0
                                ORDER BY b.expiry_date ASC LIMIT 1", [$code, $code, $code]);

        if (!empty($batch)) {
            $this->json(['success' => true, 'batch' => $batch[0]]);
        } else {
            $this->json(['success' => false, 'message' => 'No active medicine batch found for barcode: ' . $code], 404);
        }
    }

    public function printLabels(): void
    {
        $this->requireAuth();

        $batchId = (int)$this->request->get('batch_id', 0);
        $copies = (int)$this->request->get('copies', 24);

        $batch = null;
        if ($batchId > 0) {
            $row = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.pack_size 
                                  FROM batches b JOIN medicines m ON b.medicine_id = m.id WHERE b.id = ?", [$batchId]);
            $batch = $row[0] ?? null;
        }

        $this->render('barcode.print', [
            'pageTitle'    => 'Print Barcode Labels - INFOSOF',
            'activeModule' => 'barcode',
            'batch'        => $batch,
            'copies'       => $copies
        ], false); // render without sidebar for clean printing
    }
}
