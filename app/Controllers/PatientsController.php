<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PatientsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $search = trim($this->request->get('search', ''));
        $query = "SELECT p.*,
                  (SELECT COUNT(*) FROM sales WHERE patient_id = p.id) as total_visits,
                  (SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE patient_id = p.id) as total_spend,
                  (SELECT COUNT(*) FROM prescriptions WHERE patient_id = p.id) as prescription_count
                  FROM patients p WHERE p.is_active = 1";

        $params = [];
        if ($search !== '') {
            $query .= " AND (p.name LIKE ? OR p.phone LIKE ? OR p.patient_code LIKE ?)";
            $wildcard = "%{$search}%";
            $params = [$wildcard, $wildcard, $wildcard];
        }

        $query .= " ORDER BY p.name ASC";
        $patients = Database::raw($query, $params);

        $this->render('patients.index', [
            'pageTitle'    => 'Patient & Customer Management (Module 11) - INFOSOF',
            'activeModule' => 'patients',
            'patients'     => $patients,
            'search'       => $search
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!$this->hasPermission('patients', 'create') && !$this->hasPermission('pos', 'view')) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Permission denied to create patient.'], 403);
                return;
            }
            Session::setFlash('error', "Access Denied: You do not have permission to create patients.");
            $this->redirect(App::baseURL() . '/dashboard');
            return;
        }

        if ($this->request->isPost()) {
            $name = trim($this->request->post('name') ?? '');
            $phone = trim($this->request->post('phone') ?? '');
            $age = (int)$this->request->post('age', 30);
            $gender = $this->request->post('gender', 'Male');
            $email = trim($this->request->post('email', '') ?? '');
            $address = trim($this->request->post('address', '') ?? '');
            $allergies = trim($this->request->post('allergies', 'None') ?? 'None');
            $chronic = trim($this->request->post('chronic_conditions', 'None') ?? 'None');
            $bloodGroup = trim($this->request->post('blood_group', '') ?? '');

            if (empty($name) || empty($phone)) {
                if ($this->request->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Patient Name and Phone number are required.'], 400);
                    return;
                }
                $this->redirect(App::baseURL() . '/patients', 'error', 'Patient Name and Phone number are required.');
                return;
            }

            // Check if patient with this phone already exists
            $existing = Database::table('patients')->where('phone', $phone)->first();
            if ($existing) {
                if ($this->request->isAjax()) {
                    $this->json([
                        'success'        => true,
                        'id'             => $existing['id'],
                        'name'           => $existing['name'],
                        'phone'          => $existing['phone'],
                        'loyalty_points' => $existing['loyalty_points'] ?? 0,
                        'existing'       => true,
                        'message'        => "Existing patient '{$existing['name']}' found and selected."
                    ]);
                    return;
                }
                $this->redirect(App::baseURL() . '/patients', 'error', "A patient with phone '{$phone}' already exists: {$existing['name']}.");
                return;
            }

            $patientCode = 'PAT-' . rand(1000, 9999);

            try {
                $id = Database::table('patients')->insert([
                    'patient_code'        => $patientCode,
                    'name'                => $name,
                    'age'                 => $age,
                    'gender'              => $gender,
                    'phone'               => $phone,
                    'email'               => $email,
                    'address'             => $address,
                    'allergies'           => $allergies,
                    'chronic_conditions'  => $chronic,
                    'blood_group'         => $bloodGroup,
                    'loyalty_points'      => 50, // Welcome points
                    'loyalty_tier'        => 'Bronze',
                    'outstanding_balance' => 0,
                    'is_active'           => 1,
                    'created_at'          => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('create_patient', 'patients', $id, "Registered patient {$name} ({$patientCode})");

                if ($this->request->isAjax()) {
                    $this->json([
                        'success'        => true,
                        'id'             => $id,
                        'name'           => $name,
                        'phone'          => $phone,
                        'loyalty_points' => 50,
                        'existing'       => false,
                        'message'        => "Patient '{$name}' created successfully!"
                    ]);
                    return;
                }

                $this->redirect(App::baseURL() . '/patients', 'success', "Patient '{$name}' registered with 50 bonus welcome loyalty points.");
                return;
            } catch (\Throwable $e) {
                if ($this->request->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
                    return;
                }
                $this->redirect(App::baseURL() . '/patients', 'error', 'Error saving patient: ' . $e->getMessage());
                return;
            }
        }
    }
}
