<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "login.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();
$tglSekarang = date("Y-m-d");
$auth = new CAuth();
$userId = $auth->GetUserId();
$q = strtolower($_GET['q']);


if($_GET['etc'] == 'asisten'){
  $global_auth_user = array(
    array(
      "usr_name" => "Akmal Fikri, S.Kep., Ns.",
      "usr_loginname" => "FIKRI",
      "usr_password" => "1c6eb296e67b603e95437caf17e62493",
      "id_rol" => 6,
      "usr_status" => "y",
      "usr_when_create" => "2018-12-12T02:11:19.000Z",
      "usr_app_def" => 10,
      "id_dep" => "9999999",
      "usr_foto" => "default.jpg",
      "is_sync" => "n",
      "usr_id" => "a92ee73a6381625a8299f15292ec1d01",
      "usr_honor_split" => null,
      "usr_honor_persen" => null,
      "id_split_honor" => null,
      "usr_honor_bahan" => null,
      "usr_bahan_persen" => null,
      "usr_poli" => "",
      "id_kat_dokter" => null,
      "usr_no_urut" => null,
      "id_pgw" => "8e19c09ba50adb3399d41b3052dee957",
      "id_poli" => null,
      "id_kamar" => null,
      "nama_pgw" => null,
      "is_pegawai" => "y",
      "id_perawat" => null,
      "kode_bpjs_dpjp" => null
    ),
    array(
      "usr_name" => "Angga Novi Maulana, S.Kep., Ns.",
      "usr_loginname" => "ANGGA",
      "usr_password" => "eda05c540857c7ac2d6124ee205e69ca",
      "id_rol" => 6,
      "usr_status" => "y",
      "usr_when_create" => "2020-03-05T01:45:49.000Z",
      "usr_app_def" => 10,
      "id_dep" => "9999999",
      "usr_foto" => "default.jpg",
      "is_sync" => "n",
      "usr_id" => "84e01e6f66ccde744fb877fbb2adc687",
      "usr_honor_split" => null,
      "usr_honor_persen" => null,
      "id_split_honor" => null,
      "usr_honor_bahan" => null,
      "usr_bahan_persen" => null,
      "usr_poli" => "",
      "id_kat_dokter" => null,
      "usr_no_urut" => null,
      "id_pgw" => "b951c16bb09100adc0b724f17593ab82",
      "id_poli" => null,
      "id_kamar" => null,
      "nama_pgw" => null,
      "is_pegawai" => "y",
      "id_perawat" => null,
      "kode_bpjs_dpjp" => null
    )
  );

  echo json_encode($global_auth_user);
}
else if($_GET['etc'] == 'pembius'){
  $global_auth_user = array(
    array(
      "usr_name" => "dr. Moch. Rahadi Hamsya Msi. Med., SpAn",
      "usr_loginname" => "RAHADI",
      "usr_password" => "f3f74c0c84fbf730735f20b66ccc6457",
      "id_rol" => 2,
      "usr_status" => "y",
      "usr_when_create" => "2020-03-05T03:06:09.000Z",
      "usr_app_def" => 10,
      "id_dep" => "9999999",
      "usr_foto" => "default.jpg",
      "is_sync" => "n",
      "usr_id" => "4ae72b3605732f6c53ce172b20593ff0",
      "usr_honor_split" => null,
      "usr_honor_persen" => null,
      "id_split_honor" => null,
      "usr_honor_bahan" => null,
      "usr_bahan_persen" => null,
      "usr_poli" => "",
      "id_kat_dokter" => null,
      "usr_no_urut" => 25,
      "id_pgw" => "8180b91b6a032a5111ad2befe904921b",
      "id_poli" => null,
      "id_kamar" => null,
      "nama_pgw" => null,
      "is_pegawai" => "y",
      "id_perawat" => null,
      "kode_bpjs_dpjp" => null
    ),
    array(
      "usr_name" => "Fajar Noviyanto, Sp.An, dr",
      "usr_loginname" => "fajar",
      "usr_password" => "6fac3ab603bb3fb46e4277786393194c",
      "id_rol" => 2,
      "usr_status" => "y",
      "usr_when_create" => "2020-03-05T03:06:54.000Z",
      "usr_app_def" => 10,
      "id_dep" => "9999999",
      "usr_foto" => "default.jpg",
      "is_sync" => "n",
      "usr_id" => "925f5a5cfcf2b80ba05b02f388d99242",
      "usr_honor_split" => null,
      "usr_honor_persen" => null,
      "id_split_honor" => null,
      "usr_honor_bahan" => null,
      "usr_bahan_persen" => null,
      "usr_poli" => "",
      "id_kat_dokter" => null,
      "usr_no_urut" => 26,
      "id_pgw" => "905b4a55be4d52a16e8bb3683e8e1dc3",
      "id_poli" => null,
      "id_kamar" => null,
      "nama_pgw" => null,
      "is_pegawai" => "y",
      "id_perawat" => null,
      "kode_bpjs_dpjp" => null
    )
  );

  echo json_encode($global_auth_user);
}
else{



$sql = 'select * from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id';
if (isset($q)) {
  // $dpn=strtoupper(substr($_GET['q'], 0));

  // $nama=$dpn+substr($_GET['q'], 1);

  $sql .= ' WHERE lower(usr_name) like ' . QuoteValue(DPE_CHAR,  '%%' . $q . '%%');
  // $sql .= ' or usr_loginname like '.QuoteValue(DPE_CHAR,  '%%'.$_GET['q'].'%%') ;
  $sql .= "and (rol_jabatan = 'P' or rol_jabatan = 'D'  or rol_id = '10') and usr_status='y' order by a.usr_name asc";
}

// echo $sql . "<br/>";
// echo $dpn . "<br/>";
// echo $nama . "<br/>";
$q = $dtaccess->fetchAll($sql);

$rs = [];

foreach ($q as $key => $value) {
  array_push($rs, [
    'usr_id' => $value['usr_id'],
    'usr_name' => $value['usr_name'],

  ]);
}

echo json_encode($rs);


}