<?php
$conn = mysqli_connect('mysql-72898-0.cloudclusters.net', 'admin', 'gmBTydEM', 'referal_bot', 19546);
if ($conn) {
    echo 'connect';
}

$query = mysqli_query($conn, "SELECT * FROM `users`");
$sql = mysqli_fetch_all($query);
var_dump($sql);
?>