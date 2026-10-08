<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class CategoriesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $categories = Database::raw("SELECT c.*, COUNT(m.id) as total_medicines,
                      COALESCE(SUM((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active')), 0) as total_stock
                      FROM categories c
                      LEFT JOIN medicines m ON c.id = m.category_id AND m.is_active = 1
                      GROUP BY c.id
                      ORDER BY c.name ASC");

        $this->render('categories.index', [
            'pageTitle'    => 'Medicine Categories & Therapeutic Classification - INFOSOF',
            'activeModule' => 'categories',
            'categories'   => $categories
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('categories', 'create');

        if ($this->request->isPost()) {
            $name = trim($this->request->post('name'));
            $desc = trim($this->request->post('description'));
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            if (!empty($name)) {
                $id = Database::table('categories')->insert([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $desc,
                    'is_active'   => 1,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);
                $this->logAudit('create_category', 'categories', $id, "Added category {$name}");
                $this->redirect(App::baseURL() . '/categories', 'success', "Category '{$name}' created.");
            }
        }
        $this->redirect(App::baseURL() . '/categories');
    }

    public function delete(int $id): void
    {
        $this->checkPermission('categories', 'delete');
        $cat = Database::table('categories')->where('id', $id)->first();
        if ($cat) {
            Database::table('categories')->where('id', $id)->delete();
            $this->logAudit('delete_category', 'categories', $id, "Deleted category {$cat['name']}");
            $this->redirect(App::baseURL() . '/categories', 'success', 'Category deleted.');
        }
        $this->redirect(App::baseURL() . '/categories');
    }
}
