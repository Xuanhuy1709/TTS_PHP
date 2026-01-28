<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4');

$action  = $_GET['action'] ?? 'list';
$errors  = array();
$message = '';

$form = array(
  'id'    => '',
  'name'  => '',
  'email' => '',
  'score' => '',
  'dob'   => ''
);

if ($action === 'delete' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  mysqli_query($conn, "DELETE FROM students WHERE id=$id");
  $message = 'Đã xóa sinh viên!';
  $action = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $rs = mysqli_query($conn, "SELECT * FROM students WHERE id=$id");
  $row = mysqli_fetch_assoc($rs);
  if ($row) {
    $form = $row;
  } else {
    $message = 'Không tìm thấy sinh viên!';
    $action = 'list';
  }
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['id']    = trim($_POST['id'] ?? '');
  $form['name']  = trim($_POST['name'] ?? '');
  $form['email'] = trim($_POST['email'] ?? '');
  $form['score'] = trim($_POST['score'] ?? '');
  $form['dob']   = trim($_POST['dob'] ?? '');

  if ($form['name'] === '') $errors['name'] = 'Nhập họ tên.';
  if ($form['email'] === '') $errors['email'] = 'Nhập email.';
  else if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email sai.';

  if ($form['score'] === '' || !is_numeric($form['score'])) $errors['score'] = 'Nhập điểm (số).';
  else {
    $s = (float)$form['score'];
    if ($s < 0 || $s > 10) $errors['score'] = 'Điểm 0 - 10.';
  }

  if ($form['dob'] === '') $errors['dob'] = 'Chọn ngày sinh.';

  if (empty($errors)) {
    $id    = (int)$form['id'];
    $name  = $form['name'];
    $email = $form['email'];
    $score = (float)$form['score'];
    $dob   = $form['dob'];

    if ($id === 0) {
      mysqli_query($conn, "INSERT INTO students(name,email,score,dob) VALUES('$name','$email',$score,'$dob')");
      $message = 'Đã thêm sinh viên!';
    } else {
      mysqli_query($conn, "UPDATE students SET name='$name', email='$email', score=$score, dob='$dob' WHERE id=$id");
      $message = 'Đã cập nhật sinh viên!';
    }

    $action = 'list';
  } else {
    $action = ((int)$form['id'] === 0) ? 'add' : 'edit';
  }
}

$students = array();
if ($action === 'list') {
  $rs = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
  while ($row = mysqli_fetch_assoc($rs)) $students[] = $row;
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRUD Sinh viên (MySQL)</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>CRUD Sinh viên (MySQL)</h1>

      <?php if ($message !== '') { ?>
        <div class="msg"><?php echo $message; ?></div>
      <?php } ?>

      <?php if ($action === 'list') { ?>
        <div class="actions">
          <a class="btn btn-primary" href="?action=add">+ Thêm sinh viên</a>
        </div>

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
              <tr><td colspan="6">Chưa có sinh viên.</td></tr>
            <?php } else { ?>
              <?php foreach ($students as $st) { ?>
                <tr>
                  <td><?php echo (int)$st['id']; ?></td>
                  <td><?php echo $st['name']; ?></td>
                  <td><?php echo $st['email']; ?></td>
                  <td><?php echo $st['score']; ?></td>
                  <td><?php echo $st['dob']; ?></td>
                  <td>
                    <a class="btn" href="?action=edit&id=<?php echo (int)$st['id']; ?>">Sửa</a>
                    <a class="btn btn-danger"
                       href="?action=delete&id=<?php echo (int)$st['id']; ?>"
                       onclick="return confirm('Xóa sinh viên này?');">Xóa</a>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>

      <?php } else { ?>

        <form method="post" action="?action=save">
          <input type="hidden" name="id" value="<?php echo $form['id']; ?>">

          <div class="grid">
            <div>
              <label>Họ tên</label>
              <input name="name"
                     value="<?php echo $form['name']; ?>"
                     class="<?php echo isset($errors['name']) ? 'invalid' : ''; ?>">
              <?php if (isset($errors['name'])) { ?>
                <div class="error"><?php echo $errors['name']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Email</label>
              <input name="email"
                     value="<?php echo $form['email']; ?>"
                     class="<?php echo isset($errors['email']) ? 'invalid' : ''; ?>">
              <?php if (isset($errors['email'])) { ?>
                <div class="error"><?php echo $errors['email']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Điểm (0 - 10)</label>
              <input name="score"
                     value="<?php echo $form['score']; ?>"
                     class="<?php echo isset($errors['score']) ? 'invalid' : ''; ?>">
              <?php if (isset($errors['score'])) { ?>
                <div class="error"><?php echo $errors['score']; ?></div>
              <?php } ?>
            </div>

            <div>
              <label>Ngày sinh</label>
              <input type="date"
                     name="dob"
                     value="<?php echo $form['dob']; ?>"
                     class="<?php echo isset($errors['dob']) ? 'invalid' : ''; ?>">
              <?php if (isset($errors['dob'])) { ?>
                <div class="error"><?php echo $errors['dob']; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="actions">
            <button class="btn btn-primary" type="submit">Lưu</button>
            <a class="btn" href="?action=list">Quay lại</a>
          </div>
        </form>

      <?php } ?>
    </div>
  </div>
</body>
</html>
<?php mysqli_close($conn); ?>
