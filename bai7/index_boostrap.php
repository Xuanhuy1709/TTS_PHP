<?php
// =========================
// BÀI 7 - CRUD PRODUCTS + UPLOAD (MYSQL + BOOTSTRAP)
// =========================

// 1) Kết nối MySQL (MySQLi)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Kết nối DB thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// 2) Lấy action
$action = 'list';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

$message = '';
$errors = array();

$form = array(
    'id' => '',
    'name' => '',
    'price' => '',
    'description' => '',
    'image_path' => ''
);

// 3) Hàm upload ảnh (cơ bản)
function upload_image_basic($file, &$errors) {
    // Nếu không có file
    if (!isset($file)) {
        $errors[] = 'Bạn chưa chọn ảnh.';
        return '';
    }

    // error = 4 nghĩa là không chọn file
    if (!isset($file['error']) || $file['error'] === 4) {
        $errors[] = 'Bạn chưa chọn ảnh.';
        return '';
    }

    // lỗi upload
    if ($file['error'] !== 0) {
        $errors[] = 'Upload lỗi. Mã lỗi: ' . $file['error'];
        return '';
    }

    $fileName = $file['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if (!in_array($ext, $allowed)) {
        $errors[] = 'Chỉ cho phép: jpg, jpeg, png, gif, webp.';
        return '';
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Tên file mới tránh trùng
    $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destPath = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Không thể lưu file. Kiểm tra quyền ghi thư mục uploads/.';
        return '';
    }

    // Lưu path tương đối để <img src="">
    return 'uploads/' . $newName;
}

// 4) DELETE
if ($action === 'delete') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        // lấy ảnh cũ để xóa file
        $rs = mysqli_query($conn, "SELECT image_path FROM products WHERE id = $id");
        $row = mysqli_fetch_assoc($rs);
        if ($row && $row['image_path'] !== '') {
            $oldPath = __DIR__ . '/' . $row['image_path'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        mysqli_query($conn, "DELETE FROM products WHERE id = $id");
        $message = 'Đã xóa sản phẩm!';
    }
    $action = 'list';
}

// 5) EDIT: load data
if ($action === 'edit') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $rs = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
        $row = mysqli_fetch_assoc($rs);

        if ($row) {
            $form['id'] = $row['id'];
            $form['name'] = $row['name'];
            $form['price'] = $row['price'];
            $form['description'] = $row['description'];
            $form['image_path'] = $row['image_path'];
        } else {
            $message = 'Không tìm thấy sản phẩm!';
            $action = 'list';
        }
    } else {
        $action = 'list';
    }
}

// 6) ADD: form rỗng
if ($action === 'add') {
    // giữ form rỗng
}

// 7) SAVE (add/edit)
if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $form['id'] = isset($_POST['id']) ? trim($_POST['id']) : '';
        $form['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';
        $form['price'] = isset($_POST['price']) ? trim($_POST['price']) : '';
        $form['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
        $form['image_path'] = isset($_POST['old_image_path']) ? trim($_POST['old_image_path']) : '';

        // Validate cơ bản
        if ($form['name'] === '') {
            $errors[] = 'Vui lòng nhập tên sản phẩm.';
        }

        if ($form['price'] === '' || !is_numeric($form['price'])) {
            $errors[] = 'Vui lòng nhập giá (số).';
        } else {
            if ((float)$form['price'] < 0) {
                $errors[] = 'Giá không được âm.';
            }
        }

        $id = (int)$form['id'];

        // Upload ảnh:
        // - Add: bắt buộc có ảnh
        // - Edit: có thể không chọn ảnh (giữ ảnh cũ)
        $hasNewFile = false;
        if (isset($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] !== 4) {
            $hasNewFile = true;
        }

        if ($id === 0) {
            // add: bắt buộc có ảnh
            if (!$hasNewFile) {
                $errors[] = 'Thêm mới cần chọn ảnh.';
            }
        }

        if (empty($errors)) {

            // nếu có file mới thì upload
            if ($hasNewFile) {
                $newPath = upload_image_basic($_FILES['image'], $errors);
                if ($newPath !== '') {

                    // nếu đang edit và có ảnh cũ thì xóa ảnh cũ
                    if ($id !== 0 && $form['image_path'] !== '') {
                        $oldPath = __DIR__ . '/' . $form['image_path'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $form['image_path'] = $newPath;
                }
            }

            // nếu upload gây lỗi thì dừng
            if (empty($errors)) {

                // Escape (cơ bản)
                $name = mysqli_real_escape_string($conn, $form['name']);
                $desc = mysqli_real_escape_string($conn, $form['description']);
                $img  = mysqli_real_escape_string($conn, $form['image_path']);
                $price = (float)$form['price'];

                if ($id === 0) {
                    // INSERT
                    $sql = "
                        INSERT INTO products (name, price, description, image_path)
                        VALUES ('$name', $price, '$desc', '$img')
                    ";
                    mysqli_query($conn, $sql);
                    $message = 'Đã thêm sản phẩm!';
                    $action = 'list';
                } else {
                    // UPDATE
                    $sql = "
                        UPDATE products
                        SET name = '$name',
                            price = $price,
                            description = '$desc',
                            image_path = '$img'
                        WHERE id = $id
                    ";
                    mysqli_query($conn, $sql);
                    $message = 'Đã cập nhật sản phẩm!';
                    $action = 'list';
                }
            }
        }

        // nếu lỗi thì quay lại form
        if (!empty($errors)) {
            if ($id === 0) {
                $action = 'add';
            } else {
                $action = 'edit';
            }
        }
    } else {
        $action = 'list';
    }
}

// 8) LIST
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
  <meta
    charset="utf-8"
  >
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1"
  >
  <title>
    Bài 7 - CRUD Sản phẩm
  </title>

  <!-- Bootstrap CDN -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body class="bg-light">
  <div
    class="container py-4"
  >
    <div
      class="card shadow-sm"
    >
      <div
        class="card-body"
      >
        <h1
          class="h5 mb-3"
        >
          Bài 7 — Quản lý sản phẩm (CRUD + Upload ảnh)
        </h1>

        <?php if ($message !== '') { ?>
          <div
            class="alert alert-success"
            role="alert"
          >
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php } ?>

        <?php if (!empty($errors)) { ?>
          <div
            class="alert alert-danger"
            role="alert"
          >
            <b>Có lỗi:</b>
            <ul class="mb-0">
              <?php foreach ($errors as $er) { ?>
                <li><?php echo htmlspecialchars($er); ?></li>
              <?php } ?>
            </ul>
          </div>
        <?php } ?>

        <?php if ($action === 'list') { ?>

          <div class="mb-3">
            <a
              class="btn btn-primary"
              href="?action=add"
            >
              + Thêm sản phẩm
            </a>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
              <thead class="table-light">
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
                  <tr>
                    <td colspan="6">Chưa có sản phẩm.</td>
                  </tr>
                <?php } else { ?>
                  <?php foreach ($products as $p) { ?>
                    <tr>
                      <td><?php echo (int)$p['id']; ?></td>
                      <td style="width: 120px;">
                        <?php if ($p['image_path'] !== '') { ?>
                          <img
                            src="<?php echo htmlspecialchars($p['image_path']); ?>"
                            alt="img"
                            style="width: 90px; height: 70px; object-fit: cover; border-radius: 8px;"
                          >
                        <?php } else { ?>
                          <span class="text-muted">No image</span>
                        <?php } ?>
                      </td>
                      <td><?php echo htmlspecialchars($p['name']); ?></td>
                      <td><?php echo number_format((float)$p['price']); ?> đ</td>
                      <td class="text-start">
                        <?php echo nl2br(htmlspecialchars($p['description'])); ?>
                      </td>
                      <td style="width: 170px;">
                        <a
                          class="btn btn-sm btn-outline-secondary"
                          href="?action=edit&id=<?php echo (int)$p['id']; ?>"
                        >
                          Sửa
                        </a>
                        <a
                          class="btn btn-sm btn-danger"
                          href="?action=delete&id=<?php echo (int)$p['id']; ?>"
                          onclick="return confirm('Xóa sản phẩm này?');"
                        >
                          Xóa
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>

        <?php } ?>

        <?php if ($action === 'add' || $action === 'edit') { ?>

          <form
            method="post"
            action="?action=save"
            enctype="multipart/form-data"
          >
            <input
              type="hidden"
              name="id"
              value="<?php echo htmlspecialchars($form['id']); ?>"
            >
            <input
              type="hidden"
              name="old_image_path"
              value="<?php echo htmlspecialchars($form['image_path']); ?>"
            >

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">
                  Tên sản phẩm
                </label>
                <input
                  class="form-control"
                  name="name"
                  value="<?php echo htmlspecialchars($form['name']); ?>"
                >
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  Giá
                </label>
                <input
                  class="form-control"
                  name="price"
                  value="<?php echo htmlspecialchars($form['price']); ?>"
                  placeholder="VD: 120000"
                >
              </div>

              <div class="col-12">
                <label class="form-label">
                  Mô tả
                </label>
                <textarea
                  class="form-control"
                  name="description"
                  rows="4"
                ><?php echo htmlspecialchars($form['description']); ?></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">
                  Ảnh sản phẩm
                </label>

                <?php if ($action === 'edit' && $form['image_path'] !== '') { ?>
                  <div class="mb-2">
                    <img
                      src="<?php echo htmlspecialchars($form['image_path']); ?>"
                      alt="current"
                      style="width: 180px; height: 130px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;"
                    >
                    <div class="text-muted small mt-1">
                      Ảnh hiện tại (nếu không chọn ảnh mới thì giữ ảnh này)
                    </div>
                  </div>
                <?php } ?>

                <input
                  class="form-control"
                  type="file"
                  name="image"
                  accept="image/*"
                >
              </div>
            </div>

            <div class="mt-3 d-flex gap-2 flex-wrap">
              <button
                class="btn btn-primary"
                type="submit"
              >
                Lưu
              </button>
              <a
                class="btn btn-outline-secondary"
                href="?action=list"
              >
                Quay lại
              </a>
            </div>
          </form>

        <?php } ?>

      </div>
    </div>
  </div>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
<?php
mysqli_close($conn);
?>
