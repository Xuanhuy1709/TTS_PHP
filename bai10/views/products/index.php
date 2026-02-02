<h1>Bài 10 — CRUD Sản phẩm (MVC)</h1>

<div class="actions">
  <a class="btn btn-primary" href="url.php?page=product/create">+ Thêm sản phẩm</a>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Tên</th>
      <th>Giá</th>
      <th>Mô tả</th>
      <th>Hành động</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($products)) { ?>
      <tr><td colspan="5">Chưa có sản phẩm.</td></tr>
    <?php } else { ?>
      <?php foreach ($products as $p) { ?>
        <tr>
          <td><?php echo (int)$p['id']; ?></td>
          <td><?php echo $p['name']; ?></td>
          <td><?php echo number_format((float)$p['price']); ?> đ</td>
          <td style="text-align:left"><?php echo nl2br($p['description']); ?></td>
          <td>
            <a class="btn" href="url.php?page=product/edit&id=<?php echo (int)$p['id']; ?>">Sửa</a>
            <a class="btn btn-danger"
               href="url.php?page=product/delete&id=<?php echo (int)$p['id']; ?>"
               onclick="return confirm('Xóa sản phẩm này?');">
               Xóa
            </a>
          </td>
        </tr>
      <?php } ?>
    <?php } ?>
  </tbody>
</table>
