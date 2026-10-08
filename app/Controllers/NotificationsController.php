<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class NotificationsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $notifications = Database::table('notifications')->orderBy('id', 'DESC')->get();

        $this->render('notifications.index', [
            'pageTitle'     => 'Notifications & Real-Time Alerts (Module 29) - INFOSOF',
            'activeModule'  => 'notifications',
            'notifications' => $notifications
        ]);
    }

    public function markAllRead(): void
    {
        $this->requireAuth();
        Database::raw("UPDATE notifications SET is_read = 1");
        $this->redirect(App::baseURL() . '/notifications', 'success', 'All notifications marked as read.');
    }
}
