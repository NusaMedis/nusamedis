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
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
	
	if($_GET["id_reg"] || $_GET["jenis"]  || $_GET["ket"] || $_GET["dis"] || $_GET["disper"] || $_GET["pembul"] || $_GET["total"]) {
		$sql = "select l.jkn_nama,b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_no_hp,b.cust_usr_jenis_kelamin,
            b.cust_usr_alamat,a.reg_no_sep,b.cust_usr_no_identitas,d.poli_nama, f.fol_keterangan, 
            ((current_date - cust_usr_tanggal_lahir)/365) as umur,  g.pembayaran_create,a.id_pembayaran,a.id_poli,a.id_cust_usr ,a.reg_jenis_pasien , a.reg_when_update,
            c.usr_name,e.jenis_nama,a.reg_kode_trans,a.reg_tanggal,g.*,h.*, a.reg_tipe_paket, m.perusahaan_plafon, m.perusahaan_diskon,n.jbayar_nama
            from klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran g on g.pembayaran_id=a.id_pembayaran
            left join klinik.klinik_pembayaran_det h on h.id_pembayaran=g.pembayaran_id
            left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join global.global_auth_user c on c.usr_id = a.id_dokter 
            left join global.global_auth_poli d on a.id_poli = d.poli_id
            left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
            left join klinik.klinik_folio f on f.id_reg = a.reg_id 
            left join global.global_jkn l on l.jkn_id=a.reg_tipe_jkn
            left join global.global_perusahaan m on m.perusahaan_id=a.id_perusahaan
            left join global.global_jenis_bayar n on n.jbayar_id = h.id_jbayar
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
    $_POST["reg_tipe_paket"] = $dataPasien["reg_tipe_paket"];
    $_POST["perusahaan_plafon"] = $dataPasien["perusahaan_plafon"];
    $_POST["perusahaan_diskon"] = $dataPasien["perusahaan_diskon"];
    $keterangan=explode("-",$dataPasien["fol_keterangan"]);
    $terima = $keterangan[0];
    $periode = $keterangan[1];
    
    // nyari petugas yg bayar --
    $sql = "select pembayaran_who_create from klinik.klinik_pembayaran where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
    $rs = $dtaccess->Execute($sql);
    $petugas = $dtaccess->Fetch($rs);
    
     //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' and jenis_id =".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->Fetch($rs);

    $sql = "select a.*, b.biaya_paket from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id=a.id_biaya where
            fol_lunas='n' 
            and id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." 
            and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
		$dataFolio = $dtaccess->FetchAll($sql);
		// echo "absabsajsbas";
/*		for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
          if($dataFolio[$i]["fol_jumlah"]){
           if($dataFolio[$i]["fol_jumlah"]!='0.00') $total = $dataFolio[$i]["fol_jumlah"]*$dataFolio[$i]["fol_total_harga"]; else $total = $dataFolio[$i]["fol_total_harga"];
          }else{
            $total = $dataFolio[$i]["fol_total_harga"];
          }
          $totalHarga+=$total;
		} */
    for($i=0,$n=count($dataFolio);$i<$n;$i++){
		 $total = $dataFolio[$i]["fol_hrs_bayar"];
              $totalBiaya = $totalBiaya+$dataFolio[$i]["fol_nominal"];
              $dijamin = $dataFolio[$i]["fol_dijamin"];
              if($dataFolio[$i]["biaya_paket"]=="n"){
              $totalNonPaket += $dataFolio[$i]["fol_nominal"];
              }
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
          $totalDijamin+=$dijamin;
    }
    //$totalHarga=$dataFolio[0]["fol_total_harga"];
    
    $sql = "select * from global.global_konfigurasi_fasilitas where konf_fasilitas_id='1'";
   $rs = $dtaccess->Execute($sql);
   $konFasilitas = $dtaccess->Fetch($rs);
   
   $sql = "select * from global.global_detail_paket where detail_paket_id=".QuoteValue(DPE_CHAR,$_POST["reg_tipe_paket"]);
   $rs = $dtaccess->Execute($sql);
   $detPaket = $dtaccess->Fetch($rs);
   
   $sql = "select * from global.global_konf_jasa_raharja where konf_jasa_raharja_id='1'";
   $rs = $dtaccess->Execute($sql);
   $konfJR = $dtaccess->Fetch($rs);
   
    $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
            and uangmuka_jml>0";
    $uangmuka = $dtaccess->Fetch($sql);
    
    $sql = "select * from global.global_auth_poli where poli_tipe='P'";
    $rs = $dtaccess->Execute($sql);
    $op = $dtaccess->Fetch($rs);
    
    $sql = "select * from klinik.klinik_inacbg where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
   $rs = $dtaccess->Execute($sql);
   $inacbg = $dtaccess->Fetch($rs);
    
    $sql = "select pembayaran_dijamin from  klinik.klinik_pembayaran
    where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);

   $rs_dijamin = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   $dataDijamin = $dtaccess->Fetch($rs_dijamin);
   
   //total biaya
   $totalBiaya=$totalBiaya;   
   //harga dijamin
   $dijaminHarga = $dataDijamin["pembayaran_dijamin"];
   
   //perhitungan rumus JKN
   if(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $_POST["id_poli"]==$op["poli_id"]){
   $totalHarga=$totalHarga;
   } elseif(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $totalBiaya > $dijaminHarga){
   $totalHarga=$totalBiaya-$dijaminHarga;
   } elseif(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $totalBiaya < $dijaminHarga){
   $totalHarga=$dijaminHarga-$totalBiaya;
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_FASILITAS){ //fasilitas
    if($konFasilitas["konf_fasilitas_pagu"]>0){
      if($konFasilitas["konf_fasilitas_diskon_irj"]>0){
        $diskon = ($konFasilitas["konf_fasilitas_diskon_irj"]/100)*$totalBiaya;
        if(($totalBiaya-$diskon)>$konFasilitas["konf_fasilitas_pagu"]){
        $totalHarga = ($totalBiaya-$diskon)-$konFasilitas["konf_fasilitas_pagu"];
        } else {
        $totalHarga = 0;
        }
        $_POST["txtDiskon"] = $diskon;
        $_POST["txtDiskonPersen"] = currency_format($konFasilitas["konf_fasilitas_diskon_irj"]);
      } else {
        if($totalBiaya>$konFasilitas["konf_fasilitas_pagu"]){
        $totalHarga = $totalBiaya - $konFasilitas["konf_fasilitas_pagu"];
        } else {
        $totalHarga = 0;
        }
      }
    } else {
      if($konFasilitas["konf_fasilitas_diskon_irj"]>0){
        $diskon = ($konFasilitas["konf_fasilitas_diskon_irj"]/100)*$totalBiaya;
        $totalHarga = $totalBiaya-$diskon;
        $_POST["txtDiskon"] = $diskon;
        $_POST["txtDiskonPersen"] = currency_format($konFasilitas["konf_fasilitas_diskon_irj"]);
      } else {
        $totalHarga = 0;
      }
    }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JASA_RAHARJA){ //jasa raharja
    if($konfJR["konf_jasa_raharja_pagu"]>0){
      if($totalBiaya>$konfJR["konf_jasa_raharja_pagu"]){
      $totalHarga = $totalBiaya - $konfJR["konf_jasa_raharja_pagu"];
      } else {
      $totalHarga = 0;
      }
    } else {
      $totalHarga = $totalBiaya;
    }
   }elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PAKET){
     $totalHarga = $detPaket["detail_paket_nominal"]+$totalNonPaket;
   }elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_JASA_RAHARJA){
     if($_POST["id_poli"]==$op["poli_id"]){$totalHarga=$totalHarga;}
     elseif($totalBiaya > $dijaminHarga){
      $totalHarga=$totalBiaya-$dijaminHarga;
     }else{
      $totalHarga=$dijaminHarga-$totalBiaya;
     }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_FASILITAS){
     if($_POST["id_poli"]==$op["poli_id"]){$totalHarga=$totalHarga;}
     elseif($totalBiaya > $dijaminHarga){
      $totalHarga=$totalBiaya-$dijaminHarga;
     }else{
      $totalHarga=$dijaminHarga-$totalBiaya;
     }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_IKS){
     if($_POST["perusahaan_diskon"]>0){
      $diskon = ($_POST["perusahaan_diskon"]/100)*$totalBiaya;
      $_POST["txtDiskon"] = $diskon;
      $_POST["txtDiskonPersen"] = currency_format($_POST["perusahaan_diskon"]);
      if($_POST["perusahaan_plafon"]>0){
        if($_POST["perusahaan_plafon"]>($totalBiaya-$diskon)){
          $totalHarga = 0;
        } else {
          $totalHarga = ($totalBiaya-$diskon) - $_POST["perusahaan_plafon"];
        }
      } else {
        $totalHarga = $totalBiaya-$diskon;
      }
     } else {
      if($_POST["perusahaan_plafon"]>0){
        if($_POST["perusahaan_plafon"]>$totalBiaya){
          $totalHarga = 0;
        } else {
          $totalHarga = $totalBiaya - $_POST["perusahaan_plafon"];
        }
      } else {
        $totalHarga = 0;
      }
     }
   } else{
    $totalHarga=$totalHarga;
    } 
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga;
    
    $sql = "select a.*,b.jbayar_nama,c.* from klinik.klinik_pembayaran a 
            left join global.global_jenis_bayar b on a.id_jbayar = b.jbayar_id
            left join klinik.klinik_pembayaran_det c on a.pembayaran_id = c.id_pembayaran
            where c.pembayaran_det_id = ".QuoteValue(DPE_CHAR,$dataPasien["pembayaran_det_id"]).
            " and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dataDiskon = $dtaccess->Fetch($sql);
    
    $sql = "select * from klinik.klinik_pembayaran_det a
            where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDiskon["id_pembayaran"])." 
            order by pembayaran_det_ke";
    $dataPembayaranDet = $dtaccess->FetchAll($sql);

    /*
     $sql_terapi = "select a.terapi_jumlah_item,a.terapi_dosis,a.id_dep,a.terapi_harga_jual,a.terapi_total_harga,
                    b.item_nama from  klinik.klinik_perawatan_terapi a
                    left join logistik.logistik_item b on b.item_id = a.id_item
			              where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId)." and terapi_flag = 'K'"; 
		 $rs_edit = $dtaccess->Execute($sql_terapi,DB_SCHEMA_KLINIK);
     $dataPerawatan = $dtaccess->FetchAll($rs_edit);
     */
                                                                    
      // NYARI NOMER KWITANSI
   		$sql = "select pembayaran_det_kwitansi as kode from 
              klinik.klinik_pembayaran_det a 
              where pembayaran_det_id=".QuoteValue(DPE_CHAR,$dataPasien["pembayaran_det_id"]);
      $lastKode = $dtaccess->Fetch($sql);
      
      $_POST["kwitansi_nomor"] = $lastKode["kode"];

        //update folio 
        /*$sql = "update klinik.klinik_pembayaran set 
        pembayaran_no_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]).
        " where pembayaran_id = ".QuoteValue(DPE_CHAR,$dataDiskon["pembayaran_id"]).
        " and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $dtaccess->Execute($sql);   */

    
        //update folio 
        $sql = "update klinik.klinik_folio set 
        fol_lunas = 'y' ,
        fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]).
        " , id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDiskon["pembayaran_id"]).
        " , id_poli = ".QuoteValue(DPE_CHAR,$dataPasien["id_poli"]).
        " where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDiskon["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId)." and (fol_jenis like '%WA%' or fol_jenis like '%T%' or fol_jenis like '%R%' or fol_jenis like '%O%')";
        $dtaccess->Execute($sql);
        
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
 <table width="35%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
 <tr><td>
 <table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
      <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <!--<td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3">
		<?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>-->  
  </tr>
</table>

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse;font-size:12px;">
  <tr> 
    <td align="center" width="100%">&nbsp;<u>KWITANSI RAWAT JALAN</u></td>  
  </tr>
</table>
<br>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse;font-size:8px;"> 
  <tr>
    <td align="left" width="30%">No. Registrasi</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["reg_kode_trans"];?></td>  
    
  </tr>
  <tr>
    <td align="left" width="30%">No. Kwitansi</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$_POST["kwitansi_nomor"];?></td>  
    
  </tr>
  <tr>
    <td align="left" width="30%">Tanggal</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".FormatTimestamp($dataPasien["reg_tanggal"]);?></td>  
    
  </tr>
  <tr>
    <td align="left" width="30%">No. RM</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_kode"];?></td>  
    
  </tr>
  <tr>
    <td align="left" width="30%">Nama Pasien</td>  
    <td align="center" width="1%">:</td>
    <?php if($dataPasien["cust_usr_kode"]=='100' || $dataPasien["cust_usr_kode"]=='500') {?>
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["fol_keterangan"];?></td>
    <?php } else { ?>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_nama"];?></td>
    <?php } ?>    
  </tr>
  <?php if($dataPasien["cust_usr_kode"]<>'100' || $dataPasien["cust_usr_kode"]<>'500') {?>
  <tr>
    <td align="left" width="30%">Alamat</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["cust_usr_alamat"];?></td>  
  </tr>
  <?php } ?>
    <tr>
    <td align="left" width="30%">Poli</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["poli_nama"];?></td>  
  
  </tr>
    <tr>
    <td align="left" width="30%">Dokter</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["usr_name"];?></td>  
  
  </tr>
  </tr>
    <tr>
    <td align="left" width="30%">Jenis Bayar</td>  
    <td align="center" width="1%">:</td>  
    <td align="left" width="69%">&nbsp;<?php echo "&nbsp;".$dataPasien["jbayar_nama"];?></td>  
  
  </tr>

  <tr>
   <td colspan="4" align="center" width="5%">&nbsp;</td>
  </tr>

</table>

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse; font-size:8px;">
  <tr>
    <td align="center" colspan="4">----------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
<td width="1%" align="center">No.</td>
    <td width="76%" align="center">Transaksi</td>
<!--    <td width="5%" align="center">KELAS</td>    -->
    <td width="3%" align="center">Jumlah</td>
    <td width="20%"align="right">Biaya</td>
  </tr>
  <tr>
    <td align="center" colspan="4">----------------------------------------------------------------------------------------</td>
  </tr>

    <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) {?>
     <tr>
        <td align="left" valign="top"><?php echo $i+1;?></td>     
         <td align="left" valign="top"><?php echo $dataFolio[$i]["fol_nama"];?></td>
         <td align="center" valign="top"><?php echo round($dataFolio[$i]["fol_jumlah"]);?></td>
          <td align='right' valign="top"><?php echo currency_format($dataFolio[$i]["fol_hrs_bayar"])?></td>
     </tr>
     <?php 

          $totalPembayaran += $dataFolio[$i]["fol_nominal"]; 
          $totalDijamin += $dataFolio[$i]["fol_dijamin"];
          $totalDijamin1 += $dataFolio[$i]["fol_dijamin1"];
          $totalDijamin2 += $dataFolio[$i]["fol_dijamin2"];          
          $totalSubsidi += $dataFolio[$i]["fol_subsidi"];          
          //$totalIur += $dataFolio[$i]["fol_iur_bayar"];
          $totalHrsBayar += $dataFolio[$i]["fol_hrs_bayar"];
          //perhitungan rumus JKN
            $totalHarga=$totalBiaya-$dijaminHarga;
            if ($totalHarga<0) $totalHarga=0;
     
     ?>
    <?php } ?>
  <tr>
    <td align="center" colspan="4">----------------------------------------------------------------------------------------</td>
  </tr>
<!--  <tr> -->
 <!--   <td align="center">&nbsp;</td>    -->
  <!--  <td align="center" colspan="3">&nbsp;</td>    
    <td align="right"><?php echo currency_format($totalHrsBayar);?></td>
  </tr>
  <tr>
    <td align="center" colspan="8">-------------------------------------------------------------------</td>
  </tr>
  <tr>
    <td align="center" colspan="8">-------------------------------------------------------------------</td>
  </tr>-->
  <tr>
    <td align="right" colspan='4'>TOTAL BIAYA : <?php if($dataPasien["reg_jenis_pasien"]=="5"||$dataPasien["reg_jenis_pasien"]=="26"||$dataPasien["reg_jenis_pasien"]=="1") {echo currency_format($totalHarga);} else echo currency_format($totalHrsBayar);?></td>
    
  </tr>
  <tr>
    <td align="right" colspan='4'>DISKON : <?php echo currency_format($dataDiskon["pembayaran_det_diskon"]);;?></td>
    
  </tr>
  <tr>
    <td align="right" colspan='4'>TOTAL HARUS BAYAR : <?php echo currency_format($dataDiskon["pembayaran_det_total"]);;?></td>
    
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse; font-size:8px;">
  <tr>
    <td align="center" colspan="2">----------------------------------------------------------------------------------------</td>
  </tr>
  <tr>
        <td align="left" valign="top" width="25%">Terbilang : </td>
        <td align="left" colspan="3" valign="top" width="75%"><?php echo terbilang($dataDiskon["pembayaran_det_total"]);//terbilang($totalHrsBayar);?> Rupiah</td>
  </tr>
  <tr>
    <td align="center" colspan="2">----------------------------------------------------------------------------------------</td>
  </tr>
  
  <tr>
    <?php if($dataPasien["reg_jenis_pasien"]=="2") {?>
    <?php if ($dataDiskon["pembayaran_yg_dibayar"]<$dataDiskon["pembayaran_total"]){?>
       <td align="left" colspan="2">KURANG BAYAR</td>
       <td align="right"><?php echo currency_format($dataDiskon["pembayaran_total"]-$dataDiskon["pembayaran_yg_dibayar"]);?></td>
    <?php } else { ?>
       <td align="center" colspan="3">(LUNAS)</td>
    <? } } ?>
  </tr>
</table>
<table width="25%" border="0" style="border-collapse:collapse; font-size:8px;" align="right">
  <tr>
    <td align="center"><? echo $konfigurasi["dep_kota"].", ". date("d-m-Y");?></td>
  </tr>
  <tr>
    <td align="center">Kasir,</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">(<?php echo $petugas["pembayaran_who_create"];?> )</td>
  </tr>
</table>
</td></tr>
</table>
   
</div>  
</body>
</html>
