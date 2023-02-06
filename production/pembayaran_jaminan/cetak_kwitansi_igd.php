<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");   
     
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
     $thisPage = "cetak_kwitansi.php";
      
     // KONFIGURASI
       $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
     $_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
    
      if($_GET["id_reg"] || $_GET["jenis"]  || $_GET["ket"] || $_GET["dis"] || $_GET["disper"] 
    || $_GET["pembul"] || $_GET["total"]) {
        $sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin, a.reg_status,
            b.cust_usr_alamat,b.cust_usr_no_jaminan,b.cust_usr_no_identitas,d.poli_nama,g.poli_tipe_nama,
            ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_pembayaran,a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien, a.reg_tipe_rawat,
            a.reg_when_update, c.usr_name,e.jenis_nama,a.reg_kode_trans, a.reg_tipe_paket, f.perusahaan_plafon, f.perusahaan_diskon            
            from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join global.global_perusahaan f on f.perusahaan_id=a.id_perusahaan
            left join global.global_auth_poli_tipe g on g.poli_tipe_id = d.poli_tipe
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    // echo $sql;      
    $dataPasien= $dtaccess->Fetch($sql);
    $view->CreatePost($dataPasien);
    
     //echo $sql;      
        $_POST["id_reg"] = $_GET["id_reg"]; 
        $_POST["fol_jenis"] = $_GET["jenis"]; 
        $_POST["keterangan"] = $_GET["ket"];
        $_POST["diskon"] = $_GET["dis"];
        $_POST["diskonpersen"] = $_GET["disper"];
        $_POST["pembulatan"] = $_GET["pembul"];
        $_POST["total"] = $_GET["total"];
    $_POST["dibayar"] = $_GET["dibayar"];

  //AMBIL DATA FOLIO  
  $sql = "select a.*, b.biaya_paket from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id=a.id_biaya where
            fol_lunas='n' 
            and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
            and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dataFolio = $dtaccess->FetchAll($sql);
//echo $sql;
  for($i=0,$n=count($dataFolio);$i<$n;$i++)
  {
      $total = $dataFolio[$i]["fol_hrs_bayar"];
      $totalBiaya = $totalBiaya+$dataFolio[$i]["fol_nominal"];
      $totalHarga+=$total;
      
      $sqlTerbayar = "update apotik.apotik_penjualan set 
        penjualan_terbayar = 'y' where id_fol = ".QuoteValue(DPE_CHAR,$dataFolio[$i]["fol_id"]);
       
        $dtaccess->Execute($sqlTerbayar);
         unset($sqlTerbayar);
      
      
    }

    //PEMBULATAN
     if($_POST["dep_konf_bulat_ribuan"]=="y"){
        $totalint = substr($totalBiaya,-3);   
        $selisih = 1000-$totalint; 
        if($selisih<>1000)    
        $_POST["bulat"] = $selisih;
        $totalHarga = $totalBiaya + $_POST["bulat"];
     }
     else{  
        if($_POST["dep_konf_bulat_ratusan"]=="y") { 
          $totalint = substr($totalHarga,-2);
          $selisih = 100-$totalint; 
          if($selisih<>100)
          $_POST["bulat"] = $selisih;
          $totalHarga = $totalBiaya + $_POST["bulat"];
        } else {
          $totalHarga = $totalBiaya;
        } 
     }
    
    $sql = "select a.*,b.jbayar_nama,c.* from klinik.klinik_pembayaran a 
            left join global.global_jenis_bayar b on a.id_jbayar = b.jbayar_id
            left join klinik.klinik_pembayaran_det c on a.pembayaran_id = c.id_pembayaran
            where c.pembayaran_det_id = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_id"]).
            " and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dataDiskon = $dtaccess->Fetch($sql);
    
    if($dataDiskon["pembayaran_det_flag"]=="T"){ $flag="01"; }
    elseif($dataDiskon["pembayaran_det_flag"]=="P"){ $flag="02"; }
    elseif($dataDiskon["pembayaran_det_flag"]=="S"){ $flag="03"; }
    
    
    

      // NYARI NOMER KWITANSI
        $sql = "select pembayaran_det_kwitansi as kode from klinik.klinik_pembayaran_det a
              where pembayaran_det_tgl=".QuoteValue(DPE_DATE,$skr)."
              and  pembayaran_det_kwitansi is not null 
              order by pembayaran_det_create desc";
      $lastKode = $dtaccess->Fetch($sql);
      //echo $sql;
      //echo $lastKode["kode"]; die();
      
      $kode=explode(".",$lastKode["kode"]);
      $flg=$kode[0];
      $ins=$kode[1];
      $tgl=$kode[2];
      $no=$kode[3];
      
     
      if($_POST["reg_tipe_rawat"]=="J"){
        $kw1 = "01";
      } elseif($_POST["reg_tipe_rawat"]=="G"){
        $kw1 = "03";
      } elseif($_POST["reg_tipe_rawat"]=="I"){
        $kw1 = "02";
      } 
      
        $_POST["kwitansi_nomor"] = $flag.".".$kw1.".".$tgl1.".".str_pad(($no+1),5,"0",STR_PAD_LEFT);
      
      //update nomer kwitansi 
        $sql = "update klinik.klinik_pembayaran_det set 
        pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]).
        " where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_id"]);
        //echo $sql;
        $dtaccess->Execute($sql);
 
    $sql = "select who_when_update from klinik.klinik_pembayaran_det where 
            pembayaran_det_id = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_id"]);
    $rs = $dtaccess->Execute($sql);
    $kasir = $dtaccess->Fetch($rs);
    
    $sql = "select * from klinik.klinik_pembayaran_det where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
            and is_posting='n' order by pembayaran_det_ke";
    $rs = $dtaccess->Execute($sql);
    $dataPembDet = $dtaccess->FetchAll($rs);
    //echo $sql; die();
    
    for($i=0,$n=count($dataPembDet);$i<$n;$i++){
      if($dataPembDet[$i]["pembayaran_det_flag"]=="T"){ $flag="01"; }
      elseif($dataPembDet[$i]["pembayaran_det_flag"]=="P"){ $flag="02"; }
      elseif($dataPembDet[$i]["pembayaran_det_flag"]=="S"){ $flag="03"; }
      

    }

        //update folio 
        $sql = "update klinik.klinik_pembayaran set pembayaran_who_create = ".QuoteValue(DPE_CHAR,$userName)." 
                where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $dtaccess->Execute($sql);

        if($_GET["uangmuka_id"]){
        $sql = "update klinik.klinik_pembayaran_uangmuka set uangmuka_no_kwitansi=".QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"])."
                where uangmuka_id=".QuoteValue(DPE_CHAR,$_GET["uangmuka_id"]);
        $dtaccess->Execute($sql);
        }
    
        //update folio 
        $sql = "update klinik.klinik_folio set fol_lunas = 'y', 
                fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR,$dataDiskon["pembayaran_det_kwitansi"])." 
                where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." 
                and fol_lunas='n'";
        $dtaccess->Execute($sql); 
                                        
    }
    
      $sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $dataKet = $dtaccess->Fetch($sql);
        $_POST["fol_keterangan"] = $dataKet["fol_keterangan"];  
        //echo $sql;

    $lokasi = $ROOT."/gambar/img_cfg";  
     
    if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
    if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     
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

<!-- Print KwitansiCustom Theme Style -->
    <link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<html>
<head>

<title>Cetak Kwitansi Pembayaran R. Jalan</title>
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
     margin-top:          0px;
     margin-left:     0px;
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
     background-repeat: repeat-x;
     font-weight: bold;
     text-transform: uppercase;
     text-align: center;
     height: 25px;
     background-position: left top;
     cursor:pointer;
}

.menubottom {
     background-image:       url(gambar/submenu_bg.png);
     background-repeat:     no-repeat;
}

.menuleft {
     font-family:           Arial, Helvetica, sans-serif;
     font-size:             12px;
     color:                 #333333;
     background-image:       url(gambar/submenu_btn.png);
     background-repeat:     repeat-y;
     font-weight:           bolder;
}

.menuleft_bawah {
     font-family:           Arial, Helvetica, sans-serif;
     font-size:             8px;
     color:                 #333333;
     background-image:       url(gambar/submenu_btn_bawah.png); 
     font-weight:           bold;   
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
     background-repeat: repeat-x;
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
    background-repeat:  repeat-x;
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
    font-family:    Verdana, Arial, Helvetica, sans-serif;
    font-size:      10px;
    text-indent:    2px;
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
    text-indent:    2px;
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

<body onload="window.print();">
<br>
<!--<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr> <td>&nbsp;</td> </tr>
  <tr> 
    <td>No. Kwitansi</td> <td>:</td>
  <td align="center" width="100%" style="padding-left:200px">&nbsp;<font size="2"><?php echo $_POST["kwitansi_nomor"];?><font size="2"></td> 
</table>-->
<br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
  <tr> 
    <td align="right" width="5%"></td></font><td colspan="4"><img src="<?php echo $fotoName ;?>" height="75"></td>
</tr>
      <tr>
    <td width="5%"></td>
    <td align="left" width="7%">No. Kwitansi </td> 
    <td width = "1%">:</td>  
    <td align="center" width="1%"></td>  
    <td align="left" width="20%"><?php echo $_POST["kwitansi_nomor"];?></td>
    </tr>
  <tr>
    <td width="5%"></td>
    <td align="left" width="7%">Nama Penanggung </td> 
    <td width = "1%">:</td>  
    <td align="center" width="1%"></td>  
    <?php 
    if (!empty($_POST["fol_keterangan"])) { ?>
    <td align="left" width="20%">&nbsp;<?php echo $dataKet["fol_keterangan"];?></td>  
    <?php } else {?>
    <td align="left" width="20%">&nbsp;<?php echo $dataPasien["cust_usr_nama"];?></td>  
    <?php } ?>
    </tr>
  <tr>
    <td width="5%"></td>
    <td align="left" width="10%">Klinik </td>
    <td width = "1%">:</td>
    <td align="center" width="1%"></td>
    <?php if($dataPasien["cust_usr_kode"]=="500"){?>  
    <td align="left" width="20%">&nbsp;Biaya Periksa Dokter Pasien JKN Naik Kelas</td>
    <?php } else { ?>
    <td align="left" width="20%">&nbsp;<?php echo $dataPasien["poli_tipe_nama"].' - '.$dataPasien["poli_nama"];?></td>
    <?php } ?>  
  </tr>
  <tr> 
    <td width="5%"></td>
    <td align="left" width="7%">Nama Pasien </td>
    <td width = "1%">:</td>  
    <td align="center" width="1%"></td>  
    <?php if ($dataPasien["cust_usr_kode"]=="500" || $dataPasien["cust_usr_kode"]=="100") { ?>
    <td align="left" width="20%">&nbsp;<?php echo $dataKet["fol_keterangan"];?></td>  
    <?php } else {?>
    <td align="left" width="20%">&nbsp;<?php echo $dataPasien["cust_usr_nama"];?></td>  
    <?php } ?>  
  </tr>
  <?php if($dataPasien["cust_usr_kode"]<>"500"){?>
  <tr>
    <td width="5%"></td>
    <td align="left" width="7%">No. RM</td>  
    <td width = "1%">:</td>
    <td align="center" width="1%"></td>  
    <td align="left" width="20%">&nbsp;<?php echo $dataPasien["cust_usr_kode"];?></td>  
  </tr>
  <?php } ?>
    <tr>
      <td width="5%"></td>
      <td  align="left" width="7%">Total </td>
      <td width = "1%">:</td>  
      <td  align="center" width="1%"></td>  
       <!-- <td  align="left" width="20%"> &nbsp;Rp. <?php echo currency_format($totalHarga+$dataDiskon["pembayaran_det_service_cash"]);?></td> -->
       <td  align="left" width="20%"> &nbsp;Rp. <?php echo currency_format($_POST["total"]);?></td>
    </tr>
    <!-- <?php if($dataDiskon["pembayaran_det_total"]<>$dataDiskon["pembayaran_det_dibayar"]) {?>
    <?if($dataDiskon["pembayaran_det_diskon"]<>"0"){?>
    <tr>
      <td width="5%"></td>
      <td  align="left" width="7%">Diskon</td>
      <td width = "1%">:</td>  
      <td  align="center" width="1%"></td>  
      <td  align="left" width="20%"> &nbsp;Rp. <?php echo currency_format($dataDiskon["pembayaran_det_diskon"]);?></td>
    </tr>
    <?php } ?>    
    <tr>
      <td width="5%"></td>
      <td  align="left" width="7%"></td> 
      <td width = "1%"></td> 
      <td  align="center" width="1%"></td>  
      <td  align="left" width="20%"> &nbsp;Rp. <?php echo currency_format($dataDiskon["pembayaran_det_dibayar"]+$dataDiskon["pembayaran_det_service_cash"]);?></td>
    </tr>
    <?if($dataDiskon["pembayaran_det_diskon"]=="0" || $dataDiskon["pembayaran_det_diskon"]==null){?>
    <tr>
      <td width="5%"></td>
      <td  align="left" width="7%"></td>
      <td width = "1%"></td>  
      <td  align="center" width="1%"></td>  
      <td  align="left" width="20%"> &nbsp;Rp. <?php echo currency_format($dataDiskon["pembayaran_det_total"]-$dataDiskon["pembayaran_det_dibayar"]-$dataDijamin["pembayaran_det_dijamin"]+$dataDiskon["pembayaran_det_service_cash"]);?></td>
    </tr>
    <?php }} ?> -->
  <tr>
    <td width="5%"></td>
    <td  align="left" width="7%">Terbilang</td> 
    <td width = "1%">:</td> 
    <td  align="center" width="1%"></td>  
    <!-- <td  align="left" width="20%">&nbsp;<?php if($dataDiskon["pembayaran_det_total"]<>$dataDiskon["pembayaran_det_dibayar"]) {echo terbilang($dataDiskon["pembayaran_det_dibayar"]+$dataDiskon["pembayaran_det_service_cash"]);} else echo terbilang($totalHarga+$dataDiskon["pembayaran_det_service_cash"]);?> Rupiah</td>   -->
    <td  align="left" width="20%">&nbsp;<?php echo terbilang($_POST["total"]);?> Rupiah</td>  
  </tr>
</table>
<br>

<table width="100%" border="0">
  <tr>
    <td  align="center" style="padding-left:500px"><?php //echo $konfigurasi["dep_kota"];?> <?php echo date("d-m-Y");?></td>
  </tr>
  <tr>
    <td align="center"></td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td  align="center" style="padding-left:500px">(<?php echo $userName;?> )</td>
  </tr>
</table>  
</div>  
</body>
</html>
