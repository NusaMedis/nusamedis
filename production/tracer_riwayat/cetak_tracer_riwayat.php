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
     
	$sql = "select a.*,	b.*, c.poli_nama
	from klinik.klinik_registrasi a 
	left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
	left join global.global_auth_poli c on a.id_poli = c.poli_id
	where reg_id = '$_GET[id_reg]'";
	$sql .= " order by a.reg_when_update desc";
	$rs = $dtaccess->Execute($sql);
	$dataPasien = $dtaccess->Fetch($rs); 

	  //update klinik registrasi
     $sql = "update klinik.klinik_registrasi set reg_tracer_riwayat='y' where
              reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $rs = $dtaccess->Execute($sql);

?>

<html>
<head>
<title>Bukti Registrasi</title>
<script>
    window.print();
</script>
<style>
	@page {
      size: letter;
      margin: 0cm 0cm 0cm 0cm;
    }

    body{
    	margin:0;
    }
	
	.tb1 {
		margin-top: 3.5cm;
		position: relative;
		font-family:    Verdana, Arial, Helvetica, sans-serif;
		font-size:      12px;
		border-color: #000000;
		border-collapse : collapse;
		border-style:solid;
	}
	.tb2 {
		margin-top: 2.2cm;
		position: relative;
		font-family:    Verdana, Arial, Helvetica, sans-serif;
		font-size:      12px;
		border-color: #000000;
		border-collapse : collapse;
		border-style:solid;
	}

	.tb1 .nama {
		padding-left: 3cm;
	}
	.tb1 .norm {
		letter-spacing: 0.5em;
		text-align:right;
		padding-right: 0px;
	}
	.tb1 .alamat{
		padding-top: 0.2cm;
		padding-left: 1cm;
	}
	.tb1 .tempat_lahir {
		text-align:left; 
		padding:0.3cm 0cm 0cm 4.3cm;
	}
	.tb1 .tgl_lahir {
		text-align:left; 
		padding:0.6cm 0cm 0cm 2cm;
	}

	.tb1 .umur {
		text-align:left; 
		padding:0.3cm 0cm 0cm 1.5cm;
	}

	.tb1 .no_telp {
		text-align:left; 
		padding:0.1cm 0cm 0cm 4cm;
	}

	.tb1 .jekel {
		text-align:left; 
		padding:0.3cm 0cm 0cm 2.4cm;
	}
	.tb1 .nik {
		text-align:left; 
		padding:0cm 0cm 0cm 5.4cm;
	}

	.tb1 .kode_pos {
		text-align:right; 
		padding:0.1cm 2.5cm 0cm 0cm;
	}

	.tb1 .status_kawin {
		text-align:left; 
		padding:0.3cm 0cm 0cm 7cm;
	}

	.tb1 .agama {
		text-align:left; 
		padding:0.3cm 0cm 0cm 2.4cm;
	}

	.tb1 .pendidikan {
		text-align:left; 
		padding:0.4cm 0cm 0cm 4.5cm;
	}

	.tb1 .pekerjaan {
		text-align:left; 
		padding:0.3cm 0cm 0cm 2.4cm;
	}
	.tb1 .asal_rujuk {
		text-align:left;
		padding:0.5cm 0cm 0cm 2cm;
	}

	.tb1 .reg_pertama {
		text-align:left;
		padding:0.4cm 0cm 0cm 5cm;
	}

	.tb2 .space {
		padding-left: 0cm; 
	}

	.tb2 .reg_tanggal {
		padding-left: 0.4cm; 
	}

	.tb2 .reg_waktu {
		padding-left: 0.1cm; 
	}

	.tb2 .tanggal_keluar {
		padding-left: 0.2cm; 
	}

	.tb2 .waktu_keluar {
		padding-left: 0.1cm; 
	}

	.tb2 .poli {
		padding-left: 3cm; 
	}
</style>
</head>

<body>

	<table class="tb1" width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td  width="50%" colspan="2" class="nama">&nbsp;<?php echo $dataPasien["cust_usr_nama"];?></td>
		<td class="norm">&nbsp;<?php $arr = str_split($dataPasien["cust_usr_kode"],"2"); echo implode("&nbsp;",$arr); ?></td>
	  </tr>                                                  
	  <tr>
		<td rowspan="3" colspan="2" class="alamat">&nbsp;<?php echo $dataPasien["cust_usr_alamat"];?></td>
		<td class="tempat_lahir">&nbsp;<?php echo $dataPasien["cust_usr_tempat_lahir"];?></td>
	  </tr>
	  <tr>                                                    
		<td class="tgl_lahir">&nbsp;<?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]);?></td>  
	  </tr>
	  <tr>                                                    
		<td class="umur">&nbsp;<?php echo $dataPasien["reg_umur"];?> th <?php echo "&nbsp;".$dataPasien["reg_umur_bulan"];?> bl <?php echo "&nbsp;".$dataPasien["reg_umur_hari"];?> hr</td>  
      </tr>
	  <tr>                                                    
		<td class="no_telp">&nbsp;<?php echo $dataPasien["cust_usr_no_telp"];?></td>
		<td class="kode_pos">&nbsp;<?php echo $dataPasien["cust_usr_kode_pos"];?></td>
		<td class="jekel" rowspan="2">
			<?php if($dataPasien["cust_usr_jenis_kelamin"]=="L"){echo "&nbsp;X";}?>
			<?php  if ($dataPasien["cust_usr_jenis_kelamin"]=="P")  echo '<span style="padding-left:6cm;">&nbsp;X</span>';?>
		</td>  
	  </tr>                                                    
      <tr>                                                    
		<td class="nik" colspan="2">&nbsp;<?php echo $dataPasien["cust_usr_nik"];?></td>  
      </tr>                                                    
      <tr>                                                    
		<td class="status_kawin" colspan="2">
			<?php echo "&nbsp;";/*if($dataPasien["cust_usr_status_perkawinan"]=="2"){echo "X";}*/ ?>
			<?php echo "&nbsp;"; /*if($dataPasien["cust_usr_status_perkawinan"]=="1"){echo '<span style="padding-left:3.1cm;">&nbsp;X</span>';}else{echo "&nbsp;";}*/ ?>
			<br>
			<?php echo "&nbsp;"; /*if($dataPasien["cust_usr_status_perkawinan"]=="4"){echo "&nbsp;X";}else { echo "&nbsp;&nbsp;&nbsp;&nbsp;"; }*/ ?>
			<?php echo "&nbsp;"; /*if ($dataPasien["cust_usr_status_perkawinan"]=="3")   echo '<span style="padding-left:3.1cm;">&nbsp;X</span>';*/ ?>
		</td>
		<td class="agama">
			<?php echo "&nbsp;"; /*if($dataPasien["cust_usr_agama"]=="1")echo "X";*/ ?>
			<?php echo "&nbsp;"; /*if($dataPasien["cust_usr_agama"]=="2")echo '<span style="padding-left:4cm;">X</span>';*/ ?>
			<?php echo "&nbsp;"; /*if($dataPasien["cust_usr_agama"]=="3")  echo '<span style="padding-left:4.2cm;">X</span>';*/ ?>
			<br>
			<?php  echo "&nbsp;"; /*if($dataPasien["cust_usr_agama"]=="4")echo "X";*/ ?>
			<?php  echo "&nbsp;"; /*if($dataPasien["cust_usr_agama"]=="5")echo '<span style="padding-left:4cm;">X</span>';*/ ?>
			<?php  echo "&nbsp;"; /*if ($dataPasien["cust_usr_agama"]=="6")  echo '<span style="padding-left:4.2cm;">X</span>';*/ ?>
		
		</td>  
      </tr>                                                    
      <tr>                                                    
		<td class="pendidikan" colspan="2">&nbsp;<?php echo $dataPasien["cust_usr_pendidikan"];?></td>
		<td class="pekerjaan">&nbsp;<?php echo $dataPasien["cust_usr_pekerjaan"];?></td>  
      </tr>                                                    
      <tr>                                                    
		<td class="asal_rujuk" colspan="2">&nbsp;<?php echo $dataPasien["cust_usr_rujukan_asal"];?></td>
		<td class="reg_pertama">&nbsp;<?php echo $dataPasien["cust_usr_create"];?></td>  
      </tr>  
	  <tr>                                                    
		<td style="text-align:left; padding:0.5cm 0cm 0cm 2cm;" colspan="2">
			&nbsp;
			<br>
			&nbsp;
		</td>
		<td style="text-align:left; padding:0.2cm 0cm 0cm 1.4cm;">
			&nbsp;
			<span style="float:right;padding-right:0.3cm;">&nbsp;</span>
			<br>
			&nbsp;
			<span style="float:right;padding-right:0.3cm;">&nbsp;</span>
		</td>  
      </tr>  	   
	  <!-- -
		-->
	</table>
	<div class="clearfix"><br></div>
	<table class="tb2" width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td class="space" width="50px">&nbsp;</td>
		<td class="reg_tanggal" width="50px">&nbsp;<?php echo $dataPasien["reg_tanggal"]; ?></td>
		<td class="reg_waktu" width="50px">&nbsp;<?php echo FormatPukul($dataPasien["reg_waktu"]); ?></td>
		<td class="tanggal_keluar" width="50px">&nbsp;</td>
		<td class="waktu_keluar" width="50px">&nbsp;</td>
		<td class="poli" width="200px">&nbsp;<?php echo $dataPasien["poli_nama"]; ?></td>
		<td></td>
	  </tr>
	</table>
	
</body>
</html>
