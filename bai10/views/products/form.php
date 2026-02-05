<?php
$title = ($mode === 'create') ? 'Thêm sản phẩm' : 'Sửa sản phẩm';
$actionUrl = ($mode === 'create') ? 'url.php?page=product/store' : 'url.php?page=product/update';

function h($str)
{
  return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$isEdit = ($mode === 'edit');
$currentImage = isset($form['image_path']) ? $form['image_path'] : '';
?>

<h1><?php echo h($title); ?></h1>

<div class="actions">
  <a class="btn" href="url.php?page=product/index">← Quay lại</a>
</div>

<form method="post" action="<?php echo h($actionUrl); ?>" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?php echo h($form['id']); ?>">
  <input type="hidden" name="old_image_path" value="<?php echo h($currentImage); ?>">

  <div class="grid">
    <div>
      <label>Tên sản phẩm</label>
      <input name="name" value="<?php echo h($form['name']); ?>"
             class="<?php echo isset($errors['name']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['name'])) { ?>
        <div class="error"><?php echo h($errors['name']); ?></div>
      <?php } ?>
    </div>

    <div>
      <label>Giá</label>
      <input name="price" value="<?php echo h($form['price']); ?>"
             class="<?php echo isset($errors['price']) ? 'invalid' : ''; ?>">
      <?php if (isset($errors['price'])) { ?>
        <div class="error"><?php echo h($errors['price']); ?></div>
      <?php } ?>
    </div>

    <div class="full">
      <label>Mô tả</label>
      <textarea name="description"><?php echo h($form['description']); ?></textarea>
    </div>

    <div class="full">
      <label>Ảnh</label>

      <?php if ($isEdit && $currentImage !== '') { ?>
        <div style="margin:8px 0 10px;">
          <img src="../<?php echo h($currentImage); ?>" alt="product"
               style="width:180px;height:130px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;">
        </div>
      <?php } ?>

      <input type="file" name="image" accept="image/*"
             class="<?php echo isset($errors['image']) ? 'invalid' : ''; ?>">

      <?php if (isset($errors['image'])) { ?>
        <div class="error"><?php echo h($errors['image']); ?></div>
      <?php } ?>
    </div>
  </div>

  <div class="actions" style="margin-top:14px;">
    <button class="btn btn-primary" type="submit">Lưu</button>
    <a class="btn" href="url.php?page=product/index">Hủy</a>
  </div>
</form>
