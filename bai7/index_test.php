<?php
// =========================
// BÀI 7 - CRUD PRODUCTS + UPLOAD (MYSQL + HTML + CSS thuần)
// Refactor: giữ UI, giảm code thừa
// =========================

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Kết nối DB thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
function fetch_one($conn, $sql) {
    $rs = mysqli_query($conn, $sql);
    if (!$rs) return null;
    $row = mysqli_fetch_assoc($rs);
    return $row ? $row : null;
}

$action = 'list';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

$message = '';
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

$errors = array();

$form = array(
    'id' => '',
    'name' => '',
    'price' => '',
    'description' => '',
    'image_path' => ''
);

function upload_image_basic($file, &$errors) {
    if (!isset($file) || !isset($file['error']) || $file['error'] === 4) {
        return '';
    }
    if ($file['error'] !== 0) {
        $errors[] = 'Upload lỗi. Mã lỗi: ' . $file['error'];
        return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg','jpeg','png','gif','webp');
    if (!in_array($ext, $allowed)) {
        $errors[] = 'Chỉ cho phép: jpg, jpeg, png, gif, webp.';
        return '';
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $dest = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $errors[] = 'Không thể lưu file. Kiểm tra quyền thư mục uploads/.';
        return '';
    }

    return 'uploads/' . $newName;
}

if ($action === 'delete') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $row = fetch_one($conn, "SELECT image_path FROM products WHERE id=$id");
        if ($row && $row['image_path'] !== '') {
            $oldPath = __DIR__ . '/' . $row['image_path'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        mysqli_query($conn, "DELETE FROM products WHERE id=$id");
        redirect('index.php?action=list&msg=' . urlencode('Đã xóa sản phẩm!'));
    }
    $action = 'list';
}

if ($action === 'edit') {
    if (!isset($_GET['id'])) {
        redirect('index.php?action=list');
    }

    $id = (int)$_GET['id'];
    $row = fetch_one($conn, "SELECT * FROM products WHERE id=$id");
    if (!$row) {
        redirect('index.php?action=list&msg=' . urlencode('Không tìm thấy sản phẩm!'));
    }

    $form['id'] = $row['id'];
    $form['name'] = $row['name'];
    $form['price'] = $row['price'];
    $form['description'] = $row['description'];
    $form['image_path'] = $row['image_path'];
}

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php?action=list');
    }

    $form['id'] = isset($_POST['id']) ? trim($_POST['id']) : '';
    $form['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';
    $form['price'] = isset($_POST['price']) ? trim($_POST['price']) : '';
    $form['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
    $form['image_path'] = isset($_POST['old_image_path']) ? trim($_POST['old_image_path']) : '';

    $id = (int)$form['id'];

    // Validate cơ bản
    if ($form['name'] === '') {
        $errors[] = 'Vui lòng nhập tên sản phẩm.';
    }
    if ($form['price'] === '' || !is_numeric($form['price'])) {
        $errors[] = 'Vui lòng nhập giá (số).';
    } else if ((float)$form['price'] < 0) {
        $errors[] = 'Giá không được âm.';
    }

    $newPath = '';
    if (isset($_FILES['image'])) {
        $newPath = upload_image_basic($_FILES['image'], $errors);
    }

    if ($id === 0 && $newPath === '') {
        $errors[] = 'Thêm mới cần chọn ảnh.';
    }

    if (empty($errors)) {
        if ($newPath !== '') {
            if ($id !== 0 && $form['image_path'] !== '') {
                $oldPath = __DIR__ . '/' . $form['image_path'];
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $form['image_path'] = $newPath;
        }

        $name  = mysqli_real_escape_string($conn, $form['name']);
        $desc  = mysqli_real_escape_string($conn, $form['description']);
        $img   = mysqli_real_escape_string($conn, $form['image_path']);
        $price = (float)$form['price'];

        if ($id === 0) {
            mysqli_query($conn,
                "INSERT INTO products (name, price, description, image_path)
                 VALUES ('$name', $price, '$desc', '$img')"
            );
            redirect('index.php?action=list&msg=' . urlencode('Đã thêm sản phẩm!'));
        } else {
            mysqli_query($conn,
                "UPDATE products
                 SET name='$name', price=$price, description='$desc', image_path='$img'
                 WHERE id=$id"
            );
            redirect('index.php?action=list&msg=' . urlencode('Đã cập nhật sản phẩm!'));
        }
    }

    $action = ($id === 0) ? 'add' : 'edit';
}
$products = array();
if ($action === 'list') {
    $rs = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($rs)) {
        $products[] = $row;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 7 - CRUD Sản phẩm</title>

  <style>
    *{box-sizing:border-box}
    body{margin:0;padding:24px;font-family:Arial,sans-serif;background:#f4f6f8;color:#111}
    .wrap{max-width:1000px;margin:0 auto}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px}
    h1{margin:0 0 12px;font-size:18px}
    .msg-ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:12px}
    .msg-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:12px}
    a{color:inherit;text-decoration:none}
    .actions{margin-bottom:12px;display:flex;gap:10px;flex-wrap:wrap}
    .btn{display:inline-block;padding:8px 12px;border-radius:10px;border:1px solid #d1d5db;background:#fff;cursor:pointer;font-size:14px}
    .btn-primary{background:#2563eb;border-color:#2563eb;color:#fff}
    .btn-danger{background:#ef4444;border-color:#ef4444;color:#fff}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #e5e7eb;padding:10px;text-align:center;vertical-align:middle}
    th{background:#f9fafb}
    td.text-left{text-align:left}
    img.thumb{width:90px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;display:block;margin:0 auto}
    label{display:block;font-size:13px;margin:0 0 6px;color:#374151;text-align:left}
    input,textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:10px;outline:none;font-size:14px}
    textarea{min-height:110px;resize:vertical}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    @media (max-width:800px){.grid{grid-template-columns:1fr}}
    .full{grid-column:1/-1}
    .hint{margin-top:6px;font-size:13px;color:#6b7280;text-align:left}
    .confirm{font-size:13px;color:#6b7280}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Bài 7 — Quản lý sản phẩm</h1>

      <?php if ($message !== '') { ?>
        <div class="msg-ok"><?php echo h($message); ?></div>
      <?php } ?>

      <?php if (!empty($errors)) { ?>
        <div class="msg-err">
          <b>Có lỗi:</b>
          <ul style="margin:8px 0 0 18px;">
            <?php foreach ($errors as $er) { ?>
              <li><?php echo h($er); ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <?php if ($action === 'list') { ?>

        <div class="actions">
          <a class="btn btn-primary" href="?action=add">+ Thêm sản phẩm</a>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Ảnh</th>
              <th>Tên</th>
              <th>Giá</th>
              <th>Mô tả</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)) { ?>
              <tr><td colspan="6">Chưa có sản phẩm.</td></tr>
            <?php } else { ?>
              <?php foreach ($products as $p) { ?>
                <tr>
                  <td><?php echo (int)$p['id']; ?></td>
                  <td>
                    <?php if ($p['image_path'] !== '') { ?>
                      <img class="thumb" src="<?php echo h($p['image_path']); ?>" alt="img">
                    <?php } else { ?>
                      <span class="confirm">No image</span>
                    <?php } ?>
                  </td>
                  <td><?php echo h($p['name']); ?></td>
                  <td><?php echo number_format((float)$p['price']); ?> đ</td>
                  <td class="text-left"><?php echo nl2br(h($p['description'])); ?></td>
                  <td>
                    <a class="btn" href="?action=edit&id=<?php echo (int)$p['id']; ?>">Sửa</a>
                    <a class="btn btn-danger"
                       href="?action=delete&id=<?php echo (int)$p['id']; ?>"
                       onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>

      <?php } ?>

      <?php if ($action === 'add' || $action === 'edit') { ?>

        <form method="post" action="?action=save" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo h($form['id']); ?>">
          <input type="hidden" name="old_image_path" value="<?php echo h($form['image_path']); ?>">

          <div class="grid">
            <div>
              <label>Tên sản phẩm</label>
              <input name="name" value="<?php echo h($form['name']); ?>">
            </div>

            <div>
              <label>Giá</label>
              <input name="price" value="<?php echo h($form['price']); ?>" placeholder="VD: 120000">
            </div>

            <div class="full">
              <label>Mô tả</label>
              <textarea name="description"><?php echo h($form['description']); ?></textarea>
            </div>

            <div class="full">
              <label>Ảnh sản phẩm</label>

              <?php if ($action === 'edit' && $form['image_path'] !== '') { ?>
                <img class="thumb" src="<?php echo h($form['image_path']); ?>" alt="current"
                     style="width:180px;height:130px;">
                <div class="hint">Ảnh hiện tại (không chọn ảnh mới thì giữ ảnh này)</div>
              <?php } ?>

              <input type="file" name="image" accept="image/*">
              <div class="hint">Thêm mới: bắt buộc chọn ảnh. Sửa: có thể bỏ trống để giữ ảnh cũ.</div>
            </div>
          </div>

          <div class="actions" style="margin-top:14px;">
            <button class="btn btn-primary" type="submit">Lưu</button>
            <a class="btn" href="?action=list">Hủy</a>
          </div>
        </form>

      <?php } ?>

    </div>
  </div>
</body>
</html>
<?php mysqli_close($conn); ?>
