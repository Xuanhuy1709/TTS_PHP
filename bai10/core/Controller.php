<?php
class Controller
{
  public function view($viewPath, $data = array())
  {
    // $data -> biến trong view
    foreach ($data as $k => $v) {
      $$k = $v;
    }

    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/' . $viewPath . '.php';
    include __DIR__ . '/../views/layout/footer.php';
  }

  public function redirect($url)
  {
    header('Location: ' . $url);
    exit;
  }
}
