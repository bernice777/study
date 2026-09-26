<?php
    include("init.php");
    if (isset($_GET['p']) && $_GET['p'] == "register.php") {
        include("./pages/register.php");
    } else if (!isLogin()){
        include("./pages/login.php");
    } else {
        $page = isset($_GET['p']) ? $_GET['p'] : "home.php";
        // [LAB-MOD] 원본은 include("./pages/".$page) 로 임의 파일 include 가능(LFI).
        //           SQLI 범위 밖이므로 화이트리스트로 차단(원본은 original/ 에 보존).
        if (!in_array($page, lab_allowed_pages(), true)) {
            lab_scope_deny('lfi_include:'.$page);
            $page = "home.php";
        }
        include("./theme/head.php");
        include("./pages/".$page);
        include("./theme/foot.php");
    }
