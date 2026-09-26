<?php
    include("../init.php");

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // [LAB] 취약 문자열 결합 유지. lab_query 로 최종 SQL/결과만 기록.
    $sql = "select * from users where email='{$email}' and password='{$password}'";
    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'1','note'=>'ksj login (string-based)']);

    // [LAB-MOD] PHP8 에서 fetch(false) 는 TypeError 이므로 가드 (에러 자체는 Observer 로 관찰)
    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;

    if (isset($row['idx'])){
        $_SESSION['uIdx'] = $row['idx'];
        $_SESSION['email'] = $row['email'];
        die("<script>window.location.href='../';</script>");
    } else {
        die("<script>alert('login failed');history.back(-1);</script>");
    }
