<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class DoctorsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $doctors = Database::raw("SELECT d.*, 
                                 (SELECT COUNT(*) FROM prescriptions WHERE doctor_id = d.id) as total_prescriptions,
                                 (SELECT COUNT(*) FROM sales WHERE doctor_id = d.id) as total_sales_referred
                                 FROM doctors d
                                 ORDER BY d.name ASC");

        $this->render('doctors.index', [
            'pageTitle'    => 'Doctor Management & Prescriber Directory (Module 10) - INFOSOF',
            'activeModule' => 'doctors',
            'doctors'      => $doctors
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!$this->hasPermission('doctors', 'create') && !$this->hasPermission('pos', 'view') && !$this->hasPermission('billing', 'view')) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Permission denied to create doctor profile.'], 403);
                return;
            }
            $this->redirect(App::baseURL() . '/doctors', 'error', 'Permission denied.');
            return;
        }

        if ($this->request->isPost()) {
            $name = trim($this->request->post('name') ?? '');
            $regNo = trim($this->request->post('registration_number') ?? '');
            $spec = trim($this->request->post('specialization', 'General Physician') ?? 'General Physician');
            $hospital = trim($this->request->post('hospital_clinic', '') ?? '');
            $phone = trim($this->request->post('phone', '') ?? '');
            $email = trim($this->request->post('email', '') ?? '');
            $address = trim($this->request->post('address', '') ?? '');

            if (empty($name)) {
                if ($this->request->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Doctor name is required.'], 400);
                    return;
                }
                $this->redirect(App::baseURL() . '/doctors', 'error', 'Doctor name is required.');
                return;
            }

            if (empty($regNo)) {
                $regNo = 'DOC-' . date('ymd') . '-' . rand(100, 999);
            }

            $id = Database::table('doctors')->insert([
                'name'                => $name,
                'registration_number' => $regNo,
                'specialization'      => $spec,
                'hospital_clinic'     => $hospital,
                'phone'               => $phone,
                'email'               => $email,
                'address'             => $address,
                'created_at'          => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_doctor', 'doctors', $id, "Added doctor {$name}");

            if ($this->request->isAjax()) {
                $this->json([
                    'success'             => true,
                    'message'             => "Doctor {$name} registered successfully.",
                    'id'                  => $id,
                    'name'                => $name,
                    'hospital_clinic'     => $hospital,
                    'registration_number' => $regNo
                ]);
                return;
            }

            $this->redirect(App::baseURL() . '/doctors', 'success', "Doctor '{$name}' added to directory.");
        }
    }
}
