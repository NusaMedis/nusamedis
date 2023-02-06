<?php
     
	 require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
	 require_once($LIB."bit.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
    
     
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

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
     
     
     //array tipe layanan
     $layanan["1"] = "Reguler";
	   $layanan["2"] = "Eksekutif";

	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,cust_usr_tanggal_lahir,b.cust_usr_jenis_kelamin,d.poli_nama,cust_usr_jkn_asal,hak_kelas_inap,
            a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien,reg_diagnosa_awal,reg_no_sep, reg_tgl_sep,cust_usr_no_identitas,reg_dokter_sender,reg_tipe_rawat, 
            cust_usr_no_jaminan,a.reg_kode_trans from klinik.klinik_registrasi a 
            join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join global.global_rujukan f on a.reg_rujukan_id = f.rujukan_id
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    //echo $sql;      

    $dataPasien= $dtaccess->Fetch($sql);
    
		$_POST["id_reg"] = $_GET["idreg"]; 
		$_POST["id_pembayaran"] = $_GET["idbayar"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		$_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    $_POST["reg_no_sep"] = $dataPasien["reg_no_sep"];
    $_POST["reg_tgl_sep"] = $dataPasien["reg_tgl_sep"];
    $_POST["reg_tgl_rujukan"] = $dataPasien["reg_tgl_rujukan"];
    $_POST["cust_usr_jkn_asal"] = $dataPasien["cust_usr_jkn_asal"];
    $_POST["cust_usr_jenis_kelamin"] = $dataPasien["cust_usr_jenis_kelamin"];
    $_POST["hak_kelas_inap"] = $dataPasien["hak_kelas_inap"];
    $_POST["cust_usr_tanggal_lahir"] = $dataPasien["cust_usr_tanggal_lahir"]; 
    
$tglSEP = explode(" ",$_POST["reg_tgl_sep"]);
	
    $sql = "select icd_nama from klinik.klinik_icd where icd_nomor like ".QuoteValue(DPE_CHAR,"%".$dataPasien["reg_diagnosa_awal"]."%");
    $dataIcd= $dtaccess->Fetch($sql);
         
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

<title>S E P <?php echo $konfigurasi["dep_nama"];?></title>

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

<script language="JavaScript">

window.print();

</script>
</head>

<body>
  <table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
	<br>
	<td align="left" width = "1%"" valign="top" rowspan='2'>
<a href="#" target="_blank"><img height="44px" src="<?php echo $ROOT;?>gambar/logo_bpjs_lurus.png"/></a>
</td>
    <td class="tablecontent" align ="center" width="99%"><font size="2">SURAT ELIGIBILITAS PESERTA</td>
      </tr><tr><td class="tablecontent" align ="center"><font size="2"><?php echo $konfigurasi["dep_nama"]?></font></td> 
    </tr>                                                    
  </table>
	<br><br>  
<table align="left" width="50%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
    <tr>
      <td align="left" width="18%">No. Kode RS</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="34%" ><?php echo $konfigurasi["dep_kode_rs"];?></td>
    </tr>
	<tr>
      <td align="left" width="18%">No. SEP</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="34%" ><?php echo  $dataPasien["reg_no_sep"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Tgl. SEP</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo  format_date($tglSEP[0]);?></td>
    </tr>
    <tr>
      <td align="left" width="18%">No. Kartu </td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo  $dataPasien["cust_usr_no_jaminan"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Nama Peserta</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo $dataPasien["cust_usr_nama"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Tgl. Lahir </td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]);?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Jns. Kelamin </td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo $dataPasien["cust_usr_jenis_kelamin"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Poli Tujuan </td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo $dataPasien["poli_nama"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Asal Faskes Tk. I</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo $dataPasien["reg_dokter_sender"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Diagnosa Awal</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%"><?php echo $dataIcd["icd_nama"];?></td>
    </tr>
    <tr>
      <td align="left" width="18%">Catatan</td>  
    <td align="center" width="1%">:</td>
    <td align="left"width="32%"><?php //echo $dataPasien["poli_nama"];?></td>
    </tr>
</table>
<table align="right" width="50%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
      <td align="left" colspan='2' width="18%">Kelas RS</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%" colspan='2'><b><?php echo $konfigurasi["dep_tipe_rs"];?><b></td>
    </tr>
	<tr>
      <td align="left" colspan='2' width="18%">No. RM</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%" colspan='2'><b><?php echo $dataPasien["cust_usr_kode"];?><b></td>
    </tr>
    <tr>
      <td align="left" colspan='2' width="18%">No. Reg</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="32%" colspan='2'><?php echo $dataPasien["reg_kode_trans"];?></td> 
    </tr>
    <tr>
      <td align="left" colspan='2' width="18%">Peserta</td>  
    <td align="center" width="1%">:</td>
    <td align="left" colspan='2' width="32%"><?php echo $dataPasien["cust_usr_jkn_asal"];?></td>
    </tr>
    <tr>
      <td align="left" >&nbsp;</td>  
    </tr>
    <tr>
      <td align="left" colspan='2' width="18%">COB</td>  
    <td align="center" width="1%">:</td>
    <td align="left" colspan='2' width="32%"><?php // echo $dataPasien["poli_nama"];?></td>
    </tr>
    <tr>
      <td align="left" colspan='2' width="18%">Jns. Rawat</td>  
    <td align="center" width="1%">:</td>
    <td align="left" colspan='2' width="32%">Rawat Jalan</td>
    </tr>
    <tr>
      <td align="left" colspan='2'width="18%">Kls. Rawat</td>  
    <td align="center" width="1%">:</td>
    <td align="left" colspan='2' width="32%"><?php echo $dataPasien["hak_kelas_inap"];?></td>
    </tr>
    <tr>
      <td align="left" t
      width="18%">&nbsp;</td>  
    </tr>
    <tr>
          <td align="center" width='50%' colspan ='4'>Pasien/ Keluarga Pasien</td>
          <td width='50%' align="center">Petugas BPJS Kesehatan</td>
    </tr>
    <tr>
      <td align="left"colspan='4'>&nbsp;</td>  
    </tr>
</table>
<table width="100%" border='0'>    <tr>
      <td align="left" colspan='8'><sub><i>* Saya Menyetujui BPJS Kesehatan menggunakan informasi medis </i></sub></td>  
    <td></td>
    </tr>
<tr>
      <td align="left" colspan='8'><sub><i>&nbsp;&nbsp;&nbsp;Pasien jika diperlukan.</i></sub></td>  
    <td></td>
    </tr>
    <tr>
      <td align="left" colspan='8'><sub><i>* SEP bukan sebagai bukti penjaminan peserta.</i></sub></td>  
    <td></td>
    </tr>
  </table> 
  <table align="left" width="50%" border='0'>     <tr>
      <td align="left" colspan='2'>Cetakan Ke 1</td>    <td align="center" width="3%">&nbsp;</td>  
</tr></table>
<table width="50%" border='0'align="right"><tr>
<td align="center" colspan='2'>____________________</td>
<td>&nbsp;</td><td>&nbsp;</td>
<td align="center">____________________</td>
    </tr>
</table>
<?php if($dataPasien["reg_tipe_rawat"]=='J'){?>
<br>
<table border='0' width="100%">
<tr>
<td align="center" colspan='4'><br>RESUME MEDIS</td>
 </tr>
 <tr>
<td align="center" colspan='4'>RAWAT JALAN - IGD</td>
 </tr>
 <tr height="30px">
<td align="left" >Diagnosa Utama</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-10</td><td align="left"> : &nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >Diagnosa Sekunder</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-10</td><td align="left"> : &nbsp;</td>
 </tr>
<tr height="30px">
<td align="left" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="left" colspan="2">&nbsp;</td>
 </tr>
 <tr height="30px">
<td align="left" >Tindakan</td><td align="left">: ....................................................................</td><td align="left"> Kode ICD-9</td><td align="left"> : &nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr> 
 <tr height="30px">
<td align="left" >Konsul</td><td align="left">: ....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr>
<tr height="30px">
<td align="center" >&nbsp;</td><td align="left">&nbsp;&nbsp;....................................................................</td><td align="center" colspan="2">&nbsp;</td>
 </tr> 
 <tr>
<td align="center" colspan="3">&nbsp;</td><td align="left"> <?php echo $konfigurasi["dep_kota"];?>, </td>
 </tr>
 <tr>
<td align="center" colspan="3">&nbsp;</td><td align="left"> Dokter Pemeriksa<br><br><br><br>(...............................)</td>
 </tr>
</table>
<? }?>
</div>
</body>
</html>
