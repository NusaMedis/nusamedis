<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."login.php");

      //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
     		  
	if (isset($_GET["id_poli"])) {
    // Data dokter dan pelaksana
     $sql = "select usr_id, usr_name from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id
             left join global.global_auth_user_poli c on a.usr_id = c.id_usr
             where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='P' or rol_jabatan='A') and usr_status='y' ";
     $sql .= " and c.id_poli = ".QuoteValue(DPE_CHAR, $_GET["id_poli"]);
     $sql .= " order by usr_name asc";
    // die($sql);
    } else {
      $sql = "select usr_id, usr_name from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id
             where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='P' or rol_jabatan='A') and usr_status='y' ";
     $sql .= " order by usr_name asc";
       
    }
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);
     $dataPelaksana = $dtaccess->FetchAll($rs);
     $json = json_encode($dataPelaksana);
     echo $json;

?>