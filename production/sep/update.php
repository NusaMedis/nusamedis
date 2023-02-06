<?php
  require_once 'sys/sep-update.php';
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  // include_once($LIB."dateLib.php");
  // require_once($LIB."tampilan.php");
 
  //INISIALISAI AWAL LIBRARY
  // $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $userLogin = $auth->GetUserData();                 

  #update no jaminan
  $sql = "UPDATE global.global_customer_user SET ";
  $sql .= "cust_usr_no_hp =".QuoteValue(DPE_CHAR, $_POST["cust_usr_no_hp"]);
  $sql .= " WHERE cust_usr_id = ".QuoteValue(DPE_CHAR, $_POST["id_cust_usr"]);
  $dtaccess->execute($sql);

  #insert klinik sep      
  $dbTable = "klinik.klinik_sep";

  $dbField[0] = "sep_id";   // PK
  $dbField[1] = "rujukan_asal_rujukan";
  $dbField[2] = "rujukan_ppk_rujukan_txt";
  $dbField[3] = "rujukan_ppk_rujukan";
  $dbField[4] = "rujukan_no_rujukan";
  $dbField[5] = "skdp_no_surat";
  $dbField[6] = "skdp_no_dpjp";
  $dbField[7] = "kls_rawat_txt";
  $dbField[8] = "cob";
  $dbField[9] = "katarak";
  $dbField[10] = "diag_awal_txt";
  $dbField[11] = "diag_awal";
  $dbField[12] = "jaminan_lakalantas";
  $dbField[13] = "catatan";
  $dbField[14] = "laka_suplesi";
  $dbField[15] = "laka_nosep_suplesi";
  $dbField[16] = "laka_tgl_kejadian";
  $dbField[17] = "laka_kdpropinsi";
  $dbField[18] = "laka_kdkabupaten";
  $dbField[19] = "laka_kdkecamatan";
  $dbField[20] = "laka_keterangan";
  $dbField[21] = "no_sep";
  $dbField[22] = "tgl_sep";
  $dbField[23] = "rujukan_tgl_rujukan";
  $dbField[24] = "poli_eksekutif";
  $dbField[25] = "kls_rawat";
  $dbField[26] = "laka_penjamin";
  $dbField[27] = "user_nama";
  $dbField[28] = "nama_dpjp";

   if(count($_POST["laka_penjamin"]) > 1) { 
   $penjamin = implode(',', $_POST["laka_penjamin"]); 
  } else {
    $penjamin = $_POST["laka_penjamin"];
  }
  $dpjp=explode('-', $_POST["skdp_noDPJP"]);
  $nm_dpjp=$dpjp[1];
  $kd_dpjp=$dpjp[0];
  // echo $kd_dpjp;


  $kls_rawat_txt = array(1 => 'kelas 1', 2 => 'kelas 2', 3 => 'kelas 3');

  $dbValue[0] = QuoteValue(DPE_CHAR, $_POST["sep_id"]);
  $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["rujukan_asalRujukan"]);
  $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["rujukan_ppkRujukan_txt"]);
  $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["rujukan_ppkRujukan"]);
  $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["rujukan_noRujukan"]);
  $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["skdp_noSurat"]);
  $dbValue[6] = QuoteValue(DPE_CHAR, $kd_dpjp);
  $dbValue[7] = QuoteValue(DPE_CHAR, $kls_rawat_txt[$_POST["klsRawat"]]);
  $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["cob"]);
  $dbValue[9] = QuoteValue(DPE_CHAR, $_POST["katarak"]);
  $dbValue[10] = QuoteValue(DPE_CHAR, $_POST["diagAwal_txt"]);
  $dbValue[11] = QuoteValue(DPE_CHAR, $_POST["diagAwal"]);
  $dbValue[12] = QuoteValue(DPE_CHAR, $_POST["jaminan_lakaLantas"]);
  $dbValue[13] = QuoteValue(DPE_CHAR, $_POST["catatan"]);
  $dbValue[14] = QuoteValue(DPE_CHAR, $_POST["laka_suplesi"]);
  $dbValue[15] = QuoteValue(DPE_CHAR, $_POST["laka_noSepSuplesi"]);
  $dbValue[16] = QuoteValue(DPE_CHAR, date_db($_POST["laka_tglKejadian"]));
  $dbValue[17] = QuoteValue(DPE_CHAR, $_POST["laka_kdPropinsi"]);
  $dbValue[18] = QuoteValue(DPE_CHAR, $_POST["laka_kdKabupaten"]);
  $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["laka_kdKecamatan"]);
  $dbValue[20] = QuoteValue(DPE_CHAR, $_POST["laka_keterangan"]);
  $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["noSep"]);
  $dbValue[22] = QuoteValue(DPE_CHAR, date_db($_POST["tglSep"]));
  $dbValue[23] = QuoteValue(DPE_CHAR, date_db($_POST["rujukan_tglRujukan"]));
  $dbValue[24] = QuoteValue(DPE_CHAR, (!empty($_POST["poli_eksekutif"])) ? $_POST["poli_eksekutif"] : '0');
  $dbValue[25] = QuoteValue(DPE_CHAR, $_POST["klsRawat"]);
  $dbValue[26] = QuoteValue(DPE_CHAR, $penjamin);
  $dbValue[27] = QuoteValue(DPE_CHAR, $userName);
  $dbValue[28] = QuoteValue(DPE_CHAR, $nm_dpjp);
  $dbKey[0] = 0; 
  
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  $dtmodel->update(); 
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

  exit();        

?>