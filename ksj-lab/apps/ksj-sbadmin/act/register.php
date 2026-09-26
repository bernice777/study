<?php
    include("../init.php");

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // [LAB] 취약 INSERT 문자열 결합 유지.
    $sql = "insert into users (email, password) values ('{$email}', '{$password}')";
    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'insert','note'=>'ksj register insert SQLi']);

    die("<script>alert('success');window.location.href='../';</script>");
