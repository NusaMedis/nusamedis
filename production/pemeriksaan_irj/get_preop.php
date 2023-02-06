<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();


$sql = "select a.pembayaran_id, c.reg_id,c.id_poli, b.cust_usr_id,gau.usr_name,
                 c.reg_operasi, k.preop_id, k.preop_status, k.preop_waktu,k.preop_tanggal_jadwal,k.preop_selesai_jadwal
         from klinik.klinik_preop k
                  left join global.global_auth_user gau on gau.usr_id=k.id_dokter
                  left join klinik.klinik_registrasi c on c.reg_id = k.id_reg
                  left join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
                  left join klinik.klinik_pembayaran a on a.pembayaran_id=c.id_pembayaran
                  left join global.global_auth_user e on e.usr_id = c.id_dokter
                  left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
              	  left join klinik.klinik_rawatinap g on g.id_reg = c.reg_id
              	  left join klinik.klinik_kamar h on h.kamar_id = g.id_kamar
              	  left join klinik.klinik_kamar_bed i on i.bed_id = g.id_bed and i.id_kamar = g.id_kamar
                  where c.reg_operasi = 'n' and c.reg_batal is null and k.preop_status='n'";
$sql .= " and k.id_reg = '$_POST[id_reg]' ";
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);
$data = array();
for ($i = 0; $i < count($dataTable); $i++) {

  $data[] = array(
    'reg_id'   => $dataTable[$i]['reg_id'],
    'usr_name'   => $dataTable[$i]['usr_name'],
    'id_dokter'   => $dataTable[$i]['id_dokter'],
    'preop_id'   => $dataTable[$i]['preop_id'],
    'preop_status'   => $dataTable[$i]['preop_status'],
    'preop_tanggal_jadwal'   => nice_date($dataTable[$i]['preop_tanggal_jadwal'], 'd-m-Y H:i:s'),
    'preop_selesai_jadwal'   => nice_date($dataTable[$i]['preop_selesai_jadwal'], 'd-m-Y H:i:s'),
    'preop_waktu'   => nice_date($dataTable[$i]['preop_waktu'], 'd-m-Y H:i:s'),
  );
}

echo json_encode($data);
