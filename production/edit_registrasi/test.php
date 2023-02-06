<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
   require_once($ROOT."lib/login.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
   $auth = new CAuth();
   $userName = $auth->GetUserName();
   $now = date('d M Y - H:i');

   $gbr = '../gambar/Capture.PNG';
     
  $sql = "select 
  a.reg_id,a.id_cust_usr,a.reg_jenis_pasien,a.reg_status_pasien,a.reg_tipe_layanan,a.reg_kode_trans,a.reg_when_update,a.reg_umur,a.reg_umur_hari,a.reg_umur_bulan,a.reg_who_update,
  b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_alamat,b.cust_usr_tanggal_lahir,
  c.poli_nama, d.jkn_nama,e.jenis_nama,f.usr_name,h.dep_kota, i.poli_tipe_nama,a.reg_tipe_rawat,reg_no_antrian,i.poli_tipe_id,
  j.prosedur_masuk_nama, k.rujukan_nama, l.rujukan_det_nama
  from 
  klinik.klinik_registrasi a left join 
  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
  global.global_auth_poli c on a.id_poli = c.poli_id left join
  global.global_jkn d on d.jkn_id = a.reg_tipe_jkn left join
  global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id left join
  global.global_auth_user f on a.id_dokter = f.usr_id
  left join global.global_departemen h on h.dep_id = a.id_dep
  left join global.global_auth_poli_tipe i on i.poli_tipe_id = c.poli_tipe
  left join global.global_prosedur_masuk j on a.reg_prosedur_masuk = j.prosedur_masuk_id
  left join global.global_rujukan k on a.reg_rujukan_id = k.rujukan_id
  left join global.global_rujukan_det l on a.reg_rujukan_det = l.rujukan_det_id
  where reg_id = '$_GET[reg_id]'";
  $sql .= " order by a.reg_when_update desc";
  $rs = $dtaccess->Execute($sql);
   $dataPasien = $dtaccess->Fetch($rs); 
  //echo json_encode($dataPasien);

    //update klinik registrasi
     $sql = "update klinik.klinik_registrasi set reg_tracer_registrasi='y' where
              reg_id = ".QuoteValue(DPE_CHAR,$_GET["reg_id"]);
     $rs = $dtaccess->Execute($sql);
    
    
    //array tipe layanan
     $layanan["1"] = "Reguler";
   $layanan["2"] = "Eksekutif";

   $kode_transaksi = $dataPasien["reg_kode_trans"];
   $kode_transaksis = explode(".", $kode_transaksi);
   // echo $kode_transaksis[0]."<br>";
   // echo $kode_transaksis[1]."<br>";
   // echo $kode_transaksis[2]."<br>";
   // echo $kode_transaksis[3]."<br>";
   // echo $kode_transaksis[4]."<br>";


?>

<html>
<head>
<title>Bukti Registrasi</title>
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">
</head>

<body onload="window.print()">
  <table class="b1" width="30%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
  <br><br><br>
  <td rowspan="3" width="3%"></td>
      <td align="center">NO. URUT</td>  
    </tr> 
    <tr>
      <td align="center"><b><?php echo $dataPasien["reg_no_antrian"];?></b></td>
    </tr> 
    <tr>
      <td align="center"><?php echo $now; ?></td>
    </tr>                                                  
        <tr>
      <td align="center"><br></td>  
    </tr>
  </table>
  
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
  
  <tr>
    <td rowspan="8" width="5%"></td>
  <td width="10%">No. Registrasi</td>
    <td align="left" width="85%">: <?php echo $kode_transaksis[3];?></td> 
  </tr>
  <tr>
  <td width="10%">No. RM </td>
    <td align="left" width="85%">: <?php echo $dataPasien["cust_usr_kode_tampilan"];?></td>  
  </tr>
  <tr>

  <td width="10%">Nama Pasien </td>
    <td align="left" width="85%">: <?php echo $dataPasien["cust_usr_nama"];?></td>  
  </tr>
  <tr>

  <td width="10%">Tanggal Lahir </td>
    <td align="left" width="85%">: <?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]);?>
     | <?php echo $dataPasien["reg_umur"];?> thn / <?php echo "&nbsp;".$dataPasien["reg_umur_bulan"];?> bln / <?php echo "&nbsp;".$dataPasien["reg_umur_hari"];?> hr</td>  
    
    
  </tr>
  
  
  <tr>
  <td width="10%">Cara Bayar  </td>
    <?php if($dataPasien["reg_jenis_pasien"]=="5"){?>
    <td align="left" width="85%">: <?php echo $dataPasien["jenis_nama"]." - ".$dataPasien["jkn_nama"];?></td>
    <?php } else { ?>
    <td align="left" width="85%">: <?php echo $dataPasien["jenis_nama"];?></td>
    <?php } ?>  
  </tr>
   <tr>
  <td width="10%">Klinik/Departemen </td>
    <td align="left" width="85%">: <?php echo $dataPasien["poli_nama"];?></td>  
  </tr>
  <tr>

  <td width="10%">Nama Dokter </td>
    <td align="left" width="85%">: <?php echo $dataPasien["usr_name"];?></td>  
  </tr>
  <tr>

  <td width="10%">Petugas Pendaftaran</td>
    <td align="left" width="85%">: <?php echo $dataPasien['reg_who_update'];?></td>  
  </tr>
</table>
<br>
<?php if ($dataPasien["poli_tipe_id"]=='J') { ?>
<table border="0" width="100%">
  <tr>
    <td colspan="3">&nbsp;</td>
    <td width="7%">Awal</td>
    <td width="10%">Lanjutan</td>
    <td width="55%"></td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td colspan="2" style="font-size: 14px;">Assesment</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width=""><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="50%"></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td colspan="5" style="font-size: 18px;">Alur Pasien:</td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">1.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Pendaftaran</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">2.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Poli</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">3.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Dokter</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">4.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Farmasi</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">5.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Laboratorium</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">6.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Radiologi</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">7.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Kasir</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="5%" style="font-size: 14px;">8.</td>
    <td width="50%" style="font-size: 14px;" colspan="2">Pasien Pulang</td>
    <td width="5%"><img src="<?php echo $gbr; ?>" width='20' height='20'></td>
    <td width="35%"></td>
  </tr>
</table>
<?php } ?>
</body>
</html>
