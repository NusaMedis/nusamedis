<?php
require_once("../penghubung.inc.php");
require_once($LIB."/bit.php");
require_once($LIB."/login.php");
require_once($LIB."/encrypt.php");
require_once($LIB."/datamodel.php");
require_once($LIB."/dateLib.php");
require_once($LIB."/currency.php");
require_once($LIB."/tampilan.php");


$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
 $dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$err_code = 0;
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();

$_x_mode = "New";
 
// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$lokasi = $ROOT."/gambar/img_cfg";

if($_GET["id_reg"] || $_GET["pembayaran_id"]) {
  $sql = "select b.cust_usr_nama,b.cust_usr_kode_tampilan,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin,
        b.cust_usr_alamat,b.cust_usr_no_jaminan,b.cust_usr_no_identitas,d.poli_nama,cust_usr_tanggal_lahir,
        ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_pembayaran,a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien , a.reg_when_update,
        c.usr_name,e.jenis_nama,a.reg_kode_trans,a.reg_tanggal,a.reg_umur_hari,a.reg_umur_bulan,a.reg_umur,
        
         k.jkn_nama, 
        a.reg_no_sep, a.reg_kelas, l.perusahaan_nama, a.reg_diagnosa_inap, hak_kelas_inap,a.id_poli,b.cust_usr_jkn
        from klinik.klinik_registrasi a 
        join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
        left join global.global_auth_user c on c.usr_id = a.id_dokter 
        left join global.global_auth_poli d on a.id_poli = d.poli_id
        left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
        left join global.global_jkn k on k.jkn_id = a.reg_tipe_jkn
        left join global.global_perusahaan l on l.perusahaan_id = b.id_perusahaan
        where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);     
  $dataPasien= $dtaccess->Fetch($sql);     
  
  $_POST["id_reg"] = $_GET["id_reg"]; 
  $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
  $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
  $_POST["cust_usr_no_jaminan"] = $dataPasien["cust_usr_no_jaminan"];
  $_POST["cust_usr_no_identitas"] = $dataPasien["cust_usr_no_identitas"];
  $_POST["id_pembayaran"] = $dataPasien["id_pembayaran"];
  $_POST["keterangan"] = $_GET["ket"];
  $_POST["diskon"] = $_GET["dis"];
  $_POST["diskonpersen"] = $_GET["disper"];
  $_POST["pembulatan"] = $_GET["pembul"];
  $_POST["total"] = $_GET["total"];
  $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
  $_POST["reg_diagnosa_inap"] = $dataPasien["reg_diagnosa_inap"];
  $_POST["reg_kelas"] = $dataPasien["reg_kelas"];
  $_POST["hak_kelas_inap"] = $dataPasien["hak_kelas_inap"];
  $_POST["id_poli"] = $dataPasien["id_poli"];
  $_POST["cust_usr_jkn"] = $dataPasien["cust_usr_jkn"];
 
  $kelas = array('1','2','3');
  if($_POST["reg_kelas"]=='2') {$kls=$kelas[0];}
  elseif($_POST["reg_kelas"]=='3') {$kls=$kelas[1];;}
  elseif($_POST["reg_kelas"]=='4') {$kls=$kelas[2];}   

  // nyari petugas yg bayar --
  $sql = "select usr_name from klinik.klinik_folio a
          left join global.global_auth_user b on b.usr_id = a.who_when_update 
          where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $petugas = $dtaccess->Fetch($rs);
  
  //diagnosa utama pasien
  $sql = "select c.icd_nama from klinik.klinik_perawatan_icd a 
          left join klinik.klinik_perawatan b on a.id_rawat=b.rawat_id
          left join klinik.klinik_icd c on c.icd_id=a.id_icd
          where a.rawat_icd_urut='1' and b.id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
  $rs = $dtaccess->Execute($sql);
  $diagnosa = $dtaccess->Fetch($rs);

  //ambil jenis pasien
  $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' and jenis_id =".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
  $rs = $dtaccess->Execute($sql);
  $jenisPasien = $dtaccess->Fetch($rs);

    //ambil kota jamkesda
  $sql = "select b.jamkesda_kota_id, b.jamkesda_kota_nama, b.jamkesda_kota_persentase_kota, b.jamkesda_kota_persentase_prov 
        from klinik.klinik_registrasi a left join global.global_jamkesda_kota b 
        on a.id_jamkesda_kota=b.jamkesda_kota_id 
        where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
  $jk = $dtaccess->Execute($sql);
  $NamaKotajamkesda = $dtaccess->Fetch($jk);

  $sql = "select a.*, b.*, c.*, d.*
      from klinik.klinik_folio a
      left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
      left join klinik.klinik_kategori_kassa_biaya c on c.id_biaya = a.id_biaya 
      left join klinik.klinik_kategori_kassa d on c.id_kategori_kassa = d.kategori_kassa_id
      where fol_lunas='y' 
      and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." 
      and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." order by kategori_kassa_id, a.id_biaya, fol_nama asc" ;
      $dataFolio = $dtaccess->FetchAll($sql);
  
  $sql = "select * from klinik.klinik_inacbg where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
  $inacbg = $dtaccess->Fetch($sql);
   
  $sql = "select * from global.global_tarif_inacbg where kode_inacbg=".QuoteValue(DPE_CHAR,$inacbg["inacbg_kode"])." 
          and tarif_kelas=".QuoteValue(DPE_CHAR,$kls)." and tipe_inacbg='1'";
  $tarif = $dtaccess->Fetch($sql);   
  //echo $sql;

  $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
  $uangmuka = $dtaccess->Fetch($sql);
      
      // echo "absabsajsbas";
  for($i=0,$n=count($dataFolio);$i<$n;$i++){
    $total = $dataFolio[$i]["fol_hrs_bayar"];
    $totalBiaya = $totalBiaya+$dataFolio[$i]["fol_nominal"];
    $TotalSubsidi = $TotalSubsidi+$dataFolio[$i]["fol_subsidi"];
  }
  $TotalSubsidi = $TotalSubsidi;

  $sql = "select pembayaran_dijamin from  klinik.klinik_pembayaran
  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);

  $rs_dijamin = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
  $dataDijamin = $dtaccess->Fetch($rs_dijamin);
  //total biaya
  $totalBiaya=$totalBiaya;   
  //harga dijamin
  $dijaminHarga = $dataDijamin["pembayaran_dijamin"];

  if($konfigurasi["dep_konf_biaya_akomodasi"]=="y"){
    $sql = "select * from klinik.klinik_folio where UPPER(fol_nama) like '%SIDO%' and id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $folwk = $dtaccess->Fetch($sql);
    
    
    $sql = "select * from klinik.klinik_biaya where biaya_kategori=".QuoteValue(DPE_CHAR,"050")." 
            and id_kelas='1'";
             
    if ($folwk["fol_nama"] <> null) {
    $sql.="and UPPER(biaya_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($folwk["fol_nama"])."%");            
    } else {
    $sql.="and UPPER(biaya_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["kamar_nama"])."%");
    }
    $biayavip = $dtaccess->Fetch($sql);
    //echo "<br>".$sql."<br>";
    
    if ($_POST["kamar_nama"]=="HCU" || $_POST["kamar_nama"]=="ICU"){
    $sql = "select * from klinik.klinik_biaya where biaya_kategori=".QuoteValue(DPE_CHAR,"135")." 
            and id_kelas='5'";
    }else{
    $sql = "select * from klinik.klinik_biaya where biaya_kategori=".QuoteValue(DPE_CHAR,"043")." 
            and id_kelas=".QuoteValue(DPE_CHAR,$_POST["hak_kelas_inap"]+1);
    }
    $biayahak = $dtaccess->Fetch($sql);
    
   // echo $sql."DA".$_POST["hak_kelas_inap"];
    
    $sql = "select * from klinik.klinik_biaya where biaya_id=".QuoteValue(DPE_CHAR,"6666666");
    $biayaakom = $dtaccess->Fetch($sql);
               
    
    $_POST["jumlahhari"] = dateDiff ($_POST["rawatinap_tanggal_masuk"],$_POST["rawatinap_tanggal_keluar"])+1;
    $akomodasi=$biayavip["biaya_total"] - $biayahak["biaya_total"];
    $totalakomodasi=$_POST["jumlahhari"]*$akomodasi;
    //echo $totalakomodasi."vip = ".$biayavip["biaya_total"]." hak= ".$biayahak["biaya_total"];
  }

  //perhitungan rumus JKN
  if($_POST["reg_jenis_pasien"]=="5"){
    //sesuai kelas 
    if(($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="4") || ($_POST["hak_kelas_inap"]=="2" && $_POST["reg_kelas"]=="3") || ($_POST["hak_kelas_inap"]=="1" && $_POST["reg_kelas"]=="2")){
     $totalHarga=$totalBiaya-$dijaminHarga-$inacbg["inacbg_topup"];
     //echo "total ".$totalHarga;
    } 
    elseif(($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="3") || ($_POST["hak_kelas_inap"]=="2" && $_POST["reg_kelas"]=="2") || ($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="2")){ //naik kelas sampai kelas 1
     $totalHarga=($tarifNaik["tarif_inacbg"]+$inacbg["inacbg_topup"])-($dijaminHarga+$inacbg["inacbg_topup"]);
     //echo $totalHarga;
    } 
     elseif((($_POST["hak_kelas_inap"]=="1" || $_POST["hak_kelas_inap"]=="2" || $_POST["hak_kelas_inap"]=="3") && $_POST["reg_kelas"]=="1")){
     $totalHarga=($totalBiaya-$dijaminHarga-$inacbg["inacbg_topup"]);
    } else {
       $totalHarga=$totalBiaya-$dijaminHarga-$inacbg["inacbg_topup"];
    }
  } elseif($_POST["reg_jenis_pasien"]=="24"){
   $totalHarga=$totalBiaya-10000000;
  } else {
   $totalHarga=$totalHarga;
  }

  if ( $totalHarga<0 && $totalakomodasi > 0) {$totalHarga=$totalakomodasi;} elseif ($totalHarga<0) {$totalHarga=0; } 
  $grandTotalHarga = $totalHarga-$uangmuka["total"];
  if(($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="3") || ($_POST["hak_kelas_inap"]=="2" && $_POST["reg_kelas"]=="2") || ($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="2")){
    $selisih = ($dijaminHarga+$inacbg["inacbg_topup"])-($tarif["tarif_inacbg"]+$inacbg["inacbg_topup"]);
  } else {
    $selisih = ($dijaminHarga+$inacbg["inacbg_topup"])-$totalBiaya;
  }
  
  $sql = "select * from klinik.klinik_pembayaran where 
        pembayaran_jenis = 'T' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
        " and id_dep=".QuoteValue(DPE_CHAR,$depId);
  $dataDiskon = $dtaccess->Fetch($sql);

}    
?>

<!DOCTYPE html>
<html>
<head>
  <title>Cetak Tagihan Rincian Rawat Inap Sementara</title>
  <style type="text/css">
    body {
      font-family: Verdana, Arial, Helvetica, sans-serif;
      font-size: 12px;
      margin: 0px;
      padding: 0px 0px 0px 0px;
    }

    #table1 {
      width: 100%;
      margin-top: 40px; 
    }

    #table2 {
      width: 100%;
      border-collapse: collapse;
    }

    #table2 td {
      padding: 5px;
    }

    #table2 td.bord-top {
      border-top: 1px dashed;
    }

  </style>
</head>
<body onload="window.print(); window.close(); ">
  <center><span style="font-size: 14px;">RINGKASAN BIAYA PERAWATAN RAWAT JALAN</span></center>
  <table id="table1"> 
    <tr>
      <td align="left" width="7%">No. Registrasi</td>  
      <td width="1%">:</td>  
      <td align="left" width="15%"><?php echo $dataPasien["reg_kode_trans"];?></td>  
      <td align="center" width="5%">&nbsp;</td>
      <td align="left" width="7%">Tgl Lahir / Umur</td>  
      <td width="1%">:</td>  
      <td align="left" width="20%"><?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]).' / '.$dataPasien["reg_umur"].' thn '
      .$dataPasien["reg_umur_bulan"].' bln '.$dataPasien["reg_umur_hari"] ;?> hr</td>  
    </tr>
    <tr>
      <td>Tgl Registrasi</td>  
      <td>:</td>  
      <td><?php echo format_date($dataPasien["reg_tanggal"]);?></td>  
      <td>&nbsp;</td>
      <td>Cara Bayar</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["jenis_nama"].' '.$dataPasien["jkn_nama"];?></td>  
    </tr>
    <tr>
      <td>No. RM</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_kode_tampilan"];?></td>  
      <td>&nbsp;</td>
      <td>Poli Klinik</td>
      <td>:</td>  
      <td><?php echo $dataPasien["poli_nama"];?></td> 
    </tr>
    <tr>
      <td>Nama Pasien</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_nama"];?></td>  
      <td>&nbsp;</td>
      <td>No Jaminan</td>
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_no_jaminan"];?></td> 
    </tr>
    <tr>
      <td>Alamat</td>  
      <td>:</td>  
      <td><?php echo $dataPasien["cust_usr_alamat"];?></td>  
      <td>&nbsp;</td>
      <td>No SEP</td>
      <td>:</td>
      <td><?php echo $dataPasien["reg_no_sep"];?></td>  
    </tr>
  </table>
  <br><br>
  <table id="table2">
    <tr>
      <td width="25%" align="center" class="bord-top">DESKRIPSI</td>
      <td width="5%" align="center" class="bord-top">JML</td>
      <td width="10%" align="right" class="bord-top">TAGIHAN</td>
      <td width="10%" align="right" class="bord-top">DIJAMIN</td>
      <td width="10%" align="right" class="bord-top">SUBSIDI</td>
      <td width="15%" align="right" class="bord-top">HRS. BAYAR</td>
    </tr>
    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
      $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
      $folnama = $dataFolio[$i]["fol_nama"];
      if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI' || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='I') {
        $folnama = $dataFolio[$i]["fol_nama"]."(".$dataFolio[$i]["fol_catatan"].")";
        $sql = "select a.item_nama as item, a.* ,satuan_nama, c.item_nama as barang
              from apotik.apotik_penjualan_detail a
              left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
              left join logistik.logistik_item c on a.id_item = c.item_id
              left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
              where b.penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
        $rs = $dtaccess->Execute($sql); 
        $dataFarmasidetail  = $dtaccess->FetchAll($rs);
      }
      if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ) {
        $folnama = $dataFolio[$i]["fol_nama"]."(".$dataFolio[$i]["fol_catatan"].")";     
        $sql = "select a.item_nama as item, a.* ,satuan_nama, c.item_nama as barang
                from logistik.logistik_retur_penjualan_detail a
                left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                left join logistik.logistik_item c on a.id_item = c.item_id
                left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
        $rs = $dtaccess->Execute($sql);
        $dataReturdetail  = $dtaccess->FetchAll($rs);   
      }  
    ?>
    <?php if($dataFolio[$i]["kategori_kassa_id"]!=$dataFolio[$i-1]["kategori_kassa_id"]) { ?>
    <tr>
      <td align="left" class="bord-top"><span style="letter-spacing: 1.3px; font-weight: bold;"><?php echo $dataFolio[$i]["kategori_kassa_nama"];?></span></td>
      <td align="center" class="bord-top"></td>
      <td align="right" class="bord-top"></td>
      <td align="right" class="bord-top"></td>
      <td align="right" class="bord-top"></td>
      <td align="right" class="bord-top"></td>
    </tr>
    <?php } ?>
    <tr>
      <td align="left"><?php echo $folnama;?></td>
      <td align="center"><?php echo $dataFolio[$i]['fol_jumlah']; ?></td>
      <td align="right"><?php echo number_format($dataFolio[$i]['fol_nominal']); ?></td>
      <td align="right"><?php echo number_format($dataFolio[$i]['fol_dijamin1']); ?></td>
      <td align="right"><?php echo number_format($dataFolio[$i]['fol_dijamin2']); ?></td>
      <td align="right"><?php echo number_format($dataFolio[$i]['fol_nominal']); ?></td>
    </tr>
    <?php if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI' || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='I') { 
      for($x=0; $x<count($dataFarmasidetail); $x++) {
    ?>
      <tr>
        <?php if($dataFarmasidetail[$x]["item"]=='' || $dataFarmasidetail[$x]["item"]==null){ ?>
          <td align="left">-- <?php echo $dataFarmasidetail[$x]["barang"];?></td>
        <?php } else { ?>
          <td align="left">-- <?php echo $dataFarmasidetail[$x]["item"];?></td>
        <?php } ?>
        <td align="center"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php //echo $dataFarmasidetail[$x]["satuan_nama"];?></td>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right"></td>
      </tr>
      <?php } # end loop detail penjualan ?>
    <?php  } # end data penjualan  ?>
    <?php if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ) {
      for($x=0; $x<count($dataReturdetail); $x++) {
    ?>
      <tr>
        <?php if($dataReturdetail[$x]["item"]=='' || $dataReturdetail[$x]["item"]==null){ ?>
          <td align="left">-- <?php echo $dataReturdetail[$x]["barang"];?></td>
        <?php } else { ?>
          <td align="left">-- <?php echo $dataReturdetail[$x]["item"];?></td>
        <?php } ?>
        <td align="center"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php //echo $dataReturdetail[$x]["satuan_nama"];?></td>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right"></td>
      </tr>
      <?php } # end loop detail retur ?>
    <?php } # end data retur ?>
  <?php } # end loop folio ?>
    <tr>
      <td align="right" class="bord-top" colspan="5"><span style="letter-spacing: 1.3px; font-weight: bold;">Total Tagihan</span></td>
      <td align="right" class="bord-top"><?php echo number_format($totalPembayaran); ?></td>
    </tr>
  </table>

  <?php if($_POST["reg_jenis_pasien"]=="5") {?>
  <table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
      <td width="20%">Hasil Bridging<td>
      <td>:</td>
    </tr>
    <tr>
      <td width="20%">Kode INACBGs <td>
      <td>: <?php echo $inacbg["inacbg_kode"];?></td>
    </tr>
    <tr>
      <td width="20%">Tarif INACBGs Kelas Perawatan <td>
      <td>: <?php echo currency_format($tarif["tarif_inacbg"]);?></td>
    </tr>
    <tr>
      <td width="20%">Tarif Top Up <td>
      <td>: <?php echo currency_format($inacbg["inacbg_topup"]);?></td>
    </tr>
    <tr>
      <td width="20%">Tarif INACBGs Kelas Hak <td>
      <td>: <?php echo currency_format($inacbg["inacbg_dijamin"]);?></td>
    </tr>
    <tr>
      <td width="20%">Uang Muka <td>
      <td>: <?php echo currency_format($uangmuka["total"]);?></td>
    </tr>
    <tr>
    <?php if($konfigurasi["dep_konf_biaya_akomodasi"]=="y"){ ?>
    <?if((($_POST["hak_kelas_inap"]=="1" || $_POST["hak_kelas_inap"]=="2" || $_POST["hak_kelas_inap"]=="3") && $_POST["reg_kelas"]=="1" && $_POST["id_poli"] <> '23')){  ?> 
      <td width="20%">Akomodasi Naik Kelas <td>
      <td>: <?php echo currency_format($totalakomodasi);?></td>
    <? } else {
    }
    }?>
    </tr>

    <tr>
      <td width="20%">Selisih <td>
      <td>: <?php echo ($selisih > 0 ? number_format($selisih) : '0') ;?></td>
    </tr>
  </table>
  <?php } #end detail bawah ?>
  <span style="text-align: right; float: right;">Dicetak di <? echo $konfigurasi["dep_kota"].", Tanggal ". date("d-m-Y H:i:s");?><br>Printed by <? echo $userName;?></span>
</body>
</html>