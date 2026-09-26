<?php
    include("../init.php");

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($db, "select * from users where email='{$email}' and password='{$password}'");

    $row = mysqli_fetch_assoc($result);

    if (isset($row['idx'])){
        $_SESSION['uIdx'] = $row['idx'];
        $_SESSION['email'] = $row['email'];
        die("<script>window.location.href='../';</script>");
    } else {
        die("<script>alert('login failed');history.back(-1);</script>");
    }
