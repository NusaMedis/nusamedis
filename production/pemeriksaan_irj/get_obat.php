<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	 $auth = new CAuth();
     $depNama = $auth->GetDepNama();
	 $depId = $auth->GetDepId(); 
     $userName = $auth->GetUserName();
     
     //DIPATEN SEMENTARA
     $poli = $_POST['id_poli']; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif    
     $jenis_id = $_GET['jenis_id'];

     $sqlKonf = "select conf_biaya_tuslag, conf_biaya_tuslag_persen from apotik.apotik_conf";
     $rsKonf = $dtaccess->Execute($sqlKonf);
     $Konfigurasi = $dtaccess->Fetch($rsKonf); 

     #jika jenis pasien kosong
     if (empty($jenis_id)) {
          $jenis_id = '2';
     }


	 if($_POST['q']) $sql_where[] = " UPPER(c.item_nama) like '".strtoupper($_POST['q'])."%'";
     if($sql_where) $sql_where = implode(" and ",$sql_where);       
	 
	$sql = "select b.batch_id, b.batch_no, b.batch_tgl_jatuh_tempo, a.stok_batch_dep_saldo ,
          c.item_kode, c.item_id , c.item_nama , c.item_harga_beli, c.item_hpp, c.id_kategori, d.jenis_nama, satuan_nama
          from logistik.logistik_stok_batch_dep a 
          left join logistik.logistik_item_batch b on a.id_batch = b.batch_id
          left join logistik.logistik_item c  on a.id_item=c.item_id
          left join logistik.logistik_item_satuan f on c.id_satuan_jual = f.satuan_id
          left join global.global_jenis_pasien d on d.jenis_id = c.item_tipe_jenis
          left join logistik.logistik_grup_item e on e.grup_item_id = c.id_kategori";  
	$sql .= " where item_katalog='n' and c.item_aktif='y' and c.item_flag = 'B' and item_racikan='n' and b.id_dep = ".QuoteValue(DPE_CHAR,$depId)." and a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);

	if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by c.item_nama asc, b.batch_tgl_jatuh_tempo asc limit 100";
     
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
  if ($Konfigurasi["conf_biaya_tuslag_persen"] == "y") {
    $tuslag = $Konfigurasi["conf_biaya_tuslag"];
    $row['item_tuslag'] = currency_format($hargajual*($tuslag/100));
}
     $data = array();
     for ($i=0; $i < count($dataTable) ; $i++) { 
          $hargabeli = $dataTable[$i]['item_hpp'];

          $sql = "select margin_nilai from apotik.apotik_margin_bhp
                  where id_grup_item = ".QuoteValue(DPE_CHAR,$dataTable[$i]['id_katergori'])."
                  and is_aktif ='Y' and ".$hargabeli." >= harga_min and ".$hargabeli.
                  " <= harga_max ";
          $rs = $dtaccess->Execute($sql);
          $margin = $dtaccess->Fetch($rs);
          $hargajual = ((100+$margin["margin_nilai"])/100)*$dataTable[$i]["item_harga_beli"];

          $row = array();
          $row['item_id'] = $dataTable[$i]['item_id'];
          $row['item_nama'] = strtolower($dataTable[$i]['item_nama'] );
          $row['item_harga_jual'] = $hargajual;
          $row['satuan_nama'] = $dataTable[$i]['satuan_nama'];
          $data[] = $row;
     }

     echo json_encode($data);





