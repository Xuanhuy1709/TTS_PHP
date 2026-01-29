<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Kết nối DB thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

$message = '';
$errors  = array();

$form = array(
    'id' => '',
    'name' => '',
    'price' => '',
    'description' => '',
    'image_path' => ''
);

function upload_image_basic($file, &$errors) {

    if (!isset($file) || !isset($file['error']) || $file['error'] === 4) {
        $errors[] = 'Bạn chưa chọn ảnh.';
        return '';
    }

    if ($file['error'] !== 0) {
        $errors[] = 'Upload lỗi. Mã lỗi: ' . $file['error'];
        return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (!in_array($ext, $allowed)) {
        $errors[] = 'Chỉ cho phép: jpg, jpeg, png, gif, webp.';
        return '';
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName  = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destPath = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Không thể lưu file. Kiểm tra quyền thư mục uploads/.';
        return '';
    }

    return 'uploads/' . $newName;
}

if ($action === 'delete' && isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    $rs  = mysqli_query($conn, "SELECT image_path FROM products WHERE id=$id");
    $row = mysqli_fetch_assoc($rs);

    if ($row && $row['image_path'] !== '') {
        $oldPath = __DIR__ . '/' . $row['image_path'];
        if (file_exists($oldPath)) @unlink($oldPath);
    }

    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    $message = 'Đã xóa sản phẩm!';
    $action  = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {

    $id = (int)$_GET['id'];
    $rs = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $row = mysqli_fetch_assoc($rs);

    if ($row) {
        $form = $row;
    } else {
        $message = 'Không tìm thấy sản phẩm!';
        $action = 'list';
    }
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['id']          = trim($_POST['id'] ?? '');
    $form['name']        = trim($_POST['name'] ?? '');
    $form['price']       = trim($_POST['price'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['image_path']  = trim($_POST['old_image_path'] ?? '');

    if ($form['name'] === '') $errors[] = 'Vui lòng nhập tên sản phẩm.';
    if ($form['price'] === '' || !is_numeric($form['price'])) {
        $errors[] = 'Vui lòng nhập giá hợp lệ.';
    } elseif ((float)$form['price'] < 0) {
        $errors[] = 'Giá không được âm.';
    }

    $id = (int)$form['id'];

    $hasNewFile = isset($_FILES['image']) && $_FILES['image']['error'] !== 4;

    if ($id === 0 && !$hasNewFile) {
        $errors[] = 'Thêm mới cần chọn ảnh.';
    }

    if (empty($errors) && $hasNewFile) {

        $newPath = upload_image_basic($_FILES['image'], $errors);

        if ($newPath !== '') {
            if ($id !== 0 && $form['image_path'] !== '') {
                $oldPath = __DIR__ . '/' . $form['image_path'];
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $form['image_path'] = $newPath;
        }
    }

    if (empty($errors)) {

        $name  = mysqli_real_escape_string($conn, $form['name']);
        $desc  = mysqli_real_escape_string($conn, $form['description']);
        $img   = mysqli_real_escape_string($conn, $form['image_path']);
        $price = (float)$form['price'];

        if ($id === 0) {
            mysqli_query($conn,
                "INSERT INTO products (name, price, description, image_path)
                 VALUES ('$name', $price, '$desc', '$img')"
            );
            $message = 'Đã thêm sản phẩm!';
        } else {
            mysqli_query($conn,
                "UPDATE products
                 SET name='$name', price=$price, description='$desc', image_path='$img'
                 WHERE id=$id"
            );
            $message = 'Đã cập nhật sản phẩm!';
        }

        $action = 'list';
    } else {
        $action = ($id === 0) ? 'add' : 'edit';
    }
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
<div class="card">
<h1>Bài 7 — Quản lý sản phẩm</h1>
<?php if ($message !== '') { ?>
  <div class="msg-ok"><?php echo htmlspecialchars($message); ?></div>
<?php } ?>

<?php if (!empty($errors)) { ?>
  <div class="msg-err">
    <b>Có lỗi:</b>
    <ul>
      <?php foreach ($errors as $er) { ?>
        <li><?php echo htmlspecialchars($er); ?></li>
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
  <th>ID</th><th>Ảnh</th><th>Tên</th><th>Giá</th><th>Mô tả</th><th>Hành động</th>
</tr>
</thead>
<tbody>
<?php if (empty($products)) { ?>
<tr><td colspan="6">Chưa có sản phẩm.</td></tr>
<?php } else { foreach ($products as $p) { ?>
<tr>
  <td><?php echo (int)$p['id']; ?></td>
  <td><?php if ($p['image_path'] !== '') { ?><img class="thumb" src="<?php echo htmlspecialchars($p['image_path']); ?>"><?php } ?></td>
  <td><?php echo htmlspecialchars($p['name']); ?></td>
  <td><?php echo number_format((float)$p['price']); ?> đ</td>
  <td class="text-left"><?php echo nl2br(htmlspecialchars($p['description'])); ?></td>
  <td>
    <a class="btn" href="?action=edit&id=<?php echo (int)$p['id']; ?>">Sửa</a>
    <a class="btn btn-danger" href="?action=delete&id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
  </td>
</tr>
<?php }} ?>
</tbody>
</table>

<?php } ?>

<?php if ($action === 'add' || $action === 'edit') { ?>

<div class="actions">
  <a class="btn" href="?action=list">← Quay lại</a>
</div>

<form method="post" action="?action=save" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($form['id']); ?>">
<input type="hidden" name="old_image_path" value="<?php echo htmlspecialchars($form['image_path']); ?>">

<div class="grid">
  <div>
    <label>Tên sản phẩm</label>
    <input name="name" value="<?php echo htmlspecialchars($form['name']); ?>">
  </div>

  <div>
    <label>Giá</label>
    <input name="price" value="<?php echo htmlspecialchars($form['price']); ?>">
  </div>

  <div class="full">
    <label>Mô tả</label>
    <textarea name="description"><?php echo htmlspecialchars($form['description']); ?></textarea>
  </div>

  <div class="full">
    <label>Ảnh</label>
    <?php if ($action === 'edit' && $form['image_path'] !== '') { ?>
      <img class="thumb" src="<?php echo htmlspecialchars($form['image_path']); ?>" style="width:180px;height:130px;">
    <?php } ?>
    <input type="file" name="image" accept="image/*">
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
