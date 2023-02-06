<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	
	$sql = "select reg_id, id_cust_usr from klinik.klinik_registrasi a
			left join global.global_auth_poli b on a.id_poli = b.poli_id
			where reg_utama =".QuoteValue(DPE_CHAR,$_POST["id_reg"]) ;
	$sql .= " and b.poli_tipe = 'L'";
    $dataReg = $dtaccess->Fetch($sql); 
    //$dataReg['reg_utama'];
   // die($sql);
    // echo $sql;
    $sql = "select *, 1 as unit from laboratorium.lab_pemeriksaan_detail where id_cust_usr =".QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"])." order by detail_kode asc" ;
    // $dataPemeriksaan = $dtaccess->Fetch($sql); 
    
    // $sql = "select test_nm, result_value, ref_range, unit from laboratorium.lab_lis_res_data where ono =".QuoteValue(DPE_CHAR,substr($dataPemeriksaan["pemeriksaan_id"],0,20))." order by disp_seq asc" ;
    $dataHasilLab = $dtaccess->FetchAll($sql);
			//die($sql);
	$json = json_encode($dataHasilLab);
	echo $json;
	//echo $sql;
	 
	
?>
	