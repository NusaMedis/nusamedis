<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."login.php");
  require_once($LIB."dateLib.php");

  //INISIALISASI LIBRARY
  $dtaccess = new DataAccess();
  $tglSekarang = date("Y-m-d");
  $jamSkrg =  date("H:i:s");
  $auth = new CAuth();
  $userId = $auth->GetUserId();
  $tglKunjungan = date("d-m-Y");

  $sql = "select reg_id, reg_tipe_rawat, id_poli, b.poli_nama from klinik.klinik_registrasi a left join global.global_auth_poli b on a.id_poli = b.poli_id WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id'])." and reg_tanggal = ".QuoteValue(DPE_DATE, date('Y-m-d'))." and poli_tipe = ".QuoteValue(DPE_CHAR, $_POST['instalasi_id'])." order by reg_waktu desc";
  $data = $dtaccess->FetchAll($sql);

  echo '<option value="" selected>- Poli Pertama -</option>';
  foreach ($data as $value) {
    echo '<option value="'.$value['id_poli'].'">'.$value['poli_nama'].'</option>';
  }
?>