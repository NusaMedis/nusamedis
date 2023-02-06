<?php
// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "report_pasien.php";
     $poliId = $auth->IdPoli();
	 
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	

	if($_GET["nama"]){
		$sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_GET["nama"])."%' ";
	 }
	 
	 if($_GET["kode"]){
		$sql_where[] = " d.cust_usr_kode like '%".$_GET["kode"]."%'";
	 }

   if($_GET["item_nama"]){
    $sql_where[] = " e.item_nama like '%".strtoupper($_GET["item_nama"])."%'";
   }

   if($_GET["id_dokter"]){
    $sql_where[] = " c.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["id_dokter"]);
   }
   if ($_GET["tgl_awal"]) $sql_where[] = "DATE(b.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
  if ($_GET["tgl_akhir"]) $sql_where[] = "DATE(b.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));

	
    $sql = " select a.id_penjualan,a.id_item,a.penjualan_detail_jumlah,b.*,d.cust_usr_kode,e.item_nama,e.item_racikan, d.cust_usr_alamat, f.usr_name from apotik.apotik_penjualan_detail a 
            left join apotik.apotik_penjualan b on a.id_penjualan =b.penjualan_id
            left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
            left join global.global_customer_user d on c.id_cust_usr = d.cust_usr_id
            left join logistik.logistik_item e on a.id_item =e.item_id
            left join global.global_auth_user f on b.who_update = f.usr_id";
    $sql.= " where ".implode(" and ",$sql_where);
    //$sql.= " and b.id_dokter =".QuoteValue(DPE_CHAR,$_POST["nama_dokter"]);
    $sql.= " order by c.reg_tanggal, c.reg_waktu,b.penjualan_create,b.penjualan_id asc";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
     	//echo $sql;
	

	 for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_penjualan"]==$dataTable[$i-1]["id_penjualan"] ){
          $hitung[$dataTable[$i]["id_penjualan"]] += 1;
          }      
      }   

$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";   
  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
 
  if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }
  
  
?>

<script language="javascript" type="text/javascript">

window.print();

</script>

<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
          <span class="judul3">
          <?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
       <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>  
  </tr>
</table>
<br>
<table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
    <tr>
      <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent"></td>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">HISTORY PEMAKAIAN OBAT/ALKES PASIEN</td>   
    </tr>
  </table>
 <br>
<br>
<table border="1" class="table-striped nowrap" cellspacing="0" width="100%">
  <!-- <thead> -->
    <tr>
      <th>No</th>
      <th>No RM</th>
      <th>Nama Pasien</th>
      <th>Alamat</th>
      <th>No Faktur</th>
      <th>Cataatan</th>
      <th>Tanggal</th>
      <th>Dokter</th>
      <th>Item</th>
      <th>Quantity</th>
      <th>Pelaksana</th>
    </tr>
  <!-- </thead> -->
<?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
  <!-- <tbody> -->
    <tr>
      <? if($dataTable[$i]["id_penjualan"]!=$dataTable[$i-1]["id_penjualan"] ){
          $dataSpan = $hitung[$dataTable[$i]["id_penjualan"]]+1; ?>   
        <td rowspan="<? echo $dataSpan?>"><? echo $m++;?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_kode"];?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_nama"];?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_alamat"];?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["penjualan_nomor"];?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["penjualan_catatan"];?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo FormatTimeStamp($dataTable[$i]["penjualan_create"]);?></td>
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["dokter_nama"];?></td>
      <? } ?>
    
    <? if($dataTable[$i]["item_racikan"]='y') {
      $sql = "select item_nama from apotik.apotik_detail_racikan where id_nama_racikan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_item"]);
      $rs = $dtaccess->Execute($sql);
      $dataRacikan = $dtaccess->FetchAll($rs);
    ?>
      <td><strong><? echo $dataTable[$i]["item_nama"];?></strong><br>
      <table align="center" border='0'>
        <? for($a=0,$b=count($dataRacikan);$a<$b;$a++) { ?>
        <tr><td><? echo $dataRacikan[$a]["item_nama"]?></td></tr>
      <? } ?>
      </table></td>
     <? } ?>        
      <td align="right"><? echo $dataTable[$i]["penjualan_detail_jumlah"];?></td>
      <? if($dataTable[$i]["id_penjualan"]!=$dataTable[$i-1]["id_penjualan"] ){
        $dataSpan = $hitung[$dataTable[$i]["id_penjualan"]]+1; ?>   
        <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["usr_name"];?></td>
      <? } ?>
    </tr>
<? 
  $SumQuantity += $dataTable[$i]['penjualan_detail_jumlah'];
  } 
?>
    <tr>
      <td colspan="8" align="right"><b>TOTAL QUANTITY</b></td>
      <td style="color:red;" align="right"><b><?php echo str_replace(',', '.', currency_format($SumQuantity)) ?></b></td>
    </tr>
  <!-- </tbody> -->
</table>