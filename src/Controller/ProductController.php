<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ProductService;
use App\Support\Csrf;
use App\Support\Session;
use App\Support\View;

final class ProductController extends BaseController
{
    public function __construct(View $v, Session $s, Csrf $c, private ProductService $products)
    {
        parent::__construct($v, $s, $c);
    }

    public function index(): void
    {
        $this->view->render('products/index', ['title' => 'Productos', 'products' => $this->products->list()]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view->render('products/form', [
            'title' => 'Nuevo producto', 'product' => [], 'errors' => [],
            'action' => 'product.store', 'id' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $res = $this->products->create($_POST);
        if ($res['errors']) {
            $this->view->render('products/form', [
                'title' => 'Nuevo producto', 'product' => $_POST, 'errors' => $res['errors'],
                'action' => 'product.store', 'id' => null,
            ]);
            return;
        }
        $this->session->flash('success', 'Producto creado.');
        $this->redirect('home');
    }

    public function edit(): void
    {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->products->get($id);
        if ($product === null) {
            $this->session->flash('error', 'Producto no encontrado.');
            $this->redirect('home');
        }
        $this->view->render('products/form', [
            'title' => 'Editar producto', 'product' => $product, 'errors' => [],
            'action' => 'product.update', 'id' => $id,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $id  = (int) ($_POST['id'] ?? 0);
        $res = $this->products->update($id, $_POST);
        if (!$res['ok']) {
            $this->view->render('products/form', [
                'title' => 'Editar producto', 'product' => $_POST, 'errors' => $res['errors'],
                'action' => 'product.update', 'id' => $id,
            ]);
            return;
        }
        $this->session->flash('success', 'Producto actualizado.');
        $this->redirect('home');
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $ok = $this->products->delete((int) ($_POST['id'] ?? 0));
        $this->session->flash($ok ? 'success' : 'error', $ok ? 'Producto eliminado.' : 'No se pudo eliminar.');
        $this->redirect('home');
    }
}
