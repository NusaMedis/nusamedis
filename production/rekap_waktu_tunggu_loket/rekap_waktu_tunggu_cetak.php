<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();

if (!$_GET["klinik"]) $_GET["klinik"] = $depId;

// cari jenis bayar ee //
$sql = "select * from global.global_jenis_bayar where jbayar_status='y' and id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by jbayar_id";
$jsBayar = $dtaccess->FetchAll($sql);

if (!$_GET["klinik"]) $_GET["klinik"] = $depId;

//pemanggilan tanggal hari ini 
if (!$_GET["tgl_awal"]) $_GET["tgl_awal"] = date("d-m-Y");
if (!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = date("d-m-Y");


if (!empty($_GET["id_poli"])) $sql_where[] = "a.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
if (!empty($_GET["cust_usr_nama"])) $sql_where[] = "b.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_nama"]);
if ($_GET["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like '%" . $_GET["cust_usr_kode"]."%'";


if (!empty($_GET["reg_tipe_rawat"])) {
  $sql_where[] = "a.reg_tipe_rawat = " . QuoteValue(DPE_CHAR, $_GET["reg_tipe_rawat"]);
  $sql_where[] = "c.poli_tipe = " . QuoteValue(DPE_CHAR, $_GET["reg_tipe_rawat"]);
  if ($_GET["reg_tipe_rawat"]=="I") {
    // code...
   $sql_where[] = "a.reg_status !='I9' ";
    $sql_where[] = "e.klinik_waktu_tunggu_status='I2' ";
   $sql_where2[] = "a.klinik_waktu_tunggu_status='I2' ";
 }elseif ($_GET["reg_tipe_rawat"]=="J") {
   // code...

  $sql_where2[] = "a.klinik_waktu_tunggu_status='E0' ";
   $sql_where[] = "e.klinik_waktu_tunggu_status='E0' ";
}elseif ($_GET["reg_tipe_rawat"]=="G") {
   // code...
   $sql_where[] = "e.klinik_waktu_tunggu_status='G0' ";
  $sql_where2[] = "a.klinik_waktu_tunggu_status='G0' ";

}

}




if ($_GET["reg_jenis_pasien"]!="--")  $sql_where[] = "a.reg_jenis_pasien =" . QuoteValue(DPE_CHAR, $_GET["reg_jenis_pasien"]);

// filter waktu tunggu


if (!empty($_GET["id_poli"])) $sql_where[] = "a.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);

if (!empty($_GET["cust_usr_nama"])) $sql_where2[] = "d.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_nama"]);
if ($_GET["cust_usr_kode"])  $sql_where2[] = "d.cust_usr_kode like '%" . $_GET["cust_usr_kode"]."%'";
$sql_where2[] = "b.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"])." 00:00:00");
$sql_where2[] = "b.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"])." 23:59:00");
$sql_where[] = "a.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"])." 00:00:00");
$sql_where[] = "a.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"])." 23:59:00");
if ($_GET["who_update"]!="--") $sql_where[] = "e.who_update = " . QuoteValue(DPE_CHAR, $_GET["who_update"]);


// if (!empty($_GET["id_poli"])) $sql_where3[] = "b.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);

// if ($_GET["jbayar"]) $sql_where3[] = "i.id_jbayar = " . QuoteValue(DPE_CHAR, $_GET["jbayar"]);
// if ($_GET["who_update"]!="--") $sql_where3[] = "j.who_waktu_tunggu = " . QuoteValue(DPE_CHAR, $_GET["who_update"]);
// if (!empty($_GET["cust_usr_nama"])) $sql_where3[] = "c.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_nama"]);
// if ($_GET["cust_usr_kode"])  $sql_where3[] = "c.cust_usr_kode like '%" . $_GET["cust_usr_kode"]."%'";
// // $sql_where3[] = "a.klinik_waktu_tunggu_when_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"])." 00:00:00");
// // $sql_where3[] = "a.klinik_waktu_tunggu_when_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"])." 23:59:00");
// if ($_GET["reg_jenis_pasien"]!="--")  $sql_where3[] = "b.reg_jenis_pasien =" . QuoteValue(DPE_CHAR, $_GET["reg_jenis_pasien"]);

  // $sql_where[] = "c.poli_tipe!='A' and c.poli_tipe!='L' and c.poli_tipe!='R' and c.poli_tipe!='N' and c.poli_tipe!='O'";
//     $sql_where[] = "1=1";

$jmlHari = HitungHari(date_db($_GET["tgl_awal"]), date_db($_GET["tgl_akhir"]));


  //untuk mencari tanggal
  $sql_where = implode(" and ", $sql_where);

  $sql = "select a.*,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_kode, poli_nama,d.rawatinap_tanggal_keluar,f.jenis_nama,e.klinik_waktu_tunggu_status from  klinik.klinik_registrasi a
  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
  left join global.global_auth_poli c on a.id_poli = c.poli_id
  left join klinik.klinik_rawatinap d on a.reg_id = d.id_reg
  left join klinik.klinik_waktu_tunggu e on e.id_reg = a.reg_id
  left join global.global_jenis_pasien f on a.reg_jenis_pasien = f.jenis_id";

  $sql .= " where (b.cust_usr_kode !='100' or b.cust_usr_kode !='500') and (a.reg_utama=a.reg_id or reg_utama is null)  and ".$sql_where; 
  $sql .= "order by a.reg_when_update";
  
  $rs = $dtaccess->Execute($sql);
  $dataRegistrasi = $dtaccess->FetchAll($rs);

  $sql = "select * from  klinik.klinik_waktu_tunggu_status";
  $sql .= " where waktu_tunggu_tipe_rawat='$_GET[reg_tipe_rawat]' and waktu_tunggu_status_flag='y' order by waktu_tunggu_status_urut"; 
  $rs = $dtaccess->Execute($sql);
  $dataStatus = $dtaccess->FetchAll($rs);

  $sql_where2 = implode(" and ",$sql_where2);

  $sql = "select a.id_reg, a.klinik_waktu_tunggu_when_create,a.who_update, a.klinik_waktu_tunggu_when_update, klinik_waktu_tunggu_durasi, a.klinik_waktu_tunggu_durasi_detik,a.klinik_waktu_tunggu_status from 
  klinik.klinik_waktu_tunggu a
  left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
  left join klinik.klinik_waktu_tunggu_status c on c.waktu_tunggu_status_id = a.id_waktu_tunggu_status";
  $sql .= " where  reg_status not like 'A%' and ".$sql_where2;         
  $sql .= "order by b.reg_when_update,c.waktu_tunggu_status_urut";
    // echo $sql;
  $rs = $dtaccess->Execute($sql); 
  while($row = $dtaccess->Fetch($rs)) {
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"] = $row["klinik_waktu_tunggu_when_create"];  
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_update"] = $row["klinik_waktu_tunggu_when_update"];      
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi_detik"] = $row["klinik_waktu_tunggu_durasi_detik"];      
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"] = $row["klinik_waktu_tunggu_durasi"];     
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["who_update"] = $row["who_update"]; 

  }



  //var_dump($datawaktuTunggu);


  // --- construct new table ---- //
  $counterHeader = 0;
  $counterHeader2 = 0;
  $counterHeader3 = 0;
// echo '<i style="color:blue;font-size:30px;font-family:calibri ;">
//       hello php color </i> ';
  $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Reg";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pulang";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;



  $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Medrec";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Lahir";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;




  $tbHeader[0][$counterHeader][TABLE_ISI] = "Informasi Waktu";

  $tbHeader[0][$counterHeader][TABLE_COLSPAN] =2;
  $counterHeader++;


  $tbHeader[1][$counterHeader2][TABLE_ISI] = "Mulai";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
  $counterHeader2++;

  $tbHeader[1][$counterHeader2][TABLE_ISI] = "Selesai";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
  $counterHeader2++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Tunggu";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;


  $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;






  $tgl = date_db($_GET["tgl_awal"]);
  $total_durasi = array(0, 0);
  $jumlah_berdurasi = array(0, 0);
  for ($i = 0, $counter = 0, $n = count($dataRegistrasi); $i < $n; $i++, $counter = 0) {



// <p style="color: red; text-align: center">
//       Request has been sent. Please wait for my reply!
//       </p>
//       if ($dataTable[$i]['is_GETing'] == 'n') echo "style='color: red;'" if ($dataTable[$i]['is_GETing'] == 'y') echo "style='color: black;'"

$tbContent[$i][$counter][TABLE_ISI] = ($i + 1) . ".";
$tbContent[$i][$counter][TABLE_ALIGN] = "right";

$counter++;



$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["reg_tanggal"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["reg_tanggal_pulang"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_kode"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["cust_usr_tanggal_lahir"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

if ($dataRegistrasi[$i]["reg_tipe_rawat"]=="I") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Inap";
}
elseif ($dataRegistrasi[$i]["reg_tipe_rawat"]=="G") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Darurat";
}
elseif ($dataRegistrasi[$i]["reg_tipe_rawat"]=="J") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";
}
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;



$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["poli_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["jenis_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;





$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "center";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_update"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "center";
$counter++;

$hours = floor($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] / 3600);
$minutes = floor(($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] / 60) % 60);
$seconds = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] % 60;
$hasil=($diff->d *86400)+ ($diff->h * 60) +$diff->i;
$detik=$hasil*60;
// $tbContent[$i][$counter][TABLE_ISI] =   $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi"];
$tbContent[$i][$counter][TABLE_ISI] =  $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;


$tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["who_update"] ;
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;









$tgl = DateAdd($tgl, 1);
    //print_r($tgl);   



$totsampai+=$detik+$diff->s;


}

$colspan = count($tbHeader[0]);

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];
//$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];
$lokasi = $ROOT . "/gambar/img_cfg";

if ($konfigurasi["dep_logo"] != "n") {
  $fotoName = $lokasi . "/" . $konfigurasi["dep_logo"];
} elseif ($konfigurasi["dep_logo"] == "n") {
  $fotoName = $lokasi . "/default.jpg";
} else {
  $fotoName = $lokasi . "/default.jpg";
}

//ambil nama poli
$sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where poli_id = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
$rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataPoli = $dtaccess->Fetch($rs_edit);

?>
<script language="JavaScript">
  window.print();
</script>
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName; ?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul">
      <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"] ?></strong><br></span>
      <span class="judul3">
        <?php echo $konfigurasi["dep_kop_surat_1"] ?></span><br>
        <span class="judul4">
          <?php echo $konfigurasi["dep_kop_surat_2"] ?></span></td>
        </tr>
      </table>

      <br>
      <table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">
        <tr>
          <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tgl_awal"]; ?> - <?php echo $_GET["tgl_akhir"]; ?></td>
          <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">
            LAPORAN WAKTU TUNGGU LOKET<? if ($_GET["tipe"] == 'L') {
              echo "LABORATORIUM";
            } elseif ($_GET["tipe"] == 'G') {
              echo "IGD";
            } elseif ($_GET["tipe"] == 'I') {
              echo "IRNA";
            } ?> </td>
          </tr>
          <? if ($_GET["shift"] != "--") { ?>
    <!--<tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php echo $dataShift["shift_nama"]; ?></td>
    </tr>
    <? } ?> -->
    <? if ($_GET["id_poli"] != "--") { ?>

      <?php 
      if ($_GET["tipe_rawat"]=="I") {
          // code...
        $tiperawat="Rawat Inap";

      }
      elseif ($_GET["tipe_rawat"]=="G") {
          // code...
       $tiperawat="Rawat Darurat";
     }
     elseif ($_GET["tipe_rawat"]=="J") {
          // code...
       $tiperawat="Rawat Jalan";
     }?>
     
     <tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Rawat : <?php echo $tiperawat; ?></td>
     </tr>
     <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik : <?php echo $dataPoli["poli_nama"]; ?></td>
    </tr>
  <? } ?>
</table>
<br>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>
    </td>
  </tr>
</table>