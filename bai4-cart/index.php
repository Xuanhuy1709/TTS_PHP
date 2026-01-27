<?php
session_start();

$products = array(
    1 => array(
        'name' => 'Áo thun basic',
        'price' => 120000
    ),
    2 => array(
        'name' => 'Quần jean ống đứng',
        'price' => 350000
    ),
    3 => array(
        'name' => 'Áo khoác bomber',
        'price' => 520000
    )
);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
$message = '';

$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

if ($action === 'add') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        if (isset($products[$id])) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] = $_SESSION['cart'][$id] + 1;
            } else {
                $_SESSION['cart'][$id] = 1;
            }
            $message = 'Đã thêm sản phẩm vào giỏ!';
        } else {
            $message = 'Sản phẩm không tồn tại!';
        }
    }
}

if ($action === 'remove') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            $message = 'Đã xóa sản phẩm khỏi giỏ!';
        }
    }
}

if ($action === 'clear') {
    $_SESSION['cart'] = array();
    $message = 'Đã xóa toàn bộ giỏ hàng!';
}

if ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $id => $qty) {
                $id = (int)$id;
                $qty = (int)$qty;

                if ($qty <= 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    $_SESSION['cart'][$id] = $qty;
                }
            }
            $message = 'Đã cập nhật giỏ hàng!';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 4 - Giỏ hàng đơn giản</title>
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
      margin-bottom: 16px;
    }

    h1 {
      font-size: 18px;
      margin: 0 0 12px;
    }

    .msg {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
      padding: 10px 12px;
      border-radius: 10px;
      margin-bottom: 12px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      border: 1px solid #e5e7eb;
      padding: 10px;
      text-align: center;
    }

    th {
      background: #f9fafb;
    }

    .btn {
      display: inline-block;
      padding: 8px 12px;
      border-radius: 10px;
      text-decoration: none;
      border: 1px solid #d1d5db;
      color: #111;
      background: #fff;
      cursor: pointer;
      font-size: 14px;
    }

    .btn-primary {
      background: #2563eb;
      border-color: #2563eb;
      color: #fff;
    }

    .btn-danger {
      background: #ef4444;
      border-color: #ef4444;
      color: #fff;
    }

    input[type="number"] {
      width: 80px;
      padding: 6px 8px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
    }

    .actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 12px;
    }
  </style>
</head>
<body>
  <div class="wrap">

    <div class="card">
      <h1>Bài 4 — Danh sách sản phẩm</h1>

      <?php if ($message !== '') { ?>
        <div class="msg"><?php echo $message; ?></div>
      <?php } ?>

      <table>
        <thead>
          <tr>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $id => $p) { ?>
            <tr>
              <td><?php echo $p['name']; ?></td>
              <td><?php echo number_format($p['price']); ?> đ</td>
              <td>
                <a class="btn btn-primary" href="?action=add&id=<?php echo $id; ?>">
                  Thêm vào giỏ
                </a>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h1>Giỏ hàng</h1>

      <?php if (empty($_SESSION['cart'])) { ?>
        <p>Giỏ hàng đang trống.</p>
      <?php } else { ?>

        <form method="post" action="?action=update">
          <table>
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Xóa</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $total = 0;

              foreach ($_SESSION['cart'] as $id => $qty) {
                  $id = (int)$id;
                  $qty = (int)$qty;

                  if (!isset($products[$id])) {
                      continue;
                  }

                  $name = $products[$id]['name'];
                  $price = $products[$id]['price'];
                  $sub = $price * $qty;
                  $total += $sub;
              ?>
                <tr>
                  <td><?php echo $name; ?></td>
                  <td><?php echo number_format($price); ?> đ</td>
                  <td>
                    <input
                      type="number"
                      name="qty[<?php echo $id; ?>]"
                      value="<?php echo $qty; ?>"
                      min="0"
                    >
                  </td>
                  <td><?php echo number_format($sub); ?> đ</td>
                  <td>
                    <a class="btn btn-danger" href="?action=remove&id=<?php echo $id; ?>">
                      Xóa
                    </a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>

          <div class="actions">
            <button class="btn btn-primary" type="submit">Cập nhật giỏ</button>
            <a class="btn btn-danger" href="?action=clear">Xóa hết giỏ</a>
          </div>

          <h3>Tổng tiền: <?php echo number_format($total); ?> đ</h3>
        </form>

      <?php } ?>
    </div>

  </div>
</body>
</html>
