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

  private function upload_image_basic($file, &$errors)
  {
    if (!isset($file) || !isset($file['error']) || $file['error'] === 4) {
      $errors['image'] = 'Bạn chưa chọn ảnh.';
      return '';
    }

    if ($file['error'] !== 0) {
      $errors['image'] = 'Upload lỗi. Mã lỗi: ' . $file['error'];
      return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if (!in_array($ext, $allowed)) {
      $errors['image'] = 'Chỉ cho phép: jpg, jpeg, png, gif, webp.';
      return '';
    }

    // ✅ uploads chung: php-baitap/uploads
    $uploadDir = __DIR__ . '/../../uploads';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $newName  = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destPath = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
      $errors['image'] = 'Không thể lưu file. Kiểm tra quyền thư mục uploads/.';
      return '';
    }

    // ✅ lưu DB dạng uploads/xxx.png
    return 'uploads/' . $newName;
  }

  private function delete_file_if_exists($relPath)
  {
    if ($relPath === null || $relPath === '') return;

    // relPath = uploads/xxx.png => full = php-baitap/uploads/xxx.png
    $fullPath = __DIR__ . '/../../' . $relPath;
    if (file_exists($fullPath)) {
      @unlink($fullPath);
    }
  }

  public function index()
  {
    $products = $this->model->all();
    $this->view('products/index', array('products' => $products));
  }

  public function create()
  {
    $this->view('products/form', array(
      'mode' => 'create',
      'errors' => array(),
      'form' => array('id'=>'', 'name'=>'', 'price'=>'', 'description'=>'', 'image_path'=>'')
    ));
  }

  public function store()
  {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    $errors = array();
    if ($name === '') $errors['name'] = 'Vui lòng nhập tên.';
    if ($price === '' || !is_numeric($price)) {
      $errors['price'] = 'Vui lòng nhập giá (số).';
    } elseif ((float)$price < 0) {
      $errors['price'] = 'Giá không được âm.';
    }

    // thêm mới: bắt buộc chọn ảnh
    $hasNewFile = isset($_FILES['image']) && $_FILES['image']['error'] !== 4;
    if (!$hasNewFile) $errors['image'] = 'Thêm mới cần chọn ảnh.';

    $imagePath = '';
    if (empty($errors)) {
      $imagePath = $this->upload_image_basic($_FILES['image'], $errors);
    }

    if (!empty($errors)) {
      $this->view('products/form', array(
        'mode' => 'create',
        'errors' => $errors,
        'form' => array('id'=>'', 'name'=>$name, 'price'=>$price, 'description'=>$description, 'image_path'=>'')
      ));
      return;
    }

    $this->model->create($name, $price, $description, $imagePath);
    $this->redirect('url.php?page=product/index');
  }

  public function edit()
  {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $row = $this->model->find($id);
    if (!$row) $this->redirect('url.php?page=product/index');

    $this->view('products/form', array(
      'mode' => 'edit',
      'errors' => array(),
      'form' => $row
    ));
  }

  public function update()
  {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $oldImagePath = isset($_POST['old_image_path']) ? trim($_POST['old_image_path']) : '';

    $errors = array();
    if ($name === '') $errors['name'] = 'Vui lòng nhập tên.';
    if ($price === '' || !is_numeric($price)) {
      $errors['price'] = 'Vui lòng nhập giá (số).';
    } elseif ((float)$price < 0) {
      $errors['price'] = 'Giá không được âm.';
    }

    $imagePath = $oldImagePath;
    $hasNewFile = isset($_FILES['image']) && $_FILES['image']['error'] !== 4;

    if (empty($errors) && $hasNewFile) {
      $newPath = $this->upload_image_basic($_FILES['image'], $errors);
      if ($newPath !== '') {
        $this->delete_file_if_exists($oldImagePath);
        $imagePath = $newPath;
      }
    }

    if (!empty($errors)) {
      $this->view('products/form', array(
        'mode' => 'edit',
        'errors' => $errors,
        'form' => array(
          'id'=>$id,
          'name'=>$name,
          'price'=>$price,
          'description'=>$description,
          'image_path'=>$oldImagePath
        )
      ));
      return;
    }

    $this->model->update($id, $name, $price, $description, $imagePath);
    $this->redirect('url.php?page=product/index');
  }

  public function delete()
  {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
      $row = $this->model->find($id);
      if ($row && isset($row['image_path'])) {
        $this->delete_file_if_exists($row['image_path']);
      }
      $this->model->delete($id);
    }
    $this->redirect('url.php?page=product/index');
  }
}
