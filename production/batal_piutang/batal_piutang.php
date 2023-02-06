<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."currency.php");
require_once($LIB."tampilan.php");


$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$table = new InoTable("table","100%","left");
$depId = $auth->GetDepId();
$thisPage = "report_setoran_cicilan.php";
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$lokasi = $ROOT."/gambar/img_cfg";

     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;


if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
  echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
  exit(1);
} 


       //$sql = "select * from  klinik.klinik_split where (split_flag = ".QuoteValue(DPE_CHAR,SPLIT_TINDAKAN)." or split_flag = ".QuoteValue(DPE_CHAR,SPLIT_PERAWATAN)." or split_flag = ".QuoteValue(DPE_CHAR,SPLIT_REGISTRASI).") and id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by split_flag asc ";
     //$rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     //$dataSplit = $dtaccess->FetchAll($rs);

if(!$_POST["klinik"]) $_POST["klinik"]=$depId;
else $_POST["klinik"] = $_POST["klinik"];    

       // KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

$skr = date("d-m-Y");
$time = date("H:i:s");

if(!$_POST['tgl_awal']){
   $_POST['tgl_awal']  = $skr;
}
if(!$_POST['tgl_akhir']){
   $_POST['tgl_akhir']  = $skr;
}

if($_GET["batal"]){
    $sql = "select sum(deposit_history_nominal) as total,id_cust_usr from klinik.klinik_deposit_history where deposit_history_flag != 'M' and
    id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["id_pembayaran"])." group by id_cust_usr";
        // echo $sql;
    $dataDeposit = $dtaccess->Fetch($sql);

    $SaldoDeposit = str_replace('-', '', $dataDeposit['total']);


        //$nilaiDeposit =  $dataDeposit["deposit_history_nominal"]
        //UPDATE KLINIK DEPOSIT
    $sql = "update klinik.klinik_deposit set deposit_nominal = ".QuoteValue(DPE_NUMERIC,$SaldoDeposit)." where id_cust_usr =".QuoteValue(DPE_CHAR,$dataDeposit["id_cust_usr"]);
        // echo $sql;die();
    $dtaccess->Execute($sql);

        //HAPUS DEPOSIT HISTORY
    $sql = "delete from klinik.klinik_deposit_history where deposit_history_flag != 'M' and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
    $dtaccess->Execute($sql);

    $sql = "select fol_id from klinik.klinik_folio where
               id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);/*."
               and id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);*/
               $dataFolio = $dtaccess->FetchAll($sql);

               for($i=0,$n=count($dataFolio);$i<$n;$i++)
               {  
                $sqlTerbayar = "update apotik.apotik_penjualan set 
                penjualan_terbayar = 'n' where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);      
                $dtaccess->Execute($sqlTerbayar);
                unset($sqlTerbayar);
            } 

            $sql = "update klinik.klinik_folio set fol_lunas='n' where 
        id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);/*."
        and id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);*/
        $dtaccess->Execute($sql,DB_SCHEMA);
        
        $sql = "select * from klinik.klinik_pembayaran_det where
        pembayaran_det_id=".QuoteValue(DPE_CHAR,$_GET["id_pembayaran_det"]);
        $rs = $dtaccess->Execute($sql);
        $dataDet = $dtaccess->Fetch($rs);
        //echo $sql;
        //die();
        
        $sql = "select * from klinik.klinik_pembayaran where 
        pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPemb = $dtaccess->Fetch($rs);
        
        
        
        $sql = "select * from global.global_customer_user where 
        cust_usr_id=".QuoteValue(DPE_CHAR,$dataPemb["id_cust_usr"]);
        $rs = $dtaccess->Execute($sql);
        $dataCust = $dtaccess->Fetch($rs);
        
        $total = $dataPemb["pembayaran_total"] - $dataDet["pembayaran_det_total"];
        $dibayar = $dataPemb["pembayaran_yg_dibayar"] - $dataDet["pembayaran_det_total"];
        $diskon = $dataPemb["pembayaran_diskon"] - $dataDet["pembayaran_det_diskon"];
        $diskonPersen = $dataPemb["pembayaran_diskon_persen"] - $dataDet["pembayaran_det_diskon_persen"];
        $hrsBayar = $dataPemb["pembayaran_hrs_bayar"] - $dataDet["pembayaran_det_hrs_bayar"];
        $bulat = $dataPemb["pembayaran_pembulatan"] - $dataDet["pembayaran_det_pembulatan"];
        $charge = $dataPemb["pembayaran_service_cash"] - $dataDet["pembayaran_det_service_cash"];
        
        // if($_GET["reg_jenis_pasien"]=="2"){
        $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', pembayaran_total = '0', 
        pembayaran_yg_dibayar = '0', pembayaran_hrs_bayar = '".StripCurrency($hrsBayar)."',
        pembayaran_service_cash='".StripCurrency($charge)."', pembayaran_diskon='".StripCurrency($diskon)."',
        pembayaran_diskon_persen='".StripCurrency($diskonPersen)."', pembayaran_pembulatan='".StripCurrency($bulat)."', 
        pembayaran_appv='n' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $sql="update klinik.klinik_pembayaran set pembayaran_flag='n', pembayaran_hrs_bayar=0, pembayaran_total=0,
        pembayaran_yg_dibayar=0 where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      //echo $sql; die();
        $dtaccess->Execute($sql);

        $sql = "select reg_status from klinik.klinik_registrasi where reg_id=".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
        $rs = $dtaccess->Execute($sql);
        $status = $dtaccess->Fetch($rs);

        $sql="update klinik.klinik_waktu_tunggu set klinik_waktu_tunggu_status='',id_waktu_tunggu_status =''  where id_reg=".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and klinik_waktu_tunggu_status='K1'";
        $rs = $dtaccess->Execute($sql);

        if($status["reg_status"]=="E0" || $status["reg_status"]=="E1" || $status["reg_status"]=="E2" || $status["reg_status"]=="F0" || $status["reg_status"]=="A0" || $status["reg_status"]=="M0" || $status["reg_status"]=="M1"){
          $sql="update klinik.klinik_registrasi set reg_status='E2' where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      } elseif($status["reg_status"]=="G0" || $status["reg_status"]=="G1" || $status["reg_status"]=="G2" || $status["reg_status"]=="G4"){
          $sql="update klinik.klinik_registrasi set reg_status='G2' where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      } else {
          $sql="update klinik.klinik_registrasi set reg_status='I3' where reg_id=".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      }
      //echo $sql; die();
      $dtaccess->Execute($sql);
      
      $sql = "select pembayaran_det_id from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      $detId = $dtaccess->FetchAll($sql);

      for($i=0,$n=count($detId);$i<$n;$i++) {
          $sql = "delete from gl.gl_buffer_transaksi where id_pembayaran_det =".QuoteValue(DPE_CHAR,$detId[$i]["pembayaran_det_id"]);
          $dtaccess->Execute($sql);
      //update fol_lunas jadi n
          $sql = "update klinik.klinik_folio set 
          fol_lunas ='n',
          id_pembayaran_det=null 
          where id_pembayaran_det =".QuoteValue(DPE_CHAR,$detId[$i]["pembayaran_det_id"]);
          $dtaccess->Execute($sql); 

      }
      

      $sql = "delete from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

      $sql = "delete from ar_ap.ar_trans where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET['pembayaran_id']);
      $result = $dtaccess->Execute($sql);
      
      $kembali = "batal_piutang.php";
      header("location:".$kembali);
      exit();
  }

  if(!$_POST["cust_usr_jenis"])  $_POST["cust_usr_jenis"]="0";

  $perusahaan = $_POST["ush_id"];
  $kasir = $_POST["usr_id"];
  
     //$sql_where[] = "reg_tanggal is not null"; 
     // if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "j.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
  if($_POST["tgl_awal"]) $sql_where[] = "date(j.pembayaran_det_tgl) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
  if($_POST["tgl_akhir"]) $sql_where[] = "date(j.pembayaran_det_tgl) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

  if($_POST["cust_usr_kode"])  $sql_where[] = "c.cust_usr_kode like".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
  if($_POST["cust_usr_nama"])  $sql_where[] = "UPPER(c.cust_usr_nama) like".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["cust_usr_nama"]."%"));
     /*if($_POST["js_biaya"]) $sql_where[] = "a.pembayaran_jenis = ".QuoteValue(DPE_CHAR,$_POST["js_biaya"]);
     if($_POST["jbayar"]) $sql_where[] = "a.id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["jbayar"]);
     if($userData["rol"]=='2') { 
            $sql_where[] = " d.id_dokter =".QuoteValue(DPE_CHAR,$userId);
     } else {
            if($_POST["id_dokter"]) $sql_where[] = "d.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     }
     
     if($_POST["reg_shift"]){
        $sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
     }
         
     if($_POST["reg_tipe_layanan"]){
        $sql_where[] = "d.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
     }
     
     if($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"]!="0"){
         $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
       }
       
         if($_POST["ush_id"]){
         $sql_where[] = "d.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["ush_id"]);
       }

       if($_POST["id_poli"]!='--'){
          $sql_where[] = "d.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
       }

       if($_POST["usr_id"]<>"--"){
         $sql_where[] = "j.who_when_update = ".QuoteValue(DPE_CHAR,$_POST["usr_id"]);
     }*/

     /*if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
           $sql_where[] = "j.who_when_update = '".$userName."'";
       }*/
       
       $sql_where = implode(" and ",$sql_where);
       $sql = "select a.*, j.*, cust_usr_kode, cust_usr_nama, tipe_biaya_nama, d.id_dokter, reg_keterangan, 
       usr_name, poli_nama, shift_nama, jenis_nama from klinik.klinik_pembayaran_det j 
       left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
       left join klinik.klinik_registrasi d on d.reg_id = a.id_reg and a.pembayaran_id = d.id_pembayaran
       left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
       left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
       left join global.global_auth_poli f on f.poli_id = d.id_poli
       left join global.global_shift g on g.shift_id = d.reg_shift
       left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
       left join global.global_auth_user i on i.usr_id = d.id_dokter";
       $sql .= " where pembayaran_det_flag<> 'T' and ".$sql_where; 
     //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
       $sql .= " order by j.pembayaran_det_create, j.pembayaran_det_kwitansi, a.pembayaran_id, pembayaran_det_mp_ke asc";
     //echo $sql;
       $dataTable = $dtaccess->FetchAll($sql);

       for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
        if ($dataTable[$i]["id_pembayaran"] == $dataTable[$i - 1]["id_pembayaran"]) {
          $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
      }
  }


  $tableHeader = "Batal Bayar Jaminan";
  if($_POST["btnExcel"]){
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=lap_piutang_perorangan.xls');
}  

if($_POST["btnCetak"]){
        //echo $_POST["ush_id"];
        //die();
    $_x_mode = "cetak" ;      
}

      // cari jenis pasien e
$sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$jenisPasien = $dtaccess->FetchAll($rs);


     // cek nama perusahaan --
$sql = "select * from global.global_jenis_pasien where jenis_id = '7'";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$corporate = $dtaccess->Fetch($rs);

      // cari nama perusahaan --
$sql = "select * from global.global_perusahaan where id_dep =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$NamaPerusahaan = $dtaccess->FetchAll($rs);

      //ambil nama dokter e
$sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_name asc ";
$rs = $dtaccess->Execute($sql);
$dataDokter = $dtaccess->FetchAll($rs);

          //Data Klinik
          //$sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          //$rs = $dtaccess->Execute($sql);
          //$dataKlinik = $dtaccess->FetchAll($rs);

          //echo $sql;
          //$sql = "select dep_nama from global.global_departemen where dep_id = '".$_GET["klinik"]."'";
          //$rs = $dtaccess->Execute($sql);
          //$namaKlinik = $dtaccess->Fetch($rs);
          //$klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];  

$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if($konfigurasi["dep_lowest"]=='n'){
  $sql = "select * from global.global_departemen order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}else if($_POST["klinik"]){
     //Data Klinik
  $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}else{
  $sql = "select * from global.global_departemen where dep_id = '".$depId."' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}

if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;

if($_POST["dep_logo"]) $fotoName = $lokasi."/".$row_edit["dep_logo"];
else $fotoName = $lokasi."/default.jpg"; 
      //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   

        // cari jenis bayar ee //
$sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId);
$jsBayar= $dtaccess->FetchAll($sql);       

      // Data Poli //
$sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama";
$dataPoli = $dtaccess->FetchAll($sql);       

     // cari tipe layanan
$sql = "select * from global.global_tipe_biaya order by tipe_biaya_nama desc";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$tipeBiaya = $dtaccess->FetchAll($rs);

     // cari nama kasir --
$sql = "select * from global.global_auth_user_app a left join global.global_auth_user b on a.id_usr = b.usr_id where id_app = 15";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataKasir = $dtaccess->FetchAll($rs);

?>

<script language="JavaScript">
    function batal() {
      if(confirm('apakah anda yakin akan membatalkan piutang pasien ini???'));
      else return false;
  }
</script>

<?php if(!$_POST["btnExcel"]) { ?>
    <?php //echo $view->RenderBody("module.css",true,true,"BATAL PIUTANG"); ?>
<?php } ?>
<script language="JavaScript">
    function CheckSimpan(frm) {

       if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
      }
  }

  window.onload = function() { TampilCombo(); }
  function TampilCombo(id)
  {        

         //alert(id);
         if(id=="7"){
          ush_id.disabled = false;
              //elm_combo.checked = true; 

          } else {
              ush_id.disabled = true;
          }
      }   

      var _wnd_new;
      function BukaWindow(url,judul)
      {
        if(!_wnd_new) {
            _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
        } else {
            if (_wnd_new.closed) {
                _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
            } else {
                _wnd_new.focus();
            }
        }
        return false;
    }

    <?php if($_x_mode=="cetak"){ ?> 
  //BukaWindow('report_setoran_loket_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>','Pemakaian Kasir');
  //onclick="window.open(this.href); return false";
  window.open('lap_piutang_cetak.php?perusahaan=<?php echo $perusahaan;?>&id_poli=<?php echo $_POST["id_poli"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&shift=<?php echo $_POST["reg_shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&js_biaya=<?php echo $_POST["js_biaya"];?>&jbayar=<?php echo $_POST["jbayar"];?>&kasir=<?php echo $kasir;?>&layanan=<?php echo $_POST["reg_tipe_layanan"]?>', '_blank');
  //document.location.href='tutup_kasir.php';
<?php } ?>


</script>

<?php // echo $view->InitUpload(); ?>
<?php if(!$_POST["btnExcel"]) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<?php } ?>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
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
<body>
    <?php if(!$_POST["btnExcel"]) { ?>
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
                                <h2>Batal Bayar Jaminan</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
                                  <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                                    <div class='input-group date' id='datepicker'>
                                      <input name="tgl_awal" type='text' class="form-control" 
                                      value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
                                      <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                </div>                   

                                <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                                <div class='input-group date' id='datepicker2'>
                                  <input  name="tgl_akhir"  type='text' class="form-control" 
                                  value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
                                  <span class="input-group-addon">
                                    <span class="fa fa-calendar">
                                    </span>
                                </span>
                            </div>             
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                            <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">No RM</label>
                            <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                            <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary">
          <!--<input type="submit" name="btnExcel" value="Export Excel" class="submit">
            <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="submit" />-->
          <!--<input type="submit" name="btnTutup" value="Tutup Kasir" class="submit">
              <input type="submit" name="btnCetak" value="Cetak Ulang" class="submit">-->
          </div>

      </form>

  <?php } ?>
  <?php if($_POST["btnExcel"]) {?>

   <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr class="tableheader">
         <td align="center" colspan="<?php echo (count($dataSplit)+6)?>">
             <strong>LAP. PIUTANG PERORANGAN<br/>
                 <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
                 <br/>
             </strong>
         </td>          
     </tr>
     <tr class="tableheader">
      <td align="left" colspan="<?php echo (count($dataSplit)+6)?>">
          <?php echo $poliNama; ?><br/>
          <?php if($_POST["tgl_awal"]==$_POST["tgl_akhir"]) { echo "Tanggal : ".$_POST["tgl_awal"]; } elseif($_POST["tgl_awal"]!=$_POST["tgl_akhir"]) { echo "Periode : ".$_POST["tgl_awal"]." - ".$_POST["tgl_akhir"]; }  ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php echo "Shift : ".$_POST["shift"]; ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php if($_POST["cust_usr_jenis"]) { echo "Jenis Pasien : ".$bayarPasien[$_POST["cust_usr_jenis"]]; } ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //echo "Nama Poli : ".$dataPoli[$i]["poli_nama"]; ?>

          <br/>
      </td>
  </tr>
</table>
<?php }?>

<table class="table table-bordered">
  <thead>
    <tr>
      <th style="text-align: center;">Batal</th>
      <th style="text-align: center;">No</th>
      <th style="text-align: center;">Waktu</th>
      <th style="text-align: center;">No RM</th>
      <th style="text-align: center;">Nama Pasien</th>
      <th style="text-align: center;">Cara Bayar</th>
      <th style="text-align: center;">Total Piutang</th>
      <th style="text-align: center;">Petugas</th>
      <th style="text-align: center;">Klinik</th>
      <th style="text-align: center;">Dokter</th>
  </tr>
</thead>
<tbody>
    <?php 
    $x = 1;
    $urutan = 0;
    for ($i=0; $i < count($dataTable); $i++) { 
        if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
          $dataSpan = $hitung[$dataTable[$i]["id_pembayaran"]] + 1;
          $baris++;
      }
      $editPage = "batal_piutang.php?batal=1&id_dokter=".$dataTable[$i]["id_dokter"]."&id_reg=".$dataTable[$i]["id_reg"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"];

      $sql = "select * from gl.gl_buffer_transaksi where id_pembayaran_det = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_det_id']);
      $Jurnal = $dtaccess->Fetch($sql);
      ?>
      <tr>
          <?php if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) { ?>
            <?php if ($Jurnal['is_posting'] == 'n') { ?>
              <td align="center" rowspan="<?php echo $dataSpan ?>"><a href="<?php echo $editPage ?>"><img src="<?php echo $ROOT ?>/gambar/hapus.png" alt="Batal" title="Batal"></a></td>
          <?php } else {  ?>
              <td align="center" rowspan="<?php echo $dataSpan ?>">&nbsp;</td>
          <?php } ?>
          <td align="center" rowspan="<?php echo $dataSpan ?>"><?php echo $baris; ?></td>
          <td align="center" rowspan="<?php echo $dataSpan ?>"><?php echo $dataTable[$i]['pembayaran_det_create'] ?></td>
          <td align="center" rowspan="<?php echo $dataSpan ?>"><?php echo $dataTable[$i]['cust_usr_kode'] ?></td>
          <?php if ($dataTable[$i]['cust_usr_kode'] == '100') { ?>
            <td rowspan="<?php echo $dataSpan ?>"><?php echo $dataTable[$i]['reg_keterangan'] ?></td>
        <?php } else { ?>
            <td rowspan="<?php echo $dataSpan ?>"><?php echo $dataTable[$i]['cust_usr_nama'] ?></td>
        <?php } ?>
        <td align="center" rowspan="<?php echo $dataSpan ?>"><?php echo $dataTable[$i]['jenis_nama'] ?></td>
    <?php } ?>
    <td align="center"><?php echo currency_format($dataTable[$i]['pembayaran_det_dibayar']) ?></td>
    <td align="center"><?php echo $dataTable[$i]['who_when_update'] ?></td>
    <td align="center"><?php echo $dataTable[$i]['poli_nama'] ?></td>
    <td align="center"><?php echo $dataTable[$i]['usr_name'] ?></td>
</tr>
<?php 
} 
?>
</tbody>
</table>

<?php if(!$_POST["btnExcel"]) {?>
</div>
</div>

<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
    <tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php //echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
      <td align="left" width="10%" valign="middle" class="bawah">&nbsp;</td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
  </tr>
</table>

<?php } ?>
<?php if(!$_POST["btnExcel"]) { ?>
    <?php if($konfigurasi["dep_konf_dento"]=='y') { ?>
        <!--------Buat Helpicon----------->
        <script type="text/javascript">
            function showHideGB(){
                var gb = document.getElementById("gb");
                var w = gb.offsetWidth;
                gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
                gb.opened = !gb.opened;
            }
            function moveGB(x0, xf){
                var gb = document.getElementById("gb");
                var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
    <a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
    <a rel="sepur" href="<?php echo $ROOT;?>demo/laporan_pembayaran.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
    var gb = document.getElementById("gb");
    gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div>
<?php } ?>
<?php } ?>
</div>
<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>

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