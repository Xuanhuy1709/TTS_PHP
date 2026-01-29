<?php
// comand 
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('DB error');
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {

    if (!isset($_GET['id'])) {
        $rs = mysqli_query($conn, "SELECT * FROM products");
        $data = array();

        while ($row = mysqli_fetch_assoc($rs)) {
            $data[] = $row;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }


    $id = (int)$_GET['id'];
    $rs = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $row = mysqli_fetch_assoc($rs);

    echo json_encode($row, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {

    $name  = $_POST['name'];
    $price = $_POST['price'];

    mysqli_query($conn,
        "INSERT INTO products(name,price)
         VALUES('$name','$price')"
    );

    echo json_encode(array('message' => 'Created'));
    exit;
}

if ($method === 'PUT') {

    parse_str(file_get_contents("php://input"), $data);

    $id    = (int)$data['id'];
    $name  = $data['name'];
    $price = $data['price'];

    mysqli_query($conn,
        "UPDATE products SET name='$name', price='$price' WHERE id=$id"
    );

    echo json_encode(array('message' => 'Updated'));
    exit;
}

if ($method === 'DELETE') {

    $id = (int)$_GET['id'];

    mysqli_query($conn,
        "DELETE FROM products WHERE id=$id"
    );

    echo json_encode(array('message' => 'Deleted'));
    exit;
}
