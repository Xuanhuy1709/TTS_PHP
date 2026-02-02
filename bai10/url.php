<?php
require_once __DIR__ . '/config/db.php';

$page = 'product/index';
if (isset($_GET['page']) && $_GET['page'] !== '') {
  $page = $_GET['page'];
}

// page = controller/action
$parts = explode('/', $page);
$controllerName = isset($parts[0]) ? $parts[0] : 'product';
$actionName = isset($parts[1]) ? $parts[1] : 'index';

// ProductController
if ($controllerName === 'product') {
  require_once __DIR__ . '/controllers/ProductController.php';
  $controller = new ProductController($conn);

  if (!method_exists($controller, $actionName)) {
    die('Action không tồn tại!');
  }

  $controller->$actionName();
} else {
  die('Controller không tồn tại!');
}

mysqli_close($conn);
