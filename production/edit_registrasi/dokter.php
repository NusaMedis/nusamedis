<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	  
	# poli ke 2
    $sql = "select a.usr_name,a.usr_id,c.rol_jabatan
            from global.global_auth_user a 
            left join global.global_auth_role c on a.id_rol = c.rol_id";
    $sql .= " where c.rol_jabatan = 'D' ";   
    $sql .= " and usr_status = 'y'";    
    //$sql .= " and d.jadwal_dokter_jam_mulai <".QuoteValue(DPE_DATE,date("H:i:s"));
    //$sql .= " and d.jadwal_dokter_jam_selesai >".QuoteValue(DPE_DATE,date("H:i:s"));
    //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_CHAR,date('N'));
    //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_NUMERIC,GetDayNameNew(date_db($tglSekarang))); 
    $sql .= " order by usr_name asc";   
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataDokter = $dtaccess->FetchAll($rs);

  	echo '<option value="">- Poli Pertama -</option>';
    for($i=0,$n=count($dataDokter);$i<$n;$i++){ 
        echo '<option class="form_control" value="'.$dataDokter[$i]["usr_id"].'">'.
                        $dataDokter[$i]["usr_name"].'</option>';
    }
	  

?>