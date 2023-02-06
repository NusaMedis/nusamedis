<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();

#get cust usr
$id_cust_usr = $_REQUEST['id_cust_usr'];

#paging
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$limit = isset($_POST['rows']) ? intval($_POST['rows']) : 10;

//echo $page.$limit;

if ($id_cust_usr) {
  #get reg radiologi
  $sql = "SELECT reg_id from klinik.klinik_registrasi a where a.reg_utama = '$_POST[id_reg]' and id_poli in (select poli_id from global.global_auth_poli where poli_tipe = 'L')";
  $rad =  $dataTable = $dtaccess->Fetch($sql);

  //  echo $rad['reg_id'];

  $sql = "SELECT COUNT(fol_id)  as jml from klinik.klinik_folio a where a.id_reg = '$rad[reg_id]' and a.id_reg !='' and a.id_reg is not null and a.fol_jenis_sem IS NULL";
  $rs =  $dataTable = $dtaccess->Fetch($sql);

  if ($rs) {
    $jmlData = $rs['jml'];
  } else {
    $jmlData = 0;
  }

  # buat paging atau halaman
  if ($jmlData > 0 && $limit > 0) {
    $total_pages = ceil($jmlData / $limit);
  } else {
    $total_pages = 0;
  }

  if ($page > $total_pages) $page = $total_pages;
  $start = $limit * $page - $limit;
  if ($start < 0) $start = 0;

  #ambil data
  $sql = "select * from laboratorium.lab_pemeriksaan a left join klinik.klinik_registrasi b on b.reg_id=a.id_reg
      where a.id_cust_usr = '$id_cust_usr' and pemeriksaan_hasil='y' and a.id_reg is not null and a.id_reg != '' order by pemeriksaan_create desc OFFSET $start LIMIT $limit ";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);

  // echo $sql;
  // die;

  $data = [];

  for ($i = 0; $i < count($dataTable); $i++) {


    array_push($data, [
      'id_reg'   => $dataTable[$i]['id_reg'],
      'tanggal'   => date_format(date_create($dataTable[$i]['pemeriksaan_create']), 'd-m-Y'),
      'waktu'   =>   date_format(date_create($dataTable[$i]['pemeriksaan_create']), 'H:i:s'),
    ]);
  }

  $hasil = array(
    'total' => $jmlData,
    'rows'  => $data
  );

  echo json_encode($hasil);
}
