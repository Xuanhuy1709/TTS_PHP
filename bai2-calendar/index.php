<?php
$month = '';
$year  = '';
$errors = array();

$daysInMonth = 0;
$firstWeekday = 0; 
$showCalendar = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = trim($_POST['month']);
    $year  = trim($_POST['year']);
    if ($month === '' || !ctype_digit($month)) {
        $errors['month'] = 'Vui lòng nhập tháng bằng số (1-12).';
    }

    if ($year === '' || !ctype_digit($year)) {
        $errors['year'] = 'Vui lòng nhập năm bằng số (VD: 2026).';
    }

    if (empty($errors)) {
        $m = (int)$month;
        $y = (int)$year;

        if ($m < 1 || $m > 12) {
            $errors['month'] = 'Tháng phải từ 1 đến 12.';
        }

        if ($y < 1900 || $y > 2100) {
            $errors['year'] = 'Năm nên trong khoảng 1900 - 2100.';
        }
        if (empty($errors)) {
            $daysInMonth = (int)date('t', strtotime($y . '-' . $m . '-01'));

            $firstWeekday = (int)date('w', strtotime($y . '-' . $m . '-01'));

            $showCalendar = true;
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 2 - Calendar</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 24px;
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      color: #111;
    }
    .wrap { max-width: 900px; margin: 0 auto; }
    .card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 18px;
    }
    h1 { font-size: 18px; margin: 0 0 14px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
    }
    @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
    label { display: block; font-size: 13px; margin: 0 0 6px; color: #374151; }
    input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      font-size: 14px;
      outline: none;
    }
    .invalid { border-color: #ef4444 !important; }
    .error { margin-top: 6px; font-size: 13px; color: #ef4444; }
    .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid transparent;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
    }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-outline { background: #fff; color: #111; border-color: #d1d5db; }

    hr { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }

    table { width: 100%; border-collapse: collapse; }
    th, td {
      border: 1px solid #e5e7eb;
      padding: 10px;
      text-align: center;
      height: 44px;
    }
    th { background: #f9fafb; color: #374151; font-size: 13px; }
    td.empty { background: #fafafa; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Bài 2 — Hiển thị lịch theo tháng và năm (cơ bản)</h1>

      <form method="post">
        <div class="grid">
          <div>
            <label>Tháng (1-12)</label>
            <input
              name="month"
              value="<?php echo $month; ?>"
              class="<?php echo isset($errors['month']) ? 'invalid' : ''; ?>"
              placeholder="VD: 1"
            >
            <?php if (isset($errors['month'])): ?>
              <div class="error"><?php echo $errors['month']; ?></div>
            <?php endif; ?>
          </div>

          <div>
            <label>Năm</label>
            <input
              name="year"
              value="<?php echo $year; ?>"
              class="<?php echo isset($errors['year']) ? 'invalid' : ''; ?>"
              placeholder="VD: 2026"
            >
            <?php if (isset($errors['year'])): ?>
              <div class="error"><?php echo $errors['year']; ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit">Xem lịch</button>
          <a class="btn btn-outline" href="./">Reset</a>
        </div>
      </form>

      <?php if ($showCalendar): ?>
        <hr>

        <div style="font-weight:700; margin-bottom:10px;">
          Lịch tháng <?php echo (int)$month; ?> / <?php echo (int)$year; ?>
        </div>

        <table>
          <thead>
            <tr>
              <th>CN</th>
              <th>T2</th>
              <th>T3</th>
              <th>T4</th>
              <th>T5</th>
              <th>T6</th>
              <th>T7</th>
            </tr>
          </thead>
          <tbody>
            <?php

              $cell = 0; 

              echo '<tr>';


              for ($i = 0; $i < $firstWeekday; $i++) {
                  echo '<td class="empty"></td>';
                  $cell++;
              }

              for ($day = 1; $day <= $daysInMonth; $day++) {
                  echo '<td>' . $day . '</td>';
                  $cell++;


                  if ($cell % 7 === 0 && $day < $daysInMonth) {
                      echo '</tr><tr>';
                  }
              }

              while ($cell % 7 !== 0) {
                  echo '<td class="empty"></td>';
                  $cell++;
              }

              echo '</tr>';
            ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
