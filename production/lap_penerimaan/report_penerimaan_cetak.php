<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$usrId = $auth->GetUserId();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$thisPage = "report_penerimaan_cetak.php";
$table = new InoTable("table", "100%", "left");

// PRIVILLAGE
/*  if(!$auth->IsAllowed("apo_lap_retur_beli",PRIV_READ)){
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_lap_retur_beli",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  */

if ($_GET["klinik"]) {
     $_POST["klinik"] = $_GET["klinik"];
} else if ($_POST["klinik"]) {
     $_POST["klinik"] = $_POST["klinik"];
} else {
     $_POST["klinik"] = $depId;
}

if ($auth->IsAllowed() === 1) {
     include("login.php");
     exit();
}

$skr = date("d-m-Y");
if (!$_GET["tanggal_awal"]) $_GET["tanggal_awal"] = $skr;
if (!$_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] = $skr;
$sql_where[] = "c.faktur_tgl >= " . QuoteValue(DPE_DATE, date_db($_GET["tanggal_awal"]));
$sql_where[] = "c.faktur_tgl <= " . QuoteValue(DPE_DATE, DateAdd(date_db($_GET["tanggal_akhir"]), 1));
if ($_GET["id_sup"] && $_GET["id_sup"] != "--") $sql_where[] = "d.id_sup = " . QuoteValue(DPE_CHAR, $_GET["id_sup"]);
if ($_GET["id_apotik"] && $_GET["id_apotik"] != "--") $sql_where[] = "i.flag_supplier = " . QuoteValue(DPE_CHAR, $_GET["id_apotik"]);
if ($_GET["id_sumber"] && $_GET["id_sumber"] != "--") $sql_where[] = "d.id_sumber = " . QuoteValue(DPE_CHAR, $_GET["id_sumber"]);
if ($_GET["id_kategori"] && $_GET["id_kategori"] != "--") $sql_where[] = "f.id_kategori = " . QuoteValue(DPE_CHAR, $_GET["id_kategori"]);
if ($_GET["klinik"] && $_GET["klinik"] != "--") $sql_where[] = "d.id_dep = " . QuoteValue(DPE_CHAR, $_GET["klinik"]);
$sql_where[] = "f.item_nama is not null";

if ($sql_where[0])
     $sql_where = implode(" and ", $sql_where);

$sql = "select a.*,c.*,g.dep_nama,h.satuan_nama,h.satuan_jumlah,c.faktur_nomor,j.sumber_nama,
             i.sup_nama,d.*,f.*            
             from logistik.logistik_faktur_item a  
             left join logistik.logistik_faktur c on a.id_faktur=c.faktur_id
             left join logistik.logistik_po d on c.id_po=d.po_id
             left join logistik.logistik_item f on f.item_id=a.id_item
             left join global.global_departemen g on g.dep_id = a.id_dep
             left join logistik.logistik_item_satuan h on h.satuan_id = f.id_satuan_jual
             left join logistik.logistik_grup_item k on k.grup_item_id = f.id_kategori
              left join global.global_supplier i on i.sup_id = d.id_sup
               left join global.global_sumber j on j.sumber_id = d.id_sumber";
$sql .= " where po_flag='M' and " . $sql_where;
$sql .= " order by c.faktur_tgl asc, faktur_nomor asc, item_nama asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);
$dataTable = $dtaccess->FetchAll($rs);
//echo $sql;

$sql = "select * from global.global_supplier where sup_id = '" . $_GET["id_sup"] . "'";
$rs = $dtaccess->Execute($sql);
$dataSupplier = $dtaccess->Fetch($rs);

//*-- config table ---*//
$tableHeader = "&nbsp;LAPORAN PENERIMAAN";

/*$isAllowedDel = $auth->IsAllowed("inv_lap_stok_per_gudang",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("inv_lap_stok_per_gudang",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("inv_lap_stok_per_gudang",PRIV_CREATE);*/

// --- construct new table ---- //
$counterHeader = 0;

/*if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }*/

$table = new InoTable("table1", "100%", "left", null, 0, 2, 1, null);
$PageHeader = "Laporan Penerimaan";

// --- construct new table ---- //
$counter = 0;
$tbHeader[0][$counter][TABLE_ISI] = "No.";
$tbHeader[0][$counter][TABLE_WIDTH] = "2%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "No Transaksi";
$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;


$tbHeader[0][$counter][TABLE_ISI] = "Supplier";
$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Sumber Dana";
$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;



$tbHeader[0][$counter][TABLE_ISI] = "No. Faktur";
$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;



$tbHeader[0][$counter][TABLE_ISI] = "Nama Item Barang";
$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Satuan";
$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Harga Satuan";
$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Jumlah";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "Harga Gross";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "DISKON";
$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "SETELAH DISKON";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "PPN";
$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "SETELAH PPN";
$tbHeader[0][$counter][ROWSPAN] = "2";
$counter++;

$tbHeader[0][$counter][TABLE_ISI] = "HARGA BELI";
$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
$counter++;

$counter = 0;

$tbHeader[1][$counter][TABLE_ISI] = "%";
$counter++;

$tbHeader[1][$counter][TABLE_ISI] = "Rp";
$counter++;

$tbHeader[1][$counter][TABLE_ISI] = "%";
$counter++;

$tbHeader[1][$counter][TABLE_ISI] = "Rp";
$counter++;

$tbHeader[1][$counter][TABLE_ISI] = "LAMA";
$counter++;

$tbHeader[1][$counter][TABLE_ISI] = "BARU";
$counter++;
$row = -1;
//TOTAL HEADER TABLE
$jumHeader = $counterHeader;
for ($i = 0, $m = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $m++, $counter = 0) {


     if ($dataTable[$i]["po_id"] != $dataTable[$i - 1]["po_id"]) {
          $row++;
          //hitung total
          //$total+=$dataTable[$i]["penjualan_total"];

          //hitung total Tax
          // $totalTax+=$dataTable[$i]["penjualan_ppn"];

          $tbContent[$m][$counter][TABLE_ISI] = $row + 1;
          $tbContent[$m][$counter][TABLE_ALIGN] = "right";
          $counter++;


          $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["faktur_tgl"];
          $tbContent[$m][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["no_trans"];
          $tbContent[$m][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["sup_nama"];
          $tbContent[$m][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["sumber_nama"];
          $tbContent[$m][$counter][TABLE_ALIGN] = "center";
          $counter++;



          $tbContent[$m][$counter][TABLE_ISI] = "";
          $tbContent[$m][$counter][TABLE_ALIGN] = "center";
          $tbContent[$m][$counter][TABLE_COLSPAN] = "14";
          $counter++;



          $j = 0;
          $m++;
          $counter = 0;
     }

     $j++;


     $tbContent[$m][$counter][TABLE_ISI] = "";
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $tbContent[$m][$counter][TABLE_COLSPAN] = "5";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["faktur_nomor"];
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;


     $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
     $tbContent[$m][$counter][TABLE_ALIGN] = "left";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["satuan_nama"] . " (" . $dataTable[$i]["satuan_jumlah"] . ")";
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_hna"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_jumlah"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_hna_total"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_diskon_persen"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_diskon"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_hna_diskon"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_ppn_persen"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_ppn"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($bayar = $dataTable[$i]["faktur_item_hna_ppn_minus_diskon"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_harga_beli_lama"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["faktur_item_harga_beli"]);
     $tbContent[$m][$counter][TABLE_ALIGN] = "center";
     $counter++;

     $linetotal += $bayar;
}



$tbBottom[0][$counter][TABLE_ISI] = "TOTAL PENERIMAAN";
$tbBottom[0][$counter][TABLE_COLSPAN] = "10";
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = "Rp." . currency_format($linetotal);
$tbBottom[0][$counter][TABLE_COLSPAN] = "9";
$tbBottom[0][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbBottom[0][$counter][TABLE_WIDTH] = "";
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;


$tglAwal = format_date($_GET["tanggal_awal"]);
$tglAkhir = $_GET["tanggal_akhir"];


//Data Klinik
$sql = "select * from global.global_departemen where dep_id like '" . $_GET["klinik"] . "%' order by dep_id";
$rs = $dtaccess->Execute($sql);
$dataKlinik = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_GET["klinik"]);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

$lokasi = $ROOT . "/gambar/img_cfg";
if ($konfigurasi["dep_logo"]) $fotoName = $lokasi . "/" . $konfigurasi["dep_logo"];
else $fotoName = $lokasi . "/default.jpg";

$sql = "select * from logistik.logistik_grup_item where grup_item_id = " . QuoteValue(DPE_CHAR, $_GET["id_kategori"]);
$rs = $dtaccess->Execute($sql);
$dataKategori = $dtaccess->Fetch($rs);

?>


<script language="javascript" type="text/javascript">
     window.print();
</script>

<style>
     @media print {
          #tableprint {
               display: none;
          }
     }

     #splitBorder tr td table {
          border-collapse: collapse;
     }

     #splitBorder tr td table tr td {
          border: 1px solid black;
     }

     body {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
          margin: 5px;
          margin-top: 0px;
          margin-left: 0px;
     }

     .menubody {
          background-image: url(gambar/background_01.gif);
          background-position: left;
     }

     .menutop {
          font-family: Arial;
          font-size: 11px;
          color: #FFFFFF;
          background-color: #000e98;
          background-image: url(gambar/bg_topmenu.png);
          background-repeat: repeat-x;
          font-weight: bold;
          text-transform: uppercase;
          text-align: center;
          height: 25px;
          background-position: left top;
          cursor: pointer;
     }

     .menubottom {
          background-image: url(gambar/submenu_bg.png);
          background-repeat: no-repeat;
     }

     .menuleft {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 12px;
          color: #333333;
          background-image: url(gambar/submenu_btn.png);
          background-repeat: repeat-y;
          font-weight: bolder;
     }

     .menuleft_bawah {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 8px;
          color: #333333;
          background-image: url(gambar/submenu_btn_bawah.png);
          font-weight: bold;
     }

     .img-button {
          cursor: pointer;
          border: 0px;
     }

     .menuleft a:link,
     a:visited,
     a:active {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 12px;
          text-decoration: none;
          color: #333333;
     }

     .menuleft a:hover {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 12px;
          text-decoration: none;
          color: #6600CC;
     }

     table {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
          padding: 0px;
          border-color: #000000;
          border-collapse: collapse;
          border-style: solid;
     }

     #tablesearch {
          display: none;
     }

     .passDisable {
          color: #0F2F13;
          border: 1px solid #f1b706;
          background-color: #ffff99;
     }

     .tabaktif {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #E60000;
          background-color: #ffe232;
          background-image: url(gambar/tbl_subheadertab.png);
          background-repeat: repeat-x;
          font-weight: bolder;
          height: 18;
          text-transform: capitalize;
     }

     .tabpasif {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #000000;
          background-color: #ffe232;
          background-image: url(gambar/tbl_subheader2.png);
          background-repeat: repeat-x;
          font-weight: bolder;
          height: 18;
          text-transform: capitalize;
     }

     .caption {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-style: normal;
     }

     a:link,
     a:visited,
     a:active {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          text-decoration: none;
          color: #1F457E;

     }

     a:hover {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          text-decoration: underline;
          color: #8897AE;
     }

     .titlecaption {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
          font-style: oblique;
          font-weight: bolder;

     }

     .tableheader {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
          color: #333333;
          font-weight: bold;
          text-transform: uppercase;
     }

     .tablesmallheader {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: bold;
          height: 18px;
          background-position: left top;
     }

     .tablecontent {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: lighter;
          height: 18px;
     }

     .tablecontent-odd {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
          font-weight: normal;
          height: 18px;
     }

     .tablecontent-kosong {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: bold;
          color: #FC0508;
          height: 18px;
     }

     .tablecontent-medium {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 16px;
          font-weight: lighter;
          background-color: #fff5b3;
          height: 18px;
     }

     .tablecontent-gede {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 23px;
          font-weight: lighter;
          background-color: #fff5b3;
          height: 18px;
     }

     .tablecontent-odd {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: lighter;
          height: 18px;
     }

     .tablecontent-odd-kosong {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 11px;
          color: #FC0508;
          font-weight: lighter;
          height: 18px;
     }

     .tablecontent-odd-medium {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 16px;
          font-weight: lighter;
          height: 18px;
     }

     .tablecontent-odd-gede {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 23px;
          font-weight: lighter;
          height: 18px;
     }

     .tablecontent-telat {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #FC0508;
          font-weight: lighter;
          background-color: #fff5b3;
          height: 18px;
     }

     .tablecontent-odd-telat {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #FC0508;
          font-weight: lighter;
          height: 18px;
     }

     .inputField {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 11px;
          color: #0F2F13;
          border: 1px solid #1A5321;
          background-color: #EBF4A8;
     }


     .content {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          background-color: #E7E6FF;
          height: 18px;
     }

     .content-odd {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          height: 18px;
     }

     .subheader {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #000000;
          background-color: #FFFFFF;
          font-weight: bolder;
          height: 18;
          text-transform: capitalize;
     }

     .subheader-print {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          color: #000000;
          font-weight: bolder;
          height: 18;
     }

     .staycontent {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: lighter;
     }

     .button,
     submit,
     reset {
          display: none;
          visibility: hidden;
     }

     select,
     option {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          text-indent: 2px;
          margin: 2px;
          left: 0px;
          clip: rect(auto auto auto auto);
          border-top: 0px;
          border-right: 0px;
          border-bottom: 0px;
          border-left: 0px;
     }

     input,
     textarea {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          border: 1px solid #f1b706;
          text-indent: 2px;
          margin: 2px;
          left: 0px;
          width: auto;
          vertical-align: middle;
     }

     .subtitlecaption {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-style: normal;
          font-weight: 500;
     }

     .inputcontent {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: lighter;
          background: #E6EDFB url(../none);
          border: none;
          text-align: right;
     }

     .hlink {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 12px;
     }

     .navActive {
          color: #cc0000;
     }

     fieldset {
          border: thin solid #2F2F2F;
     }

     .whiteborder {
          border: none;
          margin: 0px 0px;
          padding: 0px 0px;
          border-collapse: collapse;
     }

     .adaborder {
          border-left: none;
          border-top: none;
          border-bottom: none;
          border-right: solid #999999 1px;
          margin: 0px 0px;
          padding: 0px 0px;
          border-collapse: separate;
     }

     .divcontent {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          font-weight: lighter;
          background-color: #E7E6FF;
          border-bottom: solid #999999 1px;
          border-right: solid #999999 1px;
     }

     .curedit {
          text-align: right;
     }

     #div_cetak {
          display: block;
     }

     #tblSearching {
          display: none;
     }

     #printMessage {
          display: none;
     }

     #noborder.tablecontent {
          border-style: none;
     }

     #noborder.tablecontent-odd {
          border-style: none;
     }

     .noborder {
          border-style: none;
     }

     body {
          font-family: Arial, Verdana, Helvetica, sans-serif;
          margin: 0px;
          font-size: 10px;
     }

     .tableisi {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          border: none #000000 0px;
          padding: 4px;
          border-collapse: collapse;
     }


     .tableisi td {
          border: solid #000000 1px;
          padding: 4px;
     }

     .tablenota {
          font-family: Verdana, Arial, Helvetica, sans-serif;
          font-size: 10px;
          border: solid #000000 1px;
          padding: 4px;
          border-collapse: collapse;
     }

     .tablenota .judul {
          border: solid #000000 1px;
          padding: 4px;
     }

     .tablenota .isi {
          border-right: solid black 1px;
          padding: 4px;
     }

     .ttd {
          height: 50px;
     }

     .judul {
          font-size: 14px;
          font-weight: bolder;
          border-collapse: collapse;
     }


     .judul {
          font-size: 14px;
          font-weight: bolder;
          border-collapse: collapse;
     }


     .judul1 {
          font-size: 14px;
          font-weight: bolder;
     }

     .judul2 {
          font-size: 14px;
          font-weight: bolder;
     }

     .judul3 {
          font-size: 18px;
          font-weight: normal;
     }

     .judul4 {
          font-size: 12px;
          font-weight: bold;
          background-color: #CCCCCC;
          text-align: center;
     }

     .judul5 {
          font-size: 16px;
          font-weight: bold;
          background-color: #d6d6d6;
          text-align: center;
          color: #000000;
     }

     .judul6 {
          font-size: 12px;
          font-weight: bold;
          text-align: center;
          color: #000000;
     }
</style>
<style>
     @page {
          size 8.5in 11in;
          margin: 2cm;
     }

     div.page {
          page-break-after: always;
     }
</style>
<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
     <tr>
          <td rowspan="3" width="25%" class="tablecontent"><img src="<?php echo $fotoName; ?>" height="60"></td>
          <td style="text-align:center;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">
               <BR>
               <?php echo $konfigurasi["dep_nama"] ?><BR>
               <?php echo $konfigurasi["dep_kop_surat_1"] ?><BR>
          </td>
     </tr>
     <tr>
          <td style="text-align:center;font-size:14px;font-family:times new roman;" class="tablecontent">

               <?php echo $konfigurasi["dep_kop_surat_2"] ?></td>
     </tr>
</table>
<br>
<table border="0" cellpadding="3" cellspacing="0" style="align:left" width="100%">
     <tr>
          <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tanggal_awal"]; ?> - <?php echo $_GET["tanggal_akhir"]; ?></td>
     </tr>
     <tr>
          <td width="40%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Supplier : <?php echo $dataSupplier["sup_nama"]; ?></td>
     </tr>
     <tr>
          <td style="text-align:center;font-size:15px;font-family:sans-serif;font-weight:bold;" class="tablecontent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $tableHeader . " " . $dataKategori["grup_item_nama"]; ?></td>
     </tr>
</table>
<br>
<br>
<div class="page">
     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td>
                    <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>
               </td>
          </tr>
     </table>

     <table width="100%" border="0">
          <tr>
               <td style="font-size:12px;" align="right"><br>Printed at <?php echo $konfigurasi["dep_kota"] . ", " . date("d-m-Y H:i:s"); ?></td>
          </tr>
          <tr>
               <td style="font-size:12px;" align="right">Printed by <?php echo $userName; ?></td>

          </tr>
     </table>
</div>