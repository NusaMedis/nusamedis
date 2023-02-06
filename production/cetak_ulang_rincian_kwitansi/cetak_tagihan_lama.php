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
	
	if($_GET["id_reg"] || $_GET["jenis"]  || $_GET["ket"] || $_GET["dis"] || $_GET["disper"] || $_GET["pembul"] || $_GET["total"]) {
		$sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin,
            b.cust_usr_alamat,b.cust_usr_no_jaminan,b.cust_usr_no_identitas,d.poli_nama,
            ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_pembayaran,a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien , a.reg_when_update,
            c.usr_name,e.jenis_nama,a.reg_kode_trans,f.kelas_nama,h.kamar_nama,g.rawatinap_tanggal_keluar,g.rawatinap_tanggal_masuk, k.jkn_nama, 
            a.reg_no_sep, a.reg_kelas, l.perusahaan_nama, a.reg_diagnosa_inap, hak_kelas_inap,a.id_poli,b.cust_usr_jkn
            from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join klinik.klinik_kelas f on a.reg_kelas = f.kelas_id
            left join klinik.klinik_rawatinap g on g.id_reg = a.reg_id
            left join klinik.klinik_kamar h on h.kamar_id = g.id_kamar
            left join klinik.klinik_kamar_bed i on i.bed_id = g.id_bed and i.id_kamar = g.id_kamar
            left join global.global_jkn k on k.jkn_id = b.cust_usr_jkn
            left join global.global_perusahaan l on l.perusahaan_id = b.id_perusahaan
             
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
    $_POST["kamar_nama"] = $dataPasien["kamar_nama"];
    $_POST["kelas_nama"] = $dataPasien["kelas_nama"];
    $_POST["rawatinap_tanggal_keluar"] = $dataPasien["rawatinap_tanggal_keluar"];
    $_POST["rawatinap_tanggal_masuk"] = $dataPasien["rawatinap_tanggal_masuk"];
    $_POST["reg_diagnosa_inap"] = $dataPasien["reg_diagnosa_inap"];
    $_POST["reg_kelas"] = $dataPasien["reg_kelas"];
    $_POST["hak_kelas_inap"] = $dataPasien["hak_kelas_inap"];
    $_POST["id_poli"] = $dataPasien["id_poli"];
    $_POST["cust_usr_jkn"] = $dataPasien["cust_usr_jkn"];
     //echo  $_POST["reg_kelas"] ." - " .$dataPasien["reg_kelas"];
    $kelas = array('1','2','3');
    if($_POST["reg_kelas"]=='2') {$kls=$kelas[0];}
    elseif($_POST["reg_kelas"]=='3') {$kls=$kelas[1];;}
    elseif($_POST["reg_kelas"]=='4') {$kls=$kelas[2];}   
    //echo $dataPasien["reg_kelas"]."-".$kls;
    /*if($_POST["reg_kelas"]='2') {$kls='1';}
    elseif($_POST["reg_kelas"]='3') {$kls='2'; echo " -- ".$dataPasien["reg_kelas"];}
    elseif($_POST["reg_kelas"]='4') {$kls='3';}
    echo $dataPasien["reg_kelas"]."-".$kls;       */
    
    $sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
	$dataKet = $dtaccess->Fetch($sql);
	$_POST["fol_keterangan"] = $dataKet["fol_keterangan"];	
    
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
    //$totalHarga=$dataFolio[0]["fol_total_harga"];
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
     } 
	 else {
	     $totalHarga=$totalBiaya-$dijaminHarga-$inacbg["inacbg_topup"];
	 }
   } 
   /*else {
     $totalHarga=$totalHarga-$uangmuka["uangmuka_jml"];
   }*/
elseif($_POST["reg_jenis_pasien"]=="24"){
     $totalHarga=$totalBiaya-10000000;
   } else {
     $totalHarga=$totalHarga;
   }
   if ($totalHarga<0 && $totalakomodasi > 0) {$totalHarga=$totalakomodasi;} elseif ($totalHarga<0) {$totalHarga=0;} 
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga-$uangmuka["total"];
   
   if(($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="3") || ($_POST["hak_kelas_inap"]=="2" && $_POST["reg_kelas"]=="2") || ($_POST["hak_kelas_inap"]=="3" && $_POST["reg_kelas"]=="2")){
   $selisih = ($dijaminHarga+$inacbg["inacbg_topup"])-($tarif["tarif_inacbg"]+$inacbg["inacbg_topup"]);
   } else {
   $selisih = ($dijaminHarga+$inacbg["inacbg_topup"])-$totalBiaya;
   }
   //if($selisih<0) $selisih=0;
    
    $sql = "select * from klinik.klinik_pembayaran where 
            pembayaran_jenis = 'T' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
            " and id_dep=".QuoteValue(DPE_CHAR,$depId);
		$dataDiskon = $dtaccess->Fetch($sql);
    
     }    
     		  
     
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

<title>Cetak Tagihan Rincian Rawat Inap Sementara</title>

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
  <td></td> 
  <td align="center" width="100%">&nbsp;<font size="2">RINGKASAN BIAYA PERAWATAN RAWAT INAP SEMENTARA<font size="2"></td>  
  </tr>
</table>
<br><br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse"> 
  <tr> 
	  <td align="left" width="20%">&nbsp;<br></br></td> 
  </tr>
  <tr>
    <td align="left" width="7%">No. Registrasi</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["reg_kode_trans"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Ruang Rawat</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["kamar_nama"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">Tgl Masuk</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".format_date($_POST["rawatinap_tanggal_masuk"]);?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Paket Jaminan</td>
    <td align="center" width="1%">:</td>  
    <?php if($_POST["reg_jenis_pasien"]=='18') {?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$NamaKotajamkesda["jamkesda_kota_nama"];?></td>
	  <?php } else if($_POST["reg_jenis_pasien"]=='5') {?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["jkn_nama"];?></td>
    <?php } else if($_POST["reg_jenis_pasien"]=='7') {?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["perusahaan_nama"];?></td>    
    <? } else { ?>
     <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["jenis_nama"];?></td> 
    <? }?>
  </tr>
  <tr>
    <td align="left" width="7%">Tgl Keluar</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".format_date($_POST["rawatinap_tanggal_keluar"]);?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">No. Peserta</td>
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_no_identitas"];?></td>  
  </tr>
  <tr>
    <td align="left" width="10%">Cara Bayar</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["jenis_nama"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    
    <?php if($_POST["reg_jenis_pasien"]=='5') { ?>
    <td align="left" width="10%">No. SEP </td>
    <? } else { ?>
    <td align="left" width="10%">No. Surat Jaminan</td>
    <? } ?>
    <td align="center" width="1%">:</td>  
    <?php if($_POST["reg_jenis_pasien"]=='5') { ?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["reg_no_sep"];?></td> 
    <? } else { ?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_no_jaminan"];?></td> 
    <? }?> 
  </tr>

  <tr>
    <td align="left" width="7%">No. RM</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_kode"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Kelas Rawat</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["kelas_nama"];?></td>  
  </tr>
  <tr>
    <td align="left" width="7%">Nama Pasien</td>  
    <td align="center" width="1%">:</td>  
    <?if ($_POST["fol_keterangan"]<>'') { ?>
        <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$_POST["fol_keterangan"];?></td>  
    <? } else { ?>
        <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_nama"];?></td>      
    <? } ?>
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Kelas Hak</td>  
    <td align="center" width="1%">:</td>
    <?php if($dataPasien["hak_kelas_inap"]=='1') $hak="Kelas I";
          elseif($dataPasien["hak_kelas_inap"]=='2') $hak="Kelas II";
          elseif($dataPasien["hak_kelas_inap"]=='3') $hak="Kelas III";
    ?>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$hak;?></td>  
  </tr>
  <tr>
    <td rowspan="2" align="left" width="7%">Alamat</td>  
    <td rowspan="2" align="center" width="1%">:</td>  
    <td rowspan="2" align="left" width="20%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_alamat"];?></td>  
    <td align="center" width="5%">&nbsp;</td>
    <td align="left" width="10%">Diagnosa</td>  
    <td align="center" width="1%">:</td>
    <td align="left" width="20%">&nbsp;<?php echo "&nbsp;".$diagnosa["icd_nama"];?></td>  
  </tr>
  <tr>
   <td colspan="4" align="center" width="5%">&nbsp;</td>
  </tr>

</table>
<br><br><br><br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center" colspan="8">---------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td width="25%" align="center">DESKRIPSI</td>
    <td width="5%" align="center">JML</td>
    <td width="10%" align="right">TAGIHAN</td>
  <?php  if ($_POST["reg_jenis_pasien"]=='18') { ?>
    <td width="10%" align="center">DIJAMIN DINKES <br>PROP</td>
    <td width="10%" align="center">DIJAMIN DINKES <br>&nbsp;KAB&nbsp;</td>
	<?php } else  {?>
    <td width="10%" align="right">DIJAMIN</td>
    <td width="10%" align="right">SUBSIDI</td>
	<?php }?>
    <td width="15%" align="right">HRS. BAYAR</td>
    <td width="15%" align="right"></td>
  </tr>
  <tr>
    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>

    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {?>
    
	<?php 
  if($dataFolio[$i]["fol_jenis"]!='O') {
  if ($dataFolio[$i]["biaya_id"]==$dataFolio[$i-1]["biaya_id"])   {
	$jumlahFol = $jumlahFol + $dataFolio[$i]["fol_jumlah"];
  $jumNominal = $jumNominal + $dataFolio[$i]["fol_nominal"];
  $jumDijamin = $jumDijamin + $dataFolio[$i]["fol_dijamin"]; 
  $jumDijamin1 = $jumDijamin1 + $dataFolio[$i]["fol_dijamin1"]; 
  $jumDijamin2 = $jumDijamin2 + $dataFolio[$i]["fol_dijamin2"];
  $jumSubsidi = $jumSubsidi + $dataFolio[$i]["fol_subsidi"];
  $jumHrsBayar = $jumHrsBayar + $dataFolio[$i]["fol_hrs_bayar"];
	}	
  else
  {
   $jumlahFol = $dataFolio[$i]["fol_jumlah"];
   $jumNominal = $dataFolio[$i]["fol_nominal"];
   $jumDijamin = $dataFolio[$i]["fol_dijamin"];
   $jumDijamin1 = $dataFolio[$i]["fol_dijamin1"];
   $jumDijamin2 = $dataFolio[$i]["fol_dijamin2"];
   $jumSubsidi = $dataFolio[$i]["fol_subsidi"]; 
   $jumHrsBayar = $dataFolio[$i]["fol_hrs_bayar"];
  } } 
  else if($dataFolio[$i]["fol_jenis"]=='O') { 
   $jumlahFol = $dataFolio[$i]["fol_jumlah"];
   $jumNominal = $dataFolio[$i]["fol_nominal"];
   $jumDijamin = $dataFolio[$i]["fol_dijamin"];
   $jumDijamin1 = $dataFolio[$i]["fol_dijamin1"];
   $jumDijamin2 = $dataFolio[$i]["fol_dijamin2"];
   $jumSubsidi = $dataFolio[$i]["fol_subsidi"]; 
   $jumHrsBayar = $dataFolio[$i]["fol_hrs_bayar"];
  } 
   ?>
      
      
      <?php if($dataFolio[$i]["kategori_kassa_id"]!=$dataFolio[$i-1]["kategori_kassa_id"]) 
//      && ($dataFolio[$i]["fol_jenis"]!='O' || $dataFolio[$i]["fol_jenis"]!='OI' || $dataFolio[$i]["fol_jenis"]!='OA' || $dataFolio[$i]["fol_jenis"]!='OG' || $dataFolio[$i]["fol_jenis"]!='RI' || $dataFolio[$i]["fol_jenis"]!='RA' || $dataFolio[$i]["fol_jenis"]!='RG')) 
      { ?>    
    	<tr> 
      <td width="25%" align="center"> </td> </tr>
                 
    	<tr> 
    		      <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
    	</tr>    	
    	<tr> 
              <td align="left"><?php echo $dataFolio[$i]["kategori_kassa_nama"];?></td> 
      </tr>
      
       
	   <?php } ?> 
     
	 <? if ($dataFolio[$i]["biaya_id"]!=$dataFolio[$i+1]["biaya_id"]) { ?>
      <? if ($dataFolio[$i]["fol_jenis"]!='O'  && $dataFolio[$i]["fol_jenis"]!='OI' && 
       $dataFolio[$i]["fol_jenis"]!='OA' && $dataFolio[$i]["fol_jenis"]!='OG' && 
       $dataFolio[$i]["fol_jenis"]!='RI' && $dataFolio[$i]["fol_jenis"]!='RA' && $dataFolio[$i]["fol_jenis"]!='RG')  { ?>
              
        
      <tr>  
          <td align="left"><?php echo $dataFolio[$i]["fol_nama"];?></td>
          
          <td align="center"><?php echo round($jumlahFol);?></td>
          <td align="right"><?php echo currency_format($jumNominal);?></td>
           
          <?php if ($_POST["reg_jenis_pasien"]==18) {?>          
          <td width="10%" align='right'><?php echo currency_format($jumDijamin1)?></td>          
          <td width="10%" align='right'><?php echo currency_format($jumDijamin2)?></td>
          <?php } else {?>
            
          <td width="10%" align='right'><?php if ($_POST["reg_jenis_pasien"] == 5 ){echo currency_format(0);} else {echo currency_format($jumDijamin);}?></td>
          <td width="10%" align='right'><?php echo currency_format($jumSubsidi)?></td>  
            
          <?php } ?>
            
  		<!--<td width="10%" align='right'><?php //echo currency_format($dataFolio[$i]["fol_iur_bayar"])?></td> --> 
          <td width="10%" align='right'><?php echo currency_format($jumHrsBayar)?></td>      
       </tr>     
      
              <? 
              // sub total tagihan 
      
              /*      if ($dataFolio[$i]["kategori_kassa_id"]==$dataFolio[$i-1]["kategori_kassa_id"]) {                                                                
                    $SubTotalJumlah += $jumlahFol;
                    $SubTotalNominal += $jumNominal;
                    $SubTotalDijamin += $jumDijamin;
                    $SubTotalDijamin1 += $jumDijamin1;
                    $SubTotalDijamin2 += $jumDijamin2;
                    $SubTotalSubsidi += $jumSubsidi; 
                    $SubTotalHrsBayar += $jumHrsBayar;}
                    else {                    
                    $SubTotalJumlah = $jumlahFol;
                    $SubTotalNominal = $jumNominal;
                    $SubTotalDijamin = $jumDijamin;
                    $SubTotalDijamin1 = $jumDijamin1;
                    $SubTotalDijamin2 = $jumDijamin2;
                    $SubTotalSubsidi = $jumSubsidi; 
                    $SubTotalHrsBayar = $jumHrsBayar;}  */
                          
             // else if ($dataFolio[$i]["kategori_kassa_id"]!=$dataFolio[$i-1]["kategori_kassa_id"]) {
            
              //}           
              ?> 
        <?php } ?>          
    <?php } ?> 
         
    
    
    
    
    <?php if (($dataFolio[$i]["kategori_kassa_id"]!=$dataFolio[$i-1]["kategori_kassa_id"]) && 
               ($dataFolio[$i]["fol_jenis"]=='O' || $dataFolio[$i]["fol_jenis"]=='OI' || $dataFolio[$i]["fol_jenis"]=='OA' ||
                $dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='RI' || $dataFolio[$i]["fol_jenis"]=='RA' || 
                $dataFolio[$i]["fol_jenis"]=='RG')){ ?>
	  <tr> <td width="25%" align="center"> </td> </tr>
	   
 <!-- <tr> 
    	<td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
    	</tr>
      <tr>
       <td width="10%" align='right'></td>
       <td width="10%" align='center'>Sub Total</td>
       <td width="10%" align='center'><?php echo currency_format($SubTotalJumlah)?></td>
       <td width="10%" align='right'><?php echo currency_format($SubTotalNominal)?></td>
       <?php if ($_POST["reg_jenis_pasien"]==18) {?>       
       <td width="10%" align='right'><?php echo currency_format($SubTotalDijamin1)?></td>
       <td width="10%" align='right'><?php echo currency_format($SubTotalDijamin2)?></td>
       <? } else {?>
       <td width="10%" align='right'><?php echo currency_format($SubTotalDijamin)?></td>
       <td width="10%" align='right'><?php echo currency_format($SubTotalSubsidi)?></td>
       <? } ?>
       <td width="10%" align='right'><?php echo currency_format($SubTotalHrsBayar)?></td>
      </tr> -->
       
       <tr> 
		    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
	    </tr>     
	
      
	     <tr> 
              <td align="left"><?php echo $dataFolio[$i]["kategori_kassa_nama"];?></td> 
      </tr>  
	  <?php } ?>
    
    <?php if ($dataFolio[$i]["fol_jenis"]=='O' || $dataFolio[$i]["fol_jenis"]=='OI' || 
    $dataFolio[$i]["fol_jenis"]=='OA' || $dataFolio[$i]["fol_jenis"]=='OG' || $dataFolio[$i]["fol_jenis"]=='RI' 
    || $dataFolio[$i]["fol_jenis"]=='RA' || $dataFolio[$i]["fol_jenis"]=='RG') { ?>
        <tr> 
               <td align="left"><?php echo $dataFolio[$i]["fol_nama"];?>(<?php echo $dataFolio[$i]["fol_catatan"];?>)</td>                                                                                
               <td align="center"><?php echo round($dataFolio[$i]["fol_jumlah"]);?></td>
               <td align="right"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
                
               <?php if ($_POST["reg_jenis_pasien"]==18) {?>          
               <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_dijamin1"])?></td>          
               <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_dijamin2"])?></td>
               <?php } else {?>
               <td width="10%" align='right'><?php if ($_POST["reg_jenis_pasien"] == 5 ){echo currency_format(0);}else {echo currency_format($dataFolio[$i]["fol_dijamin"]);}?></td>
               <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_subsidi"])?></td>
               <?php } ?>
               <!--<td width="10%" align='right'><?php //echo currency_format($dataFolio[$i]["fol_iur_bayar"])?></td> -->
               <td width="10%" align='right'><?php echo currency_format($dataFolio[$i]["fol_hrs_bayar"])?></td>          
        </tr>
              <? 
              // sub total tagihan
              /* if ($dataFolio[$i]["kategori_kassa_id"]==$dataFolio[$i-1]["kategori_kassa_id"]) {
              $SubTotalJumlah += $jumlahFol;
              $SubTotalNominal += $jumNominal;
              $SubTotalDijamin += $jumDijamin;
              $SubTotalDijamin1 += $jumDijamin1;
              $SubTotalDijamin2 += $jumDijamin2;
              $SubTotalSubsidi += $jumSubsidi; 
              $SubTotalHrsBayar += $jumHrsBayar;} 
              else {
              $SubTotalJumlah = $jumlahFol;
              $SubTotalNominal = $jumNominal;
              $SubTotalDijamin = $jumDijamin;
              $SubTotalDijamin1 = $jumDijamin1;
              $SubTotalDijamin2 = $jumDijamin2;
              $SubTotalSubsidi = $jumSubsidi; 
              $SubTotalHrsBayar = $jumHrsBayar;} */           
              ?> 
       
    <?php }?>  
       
    <?php 
          $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
          $totalDijamin += $dataFolio[$i]["fol_dijamin"];
          $totalDijamin1 += $dataFolio[$i]["fol_dijamin1"];
          $totalDijamin2 += $dataFolio[$i]["fol_dijamin2"];
          $totalSubsidi += $dataFolio[$i]["fol_subsidi"];
          $totalHrsBayar += $dataFolio[$i]["fol_hrs_bayar"];
     
    ?>

	 
    <?php }?>
  <tr>
    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="right">&nbsp;<?php echo currency_format($totalPembayaran);?></td>
    
    <?php if ($_POST["reg_jenis_pasien"]==18) {?>  
    <td align="right"><?php echo currency_format($totalDijamin1);?></td>
    <td align="right"><?php echo currency_format($totalDijamin2);?></td>
    <?php } else {?>
    
    <td align="right"><?php if ($_POST["reg_jenis_pasien"] == 5 ){echo currency_format(0);} else {echo currency_format($totalDijamin);}?></td>
    <td align="right"><?php echo currency_format($totalSubsidi);?></td>
    <?php }?>

    <td align="right"><?php echo currency_format($totalHrsBayar);?></td>
   
  </tr>
  
  <tr>
    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td width="25%" align="left">TOTAL TAGIHAN : <?php echo currency_format($totalBiaya);?></td>
    <td align="center"></td>
    <?php if($dataPasien["reg_jenis_pasien"]=="7" || $dataPasien["reg_jenis_pasien"]=="18") {?>
    <td align="right">TOTAL PIUTANG : <?php echo currency_format($totalPembayaran);?></td>
    <?php } elseif($dataPasien["reg_jenis_pasien"]=="5") {?>
    <td align="right">TOTAL DIJAMIN : <?php echo currency_format(($inacbg["inacbg_dijamin"]+$inacbg["inacbg_topup"]));?></td>
    <?php } else { ?>
    <td align="right">TOTAL PIUTANG : 0</td>
    <?php } ?>
    <td width="10%"align="right">&nbsp;</td>
    <td align="right"><?php if($dataPasien["reg_jenis_pasien"]=="5") {
    if ($_POST["reg_kelas"] > $dataPasien["hak_kelas_inap"]){
    echo "TOTAL SUBSIDI : ";  echo currency_format($totalHarga) ;
    }
     else {
     echo "TOTAL SUBSIDI : 0"; //if(($tarif["tarif_inacbg"]+$inacbg["inacbg_topup"])<$totalBiaya) { if($TotalSubsidi>0) { echo currency_format($TotalSubsidi);} else echo currency_format($totalHarga);} 
     //else echo currency_format($selisih); 
     }
                                                                             
     } else echo "UANG MUKA : ".currency_format($uangmuka["total"]);?></td>
    <td width="10%"align="right">&nbsp;</td>
    <td align="right">TOTAL HRS BAYAR : <?php if($dataPasien["reg_jenis_pasien"]=="5") {
    if ($_POST["reg_kelas"] > $dataPasien["hak_kelas_inap"]){
    echo currency_format(0) ;
    } else {
    echo currency_format($totalHarga);
    //if($TotalSubsidi>0){ echo "0";} else echo "0"; 
    }
    
    } else echo currency_format($totalHrsBayar);?></td>
  </tr>
  <tr>                                      
    <td align="center" colspan="8">--------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr>

</table>
<br>
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
    <td>: <?php echo currency_format($selisih);?></td>
  </tr>
</table>
<?php } ?>
<br>
<table width="100%" border="0">
  <tr>
    <td align="right">Dicetak di <? echo $konfigurasi["dep_kota"].", Tanggal ". date("d-m-Y H:i:s");?><br>Printed by <? echo $userName;?></td>
  </tr>
</table>  
</div>  
</body>
</html>
