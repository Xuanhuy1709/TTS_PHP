<?php
$errors = array();
$success = false;
$imageUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['image'])) {
        $errors[] = 'Bạn chưa chọn ảnh.';
    } else {

        $file = $_FILES['image'];

        if ($file['error'] != 0) {
            $errors[] = 'Upload lỗi. Mã lỗi: ' . $file['error'];
        } else {

            $fileName = $file['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

            if (!in_array($ext, $allowed)) {
                $errors[] = 'Chỉ cho phép: jpg, jpeg, png, gif, webp.';
            }

            if (empty($errors)) {

                $uploadDir = __DIR__ . '/uploads';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newName = time() . '_' . $fileName;
 
                $destPath = $uploadDir . '/' . $newName;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $success = true;
                    $imageUrl = 'uploads/' . $newName;
                } else {
                    $errors[] = 'Không thể lưu file. Kiểm tra quyền ghi thư mục uploads/.';
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
  <title>Bài 3 - Upload ảnh</title>
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

    input[type="file"] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      background: #fff;
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

    .box-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
      padding: 12px 14px;
      border-radius: 10px;
      margin-bottom: 14px;
    }

    .box-success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
      padding: 12px 14px;
      border-radius: 10px;
      margin-bottom: 14px;
    }

    img {
      margin-top: 14px;
      max-width: 100%;
      border-radius: 10px;
      display: block;
      border: 1px solid #e5e7eb;
    }

    .note {
      margin-top: 8px;
      color: #6b7280;
      font-size: 13px;
      word-break: break-all;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Bài 3 — Upload ảnh và hiển thị</h1>

      <?php if (!empty($errors)) { ?>
        <div class="box-error">
          <strong>Có lỗi:</strong>
          <ul>
            <?php
            foreach ($errors as $er) {
                echo '<li>' . $er . '</li>';
            }
            ?>
          </ul>
        </div>
      <?php } ?>

      <?php if ($success) { ?>
        <div class="box-success">
          Upload thành công!
        </div>
      <?php } ?>

      <form method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>

        <div class="actions">
          <button class="btn btn-primary" type="submit">Upload</button>
          <a class="btn btn-outline" href="./">Reset</a>
        </div>
      </form>

      <?php if ($success) { ?>
        <img src="<?php echo $imageUrl; ?>" alt="Ảnh đã upload">
        <div class="note">File: <?php echo $imageUrl; ?></div>
      <?php } ?>
    </div>
  </div>
</body>
</html>
