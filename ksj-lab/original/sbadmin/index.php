<?php
    include("init.php");
    if (isset($_GET['p']) && $_GET['p'] == "register.php") {
        include("./pages/register.php");
    }else if (!isLogin()){
        include("./pages/login.php");
    } else {
        if (isset($_GET['p'])){
            $page = $_GET['p'];
        } else {
            $page = "home.php";
        }
        include("./theme/head.php");
        include("./pages/".$page);
        include("./theme/foot.php");
    }

