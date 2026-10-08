<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Config\App;

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect(App::baseURL() . '/dashboard');
        }

        if ($this->request->isPost()) {
            $username = trim($this->request->post('username', ''));
            $password = trim($this->request->post('password', ''));
            $quickRole = $this->request->post('quick_role');

            // Handle Quick 1-Click Role Login for instant testing of all 9 roles
            if ($quickRole) {
                $user = Database::table('users')->where('role', $quickRole)->first();
                if ($user) {
                    $this->setLoginSession($user);
                    $this->logAudit('login', 'auth', $user['id'], "Quick login as {$user['role']}");
                    $this->redirect(App::baseURL() . '/dashboard', 'success', "Welcome, {$user['full_name']} ({$user['role']})!");
                }
            }

            // Standard Password Login
            $user = Database::table('users')->where('username', $username)->first();
            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_active']) {
                    $this->render('auth.login', ['error' => 'Your account is deactivated. Contact Administrator.'], false);
                    return;
                }

                $this->setLoginSession($user);
                $this->logAudit('login', 'auth', $user['id'], "User logged in successfully");
                $this->redirect(App::baseURL() . '/dashboard', 'success', "Welcome back, {$user['full_name']}!");
            } else {
                $this->render('auth.login', ['error' => 'Invalid username or password (default password is: admin123)'], false);
                return;
            }
        }

        $this->render('auth.login', [], false);
    }

    private function setLoginSession(array $user): void
    {
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('full_name', $user['full_name']);
        Session::set('role', $user['role']);
        Session::set('email', $user['email']);

        Database::table('users')->where('id', $user['id'])->update([
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    public function logout(): void
    {
        $this->logAudit('logout', 'auth', Session::get('user_id'), "User logged out");
        Session::destroy();
        $this->redirect(App::baseURL() . '/login', 'success', 'You have been logged out successfully.');
    }

    public function profile(): void
    {
        $this->requireAuth();
        $user = Database::table('users')->where('id', Session::get('user_id'))->first();

        if ($this->request->isPost()) {
            $fullName = trim($this->request->post('full_name'));
            $phone = trim($this->request->post('phone'));
            $password = trim($this->request->post('password'));

            $updateData = [
                'full_name' => $fullName,
                'phone'     => $phone,
            ];

            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            Database::table('users')->where('id', $user['id'])->update($updateData);
            Session::set('full_name', $fullName);
            $this->logAudit('update_profile', 'users', $user['id'], "Updated personal profile");

            $this->redirect(App::baseURL() . '/profile', 'success', 'Profile updated successfully.');
        }

        $this->render('auth.profile', [
            'pageTitle'    => 'My Profile - INFOSOF Pharmacy',
            'activeModule' => 'users',
            'user'         => $user
        ]);
    }
}
