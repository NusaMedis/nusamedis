<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$skr = date("d-m-Y");
$userData = $auth->GetUserData();
$monthName = array("--", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();

$viewPage = "item_view.php";
$editPage = "item_edit.php";
$findPage = "akun_find.php?";
$findPage12 = "akun_find11.php?";

/* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
         echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     }  */


$sql = "select * from global.global_departemen";
$dataDep = $dtaccess->Fetch($sql);

if ($_GET["klinik"]) {
  $_POST["klinik"] = $_GET["klinik"];
} else if ($_POST["klinik"]) {
  $_POST["klinik"] = $_POST["klinik"];
} else if ($_GET["tambah"]) {
  $_POST["klinik"] = $_GET["tambah"];
} else if (!$_POST["klinik"]) {
  $_POST["klinik"] = $depId;
}
$klinik = $_POST["klinik"];

/*  // cek konfigurasi gudang--
	   $sql = "select * from logistik.logistik_konfigurasi where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     $_POST["id_gudang"] = $gudang["konf_gudang"];
	 */
$plx = new expAJAX("CheckDataCustomerTipe,GetCombo,GetComboSatuanBeli,GetComboSatuanJual");

/* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  */

$PageKat = "page_kat.php";
$PageSatuanBeli = "page_satuan_beli.php";
$PageSatuanJual = "page_satuan_jual.php";
$PageSup = "page_sup.php";

if (!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
if (!$_POST["item_tipe_jenis"])  $_POST["item_tipe_jenis"] = "2";

if ($_GET["id_kategori"]) $_POST["id_kategori"] = $_GET["id_kategori"];
elseif (!$_POST["id_kategori"]) $_POST["id_kategori"] = "1";

$lokasi = $ROOT . "gambar/item";

function GetCombo()
{
  global $dtaccess, $userData, $lokasi, $view, $klinik;
  $sql = "select * from logistik.logistik_grup_item where item_flag = 'M' and id_dep=" . QuoteValue(DPE_CHAR, $klinik);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA);
  $dataKatItem = $dtaccess->FetchAll($rs);
  $opt_kat[0] = $view->RenderOption("--", "[Pilih Kategori]", $show);
  for ($i = 1, $n = count($dataKatItem); $i <= $n; $i++) {
    unset($show);
    if ($dataKatItem[$i - 1]["grup_item_id"] == $_POST["id_kategori"]) $show = "selected";
    $opt_kat[$i] = $view->RenderOption($dataKatItem[$i - 1]["grup_item_id"], $dataKatItem[$i - 1]["grup_item_nama"], $show);
  }

  return $view->RenderComboBox("id_kategori", "id_kategori", $opt_kat, "inputField");
}

function GetComboSatuanBeli()
{
  global $dtaccess, $userData, $lokasi, $view, $klinik;
  $sql = "select * from logistik.logistik_item_satuan where satuan_tipe='B' and id_dep=" . QuoteValue(DPE_CHAR, $klinik);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA);
  $dataSatuanBeli = $dtaccess->FetchAll($rs);
  $opt_satuan[0] = $view->RenderOption("--", "[Pilih Satuan Beli]", $show);
  for ($i = 1, $n = count($dataSatuanBeli); $i <= $n; $i++) {
    unset($show);
    if ($dataSatuanBeli[$i - 1]["satuan_id"] == $_POST["id_satuan_beli"]) $show = "selected";
    $opt_satuan[$i] = $view->RenderOption($dataSatuanBeli[$i - 1]["satuan_id"], $dataSatuanBeli[$i - 1]["satuan_nama"] . "(" . $dataSatuanBeli[$i - 1]["satuan_jumlah"] . ")", $show);
  }

  return $view->RenderComboBox("id_satuan_beli", "id_satuan_beli", $opt_satuan, "inputField");
}

function GetComboSatuanJual()
{
  global $dtaccess, $userData, $lokasi, $view, $klinik;
  $sql = "select * from logistik.logistik_item_satuan where satuan_tipe='J' and id_dep=" . QuoteValue(DPE_CHAR, $klinik);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA);
  $dataSatuanJual = $dtaccess->FetchAll($rs);
  $opt_satuan_jual[0] = $view->RenderOption("--", "[Pilih Satuan Jual]", $show);
  for ($i = 1, $n = count($dataSatuanJual); $i <= $n; $i++) {
    unset($show);
    if ($dataSatuanJual[$i - 1]["satuan_id"] == $_POST["id_satuan_jual"]) $show = "selected";
    $opt_satuan_jual[$i] = $view->RenderOption($dataSatuanJual[$i - 1]["satuan_id"], $dataSatuanJual[$i - 1]["satuan_nama"] . "(" . $dataSatuanJual[$i - 1]["satuan_jumlah"] . ")", $show);
  }

  return $view->RenderComboBox("id_satuan_jual", "id_satuan_jual", $opt_satuan_jual, "inputField");
}



function CheckDataCustomerTipe($custTipeNama)
{
  global $dtaccess;

  $sql = "SELECT a.item_id FROM logistik.logistik_item a 
                    WHERE upper(a.item_nama) = " . QuoteValue(DPE_CHAR, strtoupper($custTipeNama));
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);
  $dataitem = $dtaccess->Fetch($rs);

  return $dataitem["item_id"];
}




if ($_POST["x_mode"]) $_x_mode = &$_POST["x_mode"];
else $_x_mode = "New";

if ($_POST["item_id"])  $itemId = &$_POST["item_id"];

if ($_GET["id"]) {
  if ($_POST["btnDelete"]) {
    $_x_mode = "Delete";
  } else {
    $_x_mode = "Edit";
    $itemId = $enc->Decode($_GET["id"]);
  }


  $sql = "select a.* from logistik.logistik_item a 
				          where item_id = " . QuoteValue(DPE_CHAR, $itemId);

  $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);

  $sql = "select faktur_item_ppn_persen, b.faktur_item_diskon_persen, a.faktur_when_create from logistik.logistik_faktur a 
          left join logistik.logistik_faktur_item b on a.faktur_id = b.id_faktur 
          where b.id_item = '$itemId' 
          order by faktur_when_create desc limit 1 ";
  $diskon = $dtaccess->Fetch($sql);
  
  $_POST["item_nama"] = $row_edit["item_nama"];
  $_POST["item_satuan"] = $row_edit["item_satuan"];
  $_POST["item_harga_beli"] = $row_edit["item_harga_beli"];
  $_POST["item_harga_jual"] = $row_edit["item_harga_jual"];
  $_POST["item_berlaku"] = $row_edit["item_berlaku"];
  $_POST["item_keterangan"] = $row_edit["item_keterangan"];
  $_POST["id_kategori"] = $row_edit["id_kategori"];
  $_POST["id_kategori_tindakan"] = $row_edit["id_kategori_tindakan"];
  $_POST["id_petunjuk"] = $row_edit["id_petunjuk"];
  $_POST["item_stok_alert"] = $row_edit["item_stok_alert"];
  $_POST["item_stok"] = $row_edit["item_stok"];
  $_POST["id_satuan_jual"] = $row_edit["id_satuan_jual"];
  $_POST["id_satuan_beli"] = $row_edit["id_satuan_beli"];
  $_POST["item_kode"] = $row_edit["item_kode"];
  $_POST["item_tipe_jenis"] = $row_edit["item_tipe_jenis"];
  $_POST["item_spesifikasi"] = $row_edit["item_spesifikasi"];
  $_POST["klinik"] = $row_edit["id_dep"];
  $_POST["item_pic"] = $row_edit["item_pic"];
  $_POST["id_sup"] = $row_edit["id_sup"];
  $_POST["item_aktif"] = $row_edit["item_aktif"];
  $_POST["obat_flag"] = $row_edit["obat_flag"];
  // data perkiraan //
  $_POST["id_prk"] = $row_edit["id_prk"];
  $_POST["narkotika"] = $row_edit["item_narkotika"];
  $_POST["psikotropika"] = $row_edit["item_psikotropika"];
  $sql = "select * from  gl.gl_perkiraan where id_dep = " . QuoteValue(DPE_CHAR, $depId) . " and id_prk =" . QuoteValue(DPE_CHAR, $_POST["id_prk"]);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GL);
  $dataPerkiran = $dtaccess->Fetch($rs);
  $_POST["prk_id"] = $dataPerkiran["id_prk"];
  $_POST["prk_nama"] = $dataPerkiran["nama_prk"];
  $_POST["prk_no"] = $dataPerkiran["no_prk"];
  $_POST["item_hpp"] = $row_edit["item_hpp"];
  $_POST["item_harga_diskon"] = $row_edit["item_harga_diskon"];
  $_POST["id_tipe_sediaan"] = $row_edit["id_kat_item"];

  // data batch //
  $sql = "select * from  logistik.logistik_item_batch where id_dep = " . QuoteValue(DPE_CHAR, $depId) . " and batch_flag = 'A' and id_item =" . QuoteValue(DPE_CHAR, $itemId) . " order by batch_tgl_jatuh_tempo asc";
  $rs = $dtaccess->Execute($sql);
  $dataBatz = $dtaccess->Fetch($rs);
  $_POST["batch_no"] = $dataBatz["batch_no"];
  $_POST["batch_tgl_jatuh_tempo"] = format_date($dataBatz["batch_tgl_jatuh_tempo"]);

  $kembali = "item_view.php?kembali=" . $_POST["klinik"];
}

if ($_x_mode == "New") $privMode = PRIV_CREATE;
elseif ($_x_mode == "Edit") $privMode = PRIV_UPDATE;
else $privMode = PRIV_DELETE;

if ($_POST["btnNew"]) {
  header("location: " . $_SERVER["PHP_SELF"]);
  exit();
}

if ($_GET["tambah"]) {
  $_POST["klinik"] = $_GET["tambah"];
  $kembali = "item_view.php?kembali=" . $_POST["klinik"];
}

if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
  if ($_POST["btnUpdate"]) {
    $itemId = &$_POST["item_id"];
    $_x_mode = "Edit";
  }

  if ($err_code == 0) {

    if (isset($_POST['item_aktif'])) {
      $_POST['item_aktif'] = 'y';
    } else {
      $_POST['item_aktif'] = 'n';
    }
    $dbTable = "logistik.logistik_item";

    $dbField[0] = "item_id";   // PK
    $dbField[1] = "item_nama";
    $dbField[2] = "item_satuan";
    $dbField[3] = "item_harga_beli";
    $dbField[4] = "item_harga_jual";
    $dbField[5] = "item_keterangan";
    $dbField[6] = "item_berlaku";
    $dbField[7] = "id_kategori";
    $dbField[8] = "id_petunjuk";
    $dbField[9] = "item_stok_alert";
    $dbField[10] = "id_satuan_beli";
    $dbField[11] = "item_kode";
    $dbField[12] = "item_tipe_jenis";
    $dbField[13] = "item_spesifikasi";
    $dbField[14] = "id_dep";
    $dbField[15] = "id_satuan_jual";
    $dbField[16] = "item_stok";
    $dbField[17] = "id_sup";
    $dbField[18] = "id_kategori_tindakan";
    $dbField[19] = "id_prk";
    $dbField[20] = "item_flag";
    $dbField[21] = "item_aktif";
    $dbField[22] = "obat_flag";
    $dbField[23] = "item_generik";
    $dbField[24] = "item_narkotika";
    $dbField[25] = "item_psikotropika";
    $dbField[26] = "item_hpp";
    $dbField[27] = "id_kat_item";
    if ($_POST["item_pic"]) $dbField[28] = "item_pic";

    // buat mempermudah waktu cek masa berlaku --
    if ($_POST["item_berlaku_bulan"] == '1') {
      $bln = "01";
    } elseif ($_POST["item_berlaku_bulan"] == '2') {
      $bln = "02";
    } elseif ($_POST["item_berlaku_bulan"] == '3') {
      $bln = "03";
    } elseif ($_POST["item_berlaku_bulan"] == '4') {
      $bln = "04";
    } elseif ($_POST["item_berlaku_bulan"] == '5') {
      $bln = "05";
    } elseif ($_POST["item_berlaku_bulan"] == '6') {
      $bln = "06";
    } elseif ($_POST["item_berlaku_bulan"] == '7') {
      $bln = "07";
    } elseif ($_POST["item_berlaku_bulan"] == '8') {
      $bln = "08";
    } elseif ($_POST["item_berlaku_bulan"] == '9') {
      $bln = "09";
    } elseif ($_POST["item_berlaku_bulan"] == '10') {
      $bln = "10";
    } elseif ($_POST["item_berlaku_bulan"] == '11') {
      $bln = "11";
    } elseif ($_POST["item_berlaku_bulan"] == '12') {
      $bln = "12";
    }

    if ($_POST["obat_flag"] == 'g') {
      $generik = 'y';
    } elseif ($_POST["obat_flag"] == 't') {
      $generik = 'n';
    } else {
      $generik = null;
    }
    $berlakunya = $bln . "-" . $_POST["item_berlaku_tahun"];
    if (!$itemId) $itemId = $dtaccess->GetTransId();
    $dbValue[0] = QuoteValue(DPE_CHAR, $itemId);
    $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["item_nama"]);
    $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["item_satuan"]);
    $dbValue[3] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_harga_beli"]));
    $dbValue[4] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_harga_jual"]));
    $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["item_keterangan"]);
    $dbValue[6] = QuoteValue(DPE_CHAR, $berlakunya);
    $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["id_kategori"]);
    $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["id_petunjuk"]);
    $dbValue[9] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_stok_alert"]));
    $dbValue[10] = QuoteValue(DPE_CHAR, $_POST["id_satuan_beli"]);
    $dbValue[11] = QuoteValue(DPE_CHAR, $_POST["item_kode"]);
    $dbValue[12] = QuoteValue(DPE_NUMERIC, '2');
    $dbValue[13] = QuoteValue(DPE_CHAR, $_POST["item_spesifikasi"]);
    $dbValue[14] = QuoteValue(DPE_CHAR, $_POST["klinik"]);
    $dbValue[15] = QuoteValue(DPE_CHAR, $_POST["id_satuan_jual"]);
    $dbValue[16] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_stok"]));
    $dbValue[17] = QuoteValue(DPE_CHAR, $_POST["id_sup"]);
    $dbValue[18] = QuoteValue(DPE_CHAR, $_POST["id_kategori_tindakan"]);
    $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["prk_id"]);
    $dbValue[20] = QuoteValue(DPE_CHAR, 'M');
    $dbValue[21] = QuoteValue(DPE_CHAR, $_POST['item_aktif']);
    $dbValue[22] = QuoteValue(DPE_CHAR, $_POST['obat_flag']);
    $dbValue[23] = QuoteValue(DPE_CHAR, $generik);
    $dbValue[24] = QuoteValue(DPE_CHAR, $_POST['narkotika']);
    $dbValue[25] = QuoteValue(DPE_CHAR, $_POST['psikotropika']);
    $dbValue[26] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST['item_hpp']));
    $dbValue[27] = QuoteValue(DPE_CHAR, $_POST['id_tipe_sediaan']);
    if ($_POST["item_pic"]) $dbValue[28] = QuoteValue(DPE_CHAR, $_POST["item_pic"]);


    // print_r($dbValue);
    // die();
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_LOGISTIK);

    if ($_POST["btnSave"]) {
      $dtmodel->Insert() or die("insert  error");
    } else if ($_POST["btnUpdate"]) {
      $dtmodel->Update() or die("update  error");
    }
    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);

    // jika input data barang baru // 
    if ($_x_mode == "New") {

      /*      // insert data stok item batch //
              $dbTable = "logistik.logistik_item_batch";
              $dbField[0]  = "batch_id";   // PK
              $dbField[1]  = "batch_no";
              $dbField[2]  = "batch_create";    
              $dbField[3]  = "batch_tgl_jatuh_tempo";
              $dbField[4]  = "batch_stok_saldo";
              $dbField[5]  = "batch_flag";
              $dbField[6]  = "id_item";
              $dbField[7]  = "id_dep";
              $dbField[8]  = "id_gudang";
              
              $batchId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["batch_no"]);
              $dbValue[2] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
              $dbValue[3] = QuoteValue(DPE_CHAR,date_db($_POST["batch_tgl_jatuh_tempo"]));   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"])); 
              $dbValue[5] = QuoteValue(DPE_CHAR,'A');
              $dbValue[6] = QuoteValue(DPE_CHAR,$itemId); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("insert  error");
              	
              unset($dtmodel);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);
            
        */


      //masukkan ke semua gudang
      $sql = "select gudang_id from logistik.logistik_gudang where gudang_flag = 'M'";
      $rs = $dtaccess->Execute($sql);
      $dataGudang = $dtaccess->FetchAll($rs);
      for ($i = 0, $n = count($dataGudang); $i < $n; $i++) {
        # code...

        // insert data stok item dahulu //
        $dbTable = "logistik.logistik_stok_item";
        $dbField[0]  = "stok_item_id";   // PK
        $dbField[1]  = "stok_item_jumlah";
        $dbField[2]  = "id_item";
        $dbField[3]  = "id_gudang";
        $dbField[4]  = "stok_item_flag";
        $dbField[5]  = "stok_item_create";
        $dbField[6]  = "stok_item_saldo";
        $dbField[7]  = "id_dep";
        $dbField[8]  = "stok_item_hpp";


        $stokItemId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR, $stokItemId);
        $dbValue[1] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_stok"]));
        $dbValue[2] = QuoteValue(DPE_CHAR, $itemId);
        $dbValue[3] = QuoteValue(DPE_CHAR, $dataGudang[$i]["gudang_id"]);   //sesuai konfigurasi apotik 
        $dbValue[4] = QuoteValue(DPE_CHAR, 'A');               // A adalah saldo
        $dbValue[5] = QuoteValue(DPE_DATE, format_date($_POST["tgl_awal"]) . " " . date('H:i:s'));
        $dbValue[6] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_stok"]));
        $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["klinik"]);
        $dbValue[8] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_harga_beli"]));


        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

        $dtmodel->Insert() or die("insert  error");

        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);

        $dbTable = "logistik.logistik_stok_dep";
        $dbField[0]  = "stok_dep_id";   // PK
        $dbField[1]  = "id_item";
        $dbField[2]  = "stok_dep_saldo";
        $dbField[3]  = "stok_dep_create";
        $dbField[4]  = "stok_dep_tgl";
        $dbField[5]  = "id_gudang";
        $dbField[6]  = "id_dep";

        $stokDepId = $dtaccess->GetTransID();

        $dbValue[0] = QuoteValue(DPE_CHAR, $stokDepId);
        $dbValue[1] = QuoteValue(DPE_CHAR, $itemId); //QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
        $dbValue[2] = QuoteValue(DPE_NUMERIC, StripCurrency($_POST["item_stok"]));
        $dbValue[3] = QuoteValue(DPE_DATE, format_date($_POST["tgl_awal"]) . " " . date('H:i:s'));
        $dbValue[4] = QuoteValue(DPE_DATE, format_date($_POST["tgl_awal"]));
        $dbValue[5] = QuoteValue(DPE_CHAR, $dataGudang[$i]["gudang_id"]);
        $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["klinik"]);
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

        $dtmodel->Insert() or die("insert  error");

        unset($dbTable);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
      }
    }
    $kembali = "item_view.php?kembali=" . $_POST["klinik"];

    header("location:" . $kembali);
    exit();
  }
}

//Data Klinik
$sql = "select * from global.global_departemen where dep_id like '" . $klinik . "%' order by dep_id";
$rs = $dtaccess->Execute($sql);
$dataKlinik = $dtaccess->FetchAll($rs);

if ($_GET["del"]) {
  $itemId = $enc->Decode($_GET["id"]);

  $sql = "delete from logistik.logistik_item where item_id = " . QuoteValue(DPE_CHAR, $itemId);
  $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);

  $kembali = "item_view.php?kembali=" . $_POST["klinik"];

  header("location:" . $kembali);
  exit();
}

//-- bikin combo box untuk satuan Beli item --//
$sql = "select * from logistik.logistik_item_satuan where id_dep like '" . $_POST["klinik"] . "%' and satuan_tipe ='B' order by satuan_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);

unset($opt_satuan_beli);
$i = 1;
$opt_satuan_beli[0] = $view->RenderOption("--", "[Pilih Satuan Beli]", $show);
while ($data_satuan_beli = $dtaccess->Fetch($rs)) {
  unset($show);
  if ($data_satuan_beli["satuan_id"] == $_POST["id_satuan_beli"]) $show = "selected";
  $opt_satuan_beli[$i] = $view->RenderOption($data_satuan_beli["satuan_id"], $data_satuan_beli["satuan_nama"], $show);
  $i++;
}

//-- bikin combo box untuk satuan Jual item --//
$sql = "select * from logistik.logistik_item_satuan where id_dep like '" . $_POST["klinik"] . "%' and satuan_tipe ='J' order by satuan_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);

unset($opt_satuan_jual);
$i = 1;
$opt_satuan_jual[0] = $view->RenderOption("--", "[Pilih Satuan Jual]", $show);
while ($data_satuan_jual = $dtaccess->Fetch($rs)) {
  unset($show);
  if ($data_satuan_jual["satuan_id"] == $_POST["id_satuan_jual"]) $show = "selected";
  $opt_satuan_jual[$i] = $view->RenderOption($data_satuan_jual["satuan_id"], $data_satuan_jual["satuan_nama"], $show);
  $i++;
}

//-- bikin combo box untuk jenis item --//
$sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'  order by jenis_id asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);

unset($opt_jenis);
$i = 1;
$opt_jenis[0] = $view->RenderOption("--", "[Pilih Jenis]", $show);
while ($data_jenis = $dtaccess->Fetch($rs)) {
  unset($show);
  if ($data_jenis["jenis_id"] == $_POST["item_tipe_jenis"]) $show = "selected";
  $opt_jenis[$i] = $view->RenderOption($data_jenis["jenis_id"], $data_jenis["jenis_nama"], $show);
  $i++;
}

//Kategori Tindakannya
$sql = "select * from klinik.klinik_kategori_tindakan where id_dep = '" . $_POST["klinik"] . "' order by kategori_tindakan_nama ";
$rs = $dtaccess->Execute($sql);
$dataKatTind = $dtaccess->FetchAll($rs);

//-- bikin combo box untuk kategori --//
$sql = "select * from logistik.logistik_grup_item where item_flag='M' order by grup_item_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);
// echo $sql; die();    
unset($opt_kat);
$i = 1;
$opt_kat[0] = $view->RenderOption("--", "[Pilih Kategori]", $show);
while ($data_kat = $dtaccess->Fetch($rs)) {
  unset($show);
  if ($data_kat["grup_item_id"] == $_POST["id_kategori"]) $show = "selected";
  $opt_kat[$i] = $view->RenderOption($data_kat["grup_item_id"], $data_kat["grup_item_nama"], $show);
  $i++;
  //         echo $_POST["id_kategori"];
}

//-- bikin combo box untuk Supplier --//
$sql = "select * from global.global_supplier where id_dep = " . QuoteValue(DPE_CHAR, $depId) . " order by sup_nama";
$dataSup = $dtaccess->FetchAll($sql);

$sql = "select * from logistik.logistik_tipe_sediaan";
$dataTipeSediaan = $dtaccess->FetchAll($sql);



$berlaku = explode("-", $_POST["item_berlaku"]);
//echo $berlaku[0]."-".$berlaku[1];
unset($opt_berlaku_tahun);
unset($opt_berlaku_bulan);
unset($show);
for ($r = 0; $r < 10; $r++) {
  unset($show);
  if ($berlaku[1] == "201" . $r) $show = "selected";
  $opt_berlaku_tahun[$r] = $view->RenderOption("201" . $r, "201" . $r, $show);
}

for ($m = 1; $m <= 13; $m++) {
  unset($show);
  if ($berlaku[0] == $m) $show = "selected";
  $opt_berlaku_bulan[$m] = $view->RenderOption($m, $monthName[$m], $show);
}

if ($_POST["item_pic"]) $fotoName = $lokasi . "/" . $row_edit["item_pic"];
else $fotoName = $lokasi . "/default_barang.jpg";
?>


<!DOCTYPE html>
<html lang="en">
<script language="javascript" type="text/javascript">
  function ajaxFileUpload() {
    $("#loading")
      .ajaxStart(function() {
        $(this).show();
      })
      .ajaxComplete(function() {
        $(this).hide();
      });

    $.ajaxFileUpload({
      url: 'item_pic.php',
      secureuri: false,
      fileElementId: 'fileToUpload',
      dataType: 'json',
      success: function(data, status) {
        if (typeof(data.error) != 'undefined') {
          if (data.error != '') {
            alert(data.error);
          } else {
            alert(data.msg);

            document.getElementById('item_pic').value = data.file;
            document.img_item_item.src = '<?php echo $lokasi . "/"; ?>' + data.file;
          }
        }
      },
      error: function(data, status, e) {
        alert(e);
      }
    })

    return false;

  }

  <? $plx->Run(); ?>

  function CheckDataSave(frm) {

    if (!frm.item_nama.value) {
      alert('Nama item Harus Diisi');
      frm.item_nama.focus();
      return false;
    }



    /*if(!frm.batch_tgl_jatuh_tempo.value){
		alert('Batch Tanggal Jatuh Tempo Harus Diisi');
		frm.batch_tgl_jatuh_tempo.focus();
          return false;
	}  */


    if (frm.x_mode.value == "New") {

      if (!frm.batch_tgl_jatuh_tempo.value) {
        alert('Batch Tanggal Jatuh Tempo Harus Diisi');
        frm.batch_tgl_jatuh_tempo.focus();
        return false;
      }
      /*if(CheckDataCustomerTipe(frm.item_nama.value,'type=r')){
      	alert('Nama item Sudah Ada');
      	frm.item_nama.focus();
      	frm.item_nama.select();
      	return false;
      }  */
    }
    document.frmEdit.submit();
  }

  function getCombo() {
    GetCombo('target=dv_combo');
  }

  function getComboSatuanBeli() {
    GetComboSatuanBeli('target=dv_combo_satuan_beli');
  }

  function getComboSatuanJual() {
    GetComboSatuanJual('target=dv_combo_satuan_jual');
  }

  
</script>

<?php require_once($LAY . "header.php"); ?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">

      <?php require_once($LAY . "sidebar.php"); ?>
      <!-- top navigation -->
      <?php require_once($LAY . "topnav.php"); ?>
      <!-- /top navigation -->
      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          <div class="page-title">
            <div class="title_left">
              <h3>Manajemen</h3>
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Master Barang</h2>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tanggal <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"]; ?>" />
                        <img src="<?php echo $ROOT; ?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
                      </div>
                    </div>
                    <div class="form-group">
                      <?php
                      if ($_POST['item_kode'] == '' || $_POST['item_kode'] == null || !$_POST['item_kode']) {
                        // generate kode otomatis
                        $sql = "select max(item_kode) as kode from logistik.logistik_item";
                        $kodeotomatis = $dtaccess->Fetch($sql);
                        $kode = substr($kodeotomatis['kode'], 2);
                        $kode = "1-".($kode+1);
                      }
                      ?>
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kode <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        
                        <input type="text" name="item_kode" id="item_kode" class="form-control" value="<?= ($kode) ? $kode : $_POST['item_kode'] ?>">
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama</label>
                      <div class="col-md-4 col-sm-4 col-xs-12">
                        <?php echo $view->RenderTextBox("item_nama", "item_nama", "50", "100", $_POST["item_nama"], "inputField", null, false); ?>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">kategori Barang</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <td colspan="2" class="tablecontent-odd" width="3%">
                          <div id="dv_combo"><?php echo GetCombo(); ?>
                          </div>
                        </td>
                        <td align="left" class="tablecontent-odd">
                          <a href="<?php echo $PageKat; ?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=300&width=600&modal=true" class="thickbox" title="Tambah Satuan Jual"><img src="<?php echo $ROOT; ?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Jual" alt="Tambah Satuan Jual" /></a>
                        </td>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Suplier</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        
                          <select name="id_sup" class="form-control">
                            <option>PIlih Suplier</option>
                            <?php for($i=0; $i < count($dataSup); $i++) { ?>
                              <option value="<?=$dataSup[$i]['sup_id']?>" <?=($_POST['id_sup'] == $dataSup[$i]['sup_id']) ? "selected" : "" ?>><?=$dataSup[$i]['sup_nama']?></option>
                            <?php } ?>
                          </select>
                          
                        
                       
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipe Sediaan</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        
                          <select name="id_tipe_sediaan" class="form-control">
                            <option>PIlih Tipe</option>
                            <?php for($i=0; $i < count($dataTipeSediaan); $i++) { ?>
                              <option value="<?=$dataTipeSediaan[$i]['tipe_sediaan_id']?>" <?=($_POST['id_tipe_sediaan'] == $dataTipeSediaan[$i]['tipe_sediaan_id']) ? "selected" : "" ?>><?=$dataTipeSediaan[$i]['tipe_sediaan_nama']?></option>
                            <?php } ?>
                          </select>
                          
                        
                       
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Status Item </label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="item_aktif" id="item_aktif" value="<?php echo "y"; ?>" <?php if ($_POST["item_aktif"] == "y") echo "checked"; ?> />
                        <label for="item_aktif">Aktif</label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Barang</label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <input type="radio" name="obat_flag" <?php if (isset($_POST['obat_flag']) && $_POST['obat_flag'] == "g") echo "checked"; ?> value="g">&nbsp;Generik&nbsp;
                        <input type="radio" name="obat_flag" <?php if (isset($_POST['obat_flag']) && $_POST['obat_flag'] == "t") echo "checked"; ?> value="t">&nbsp;Non Generik&nbsp;
                        <input type="radio" name="obat_flag" <?php if (isset($_POST['obat_flag']) && $_POST['obat_flag'] == "a") echo "checked"; ?> value="a">&nbsp;Alkes / Alat Kesehatan&nbsp;
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Item Narkotika</label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <select name="narkotika" class="form-control">
                          <option value=""></option>
                          <option value="n" <?php if ($_POST['narkotika'] == "n") echo "selected"; ?>>Tidak</option>
                          <option value="y" <?php if ($_POST['narkotika'] == "y") echo "selected"; ?>>Ya</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Item Psikotropika</label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <select name="psikotropika" class="form-control">
                          <option value=""></option>
                          <option value="n" <?php if ($_POST['psikotropika'] == "n") echo "selected"; ?>>Tidak</option>
                          <option value="y" <?php if ($_POST['psikotropika'] == "y") echo "selected"; ?>>Ya</option>
                        </select>
                      </div>
                    </div>
                    <div class="ln_solid"></div>

                </div>
              </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Foto</h2>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <table width="100%" border="0" cellpadding="2" cellspacing="2" rowspan="3">
                        <tr>
                          <td>
                            <img hspace="2" width="100" height="100" name="img_item_item" id="img_item_item" src="<?php echo $fotoName; ?>" valign="middle" border="1">
                            <input type="hidden" name="item_pic" id="item_pic" value="<?php echo $_POST["item_pic"]; ?>">
                            <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="submit">
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <button class="submit" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar</button>
                            <span id="loading" style="display:none;"><img width="25" height="25" id="imgloading" src="<?php echo $ROOT; ?>gambar/loading.gif"></span>
                          </td>
                        </tr>
                      </table>
                    </div>
                  </div>
                  <div class="ln_solid"></div>
                </div>
              </div>
            </div>


            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Detail</h2>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <div class="form-group">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Satuan Beli</label>
                      <div class="col-md-10 col-sm-10 col-xs-10">
                        <?php echo GetComboSatuanBeli(); ?>
                      </div>
                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <a href="<?php echo $PageSatuanBeli; ?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Satuan Beli"><img src="<?php echo $ROOT; ?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Beli" alt="Tambah Satuan Beli" /></a>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Satuan Jual</label>
                      <div class="col-md-10 col-sm-10 col-xs-10">
                        <?php echo GetComboSatuanJual(); ?>
                      </div>
                      <div class="col-md-1 col-sm-1 col-xs-1">
                        <a href="<?php echo $PageSatuanJual; ?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Satuan Juak"><img src="<?php echo $ROOT; ?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Jual" alt="Tambah Satuan Jual" /></a>
                      </div>
                    </div>
                  </div>
                  <div class="clearfix"></div>
                  <br>
                  <?php

                      $hargabeli = ($_POST["item_harga_beli"]) ? $_POST["item_harga_beli"] : 0;
                    
                      $sql = "select margin_nilai from apotik.apotik_margin
                              where id_grup_item = " . QuoteValue(DPE_CHAR, $_POST["id_kategori"]) . "
                              and is_aktif ='Y' and " . $hargabeli . " >= harga_min and " . $hargabeli .
                              " <= harga_max ";
                      
                      $rs = $dtaccess->Execute($sql);
                      $margin = $dtaccess->Fetch($rs);


                  ?>


                  <div class="form-group">
                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>Harga Pokok dari Supplier</label>
                      <!-- <input type="text" name="item_hpp" class="form-control" value=""> -->
                      <input name="item_hpp" id="item_hpp" class="form-control" value="<?=currency_format($_POST["item_hpp"])?>" <?=($kode) ? "" : "readonly"?>>
                    </div>
                    <?php
                    if($_POST["obat_flag"] == 'g'){
                        $dsk = $_POST["item_hpp"] * $diskon['faktur_item_diskon_persen'] / 100;
                        $diskonRp = $_POST["item_hpp"] - ($_POST["item_hpp"] * $diskon['faktur_item_diskon_persen'] / 100);
                        $_POST["item_harga_diskon"] = ($_POST["item_harga_diskon"] >= $_POST["item_hpp"]) ? $_POST["item_hpp"] : $_POST["item_harga_diskon"];
                        $_POST["item_hpp"] = $_POST["item_harga_diskon"];
                      }
                      $ppn = $_POST["item_hpp"] * $diskon['faktur_item_ppn_persen'] / 100;
                      $_POST["item_harga_beli"] = $_POST["item_hpp"] + $ppn;
                      $hrgmargin = $_POST["item_hpp"] * ($margin['margin_nilai']/100);
                      $_POST["item_harga_jual"] = $_POST["item_hpp"] + $hrgmargin;
                    if($_POST["obat_flag"] == 'g'){
                      
                    ?>
                      <div class="col-md-2 col-sm-6 col-xs-12">
	                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>Diskon</label>
	                      <!-- <input type="text" name="item_hpp" class="form-control" value=""> -->
	                      <?php echo $view->RenderTextBox("item_harga_diskon", "item_harga_diskon", "20", "100", currency_format($dsk), "inputField", readonly, null, true); ?>
	                    </div>

	                    <div class="col-md-2 col-sm-6 col-xs-12">
	                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>Harga Pokok Dikurangi Diskon</label>
	                      <!-- <input type="text" name="item_hpp" class="form-control" value=""> -->
	                      <?php echo $view->RenderTextBox("item_harga_diskon", "item_harga_diskon", "20", "100", currency_format($_POST["item_harga_diskon"]), "inputField", readonly, null, true); ?>
	                    </div>
                	<?php } ?>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>PPn Masukan</label>
                      <input type="text" id="ppn_masukan" class="form-control" value="<?=$ppn?>">
                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>Harga Beli</label>
                      <?php echo $view->RenderTextBox("item_harga_beli", "item_harga_beli", "20", "100", currency_format($_POST["item_harga_beli"]), "inputField", null, true); ?>
                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12"><br>Margin</label>
                      <input type="text" id="margin" class="form-control" value="<?=$hrgmargin?>">
                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12" for="first-name"><br>Harga Jual</label>
                      <?php echo $view->RenderTextBox("item_harga_jual", "item_harga_jual", "20", "100", currency_format($_POST["item_harga_jual"]), "inputField", null, true); ?>
                    </div>

                    <div class="col-md-1 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-4 col-xs-12" for="first-name"><br>&nbsp;
                      </label>
                      <button type="button" name="hitung" id="hitung" class="btn btn-success">Hitung</button>
                    </div>
                  </div>
                  <div class="ln_solid"></div>
                </div>

                <div class="form-group">
                  <div class="col-md-4 col-sm-6 col-xs-12">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12">Stok Alert
                    </label>
                    <?php echo $view->RenderTextBox("item_stok_alert", "item_stok_alert", "20", "100", currency_format($_POST["item_stok_alert"]), "inputField", null, true); ?>
                  </div>
                </div>

                <div class="form-group">
                  <div class="col-md-4 col-sm-6- col-xs-12">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12">Perkiraan</label>
                    <input type="text" class="form-control" name="prk_nama" id="prk_nama" value="<?php echo $_POST['prk_nama']; ?>">
                    <input type="hidden" class="form-control" name="prk_id" id="prk_id" value="<?php echo $_POST["prk_id"]; ?>">
                    <input type="hidden" class="form-control" name="prk_no" id="prk_no" value="<?php echo $_POST["prk_no"]; ?>">
                    <a href="<?php echo $findPage12; ?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">
                      <img src="<?php echo $ROOT; ?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>
                  </div>
                </div>

                <div class="ln_solid"></div>
              </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <table width="80%" border="0" cellpadding="1" cellspacing="1">
                  <tr>
                    <td colspan="2" align="center">
                      <?php echo $view->RenderButton(BTN_SUBMIT, ($_x_mode == "Edit") ? "btnUpdate" : "btnSave", "btnSave", "Simpan", "submit", false, "onClick=\"javascript:return CheckDataSave(document.frmEdit);\""); ?>
                      <?php echo $view->RenderButton(BTN_BUTTON, "btnBack", "btnBack", "Kembali", "submit", false, "onClick=\"document.location.href='" . $kembali . "';\""); ?>
                    </td>
                  </tr>
                </table>
              </div>
            </div>


            <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"]; ?>" />
            <script>
              document.frmEdit.item_kode.focus();
            </script>
           
            <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
              <?php echo $view->RenderHidden("item_id", "item_id", $itemId); ?>
            <? } ?>
            <?php echo $view->RenderHidden("x_mode", "x_mode", $_x_mode); ?>
            </form>
          </div>
          <!-- /page content -->

          <!-- footer content -->
          <?php require_once($LAY . "footer.php") ?>
          <!-- /footer content -->
        </div>
        <script>
          
          var margin_nilai = <?= ($margin["margin_nilai"]) ? $margin["margin_nilai"] : 0 ?>;
          // supaya nilai marginya sesuai drop down
          $('#id_kategori').on('change', function() {
            var happ = $('#item_hpp').val().replace(',', '');
            $.ajax({
              url: "get_margin.php?id_kategori=" + $(this).val() + '&hargabeli=' + happ,
              success: function(r) {
                margin_nilai = r;
              }
            })
          });

          $(document).ready(function(){
            <?php if($_POST["obat_flag"] == 'g') { ?>
          		var hpp = $('#item_harga_diskon').val().replace(',', '');
          	<?php } 
          	else { ?>
            	var hpp = $('#item_hpp').val().replace(',', '');
            <?php } ?>


          });

          $('#hitung').on('click', function() {

          	<?php if($_POST["obat_flag"] == 'g') { ?>
          		var hpp = $('#item_harga_diskon').val().replace(',', '');
          	<?php } 
          	else { ?>
            	var hpp = $('#item_hpp').val().replace(',', '');
            <?php } ?>

            var hrg_jual = $('#item_harga_jual').val().replace(',', '');

            var ppn = <?=$diskon['faktur_item_diskon_persen']?> / 100 * parseFloat(hpp);

            $('#ppn_masukan').val(formatCurrency(ppn));
            $('#item_harga_beli').val(formatCurrency(parseFloat(hpp) + ppn));

            var hrg_beli = $('#item_hpp').val().replace(',', '');
            var margin = parseFloat(margin_nilai) / 100 * parseFloat(hrg_beli);
            var hrgjual = (margin + parseFloat(hrg_beli));
            $('#margin').val(formatCurrency(margin));
            // alert(parseInt(hpp) + ppn);
            $('#item_harga_jual').val(formatCurrency(hrgjual));
          });
        </script>
      </div>

      <?php require_once($LAY . "js.php") ?>

</body>

</html>