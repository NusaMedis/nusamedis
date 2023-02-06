<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."login.php");

      //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
     
     $timeNow = date('H:i:s');
     $day = date('w');
     $day = ($day == 0) ? 7 : $day;
     $id_poli = $_GET['poli_id'];
	 
     if($id_poli == '20' || $id_poli == 'bd731912df14620374835f5e595d78bb'){
        $sql = "SELECT usr_id, usr_name from global.global_auth_user where usr_status='y' ";
        $sql .= ($id_poli == '20') ? " and usr_id = '2d4b24fadc8a8e66c0d2a854ff305629' " : "";
        $sql .= ($id_poli == 'bd731912df14620374835f5e595d78bb') ? " and usr_id = '664b145bcf4f2f957e8f05dfcd87398a' " : "";
        $sql .= " order by usr_name asc";
     }
     else{
        $sql = "SELECT usr_id, usr_name from klinik.klinik_jadwal_dokter a
             left join global.global_auth_user b on a.id_dokter = b.usr_id
             where jadwal_dokter_hari = $day and usr_status='y' and jadwal_dokter_jam_mulai <= '$timeNow' and jadwal_dokter_jam_selesai >= '$timeNow' ";
        $sql .= ($id_poli) ? " and a.id_poli = '$id_poli' " : "";
        $sql .= " order by usr_name asc";
     }
     
       
     $rs = $dtaccess->Execute($sql);
     // $dataDokter = $dtaccess->FetchAll($rs);
     $dataPelaksana = $dtaccess->FetchAll($rs);
     $json = json_encode($dataPelaksana);
     echo $json;

?>