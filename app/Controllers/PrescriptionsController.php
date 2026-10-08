<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PrescriptionsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $query = "SELECT p.*, pt.name as patient_name, pt.phone as patient_phone, pt.allergies,
                  d.name as doctor_name, d.specialization as doctor_spec,
                  u.full_name as pharmacist_name
                  FROM prescriptions p
                  JOIN patients pt ON p.patient_id = pt.id
                  LEFT JOIN doctors d ON p.doctor_id = d.id
                  LEFT JOIN users u ON p.pharmacist_id = u.id
                  ORDER BY p.prescription_date DESC";

        $prescriptions = Database::raw($query);

        $patients = Database::table('patients')->where('is_active', 1)->get();
        $doctors = Database::table('doctors')->get();
        $medicines = Database::table('medicines')->where('is_active', 1)->orderBy('name', 'ASC')->get();

        $this->render('prescriptions.index', [
            'pageTitle'     => 'Prescription Management & Digital Archive - INFOSOF',
            'activeModule'  => 'prescriptions',
            'prescriptions' => $prescriptions,
            'patients'      => $patients,
            'doctors'       => $doctors,
            'medicines'     => $medicines
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('prescriptions', 'create');

        if ($this->request->isPost()) {
            $patientId = (int)$this->request->post('patient_id');
            $doctorId = !empty($this->request->post('doctor_id')) ? (int)$this->request->post('doctor_id') : null;
            $rxDate = $this->request->post('prescription_date', date('Y-m-d'));
            $pharmacistNotes = trim($this->request->post('pharmacist_notes', ''));

            $rxNumber = 'RX-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);

            // Handle file upload if attached
            $filePath = null;
            $file = $this->request->getFile('prescription_file');
            if ($file && !empty($file['tmp_name'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'rx_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetDir = dirname(__DIR__, 2) . '/public/uploads/prescriptions';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $fileName)) {
                    $filePath = 'uploads/prescriptions/' . $fileName;
                }
            }

            $rxId = Database::table('prescriptions')->insert([
                'prescription_number' => $rxNumber,
                'patient_id'          => $patientId,
                'doctor_id'           => $doctorId,
                'prescription_date'   => $rxDate,
                'file_path'           => $filePath,
                'status'              => 'verified',
                'pharmacist_id'       => $this->getUser()['id'] ?? 3,
                'pharmacist_notes'    => $pharmacistNotes,
                'created_at'          => date('Y-m-d H:i:s')
            ]);

            // Save Prescribed Medication Items
            $medicineIds = $this->request->post('med_id') ?? [];
            $dosages = $this->request->post('dosage') ?? [];
            $frequencies = $this->request->post('frequency') ?? [];
            $durations = $this->request->post('duration') ?? [];
            $quantities = $this->request->post('qty') ?? [];

            for ($i = 0; $i < count($medicineIds); $i++) {
                if (!empty($medicineIds[$i])) {
                    Database::table('prescription_items')->insert([
                        'prescription_id' => $rxId,
                        'medicine_id'     => (int)$medicineIds[$i],
                        'dosage'          => $dosages[$i] ?? '1 Tablet',
                        'frequency'       => $frequencies[$i] ?? '1-0-1',
                        'duration'        => $durations[$i] ?? '5 Days',
                        'qty_prescribed'  => (int)($quantities[$i] ?? 10),
                        'qty_dispensed'   => 0
                    ]);
                }
            }

            $this->logAudit('create_prescription', 'prescriptions', $rxId, "Created prescription {$rxNumber}");
            $this->redirect(App::baseURL() . '/prescriptions', 'success', "Prescription {$rxNumber} registered and verified.");
            return;
        }

        $this->redirect(App::baseURL() . '/prescriptions?open=upload');
    }

    public function upload(): void
    {
        $this->requireAuth();
        $this->redirect(App::baseURL() . '/prescriptions?open=upload');
    }

    public function getItems(int $id): void
    {
        $items = Database::raw("SELECT pi.*, m.name as medicine_name, m.brand_name, m.strength, m.dosage_form, m.unit
                                FROM prescription_items pi
                                JOIN medicines m ON pi.medicine_id = m.id
                                WHERE pi.prescription_id = ?", [$id]);

        $this->json(['success' => true, 'items' => $items]);
    }

    public function verify(int $id): void
    {
        $this->checkPermission('prescriptions', 'approve');

        Database::table('prescriptions')->where('id', $id)->update([
            'status'        => 'verified',
            'pharmacist_id' => $this->getUser()['id'] ?? 3
        ]);

        $this->redirect(App::baseURL() . '/prescriptions', 'success', 'Prescription verified by Pharmacist.');
    }
}
