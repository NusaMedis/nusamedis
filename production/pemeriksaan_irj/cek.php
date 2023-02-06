<?php
 require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");


$tg = date("2018-02-28 17:09:00");
$nw = date("Y-m-d H:i:s");
echo $nw;
echo "<br>";
echo durasi( $tg ,$nw);
echo "<br>";
echo durasiDetik($tg ,$nw);
?>