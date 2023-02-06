<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/tampilan.php");
     
   // if(!$auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_READ)){
   //        die("access_denied");
   //        exit(1);

   //   } elseif($auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_READ)===1){
   //        echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
   //        exit(1);
   //   } 
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $table = new InoTable("table","100%","center");
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
     $poliId = $auth->IdPoli();
     $userName = $auth->GetUserName();
     
     if($_GET["id_deposit"]) $_POST["id_deposit"] = $_GET["id_deposit"];
     if($_GET["id_cust_usr"]) $_POST["id_cust_usr"] = $_GET["id_cust_usr"];
     if($_GET["id"]) $_POST["id_deposit_history"] = $_GET["id"];
     if($_GET["id_multipayment"]) $_POST["id_multipayment"] = $_GET["id_multipayment"];
                 
     $thisPage = "deposit_masuk_edit.php?id=".$_POST["id_deposit_history"]."&id_deposit=".$_POST["id_deposit"]."&id_cust_usr=".$_POST["id_cust_usr"]."&id_multipayment=".$_POST["id_multipayment"];
     $findPage = "pasien_find.php?";

     if ($_GET['del']) {
       $sql = "select * from klinik.klinik_deposit where id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET['id_cust_usr']);
       $dataDeposit = $dtaccess->Fetch($sql);

       $sql = "select * from klinik.klinik_deposit_history where deposit_history_id = ".QuoteValue(DPE_CHAR,$_GET['id']);
       $dataDepositHistory = $dtaccess->Fetch($sql);

       $DepositSekarang = $dataDeposit['deposit_nominal'];
       $HistoryDeposit = $dataDepositHistory['deposit_history_nominal'];

       $Back = $DepositSekarang - $HistoryDeposit;

       $sql = "update klinik.klinik_deposit set deposit_nominal = ".QuoteValue(DPE_NUMERIC, $Back)." where id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET['id_cust_usr']);
       $result = $dtaccess->Execute($sql);

       $sql = "delete from klinik.klinik_deposit_history where deposit_history_id = ".QuoteValue(DPE_CHAR, $_GET['id']);
       $result = $dtaccess->Fetch($sql);

       $sql = "delete from gl.gl_buffer_transaksi where id_pembayaran_det = ".QuoteValue(DPE_CHAR,$dataDepositHistory['id_multipayment']);
       $result = $dtaccess->Execute($sql);

      header('Location:deposit_masuk_view.php');
     }
     
     if($_POST["btnLanjut"] || $_GET["kode"] || $_GET["id_cust_usr"]) {
     
     if($_GET["kode"]) $_POST["cust_usr_kode"]=$_GET["kode"];
         
         if(!$_POST["id_cust_usr"]) {
         echo "<script>alert('Harus Pilih Pasien Dahulu');</script>;"; 
         echo "<script>document.location.href='".$thisPage."';</script>;";
         } 
         
        $sql = "select * from global.global_customer_user where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $rs = $dtaccess->Execute($sql);
        $dataPasien = $dtaccess->Fetch($rs);
        
        $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];
        $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
        $_POST["cust_usr_nama"] = $dataPasien["cust_usr_nama"];
        $_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];   
        $_POST["id_reg"] = $_POST["id_reg"];   
        
     }
       $sql = "select * from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$_GET['id_multipayment'])." and multipayment_urut = '2'";
       $dataJenis2 = $dtaccess->Fetch($sql);

       $sql = "select * from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$_GET['id_multipayment'])." and multipayment_urut = '3'";
       $dataJenis3 = $dtaccess->Fetch($sql);
     
     if($_GET["id"]){
        $_x_mode = "Edit";
        $_POST["id_deposit_history"] = $_GET["id"];
        $_POST["id_deposit_history2"] = $dataJenis2["deposit_history_id"];
        $_POST["id_deposit_history3"] = $dataJenis3["deposit_history_id"];
        $_POST["id_multipayment"] = $_POST["id_multipayment"];
      
            
        $sql = "select * from klinik.klinik_deposit_history a 
                left join klinik.klinik_deposit b on b.id_cust_usr=a.id_cust_usr 
                where id_multipayment=".QuoteValue(DPE_CHAR,$_GET["id_multipayment"]);
        $rs = $dtaccess->Execute($sql);
        $dataEdit = $dtaccess->Fetch($rs);
        //echo $sql;
        //$_POST["deposit_history_nominal"] = $dataEdit["deposit_history_nominal"];
        $_POST["deposit_history_nominal_lama"] = $dataEdit["deposit_history_nominal"];
        $_POST["deposit_history_nominal_sisa"] = $dataEdit["deposit_history_nominal_sisa"];
        $_POST["deposit_history_nominal_sisa_lama"] = $dataEdit["deposit_history_nominal_sisa"];
        $_POST["deposit_nominal"] = $dataEdit["deposit_nominal"];
        $_POST["deposit_tgl"] = $dataEdit["deposit_tgl"];
        $_POST["deposit_history_tgl"] = $dataEdit["deposit_history_tgl"];
        $_POST["id_multipayment"] = $dataEdit["id_multipayment"];
     }
      
        $sql = "select * from klinik.klinik_deposit where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $rs = $dtaccess->Execute($sql);
        $dataDeposit = $dtaccess->Fetch($rs);  
      if($_POST["btnSave"] || $_POST["btnUpdate"]){
        $_POST["id_deposit"] = & $_POST["id_deposit"];
        $_POST["id_deposit_history"] = & $_POST["id_deposit_history"];
        $_POST["id_multipayment"] = & $_POST["id_multipayment"];
        $_POST["id_cust_usr"] = & $_POST["id_cust_usr"];
        
        
        if(!$dataDeposit){
          $dbTable = "klinik.klinik_deposit";
          $dbField[0] = "deposit_id";
          $dbField[1] = "id_cust_usr";
          $dbField[2] = "deposit_when_update";
          $dbField[3] = "deposit_who_update";
          $dbField[4] = "id_dep";
          $dbField[5] = "deposit_tgl";
          
          $_POST["id_deposit"] = $dtaccess->GetTransId();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_deposit"]);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[3] = QuoteValue(DPE_CHAR,$userName);
          $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d"));
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          $dtmodel->Insert() or die("insert  error");
                       
          unset($dbField);
          unset($dtmodel);
          unset($dbValue);
          unset($dbKey);
        }
        
        $dbTable = "klinik.klinik_deposit_history";
        $dbField[0] = "deposit_history_id";
        $dbField[1] = "id_cust_usr";
        $dbField[2] = "id_dep";
        $dbField[3] = "deposit_history_nominal";
        $dbField[4] = "deposit_history_nominal_sisa";
        $dbField[5] = "deposit_history_who_create";
        $dbField[6] = "deposit_history_ket";
        $dbField[7] = "id_jbayar";
        $dbField[8] = "id_multipayment";
        $dbField[9] = "multipayment_urut";
        $dbField[10] = "deposit_history_flag";
        if($_POST["btnSave"]){
        $dbField[11] = "deposit_history_when_create";
        $dbField[12] = "deposit_history_tgl";
        $dbField[13] = "deposit_history_no_bukti";
        $dbField[14] = "id_reg";
        }
        
        $sql = "select deposit_history_no_bukti as kode from klinik.klinik_deposit_history 
                where id_dep=".QuoteValue(DPE_CHAR,$depId)." and deposit_history_no_bukti like 'TM-%' 
                order by deposit_history_when_create desc";
        $lastKode = $dtaccess->Fetch($sql);
        $kode = explode("-",$lastKode["kode"]);  
        $noBukti = "TM-".str_pad($kode[1]+1,6,"0",STR_PAD_LEFT);
        
        $ket = "Titip Uang a.n ".$_POST["cust_usr_nama"]." (".$_POST["cust_usr_kode"].")";
        
        $nominal = StripCurrency($_POST["deposit_history_nominal_sisa_lama"])-$_POST["deposit_history_nominal_lama"]+StripCurrency($_POST["jbayar_jumlah1"]);
        //echo $_POST["deposit_history_nominal_sisa_lama"]."-".$_POST["deposit_history_nominal_lama"]."-".$_POST["deposit_history_nominal"];
        
        if(!$_POST["id_deposit_history"]){
          $_POST["id_deposit_history"] = $dtaccess->GetTransId();
          $idMultipayment = $dtaccess->GetTransId();
        } else {
          $_POST["id_deposit_history"] = $_POST["id_deposit_history"];
          $idMultipayment = $_POST['id_multipayment'];
        }
        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_deposit_history"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah1"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($nominal));
        $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[6] = QuoteValue(DPE_CHAR,$ket);
        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST['id_jbayar1']);
        $dbValue[8] = QuoteValue(DPE_CHAR,$idMultipayment);
        $dbValue[9] = QuoteValue(DPE_CHAR,1);
        $dbValue[10] = QuoteValue(DPE_CHAR,'M');
        if($_POST["btnSave"]){
        $dbValue[11] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[12] = QuoteValue(DPE_DATE,date("Y-m-d"));
        $dbValue[13] = QuoteValue(DPE_CHAR,$noBukti);
        $dbValue[14] = QuoteValue(DPE_CHAR,$_POST['id_reg']);
        }
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        //echo $_POST['id_multipayment'];die();
        
        if($_POST["btnSave"]){             
        $dtmodel->Insert() or die("insert  error");
        } else {
        $dtmodel->Update() or die("update  error");
        }
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);

      if ($_POST['jbayar_jumlah2']!='0') {
        $dbTable = "klinik.klinik_deposit_history";
        $dbField[0] = "deposit_history_id";
        $dbField[1] = "id_cust_usr";
        $dbField[2] = "id_dep";
        $dbField[3] = "deposit_history_nominal";
        $dbField[4] = "deposit_history_nominal_sisa";
        $dbField[5] = "deposit_history_who_create";
        $dbField[6] = "deposit_history_ket";
        $dbField[7] = "id_jbayar";
        $dbField[8] = "id_multipayment";
        $dbField[9] = "multipayment_urut";
        $dbField[10] = "deposit_history_flag";
        if($_POST["id_deposit_history2"]==''){
        $dbField[11] = "deposit_history_when_create";
        $dbField[12] = "deposit_history_tgl";
        $dbField[13] = "deposit_history_no_bukti";
        }

        if($_POST["id_deposit_history2"]==''){
          $a = $dtaccess->GetTransId();
        } else {
          $a = $_POST["id_deposit_history2"];
        }
        $saldo = $nominal+StripCurrency($_POST['jbayar_jumlah2']);
        //echo 'nominal : '.$nominal.'  jbayar2 : '.StripCurrency($_POST['jbayar_jumlah2']);
        //die();
        $dbValue[0] = QuoteValue(DPE_CHAR,$a);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah2"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($saldo));
        $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[6] = QuoteValue(DPE_CHAR,$ket);
        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST['id_jbayar2']);
        $dbValue[8] = QuoteValue(DPE_CHAR,$idMultipayment);
        $dbValue[9] = QuoteValue(DPE_CHAR,2);
        $dbValue[10] = QuoteValue(DPE_CHAR,'M');
        if($_POST["id_deposit_history2"]==''){
        $dbValue[11] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[12] = QuoteValue(DPE_DATE,date("Y-m-d"));
        $dbValue[13] = QuoteValue(DPE_CHAR,$noBukti);
        }
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        // print_r($dbValue); die();
        
        if($_POST["id_deposit_history2"]==''){             
        $dtmodel->Insert() or die("insert  error");
        } else {
        $dtmodel->Update() or die("update  error");
        }
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);

      }

      if ($_POST['jbayar_jumlah3']!='0') {
        $dbTable = "klinik.klinik_deposit_history";
        $dbField[0] = "deposit_history_id";
        $dbField[1] = "id_cust_usr";
        $dbField[2] = "id_dep";
        $dbField[3] = "deposit_history_nominal";
        $dbField[4] = "deposit_history_nominal_sisa";
        $dbField[5] = "deposit_history_who_create";
        $dbField[6] = "deposit_history_ket";
        $dbField[7] = "id_jbayar";
        $dbField[8] = "id_multipayment";
        $dbField[9] = "multipayment_urut";
        $dbField[10] = "deposit_history_flag";
        if($_POST["id_deposit_history3"]==''){
        $dbField[11] = "deposit_history_when_create";
        $dbField[12] = "deposit_history_tgl";
        $dbField[13] = "deposit_history_no_bukti";
        }

        if($_POST["id_deposit_history3"]==''){
          $b = $dtaccess->GetTransId();
        } else {
          $b = $_POST["id_deposit_history3"];
        }
        $saldo2 = $saldo+StripCurrency($_POST['jbayar_jumlah3']);
        //echo $saldo;
        $dbValue[0] = QuoteValue(DPE_CHAR,$b);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah3"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($saldo2));
        $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[6] = QuoteValue(DPE_CHAR,$ket);
        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST['id_jbayar3']);
        $dbValue[8] = QuoteValue(DPE_CHAR,$idMultipayment);
        $dbValue[9] = QuoteValue(DPE_CHAR,3);
        $dbValue[10] = QuoteValue(DPE_CHAR,'M');
        if($_POST["id_deposit_history3"]==''){
        $dbValue[11] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[12] = QuoteValue(DPE_DATE,date("Y-m-d"));
        $dbValue[13] = QuoteValue(DPE_CHAR,$noBukti);
        }
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        //print_r($dbValue); die();
        
        if($_POST["id_deposit_history3"]==''){             
        $dtmodel->Insert() or die("insert  error");
        } else {
        $dtmodel->Update() or die("update  error");
        }
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);

      }

      require_once('posting_gl.php');
        
        $sql = "select sum(deposit_history_nominal) as total from klinik.klinik_deposit_history where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $rs = $dtaccess->Execute($sql);
        $total = $dtaccess->Fetch($rs);
        
        $sql = "update klinik.klinik_deposit set deposit_nominal=".QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]))."
                where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dtaccess->Execute($sql);
        
        $cetak = "y";
      }

       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
       $dataJenisBayar2= $dtaccess->FetchAll($sql); 

       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' and jbayar_id <> '01' order by jbayar_id asc";
       $dataJenisBayar3= $dtaccess->FetchAll($sql); 

       $sql = "select * from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$_GET['id_multipayment'])." and multipayment_urut = '1'";
       $dataJenis1 = $dtaccess->Fetch($sql);
       $sql = "select * from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$_GET['id_multipayment'])." and multipayment_urut = '2'";
       $dataJenis2 = $dtaccess->Fetch($sql);
       $sql = "select * from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$_GET['id_multipayment'])." and multipayment_urut = '3'";
       $dataJenis3 = $dtaccess->Fetch($sql);

        //ambil nama poli
      $sql = "select b.poli_nama, b.poli_id from  global.global_auth_poli b where id_dep like '".$depId."%'";    
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs_edit);
  
       //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs); 
     
        //Data Klinik
     $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
      $lokasi = $ROOT."/gambar/foto_pasien";   
      if($_POST["foto_pas"]) $fotoName = $lokasi."/".$_POST["foto_pas"];
      else $fotoName = $lokasi."/default.jpg";  
           
              
      if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_rekammedik.xls');
      }
      
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
      }  
 
 
    //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
        

?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/kinetic-v3.js"></script>

<script type="text/javascript">
$(document).ready(function() {
$("a[rel=sepur]").fancybox({
'width' : '50%',
'height' : '100%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script>

<script type="text/javascript">
$(document).ready(function() {
$("a#lap").fancybox({
  'overlayShow' : true,
  'transitionIn'  : 'elastic',
  'transitionOut' : 'elastic',
  'overlayColor' : '#111'      
});
}); 
</script>

<script language="JavaScript">

var _wnd_new;

function GantiPengurangan(totalbayar) 
{
     var bayar1 = document.getElementById('jbayar_jumlah1').value.toString().replace(/\,/g,"");
     var bayar2 = document.getElementById('jbayar_jumlah2').value.toString().replace(/\,/g,"");
     var bayar3 = document.getElementById('jbayar_jumlah3').value.toString().replace(/\,/g,"");
     var sisa = document.getElementById('deposit_history_nominal_sisa_lama').value.toString().replace(/\,/g,"");
     var total;
     var tunai = $('#id_jbayar1').val();

     total1 = bayar1 * 1;
     total2 = bayar2 * 1;
     total3 = bayar3 * 1;
     sisadeposit = sisa * 1;

     total = sisadeposit + total1 + total2 + total3;

     document.getElementById('total_titip').value = formatCurrency(total);
 
}

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
}

  function rejenis(kliniks) {
   document.location.href='pasien_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
<?php if($cetak=="y"){ ?> 
  BukaWindow('deposit_masuk_cetak.php?id_cust_usr=<?php echo $_POST["id_cust_usr"];?>&id=<?php echo $_POST["id_deposit_history"];?>&id_multipayment=<?php echo $idMultipayment;?>&id_deposit=<?php echo $_POST["id_deposit"];?>&klinik=<?php echo $_POST["klinik"];?>', '_blank');
  //document.location.href='rm_cetak.php?cust_usr_kode=<?php echo $_POST["cust_usr_kode"];?>&klinik=<?php echo $_POST["klinik"];?>';
  document.location.href='deposit_masuk_view.php';
<?php } ?>
  
</script>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Deposit Masuk</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<body>
  <?php if(!$dataPasien) { ?>

    <div id="body">
      <div id="scroller">
      <br />
        <form name="frmView" method="POST" action="<?php echo $thisPage; ?>" onSubmit="return CheckSimpan(this);">
          <table align="center" border=0 cellpadding=1 cellspacing=1 width="100%" >
            <tr>
              <td width= "10%" align="left" class="tablecontent">No. RM</td>
                  <td width= "50%" align="left" class="tablecontent-odd">
                    <input type="text" name="cust_usr_kode" id="cust_usr_kode" readonly size="35" maxlength="35" value="<?php echo $_POST["cust_usr_kode"];?>" align="left"/>
                      <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Pasien" alt="Cari Pasien" class="tombol" align="middle"/></a>
                      <!-- <input type="submit" name="btnLanjut" value="Lanjut" class="submit" align="right"/>      -->
                    <input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $_POST["cust_usr_id"];?>" />
                    <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
                    <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>" />
                    <input type="hidden" name="id_deposit" id="id_deposit" value="<?php echo $_POST["id_deposit"];?>" />
                    <input type="hidden" name="id_deposit_history" id="id_deposi_history" value="<?php echo $_POST["id_deposit_history"];?>" />
                    <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $_POST["id_reg"];?>" />
                      <?php if(!$dataPasien && $_POST["btnLanjut"]){  ?>
                        <font color="red">* Data pasien tidak ditemukan !</font>
                      <?php } ?>
                  </td>
        </div>
            </tr>
  
              <tr>
                  <td class="tablecontent">&nbsp;</td>
                  <td class="tablecontent-odd" colspan="6">
                    <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary" />
                  </td>
              </tr>
        </table>
          <br />  
      </form>         
  <?php } ?>

  <?php if($dataPasien && ($_POST["btnLanjut"] || $_GET["kode"] || $_GET["id"])) { ?>
        <form name="frmEdit" method="POST" action="<?php echo $thisPage; ?>">
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td width="100%">
      <div id="kasir">
      <table width="100%" border="0" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "10%" align="center" class="tablecontent" rowspan="4"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               <td width= "15%" align="left" class="tablecontent">No. RM</td>
               <td width= "65%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr> 
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "65%" align="left" class="tablecontent-odd"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Alamat</td>
               <td width= "65%" align="left" class="tablecontent-odd"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
<!--           <tr>
           <td width= "15%" align="left" class="tablecontent-odd"><b>&nbsp;&nbsp;&nbsp;Jumlah Uang Titip</b> </td><
           <td class="tablecontent" width="40%">&nbsp;&nbsp;
                <?php echo $view->RenderTextBox("deposit_history_nominal","deposit_history_nominal","30","30",currency_format($dataEdit["deposit_history_nominal"]),"curedit", "",true,null); ?> </td>
           </tr>      -->    
           <tr>
             <td width="15%" align="left" class="tablecontent-odd">Jumlah Uang Titip</td>
             <td><input type="text" name="total_titip" id="total_titip" value="<?php echo currency_format($dataDeposit['deposit_nominal']); ?>" readonly class="form-control" onchange="GantiPengurangan(this);"></td>
             <td width="65%">&nbsp;</td>
           </tr>  
        </table>
        <table width="100%" border="0">  
           <tr>
            <td width="5%">&nbsp;</td>
             <td width="5%%">Jenis Bayar 1</td>
             <td width="5%">
              <select name="id_jbayar1" class="select2_single form-control" id="id_jbayar1" onKeyDown="return tabOnEnter(this, event);">    
                  <?php //if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php //} ?>
                    <?php $counter = -1;
                    for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++)
                     {
                      unset($spacer); 
                      $length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
                      for($j=0;$j<$length;$j++) $spacer .= "..";  
                        ?>                                                                      
                    <option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"];?>" <?php if($dataJenis1["id_jbayar"]==$dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"];?></option>
                <?php } ?>
             </select>
             </td>
             <td width="1%"></td>
             <td width="5%">
              <!-- <input type="text" name="jbayar_jumlah1" id="jbayar_jumlah1" value="<?php echo currency_format($dataJenis1['deposit_history_nominal']); ?>" onchange="GantiPengurangan(this)" class="form-control"> -->
              <?php echo $view->RenderTextBox("jbayar_jumlah1","jbayar_jumlah1","30","30",currency_format($dataJenis1['deposit_history_nominal']),"curedit", $tipeText,true,'onChange=GantiPengurangan(this);');?>
            </td>
             <td width="20%">&nbsp;</td>
           </tr>
           <tr>
             <td colspan="6">&nbsp;</td>
           </tr>
           <tr>
            <td width="5%">&nbsp;</td>
             <td width="5%%">Jenis Bayar 2</td>
             <td width="5%">
              <select name="id_jbayar2" class="select2_single form-control" id="id_jbayar2" onKeyDown="return tabOnEnter(this, event);">    
                  <?php //if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php //} ?>
                    <?php $counter = -1;
                    for($i=0,$n=count($dataJenisBayar3);$i<$n;$i++)
                     {
                      unset($spacer); 
                      $length = (strlen($dataJenisBayar3[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
                      for($j=0;$j<$length;$j++) $spacer .= "..";  
                        ?>                                                                      
                    <option value="<?php echo $dataJenisBayar3[$i]["jbayar_id"];?>" <?php if($dataJenis2["id_jbayar"]==$dataJenisBayar3[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar3[$i]["jbayar_nama"];?></option>
                <?php } ?>
             </select>
             </td>
             <td width="1%"></td>
             <td width="5%">
              <!-- <input type="text" name="jbayar_jumlah2" id="jbayar_jumlah2" value="<?php echo currency_format($dataJenis2['deposit_history_nominal']); ?>" onchange="GantiPengurangan(this)" class="form-control"> -->
              <?php echo $view->RenderTextBox("jbayar_jumlah2","jbayar_jumlah2","30","30",currency_format($dataJenis2['deposit_history_nominal']),"curedit", $tipeText,true,'onChange=GantiPengurangan(this);');?>
              </td>
             <td width="20%">&nbsp;</td>
           </tr>
           <tr>
             <td colspan="6">&nbsp;</td>
           </tr>
           <tr>
            <td width="5%">&nbsp;</td>
             <td width="5%%">Jenis Bayar 3</td>
             <td width="5%">
              <select name="id_jbayar3" class="select2_single form-control" id="id_jbayar3" onKeyDown="return tabOnEnter(this, event);">    
                  <?php //if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php //} ?>
                    <?php $counter = -1;
                    for($i=0,$n=count($dataJenisBayar3);$i<$n;$i++)
                     {
                      unset($spacer); 
                      $length = (strlen($dataJenisBayar3[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
                      for($j=0;$j<$length;$j++) $spacer .= "..";  
                        ?>                                                                      
                    <option value="<?php echo $dataJenisBayar3[$i]["jbayar_id"];?>" <?php if($dataJenis3["id_jbayar"]==$dataJenisBayar3[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar3[$i]["jbayar_nama"];?></option>
                <?php } ?>
             </select>
             </td>
             <td width="1%"></td>
             <td width="5%">
              <!-- <input type="text" name="jbayar_jumlah3" id="jbayar_jumlah3" value="<?php echo currency_format($dataJenis3['deposit_history_nominal']); ?>" onchange="GantiPengurangan(this)" class="form-control"> -->
              <?php echo $view->RenderTextBox("jbayar_jumlah3","jbayar_jumlah3","30","30",currency_format($dataJenis3['deposit_history_nominal']),"curedit", $tipeText,true,'onChange=GantiPengurangan(this);');?>
             </td>
             <td width="20%">&nbsp;</td>
           </tr>
              <br><br>
          <tr>
               <td width= "50%" align="center" class="tablecontent" colspan="7">
               <table width="100%" border="0">
               <tr>
               <td width="50%" align="left">&nbsp;</td>
               <td width="50%" align="center">
               <?php if($_GET["id"]){ ?>
               <input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="btn btn-primary" onClick="javascript:return CekData();"/>
               <?php } else { ?>
               <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="btn btn-primary" onClick="javascript:return CekData();"/>
               <?php } ?>     
               <input type="button" name="kembali" id="kembali" value="Kembali" class="btn btn-default" onClick="document.location.href='deposit_masuk_view.php'";/>     
               </td>
               </tr>
               </table>
          </td>
          </tr>
           
     </table>
          </div>
              <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>" />
              <input type="hidden" name="id_deposit" id="id_deposit" value="<?php echo $_POST["id_deposit"];?>" />
              <input type="hidden" name="id_deposit_history" id="id_deposit_history" value="<?php echo $_POST["id_deposit_history"];?>" />
              <input type="hidden" name="deposit_history_nominal_lama" id="deposit_history_nominal_lama" value="<?php echo $_POST["deposit_history_nominal_lama"];?>" />
              <input type="hidden" name="deposit_history_nominal_sisa_lama" id="deposit_history_nominal_sisa_lama" value="<?php echo currency_format($dataDeposit["deposit_nominal"]);?>" /> 
              <input type="hidden" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $_POST["cust_usr_kode"]; ?>">
              <input type="hidden" name="cust_usr_nama" id="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"]; ?>">
              <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"]; ?>">
              <input type="hidden" name="id_multipayment" id="id_multipayment" value="<?php echo $_GET["id_multipayment"]; ?>">
              <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $_POST["id_reg"]; ?>">
        </tr>
  </table>      
  </form>                                                             
    <?php } ?>
    </div>
    </div>
    
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
