<?php
    require_once("../penghubung.inc.php");
      require_once($LIB."bit.php");
      require_once($LIB."login.php");
      require_once($LIB."encrypt.php");
      require_once($LIB."datamodel.php");
    require_once($LIB."dateLib.php");
      require_once($LIB."barcode.php");
      require_once($LIB."expAJAX.php");
      require_once($LIB."tampilan.php");
      require_once($LIB."currency.php");
           
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
     
     
     //array tipe layanan
     $layanan["1"] = "Reguler";
     $layanan["2"] = "Eksekutif";
     
  if($_GET["id_reg"] || $_GET["jenis"]  || $_GET["ket"] || $_GET["dis"] || $_GET["disper"] || $_GET["pembul"] || $_GET["total"]) {
    $sql = "select cust_usr_nama,cust_usr_kode,cust_usr_kode_tampilan,cust_usr_tanggal_lahir,cust_usr_no_hp,b.cust_usr_jenis_kelamin,cust_usr_alamat, d.poli_nama,
            ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien , id_poli_asal,reg_tanggal, reg_waktu,
            a.reg_tipe_layanan,a.reg_when_update,a.reg_kode_trans,a.reg_umur_hari,a.reg_umur_bulan,a.reg_umur, h.poli_tipe_nama, jkn_nama, k.poli_nama as poli_asal,reg_no_sep,perusahaan_nama,
            c.usr_name,e.jenis_nama,f.rujukan_nama, i.usr_name as petugas_nama
            from klinik.klinik_registrasi a 
            join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join global.global_rujukan f on a.reg_rujukan_id = f.rujukan_id
            left join global.global_auth_poli_tipe h on d.poli_tipe = h.poli_tipe_id
            left join global.global_auth_user i on i.usr_id = a.reg_who_update 
            left join global.global_jkn j on a.reg_tipe_jkn = j.jkn_id
            left join global.global_auth_poli k on a.id_poli_asal = k.poli_id
            left join global.global_perusahaan l on a.id_perusahaan = l.perusahaan_id
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
    //echo $sql;      

    $dataPasien= $dtaccess->Fetch($sql);
    //print_r($dataPasien);

    $_POST["id_reg"] = $_GET["id_reg"]; 
    $_POST["fol_jenis"] = $_GET["jenis"]; 
    $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
    $_POST["keterangan"] = $_GET["ket"];
    $_POST["diskon"] = $_GET["dis"];
    $_POST["diskonpersen"] = $_GET["disper"];
    $_POST["pembulatan"] = $_GET["pembul"];
    $_POST["total"] = $_GET["total"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    
     //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' and jenis_id =".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->Fetch($rs);

    $sql = "select a.*,c.usr_name as dokter_nama from klinik.klinik_folio a 
            left join klinik.klinik_folio_pelaksana b on a.fol_id = b.id_fol and b.fol_pelaksana_tipe = '1'
            left join global.global_auth_user c on b.id_usr = c.usr_id 
            where
            fol_lunas='n' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $dataFolio = $dtaccess->FetchAll($sql);
    //print_r($dataFolio);
  //echo $sql;
    
   /* // nyari pelaksana  --
    $sql = "select usr_name from klinik.klinik_folio_pelaksana a
            left join global.global_auth_user b on b.usr_id = a.id_usr
            where id_fol =".QuoteValue(DPE_CHAR,$dataFolio[]["fol_id"]);
    $rs = $dtaccess->Execute($sql);
    $petugas = $dtaccess->Fetch($rs);
    echo $sql;
    die();*/
    
    $sql = "select usr_name from  global.global_auth_user
            where usr_id =".QuoteValue(DPE_CHAR,$userId);
    $rs = $dtaccess->Execute($sql);
    $petugas = $dtaccess->Fetch($rs);
    
    $totalHarga=$dataFolio[0]["fol_total_harga"];
    
    $sql = "select * from klinik.klinik_pembayaran where pembayaran_jenis = 'T' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $dataDiskon = $dtaccess->Fetch($sql);

   }
     
        // KONFIGURASI
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

<html>
<head>

<title>Surat Perintah Bayar</title>
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">
</head>

<body onload="window.print(); window.close();">

<table border="0" cellpadding="2" cellspacing="0" align="left">
    <tr>
      <td class="tablecontent"><font size="2"><?php echo $konfigurasi["dep_nama"]?></font></td>
    </tr>
    <tr>
      <td class="tablecontent"><?php echo $konfigurasi["dep_kop_surat_1"]?></td>
    </tr>
    <tr>
      <td class="tablecontent"><?php echo $konfigurasi["dep_kop_surat_2"]?></td>
    </tr>
    <tr>
      <td class="tablecontent"><?php echo $konfigurasi["dep_kop_surat_3"]?></td>
    </tr>
  </table>  
  <br><br>
  <table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
      <td align="center">SURAT PERINTAH BAYAR</td>  
    </tr>                                                    
    <tr>
      <td align="center"><?php echo $dataPasien["poli_tipe_nama"]." - ".$dataPasien["poli_nama"];?> <!-- (<?php echo $layanan[$dataPasien["reg_tipe_layanan"]];?>) --></td>  
    </tr>
    <tr>
      <td align="center">.....................................................................................................................................................................................</td>  
    </tr>                                                    
  </table>
 <br><br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
  <tr>
    <td align="left" width="7%">No. Reg</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["reg_kode_trans"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Cara Kunjungan</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["rujukan_nama"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">Tgl. Reg</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo format_date($dataPasien['reg_tanggal']) .' '.$dataPasien['reg_waktu'];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Poli Asal</td>
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo ( $dataPasien["id_poli"]==$dataPasien["id_poli_asal"] ? "Poli Pertama" : $dataPasien["poli_asal"] ) ?></td> 
  </tr>
  <tr>
    <td align="left" width="7%">No. RM</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["cust_usr_kode_tampilan"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Cara Bayar</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["jenis_nama"].' '.$dataPasien["jkn_nama"].$dataPasien["perusahaan_nama"];?></td> 
  </tr>
  <tr>
    <td align="left" width="7%">Nama</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["cust_usr_nama"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">No Sep</td>
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["reg_no_sep"];?></td> 
  </tr>
  <tr>
    <td align="left" width="7%">Tgl Lahir / Umur</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]).' / '.$dataPasien["reg_umur"];?> thn <?php echo $dataPasien["reg_umur_bulan"];?> bln <?php echo $dataPasien["reg_umur_hari"];?> hr</td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%"></td>
    <td align="center" width="1%"></td>  
    <td align="left" width="20%"></td>
  </tr>
  <tr>
    <td align="left" width="7%">Alamat</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%"><?php echo $dataPasien["cust_usr_alamat"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">&nbsp;</td>  
    <td align="center" width="1%">&nbsp;</td>  
    <td align="left" width="20%">&nbsp;</td>  
  </tr>

</table>
<br><br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
      <td colspan="5" align="center">.....................................................................................................................................................................................</td>  
  </tr>                                                    
  <tr>
    <td width="5%" align="center">No.</td>
    <td width="35%" align="center">Uraian</td>
    <td width="10%" align="center">Qty</td>
    <td width="15%" align="center">Total</td>
    <td width="30%" align="center">Pelaksana</td>
  </tr>
  <tr>
      <td colspan="5" align="center">.....................................................................................................................................................................................</td>  
  </tr>                                                    

    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {?>
     <tr>
         <td align="left"><?php echo $i+1;?></td>
         <td align="left">
            <?php if($dataFolio[$i]["fol_jenis"]=="O" || $dataFolio[$i]["fol_jenis"]=="OA" || $dataFolio[$i]["fol_jenis"]=="OG" || 
                     $dataFolio[$i]["fol_jenis"]=="OI" || $dataFolio[$i]["fol_jenis"]=="R" || $dataFolio[$i]["fol_jenis"]=="RA" || 
                     $dataFolio[$i]["fol_jenis"]=="RA" || $dataFolio[$i]["fol_jenis"]=="RG" || $dataFolio[$i]["fol_jenis"]=="RI" || $dataFolio[$i]["fol_jenis"]=="I"){
                    echo $dataFolio[$i]["fol_nama"]." (".$dataFolio[$i]["fol_catatan"].")";
                  } else echo $dataFolio[$i]["fol_nama"];?>
         </td>
         <td align="center"><?php echo round($dataFolio[$i]["fol_jumlah"]);?></td>
         <td align="right"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
         <td align="left" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $dataPasien["usr_name"];?></td>
     </tr>
     <?php $totalPembayaran += $dataFolio[$i]["fol_nominal"]; ?>
    <?php } ?>
    <tr>
      <td colspan="5" align="center">.....................................................................................................................................................................................</td>  
  </tr>                                                    
  <tr>
    <td colspan="4" align="right"><?php echo currency_format($totalPembayaran);?></td>
    <td width="30%" align="center"></td>
  </tr>
  <tr>
      <td colspan="5" align="center">.....................................................................................................................................................................................</td>  
  </tr> 

</table>
<br>
<div style="position:absolute;right:120px;">
<table width="100%" border="0">
  <tr>
    <td align="center"><?php echo $konfigurasi["dep_kota"];?>, <?php echo $dataPasien['reg_tanggal'];?></td>
  </tr>
  <tr>
    <td align="center">Petugas,</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">(<?php echo $petugas["usr_name"];?> )</td>
  </tr>
  </table>
</div>
</body>
</html>