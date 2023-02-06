<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();

//DIPATEN SEMENTARA

$sql = "select id_gudang from global.global_auth_poli where poli_id = '33'";
$Gudang = $dtaccess->Fetch($sql);

$theDep = $_GET["id_gudang"]; //$_GET["id_gudang"];  //Ambil Gudang yang aktif    
$jenis_id = $_GET['jenis_id'];

$sqlKonf = "select conf_biaya_tuslag, conf_biaya_tuslag_persen from apotik.apotik_conf";
$rsKonf = $dtaccess->Execute($sqlKonf);
$Konfigurasi = $dtaccess->Fetch($rsKonf);

//Apotik Konfigurasi                                                          
$sql = "select conf_apotik_harga_otomatis_margin from apotik.apotik_conf";
$rs = $dtaccess->Execute($sql);
$confApotik = $dtaccess->Fetch($rs);

#jika jenis pasien kosong
if (empty($jenis_id)) {
     $jenis_id = '2';
}


if ($_GET['item_nama']) $sql_where[] = " UPPER(c.item_nama) like '" . strtoupper($_GET['item_nama']) . "%'";
if ($_GET['item_kode']) $sql_where[] = " UPPER(c.item_kode) like '" . strtoupper($_GET['item_kode']) . "%'";
if ($sql_where) $sql_where = implode(" and ", $sql_where);

$sql = "select b.batch_id, b.batch_no, b.batch_tgl_jatuh_tempo, f.stok_dep_saldo ,
          c.item_kode, c.item_id , c.item_nama , c.item_harga_beli, c.item_harga_jual, d.jenis_nama,c.item_stok_alert,item_hpp, c.id_kategori, c.obat_flag, c.item_harga_diskon
          from logistik.logistik_stok_batch_dep a 
          left join logistik.logistik_item_batch b on a.id_batch = b.batch_id
          left join logistik.logistik_item c  on a.id_item=c.item_id
          left join global.global_jenis_pasien d on d.jenis_id = c.item_tipe_jenis
          left join logistik.logistik_grup_item e on e.grup_item_id = c.id_kategori
          left join logistik.logistik_stok_dep f on f.id_item = c.item_id and f.id_gudang = a.id_gudang";
$sql .= " where item_katalog='n' and c.item_aktif='y' and c.item_flag = 'M' and item_racikan='n'"; 
$sql .= " and f.id_gudang =" . QuoteValue(DPE_CHAR, $theDep);

if ($sql_where) $sql .= " and " . $sql_where;
$sql .= " order by c.item_nama asc, b.batch_tgl_jatuh_tempo asc limit 100";
// echo $sql;
// die();
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);
// echo json_encode($dataTable);

$data = array();
for ($i = 0; $i < count($dataTable); $i++) {

     if($dataTable[$i]['item_harga_diskon'] && $dataTable[$i]['obat_flag'] == 'g' && $dataTable[$i]['item_harga_diskon'] != null){
               $dataTable[$i]['item_hpp'] = ($dataTable[$i]['item_harga_diskon'] <= $dataTable[$i]['item_hpp']) ? $dataTable[$i]['item_harga_diskon'] : $dataTable[$i]['item_hpp'];
     }

     $hargabeli = $dataTable[$i]['item_hpp'];

     $sql = "select margin_nilai from apotik.apotik_margin
               where id_grup_item = ".QuoteValue(DPE_CHAR, $dataTable[$i]['id_kategori'])."
               and is_aktif ='Y' and " . intval($hargabeli) . " >= harga_min and " . intval($hargabeli) .
          " <= harga_max ";

     $rs = $dtaccess->Execute($sql);
     $margin = $dtaccess->Fetch($rs);


     if ($confApotik["conf_apotik_harga_otomatis_margin"] == 'y')  //jika harga dari margin maka dari perhitungan jika tidak ambil dari db
     {
          //$hargajual = (((100+$margin["margin_nilai"])/100)*$dataTable[$i]["item_harga_beli"])+$ppn;
          $hmargin = ((100 + $margin["margin_nilai"]) / 100) * $dataTable[$i]["item_hpp"];
          $hargajual = 1.1 * intval($hmargin);
     } else { 
          $hargajual = $dataTable[$i]["item_harga_jual"];
          $ppn = ceil(0.1 * $dataTable[$i]["item_harga_jual"]);
     }

     $row = array();
     $row['item_stok_alert'] = $dataTable[$i]['item_stok_alert'];
     $row['batch_id'] = $dataTable[$i]['batch_id'];
     $row['batch_no'] = $dataTable[$i]['batch_no'];
     $row['batch_tgl_jatuh_tempo'] = $dataTable[$i]['batch_tgl_jatuh_tempo'];
     $row['stok_batch_dep_saldo'] = $dataTable[$i]['stok_dep_saldo'];
     $row['item_kode'] = $dataTable[$i]['item_kode'];
     $row['item_id'] = $dataTable[$i]['item_id'];
     $row['id_kategori'] = $dataTable[$i]['id_kategori'];
     $row['item_nama'] = strtolower($dataTable[$i]['item_nama']);
     $row['item_harga_beli'] = currency_format(intval($hargabeli));
     $row['item_harga_margin'] = currency_format(intval($hmargin));
     $row['item_harga_jual'] = currency_format(intval($hmargin));
     $row['margin_nilai'] = currency_format(intval($margin["margin_nilai"]));
     $row['hpp'] = $dataTable[$i]['item_hpp'];
     if ($Konfigurasi["conf_biaya_tuslag_persen"] == "y") {
          $tuslag = $Konfigurasi["conf_biaya_tuslag"];
          $row['item_tuslag'] = currency_format(intval(($hmargin + $ppn) * ($tuslag / 100)));
     }
     $row['ppn'] = currency_format(intval($hmargin * 0.1));
     $row['tuslag'] = intval($tuslag);
     $row['jenis_nama'] = $dataTable[$i]['jenis_nama'];
     $data[] = $row;
}

echo json_encode($data);
