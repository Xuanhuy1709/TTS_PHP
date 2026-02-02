<?php
class ProductModel
{
  public $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  public function all()
  {
    $rows = array();
    $rs = mysqli_query($this->conn, "SELECT * FROM products ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($rs)) {
      $rows[] = $row;
    }
    return $rows;
  }

  public function find($id)
  {
    $id = (int)$id;
    $rs = mysqli_query($this->conn, "SELECT * FROM products WHERE id=$id");
    return mysqli_fetch_assoc($rs);
  }

  public function create($name, $price, $description)
  {
    $name = mysqli_real_escape_string($this->conn, $name);
    $description = mysqli_real_escape_string($this->conn, $description);
    $price = (float)$price;

    $sql = "INSERT INTO products(name, price, description) VALUES('$name', $price, '$description')";
    return mysqli_query($this->conn, $sql);
  }

  public function update($id, $name, $price, $description)
  {
    $id = (int)$id;
    $name = mysqli_real_escape_string($this->conn, $name);
    $description = mysqli_real_escape_string($this->conn, $description);
    $price = (float)$price;

    $sql = "UPDATE products SET name='$name', price=$price, description='$description' WHERE id=$id";
    return mysqli_query($this->conn, $sql);
  }

  public function delete($id)
  {
    $id = (int)$id;
    return mysqli_query($this->conn, "DELETE FROM products WHERE id=$id");
  }
}
