<?php

function connect()
{
    $mysqli = mysqli_connect('127.0.0.1:3306', 'root', '', "vk_test");
    if (mysqli_connect_errno()) {
        echo "Не удалось подключиться к MySQL: " . mysqli_connect_error();
    }

    return $mysqli;
}