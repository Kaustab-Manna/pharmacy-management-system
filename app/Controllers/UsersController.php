<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class UsersController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('users', 'view');

        $users = Database::table('users')->orderBy('id', 'ASC')->get();

        $this->render('users.index', [
            'pageTitle'    => 'User & Role Management (Module 1) - INFOSOF',
            'activeModule' => 'users',
            'users'        => $users
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('users', 'create');

        if ($this->request->isPost()) {
            $username = trim($this->request->post('username') ?? '');
            $fullName = trim($this->request->post('full_name') ?? '');
            $email = trim($this->request->post('email') ?? '');
            $phone = trim($this->request->post('phone') ?? '');
            $role = trim($this->request->post('role') ?? 'cashier');
            $password = trim($this->request->post('password') ?? 'admin123');

            // 1. Validate required fields
            if (empty($username) || empty($fullName) || empty($email)) {
                $this->redirect(App::baseURL() . '/users', 'error', 'Username, Full Name, and Email address are required fields.');
                return;
            }

            // 2. Validate username format (alphanumeric, underscores, hyphens, periods, min 3 chars)
            if (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
                $this->redirect(App::baseURL() . '/users', 'error', 'Username must be between 3 and 30 characters and contain only letters, numbers, dots, hyphens, or underscores.');
                return;
            }

            // 3. Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect(App::baseURL() . '/users', 'error', "Please enter a valid email address (e.g., name@example.com).");
                return;
            }

            // 4. Check for duplicate username
            $existingUser = Database::table('users')->where('username', $username)->first();
            if ($existingUser) {
                $this->redirect(App::baseURL() . '/users', 'error', "The username '{$username}' is already taken. Please choose a different unique username.");
                return;
            }

            // 5. Check for duplicate email
            $existingEmail = Database::table('users')->where('email', $email)->first();
            if ($existingEmail) {
                $this->redirect(App::baseURL() . '/users', 'error', "The email address '{$email}' is already registered with user account '{$existingEmail['username']}'.");
                return;
            }

            // 6. Validate password length
            if (strlen($password) < 4) {
                $this->redirect(App::baseURL() . '/users', 'error', 'Initial password must be at least 4 characters long.');
                return;
            }

            try {
                $id = Database::table('users')->insert([
                    'username'   => $username,
                    'full_name'  => $fullName,
                    'email'      => $email,
                    'phone'      => $phone,
                    'role'       => $role,
                    'password'   => password_hash($password, PASSWORD_BCRYPT),
                    'is_active'  => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('create_user', 'users', $id, "Created staff user {$username} with role {$role}");
                $this->redirect(App::baseURL() . '/users', 'success', "User '{$username}' created successfully with role: " . ucwords(str_replace('_', ' ', $role)));
                return;
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'users.username') !== false || stripos($msg, 'username') !== false) {
                    $errorText = "The username '{$username}' already exists. Please choose a different username.";
                } elseif (stripos($msg, 'users.email') !== false || stripos($msg, 'email') !== false) {
                    $errorText = "The email address '{$email}' is already registered to another user.";
                } else {
                    $errorText = "Database error while creating user: " . $msg;
                }
                $this->redirect(App::baseURL() . '/users', 'error', $errorText);
                return;
            }
        }
        $this->redirect(App::baseURL() . '/users');
    }

    public function update(int $id): void
    {
        $this->checkPermission('users', 'edit');

        if ($this->request->isPost()) {
            $user = Database::table('users')->where('id', $id)->first();
            if (!$user) {
                $this->redirect(App::baseURL() . '/users', 'error', 'User not found.');
                return;
            }

            $fullName = trim($this->request->post('full_name') ?? '');
            $email = trim($this->request->post('email') ?? '');
            $phone = trim($this->request->post('phone') ?? '');
            $role = trim($this->request->post('role') ?? $user['role']);
            $newPassword = trim($this->request->post('password') ?? '');

            if (empty($fullName) || empty($email)) {
                $this->redirect(App::baseURL() . '/users', 'error', 'Full Name and Email are required.');
                return;
            }

            // Check if email already taken by someone else
            $emailCollision = Database::raw("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
            if (!empty($emailCollision)) {
                $this->redirect(App::baseURL() . '/users', 'error', "The email '{$email}' is already used by another user.");
                return;
            }

            $updateData = [
                'full_name'  => $fullName,
                'email'      => $email,
                'phone'      => $phone,
                'role'       => $role,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 4) {
                    $this->redirect(App::baseURL() . '/users', 'error', 'Password must be at least 4 characters.');
                    return;
                }
                $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            }

            try {
                Database::table('users')->where('id', $id)->update($updateData);
                $this->logAudit('update_user', 'users', $id, "Updated profile for {$user['username']}");
                $this->redirect(App::baseURL() . '/users', 'success', "User '{$user['username']}' updated successfully.");
                return;
            } catch (\Throwable $e) {
                $this->redirect(App::baseURL() . '/users', 'error', "Failed to update user: " . $e->getMessage());
                return;
            }
        }
        $this->redirect(App::baseURL() . '/users');
    }

    public function toggleStatus(int $id): void
    {
        $this->checkPermission('users', 'edit');

        $currentUser = $this->getUser();
        if ($currentUser && (int)$currentUser['id'] === $id) {
            $this->redirect(App::baseURL() . '/users', 'error', 'Action restricted: You cannot deactivate your own logged-in account.');
            return;
        }

        $user = Database::table('users')->where('id', $id)->first();
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            Database::table('users')->where('id', $id)->update([
                'is_active'  => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $statusLabel = $newStatus ? 'activated' : 'deactivated';
            $this->logAudit('toggle_status', 'users', $id, "User {$user['username']} {$statusLabel}");
            $this->redirect(App::baseURL() . '/users', 'success', "User '{$user['username']}' has been {$statusLabel}.");
            return;
        }
        $this->redirect(App::baseURL() . '/users', 'error', 'User not found.');
    }

    public function delete(int $id): void
    {
        $this->checkPermission('users', 'delete');

        $currentUser = $this->getUser();
        if ($currentUser && (int)$currentUser['id'] === $id) {
            $this->redirect(App::baseURL() . '/users', 'error', 'Action restricted: You cannot delete your own logged-in account.');
            return;
        }

        $user = Database::table('users')->where('id', $id)->first();
        if (!$user) {
            $this->redirect(App::baseURL() . '/users', 'error', 'User not found.');
            return;
        }

        if (in_array($user['role'], ['super_admin', 'superadmin']) || in_array($user['username'], ['admin', 'superadmin'])) {
            $this->redirect(App::baseURL() . '/users', 'error', 'The primary administrator account cannot be deleted for system safety.');
            return;
        }

        try {
            Database::table('users')->where('id', $id)->delete();
            $this->logAudit('delete_user', 'users', $id, "Deleted user {$user['username']}");
            $this->redirect(App::baseURL() . '/users', 'success', "User '{$user['username']}' was deleted successfully.");
            return;
        } catch (\Throwable $e) {
            $this->redirect(App::baseURL() . '/users', 'error', "Error deleting user: " . $e->getMessage());
            return;
        }
    }
}
