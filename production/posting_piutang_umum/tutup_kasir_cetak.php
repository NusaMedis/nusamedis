<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $thisPage = "report_setoran_cicilan.php";
     $printPage = "report_setoran_cicilan_cetak.php?";
    
   //  if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
       //$_POST["klinik"] = $_GET["klinik"]; 
       
     if($_GET["klinik"]) { $_POST["klinik"]=$_GET["klinik"]; }
      else if(!$_POST["klinik"]) { $_POST["klinik"]=$depId; }
      
   /*  if(!$auth->IsAllowed("kas_pembay_pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("kas_pembay_pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  */

 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_id"] = $konfigurasi["dep_id"];
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
          
       $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_GET['tgl_awal']){
     $_GET['tgl_awal']  = $skr;
     }
     if(!$_GET['tgl_akhir']){
     $_GET['tgl_akhir']  = $skr;
     }
     
	 
	 if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "j.id_dep = ".QuoteValue(DPE_CHAR,$_GET["klinik"]);
     //if($_GET["tgl_awal"]) $sql_where[] = "date(j.pembayaran_det_tgl) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     //if($_GET["tgl_akhir"]) $sql_where[] = "date(j.pembayaran_det_tgl) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
	   /*if($_GET["kasir"]<>"--"){
		 $sql_where[] = "j.who_when_update = ".QuoteValue(DPE_CHAR,$_GET["kasir"]);
	   } */
	   
     //if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
		   //$sql_where[] = "j.who_when_update = '".$userName."'";
	   //}
     
	   $sql_where = implode(" and ",$sql_where);
     $sql = "select a.*, j.*, cust_usr_kode, cust_usr_nama, tipe_biaya_nama, 
            usr_name, poli_nama, shift_nama, jenis_nama from klinik.klinik_pembayaran_det j 
            left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
            left join klinik.klinik_registrasi d on d.reg_id = a.id_reg and a.pembayaran_id = d.id_pembayaran
            left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
            left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
            left join global.global_auth_poli f on f.poli_id = d.id_poli
                left join global.global_shift g on g.shift_id = d.reg_shift
                left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
                left join global.global_auth_user i on i.usr_id = d.id_dokter";
     $sql .= " where is_tutup='n' and ".$sql_where; 
     //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
     $sql .= " order by j.pembayaran_det_create, j.pembayaran_det_kwitansi, a.pembayaran_id asc";
     //echo $sql;
     $dataTable = $dtaccess->FetchAll($sql);

     for($i=0,$n=count($dataTable);$i<$n;$i++){
     /*
        if($dataTable[$i]["reg_status"]=="E0" || $dataTable[$i]["reg_status"]=="E1" || $dataTable[$i]["reg_status"]=="E2" || $dataTable[$i]["reg_status"]=="M0" || $dataTable[$i]["reg_status"]=="M1" || $dataTable[$i]["reg_status"]=="A0"){
          $sql = "update klinik.klinik_registrasi set reg_status='F0' where id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
          //echo $sql; die();
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        } elseif($dataTable[$i]["reg_status"]=="G0" || $dataTable[$i]["reg_status"]=="G1" || $dataTable[$i]["reg_status"]=="G2"){
          $sql = "update klinik.klinik_registrasi set reg_status='G4' where id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
          //echo $sql; die();
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        } elseif($dataTable[$i]["reg_status"]=="V0" || $dataTable[$i]["reg_status"]=="V1" || $dataTable[$i]["reg_status"]=="V2"){
          $sql = "update klinik.klinik_registrasi set reg_status='V4' where id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
          //echo $sql; die();
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        } elseif($dataTable[$i]["reg_status"]=="I5"){
          $sql = "update klinik.klinik_registrasi set reg_status='I6' where id_pembayaran=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
          //echo $sql; die();
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        }  */
        
        $sql = "update klinik.klinik_pembayaran_det set is_tutup='y', pembayaran_det_tgl_tutup=".QuoteValue(DPE_DATE,date("Y-m-d"))." 
                where pembayaran_det_id=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_det_id"]);
        $rs = $dtaccess->Execute($sql);
     }

   	 $counter=0;
   	 $counterHeader=0;
		
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
     /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Rincian Global";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Rincian Rinci";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Kwitansi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;   */
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%"; 
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;
	   
     //if($_POST["cust_usr_jenis"]=="0" || !$_POST["cust_usr_jenis"]) {
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
	   //}
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;   
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Penerimaan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
     
   /*  $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
       
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;      */
     	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;
	
    
	   
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

	 $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_reg"]]+1;
   
   $sql = "select j.fol_keterangan from klinik.klinik_folio j 
          left join klinik.klinik_registrasi d on d.reg_id=j.id_reg 
          left join klinik.klinik_pembayaran b on b.pembayaran_id=j.id_pembayaran 
          left join global.global_customer_user c on c.cust_usr_id=j.id_cust_usr
          left join klinik.klinik_pembayaran_det a on a.id_pembayaran=j.id_pembayaran
          where j.id_reg=".QuoteValue(DPE_CHAR,$dataTable[$i]["id_reg"])." and ".$sql_where;
   $ket = $dtaccess->Fetch($sql);
   
   $keterangan=explode("-",$ket["fol_keterangan"]);
   $terima = $keterangan[0];
   $periode = $keterangan[1];
              
              $tbContent[$i][$counter][TABLE_ISI] = $m+1;
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++;
              $m++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $counter++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $counter++;
    	
              if($dataTable[$i]["cust_usr_kode"]=='500' || $dataTable[$i]["cust_usr_kode"]=='100'){
              $tbContent[$i][$counter][TABLE_ISI] = $terima;
              } else {
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
              }
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $counter++;
              
              $daytime = explode(" ", $dataTable[$i]["pembayaran_det_create"]);
              
              //$tbContent[$i][$counter][TABLE_ISI] = format_date($time[0])."&nbsp;".$time[1];
              $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["pembayaran_det_tgl"]);
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $daytime[1];
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;			  
              
              //if($_POST["cust_usr_jenis"]=="0" || !$_POST["cust_usr_jenis"]) {
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $counter++;
              //}
              
              $TipeRawat["J"] = "IRJ";
              $TipeRawat["I"] = "IRNA";
              $TipeRawat["G"] = "IGD";
              
              
              $TipeRawat["T"] = "Tunai";
              $TipeRawat["P"] = "Piutang Perorangan";
              $TipeRawat["J"] = "Jaminan";
              
              $tbContent[$i][$counter][TABLE_ISI] = $TipeRawat[$dataTable[$i]["reg_tipe_rawat"]];
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
               
              $tbContent[$i][$counter][TABLE_ISI] = $TipeRawat[$dataTable[$i]["pembayaran_det_tipe_piutang"]];
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              
              /*
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++;  */
              
              
              $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_det_total"]);
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              $totalSeluruh +=$dataTable[$i]["pembayaran_det_total"];
              	
				    
          
		          //$total += $dataTable[$i]["fol_dibayar"];
      
     }  
     
     $counter = 0;
	   
	$tbBottom[0][$counter][TABLE_ISI] = "&nbsp";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 10;
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalSeluruh);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
     
     $tableHeader = "Tutup Kasir";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran.xls');
     }
     
       if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
     
     //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_id=".QuoteValue(DPE_NUMERIC,$_GET["reg_jenis_pasien"]);
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->Fetch($rs);
     
          //Data Klinik
          $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
          
          //echo $sql;
          $sql = "select dep_nama from global.global_departemen where dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
          $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
          
        // cari tipe layanan
     $sql = "select * from global.global_tipe_biaya where tipe_biaya_id = '".$_GET["layanan"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiayaId = $dtaccess->Fetch($rs);
	 
	 //cari shift by id
			$sql = "select * from global.global_shift where shift_id = '".$_GET["shift"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataShiftId = $dtaccess->Fetch($rs);
			
			//cari nama petugas by id
			$sql = "select * from global.global_auth_user where usr_id = '".$_GET["kasir"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataKasirId = $dtaccess->Fetch($rs);


     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
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

<?php //echo $view->RenderBody("inventori_prn.css",true, "CETAK TUTUP KASIR"); ?>


<script language="javascript" type="text/javascript">

window.print();

</script>

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
     font-size:        12px;
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

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3">
		<?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>  
  </tr>
</table>
<br>
 <table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
    <tr>
      <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tgl_awal"];?> - <?php echo $_GET["tgl_akhir"];?></td>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">TUTUP KASIR</td>   
    </tr>
    <!--<tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php if($_GET["shift"]=="--"){ echo "--";} else { echo $dataShiftId["shift_nama"];} ?> </td>
    </tr>
	<tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Nama Petugas : <?php if($_GET["kasir"]=="--"){ echo "--";} else { echo $dataKasirId["usr_name"];} ?> </td>
    </tr>
	<tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Layanan : <?php if($_GET["layanan"]=="--"){ echo "--";} else { echo $tipeBiayaId["tipe_biaya_nama"];} ?> </td>
    </tr>
   <?php if($_GET["reg_jenis_pasien"]) { ?> 
    <tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Pasien : <?php echo $jenisPasien["jenis_nama"];?> </td>
    </tr>
    <?php } ?>
    <?php if($_GET["js_biaya"]) { ?> 
    <tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Biaya : <?php if($_GET["js_biaya"]=='T') echo "TUNAI"; else echo "CICILAN"; ?> </td>
    </tr>
    <?php } ?> -->
  </table>
 <br>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table>
<br>
<br>
<table width="100%" border="0">
  <tr>
    <td align="center">&nbsp;</td>
    <td align="center"><?php echo $konfigurasi["dep_kota"];?>, <?php echo date("d-m-Y");?></td>
  </tr>
  <tr>
    <td align="center">Petugas Bank</td>
    <td align="center">Petugas Kasir,</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="center">(..........................)</td>
    <td align="center">(<?php echo $userName;?> )</td>
  </tr>
</table> 
