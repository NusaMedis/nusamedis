<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $thisPage = "report_pasien.php";
     $skr = date("d-m-Y");
     //$_POST["klinik"]=$depId;

     if (!$_GET["klinik"]) $_POST["klinik"]=$depId;
     else $_POST["klinik"]= $_GET["klinik"];

     //pemanggilan tanggal hari ini jika gk ada get tgl awal n akhir 
     if(!$_GET["tgl_awal"]) $_GET["tgl_awal"]  = $skr;
     if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"]  = $skr;


     //untuk mencari tanggal
     if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]."%");
     if($_GET["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     if($_GET["id_kecamatan"]) $sql_where[] = " b.id_kecamatan = ".QuoteValue(DPE_CHAR,$_GET["id_kecamatan"]);     
     if($_GET["reg_status_pasien"]) $sql_where[] = " reg_status_pasien = ".QuoteValue(DPE_CHAR,$_GET["reg_status_pasien"]);     
     if($_GET["shift"] && $_GET["shift"]!="--") $sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_GET["shift"]);     

     if($_GET["jenis"] && $_GET["jenis"]!="--") $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["jenis"]);
     if($_GET["id_poli"]){ $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);  }

          
     //if($_POST["id_poli"] && $_POST["id_poli"] <> '--') $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

      $sqli = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
			   a.reg_batal,d.usr_name,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur,
			   g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran,
               a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd from klinik.klinik_registrasi a 
			   left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join global.global_auth_poli c on c.poli_id = a.id_poli
               left join global.global_auth_user d on a.id_dokter = d.usr_id
               left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
               left join global.global_departemen f on a.id_dep = f.dep_id
			   left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
			   left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
			   left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
			   left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
         left join klinik.klinik_perawatan k on k.id_reg=a.reg_id";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and (a.reg_status='E0' or a.reg_status='E1' or a.reg_status='F0' or a.reg_status='E2')  
              and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_batal is null ";
     $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sqli,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
      
    //echo $sql;
     
	   // --- ngitung jml data e ---
	  $sql = "select count(reg_id) as total
            from   klinik.klinik_registrasi a 
            join   global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
            where (a.reg_status='E0' or a.reg_status='E1' or a.reg_status='F0' or a.reg_status='E2') and cust_usr_kode<>'500' and cust_usr_kode<>'100'";
            $sql .= " and ".implode(" and ",$sql_where);
            $sql.= " and a.reg_batal is null ";
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;

     $tableHeader = "&nbsp;Report Kunjungan Pasien IRJ Harian";
  
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
		if($dataTable[$i]["id_poli"]!=$dataTable[$i-1]["id_poli"]){
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;    
          
          if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          if($dataTable[$i]["reg_jenis_pasien"]=='5'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jkn_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='18'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jamkesda_kota_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='7'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["perusahaan_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }else{
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
			  $counter++;
		  }
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
		  
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
        }  
     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
  $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])   ; 
  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataPoli = $dtaccess->FetchAll($rs_edit);
  
   // ambil jenis pasien
   $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
   $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
   $jenisPasien = $dtaccess->FetchAll($rs); 
     
   // ambil jenis pasien
   $sql = "select * from global.global_shift where shift_id = '".$_GET["shift"]."'";
   $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
   $dataShift = $dtaccess->Fetch($rs); 
        
     //Data Klinik
          $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
    //echo $sql;
          $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
                                                      
      //Nama Sekolah
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];
      $lokasi = $ROOT."/gambar/img_cfg";   
      
        if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }    

?>

<script language="JavaScript">

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
     <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN KUNJUNGAN PASIEN IRJ</td>
    </tr>
    <? if ($_GET["shift"]!="--") { ?>
    <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php echo $dataShift["shift_jam_awal"]."-".$dataShift["shift_jam_akhir"];?></td>
    </tr>
    <? } ?>
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

<?php echo $view->RenderBodyEnd(); ?>