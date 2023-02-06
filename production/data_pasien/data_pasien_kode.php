<?php
  // No. RM Otomatis
  DEFINE(TIPE_NO_RM_SEQUENTIAL,'1');
  DEFINE(TIPE_NO_RM_ALFABETIC,'2');
  DEFINE(TIPE_NO_RM_CUSTOM1,'3');
  DEFINE(TIPE_NO_RM_CUSTOM2,'4');
  DEFINE(TIPE_NO_RM_CUSTOM3,'5');
      
  $sql = "select dep_panjang_kode_pasien,dep_tipe_no_rm,dep_jml_nol_depan from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
  $panjangKodePasien = $dtaccess->Fetch($sql);  
       
  $_POST["dep_tipe_no_rm"] = $panjangKodePasien["dep_tipe_no_rm"];
  $_POST["dep_jml_nol_depan"]  = $panjangKodePasien["dep_jml_nol_depan"];
	
  if ($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_SEQUENTIAL) {                   
    $sqle = "select max(CAST (cust_usr_kode as integer)) as kode from global.global_customer_user where cust_usr_kode<>'100' and cust_usr_kode<>'500' and cust_usr_kode<>'501'";
    $lastKodeNonHuruf = $dtaccess->Fetch($sqle);
    
    $lastKodeNonHuruf["kode"] = intval($lastKodeNonHuruf["kode"]);	 
  } elseif ($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_ALFABETIC) {
        $hurufNama = substr($_POST["cust_usr_nama"],0,1); 

        $sql = "select cust_usr_huruf_urut as kode from global.global_customer_user where cust_usr_huruf = ".QuoteValue(DPE_CHAR,strtoupper($hurufNama))." order by kode desc";
        $lastKodeNonHuruf = $dtaccess->Fetch($sql); 
  } else {
    $tahunrm = substr(date('Y'),-2);
                   
    $sql = "select cust_usr_rak_rm as rak from global.global_customer_user where cust_usr_tahun_rm = ".QuoteValue(DPE_CHAR,$tahunrm)." order by rak desc";
    $rakrm = $dtaccess->Fetch($sql);
    
    $_POST["cust_usr_tahun_rm"] = $tahunrm;
    
    if (!$rakrm) { 
      $_POST["cust_usr_rak_rm"] = '1';
      $_POST["cust_usr_urut_rm"] = '1';         
    } else {
      $sql = "select cust_usr_urut_rm as rm_urut from global.global_customer_user where cust_usr_tahun_rm = ".QuoteValue(DPE_CHAR,$tahunrm)." and cust_usr_rak_rm = ".QuoteValue(DPE_CHAR,$rakrm["rak"])." order by rm_urut desc";
      $rmurut = $dtaccess->Fetch($sql);
         
      if($rmurut["rm_urut"]=='99') {
        $_POST["cust_usr_urut_rm"] = '1';
        $_POST["cust_usr_rak_rm"] = $rakrm["rak"]+1;
      } else {           
        $_POST["cust_usr_urut_rm"] = $rmurut["rm_urut"]+1;
        $_POST["cust_usr_rak_rm"] = $rakrm["rak"];
      }         
    }
  }
       
  if ($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_SEQUENTIAL) {
    if($_POST["dep_jml_nol_depan"]<>0) $_POST["kode_pasien"] = str_pad($lastKodeNonHuruf["kode"]+1,$panjangKodePasien["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
    else $_POST["kode_pasien"] = $lastKodeNonHuruf["kode"]+1;
  } elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_ALFABETIC) {
    $_POST["kode_pasien"] = $hurufNama."".str_pad($lastKodeNonHuruf["kode"]+1,$panjangKodePasien["dep_panjang_kode_pasien"],"0",STR_PAD_LEFT);
  } elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM1) {
    $_POST["kode_pasien"] = str_pad($_POST["cust_usr_rak_rm"],2,"0",STR_PAD_LEFT)."".str_pad($_POST["cust_usr_urut_rm"],2,"0",STR_PAD_LEFT)."".$tahunrm;
  } elseif($_POST["dep_tipe_no_rm"]==TIPE_NO_RM_CUSTOM3) {
    $_POST["kode_pasien"] = $tahunrm."".str_pad($_POST["cust_usr_rak_rm"],2,"0",STR_PAD_LEFT)."".str_pad($_POST["cust_usr_urut_rm"],2,"0",STR_PAD_LEFT);
  } else {
    $_POST["kode_pasien"] = str_pad($_POST["cust_usr_urut_rm"],2,"0",STR_PAD_LEFT)."".str_pad($_POST["cust_usr_rak_rm"],2,"0",STR_PAD_LEFT)."".$tahunrm;         
  }
       
  $arr = str_split($_POST["kode_pasien"],"2");
  $_POST["cust_usr_kode_tampilan"] = implode(".",$arr); // cust_usr_kode_tampilan

  /* INSERT GLLOBAL CUSTOMER USER */
  $dbTable = "global.global_customer_user"; 

  $dbField[0] = "cust_usr_id";   // PK  
  if ($norm_depan == 'y') {
    $dbField[1] = "cust_usr_kode";
    $dbField[2] = "cust_usr_kode_tampilan";
  }
     
  $custUsrId = $dtaccess->GetTransID();
  $dbValue[0] = QuoteValue(DPE_CHAR, $custUsrId);   
  if ($norm_depan == 'y') {
    $thn=substr(date('Y'), -2);
   $_POST["kode_pasien"]= str_replace(substr($_POST["kode_pasien"],0, 2), $thn, $_POST["kode_pasien"]);
    $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["kode_pasien"]);
    $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["cust_usr_kode_tampilan"]);
  } 
  // print_r($dbValue);die();
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  // $dtmodel->Insert() or die("insert  error"); 
         
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  /* INSERT GLLOBAL CUSTOMER USER */
?>