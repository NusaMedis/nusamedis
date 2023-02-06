<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "login.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();
$tglSekarang = date("Y-m-d");

//Tanggal Kemarin
$tglKemarin = date('Y-m-d', strtotime("-5 day", strtotime(date("Y-m-d"))));

$auth = new CAuth();
$userId = $auth->GetUserId();

if (isset($_GET['reg_id'])) {
  $sql = "select a.reg_diagnosa_igd,
    a.id_dep,a.reg_id,a.reg_tingkat_kegawatan,a.reg_status_kondisi_deskripsi,a.reg_tanggal,a.reg_status_kondisi,a.id_pembayaran,
    a.id_cust_usr,a.reg_rujukan_id,a.id_poli,a.id_dokter,a.reg_jenis_pasien,
    b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_id,b.cust_usr_nama,b.cust_usr_alamat,
    c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id,
    h.rawat_anamnesa,h.rawat_keluhan,h.rawat_catatan,h.rawat_pemeriksaan_fisik,h.rawat_diagnosa_utama,h.rawat_ket, i.perusahaan_nama
    from
    klinik.klinik_registrasi a left join 
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
    left join global.global_auth_poli c on a.id_poli = c.poli_id 
    left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
    left join global.global_shift e on e.shift_id = a.reg_shift
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_auth_user g on g.usr_id = a.id_dokter
    left join klinik.klinik_perawatan h on h.id_reg = a.reg_id
    left join global.global_perusahaan i on i.perusahaan_id = a.id_perusahaan

    where reg_id = '$_GET[reg_id]'";
  $sql .= " order by a.reg_when_update asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);


  $data = [];

  for ($i = 0; $i < count($dataTable); $i++) {

    array_push($data, [
      'id_dep'   => $dataTable[$i]['id_dep'],
      'reg_id'   => $dataTable[$i]['reg_id'],
      'reg_tingkat_kegawatan'   => $dataTable[$i]['reg_tingkat_kegawatan'],
      'reg_status_kondisi_deskripsi'   => $dataTable[$i]['reg_status_kondisi_deskripsi'],
      'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
      'reg_status_kondisi'   => $dataTable[$i]['reg_status_kondisi'],
      'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
      'id_cust_usr'   => $dataTable[$i]['id_cust_usr'],
      'reg_rujukan_id'   => $dataTable[$i]['reg_rujukan_id'],
      'id_poli'   => $dataTable[$i]['id_poli'],
      'id_dokter'   => $dataTable[$i]['id_dokter'],
      'reg_jenis_pasien'   => $dataTable[$i]['reg_jenis_pasien'],
      'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
      'cust_usr_nama'   => $dataTable[$i]['cust_usr_nama'],
      'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
      'poli_nama'   => $dataTable[$i]['poli_nama'],
      'sebab_sakit_nama'   => $dataTable[$i]['sebab_sakit_nama'],
      'shift_nama'   => $dataTable[$i]['shift_nama'],
      'jenis_nama'   => $dataTable[$i]['jenis_nama'],
      'usr_name'   => $dataTable[$i]['usr_name'],
      'usr_id'   => $dataTable[$i]['usr_id'],
      'rawat_anamnesa'   => $dataTable[$i]['rawat_anamnesa'],
      'rawat_keluhan'   => $dataTable[$i]['rawat_keluhan'],
      'rawat_catatan'   => $dataTable[$i]['rawat_catatan'],
      'rawat_pemeriksaan_fisik'   => $dataTable[$i]['rawat_pemeriksaan_fisik'],
      'rawat_diagnosa_utama'   => $dataTable[$i]['rawat_diagnosa_utama'],
      'rawat_ket'   => $dataTable[$i]['rawat_ket'],
      'rawat_ket'   => $dataTable[$i]['perusahaan_nama'],
      'reg_diagnosa_igd'   => $dataTable[$i]['reg_diagnosa_igd']
    ]);
  }

  echo json_encode($data);
  //echo json_encode(array('errorMsg'=>'Some errors occured.'));
} elseif (!isset($_GET['reg_id'])) {

  if (isset($_POST['tgl_awal']) || isset($_POST['tgl_akhir'])) {
    $kondisi = " and a.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']));
    $kondisi .= " and a.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']));
  } else {
    $kondisi = "and a.reg_tanggal <='$tglSekarang'";
    $kondisi .= "and a.reg_tanggal >='$tglKemarin'";
  }

  $sql = "select a.reg_kode_trans,a.reg_id,a.reg_tanggal,a.reg_waktu,a.reg_status,
    a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,
    b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama, perusahaan_nama,h.usr_name from 
    klinik.klinik_registrasi a left join 
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
    global.global_auth_poli c on a.id_poli = c.poli_id left join
    global.global_auth_user_poli d on a.id_poli = d.id_poli
    left join global.global_jkn e on a.reg_tipe_jkn = e.jkn_id
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
    left join global.global_auth_user h on h.usr_id = a.id_dokter
    ";
  $sql .= " where c.poli_tipe='G' ";
  $sql .= $kondisi;
  $sql .= " group by a.reg_id, b.cust_usr_id, c.poli_id, f.jenis_id, e.jkn_id, g.perusahaan_id,h.usr_id order by a.reg_tanggal desc,a.reg_waktu desc ";
  //die($sql);
  // echo $sql;
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  $data = [];

  for ($i = 0; $i < count($dataTable); $i++) {

    array_push($data, [
      'reg_kode_trans'   => $dataTable[$i]['reg_kode_trans'],
      'reg_id'   => $dataTable[$i]['reg_id'],
      'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
      'reg_waktu'   => $dataTable[$i]['reg_waktu'],
      'reg_status'   => $dataTable[$i]['reg_status'],
      'reg_tipe_jkn'   => $dataTable[$i]['reg_tipe_jkn'],
      'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
      'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
      'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_nama'   => str_replace("*", "'", $dataTable[$i]['cust_usr_nama']),
      'cust_usr_tanggal_lahir'   => format_date($dataTable[$i]['cust_usr_tanggal_lahir']),
      'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
      'poli_nama'   => $dataTable[$i]['poli_nama'],
      'jenis_nama'   => $dataTable[$i]['jenis_nama'],
      'jkn_nama'   => $dataTable[$i]['jkn_nama'],
      'perusahaan_nama'   => $dataTable[$i]['perusahaan_nama'],
      'dokter'   => $dataTable[$i]['usr_name'],
      'poli'   => $dataTable[$i]['poli_nama']
    ]);
  }

  echo json_encode($data);
}
