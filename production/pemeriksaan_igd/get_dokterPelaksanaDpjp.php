<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "login.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();
//and d.rol_jabatan = '$_GET[id_rol_jabatr]'"; 
if (isset($_GET["id_poli"])) {
        // Data dokter dan pelaksana
        $sql = "select usr_id, usr_name from global.global_auth_user a
        left join global.global_auth_role b on a.id_rol = b.rol_id
        where (rol_jabatan = 'D')  and usr_status='y' ";
   $sql .= " order by usr_name asc";
        // die($sql);
} else {
        $sql = "select usr_id, usr_name from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id
             where (rol_jabatan = 'D')  and usr_status='y' ";
        $sql .= " order by usr_name asc";
}
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
// $dataDokter = $dtaccess->FetchAll($rs);
$dataPelaksana = $dtaccess->FetchAll($rs);
$json = json_encode($dataPelaksana);
echo $json;
