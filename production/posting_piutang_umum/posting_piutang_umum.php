<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
   
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depId = $auth->GetDepId();
     $thisPage = "report_setoran_loket.php";
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $lokasi = $ROOT."/gambar/img_cfg";
     
     if(!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else $_POST["klinik"] = $_POST["klinik"];    
     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
   
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
       die("Maaf anda tidak berhak membuka halaman ini....");
       exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
       echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
       exit(1);
     } 
 
     // konfigurasi
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
          
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }

  if ($_POST["tgl_awal"]) $sql_where[] = "d.reg_tanggal_pulang >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
  if ($_POST["tgl_akhir"]) $sql_where[] = "d.reg_tanggal_pulang <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));
if ($_POST["cust_usr_kode"]) $sql_where[] = "c.cust_usr_kode like " . QuoteValue(DPE_CHAR, "%" . $_POST["cust_usr_kode"] . "%");
  if ($_POST["layanan"] <> "--") {
  if ($_POST["layanan"] == "A") {
    $sql_where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status like '%F%' or d.reg_status like '%A%' or reg_status like '%R%')";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "d.reg_status like 'I%'";
  } else {
    $sql_where[] = "d.reg_status like 'G%'";
  }
}
     
    $sql_where = implode(" and ", $sql_where);

    $sql = "select d.reg_keterangan, a.*, c.cust_usr_nama, c.cust_usr_kode, b.biaya_nama,              
             f.jenis_nama, g.usr_name as dokter,  d.reg_jenis_pasien, d.reg_tanggal,d.reg_waktu,e.dep_nama,
             j.poli_nama, m.usr_name as ptg_entri, reg_tanggal_pulang, x.who_when_update, reg_tipe_rawat
             from  klinik.klinik_folio a  
                  left join klinik.klinik_registrasi d on a.id_pembayaran = d.id_pembayaran
                  left join klinik.klinik_pembayaran i on i.pembayaran_id = d.id_pembayaran
                  left join klinik.klinik_pembayaran_det x on x.pembayaran_det_id = a.id_pembayaran_det
                 left join global.global_customer_user c on d.id_cust_usr = c.cust_usr_id
                 left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
                 left join global.global_departemen e on e.dep_id = a.id_dep 
                 left join global.global_jenis_pasien f on f.jenis_id = d.reg_jenis_pasien
                 left join global.global_auth_user g on a.id_dokter = g.usr_id
                 left join global.global_auth_poli j on j.poli_id = d.id_poli
                 left join global.global_auth_user m on a.who_when_update = m.usr_id";
    $sql .= " where 1=1 and fol_nominal_satuan <> '0' and d.reg_utama is null and fol_lunas = 'n' and " . $sql_where;
    $sql .= " order by d.id_pembayaran,d.reg_tanggal,d.reg_waktu,a.fol_waktu";
    // echo $sql;
    $dataTable = $dtaccess->FetchAll($sql);   

for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
  if ($dataTable[$i]["id_pembayaran"] == $dataTable[$i - 1]["id_pembayaran"]) {
    $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
  }
}
// -- end ---
/*  $m=0;

     $sql = "select b.* from  klinik.klinik_folio_split b
             inner join  klinik.klinik_folio a on b.id_fol = a.fol_id
             left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran
             left join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
             join  klinik.klinik_registrasi d on d.reg_id = a.id_reg and d.id_cust_usr = a.id_cust_usr 
             left join klinik.klinik_split e on e.split_id = b.id_split";
     $sql .= " where d.reg_status like 'G%' and ".$sql_where; 
     $sql .= " order by a.id_pembayaran,i.pembayaran_create,a.fol_waktu, e.split_urut";
    // echo $sql;
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
     while($row = $dtaccess->Fetch($rs)) {
      $dataFolSplit[$row["id_fol"]][$row["id_split"]] = $row["folsplit_nominal"];
       }
         */
$counter = 0;
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Registrasi";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pulang";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

/*$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
     $counterHeader++;*/

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

/*$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;*/

//$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
//$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
//$counterHeader++;

//$tbHeader[0][$counterHeader][TABLE_ISI] = "Shift";
//$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
//$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

 $tbHeader[0][$counterHeader][TABLE_ISI] = "Jasa RS";
 $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Total Rincian";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

/*$tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Subsidi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Harus Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diskon";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kurang Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Pembayaran";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;*/
/*
     for($i=0,$n=count($dataSplit);$i<$n;$i++){
        
        $tbHeader[0][$counterHeader][TABLE_ISI] = $dataSplit[$i]["split_nama"];
        $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";  
        $counterHeader++;   
        //$counter=0;
      //$n = $i;
     }     
      */
$tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Ptg. Entri";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Ptg. Kasir";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;


for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {


  /*$sql = "select sum(a.pembayaran_total) as total from klinik.klinik_pembayaran a
            left join klinik.klinik_registrasi d on d.reg_id = a.id_reg";
     $sql .= " where a.pembayaran_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_pembayaran"])." 
     and date(a.pembayaran_tanggal) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." 
     and date(a.pembayaran_tanggal) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]))."
              and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);      
    $dataDet = $dtaccess->Fetch($sql); */

  $sql = "select usr_name from klinik.klinik_folio_pelaksana b 
          left join global.global_auth_user g on b.id_usr=g.usr_id 
          left join klinik.klinik_folio a on a.fol_id=b.id_fol 
          where b.id_fol=" . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_id"]) . " order by fol_pelaksana_tipe asc";
  $pelaksana = $dtaccess->FetchAll($sql);
  //echo $sql;

  if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]] + 1;

    $tbContent[$i][$counter][TABLE_ISI] = $m + 1;
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $m++;

    //$daytime = explode(".", $dataTable[$i]["pembayaran_create"]);
    //$time = explode(" ", $daytime[0]);
    $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal_pulang"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;*/

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    if ($dataTable[$i]["cust_usr_kode"] == '500' || $dataTable[$i]["cust_usr_kode"] == '100') {
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_keterangan"];
    } else {
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
    }
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;*/

    //$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
    //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    //$counter++;

    //$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["shift_nama"];
    //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //$tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    //$counter++;
  }
  //echo $sql;
  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;


  $tbContent[$i][$counter][TABLE_ISI] =  ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["fol_nominal_satuan"])) : currency_format($dataTable[$i]["fol_nominal_satuan"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_jumlah"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;  
  // $totalBiaya += $dataTable[$i]["fol_nominal"]; 
// if ($_POST['status'] != 'y') {
  $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran =" . QuoteValue(DPE_CHAR, $dataTable[$i]["id_pembayaran"]);
  $sql .= "and fol_lunas = 'n'";
// }elseif ($_POST['status'] == 'y') {
//   $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran_det =" . QuoteValue(DPE_CHAR, $dataTable[$i]["id_pembayaran_det"]);
//   if ($_POST['status']=='y') {
//     $sql .= "and fol_lunas = 'y'";//.QuoteValue(DPE_CHAR,$dataTable[$i]['fol_nomor_kwitansi']);
//   }elseif ($_POST['status'] == 'n') {
//     $sql .= "and fol_lunas = 'n'";
//   }
// }
  $dataDetFolJum = $dtaccess->Fetch($sql);

// if ($_POST['status'] != 'y') {
  $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_biaya <> '9999999' and id_pembayaran =" . QuoteValue(DPE_CHAR, $dataTable[$i]["id_pembayaran"]);
  $sql .= "and fol_lunas = 'n'";
// }elseif ($_POST['status'] == 'y') {
//   $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_biaya <> '9999999' and id_pembayaran_det =" . QuoteValue(DPE_CHAR, $dataTable[$i]["id_pembayaran_det"]);
//   if ($_POST['status']=='y') {
//     $sql .= "and fol_lunas = 'y'";//.QuoteValue(DPE_CHAR,$dataTable[$i]['fol_nomor_kwitansi']);
//   }elseif ($_POST['status'] == 'n') {
//     $sql .= "and fol_lunas = 'n'";
//   }
// }
  $dataDetFolJumX = $dtaccess->Fetch($sql);
  $totalFolioDetail = $dataDetFolJum["total"];
  $totalFolioDetailX = $dataDetFolJumX["total"];
  if ($dataTable[$i]['tipe_rawat'] == 'I') {
    $JasaRS = 0.1 * $totalFolioDetailX;
  }else{
    $JasaRS = 0;
  }
  $FixTotalFolioDetail = $totalFolioDetail + $JasaRS;

  if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
    $tbContent[$i][$counter][TABLE_ISI] = currency_format($JasaRS);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] =  ($_POST['btnExcel']) ? str_replace(',', '', currency_format($FixTotalFolioDetail)) : currency_format($FixTotalFolioDetail);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $totalFolio += $FixTotalFolioDetail;
    $totalJasaRs += $JasaRS;
  }

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ptg_entri"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  if ($dataTable[$i]['who_when_update'] <> '') {
    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["who_when_update"];
  }else{
    $tbContent[$i][$counter][TABLE_ISI] = 'Belum Tutup Transaksi';
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;
}
$rows = $m;
$counter = 0;

$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 10;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($totalJasaRs)) : "Rp." . currency_format($totalJasaRs);
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($totalFolio)) : "Rp." . currency_format($totalFolio);
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;

/*$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
     $tbBottom[0][$counter][TABLE_COLSPAN] = 3;
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
     $counter++;
  
     $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalDiskon);
    $tbBottom[0][$counter][TABLE_ALIGN] = "right";
    $counter++;
    
      $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalKurangBayar);
    $tbBottom[0][$counter][TABLE_ALIGN] = "right";
    $counter++;
    
    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totaled);
    $tbBottom[0][$counter][TABLE_ALIGN] = "right";
    $counter++;
    
    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalDijamin);
    $tbBottom[0][$counter][TABLE_ALIGN] = "right";
    $counter++;
  
    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
    $tbBottom[0][$counter][TABLE_COLSPAN] = 1;
    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
    $counter++; 
  
    for($j=0,$k=count($dataSplit);$j<$k;$j++){
         $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
         $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totSplit[$j]);
         $tbBottom[0][$counter][TABLE_ALIGN] = "right";
         $counter++;
     }
      */
$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 2;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;
     
  $tableHeader = "Laporan Belum Posting";
if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=LAPORAN_BELUM_POSTING.xls');
}

if ($_POST["btnCetak"]) {
  $_x_mode = "cetak";
}
     
?>

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
  BukaWindow('posting_piutang_umum_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&layanan=<?php echo $_POST["layanan"];?>&cust_usr_kode=<?php echo $_POST["cust_usr_kode"];?>', '_blank');
  document.location.href='posting_piutang_umum.php';
<?php } ?>

</script>

<?php if (!$_POST["btnExcel"]) { ?>
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
            
            <!-- Row -->
            <div class="row">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Laporan Belum Posting</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content" >
                  <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
                    <table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>

                        <select class="select2_single form-control" name="layanan" id="layanan" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Semua Tipe Rawat ]</option>
                          <option value="A" <?php if ($_POST["layanan"] == 'A') echo "selected"; ?>>Rawat Jalan</option>
                          <option value="I" <?php if ($_POST["layanan"] == 'I') echo "selected"; ?>>Rawat Inap</option>
                          <option value="G" <?php if ($_POST["layanan"] == 'G') echo "selected"; ?>>I G D</option>
                          <option value="O" <?php if ($_POST["layanan"] == 'O') echo "selected"; ?>>Non-Fungsional</option>
                        </select>

                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>

                        <input class="form-control col-md-7 col-xs-12" type="text" id="cust_usr_kode" name="cust_usr_kode" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_kode"]; ?>" />
                        <?php //if($userId=='b9ead727d46bc226f23a7c1666c2d9fb' || $userId=='fed7a2bfc3479110ea037d1940b44c7c'){ 
                        ?>

                      </div>
                      
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <!-- <input type="submit" name="btnTutup" value="Posting" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-danger"> -->
                        <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-success">
                        <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                        <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
                      </div>
                    </table>
                </div>
              </div>
            </div>
            <? } ?>
            <!-- END ROW  -->
            
            <!-- Row -->
            <div class="row">
              <div class="x_panel">
                <div class="x_title">
                  <h2></h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content" >
                  <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                   <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?> 
                  </table>
                </div>
              </div>
            </div>
            <!-- END ROW  -->
            
          </div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
        <?php require_once($LAY."footer.php"); ?>
      </div>
    </div>
    <?php require_once($LAY."js.php"); ?>
  </body>
</html>