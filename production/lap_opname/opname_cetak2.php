<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
	   
	   if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
         
     // konfigurasi gudang apotik
	   //$sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     //$rs = $dtaccess->Execute($sql);
     //$gudang = $dtaccess->Fetch($rs);
     //$_POST["id_dep"] = $gudang["conf_gudang_obat"];
	   
	   if($_GET["id_gudang"]) { $_POST["id_gudang"] = $_GET["id_gudang"]; }
	   
	   $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_GET['tanggal_awal']){
     $_GET['tanggal_awal']  = $skr;
     }
     if(!$_GET['tanggal_akhir']){
     $_GET['tanggal_akhir']  = $skr;
     }

     if($_GET["id_periode"]){ $sql_where[] = "h.id_periode = ".QuoteValue(DPE_CHAR,$_GET["id_periode"]);
     } else {
     if($_GET["tanggal_awal"]) $sql_where[] = "h.opname_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     if($_GET["tanggal_akhir"]) $sql_where[] = "h.opname_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));
     }
          
     if($_GET["id_gudang"] && $_GET["id_gudang"]!="--") $sql_where[] = "h.id_gudang = ".QuoteValue(DPE_CHAR,$_GET["id_gudang"]);
     if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_GET["klinik"]);
     $sql_where = implode(" and ",$sql_where);

     $sql = "select a.*,b.*,a.id_dep as dep_dep, c.gudang_nama, a.stok_item_create as tanggal,
             d.gudang_nama as nama_asal , e.gudang_nama as nama_tujuan, f.dep_nama as departemen, g.gudang_nama as gudang
             from logistik.logistik_stok_item a
             left join logistik.logistik_item b on a.id_item = b.item_id
             left join logistik.logistik_gudang c on c.gudang_id = a.id_gudang
             left join logistik.logistik_gudang d on d.gudang_id = a.id_dep_asal
             left join logistik.logistik_gudang e on e.gudang_id = a.id_dep_tujuan
             left join global.global_departemen f on f.dep_id = a.id_dep
             left join logistik.logistik_gudang g on g.gudang_id = a.id_gudang
             left join logistik.logistik_opname h on a.id_opname = h.opname_id ";
     $sql .= " where ".$sql_where;
     $sql .= " and stok_item_flag = 'O' and b.item_aktif='y' and ".$sql_where;
     $sql .= " order by c.gudang_id asc, a.stok_item_create asc";
     
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	$dataTable = $dtaccess->FetchAll($rs);
    // echo $sql;
    $sql =" select a.stok_dep_saldo , b.gudang_nama from logistik.logistik_stok_dep a
    left join logistik.logistik_gudang b on b.gudang_id = a.id_gudang";
    $sql .= " where a.id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"]);
    if($_GET["id_gudang"] && $_GET["id_gudang"]!="--"){
    $sql .= " and a.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    }
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	  $dataStok = $dtaccess->FetchAll($rs);

    $periode = $_GET["id_periode"];
     $sql = "SELECT * from logistik.logistik_penerimaan_periode WHERE penerimaan_periode_id = '$periode' order by penerimaan_periode_tanggal_awal desc limit 1";
     $periodeSeb = $dtaccess->Fetch($sql);
  
  
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	 if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
	
	  //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
        
    $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);

$sql = "select penerimaan_periode_nama from logistik.logistik_penerimaan_periode
        where penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$_GET["id_periode"]);
 $rs = $dtaccess->Execute($sql);
 $dataPeriodeOpname = $dtaccess->Fetch($rs);    
    //-- bikin combo box untuk Tujuan --//
   	$sql = "select gudang_nama from logistik.logistik_gudang where gudang_id = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." order by gudang_id asc"; 
		$dataGudang = $dtaccess->Fetch($sql);
     
  $lokasi = $ROOT."/gambar/img_cfg";
  if($konfigurasi["dep_logo"]) $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
   else $fotoName = $lokasi."/default.jpg";
     	
?>


<script language="javascript" type="text/javascript">
 window.print();
</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>
<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?php echo $fotoName ;?>" height="60"></td>
      <td style="text-align:center;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">

      <?php echo $konfigurasi["dep_nama"]?><BR>
      <?php echo $konfigurasi["dep_kop_surat_1"]?><BR>
      </td>
       </tr> 
       <tr>
       <td style="text-align:center;font-size:14px;font-family:times new roman;" class="tablecontent">
     
      <?php echo $konfigurasi["dep_kop_surat_2"]?></td>
    </tr>
  </table>
<br>
 <table border="0" cellpadding="3" cellspacing="0" style="align:left" width="100%">     
   <tr>
      <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php if($_GET["id_periode"]) { echo $dataPeriodeOpname["penerimaan_periode_nama"]; }else{ echo $_GET["tanggal_awal"];?> - <?php echo $_GET["tanggal_akhir"]; } ?></td>


    </tr>
    <tr>
          <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Gudang : <strong><?php echo $dataGudang["gudang_nama"];?></strong></td>
        </tr>
        <tr>
        <td style="text-align:center;font-size:15px;font-family:sans-serif;font-weight:bold;" class="tablecontent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $PageHeader; ?></td>
        </tr>
  </table>

<br>

  
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<table border="1" cellpadding="0" cellspacing="0">
  <tr>
    <th rowspan="3" style="text-align: center;">NO</th>
    <th rowspan="3" style="text-align: center;">Kode</th>
    <th rowspan="3" style="text-align: center;">Nama Obat</th>
    <th rowspan="3" style="text-align: center;">Kel.</th>
    <th rowspan="2" colspan="2" style="text-align: center;">Awal</th>
    <th colspan="2" style="text-align: center;">Masuk</th>
    <th colspan="2" style="text-align: center;">Keluar</th>
    <th rowspan="2" colspan="2" style="text-align: center;">Koreksi Keuangan</th>
    <th rowspan="2" colspan="2" style="text-align: center;">Akhir</th>
    <th rowspan="2" colspan="2" style="text-align: center;">Selisih</th>
  </tr>
  <tr>
    <th colspan="2" style="text-align: center;">Mutasi Gudang</th>
    
    <th colspan="2" style="text-align: center;"><?=($_POST["id_gudang"] == '1') ? 'Mutasi Ke Unit' : 'Penjualan'?></th>
  </tr>
  <tr>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    
  </tr>

 <?php
  for($i = 0, $n = count($dataTable); $i < $n; $i++){
    $item_id = $dataTable[$i]['item_id'];
    ?>
  <tr>
    <td><?=$i+1?></td>
    <td><?=$dataTable[$i]['item_kode']?></td>
    <td><?=$dataTable[$i]['item_nama']?></td>
    <td><?=$dataTable[$i]['grup_item_nama']?></td>
    <td><?=currency_format($dataTable[$i]["stok_item_saldo"])?></td>
    <td><?=currency_format($dataTable[$i]["stok_item_saldo"]*$dataTable[$i]["item_hpp"])?></td>

    <?php
    $sql = "SELECT sum(stok_item_jumlah) as masuk from logistik.logistik_stok_item WHERE stok_item_create >= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_awal'])." and stok_item_create <= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_akhir'])." and stok_item_flag = 'B' and id_gudang = ".QuoteValue(DPE_CHAR, $_POST["id_gudang"])." and id_item = '$item_id'";
    $dataMasuk = $dtaccess->Fetch($sql);
    ?>

    <!-- <td><?=($dataMasuk['masuk']) ? $dataMasuk['masuk'] : 0 ?></td> -->
    <td>0</td> 
    <td><?= ($dataMasuk['masuk']) ? currency_format($dataTable[$i]["item_hpp"] * $dataMasuk['masuk']) : currency_format($dataTable[$i]["item_hpp"])?></td>

    <?php
    if($_POST["id_gudang"] == '1'){
    $sql = "SELECT sum(transfer_detail_jumlah) as kirim from logistik.logistik_transfer_stok_detail a
            LEFT JOIN logistik.logistik_transfer_stok b on a.id_transfer = b.transfer_id

            where b.transfer_tanggal_permintaan >= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_awal'])." and b.transfer_tanggal_permintaan <= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_akhir'])." and a.id_item = '$item_id' and b.is_approve_terima = 'y' and b.id_asal = ".QuoteValue(DPE_CHAR, $_POST["id_gudang"]);
    $mutasi = $dtaccess->Fetch($sql);
    ?>

    <!-- <td><?=($mutasi['kirim']) ? $mutasi['kirim'] : 0 ?></td> -->
    <td>0</td> 
    <td><?=currency_format($dataTable[$i]["item_hpp"])?></td>

    <?php
  }
  else {
    $sql = "SELECT sum(stok_item_jumlah) as terjual from logistik.logistik_stok_item where id_item = '$item_id' and stok_item_create >= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_awal'])." and stok_item_create <= ".QuoteValue(DPE_CHAR, $periodeSeb['penerimaan_periode_tanggal_akhir'])." and stok_item_flag = 'P' and id_gudang = ".QuoteValue(DPE_CHAR, $_POST["id_gudang"]);
    $terjual = $dtaccess->Fetch($sql);
    ?>

    <!-- <td><?=($terjual['terjual']) ? $terjual['terjual'] : 0 ?></td> -->
    <td>0</td>
    <td><?=currency_format($dataTable[$i]["item_hpp"])?></td>
  <?php } ?>
    <?php
    if($periode == '81d1668ff3f5c617b8dee77fecfcaae2'){
      ?>
      <td><?=currency_format($dataTable[$i]["stok_item_saldo"]-$dataTable[$i]["stok_item_saldo"])?></td>
      <td><?=currency_format($dataTable[$i]["item_hpp"])?></td>
      <?php
    }
    else{
      ?>
      <td></td>
      <td></td>
      <?php
    }
    ?>
    
    <td><?=currency_format($dataTable[$i]["stok_item_saldo"])?></td>
    <td><?=currency_format($dataTable[$i]["stok_item_saldo"]*$dataTable[$i]["item_hpp"])?></td>
    <?php
    if($periode == '81d1668ff3f5c617b8dee77fecfcaae2'){
      ?>
      <td></td>
      <td></td>
      <?php
    }
    else{
      ?>
      <td></td>
      <td></td>
      <td><?=currency_format($dataTable[$i]["stok_item_saldo"]-$dataTable[$i]["stok_item_saldo"])?></td>
      <td><?=currency_format($dataTable[$i]["item_hpp"])?></td>
      <?php
    }
    ?>
    
    
  </tr>
    <?php
  }
  ?>
  
</table>
</td>
</tr>
</table> 

