<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class DocumentsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $category = $this->request->get('category');
        $query = "SELECT d.*, u.full_name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id";
        $params = [];

        if (!empty($category)) {
            $query .= " WHERE d.category = ?";
            $params[] = $category;
        }

        $query .= " ORDER BY d.id DESC";
        $documents = Database::raw($query, $params);

        $this->render('documents.index', [
            'pageTitle'    => 'Central Document & Certificate Archive (Module 34) - INFOSOF',
            'activeModule' => 'documents',
            'documents'    => $documents,
            'category'     => $category
        ]);
    }

    public function upload(): void
    {
        $this->checkPermission('settings', 'create');

        if ($this->request->isPost()) {
            $title = trim($this->request->post('title'));
            $category = $this->request->post('category', 'Other');

            $file = $this->request->getFile('doc_file');
            if ($file && !empty($file['tmp_name'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'doc_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $targetDir = dirname(__DIR__, 2) . '/public/uploads/documents';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $fileName)) {
                    $docPath = 'uploads/documents/' . $fileName;

                    Database::table('documents')->insert([
                        'title'        => $title,
                        'category'     => $category,
                        'file_path'    => $docPath,
                        'file_size_kb' => round($file['size'] / 1024),
                        'file_type'    => $file['type'],
                        'uploaded_by'  => $this->getUser()['id'] ?? null,
                        'created_at'   => date('Y-m-d H:i:s')
                    ]);

                    $this->redirect(App::baseURL() . '/documents', 'success', "Document '{$title}' archived.");
                }
            }
        }
        $this->redirect(App::baseURL() . '/documents');
    }
}
