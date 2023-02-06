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
	   
	   
	   
	   if($_GET["id_periode"]) $sql_where[] = "c.id_periode = ".QuoteValue(DPE_CHAR,$_GET["id_periode"]); 
     if($_GET["id_gudang"] && $_GET["id_gudang"]<>'--') $sql_where[] = "c.id_gudang = ".QuoteValue(DPE_CHAR,$_GET["id_gudang"]);
     $sql_where = implode(" and ",$sql_where);

     $sql = "SELECT * from logistik.logistik_opname_detail a
             left join logistik.logistik_item b on a.id_item = b.item_id 
             left join logistik.logistik_opname c on a.id_opname = c.opname_id 
             left join logistik.logistik_grup_item d on b.id_kategori = d.grup_item_id";
     $sql .= " where opname_detail_selisih <> 0 and ".$sql_where;
     //echo $sql;
     $dataTable = $dtaccess->FetchAll($sql);

     
    // echo $sql;
    $sql =" select a.stok_dep_saldo , b.gudang_nama from logistik.logistik_stok_dep a
    left join logistik.logistik_gudang b on b.gudang_id = a.id_gudang";
    $sql .= " where a.id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"]);
    if($_GET["id_gudang"] && $_GET["id_gudang"]!="--"){
    $sql .= " and a.id_gudang =".QuoteValue(DPE_CHAR,$_GET["id_gudang"]);
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
    $sql = "select * from global.global_departemen  order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
        
    $sql = "select * from global.global_departemen ";
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);

$sql = "select penerimaan_periode_nama from logistik.logistik_penerimaan_periode
        where penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$_GET["id_periode"]);
 $rs = $dtaccess->Execute($sql);
 $dataPeriodeOpname = $dtaccess->Fetch($rs);    
    //-- bikin combo box untuk Tujuan --//
   	$sql = "select gudang_nama from logistik.logistik_gudang where gudang_id = ".QuoteValue(DPE_CHAR,$_GET["id_gudang"])." order by gudang_id asc"; 
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
     #tableprint { 
       display:none; 
       }

     @page {
       size: F4 landscape;
       max-width: 100%;
       max-height: 100%;
       transform: rotate(-90deg); 
       -webkit-transform: rotate(-90deg); 
       -moz-transform:rotate(-90deg);
       filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
       
       }
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
          <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Waktu Cetak : <strong><?php echo date("d-m-Y H:i:s");?></strong></td>
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
<thead>
  <tr>
    <th rowspan="2" style="text-align: center;">NO</th>
    <th rowspan="2" style="text-align: center;">Kode</th>
    <th rowspan="2" style="text-align: center;">Nama Obat</th>
    <th rowspan="2" style="text-align: center;">Kel.</th>
    <th rowspan="2" style="text-align: center;">HPP</th>
    <th colspan="2" style="text-align: center;">Stok Tercatat</th>
    <th colspan="2" style="text-align: center;">Stok Fisik</th>
    <th colspan="2" style="text-align: center;">Selisih</th>
    <th rowspan="2" style="text-align: center;">Keterangan</th>
  </tr>
  
  <tr>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
  </tr>
</thead>

  <?php
  for($i = 0, $n = count($dataTable); $i < $n; $i++){
    $awal = $dataTable[$i]['opname_detail_jumlah_sebelumnya'];
    $selisih = $dataTable[$i]['opname_detail_selisih'];
    ?>
  <tr>
    <td><?=$i+1?></td>
    <td><?=$dataTable[$i]['item_kode']?></td>
    <td><?=$dataTable[$i]['item_nama']?></td>
    <td><?=$dataTable[$i]['grup_item_nama']?></td>
    <td><?=$dataTable[$i]['opname_detail_hpp']?></td>
    <td><?=number_format($awal, 2, ',', '.')?></td>
    <td><?=number_format($awal*$dataTable[$i]["opname_detail_hpp"], 0, ',', '.')?></td>

    <?php
    $hpp = $awal*$dataTable[$i]["opname_detail_hpp"];
    $opname += $dataTable[$i]["opname_detail_jumlah"];
    $hppOpname += $hpp;
    ?>

    <td><?=number_format($dataTable[$i]["opname_detail_jumlah"], 2, ',', '.')?></td>
    <td><?=number_format($dataTable[$i]["opname_detail_jumlah"]*$dataTable[$i]["opname_detail_hpp"], 0, ',', '.')?></td>

    <td><?=number_format($selisih, 2, ',', '.')?></td>
    <td><?=number_format($dataTable[$i]["opname_detail_hpp"]*$selisih, 0, ',', '.')?></td>
    <td><?=$dataTable[$i]["opname_detail_keterangan"]?></td>
  </tr>
    <?php
    $hpp_akhir = $dataTable[$i]["opname_detail_jumlah"]*$dataTable[$i]["opname_detail_hpp"];
    $hpp_selisih = $dataTable[$i]["opname_detail_hpp"]*$selisih;
    $akhir += $dataTable[$i]["opname_detail_jumlah"];
    $hppAkhir += $hpp_akhir;
    $hppSelisih += $hpp_selisih;
  }
  ?>
  <tr>
    <td></td>
    <td></td>
    <td>Jumlah</td>
    <td></td>
    <td></td>
    <td><!-- <?=number_format($opname, 2, ',', '.')?> --></td>
    <td><?=number_format($hppOpname, 0, ',', '.')?></td>
    <td><!-- <?=number_format($akhir, 2, ',', '.')?> --></td>
    <td><?=number_format($hppAkhir, 0, ',', '.')?></td>
    <td></td>
    <td><?=number_format($hppSelisih, 0, ',', '.')?></td>
    <td></td>
  </tr>
  
</table>
<br>
<br>
<br>
<br>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <center>
        
        IT
        <br>
        <br>
        <br>
        (.............................)
      </center>
    </td>
    <td>
      <center>
        
        Apoteker
        <br>
        <br>
        <br>
        (.............................)
      </center>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      <center>
      <br>
      Verifikator
        <br>
        <br>
        <br>
        (.............................)
      </center>
    </td>
    <td>
      <center>
      Mengetahui <br>
      Kabag Keuangan
        <br>
        <br>
        <br>
        (.............................)
      </center>
    </td>
  </tr>
</table>
</td>
</tr>
</table> 


