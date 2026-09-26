<?php
    ini_set("display_errors", true);

    session_start();

    $db = mysqli_connect("localhost", "root", "1234");
    mysqli_select_db($db, "sbadmin");

    function isLogin(){
        if (isset($_SESSION['uIdx'])) return true;
        return false;
    }
