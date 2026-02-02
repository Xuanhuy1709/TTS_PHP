<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductController extends Controller
{
  public $model;

  public function __construct($conn)
  {
    $this->model = new ProductModel($conn);
  }

  // /product/index
  public function index()
  {
    $products = $this->model->all();
    $this->view('products/index', array(
      'products' => $products
    ));
  }

  // /product/create
  public function create()
  {
    $this->view('products/form', array(
      'mode' => 'create',
      'errors' => array(),
      'form' => array('id'=>'', 'name'=>'', 'price'=>'', 'description'=>'')
    ));
  }

  // /product/store  (POST)
  public function store()
  {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    $errors = array();
    if ($name === '') $errors['name'] = 'Vui lòng nhập tên.';
    if ($price === '' || !is_numeric($price)) $errors['price'] = 'Vui lòng nhập giá (số).';

    if (!empty($errors)) {
      $this->view('products/form', array(
        'mode' => 'create',
        'errors' => $errors,
        'form' => array('id'=>'', 'name'=>$name, 'price'=>$price, 'description'=>$description)
      ));
      return;
    }

    $this->model->create($name, $price, $description);
    $this->redirect('url.php?page=product/index');
  }

  // /product/edit&id=1
  public function edit()
  {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $row = $this->model->find($id);
    if (!$row) {
      $this->redirect('url.php?page=product/index');
    }

    $this->view('products/form', array(
      'mode' => 'edit',
      'errors' => array(),
      'form' => $row
    ));
  }

  // /product/update (POST)
  public function update()
  {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    $errors = array();
    if ($name === '') $errors['name'] = 'Vui lòng nhập tên.';
    if ($price === '' || !is_numeric($price)) $errors['price'] = 'Vui lòng nhập giá (số).';

    if (!empty($errors)) {
      $this->view('products/form', array(
        'mode' => 'edit',
        'errors' => $errors,
        'form' => array('id'=>$id, 'name'=>$name, 'price'=>$price, 'description'=>$description)
      ));
      return;
    }

    $this->model->update($id, $name, $price, $description);
    $this->redirect('url.php?page=product/index');
  }

  // /product/delete&id=1
  public function delete()
  {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
      $this->model->delete($id);
    }
    $this->redirect('url.php?page=product/index');
  }
}
