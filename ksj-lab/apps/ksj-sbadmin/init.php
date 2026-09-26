<?php
    // [LAB-MOD] 원본: $db = mysqli_connect("localhost","root","1234"); mysqli_select_db($db,"sbadmin");
    //           → 하드코딩 제거, 환경변수 기반 최소권한(lab_app) + utf8mb4 연결로 교체.
    //           lab_connect() 는 labkit.php(auto_prepend)에서 정의됨.
    session_start();
    $db = lab_connect();

    function isLogin(){
        if (isset($_SESSION['uIdx'])) return true;
        return false;
    }
