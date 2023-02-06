<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $skr = date("Y-m-d");
     $tgl1 = date("dmY");

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
	
	if($_GET["id_reg"]) 
  {
      		$sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin,
                  b.cust_usr_alamat,b.cust_usr_no_jaminan,b.cust_usr_no_identitas,d.poli_nama, f.fol_keterangan, 
                  ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_pembayaran,a.id_poli,a.id_cust_usr,
                  a.reg_jenis_pasien , a.reg_when_update, a.reg_tipe_layanan, a.reg_tanggal, i.pembayaran_create,
                  c.usr_name as nama_dokter,e.jenis_nama,a.reg_kode_trans, a.reg_tipe_jkn, a.reg_status, a.reg_no_sep, 
                  g.inacbg_kode, h.jkn_nama, a.reg_tipe_paket,
                  j.perusahaan_diskon, j.perusahaan_plafon
                  from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                  left join global.global_auth_user c on c.usr_id = a.id_dokter 
                  left join global.global_auth_poli d on a.id_poli = d.poli_id
                  left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
                  left join klinik.klinik_folio f on f.id_reg=a.reg_id
                  left join klinik.klinik_inacbg g on g.id_reg=a.reg_id 
                  left join global.global_jkn h on h.jkn_id=a.reg_tipe_jkn
                  left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran
                  left join global.global_perusahaan j on j.perusahaan_id=a.id_perusahaan
                  where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
                 
          $dataPasien= $dtaccess->Fetch($sql);
           //echo $sql;      
      		$_POST["id_reg"] = $_GET["id_reg"]; 
      		$_POST["fol_jenis"] = $_GET["jenis"]; 
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
          $_POST["reg_tipe_jkn"] = $dataPasien["reg_tipe_jkn"];
          $_POST["reg_status"] = $dataPasien["reg_status"];
          $_POST["reg_tipe_layanan"] = $dataPasien["reg_tipe_layanan"];
          $_POST["reg_tipe_paket"] = $dataPasien["reg_tipe_paket"];
          $_POST["perusahaan_plafon"] = $dataPasien["perusahaan_plafon"];
          $_POST["perusahaan_diskon"] = $dataPasien["perusahaan_diskon"];
          
          //Data Transaksi Pembayaan
          $sql = "select a.*, b.biaya_paket from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id=a.id_biaya where
            id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." 
            and id_pembayaran_det = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_id"])."
            and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
      		$dataFolio = $dtaccess->FetchAll($sql);
          
          
          //Data Transaksi Obat
          $sql = "select a.fol_jenis from klinik.klinik_folio a where
                  id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." 
                  and id_pembayaran_det = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_id"])."
                  and a.id_dep=".QuoteValue(DPE_CHAR,$depId)."
                  and (a.fol_jenis like 'O%' or a.fol_jenis like 'R%')";
      		$dataFolioObat = $dtaccess->Fetch($sql);

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
<?php if($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="7" || $dataPasien["reg_jenis_pasien"]=="18") {?>
<title>Cetak Rincian Tindakan</title>
<?php } else {?>
<title>Cetak Rincian Pembayaran IGD</title>
<?php } ?>

<style>
@media print {
     #tableprint { display:none; }
}
  
#splitBorder tr td table{
border-collapse:collapse;
}

#splitBorder tr td table tr td {
border:1px solid black;
}

body {
     font-family:      Verdana, Arial, Helvetica, sans-serif;
     font-size:        10px;
     margin: 5px;
     margin-top:		  0px;
     margin-left:	  0px;
}

.menubody{
     background-image:    url(gambar/background_01.gif);
     background-position: left;
}
.menutop {
     font-family: Arial;
     font-size: 11px;
     color:               #FFFFFF;
     background-color:    #000e98;
     background-image:     url(gambar/bg_topmenu.png);
     background-repeat:	repeat-x;
     font-weight: bold;
     text-transform: uppercase;
     text-align: center;
     height: 25px;
     background-position: left top;
     cursor:pointer;
}

.menubottom {
     background-image:    	 url(gambar/submenu_bg.png);
     background-repeat:   	no-repeat;
}

.menuleft {
     font-family:      		Arial, Helvetica, sans-serif;
     font-size:        		12px;
     color:					#333333;
     background-image:    	 url(gambar/submenu_btn.png);
     background-repeat:   	repeat-y;
     font-weight: 			bolder;
}

.menuleft_bawah {
     font-family:      		Arial, Helvetica, sans-serif;
     font-size:        		8px;
     color:					#333333;
     background-image:    	 url(gambar/submenu_btn_bawah.png);	
     font-weight: 			bold;	
}

.img-button {
     cursor:     pointer;
     border:     0px;
}

.menuleft a:link, a:visited, a:active {
     font-family:      Arial, Helvetica, sans-serif;
     font-size:        12px;
     text-decoration:  none;
     color:            #333333;
}

.menuleft a:hover {
     font-family:      Arial, Helvetica, sans-serif;
     font-size:        12px;
     text-decoration:  none;
     color:            #6600CC;
}

table {
     font-family:    Verdana, Arial, Helvetica, sans-serif;
     font-size:      12px;
	padding:0px;
	border-color:#000000;
	border-collapse : collapse;
	border-style:solid;
	}

#tablesearch{
	display:none;
}

.passDisable{
     color: #0F2F13;
     border: 1px solid #f1b706;
     background-color: #ffff99;
}

.tabaktif {
     font-family: Verdana, Arial, Helvetica, sans-serif;
     font-size: 10px;
     color:               #E60000;
     background-color:    #ffe232;
     background-image:     url(gambar/tbl_subheadertab.png);
     background-repeat:	repeat-x;
     font-weight: bolder;
     height: 18;
     text-transform: capitalize;
}

.tabpasif {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color:               #000000;
	background-color:    #ffe232;
	background-image:     url(gambar/tbl_subheader2.png);
	background-repeat:	repeat-x;
	font-weight: bolder;
	height: 18;
	text-transform: capitalize;
}

.caption {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-style: normal;
}

a:link, a:visited, a:active {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
    text-decoration:  none;
    color:            #1F457E;

}

a:hover {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
    text-decoration:  underline;
    color:            #8897AE;
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
	color:               #333333;
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
	font-size: 10px;
	font-weight: lighter;
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
	background-color:    #fff5b3;
	height: 18px;
}

.tablecontent-gede {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 23px;
	font-weight: lighter;
	background-color:    #fff5b3;
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
	background-color:    #fff5b3;
	height: 18px;
}

.tablecontent-odd-telat {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #FC0508;
	font-weight: lighter;
	height: 18px;
}

.inputField
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #0F2F13;
	border: 1px solid #1A5321;
	background-color: #EBF4A8;
}


.content {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	background-color:    #E7E6FF;
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
	color:               #000000;
	background-color:    #FFFFFF;
	font-weight: bolder;
	height: 18;
	text-transform: capitalize;
}

.subheader-print {
    font-family:        Verdana, Arial, Helvetica, sans-serif;
    font-size:          10px;
    color:              #000000;
    font-weight:        bolder;
    height:             18;
}

.staycontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
}

.button, submit, reset {
    display:none;
    visibility:hidden;
}

select, option {
	font-family:	Verdana, Arial, Helvetica, sans-serif;
	font-size:		10px;
	text-indent:	2px;
	margin: 2px;
	left: 0px;
	clip:  rect(auto auto auto auto);
	border-top: 0px;
	border-right: 0px;
	border-bottom: 0px;
	border-left: 0px;
}

input, textarea {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	border: 1px solid #f1b706;
	text-indent:	2px;
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
	background : #E6EDFB url(../none);
	border: none;
	text-align: right;
}

.hlink {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}

.navActive {
	color:  #cc0000;
}

fieldset {
	border: thin solid #2F2F2F;
}

.whiteborder {
	border: none;
	margin: 0px 0px;
	padding: 0px 0px;
	border-collapse : collapse;
}

.adaborder {
	border-left: none;
	border-top: none;
	border-bottom: none;
	border-right: solid #999999 1px;
	margin: 0px 0px;
	padding: 0px 0px;
	border-collapse : separate;
}

.divcontent {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: lighter;
	background-color:    #E7E6FF;
	border-bottom: solid #999999 1px;
	border-right: solid #999999 1px;
}

.curedit {
	text-align: right;
}
 
#div_cetak{ display: block; }

#tblSearching{ display: none; }

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
	   font-family:      Arial, Verdana, Helvetica, sans-serif;
	   margin: 0px;
	    font-size: 10px;
    }
    
    .tableisi {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: none #000000 0px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    
    .tableisi td {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: solid #000000 1px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    .tablenota .judul  {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota .isi {
	    border-right: solid black 1px;
	    padding:4px;
    }
    
    .ttd {
	    height:50px;
    }
    
    .judul {
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
    }
    
    
    .judul {
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
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
	    background-color : #CCCCCC;
	    text-align : center;
    }
    .judul5 {
	    font-size: 16px;
	    font-weight: bold;
	    background-color : #d6d6d6;
	    text-align : center;
	    color : #000000;
    } 
    .judul6 {
	    font-size: 12px;
	    font-weight: bold;
	    text-align : center;
	    color : #000000;
    }  
</style>

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>

<form name="frmView" method="POST" action="<?php echo $thisPage; ?>">

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="right"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" id="judul">  
     <span class="judul2"><strong><?php echo $konfigurasi["dep_nama"];?></strong><br></span>
		<span class="judul3"><strong>
		<?php echo $konfigurasi["dep_kop_surat_1"];?></strong></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"];?></span></td> 
    <td width="30%" ></td> 
  </tr>
</table>
 <table border="0" cellpadding="2" cellspacing="0"  align="center" width="100%">
 <tr>
    <td align="center">================================================================================================================================================================================================================================</td>
</tr>
  </table>
 <table border="0" cellpadding="2" cellspacing="0"  align="center" width="100%">     
    <tr>
      <td style="text-align:center;font-size:15px;font-family:sans-serif;font-weight:bold;" class="tablecontent"><u>RINCIAN TINDAKAN RAWAT DARURAT</u></td>
    </tr>
  </table>
<br>
<table border="0" cellpadding="0" cellspacing="0"  align="center" width="100%">    
    <tr>
       <td width="30%" style="font-size: 12px;font-weight:normal;">Nomor Registrasi</td>
       <td width="2%" style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["reg_kode_trans"];?></td>
       <td></td>
       <td style="font-size: 12px;font-weight:normal;">Klinik yang dituju</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["poli_nama"];?></td>
    </tr> 
    <tr>
       <td style="font-size: 12px;font-weight:normal;">Tanggal</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".FormatTimestamp($dataPasien["reg_tanggal"]);?></td>
       <td></td>
       <td style="font-size: 12px;font-weight:normal;">Nama Dokter</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["nama_dokter"];?></td>
    </tr>    
    <tr>
     <td style="font-size: 12px;font-weight:normal;">No.RM</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["cust_usr_kode"];?></td>
       <td></td>
       <td style="font-size: 12px;font-weight:normal;">No Kartu Peserta</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["cust_usr_no_identitas"];?></td> 
    </tr>   
    <tr>
       <td style="font-size: 12px;font-weight:normal;">Nama Pasien</td>
      <td style="font-size: 12px;font-weight:normal;">:</td>                
       <?php if($dataPasien["cust_usr_kode"]=='100' || $dataPasien["cust_usr_kode"]=='500' || $dataPasien["fol_keterangan"] <> '') {?>
       <td align="left" width="20%"><?php echo "".$dataPasien["fol_keterangan"];?></td>
       <?php } else { ?>  
       <td align="left" width="20%"><?php echo "".$dataPasien["cust_usr_nama"];?></td>
       <?php } ?>  
       
       <td></td>
       <td style="font-size: 12px;font-weight:normal;">Cara Bayar</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='26'){?>
       <td align="left" width="20%"><?php echo "".$dataPasien["jenis_nama"]." - ".$dataPasien["jkn_nama"];?></td>
       <?php } else {?>
       <td align="left" width="20%"><?php echo "".$dataPasien["jenis_nama"];?></td>
       <?php } ?>   
       
    </tr>
    <tr>
       <td style="font-size: 12px;font-weight:normal;">Alamat</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["cust_usr_alamat"];?></td>
       <td></td>
       <td style="font-size: 12px;font-weight:normal;">No.SEP</td>
       <td style="font-size: 12px;font-weight:normal;">:</td>
       <td style="font-size: 12px;font-weight:normal;"><?php echo "".$dataPasien["reg_no_sep"];?></td> 
    </tr>      

  </table>
<br>
<table align="center" width="100%" border="1" cellpadding="5" cellspacing="0">
      <tr align="center">
         <td style="font-size:12px"><b>DESKRIPSI</b></td>
         <td style="font-size:12px"><b>JUMLAH</b></td> 
         <td style="font-size:12px"><b>TAGIHAN</b></td>
         <td style="font-size:12px"><b>SUBSIDI</b></td>
         <td style="font-size:12px"><b>HARUS BAYAR</b></td>
         <td style="font-size:12px"><b>TOTAL TAGIHAN</b></td>
         </tr>
         
        <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {
       if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI'
            || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG'
            || $dataFolio[$i]["fol_jenis"]=='I'){
        $sql = "select item_nama, a.* ,satuan_nama
                  from apotik.apotik_penjualan_detail a
                  left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                  left join logistik.logistik_item c on a.id_item = c.item_id
                  left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                  where b.penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
            $rs = $dtaccess->Execute($sql); 
            $dataFarmasidetail  = $dtaccess->FetchAll($rs); 
       //     echo $sql;
             }        
            
   if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' 
          || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ){
     $sql = "select item_nama, a.* ,satuan_nama
                  from logistik.logistik_retur_penjualan_detail a
                  left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                  left join logistik.logistik_item c on a.id_item = c.item_id
                  left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                  where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
            $rs = $dtaccess->Execute($sql);
            $dataReturdetail  = $dtaccess->FetchAll($rs);     }      ?>            

     <tr>
         <td align="left">
            <?php if($dataFolio[$i]["fol_jenis"]=="O" || $dataFolio[$i]["fol_jenis"]=="OA" || $dataFolio[$i]["fol_jenis"]=="OG" || 
                     $dataFolio[$i]["fol_jenis"]=="OI" || $dataFolio[$i]["fol_jenis"]=="R" || $dataFolio[$i]["fol_jenis"]=="RA" || 
                     $dataFolio[$i]["fol_jenis"]=="RA" || $dataFolio[$i]["fol_jenis"]=="RG" || $dataFolio[$i]["fol_jenis"]=="RI"){
                    echo $dataFolio[$i]["fol_nama"]." (".$dataFolio[$i]["fol_catatan"].")";
                  } else echo $dataFolio[$i]["fol_nama"];?>
         </td>
         <td align="center"><?php echo round($dataFolio[$i]["fol_jumlah"]);?></td>
         <td align="right"><?php echo currency_format($dataFolio[$i]["fol_nominal_satuan"]);?></td>

          <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_subsidi"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_hrs_bayar"])?></td>
          <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_nominal"])?></td>
     </tr>
           <?php if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA'
           || $dataFolio[$i]["fol_jenis"]=='RI' ||$dataFolio[$i]["fol_jenis"]=='RG'){  //JIKA ADA RETUR OBAT                       
   for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr>

          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php } } 
       
          $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
          $totalDijamin += $dataFolio[$i]["fol_dijamin"];
          $totalDijamin1 += $dataFolio[$i]["fol_dijamin1"];
          $totalDijamin2 += $dataFolio[$i]["fol_dijamin2"];          
          $totalSubsidi += $dataFolio[$i]["fol_subsidi"];          
          //$totalIur += $dataFolio[$i]["fol_iur_bayar"];
          $totalHrsBayar += $dataFolio[$i]["fol_hrs_bayar"];
          //perhitungan rumus JKN
            $totalHarga=$totalPembayaran-$dijaminHarga;
            if ($totalHarga<0) $totalHarga=0;
     
       
       
       } ?>                 
                              
    
     
     
  
<tr><td></td></tr>         
         
</table>
 
 <?php if($dataFolioObat){ //JIKA ADA PENJUALAN OBAT?>        
<table align="center" width="100%" border="1" cellpadding="5" cellspacing="0">
      <tr align="center">
         <td style="font-size:12px;"><b>NAMA ITEM/OBAT</b></td>
         <td style="font-size:12px;"><b>JUMLAH</b></td>    
         <td style="font-size:12px;"><b>HARGA SATUAN</b></td>
         <td style="font-size:12px;"><b>TOTAL HARGA</b></td>
        </tr>
   
     <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {
    

        if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI'
            || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG'
            || $dataFolio[$i]["fol_jenis"]=='I' || $dataFolio[$i]["fol_jenis"]=='R'|| 
            $dataFolio[$i]["fol_jenis"]=='RI'
            || $dataFolio[$i]["fol_jenis"]=='RA' ||$dataFolio[$i]["fol_jenis"]=='RG'){  
      
              } 
            if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI'
            || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG'
            || $dataFolio[$i]["fol_jenis"]=='I'){  
    
  for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) { ?>
       <tr>

          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>

       </tr>     
       <?php }           
              } 
            if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA'
           || $dataFolio[$i]["fol_jenis"]=='RI' ||$dataFolio[$i]["fol_jenis"]=='RG'){                       
      for($x=0,$y=count($dataReturdetail);$x<$y;$x++) { ?>
       <tr>

          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php }                 
               
            }         
    

          
    
 } ?>
  
</table>
<?php } ?>

<table align="center" width="100%" border="0" cellpadding="5" cellspacing="0">  
  <tr>
    <td align="left" >TOTAL TAGIHAN : <?php echo currency_format($totalPembayaran);?></td>
    </tr>
      <?php if($dataPasien["reg_jenis_pasien"]=="7" || $dataPasien["reg_jenis_pasien"]=="18") {?>
    <tr>
    <td align="left" colspan='8'>TOTAL PIUTANG : <?php echo currency_format($totalPembayaran);?></td>
    </tr>
    <?php } elseif($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="26") {?>
    <tr>
    <td align="left" colspan='8'>TOTAL DIJAMIN : <?php echo currency_format($dijaminHarga);?></td>
    </tr>
    <?php } else { ?>
    <tr>
    <td align="left" colspan='8'>TOTAL PIUTANG : 0</td>
  </tr>
  <?php }  ?>
  <tr>
   <td colspan='8'>
  <?php  
   if(($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="26") && ($_POST["reg_tipe_jkn"]=="1" || $_POST["reg_tipe_layanan"]=="1")) { echo "TOTAL SUBSIDI : ".currency_format($totalHarga); } else echo "TOTAL HRS BAYAR : ".currency_format($totalHrsBayar);?>
   </td>
   </tr>
  <tr>
    <?php if($dataPasien["reg_jenis_pasien"]=="7" || $dataPasien["reg_jenis_pasien"]=="18") {?>
    <td align="left" colspan="8">Terbilang : <?php echo terbilang($totalPembayaran);?> Rupiah</td>
    <?php } elseif($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="26") {?>
    <td align="left" colspan="8"></td>
    <?php } else { ?>
    <td align="left" colspan="8">Terbilang : <?php echo terbilang($totalHrsBayar);?> Rupiah</td>
    <?php } ?>
  </tr>
      <?php if($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="26") {?>
  <tr>
    <td align="left" colspan="8">Kode INACBG : <?php echo $dataPasien["inacbg_kode"];?></td>
  </tr>
  <tr>
    <td align="left" colspan="8">Selisih : <?php echo currency_format($selisih);?></td>
  </tr>
      <?php } ?>

</table>
<br>
<table width="100%" border="0">
  <tr>
    <td align="center"><?php echo $konfigurasi["dep_kota"];?>, <?php echo date("d-m-Y");?></td>
  </tr>
  <tr>
    <td align="center">Petugas,</td>
  </tr>
  <tr>
    <td align="center"></td>
  </tr>
  <tr>
    <td align="center"></td>
  </tr>
  <tr>
    <td align="center">(<?php echo $userName;?> )</td>
  </tr>
</table>  
</html>
