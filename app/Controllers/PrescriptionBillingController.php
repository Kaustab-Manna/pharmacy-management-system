<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PrescriptionBillingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $patientId = $this->request->get('patient_id');
        $prescriptionId = $this->request->get('prescription_id');

        $patients = Database::raw("SELECT DISTINCT p.* FROM patients p JOIN prescriptions rx ON p.id = rx.patient_id WHERE rx.status IN ('verified', 'partially_dispensed')");

        $activePrescriptions = [];
        if (!empty($patientId)) {
            $activePrescriptions = Database::table('prescriptions')
                ->where('patient_id', $patientId)
                ->where('status', 'verified')
                ->orWhere('status', 'partially_dispensed')
                ->get();
        }

        $prescribedItems = [];
        if (!empty($prescriptionId)) {
            $prescribedItems = Database::raw("SELECT pi.*, m.name as medicine_name, m.brand_name, m.dosage_form, m.strength, m.pack_size, m.unit,
                                              COALESCE((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.expiry_date >= ?), 0) as available_stock,
                                              (SELECT b.id FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.quantity > 0 ORDER BY b.expiry_date ASC LIMIT 1) as suggested_batch_id,
                                              (SELECT b.batch_number FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.quantity > 0 ORDER BY b.expiry_date ASC LIMIT 1) as suggested_batch_number,
                                              (SELECT b.mrp FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.quantity > 0 ORDER BY b.expiry_date ASC LIMIT 1) as suggested_mrp
                                              FROM prescription_items pi
                                              JOIN medicines m ON pi.medicine_id = m.id
                                              WHERE pi.prescription_id = ?", [date('Y-m-d'), $prescriptionId]);
        }

        $this->render('prescriptions.billing', [
            'pageTitle'           => 'Prescription-Based Billing (Module 13) - INFOSOF',
            'activeModule'        => 'prescription_billing',
            'patients'            => $patients,
            'patientId'           => $patientId,
            'prescriptionId'      => $prescriptionId,
            'activePrescriptions' => $activePrescriptions,
            'prescribedItems'     => $prescribedItems
        ]);
    }
}
