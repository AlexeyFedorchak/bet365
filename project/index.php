<?php
$conn = mysqli_connect('db', 'user', '123', 'test_db');

if ($conn->connect_error) {
    echo 'Error!!';
}

echo 'Connected</br>';
echo '<h1>Hi from first docker project!</h1>';