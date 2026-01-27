<?php
// ====== CONNECT ======
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Kết nối DB thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// ====== ACTION ======
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
if ($action !== 'list' && $action !== 'add' && $action !== 'edit' && $action !== 'save' && $action !== 'delete') {
    $action = 'list';
}

// ====== STATE ======
$errors  = array();
$message = '';
$form = array(
    'id'    => '',
    'name'  => '',
    'email' => '',
    'score' => '',
    'dob'   => ''
);

// ====== DELETE ======
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM students WHERE id = $id");
    $message = 'Đã xóa sinh viên!';
    $action  = 'list';
}

// ====== EDIT (load data) ======
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $rs = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
    $row = mysqli_fetch_assoc($rs);

    if ($row) {
        $form['id']    = $row['id'];
        $form['name']  = $row['name'];
        $form['email'] = $row['email'];
        $form['score'] = $row['score'];
        $form['dob']   = $row['dob'];
    } else {
        $message = 'Không tìm thấy sinh viên!';
        $action  = 'list';
    }
}

// ====== SAVE (insert/update) ======
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['id']    = isset($_POST['id']) ? trim($_POST['id']) : '';
    $form['name']  = isset($_POST['name']) ? trim($_POST['name']) : '';
    $form['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';
    $form['score'] = isset($_POST['score']) ? trim($_POST['score']) : '';
    $form['dob']   = isset($_POST['dob']) ? trim($_POST['dob']) : '';

    // Validate
    if ($form['name'] === '') $errors['name'] = 'Vui lòng nhập họ tên.';

    if ($form['email'] === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } else if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    if ($form['score'] === '' || !is_numeric($form['score'])) {
        $errors['score'] = 'Vui lòng nhập điểm (số).';
    } else {
        $s = (float)$form['score'];
        if ($s < 0 || $s > 10) $errors['score'] = 'Điểm nên từ 0 đến 10.';
    }

    if ($form['dob'] === '') $errors['dob'] = 'Vui lòng chọn ngày sinh.';

    // Lưu DB
    if (empty($errors)) {
        $id = (int)$form['id'];

        $name  = mysqli_real_escape_string($conn, $form['name']);
        $email = mysqli_real_escape_string($conn, $form['email']);
        $score = (float)$form['score'];
        $dob   = mysqli_real_escape_string($conn, $form['dob']);

        if ($id === 0) {
            mysqli_query($conn, "INSERT INTO students (name,email,score,dob) VALUES ('$name','$email',$score,'$dob')");
            $message = 'Đã thêm sinh viên!';
        } else {
            mysqli_query($conn, "UPDATE students SET name='$name', email='$email', score=$score, dob='$dob' WHERE id=$id");
            $message = 'Đã cập nhật sinh viên!';
        }

        $action = 'list';
    } else {
        // Có lỗi -> quay lại form
        $action = ((int)$form['id'] === 0) ? 'add' : 'edit';
    }
}

// ====== LIST ======
$students = array();
if ($action === 'list') {
    $rs = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($rs)) {
        $students[] = $row;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1"
  >
  <title>Bài 5 - CRUD MySQL</title>
  <style>
    * { box-sizing: border-box; }
    body { margin:0; padding:24px; font-family:Arial,sans-serif; background:#f4f6f8; color:#111; }
    .wrap { max-width:950px; margin:0 auto; }
    .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; }
    h1 { margin:0 0 12px; font-size:18px; }
    a { color:inherit; text-decoration:none; }
    .btn { display:inline-block; padding:8px 12px; border-radius:10px; border:1px solid #d1d5db; background:#fff; font-size:14px; cursor:pointer; }
    .btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
    .btn-danger { background:#ef4444; border-color:#ef4444; color:#fff; }
    .msg { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:10px 12px; border-radius:10px; margin-bottom:12px; }

    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #e5e7eb; padding:10px; text-align:center; vertical-align:middle; }
    th { background:#f9fafb; }

    label { display:block; font-size:13px; margin:0 0 6px; color:#374151; }
    input { width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:10px; outline:none; }
    .invalid { border-color:#ef4444 !important; }
    .error { margin-top:6px; font-size:13px; color:#ef4444; }

    .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media (max-width:700px){ .grid{ grid-template-columns:1fr; } }
    .actions { margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Bài 5 — CRUD Sinh viên (MySQL)</h1>

      <?php if ($message !== '') { ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
      <?php } ?>

      <?php if ($action === 'list') { ?>
        <div class="actions">
          <a
            class="btn btn-primary"
            href="?action=add"
          >
            + Thêm sinh viên
          </a>
        </div>

        <div style="height:12px;"></div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Họ tên</th>
              <th>Email</th>
              <th>Điểm</th>
              <th>Ngày sinh</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students)) { ?>
              <tr>
                <td colspan="6">Chưa có sinh viên.</td>
              </tr>
            <?php } else { ?>
              <?php foreach ($students as $st) { ?>
                <tr>
                  <td><?php echo (int)$st['id']; ?></td>
                  <td><?php echo htmlspecialchars($st['name']); ?></td>
                  <td><?php echo htmlspecialchars($st['email']); ?></td>
                  <td><?php echo htmlspecialchars($st['score']); ?></td>
                  <td><?php echo htmlspecialchars($st['dob']); ?></td>
                  <td>
                    <a
                      class="btn"
                      href="?action=edit&id=<?php echo (int)$st['id']; ?>"
                    >
                      Sửa
                    </a>
                    <a
                      class="btn btn-danger"
                      href="?action=delete&id=<?php echo (int)$st['id']; ?>"
                      onclick="return confirm('Xóa sinh viên này?');"
                    >
                      Xóa
                    </a>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
      <?php } ?>

      <?php if ($action === 'add' || $action === 'edit') { ?>
        <form
          method="post"
          action="?action=save"
        >
          <input
            type="hidden"
            name="id"
            value="<?php echo htmlspecialchars($form['id']); ?>"
          >

          <div class="grid">
            <div>
              <label>Họ tên</label>
              <input
                name="name"
                value="<?php echo htmlspecialchars($form['name']); ?>"
                class="<?php echo isset($errors['name']) ? 'invalid' : ''; ?>"
              >
              <?php if (isset($errors['name'])) { ?>
                <div class="error"><?php echo $errors['name']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Email</label>
              <input
                name="email"
                value="<?php echo htmlspecialchars($form['email']); ?>"
                class="<?php echo isset($errors['email']) ? 'invalid' : ''; ?>"
              >
              <?php if (isset($errors['email'])) { ?>
                <div class="error"><?php echo $errors['email']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Điểm (0 - 10)</label>
              <input
                name="score"
                value="<?php echo htmlspecialchars($form['score']); ?>"
                class="<?php echo isset($errors['score']) ? 'invalid' : ''; ?>"
              >
              <?php if (isset($errors['score'])) { ?>
                <div class="error"><?php echo $errors['score']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Ngày sinh</label>
              <input
                type="date"
                name="dob"
                value="<?php echo htmlspecialchars($form['dob']); ?>"
                class="<?php echo isset($errors['dob']) ? 'invalid' : ''; ?>"
              >
              <?php if (isset($errors['dob'])) { ?>
                <div class="error"><?php echo $errors['dob']; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="actions">
            <button
              class="btn btn-primary"
              type="submit"
            >
              Lưu
            </button>
            <a
              class="btn"
              href="?action=list"
            >
              Quay lại
            </a>
          </div>
        </form>
      <?php } ?>

    </div>
  </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
