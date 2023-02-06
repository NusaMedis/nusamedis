<?php
    //require_once("../penghubung.inc.php");
    require_once("../lib/login.php");
    $auth = new CAuth();
    $auth->Logout();
    header("location: ../");
    exit();
?>
