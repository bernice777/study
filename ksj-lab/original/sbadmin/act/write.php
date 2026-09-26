<?php
    include("../init.php");

    $uIdx = $_SESSION['uIdx'];
    $title = $_POST['title'];
    $contents = $_POST['contents'];
    $upPath = "";
    $upName = "";
    $type = $_POST['type'];

    if (isset($_FILES['upload']) && !empty($_FILES['upload']['name'])){
        $upName = $_FILES['upload']['name'];
        $upPath = "../uploads/".$_FILES['upload']['name'];
        move_uploaded_file($_FILES['upload']['tmp_name'], $upPath);
    }

    $result = mysqli_query($db, "insert into {$type} (uIdx, title, contents, upPath, upName) values ('{$uIdx}', '{$title}', '{$contents}', '{$upPath}', '{$upName}')");

    die("<script>window.location.href='../';</script>");
