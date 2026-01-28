<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die(mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

$mode = 'login';
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
}

if (isset($_SESSION['user'])) {
    if ($mode !== 'logout') {
        $mode = 'home';
    }
}

$message = '';
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}
$errors = array();

if ($mode === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php?mode=login&msg=Bạn đã đăng xuất');
    exit;
}

$name = '';
$email = '';

if ($mode === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['name']))  $name  = trim($_POST['name']);
        if (isset($_POST['email'])) $email = trim($_POST['email']);

        $password = '';
        $confirm  = '';

        if (isset($_POST['password'])) $password = $_POST['password'];
        if (isset($_POST['confirm']))  $confirm  = $_POST['confirm'];

        if ($name === '') {
            $errors['name'] = 'Nhập họ tên.';
        }

        if ($email === '') {
            $errors['email'] = 'Nhập email.';
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email sai.';
            }
        }

        if ($password === '') {
            $errors['password'] = 'Nhập mật khẩu.';
        } else {
            if (strlen($password) < 6) {
                $errors['password'] = 'Mật khẩu >= 6 ký tự.';
            }
        }

        if ($confirm === '') {
            $errors['confirm'] = 'Nhập lại mật khẩu.';
        } else {
            if ($confirm !== $password) {
                $errors['confirm'] = 'Mật khẩu không khớp.';
            }
        }

        if (empty($errors)) {
            $rs = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
            $exists = mysqli_fetch_assoc($rs);

            if ($exists) {
                $errors['email'] = 'Email đã tồn tại.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                mysqli_query($conn,
                    "INSERT INTO users(name,email,password_hash)
                     VALUES('$name','$email','$hash')"
                );

                header('Location: index.php?mode=login&msg=Đăng ký thành công! Hãy đăng nhập.');
                exit;
            }
        }
    }
}

$loginEmail = '';

if ($mode === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $loginEmail = '';
        $loginPass  = '';

        if (isset($_POST['email']))    $loginEmail = trim($_POST['email']);
        if (isset($_POST['password'])) $loginPass  = $_POST['password'];

        if ($loginEmail === '' || $loginPass === '') {
            $errors['login'] = 'Nhập email và mật khẩu.';
        } else {
            $rs = mysqli_query($conn, "SELECT * FROM users WHERE email='$loginEmail' LIMIT 1");
            $row = mysqli_fetch_assoc($rs);

            if (!$row) {
                $errors['login'] = 'Sai email hoặc mật khẩu.';
            } else {
                if (password_verify($loginPass, $row['password_hash'])) {

                    $_SESSION['user'] = array(
                        'id'    => $row['id'],
                        'name'  => $row['name'],
                        'email' => $row['email']
                    );

                    header('Location: index.php');
                    exit;

                } else {
                    $errors['login'] = 'Sai email hoặc mật khẩu.';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 6</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="actions">
        <a
          class="btn <?php if ($mode === 'login') { echo 'btn-primary'; } ?>"
          href="?mode=login"
        >
          Đăng nhập
        </a>

        <a
          class="btn <?php if ($mode === 'register') { echo 'btn-primary'; } ?>"
          href="?mode=register"
        >
          Đăng ký
        </a>

        <?php if (isset($_SESSION['user'])) { ?>
          <a class="btn btn-danger" href="?mode=logout">Đăng xuất</a>
        <?php } ?>
      </div>

      <?php if ($message !== '') { ?>
        <div class="msg"><?php echo $message; ?></div>
      <?php } ?>

      <?php if ($mode === 'home') { ?>
        <p>Xin chào: <strong><?php echo $_SESSION['user']['name']; ?></strong></p>
        <p>Email: <?php echo $_SESSION['user']['email']; ?></p>
      <?php } ?>

      <?php if ($mode === 'login') { ?>
        <?php if (isset($errors['login'])) { ?>
          <div class="error"><?php echo $errors['login']; ?></div>
        <?php } ?>

        <form method="post" action="?mode=login">
          <label>Email</label>
          <input name="email" value="<?php echo $loginEmail; ?>" placeholder="VD: abc@gmail.com">

          <label>Mật khẩu</label>
          <input type="password" name="password" placeholder="Nhập mật khẩu">

          <button class="btn btn-primary" type="submit">Đăng nhập</button>
        </form>
      <?php } ?>

      <?php if ($mode === 'register') { ?>
        <form method="post" action="?mode=register">
          <label>Họ tên</label>
          <input
            name="name"
            value="<?php echo $name; ?>"
            class="<?php if (isset($errors['name'])) { echo 'invalid'; } ?>"
          >
          <?php if (isset($errors['name'])) { ?>
            <div class="error"><?php echo $errors['name']; ?></div>
          <?php } ?>

          <label>Email</label>
          <input
            name="email"
            value="<?php echo $email; ?>"
            class="<?php if (isset($errors['email'])) { echo 'invalid'; } ?>"
          >
          <?php if (isset($errors['email'])) { ?>
            <div class="error"><?php echo $errors['email']; ?></div>
          <?php } ?>

          <label>Mật khẩu</label>
          <input
            type="password"
            name="password"
            class="<?php if (isset($errors['password'])) { echo 'invalid'; } ?>"
          >
          <?php if (isset($errors['password'])) { ?>
            <div class="error"><?php echo $errors['password']; ?></div>
          <?php } ?>

          <label>Nhập lại mật khẩu</label>
          <input
            type="password"
            name="confirm"
            class="<?php if (isset($errors['confirm'])) { echo 'invalid'; } ?>"
          >
          <?php if (isset($errors['confirm'])) { ?>
            <div class="error"><?php echo $errors['confirm']; ?></div>
          <?php } ?>

          <button class="btn btn-primary" type="submit">Đăng ký</button>
        </form>
      <?php } ?>

    </div>
  </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
