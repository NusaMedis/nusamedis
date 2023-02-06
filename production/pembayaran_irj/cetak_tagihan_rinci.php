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
     $thisPage = "kasir_view.php";
	
	if($_GET["id_reg"] ) {
		$sql = "select a.id_pembayaran,a.id_poli,a.id_cust_usr, a.reg_jenis_pasien , a.reg_when_update, a.reg_kode_trans,a.reg_tanggal, 
            b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur, 
            b.cust_usr_alamat,a.reg_no_sep,b.cust_usr_no_identitas,c.usr_name, d.poli_nama, e.jenis_nama, f.fol_keterangan, 
            g.pembayaran_create, g.pembayaran_total, g.pembayaran_dijamin, g.pembayaran_subsidi, g.pembayaran_hrs_bayar,
            h.pembayaran_det_kwitansi, h.pembayaran_det_hrs_bayar, h.pembayaran_det_total, h.pembayaran_det_service_cash,
            l.jkn_nama
            from klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran g on g.pembayaran_id=a.id_pembayaran
            left join klinik.klinik_pembayaran_det h on h.id_reg=a.reg_id
            left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join klinik.klinik_folio f on f.id_reg = a.reg_id 
            left join global.global_jkn l on l.jkn_id=a.reg_tipe_jkn
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
           
    $dataPasien= $dtaccess->Fetch($sql);
    $view->CreatePost($dataPasien);
    $keterangan=explode("-",$dataPasien["fol_keterangan"]);
    $terima = $keterangan[0];
    $periode = $keterangan[1];
//echo $sql;
    //AMBIL DATA TRANSAKSI
    $sql = "select a.fol_waktu,a.fol_nama, a.fol_nominal, a.fol_jumlah, a.fol_hrs_bayar,  a.fol_jenis, a.fol_catatan, a.tindakan_tanggal, a.tindakan_waktu,
            f.usr_name
		from klinik.klinik_folio a
    left join global.global_auth_user f on a.fol_pelaksana = f.usr_id
		where a.fol_lunas='n'  and a.fol_nama <> ''
		and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." 
		and id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
		//and a.id_pembayaran_det =".QuoteValue(DPE_CHAR,$_GET["id_pembayaran_det"]);
    $sql .= " order by a.tindakan_tanggal,a.tindakan_waktu asc";
    $dataFolio = $dtaccess->FetchAll($sql);
//echo $sql;
    for($i=0,$n=count($dataFolio);$i<$n;$i++){
		 $totalBiaya = $totalBiaya+$dataFolio[$i]["fol_nominal"];
		 if ($dataFolio[$i]['id_biaya'] != '9999999') {
			 $totalBiayaJasa = $totalBiayaJasa+$dataFolio[$i]["fol_nominal"];
		 }
    }
    $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['id_pembayaran'])." and id_biaya <> '9999999'";
    $TindakanJasa = $dtaccess->Fetch($sql);
    $jasa = $TindakanJasa['total'] * 0.1;

  }

  $sql = "select deposit_nominal from klinik.klinik_deposit where id_cust_usr = " . QuoteValue(DPE_CHAR, $dataPasien["id_cust_usr"]);
  $datadeposit = $dtaccess->Fetch($sql);
  if($datadeposit['deposit_nominal'] || $datadeposit['deposit_nominal'] != 0) $deposit = $datadeposit['deposit_nominal'];
  else $deposit = 0;
     
     
     
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

<title>Cetak Ulang Rincian</title>

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




</style>

<script>              
$(document).ready( function() {
	window.print();
});    
</script> 
</head>

<body>

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr> 
    <td align="center" width="100%">&nbsp;<font size="2">RINCIAN BIAYA TINDAKAN RAWAT INAP SEMENTARA<font size="2"></td>  
  </tr>
</table>
<br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
  <tr>
    <td align="left" width="7%">No. Registrasi</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["reg_kode_trans"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Poli yang dituju</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["poli_nama"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">No. Kwitansi</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["pembayaran_det_kwitansi"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="5%">Nama Dokter</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="25%">&nbsp;<?php echo "&nbsp;".$dataPasien["usr_name"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">Tanggal</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".FormatTimestamp($dataPasien["reg_tanggal"]);?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">No. Peserta</td>
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_no_identitas"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">No. RM</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_kode"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Cara Bayar</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["jenis_nama"]."  ".$dataPasien["jkn_nama"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">Nama Pasien</td>  
    <td align="center" width="1%">:</td>
    <?php if($dataPasien["cust_usr_kode"]=='100' || $dataPasien["cust_usr_kode"]=='500' || $dataPasien["fol_keterangan"]<>'') {?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["fol_keterangan"];?></td>
    <?php } else { ?>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".str_replace("*", "'", $dataPasien["cust_usr_nama"]) ;?></td>
    <?php } ?>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">No. SEP</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["reg_no_sep"];?></td>  
  </tr>
  <?php if($dataPasien["cust_usr_kode"]<>'100' || $dataPasien["cust_usr_kode"]<>'500') {?>
  <tr>
    <td align="left" width="7%">Alamat</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_alamat"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">&nbsp;</td>  
    <td align="center" width="1%">&nbsp;</td>  
    <td align="left" width="20%">&nbsp;</td>  
  </tr>
  <?php } ?>
  <tr>
   <td colspan="4" align="center" width="5%">&nbsp;</td>
  </tr>

</table>

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center" colspan="8">---------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td width="2%" align="center">NO</td>
    <td width="15%" align="center">TANGGAL</td>
    <td width="35%" align="center">DESKRIPSI</td>
<!--    <td width="5%" align="center">KELAS</td>    -->
    <td width="5%" align="center">JML</td>
   
    <td align="right">TAGIHAN</td>    
    <td width="10%" align="right">HRS. BAYAR</td>
    <td width="10%" align="right">PELAKSANA</td>
  </tr>
  <tr>
    <td align="center" colspan="8">----------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>

    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {
    

            
    $folnama = $dataFolio[$i]["fol_nama"];
       if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI'
        || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG'
            || $dataFolio[$i]["fol_jenis"]=='I'){
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
                                
                         if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA' 
                              || $dataFolio[$i]["fol_jenis"]=='RG' || $dataFolio[$i]["fol_jenis"]=='RI' ){
                                     $folnama = $dataFolio[$i]["fol_nama"]."(".$dataFolio[$i]["fol_catatan"].")";     
                         $sql = "select a.item_nama as item, a.* ,satuan_nama, c.item_nama as barang
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql);
                                $dataReturdetail  = $dtaccess->FetchAll($rs);     }  
    
    
    ?>
     <tr>
          <td align="left"><?php echo $i+1;?></td>
          <? if($dataFolio[$i]["fol_waktu"]<>null){ ?>
          <td align="left"><?php echo FormatTimeStamp($dataFolio[$i]["fol_waktu"]);?></td>	
         <? }else{ ?>
         <td align="left"><?php echo format_date($dataFolio[$i]["tindakan_tanggal"])." ".$dataFolio[$i]["tindakan_waktu"];?></td>
         <? } ?>
         <td align="left"><?php echo $folnama;?></td>
<!--         <td align="left">&nbsp;</td>     -->
         <td align="center"><?php echo round($dataFolio[$i]["fol_jumlah"]);?></td>
             
         <td align="right"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
          <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_hrs_bayar"])?></td>
          <td width="10%" align='right'><?php echo $dataFolio[$i]["usr_name"]?></td>
     </tr>
<? if($dataFolio[$i]["fol_jenis"]=='O'||$dataFolio[$i]["fol_jenis"]=='OI'
            || $dataFolio[$i]["fol_jenis"]=='OA' ||$dataFolio[$i]["fol_jenis"]=='OG'
            || $dataFolio[$i]["fol_jenis"]=='I'){  ?>
     <tr><td>&nbsp;</td><td colspan ="2">
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse; font-size:8px;">    
    <?php for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) {?>
       <tr>
          <? if($dataFarmasidetail[$x]["item"]=='' || $dataFarmasidetail[$x]["item"]==null){ ?>
          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["barang"];?></td>
          <? } else { ?>
          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item"];?></td>          
          <? } ?>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>
        <td align="right"></td>
       </tr>
              <?php } ?>
 </table>
      </td><td>&nbsp;</td></tr>            
                 
  
       <?php } ?>
           <?php if($dataFolio[$i]["fol_jenis"]=='R' || $dataFolio[$i]["fol_jenis"]=='RA'
           || $dataFolio[$i]["fol_jenis"]=='RI' ||$dataFolio[$i]["fol_jenis"]=='RG'){ ?>
     <tr><td>&nbsp;</td><td colspan ="2">
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse; font-size:8px;">                                   
    <?php for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr>
          <? if($dataReturdetail[$x]["item"]=='' || $dataReturdetail[$x]["item"]==null){ ?>
          <td align="left"> -  <?php echo $dataReturdetail[$x]["barang"];?></td>
          <? } else { ?>
          <td align="left"> -  <?php echo $dataReturdetail[$x]["item"];?></td>          
          <? } ?>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>
              <?php } ?>     
 </table>
      </td><td>&nbsp;</td></tr>
                 
              
           <? }  
          if ($_POST["reg_jenis_pasien"]=='5')
                $totalPembayaran = $dataPasien["pembayaran_total"]; 
          else
               /*$totalPembayaran = $dataPasien["pembayaran_det_total"];
          $totalDijamin = $dataPasien["pembayaran_dijamin"]; 
          $totalSubsidi = $dataPasien["pembayaran_subsidi"];          
          //$totalIur += $dataFolio[$i]["fol_iur_bayar"];
          $totalHrsBayar = $dataPasien["pembayaran_hrs_bayar"]; */
          $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
          $totalDijamin += $dataFolio[$i]["fol_dijamin"];
          $totalDijamin1 += $dataFolio[$i]["fol_dijamin1"];
          $totalDijamin2 += $dataFolio[$i]["fol_dijamin2"];
          $totalSubsidi += $dataFolio[$i]["fol_subsidi"];
          $totalHrsBayar += $dataFolio[$i]["fol_hrs_bayar"];
     
     ?>
    <?php } ?>
  <tr>
    <td align="center" colspan="8">-----------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
  <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="right"><?php echo currency_format($totalPembayaran);?></td>
    <?php if($_POST["reg_jenis_pasien"]=='5') {?>
    <td align="right"><?php if ($_POST["reg_jenis_pasien"]=='5') { echo "0"; } else echo currency_format($totalDijamin);?></td>
    <td align="right"><?php echo currency_format($totalSubsidi);?></td>
    <? } ?>
    <td align="right"><?php echo currency_format($totalHrsBayar);?></td>
  </tr>
  <tr>
    <td align="center" colspan="8">-------------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td align="center" colspan="8">-------------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td width="35%" align="left" colspan='8'>TOTAL TINDAKAN : <b><?php echo currency_format($totalBiaya);?></b></td>
    
  </tr>
  <tr>
  <?php 
  	// $jasa = 0.1 * $totalBiayaJasa;
  	$totalTagihan = $jasa+$totalBiaya-$deposit;
  ?>
    <td width="35%" align="left" colspan='8'>JASA RS : <b><?php echo currency_format($jasa);?></b></td>
    
  </tr>
  <tr>
    <td width="35%" align="left" colspan='8'>DEPOSIT : <b><?php echo currency_format($deposit);?></b></td>
    
  </tr>
  <tr>
    <td width="35%" align="left" colspan='8'>TOTAL TAGIHAN : <b><?php echo currency_format($totalTagihan);?></b></td>
    
  </tr>
  <tr>
    <td align="center" colspan="8">---------------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
 </table> 
 <table width=100% border='0'>
  <tr>
    <td align="right">Dicetak di <? echo $konfigurasi["dep_kota"].", Tanggal ". date("d-m-Y H:i:s");?><br>Printed by <? echo $userName;?></td>
  </tr>
</table>  
</div> 
</div>  
</body>
</html>
