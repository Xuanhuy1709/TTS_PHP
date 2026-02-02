<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('DB error: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

$type = 'day';
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'month') {
        $type = 'month';
    }
}

$date_from = '';
$date_to = '';

if (isset($_GET['date_from'])) $date_from = trim($_GET['date_from']);
if (isset($_GET['date_to']))   $date_to   = trim($_GET['date_to']);

if ($date_from === '' && $date_to === '') {
    $date_to = date('Y-m-d');
    $date_from = date('Y-m-d', strtotime('-30 days'));
}

$where = "WHERE 1=1";
if ($date_from !== '') $where .= " AND DATE(o.created_at) >= '$date_from'";
if ($date_to !== '')   $where .= " AND DATE(o.created_at) <= '$date_to'";

$groupSelect = "DATE(o.created_at)";
$groupBy = "DATE(o.created_at)";
$orderBy = "DATE(o.created_at) ASC";
$titleType = "Theo ngày";

if ($type === 'month') {
    $groupSelect = "DATE_FORMAT(o.created_at, '%Y-%m')";
    $groupBy = "DATE_FORMAT(o.created_at, '%Y-%m')";
    $orderBy = "DATE_FORMAT(o.created_at, '%Y-%m') ASC";
    $titleType = "Theo tháng";
}

$sql = "
    SELECT
        $groupSelect AS period,
        SUM(oi.qty * oi.price) AS revenue
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    $where
    GROUP BY $groupBy
    ORDER BY $orderBy
";

$rs = mysqli_query($conn, $sql);

$data = array();
$total = 0;

while ($row = mysqli_fetch_assoc($rs)) {
    $row['revenue'] = (float)$row['revenue'];
    $total += $row['revenue'];
    $data[] = $row;
}

$labels = array();
$values = array();

for ($i = 0; $i < count($data); $i++) {
    $labels[] = $data[$i]['period'];
    $values[] = $data[$i]['revenue'];
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bài 9 - Báo cáo doanh thu</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="wrap">

    <div class="card">
      <h1>Bài 9 — Báo cáo doanh thu (<?php echo $titleType; ?>)</h1>

      <form method="get" class="row">
        <div>
          <label>Loại báo cáo</label>
          <select name="type">
            <option value="day" <?php echo ($type === 'day') ? 'selected' : ''; ?>>Theo ngày</option>
            <option value="month" <?php echo ($type === 'month') ? 'selected' : ''; ?>>Theo tháng</option>
          </select>
        </div>

        <div>
          <label>Từ ngày</label>
          <input type="date" name="date_from" value="<?php echo $date_from; ?>">
        </div>

        <div>
          <label>Đến ngày</label>
          <input type="date" name="date_to" value="<?php echo $date_to; ?>">
        </div>

        <div>
          <button class="btn" type="submit">Xem báo cáo</button>
          <a class="btn-outline" href="index.php">Reset</a>
        </div>
      </form>
    </div>

    <div class="grid">
      <div class="card">
        <h1>Bảng doanh thu</h1>
        <table>
          <thead>
            <tr>
              <th><?php echo ($type === 'day') ? 'Ngày' : 'Tháng'; ?></th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data)) { ?>
              <tr><td colspan="2">Không có dữ liệu.</td></tr>
            <?php } else { ?>
              <?php foreach ($data as $r) { ?>
                <tr>
                  <td><?php echo $r['period']; ?></td>
                  <td class="right"><?php echo number_format($r['revenue']); ?> đ</td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <th class="right">Tổng</th>
              <th class="right"><?php echo number_format($total); ?> đ</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="card">
        <h1>Biểu đồ doanh thu</h1>
        <canvas id="revChart"></canvas>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    var labels = <?php echo json_encode($labels); ?>;
    var values = <?php echo json_encode($values); ?>;

    var ctx = document.getElementById('revChart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Doanh thu',
          data: values
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>
<?php mysqli_close($conn); ?>
