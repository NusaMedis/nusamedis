<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	  
	# poli ke 2
     $sql = "select reg_id, reg_tipe_rawat, id_poli, b.poli_nama
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET['id_cust_usr'])." and reg_tanggal = ".QuoteValue(DPE_DATE, date('Y-m-d'));  
     $sql .= " order by reg_waktu desc";  
     $rs = $dtaccess->Execute($sql);
     $reg = $dtaccess->Fetch($rs);
     $polike2 = $dtaccess->FetchAll($rs);

  	echo '<option value="">- Poli Pertama -</option>';
    for($i=0,$n=count($polike2);$i<$n;$i++){ 
        echo '<option class="form_control" value="'.$polike2[$i]["poli_id"].'">'.
                        $polike2[$i]["poli_nama"].'</option>';
    }
	  

?>