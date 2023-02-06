<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
	 
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
     $thisPage = "report_setoran_loket.php";
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $lokasi = $ROOT."/gambar/img_cfg";
     
         if(!$_POST["klinik"]) $_POST["klinik"]=$depId;
	   else $_POST["klinik"] = $_POST["klinik"];    
     //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
	 
            if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     /*   
     if(!$auth->IsAllowed("kassa_loket_cetak_ulang_rincian",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("kassa_loket_cetak_ulang_rincian",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/ 

	   //$sql = "select * from  klinik.klinik_split where (split_flag = ".QuoteValue(DPE_CHAR,SPLIT_TINDAKAN)." or split_flag = ".QuoteValue(DPE_CHAR,SPLIT_PERAWATAN)." or split_flag = ".QuoteValue(DPE_CHAR,SPLIT_REGISTRASI).") and id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by split_flag asc ";
     //$rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     //$dataSplit = $dtaccess->FetchAll($rs);
 
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_konf_cetak_kasir"] = $konfigurasi["dep_konf_cetak_kasir"];
          
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
     
     if(!$_POST["reg_jenis_pasien"])  $_POST["reg_jenis_pasien"]="0";
     
     $perusahaan = $_POST["ush_id"];
  
     $sql_where[] = "reg_tanggal is not null"; 
    // if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "b.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "date(a.pembayaran_det_tgl) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "date(a.pembayaran_det_tgl) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     if($_POST["js_biaya"]) $sql_where[] = "b.pembayaran_jenis = ".QuoteValue(DPE_CHAR,$_POST["js_biaya"]);
     if($_POST["jbayar"]) $sql_where[] = "b.id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["jbayar"]);
     if($_POST["id_dokter"]) $sql_where[] = "d.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     if($_POST["reg_shift"] && $_POST["reg_shift"]!="--")
          $sql_where[] = "d.reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
    
     if($_POST["reg_tipe_rawat"] && $_POST["reg_tipe_rawat"]!="--")
          $sql_where[] = "d.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);
  
     if($_POST["reg_jenis_pasien"] || $_POST["reg_jenis_pasien"]!="0"){
		 $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
	   }
	   
	     if($_POST["ush_id"]){
		 $sql_where[] = "d.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["ush_id"]);
	   }
	   if($_POST["reg_tipe_layanan"]){$sql_where[] = "d.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);}
     
     if($_POST["cust_usr_kode"])  $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
     if($_POST["cust_usr_nama"])  $sql_where[] = "UPPER(c.cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%");
	   
     $sql_where = implode(" and ",$sql_where);
     if($_POST["btnLanjut"] || $_POST["btnExcel"])   
     { 
     $sql = "select a.pembayaran_det_id,a.pembayaran_det_kwitansi,a.id_reg,a.pembayaran_det_total,
             a.pembayaran_det_tgl,a.pembayaran_det_create,b.pembayaran_id,
             c.cust_usr_kode,c.cust_usr_nama,
             d.reg_tipe_layanan,d.reg_tipe_rawat,
             h.jenis_nama,j.poli_nama from klinik.klinik_pembayaran_det a 
             left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
             left join klinik.klinik_pembayaran b on b.pembayaran_id=d.id_pembayaran
             left join global.global_customer_user c on c.cust_usr_id = d.id_cust_usr
             
             left join global.global_jenis_bayar e on e.jbayar_id = b.id_jbayar
             left join global.global_perusahaan f on f.perusahaan_id = d.id_perusahaan
		         left join global.global_tipe_biaya g on g.tipe_biaya_id = d.reg_tipe_layanan
			       left join global.global_jenis_pasien h on h.jenis_id = d.reg_jenis_pasien
			       left join global.global_auth_user i on i.usr_id = d.id_dokter
             left join global.global_auth_poli j on j.poli_id = d.id_poli";
     $sql .= " where (1=1) 
              and ".$sql_where; 
     $sql .= " order by pembayaran_det_create asc";
     $dataTable = $dtaccess->FetchAll($sql);
     }
     //echo $_POST["ush_id"];
 
     //echo $sql;
     //echo $_POST["shift"];
      for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_reg"]==$dataTable[$i-1]["id_reg"] ){
          $hitung[$dataTable[$i]["id_reg"]] += 1;
          }      
      }                                                                                      

   	 $counter=0;
   	 $counterHeader=0;
		
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Rincian Global";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Rincian Rinci";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Kwitansi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	   $counterHeader++;
     
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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
	   $counterHeader++;
     	   
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
              //cari totalnya
              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran_det = ".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_det_id"]);
              $rs = $dtaccess->Execute($sql);
              $totalfolio = $dtaccess->Fetch($rs);

              $tbContent[$i][$counter][TABLE_ISI] = $m+1;
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++;
              $m++;
              if ($dataTable[$i]["reg_tipe_rawat"]=='J')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
			        }
              if ($dataTable[$i]["reg_tipe_rawat"]=='G')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
			        }                                                                                                                                                                                                                                           
    	        if ($dataTable[$i]["reg_tipe_rawat"]=='I')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/apina.png" style="cursor:pointer" alt="Cetak Rincian Global" title="Cetak Rincian Global" border="0" onClick="ProsesCetakGlobal(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_id"]."-".$dataTable[$i]["pembayaran_jenis"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-".$dataTable[$i]["pembayaran_det_id"].'\');"/>';
			        }
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
			        
              if ($dataTable[$i]["reg_tipe_rawat"]=='J')
              {                                                                                                                                                                                           
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/master_icd.png" style="cursor:pointer" alt="Cetak Rincian IRJ" title="Cetak Rincian IRJ" border="0" onClick="ProsesCetak(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_id"]."-".$dataTable[$i]["pembayaran_jenis"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-".$dataTable[$i]["pembayaran_det_id"].'\');"/>';
			        }
              if ($dataTable[$i]["reg_tipe_rawat"]=='G')
              {                                                                                                                                                                                           
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/master_icd.png" style="cursor:pointer" alt="Cetak Rincian IGD" title="Cetak Rincian IGD" border="0" onClick="ProsesCetakIGD(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_id"]."-".$dataTable[$i]["pembayaran_jenis"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-".$dataTable[$i]["pembayaran_det_id"].'\');"/>';
			        }
              if ($dataTable[$i]["reg_tipe_rawat"]=='I')
              {                                                                                                                                                                                           
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/master_icd.png" style="cursor:pointer" alt="Cetak Rincian IRNA" title="Cetak Rincian IRNA" border="0" onClick="ProsesCetakIRNA(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_id"]."-".$dataTable[$i]["pembayaran_jenis"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-".$dataTable[$i]["pembayaran_det_id"].'\');"/>';
			        }
              
              
              
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
              // window.open('cetak_kwitansi.php?id_reg='+all_id[0]+'&dis='+all_id[1]+'&disper='+all_id[2]+'&pembul='+all_id[3]+'&total='+all_id[4]+'&pembayaran_det_id='+all_id[5]+'&uangmuka_id='+all_id[6],'Cetak Ulang Kwitansi Klinik');
              if ($dataTable[$i]["reg_tipe_rawat"]=='J')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetakKwitansi(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-"."0"."-".$dataTable[$i]["pembayaran_det_total"]."-".$dataTable[$i]["pembayaran_det_id"]."-".$dataTable[$i]["pembayaran_diskon_persen"].'\');"/>';
			        }
              if ($dataTable[$i]["reg_tipe_rawat"]=='G')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetakKwitansi_igd(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-"."0"."-".$dataTable[$i]["pembayaran_det_total"]."-".$dataTable[$i]["pembayaran_det_id"]."-".$dataTable[$i]["pembayaran_diskon_persen"].'\');"/>';
			        }
              if ($dataTable[$i]["reg_tipe_rawat"]=='I')
              {
                  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetakKwitansi_irna(\''.$dataTable[$i]["id_reg"]."-".$dataTable[$i]["pembayaran_diskon"]."-".$dataTable[$i]["pembayaran_diskon_persen"]."-"."0"."-".$dataTable[$i]["pembayaran_det_total"]."-".$dataTable[$i]["pembayaran_det_id"]."-".$dataTable[$i]["pembayaran_diskon_persen"].'\');"/>';
			        }
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
              
              
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
              
              $tbContent[$i][$counter][TABLE_ISI] = $TipeRawat[$dataTable[$i]["reg_tipe_rawat"]];
              $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $counter++;
              
              $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              
              $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalfolio["total"]);
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $counter++; 
              $totalSeluruh +=$dataTable[$i]["pembayaran_det_total"];
              	
				    
          
		          //$total += $dataTable[$i]["fol_dibayar"];
      
     }  
     
     $counter = 0;
	   
	$tbBottom[0][$counter][TABLE_ISI] = "&nbsp";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 12;
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalSeluruh);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	

     
  $tableHeader = "Cetak Ulang Rincian";
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_cicilan.xls');
     }  
  
   if($_POST["btnCetak"]){
   //echo $_POST["ush_id"];
   //die();
      $_x_mode = "cetak" ;      
   }
      

      // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where jenis_id<>'2' and jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
     
     
     // cek nama perusahaan --
     $sql = "select * from global.global_jenis_pasien where jenis_id = '7'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $corporate = $dtaccess->Fetch($rs);
     
      // cari nama perusahaan --
     $sql = "select * from global.global_perusahaan where id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $NamaPerusahaan = $dtaccess->FetchAll($rs);
     
      //ambil nama dokter e
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_id asc ";
     $rs = $dtaccess->Execute($sql);
     $dataDokter = $dtaccess->FetchAll($rs);
     
      // Data Tipe Layanan //
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_nama";
     $dataTipeBiaya = $dtaccess->FetchAll($sql);       

     //cari data shift
		 $sql = "select * from global.global_shift";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);

	 //Data Klinik
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     if($konfigurasi["dep_lowest"]=='n'){
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }else if($_POST["klinik"]){
     //Data Klinik
          $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
          
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      
      if($_POST["dep_logo"]) $fotoName = $lokasi."/".$row_edit["dep_logo"];
      else $fotoName = $lokasi."/default.jpg"; 
      //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
      
        // cari jenis bayar ee //
       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId);
		   $jsBayar= $dtaccess->FetchAll($sql);       
     
?>

<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }
}

  window.onload = function() { TampilCombo(); }
  function TampilCombo(id)
    {        
         
         //alert(id);
         if(id=="7"){
              ush_id.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              ush_id.disabled = true;
         }
    }   

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function ProsesCetak(id) {
//alert(id);

	   var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
	<?php if($_POST["dep_konf_cetak_kasir"]=='n'){ ?>
  window.open('cetak_ulang_dot_pemeriksaan_ku.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <? }else{ ?>
  window.open('cetak_ulang_pemeriksaan.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <?php } ?>  
	//document.location.href='<?php echo $thisPage;?>';
}

function ProsesCetakIGD(id) {
//alert(id);

	   var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
	<?php if($_POST["dep_konf_cetak_kasir"]=='n'){ ?>
  window.open('cetak_ulang_dot_pemeriksaan_ku.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <? }else{ ?>
  window.open('cetak_ulang_pemeriksaan_igd.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <?php } ?>  
	//document.location.href='<?php echo $thisPage;?>';
}

function ProsesCetakIRNA(id) {
//alert(id);

	   var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
	<?php if($_POST["dep_konf_cetak_kasir"]=='n'){ ?>
  window.open('cetak_ulang_dot_pemeriksaan_ku.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <? }else{ ?>
  window.open('cetak_tagihan_rinci.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&id_pembayaran_det='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <?php } ?>  
	//document.location.href='<?php echo $thisPage;?>';
}


function ProsesCetakGlobal(id) {
//alert(id);

	   var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
	<?php if($_POST["dep_konf_cetak_kasir"]=='n'){ ?>
  window.open('cetak_ulang_dot_pemeriksaan_ku.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&pembayaran_det_id='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <? }else{ ?>
  window.open('cetak_tagihan.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4]+'&id_pembayaran_det='+all_id[5],'Cetak Ulang Kwitansi Klinik');
  <?php } ?>  
	//document.location.href='<?php echo $thisPage;?>';
}

function ProsesCetakKwitansi(id)
 {
	   var all_id = id.split('-');    
     window.open('cetak_kwitansi.php?id_reg='+all_id[0]+'&dis='+all_id[1]+'&disper='+all_id[2]+'&pembul='+all_id[3]+'&total='+all_id[4]+'&pembayaran_det_id='+all_id[5]+'&uangmuka_id='+all_id[6],'Cetak Ulang Kwitansi Klinik');
 
}

function ProsesCetakKwitansi_igd(id)
 {
	   var all_id = id.split('-');    
     window.open('cetak_kwitansi_igd.php?id_reg='+all_id[0]+'&dis='+all_id[1]+'&disper='+all_id[2]+'&pembul='+all_id[3]+'&total='+all_id[4]+'&pembayaran_det_id='+all_id[5]+'&uangmuka_id='+all_id[6],'Cetak Ulang Kwitansi Klinik');
 
}

function ProsesCetakKwitansi_irna(id)
 {
	   var all_id = id.split('-');    
     window.open('cetak_kwitansi_irna.php?id_reg='+all_id[0]+'&dis='+all_id[1]+'&disper='+all_id[2]+'&pembul='+all_id[3]+'&total='+all_id[4]+'&pembayaran_det_id='+all_id[5]+'&uangmuka_id='+all_id[6],'Cetak Ulang Kwitansi Klinik');
 
}


function ProsesCetakKategori(id) {
//alert(id);

	   var all_id = id.split('-');
     var link  = 'edit_data_pasiene.php?usr_id='+all_id[0]+'&id_reg='+all_id[1]+'&kode='+all_id[2];
     
	<?php if($_POST["dep_konf_cetak_kasir"]=='n'){ ?>
  window.open('cetak_ulang_dot_pemeriksaan_ku_kategori_2.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4],'Cetak Ulang Kwitansi Klinik');
  <? }else{ ?>
  window.open('cetak_ulang_pemeriksaan.php?id_reg='+all_id[0]+'&id_pembayaran='+all_id[1]+'&pembayaran_jenis='+all_id[2]+'&diskon='+all_id[3]+'&diskon_persen='+all_id[4],'Cetak Ulang Kwitansi Klinik');
  <?php } ?>  
	//document.location.href='<?php echo $thisPage;?>';
}

<?php if($_x_mode=="cetak"){ ?>	
  //BukaWindow('report_setoran_loket_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&reg_jenis_pasien=<?php echo $_POST["reg_jenis_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>','Pemakaian Kasir');
  //onclick="window.open(this.href); return false";
  window.open('report_setoran_cicilan_cetak.php?perusahaan=<?php echo $perusahaan;?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&reg_jenis_pasien=<?php echo $_POST["reg_jenis_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&js_biaya=<?php echo $_POST["js_biaya"];?>&jbayar=<?php echo $_POST["jbayar"]?>', '_blank');
  //document.location.href='report_setoran_loket_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&reg_jenis_pasien=<?php echo $_POST["reg_jenis_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>';
<?php } ?>

</script>


<!DOCTYPE html>
<html lang="en">  
  <?php require_once($LAY."header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
		
        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
		
       <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Cetak Ulang Rincian</h3>
              </div>
            </div>
            <div class="clearfix"></div>
			
			<!-- Row -->
            <div class="row">
                <div class="x_panel">
                  <div class="x_title">
                    <h2></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content" >	 
        					<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
            					<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
                					<div class="col-md-4 col-sm-6 col-xs-12">
                                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Kwitansi(DD-MM-YYYY)</label>
                                        <div class='input-group date' id='datepicker'>
                							<input  id="tgl_awal" name="tgl_awal" type='text' class="form-control" value="<?php echo $_POST["tgl_awal"] ?>"  />
                							<span class="input-group-addon">
                								<span class="fa fa-calendar">
                								</span>
                							</span>
                						</div>	           			 
                			
                                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal Kwitansi(DD-MM-YYYY)</label>
                						<div class='input-group date' id='datepicker2'>
                							<input  id="tgl_akhir" name="tgl_akhir"  type='text' class="form-control" value="<?php echo $_POST["tgl_akhir"] ?>"  />
                							<span class="input-group-addon">
                								<span class="fa fa-calendar">
                								</span>
                							</span>
                						</div>	     			 
                				    </div>
            
                				    <div class="col-md-4 col-sm-6 col-xs-12">
                                 <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
                      						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
                      						
                				    </div>
                            
                					 <div class="col-md-4 col-sm-6 col-xs-12">
                                 <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                    						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
                    						 
                				    </div>
				    
              				    <div class="col-md-4 col-sm-6 col-xs-12">
                                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat </label>
              						
              							<select name="reg_tipe_rawat" class="select2_single form-control" id="reg_tipe_rawat" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                      					<option value="--" <?php if('--'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Semua Tipe Rawat";?></option>
                      					<option value="J" <?php if('J'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Jalan";?></option>
                      					<option value="I" <?php if('I'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Inap";?></option>
                      					<option value="G" <?php if('G'==$_POST["reg_tipe_rawat"]) echo "selected"; ?> ><?php echo "Rawat Darurat";?></option>
              			    		</select>
              			   		 
              				    </div>
                          
                          <div class="col-md-4 col-sm-6 col-xs-12">
                                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Rawat </label>
              						
              							<select name="reg_jenis_pasien" class="select2_single form-control" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                        					<!--<option value="" >[ Pilih Cara Bayar ]</option>-->
                        					<?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                        					<option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
              						  <?php } ?>
              			    		</select>
              			   		
              				    </div>
				    
              					 <div class="col-md-4 col-sm-6 col-xs-12">
                                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
              						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success">
              				    </div>			 
					
					     </table>

					</form>
                  </div>
                </div>      
            </div>
            <!-- END ROW  -->
            
			<!-- Row -->
            <div class="row">
                <div class="x_panel">
                  <div class="x_title">
                    <h2></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content" >	 
					<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>				  
                  </div>
                </div>      
            </div>
            <!-- END ROW  -->
          
		  </div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
		<?php require_once($LAY."footer.php"); ?>
      </div>
    </div>
<?php require_once($LAY."js.php"); ?>
  </body>
</html>