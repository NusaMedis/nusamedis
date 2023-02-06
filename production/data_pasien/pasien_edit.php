<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
	   require_once($ROOT."lib/tampilan.php");
     require_once($ROOT."lib/expAJAX.php");		
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
     $plx = new expAJAX("CheckKode");
     
     if(!$auth->IsAllowed("fo_registrasi",PRIV_CREATE) && !$auth->IsAllowed("fo_kunjungan_pasien",PRIV_READ)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("fo_registrasi",PRIV_CREATE)===1 || $auth->IsAllowed("fo_kunjungan_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $sql = "select * from global.global_konf_reg where konf_reg_id = '1'";
    	$rs = $dtaccess->Execute($sql);
    	$konfReg = $dtaccess->Fetch($rs);
    	//$dtaccess->Clear($rs);
    	//echo $sql;
    	$_POST["konf_reg_id"] = $konfReg["konf_reg_id"];
      $_POST["konf_reg_sebab_sakit"] = $konfReg["konf_reg_sebab_sakit"];
      $_POST["id_dep"] = $konfReg["id_dep"];
      $_POST["konf_reg_istri"] = $konfReg["konf_reg_istri"];
      $_POST["konf_reg_ayah"] = $konfReg["konf_reg_ayah"];
      $_POST["konf_reg_ibu"] = $konfReg["konf_reg_ibu"];
      $_POST["konf_reg_sebab_sakit_aktif"] = $konfReg["konf_reg_sebab_sakit_aktif"];
      $_POST["konf_reg_layanan"] = $konfReg["konf_reg_layanan"];
      $_POST["konf_reg_shift"] = $konfReg["konf_reg_shift"];
      $_POST["konf_reg_cara_kunjungan"] = $konfReg["konf_reg_cara_kunjungan"];
      $_POST["konf_reg_cara_kunjungan_aktif"] = $konfReg["konf_reg_cara_kunjungan_aktif"];
     
     if($_GET["id_klinik_waktu_tunggu"]) $_POST["id_klinik_waktu_tunggu"]=$_GET["id_klinik_waktu_tunggu"];
     if($_POST["id_klinik_waktu_tunggu"]) $_POST["id_klinik_waktu_tunggu"]=$_POST["id_klinik_waktu_tunggu"];
     if($_GET["reguler"]) $_POST["reguler"]=$_GET["reguler"];
     if($_POST["reguler"]) $_POST["reguler"]=$_POST["reguler"];
     if($_GET["eksekutif"]) $_POST["eksekutif"]=$_GET["eksekutif"];
     if($_POST["eksekutif"]) $_POST["eksekutif"]=$_POST["eksekutif"];
     if($_GET["id_loket"]) $_POST["id_loket"]=$_GET["id_loket"];
     if($_POST["id_loket"]) $_POST["id_loket"]=$_POST["id_loket"];
     if($_GET["lama"]) $_POST["lama"]=$_GET["lama"];
     if($_GET["jkn"]) $_POST["jkn"]=$_GET["jkn"];
	
     if($_POST["id_klinik_waktu_tunggu"]){
       if($_POST["reguler"]){          
     	  $backPage="pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&reguler=1&id_loket=".$_POST["id_loket"]."&";
       } elseif($_POST["eksekutif"]){
        $backPage="pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&eksekutif=1&id_loket=".$_POST["id_loket"]."&";
       }
      } else $backPage="pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&";
      
      $lokTakeFoto = $ROOT."gambar/foto_pasien";
       if(!$_POST["cust_usr_asal_negara"]) $_POST["cust_usr_asal_negara"] ='1';
       
       if($_POST["cust_usr_asal_negara"]=='1'){ $_POST["cust_usr_negara"]='1';
       }else{ $_POST["cust_usr_negara"] ='2';
       }
       if(!$_POST["cust_usr_negara"]) $_POST["cust_usr_negara"] ='1';
       
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
        
	if($_POST["usr_cust_id"])  $usrCustId = & $_POST["usr_cust_id"];
 
     function CheckKode($kode,$custUsrId=null){
       	global $dtaccess;
            
            $sql = "SELECT a.cust_usr_id FROM   global.global_customer_user a 
                    WHERE upper(a.cust_usr_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kode));
                      
            if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_CHAR,$custUsrId);
            
            $rs = $dtaccess->Execute($sql);
            $dataAdaKode = $dtaccess->Fetch($rs);           
  			return $dataAdaKode["cust_usr_id"];
       }
  
     if($_GET["id"] || $_GET["dep_id"]) 
     {

          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $usrCustId = $enc->Decode($_GET["id"]);
          }        
                    
          $sql = "select a.* from  global.global_customer_user a where cust_usr_id = ".QuoteValue(DPE_CHAR,$usrCustId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);                 
          
          $_POST["cust_usr_kode"] = $row_edit["cust_usr_kode"];                                                             
          $_POST["cust_usr_nama"] = $row_edit["cust_usr_nama"];
          $_POST["cust_usr_alamat"] = $row_edit["cust_usr_alamat"];
          $_POST["usr_cust_kode"] = $row_edit["usr_cust_kode"];	   
          $_POST["cust_usr_id"] = $row_edit["cust_usr_id"];
		      $_POST["cust_usr_tempat_lahir"] = $row_edit["cust_usr_tempat_lahir"];
		      $_POST["cust_usr_tanggal_lahir"] = format_date($row_edit["cust_usr_tanggal_lahir"]);
          $tglLahir=explode("-",format_date($row_edit["cust_usr_tanggal_lahir"]));
          $_POST["tgl"]=$tglLahir[0];
          $_POST["bln"]=$tglLahir[1];
          $_POST["thn"]=$tglLahir[2];    
		      $_POST["cust_tgllahir_pasangan"] = format_date($row_edit["cust_tgllahir_pasangan"]);
          $tglLahir1=explode("-",format_date($row_edit["cust_tgllahir_pasangan"]));
          $_POST["tgl1"]=$tglLahir1[0];
          $_POST["bln1"]=$tglLahir1[1];
          $_POST["thn1"]=$tglLahir1[2];    

		      $_POST["cust_usr_jenis_kelamin"] = $row_edit["cust_usr_jenis_kelamin"]; 
		      $_POST["cust_usr_no_identitas"] = $row_edit["cust_usr_no_identitas"];
		      $_POST["cust_usr_nik"] = $row_edit["cust_usr_nik"];
		      $_POST["cust_usr_agama"] = $row_edit["cust_usr_agama"];  
		      $_POST["cust_user_ayah"] = $row_edit["cust_user_ayah"];
		      $_POST["cust_usr_ibu"] = $row_edit["cust_usr_ibu"];
		      $_POST["cust_usr_no_hp"] = $row_edit["cust_usr_no_hp"]; 
        	$_POST["id_kec"]=$row_edit["id_kecamatan"];
    	    $_POST["id_kel"]=$row_edit["id_kelurahan"];
    	    $_POST["id_pendidikan"]=$row_edit["id_pendidikan"];
        	$_POST["cust_usr_nama_kk"]=$row_edit["cust_usr_nama_kk"];
        	$_POST["cust_usr_dusun"]=$row_edit["cust_usr_dusun"];
        	$_POST["id_prop"]=$row_edit["id_prop"];
        	$_POST["id_kota"]=$row_edit["id_kota"];
          $_POST["cust_usr_foto"] = $row_edit["cust_usr_foto"];
          $_POST["cust_usr_no_jaminan"] = $row_edit["cust_usr_no_jaminan"];
          $_POST["cust_usr_istri"] = $row_edit["cust_usr_istri"];
          $_POST["cust_usr_ktp"] = $row_edit["cust_usr_ktp"];
          $_POST["cust_usr_ktp_pasangan"] = $row_edit["cust_usr_ktp_pasangan"];          
          $_POST["cust_usr_asal_negara"] = $row_edit["cust_usr_asal_negara"];
          $_POST["cust_usr_negara"] = $row_edit["cust_usr_negara"];
          /*$umurPasien=explode("~",$row_edit["cust_usr_umur"]);
    	    $_POST["tahun"]=$umurPasien[0];
    	    $_POST["bulan"]=$umurPasien[1];
    	    $_POST["hari"]=$umurPasien[2];*/
          $_POST["id_pekerjaan"] = $row_edit["id_pekerjaan"];
          $_POST["cust_usr_penanggung_jawab_pendidikan"] =  $row_edit["cust_usr_penanggung_jawab_pendidikan"];
          $_POST["cust_usr_penanggung_jawab_pekerjaan"] =  $row_edit["cust_usr_penanggung_jawab_pekerjaan"];
          $_POST["cust_usr_penanggung_jawab_status"] =  $row_edit["cust_usr_penanggung_jawab_status"];                               
          $_POST["cust_usr_penanggung_jawab"] =  $row_edit["cust_usr_penanggung_jawab"];
          $_POST["id_status_perkawinan"] =  $row_edit["id_status_perkawinan"];
        	$_POST["cust_usr_dik_pasangan"] = $row_edit["cust_usr_dik_pasangan"];
        	$_POST["cust_usr_pekerjaan_pasangan"] = $row_edit["cust_usr_pekerjaan_pasangan"];
        	$_POST["cust_usr_nik_pasangan"] = $row_edit["cust_usr_nik_pasangan"];
        	$_POST["cust_usr_agama_pasangan"] = $row_edit["cust_usr_agama_pasangan"];
        	$_POST["cust_usr_telp_pasangan"] = $row_edit["cust_usr_telp_pasangan"];
        	$_POST["cust_usr_alamat_pasangan"] = $row_edit["cust_usr_alamat_pasangan"];
        	$_POST["cust_usr_alergi"] = $row_edit["cust_usr_alergi"];                        

    	    $hitungUmur = HitungUmur(date_db($_POST["cust_usr_tanggal_lahir"]));
          $umurtahun = (strtotime(date("Y-m-d")) - strtotime(date_db($_POST["cust_usr_tanggal_lahir"])))/86400/365;
          $umurbulan = ($umurtahun - floor($umurtahun)) * 12;
          $umurhari = ($umurbulan - floor($umurbulan)) * 31; 
          //echo $hitungUmur."-".floor($umurtahun)."-".floor($umurbulan)."-".floor($umurhari);
          $_POST["tahun"]=floor($umurtahun);
        	$_POST["bulan"]=floor($umurbulan);
        	$_POST["hari"]=floor($umurhari);

          if($_POST["cust_tgllahir_pasangan"]==null){
          $_POST["tahun1"]=0;
        	$_POST["bulan1"]=0;
        	$_POST["hari1"]=0;
          }else{
    	    $hitungUmur1 = HitungUmur(date_db($_POST["cust_tgllahir_pasangan"]));
          $umurtahun1 = (strtotime(date("Y-m-d")) - strtotime(date_db($_POST["cust_tgllahir_pasangan"])))/86400/365;
          $umurbulan1 = ($umurtahun1 - floor($umurtahun1)) * 12;
          $umurhari1 = ($umurbulan1 - floor($umurbulan1)) * 31; 
          //echo $hitungUmur."-".floor($umurtahun)."-".floor($umurbulan)."-".floor($umurhari);
          $_POST["tahun1"]=floor($umurtahun1);
        	$_POST["bulan1"]=floor($umurbulan1);
        	$_POST["hari1"]=floor($umurhari1);
         }
    	    //cari data kota   
       /*   if ($_POST["id_kota"])
          {           
           $sql = "select * from global.global_kota where kota_id='".$_POST["id_kota"]."'";
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           $dataKotaNya = $dtaccess->Fetch($rs);     
           $_POST["kota_nama"]=$dataKotaNya["kota_nama"];
          } */
 	
      }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) 
    { 
    
    
                                
          if($_POST["btnUpdate"]){
               $usrCustId = & $_POST["usr_cust_id"];
               $_x_mode = "Edit";
          } 
          
              if($_POST["kel"]==$_POST["id_kelurahan"]){
                $sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["id_kel"]."'";
                //echo "post".$sql; die();
                $lokasi = $dtaccess->Fetch($sql);
              }else{
                $sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["kel"]."'";
                //echo "ajax".$sql; die();
                $lokasi = $dtaccess->Fetch($sql);
              }
          
              $dbTable = " global.global_customer_user";
               
               $dbField[0] = "cust_usr_id";   // PK
               $dbField[1] = "cust_usr_nama";  
               $dbField[2] = "cust_usr_kode";
               $dbField[3] = "cust_usr_alamat";
               $dbField[4] = "cust_usr_tempat_lahir";
               $dbField[5] = "cust_usr_tanggal_lahir";
               $dbField[6] = "cust_usr_jenis_kelamin";
               $dbField[7] = "cust_usr_no_identitas";
               $dbField[8] = "cust_usr_nik";
               $dbField[9] = "cust_usr_agama";
               $dbField[10] = "cust_user_ayah";
               $dbField[11] = "cust_usr_ibu";
               $dbField[12] = "cust_usr_no_hp";
               $dbField[13] = "id_kecamatan";
               $dbField[14] = "id_kelurahan";
               $dbField[15] = "id_pendidikan";
               $dbField[16] = "cust_usr_nama_kk";
               $dbField[17] = "cust_usr_dusun";
               $dbField[18] = "cust_usr_umur";
               $dbField[19] = "id_prop";
               $dbField[20] = "id_kota";
               $dbField[21] = "id_pekerjaan";
               $dbField[22] = "id_status_perkawinan";
               $dbField[23] = "cust_usr_penanggung_jawab";
               $dbField[24] = "cust_usr_penanggung_jawab_status";
               $dbField[25] = "cust_usr_no_jaminan";  
               $dbField[26] = "cust_usr_penanggung_jawab_pendidikan";   
               $dbField[27] = "cust_usr_penanggung_jawab_pekerjaan";
               $dbField[28] = "cust_usr_istri";
               $dbField[29] = "cust_usr_negara";
               $dbField[30] = "cust_usr_asal_negara";
              $dbField[31] = "cust_usr_ktp";
              $dbField[32] = "cust_usr_ktp_pasangan";                  
              $dbField[33] = "cust_usr_dik_pasangan";                  
              $dbField[34] = "cust_usr_pekerjaan_pasangan";                  
              $dbField[35] = "cust_usr_nik_pasangan";                  
              $dbField[36] = "cust_usr_agama_pasangan";                  
              $dbField[37] = "cust_usr_telp_pasangan";                  
              $dbField[38] = "cust_usr_alergi";
              $dbField[39] = "cust_usr_alamat_pasangan";          
              $dbField[40] = "cust_tgllahir_pasangan";          
              $dbField[41] = "cust_usr_suami_umur";          
                              
               if(!$usrCustId) $usrCustId = $dtaccess->GetTransId(); 
               $_POST["cust_usr_tanggal_lahir"]=$_POST["tgl"]."-".$_POST["bln"]."-".$_POST["thn"];  
               $_POST["cust_tgllahir_pasangan"]=$_POST["tgl1"]."-".$_POST["bln1"]."-".$_POST["thn1"];
               
               $dbValue[0] = QuoteValue(DPE_CHAR,$usrCustId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
               $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_identitas"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nik"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["cust_usr_agama"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["cust_user_ayah"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["cust_usr_ibu"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_hp"]);
               $dbValue[13] = QuoteValue(DPE_CHAR,$lokasi["lokasi_kecamatan"]);
               $dbValue[14] = QuoteValue(DPE_CHAR,$lokasi["lokasi_kelurahan"]);
               $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_pendidikan"]);
               $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama_kk"]);
               $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["cust_usr_dusun"]);
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["tahun"]."~".$_POST["bulan"]."~".$_POST["hari"]);   
               $dbValue[19] = QuoteValue(DPE_CHAR,$lokasi["lokasi_propinsi"]);
               $dbValue[20] = QuoteValue(DPE_CHAR,$lokasi["lokasi_kabupatenkota"]);
               $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["id_pekerjaan"]);
               $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["id_status_perkawinan"]);
               $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab"]);
               $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_status"]);
               $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_jaminan"]);
               $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_pendidikan"]);
               $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_pekerjaan"]);
               $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["cust_usr_istri"]);
               $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cust_usr_negara"]);
               $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["cust_usr_asal_negara"]);
               $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["cust_usr_ktp"]);
               $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["cust_usr_ktp_pasangan"]);                  
               $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["cust_usr_dik_pasangan"]);                  
               $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["cust_usr_pekerjaan_pasangan"]);                  
               $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nik_pasangan"]);                  
               $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["cust_usr_agama_pasangan"]);                  
               $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["cust_usr_telp_pasangan"]);                  
               $dbValue[38] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alergi"]);
               $dbValue[39] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat_pasangan"]);          
               $dbValue[40] = QuoteValue(DPE_DATE,date_db($_POST["cust_tgllahir_pasangan"]));          
               $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["tahun1"]."~".$_POST["bulan1"]."~".$_POST["hari1"]);          

               //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
                
        
        if($_POST["reg_kelengkapan_dokumen"]=='1'){
        $sql =" update klinik.klinik_registrasi set reg_kelengkapan_dokumen = 'y' where reg_id = ".QuoteValue(DPE_CHAR,$_POST['id_reg']);
        $dtaccess->Execute($sql);
        
        }else{
         $sql =" update klinik.klinik_registrasi set reg_kelengkapan_dokumen = 'n' where reg_id = ".QuoteValue(DPE_CHAR,$_POST['id_reg']);
        $dtaccess->Execute($sql);
        
        }
        
        //echo $sql; 
      if($_POST["id_klinik_waktu_tunggu"]){                  
        if($_POST["reguler"]){
          echo "<script>document.location.href='pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&reguler=1&id_loket=".$_POST["id_loket"]."';</script>";
        } elseif($_POST["eksekutif"]){
          echo "<script>document.location.href='pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&eksekutif=1&id_loket=".$_POST["id_loket"]."';</script>";
        }
      } else {
        echo "<script>document.location.href='pasien_view.php';</script>";
      }
      exit(); 
             
     }

     
     if ($_GET["del"]) {
          $pasienId = $enc->Decode($_GET["id"]);
          
           $sql = "delete from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR,$pasienId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          
          echo "<script>document.location.href='pasien_view.php?klinik=".$_GET["id_dep"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."';</script>";
          exit();    
     }
 
     if ($_GET["hapusmedrec"]) {
          $pasienId = $enc->Decode($_GET["id"]);
 
           $sql = "delete from klinik.klinik_registrasi where id_cust_usr = ".QuoteValue(DPE_CHAR,$pasienId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  
    
           $dbTable = "global.global_customer_user";
           $dbField[0] = "cust_usr_id";
           $dbField[1] = "cust_usr_kosong";
           $dbField[2] = "cust_usr_nama";
           $dbField[3] = "cust_usr_alamat";
           $dbField[4] = "cust_usr_tempat_lahir";
           $dbField[5] = "cust_usr_tanggal_lahir";
           $dbField[6] = "cust_usr_jenis_kelamin";
           $dbField[7] = "cust_usr_jenis";
           $dbField[8] = "cust_usr_who_update";
           $dbField[9] = "cust_usr_when_update";
           $dbField[10] = "cust_usr_umur";
           $dbField[11] = "id_kecamatan";
           $dbField[12] = "id_kelurahan";
           $dbField[13] = "id_prop";
           $dbField[14] = "id_kota";
		   //print_r($dbField);
		   //die();

           $dbValue[0] = QuoteValue(DPE_CHAR,$pasienId);
           $dbValue[1] = QuoteValue(DPE_CHAR,'y');
           $dbValue[2] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[3] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[4] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[5] = QuoteValue(DPE_DATE,NULL);
           $dbValue[6] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[7] = QuoteValue(DPE_NUMERIC,0);
           $dbValue[8] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[9] = QuoteValue(DPE_DATE,NULL);
           $dbValue[10] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[11] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[12] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[13] = QuoteValue(DPE_CHAR,NULL);
           $dbValue[14] = QuoteValue(DPE_CHAR,NULL);
          
           $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
           $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
           
           $dtmodel->Update() or die("insert  error");
           
           unset($dbField);
           unset($dtmodel);
           unset($dbValue);
           unset($dbKey);
 
          if($_POST["id_klinik_waktu_tunggu"]){                  
            if($_POST["reguler"]){
              echo "<script>document.location.href='pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&reguler=1&id_loket=".$_POST["id_loket"]."&klinik=".$_GET["id_dep"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."';</script>";
            } elseif($_POST["eksekutif"]){
              echo "<script>document.location.href='pasien_view.php?jkn=".$_POST["jkn"]."&lama=".$_POST["lama"]."&id_klinik_waktu_tunggu=".$_POST["id_klinik_waktu_tunggu"]."&eksekutif=1&id_loket=".$_POST["id_loket"]."&klinik=".$_GET["id_dep"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."';</script>";
            }
          } else {
            echo "<script>document.location.href='pasien_view.php?klinik=".$_GET["id_dep"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."';</script>";
          }
          exit();    
     }
     
     
     

     //cari data pendidikan
     $sql = "select * from global.global_pendidikan order by pendidikan_urut";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPendidikan = $dtaccess->FetchAll($rs);
     
     //cari data prop
     $sql = "select * from global.global_propinsi order by prop_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataProp = $dtaccess->FetchAll($rs);   

     	//cari data kecamatan
     $sql = "select * from global.global_kecamatan order by kec_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKec = $dtaccess->FetchAll($rs);
     
     //cari data kelurahan
		 $sql = "select * from global.global_kelurahan where id_kec = '".$_POST["id_kecamatan"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKel = $dtaccess->FetchAll($rs);
     
      //cari data kota
     $sql = "select * from global.global_kota order by kota_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs); 
     
     //cari data pekerjaan
     $sql = "select * from global.global_pekerjaan order by pekerjaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPekerjaan = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_status_pj order by status_pj_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatusPJ = $dtaccess->FetchAll($rs);
     
     //cari data agama
     $sql = "select * from global.global_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataAgama = $dtaccess->FetchAll($rs);     
     
     //cari status perkawinan
     $sql = "select * from global.global_status_perkawinan order by status_perkawinan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatus = $dtaccess->FetchAll($rs);
     
     //combo negara kebangsaan
     $sql = "select * from global.global_negara order by negara_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataNegara = $dtaccess->FetchAll($rs);      
?>
<?php echo $view->InitThickBox(); ?>  
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<script language="javascript" type="text/javascript">
<? $plx->Run(); ?>

function CheckDataSave(frm) {
     
   if(CheckKode(document.getElementById('cust_usr_kode').value,document.getElementById('usr_cust_id').value,'type=r')){
			
      alert('No RM tersebut sudah dipakai');
			document.getElementById('cust_usr_kode').focus();
			return false;
	  	}         
	
     //        membuat variabel numbers bernilai angka 0 s/d 9
        var numbers=/^[0-9]+$/;
     //        validasi nip tidak boleh kosong (required)
        if (document.getElementById('cust_usr_kode').value==null || document.getElementById('cust_usr_kode').value=="")
          {
          alert("No RM tidak boleh kosong !");
          document.getElementById('cust_usr_kode').focus();
          return false;
          };
          
//        validasi nip harus berupa angka
//        dengan membandingkan dengan variabel number yang dibuat pada baris 21
        if (!document.getElementById('cust_usr_kode').value.match(numbers))
          {
          alert("No RM harus angka !");
          document.getElementById('cust_usr_kode').focus();
          return false;
          };
     
     if (document.getElementById('cust_usr_kode').value.match(numbers))
      {
        var panjang = <?php echo $_POST["dep_panjang_kode_pasien"];?>;
        var nol = <?php echo $_POST["dep_jml_nol_depan"];?>;
        
        jml = panjang-nol;
        
        if(document.getElementById('cust_usr_kode').value.length>jml){
          alert("Panjang No RM tidak boleh lebih dari "+jml);
          document.getElementById('cust_usr_kode').focus();
          return false;
        }
      }
      
     if(!document.getElementById('cust_usr_nama').value) {
         alert('Nama Pasien harap diisi');
         document.getElementById('cust_usr_nama').focus();
         return false;
     }          

     if(!document.getElementById('cust_usr_tempat_lahir').value) {
         alert('Tempat Lahir Pasien harap diisi');
         document.getElementById('cust_usr_tempat_lahir').focus();
         return false;
     }   
     

     /*if(!document.getElementById('cust_usr_tanggal_lahir').value) {
         alert('Tanggal Lahir Pasien harap diisi');
         document.getElementById('cust_usr_tanggal_lahir').focus();
         return false;
     }*/
     
     if(!document.getElementById('tgl').value) {
         alert('Tanggal Lahir Pasien harap diisi');
         document.getElementById('tgl').focus();
         return false;
     }
     
     if(!document.getElementById('bln').value) {
         alert('Bulan Lahir Pasien harap diisi');
         document.getElementById('bln').focus();
         return false;
     }
     
     if(!document.getElementById('thn').value) {
         alert('Tahun Lahir Pasien harap diisi');
         document.getElementById('thn').focus();
         return false;
     }   
     
     if(isNaN(document.getElementById('tahun').value)) {
         alert('Format Tanggal Lahir Salah');
         document.getElementById('cust_usr_tanggal_lahir').focus();
         return false;
     }              

     if(!document.getElementById('tahun').value) {
         alert('Umur Pasien harap diisi');
         document.getElementById('tahun').focus();
         return false;
     }                     


     if(document.getElementById('cust_usr_jenis_kelamin').value=='--') {
         alert('Jenis Kelamin Pasien harap diisi');
         document.getElementById('cust_usr_jenis_kelamin').focus();
         return false;
     }      
     
    if(!document.getElementById('vcust_usr_alamat').value) {
         alert('Alamat Pasien harap diisi');
         document.getElementById('vcust_usr_alamat').focus();
         return false;
     }   
     
     if(document.getElementById('cust_usr_no_hp').value=='') {
         alert('Nomor Telp/HP harap diisi');
         document.getElementById('cust_usr_no_hp').focus();
         return false;
     }
     
     if(!document.getElementById('cust_usr_ibu').value || document.getElementById('cust_usr_ibu').value=='') {
         alert('Nama Ibu Kandung harap diisi');
         document.getElementById('cust_usr_ibu').focus();
         return false;
     }

  	return true;      
}
                                                          
</script> 

<script language="Javascript">
function Umur(umur) {
      /* tgllahir = document.getElementById("cust_usr_tanggal_lahir").value;
      tanggal = tgllahir.split("-");
      t = tanggal[0];
      bln = (tanggal[1] - 1);
      thn = tanggal[2];*/
      tgl1 = document.getElementById("tgl").value;
      bln1 = document.getElementById("bln").value;
      thn1 = document.getElementById("thn").value;
      t = tgl1;
      bln = (bln1 - 1);
      thn = thn1;
      
      var d = new Date();
      d.setDate(t);
      d.setMonth(bln);
      d.setFullYear(thn);
      x1 = d.getTime();
      var d2 = new Date();
      x2 = d2.getTime();
      beda = x2-x1;
      var umurtahun = beda/(1000*60*60*24*365);
      var umurbulan = (umurtahun - Math.floor(umurtahun)) * 12;
      var umurhari = (umurbulan - Math.floor(umurbulan)) * 31;
      
      document.getElementById("tahun").value = Math.floor(umurtahun);
      document.getElementById("bulan").value = Math.floor(umurbulan);
      document.getElementById("hari").value = Math.floor(umurhari);
            
}

function TanggalLahir(tanggal) {
      umur = document.getElementById("tahun").value;

      var e = new Date();
      
      skr = e.getFullYear();

      thn = skr-umur;
      var tahunlahir = thn;
      document.getElementById("cust_usr_tanggal_lahir").value = "01-01-" + Math.floor(tahunlahir);
            
}
function Umur1(umur) {
      /* tgllahir = document.getElementById("cust_usr_tanggal_lahir").value;
      tanggal = tgllahir.split("-");
      t = tanggal[0];
      bln = (tanggal[1] - 1);
      thn = tanggal[2];*/
      tgl1 = document.getElementById("tgl1").value;
      bln1 = document.getElementById("bln1").value;
      thn1 = document.getElementById("thn1").value;
      t = tgl1;
      bln = (bln1 - 1);
      thn = thn1;
      
      var d = new Date();
      d.setDate(t);
      d.setMonth(bln);
      d.setFullYear(thn);
      x1 = d.getTime();
      var d2 = new Date();
      x2 = d2.getTime();
      beda = x2-x1;
      var umurtahun = beda/(1000*60*60*24*365);
      var umurbulan = (umurtahun - Math.floor(umurtahun)) * 12;
      var umurhari = (umurbulan - Math.floor(umurbulan)) * 31;
      
      document.getElementById("tahun1").value = Math.floor(umurtahun);
      document.getElementById("bulan1").value = Math.floor(umurbulan);
      document.getElementById("hari1").value = Math.floor(umurhari);
            
}

function TanggalLahir1(tanggal) {
      umur = document.getElementById("tahun1").value;

      var e = new Date();
      
      skr = e.getFullYear();

      thn = skr-umur;
      var tahunlahir = thn;
      document.getElementById("cust_tgllahir_pasangan").value = "01-01-" + Math.floor(tahunlahir);
            
}
var _wnd_baru;
function BukaWindowBaru(url,judul)
{
    if(!_wnd_baru) {
			_wnd_baru = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
	} else {
		if (_wnd_baru.closed) {
			_wnd_baru = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
		} else {
			_wnd_baru.focus();
		}
	}
     return false;
}

</script>
<script type="text/javascript" src="ajax.js"></script>
<?php echo $view->RenderBody("module.css",true,false,"EDIT PASIEN"); ?>
<body>
<div id="body">
<div id="scroller">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
	<td>	
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <table width="100%" border="1" cellpadding="1" cellspacing="1"> 

          <tr>
               <td align="left" class="tablesmallheader" width="5%" rowspan="27" valign="top">
        <img hspace="2" height="100" name="original" id="original" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_foto"]) echo $lokTakeFoto."/".$_POST["cust_usr_foto"]; else echo $lokTakeFoto."/default.jpg";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_pic.php?orifoto='+ document.frmEdit.cust_usr_foto.value + '&nama='+document.frmEdit.cust_usr_kode.value,'UploadFoto')">
				<br><font size="2" color="red">Klik 2x untuk mengganti foto</font>
        <input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>">
        <img hspace="2" height="100" name="original1" id="original1" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_ktp"]) echo $lokTakeFoto."/".$_POST["cust_usr_ktp"]; else echo $lokTakeFoto."/ktp_default.png";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_ktp.php?orifoto='+ document.frmEdit.cust_usr_ktp.value + '&nama='+document.frmEdit.cust_usr_kode.value,'UploadFoto')">
				<br><font size="2" color="red">Klik 2x untuk mengganti/upload KTP</font>
        <input type="hidden" name="cust_usr_ktp" id="cust_usr_ktp" value="<?php echo $_POST["cust_usr_ktp"];?>">
        <img hspace="2" height="100" name="original2" id="original2" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_ktp_pasangan"]) echo $lokTakeFoto."/".$_POST["cust_usr_ktp_pasangan"]; else echo $lokTakeFoto."/ktp_default.png";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_ktp_pasangan.php?orifoto='+ document.frmEdit.cust_usr_ktp_pasangan.value + '&nama='+document.frmEdit.cust_usr_kode.value,'UploadFoto')">
				<br><font size="2" color="red">Klik 2x untuk mengganti/upload KTP Pasangan</font>
        <input type="hidden" name="cust_usr_ktp_pasangan" id="cust_usr_ktp_pasangan" value="<?php echo $_POST["cust_usr_ktp_pasangan"];?>">
               </td>
               <td align="right" class="tablesmallheader" width="15%"><strong>No. RM</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode","50","50",$_POST["cust_usr_kode"],"inputField", "readonly",false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Nama Pasien</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","70","70",$_POST["cust_usr_nama"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>TTL(dd-mm-yyyy)</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_tempat_lahir","cust_usr_tempat_lahir","40","40",$_POST["cust_usr_tempat_lahir"],"inputField", null,false);?>/
                    <!--<input type="text" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" size="10" maxlength="10" value="<?php echo $_POST["cust_usr_tanggal_lahir"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);"/><font color="red">*</font>-->
                    <input type="text" id="tgl" name="tgl" size="2" maxlength="2" value="<?php echo $_POST["tgl"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur(this.value);"/> -
                    <input type="text" id="bln" name="bln" size="2" maxlength="2" value="<?php echo $_POST["bln"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur(this.value);"/> -
                    <input type="text" id="thn" name="thn" size="4" maxlength="4" value="<?php echo $_POST["thn"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur(this.value);"/><font color="red">*</font>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Umur</strong>&nbsp;</td>
               <td width="85%" colspan="">
                  <input type="text" name="tahun" id="tahun" size="3" maxlength="3" value="<?php echo $_POST["tahun"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> tahun
                  <input type="text" name="bulan" id="bulan" size="3" maxlength="3" value="<?php echo $_POST["bulan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> bulan  
          		    <input type="text" name="hari" id="hari" size="3" maxlength="3" value="<?php echo $_POST["hari"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> hari 
                </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Jenis Kelamin&nbsp;</td>
               <td width="85%" colspan="">
               		<select name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
            				<option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
            				<option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
            			</select>
              </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Alamat</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_alamat","cust_usr_alamat","85","100",$_POST["cust_usr_alamat"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Agama</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <select name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">	
                    	<option value="" >[ Pilih Agama ]</option>	
                    	<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
                       <option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
              			  <?php } ?>
                    </select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Propinsi</strong>&nbsp;</td>
               <td width="85%" colspan="">
                  <script type="text/javascript" src="js/ajax_kota.js"></script>
          <?//if (!$_POST["id_prop"]) { ?>
      				<select name="prop" id="prop" onchange="ajaxkota(this.value)">
      					<option value="">Pilih Provinsi</option>
      					<?php          
                  $sql = "select * from  global.global_lokasi where lokasi_kabupatenkota='00' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_nama";
                  $dataProvinsi = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataProvinsi);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataProvinsi[$i]['lokasi_propinsi'];?>" <?php if($dataProvinsi[$i]["lokasi_propinsi"]==$_POST["id_prop"]) echo "selected";?>><?php echo $dataProvinsi[$i]['lokasi_nama'];?></option>';
                  <? } ?>                                                                   
      				</select>
         <? //} else { ?> 
                <!--<input type="text" id="prop_nama" name="prop_nama" size="20" maxlength="20" readonly value="<?php echo $_POST["prop_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>                  
                <input type="hidden" id="id_prop" name="id_prop" value="<?php echo $_POST["id_prop"];?>"/>-->                  
         <?// } ?>
            &nbsp;&nbsp;
          <?if (!$_POST["id_kota"]) { ?>
            <select name="kota" id="kota" onchange="ajaxkec(this.value)">
    					<option value="">Pilih Kota</option>
    				</select> 
          <? } else { ?>
            <select name="kota" id="kota" onchange="ajaxkec(this.value)">
    					<option value="">Pilih Kota</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_nama";
                  $dataKabKota = $dtaccess->FetchAll($sql);
        			   //  return $sql;  die();                                                
        		    	for($i=0,$n=count($dataKabKota);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataKabKota[$i]['lokasi_kabupatenkota'];?>" <?php if($dataKabKota[$i]["lokasi_kabupatenkota"]==$_POST["id_kota"]) echo "selected";?>><?php echo $dataKabKota[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select> 
              <!--<input type="text" id="kota_nama" name="kota_nama" size="20" maxlength="20" readonly value="<?php echo $_POST["kota_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>                  
              <input type="hidden" id="id_kota" name="id_kota" value="<?php echo $_POST["id_kota"];?>"/>-->                  
          <? } ?>


           </span>              
                </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Kecamatan/Kelurahan</strong>&nbsp;</td>
               <td width="85%" colspan="">
                  <?if (!$_POST["id_kec"]) { ?>
        		<select name="kec" id="kec" onchange="ajaxkel(this.value)">
    					<option value="">Pilih Kecamatan</option>
    				</select>	
          <? } else { ?>
            <select name="kec" id="kec" onchange="ajaxkel(this.value)">
    					<option value="">Pilih Kecamatan</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kabupatenkota='".$_POST["id_kota"]."' and lokasi_kelurahan='0000' order by lokasi_nama";
                  $dataKec = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataKec);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataKec[$i]['lokasi_kecamatan'];?>" <?php if($dataKec[$i]["lokasi_kecamatan"]==$_POST["id_kec"]) echo "selected";?>><?php echo $dataKec[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select> 
              <!--<input type="text" id="kec_nama" name="kec_nama" size="20" maxlength="20" readonly value="<?php echo $_POST["kec_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>                  
              <input type="hidden" id="id_kec" name="id_kec" value="<?php echo $_POST["id_kec"];?>"/>-->                  
          <? } ?>

         &nbsp;&nbsp;
        <?if (!$_POST["id_kel"]) { ?>
            <select name="kel" id="kel">
    					<option value="">Pilih Kelurahan/Desa</option>
    				</select> 
          <? } else { ?>
            <select name="kel" id="kel">
    					<option value="">Pilih Kelurahan/Desa</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kabupatenkota='".$_POST["id_kota"]."' and lokasi_kecamatan='".$_POST["id_kec"]."' order by lokasi_nama";
                  $dataKel = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataKel);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataKel[$i]['lokasi_kelurahan'];?>" <?php if($dataKel[$i]["lokasi_kelurahan"]==$_POST["id_kel"]) echo "selected";?>><?php echo $dataKel[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select> 
              <!--<input type="text" id="kel_nama" name="kel_nama" size="20" maxlength="20" readonly value="<?php echo $_POST["kel_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>-->                  
              <input type="hidden" id="id_kel" name="id_kel" value="<?php echo $_POST["id_prop"].".".$_POST["id_kota"].".".$_POST["id_kec"].".".$_POST["id_kel"];?>"/>                  
          <? } ?> <font color="red">*</font>              
                </td>
          </tr>
          <tr>
           <td align="right" class="tablesmallheader" width="15%"><strong>Nama Dusun / RT / RW</strong>&nbsp;</td>
           <td width="85%" colspan="">
                <?php echo $view->RenderTextBox("cust_usr_dusun","cust_usr_dusun","50","50",$_POST["cust_usr_dusun"],"inputField", null,false);?>
           </td>
          </tr>
          	<script>
		var groups=document.frmEdit.id_kecamatan.options.length
		var group=new Array(groups)
		for (i=0; i<groups; i++)
			group[i]=new Array()

				<?php for($x=0,$n=count($dataKec);$x<$n;$x++) { 
					$sql = 'select * from global.global_kelurahan where id_kec='.QuoteValue(DPE_CHAR,$idKec[$x]);
					$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
					$dataKel = $dtaccess->FetchAll($rs); 
					
				?>

					<?php for($q=0,$e=1,$w=count($dataKel);$q<$w,$e<$w;$q++,$e++) {?>
						group[<?php echo $x;?>][<?php echo $q;?>]=new Option('<?php echo $e.'. '.$dataKel[$q]["kel_nama"];?>','<?php echo $dataKel[$q]["kel_id"];?>')
					<?php } ?>
				<?php } ?>

		var temp=document.frmEdit.id_kelurahan

		function redirect(x){
			for (m=temp.options.length-1;m>0;m--)
				temp.options[m]=null
			for (i=0;i<group[x].length;i++){
				temp.options[i]=new Option(group[x][i].text,group[x][i].value)
			}
				temp.options[0].selected=true
			}

	</script>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>No. Telp / HP</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_no_hp","cust_usr_no_hp","40","40",$_POST["cust_usr_no_hp"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>No. KTP/Identitas </strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_nik","cust_usr_nik","60","60",$_POST["cust_usr_nik"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Pendidikan&nbsp;</td>
               <td width="85%" colspan="">
          		   <select name="id_pendidikan" id="id_pendidikan" onKeyDown="return tabOnEnter(this, event);">	
                	<option value="--" >[ Pilih sekolah ]</option>	
                	<?php for($i=0,$n=count($dataPendidikan);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataPendidikan[$i]["pendidikan_id"];?>" <?php if($dataPendidikan[$i]["pendidikan_id"]==$_POST["id_pendidikan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPendidikan[$i]["pendidikan_nama"];?></option>
          			  <?php } ?>
                </select>
              </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Pekerjaan&nbsp;</td>
               <td width="85%" colspan="">
          		   <select name="id_pekerjaan" id="id_pekerjaan" onKeyDown="return tabOnEnter(this, event);">	
                  	 <option value="" ></option>
                  	 <?php for($i=0,$n=count($dataPekerjaan);$i<$n;$i++){ ?>
                     <option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"];?>" <?php if($dataPekerjaan[$i]["pekerjaan_id"]==$_POST["id_pekerjaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPekerjaan[$i]["pekerjaan_nama"];?></option>
            			   <?php } ?>	
                 </select>
              </td>
          </tr>
          <?php if($_POST["konf_reg_ayah"]=='y'){?>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Nama Ayah Kandung</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_user_ayah","cust_user_ayah","40","40",$_POST["cust_user_ayah"],"inputField", null,false);?>
               </td>
          </tr>
          <?php } if($_POST["konf_reg_ibu"]=='y'){ ?>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Nama Ibu Kandung</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_ibu","cust_usr_ibu","40","40",$_POST["cust_usr_ibu"],"inputField", null,false);?>
               </td>
          </tr>
          <?php } ?>
<tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Kebangsaan&nbsp;</td>
               <td width="85%" colspan="">
          		   <select name="cust_usr_asal_negara" id="cust_usr_asal_negara" onKeyDown="return tabOnEnter(this, event);">	
                	 <option value="" >Pilih Kebangsaan</option>
                	 <?php for($i=0,$n=count($dataNegara);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataNegara[$i]["negara_id"];?>" <?php if($dataNegara[$i]["negara_id"]==$_POST["cust_usr_asal_negara"]) echo "selected"; ?>><?php echo $dataNegara[$i]["negara_nama"]." ( ".$dataNegara[$i]["negara_kode"]." ) ";?></option>
  	             		<?php } ?>	
                 </select>
              </td>
       
          </tr>
<tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Riwayat Alergi&nbsp;</td>
               <td width="85%" colspan="">
  		<input type="text" name="cust_usr_alergi" id="cust_usr_alergi" size="75" value="<?php echo $_POST["cust_usr_alergi"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>              </td>
       
          </tr>


          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Status Perkawinan&nbsp;</td>
               <td width="85%" colspan="">
          		   <select name="id_status_perkawinan" id="id_status_perkawinan" onKeyDown="return tabOnEnter(this, event);">	
                	 <option value="" >Pilih Status Perkawinan</option>
                	 <?php for($i=0,$n=count($dataStatus);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataStatus[$i]["status_perkawinan_id"];?>" <?php if($dataStatus[$i]["status_perkawinan_id"]==$_POST["id_status_perkawinan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatus[$i]["status_perkawinan_nama"];?></option>
          			   <?php } ?>	
                 </select>
              </td>
          </tr>
          <?php if($_POST["konf_reg_istri"]=='y'){ ?>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Nama Pasangan&nbsp;</td>
               <td width="85%" colspan="">
                <input type="text" name="cust_usr_istri" id="cust_usr_istri" size="30" maxlength="40" value="<?php echo $_POST["cust_usr_istri"];?>"/></font>
              </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Tanggal Lahir Pasangan(dd-mm-yyyy)</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <input type="text" id="tgl1" name="tgl1" size="2" maxlength="2" value="<?php echo $_POST["tgl1"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur1(this.value);"/> -
                    <input type="text" id="bln1" name="bln1" size="2" maxlength="2" value="<?php echo $_POST["bln1"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur1(this.value);"/> -
                    <input type="text" id="thn1" name="thn1" size="4" maxlength="4" value="<?php echo $_POST["thn1"];?>" onKeyDown="return tabOnEnter(this, event);" onClick="Umur1(this.value);"/><font color="red">*</font>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Usia Pasangan</strong>&nbsp;</td>
               <td width="85%" colspan="">
                  <input type="text" name="tahun1" id="tahun1" size="3" maxlength="3" value="<?php echo $_POST["tahun1"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir1(this.value);"/> tahun
                  <input type="text" name="bulan1" id="bulan1" size="3" maxlength="3" value="<?php echo $_POST["bulan1"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir1(this.value);"/> bulan  
          		    <input type="text" name="hari1" id="hari1" size="3" maxlength="3" value="<?php echo $_POST["hari1"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir1(this.value);"/> hari 
                </td>
          </tr>

          <?php } ?>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Nama Penanggung Jawab </strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <input type="text" name="cust_usr_penanggung_jawab" id="cust_usr_penanggung_jawab" size="30" maxlength="65" value="<?php echo $_POST["cust_usr_penanggung_jawab"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                     &nbsp;Status :
                     <select name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">	
                      	<option value="" ></option>
                      	<?php for($i=0,$n=count($dataStatusPJ);$i<$n;$i++){ ?>
                        <option value="<?php echo $dataStatusPJ[$i]["status_pj_id"];?>" <?php if($dataStatusPJ[$i]["status_pj_id"]==$_POST["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatusPJ[$i]["status_pj_nama"];?></option>
                			  <?php } ?>	
                     </select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Pendidikan Pasangan </strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <select name="cust_usr_dik_pasangan" id="cust_usr_dik_pasangan" onKeyDown="return tabOnEnter(this, event);">	
                    	<option value="" ></option>	
                    	<?php for($i=0,$n=count($dataPendidikan);$i<$n;$i++){ ?>
                      <option value="<?php echo $dataPendidikan[$i]["pendidikan_id"];?>" <?php if($dataPendidikan[$i]["pendidikan_id"]==$_POST["cust_usr_dik_pasangan"]) echo "selected"; ?>><?php echo $dataPendidikan[$i]["pendidikan_nama"];?></option>
              			  <?php } ?>
                    </select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Pekerjaan Pasangan </strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <select name="cust_usr_pekerjaan_pasangan" id="cust_usr_pekerjaan_pasangan" onKeyDown="return tabOnEnter(this, event);">	
                    	 <option value="" ></option>
                    	 <?php for($i=0,$n=count($dataPekerjaan);$i<$n;$i++){ ?>
                       <option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"];?>" <?php if($dataPekerjaan[$i]["pekerjaan_id"]==$_POST["cust_usr_pekerjaan_pasangan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPekerjaan[$i]["pekerjaan_nama"];?></option>
              			   <?php } ?>	
                    </select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>No. KTP/Identitas Pasangan</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_nik_pasangan","cust_usr_nik_pasangan","60","60",$_POST["cust_usr_nik_pasangan"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Alamat Pasangan (KTP)</strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("cust_usr_alamat_pasangan","cust_usr_alamat_pasangan","60","60",$_POST["cust_usr_alamat_pasangan"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>Agama Pasangan</strong>&nbsp;</td>
               <td width="85%" colspan="">
	         <select name="cust_usr_agama_pasangan" id="cust_usr_agama_pasangan" >	
        		<option value="" >[ Pilih Agama ]</option>	
          	<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
             <option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama_pasangan"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
    			  <?php } ?>
            </select> 
               </td>
          </tr>
          <tr>
               <td align="right" class="tablesmallheader" width="15%"><strong>No HP/Telp Pasangan</strong>&nbsp;</td>
               <td width="85%" colspan="">
      	<input type="text" name="cust_usr_telp_pasangan" id="cust_usr_telp_pasangan" size="20" value="<?php echo $_POST["cust_usr_telp_pasangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
          </tr>

        <!-- <tr>
         <td align="right" class="tablesmallheader" width="15%"><strong>Kelengkapan Dokumen&nbsp;</td>
  		   <td width="43" class="tablecontent-odd">-->
  	         <!--	<input type="checkbox" name="reg_kelengkapan_dokumen" id="reg_kelengkapan_dokumen" size="30" maxlength="30" value="<?php echo $_POST["reg_kelengkapan_dokumen"];?>" onKeyDown="return tabOnEnter(this, event);"/>
                  -->
           <!--<input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="reg_kelengkapan_dokumen" id="reg_kelengkapan_dokumen" <?php if ($_POST["reg_kelengkapan_dokumen"]=='1') echo "checked"; ?> value="y"/>
          
         </td> 
         </tr>-->
         
          <tr>
               <td colspan="3" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$backPage."';\"");?>                    
               </td>
          </tr>
     </table>                   
     </td>
</tr>
</table>
	</fieldset>
</td>
</tr>
</table>

<script>document.frmEdit.cust_usr_nama.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("id_kelurahan","id_kelurahan",$row_edit["id_kelurahan"]);?>
<?php echo $view->RenderHidden("usr_cust_id","usr_cust_id",$usrCustId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<input type="hidden" name="id_klinik_waktu_tunggu" id="id_klinik_waktu_tunggu" value="<?php echo $_POST["id_klinik_waktu_tunggu"];?>">
<input type="hidden" name="reguler" id="reguler" value="<?php echo $_POST["reguler"];?>">
<input type="hidden" name="eksekutif" id="eksekutif" value="<?php echo $_POST["eksekutif"];?>">
<input type="hidden" name="id_loket" id="id_loket" value="<?php echo $_POST["id_loket"];?>">
<input type="hidden" name="lama" id="lama" value="<?php echo $_POST["lama"];?>">
<input type="hidden" name="jkn" id="jkn" value="<?php echo $_POST["jkn"];?>">
<input type="hidden" name="cust_usr_no_identitas" id="cust_usr_no_identitas" value="<?php echo $_POST["cust_usr_no_identitas"];?>">
</form>
</div>
<br><br><br>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
