<?php
    include("../init.php");

    $uIdx = $_SESSION['uIdx'] ?? 0;
    $title = $_POST['title'] ?? '';
    $contents = $_POST['contents'] ?? '';
    $upPath = "";
    $upName = "";
    $type = $_POST['type'] ?? 'freeboard';

    if (isset($_FILES['upload']) && !empty($_FILES['upload']['name'])){
        // [LAB-MOD] 파일 업로드는 SQLI 범위 밖(웹셸 위험) → SQLI 모드에서 차단.
        //           원본 업로드 코드는 original/ 에 보존.
        lab_scope_deny('upload');
        $upName = $_FILES['upload']['name'];
        $upPath = "../uploads/".$_FILES['upload']['name'];
        move_uploaded_file($_FILES['upload']['tmp_name'], $upPath);
    }

    // [LAB] 취약 문자열 결합(테이블명 $type + 값) 유지.
    $sql = "insert into {$type} (uIdx, title, contents, upPath, upName) values ('{$uIdx}', '{$title}', '{$contents}', '{$upPath}', '{$upName}')";
    $result = lab_query($db, $sql, __FILE__, __LINE__, ['note'=>'ksj board write (table+value inj)']);

    die("<script>window.location.href='../';</script>");
