<?php
$a = '';
$b = '';
$op = '';
$result = null;
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $a = trim($_POST['a']);
    $b = trim($_POST['b']);
    if (isset($_POST['op'])) {
    $op = $_POST['op'];
} else {
    $op = '';
}


    if ($a === '' || !is_numeric($a)) {
        $errors['a'] = 'Vui lòng nhập số A hợp lệ.';
    }

    if ($b === '' || !is_numeric($b)) {
        $errors['b'] = 'Vui lòng nhập số B hợp lệ.';
    }

    if ($op === '') {
        $errors['op'] = 'Vui lòng chọn phép toán.';
    } else {
        if ($op !== '+' && $op !== '-' && $op !== '*' && $op !== '/') {
            $errors['op'] = 'Phép toán không hợp lệ.';
        }
    }

    if (empty($errors)) {
        $aNum = (float)$a;
        $bNum = (float)$b;

        if ($op === '/' && $bNum == 0) {
            $errors['b'] = 'Không thể chia cho 0.';
        } else {
            if ($op === '+') {
                $result = $aNum + $bNum;
            } elseif ($op === '-') {
                $result = $aNum - $bNum;
            } elseif ($op === '*') {
                $result = $aNum * $bNum;
            } elseif ($op === '/') {
                $result = $aNum / $bNum;
            }
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bài 1 - Calculator</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 24px;
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      color: #111;
    }

    .wrap {
      max-width: 900px;
      margin: 0 auto;
    }

    .card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 18px;
    }

    h1 {
      font-size: 18px;
      margin: 0 0 14px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }

    label {
      display: block;
      font-size: 13px;
      margin: 0 0 6px;
      color: #374151;
    }

    input,
    select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      font-size: 14px;
      outline: none;
    }

    .invalid {
      border-color: #ef4444 !important;
    }

    .error {
      margin-top: 6px;
      font-size: 13px;
      color: #ef4444;
    }

    .actions {
      margin-top: 14px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid transparent;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary {
      background: #2563eb;
      color: #fff;
    }

    .btn-outline {
      background: #fff;
      color: #111;
      border-color: #d1d5db;
    }

    hr {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 16px 0;
    }

    .result {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
      padding: 12px 14px;
      border-radius: 10px;
    }

    .formula {
      margin-top: 6px;
      font-size: 13px;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Bài 1 — Trang tính toán đơn giản</h1>

      <form method="post" >
        <div class="grid">
          <div>
            <label>Số A</label>
            <input
              name="a"
              value="<?php echo $a; ?>"
              class="<?php echo isset($errors['a']) ? 'invalid' : ''; ?>"
              placeholder="Nhập số A"
            >
            <?php if (isset($errors['a'])): ?>
              <div class="error"><?php echo $errors['a']; ?></div>
            <?php endif; ?>
          </div>

          <div>
            <label>Phép toán</label>
            <select name="op" class="<?php echo isset($errors['op']) ? 'invalid' : ''; ?>">
              <option value="" <?php echo ($op === '') ? 'selected' : ''; ?> disabled>
                -- Chọn phép toán --
              </option>

              <option value="+" <?php echo ($op === '+') ? 'selected' : ''; ?>>+</option>
              <option value="-" <?php echo ($op === '-') ? 'selected' : ''; ?>>-</option>
              <option value="*" <?php echo ($op === '*') ? 'selected' : ''; ?>>*</option>
              <option value="/" <?php echo ($op === '/') ? 'selected' : ''; ?>>/</option>
            </select>

            <?php if (isset($errors['op'])): ?>
              <div class="error"><?php echo $errors['op']; ?></div>
            <?php endif; ?>
          </div>

          <div>
            <label>Số B</label>
            <input
              name="b"
              value="<?php echo $b; ?>"
              class="<?php echo isset($errors['b']) ? 'invalid' : ''; ?>"
              placeholder="Nhập số B"
            >
            <?php if (isset($errors['b'])): ?>
              <div class="error"><?php echo $errors['b']; ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit">Tính</button>
          <a class="btn btn-outline" href="./">Reset</a>
        </div>
      </form>

      <?php if ($result !== null && empty($errors)): ?>
        <hr>
        <div class="result">
          Kết quả: <strong><?php echo $result; ?></strong>
          <div class="formula">
            <?php echo $a . ' ' . $op . ' ' . $b . ' = ' . $result; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
