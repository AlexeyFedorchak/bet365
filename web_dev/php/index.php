<?php
$conn = mysqli_connect('db', 'user', '123', 'test_db');

if ($conn->connect_error) {
    echo 'Error!!';
}

echo 'Connected';