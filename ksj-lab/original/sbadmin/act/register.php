<?php
    include("../init.php");

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($db, "insert into users (email, password) values ('{$email}', '{$password}')");

    die("<script>alert('success');window.location.href='../';</script>");
