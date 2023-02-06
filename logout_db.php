<?php
    require_once("penghubung.inc.php");
    require_once($ROOT."lib/regLib.php"); 
    require_once($ROOT."lib/conf/database.php");
    
    $globalDataDB=new Registry("reg_db");
    $globalDataDB->DelEntry("Nama Database");
    $globalDataDB->Free();
    header("location: ./");
    exit();
?>
