<?php
$title = ($mode === 'create') ? 'Thêm sản phẩm' : 'Sửa sản phẩm';
$actionUrl = ($mode === 'create') ? 'url.php?page=product/store' : 'url.php?page=product/update';
?>

<h1><?php echo $title; ?></h1>

<div class="actions">
  <a class="btn" href="url.php?page=product/index">← Quay lại</a>
</div>

<form method="post" action="<?php echo $actionUrl; ?>">
  <input type="hidden" name="id" value="<?php echo $form['id']; ?>">

  <div class="grid">
    <div>
      <label>Tên sản phẩm</label>
      <input name="name"
             value="<?php echo $form['name']; ?>"
             class="<?php echo isset($errors['name']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['name'])) { ?>
        <div class="error"><?php echo $errors['name']; ?></div>
      <?php } ?>
    </div>

    <div>
      <label>Giá</label>
      <input name="price"
             value="<?php echo $form['price']; ?>"
             class="<?php echo isset($errors['price']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['price'])) { ?>
        <div class="error"><?php echo $errors['price']; ?></div>
      <?php } ?>
    </div>

    <div class="full">
      <label>Mô tả</label>
      <textarea name="description"><?php echo $form['description']; ?></textarea>
    </div>
  </div>

  <div class="actions" style="margin-top:14px;">
    <button class="btn btn-primary" type="submit">Lưu</button>
    <a class="btn" href="url.php?page=product/index">Hủy</a>
  </div>
</form>

