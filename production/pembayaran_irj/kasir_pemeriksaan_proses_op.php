<?php        
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");                                                             
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
     
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
   if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) || !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     $delPage = "kasir_pemeriksaan_proses.php?";

     $table = new InoTable("table","100%","left");

     
     if ($_GET["id_dokter"]) $_POST["id_dokter"]=$_GET["id_dokter"];
     if ($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
     if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"]=$_GET["reg_jenis_pasien"];

	
	if($_GET["id_reg"] || $_GET["pembayaran_id"]) {
		$sql = "select a.reg_jenis_pasien, a.reg_tipe_jkn, a.id_poli,cust_usr_alamat, cust_usr_nama, cust_usr_kode, b.cust_usr_jenis_kelamin, 
            cust_usr_foto, a.id_dokter, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, a.id_perusahaan, 
            c.fol_keterangan, a.id_jamkesda_kota, b.cust_usr_jkn, a.reg_tipe_layanan, d.pembayaran_dijamin 
            from  klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran d on d.pembayaran_id = a.id_pembayaran 
            join  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
            left join klinik.klinik_folio c on c.id_reg=a.reg_id
            where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);

    $dataPasien= $dtaccess->Fetch($sql);
    
    $_POST['fol_id'] = $_GET["fol_id"];
		
    $_POST["id_reg"] = $_GET["id_reg"];  
		$_POST["id_biaya"] = $_GET["biaya"]; 
    $_POST["pembayaran_id"] = $_GET["pembayaran_id"];
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    if (!$_POST["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    if (!$_POST["id_poli"]) $_POST["id_poli"] = $dataPasien["id_poli"];
    if (!$_POST["id_pelaksana"]) $_POST["id_pelaksana"] = $dataPasien["id_pelaksana"];
    if (!$_POST["id_perusahaan"]) $_POST["id_perusahaan"] = $dataPasien["id_perusahaan"];
    if (!$_POST["id_jamkesda_kota"]) $_POST["id_jamkesda_kota"] = $dataPasien["id_jamkesda_kota"];
    if (!$_POST["cust_usr_jkn"]) $_POST["cust_usr_jkn"] = $dataPasien["cust_usr_jkn"];
    if (!$_POST["reg_tipe_jkn"]) $_POST["reg_tipe_jkn"] = $dataPasien["reg_tipe_jkn"];
    if (!$_POST["id_dokter"]) $_POST["id_dokter"] = $dataPasien["id_dokter"];
		$_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];
		$_POST["pembayaran_id"] = $_GET["pembayaran_id"];
    $_POST["fol_keterangan"] = $dataPasien["fol_keterangan"];
    $_POST["reg_tipe_layanan"] = $dataPasien["reg_tipe_layanan"];
    
		$sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
            and id_dep =".QuoteValue(DPE_CHAR,$depId);
		$dataKet = $dtaccess->Fetch($sql);
		$_POST["fol_keterangan"] = $dataKet["fol_keterangan"];
		
		$lokasi = $ROOT."gambar/foto_pasien";
		
		 $sql = "select sum(pembayaran_total) as total, sum(pembayaran_yg_dibayar) as dibayar from klinik.klinik_pembayaran a
            where pembayaran_flag = 'n' and pembayaran_jenis = 'C' and id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]); 
     $dataCicilan = $dtaccess->Fetch($sql);
     
     $sisaCicilan = $dataCicilan["total"] - $dataCicilan["dibayar"];   
	}
  
     // cari data registrasinya hari ini
     $sql = "select reg_id,poli_nama,c.usr_name,d.usr_name as dokter_sender,reg_who_update
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id
            left join global.global_auth_user c on a.id_dokter = c.usr_id
            left join global.global_auth_user d on a.reg_dokter_sender = d.usr_id
            where a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and a.id_pembayaran =".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
     $sql .= " order by reg_tanggal, reg_waktu asc";
 		 $dataorderPoli= $dtaccess->FetchAll($sql);
     
     $sql = "select * from  klinik.klinik_folio a left join global.global_auth_user b on a.id_dokter = b.usr_id
			       where fol_lunas='n' and id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by fol_waktu asc"; 
		 //echo $sql;
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);
//      echo $sql;
//      die();
    for($i=0,$n=count($dataTable);$i<$n;$i++){
          
          //if($dataTable[$i]["fol_jumlah"]){
            //$total = $dataTable[$i]["fol_jumlah"]*$dataTable[$i]["fol_nominal"];
          //}else{
 
              $total = $dataTable[$i]["fol_hrs_bayar"];
              $totalBiaya = $totalBiaya+$dataTable[$i]["fol_nominal"];
              $dijamin = $dataTable[$i]["fol_dijamin"];
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
          $totalDijamin+=$dijamin;
          //$grandTotalHarga = $totalHarga;
   } 
   
     $sql = "select * from global.global_auth_poli where poli_tipe='P'";
     $rs = $dtaccess->Execute($sql);
     $op = $dtaccess->Fetch($rs);
     //echo $sql; 
   
   $sql = "select * from klinik.klinik_inacbg where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
   $rs = $dtaccess->Execute($sql);
   $inacbg = $dtaccess->Fetch($rs);
   //echo "appv-".$inacbg["inacbg_appv"];
   
   $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
   $uangmuka = $dtaccess->Fetch($sql);
   
   $sql = "select pembayaran_dijamin from  klinik.klinik_pembayaran
     where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);

   $rs_dijamin = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   $dataDijamin = $dtaccess->Fetch($rs_dijamin);
   
   //total biaya
   $totalBiaya=$totalBiaya;   
   //harga dijamin
   $dijaminHarga = $dataDijamin["pembayaran_dijamin"]+$inacbg["inacbg_topup"];
   
   //echo "masuk = ".$op["poli_id"];
   //perhitungan rumus JKN
   if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$op["poli_id"]){
   $totalHarga=$totalHarga;
   } elseif($_POST["reg_jenis_pasien"]=="5" && $totalBiaya > $dijaminHarga){
   $totalHarga=$totalBiaya-$dijaminHarga;
   }elseif($_POST["reg_jenis_pasien"]=="5" && $totalBiaya < $dijaminHarga){
   $totalHarga=$dijaminHarga-$totalBiaya;
   } else $totalHarga=$totalHarga;
   
   //if ($totalHarga<0) $totalHarga=0; 
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga-$uangmuka["total"];   	 
   //echo "total ".$totalHarga."-".$inacbg["inacbg_topup"];
   
   if($uangmuka["total"]>0){
   $retur = $uangmuka["total"] - $totalHarga;
   if($retur<0) $retur=0;
   } 	 

 if ($_POST["btnTindakan"] ) {
 
       $sql_rawat = "select * from klinik.klinik_perawatan 
                     where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                     and id_dep =".QuoteValue(DPE_CHAR,$depId);
       $dataPerawat= $dtaccess->Fetch($sql_rawat);	
 
 if(!$dataPerawat){
 
          $dbTable = " klinik.klinik_perawatan";
          $dbField[0] = "rawat_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "rawat_waktu_kontrol";
          $dbField[4] = "rawat_tanggal";
          $dbField[5] = "rawat_flag"; 
          $dbField[6] = "rawat_flag_komen"; 
          $dbField[7] = "id_poli"; 
          $dbField[8] = "id_dep";
          $dbField[9] = "rawat_who_update";
          $dbField[10] = "rawat_waktu";         
          
          $_POST["rawat_id"] = $dtaccess->GetTransID();          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
          $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$poliId); 
          $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
          $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          $dtmodel->Insert() or die("insert  error");	
      
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);
      }
      
       if($_POST["id_tindakan"][0]){
    
        $tindakanId = explode("-", $_POST["id_tindakan"][0]);
    
              //for($i=0,$n=count($_POST["tindakan_id"]);$i<$n;$i++) { 
              $dbTable = "klinik.klinik_perawatan_tindakan";
              $dbField[0] = "rawat_tindakan_id";   // PK
              $dbField[1] = "id_rawat";
              $dbField[2] = "id_tindakan";
              $dbField[3] = "rawat_tindakan_total";
              $dbField[4] = "id_dep";                
              $dbField[5] = "rawat_tindakan_jumlah";  
              
              $sql_perawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                              and id_dep =".QuoteValue(DPE_CHAR,$depId);
              $dataRawat= $dtaccess->Fetch($sql_perawat);	
              $_POST["id_rawat_pemeriksaan"] = $dataRawat["rawat_id"];
              
              $totalTindNom = StripCurrency($tindakanId[1])*$_POST["txtQty"];
                  // echo $_POST["txtQty"];
                  // die();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
                   $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawat["rawat_id"]);
                   $dbValue[2] = QuoteValue(DPE_CHAR,$tindakanId[0]);
                   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindNom));
                   $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["txtQty"]);
                  // print_r ($dbValue);
                  // die();
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
                   if($tindakanId[0]) $dtmodel->Insert() or die("insert  error");
                   
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);
    
               //for($i=0,$n=count($_POST["tindakan_id"]);$i<$n;$i++) { 
        
              //nyari id dokter siapa yg meriksa //
              //$sql = "select id_dok_update from klinik.klinik_folio where fol_jenis = 'RS' and id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
              //$idDokter = $dtaccess->Fetch($sql);
    
              $dbTable = "klinik.klinik_folio";
              $dbField[0] = "fol_id";   // PK
              $dbField[1] = "id_reg";
              $dbField[2] = "fol_nama";
              $dbField[3] = "fol_nominal";
              $dbField[4] = "fol_jenis";
              $dbField[5] = "id_cust_usr";
              $dbField[6] = "fol_waktu";
              $dbField[7] = "fol_lunas";
              $dbField[8] = "id_biaya";
              $dbField[9] = "id_poli";
              $dbField[10] = "fol_jenis_pasien";
              $dbField[11] = "id_dep";
              $dbField[12] = "who_when_update";
              $dbField[13] = "id_dokter";
              $dbField[14] = "fol_total_harga";
              $dbField[15] = "fol_jumlah";
              $dbField[16] = "fol_nominal_satuan"; 
              
             $sqltdk = "select biaya_jenis,biaya_nama,biaya_total from klinik.klinik_biaya where 
                        biaya_id = ".QuoteValue(DPE_CHAR,$tindakanId[0])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataTdk= $dtaccess->Fetch($sqltdk);
             
             $totalTindNom = StripCurrency($dataTdk["biaya_total"])*$_POST["txtQty"];
              
                   $folId = $dtaccess->GetTransID();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
                   $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
                   $dbValue[2] = QuoteValue(DPE_CHAR,$dataTdk["biaya_nama"]);
                   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindNom));
                   $dbValue[4] = QuoteValue(DPE_CHAR,$dataTdk["biaya_jenis"]);
                   $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_cust_usr"]);
                   $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                   $dbValue[7] = QuoteValue(DPE_CHARKEY,'n');
                   $dbValue[8] = QuoteValue(DPE_CHAR,$tindakanId[0]);
                   $dbValue[9] = QuoteValue(DPE_CHARKEY,$poliId);
                   $dbValue[10] = QuoteValue(DPE_NUMERICKEY,'2');
                   $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[12] = QuoteValue(DPE_CHAR,$userId);
                   $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                   $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindNom)*$_POST["txtQty"]);
                   $dbValue[15] = QuoteValue(DPE_NUMERIC,$_POST["txtQty"]);
                   $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTdk["biaya_total"]));
                   
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                   
                   if($tindakanId[0]) $dtmodel->Insert() or die("insert  error");
                   
                   unset($dbField);
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);
                    
            	$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$tindakanId[0]);
    					$splitData = $dtaccess->FetchAll($sql);
    					
    					for($a=0,$b=count($splitData);$a<$b;$a++) { 
    						$dbTable = "klinik.klinik_folio_split";
    					
    						$dbField[0] = "folsplit_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_split";
    						$dbField[3] = "folsplit_nominal";
    						
    						$totalTindNom = $splitData[$a]["bea_split_nominal"]*$_POST["txtQty"];
    							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
    						$dbValue[2] = QuoteValue(DPE_CHAR,$splitData[$a]["id_split"]);
    						$dbValue[3] = QuoteValue(DPE_NUMERIC,$totalTindNom);
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey); 
        			 } 
            
             $sql = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                    and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataReg = $dtaccess->Fetch($sql);
           
           if(!$dataReg){    
              // Insert Biaya Pembayaran //
              $dbTable = "klinik.klinik_pembayaran";
              $dbField[0] = "pembayaran_id";   // PK
              $dbField[1] = "pembayaran_create";
              $dbField[2] = "pembayaran_who_create";
              $dbField[3] = "pembayaran_tanggal";
              $dbField[4] = "id_reg";
              $dbField[5] = "id_cust_usr";
              $dbField[6] = "pembayaran_total";
              $dbField[7] = "id_dep";
              $dbField[8] = "pembayaran_flag";
              $dbField[9] = "pembayaran_hrs_bayar";
                            
                   $byrId = $dtaccess->GetTransID();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrId);
                   $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                   $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
                   $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
                   $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                   $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                   $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($tindakanId[1]));
                   $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[8] = QuoteValue(DPE_CHAR,'n');
                   $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($tindakanId[1]));
                   
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                   
                   $dtmodel->Insert() or die("insert  error");
                   
                   unset($dbField);
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);  
            }       
          }
               
      $sql = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
              and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $dataBayar = $dtaccess->Fetch($sql);                     
      
      $kembali = "kasir_pemeriksaan_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."
                  &id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
      header("location:".$kembali);
      exit();
    }
       
    if ($_POST["btnOk"]) {
      $sql = "update  klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
      
      $kembali = "kasir_pemeriksaan_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."
                  &id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
      header("location:".$kembali);
      exit();    
    }
	
	// ----- print data ----- //
	if ($_POST["btnPrint"]) {	
      $sql = "select dep_konf_cetak_kasir from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_cetak_kasir"] = $row_edit["dep_konf_cetak_kasir"]; 
      
     $cetak = "y";
	}
	
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {	
  
  //echo $_POST["op"]; die();
    
  //yang lama
/*  		$sql = "update  klinik.klinik_registrasi set reg_status='E0', 
    reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' , reg_bayar = 'n',
    id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
    id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]).",
    reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"])."
    where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
    " and id_dep=".QuoteValue(DPE_CHAR,$depId); */
    
    $sql = "select * from klinik.klinik_pembayaran where 
            id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
            and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $dataReg = $dtaccess->Fetch($sql);

   if($_POST["fol_keterangan"]){
    $sql = "update klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
            where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   }
  
   $sql = "select reg_utama from klinik.klinik_registrasi where 
          (id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." or reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).") 
          and reg_utama is not null";
   $reg = $dtaccess->Execute($sql);
   $regUtama= $dtaccess->FetchAll($reg);
   //print_r($regUtama); die();
   //echo $regUtama; die();
   
   $sql = "select reg_id from klinik.klinik_registrasi where reg_utama = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
   //reg_utama = ".QuoteValue(DPE_CHAR,$regUtama[0]["reg_utama"]);
   //echo $sql;
   $rs = $dtaccess->Execute($sql);
   $allReg = $dtaccess->FetchAll($rs);
   //echo $sql; die();
   
    for($i=0,$n=count($allReg);$i<$n;$i++) {
  /*	$sql = "update  klinik.klinik_registrasi set reg_status='F0', 
      reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' , reg_bayar = 'n'
      where reg_id = ".QuoteValue(DPE_CHAR,$allReg[$i]["reg_id"]).
      " and id_dep=".QuoteValue(DPE_CHAR,$depId);    */
      // update registrasi // 
  
  		$sql = "update klinik.klinik_registrasi set reg_waktu = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n'
              where reg_id = ".QuoteValue(DPE_CHAR,$allReg[$i]["reg_id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
    }

/*
		$sql = "update  klinik.klinik_registrasi set reg_status='F0', 
    reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' , reg_bayar = 'n'
    where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
    " and id_dep=".QuoteValue(DPE_CHAR,$depId);  */
    
		$sql = "update klinik.klinik_registrasi set reg_waktu = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n'
            where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);  
    // update registrasi // 
    $dtaccess->Execute($sql);
    
    // cari dokter e //
    $sql = "select usr_name from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    $rs = $dtaccess->Execute($sql);
    $Doktere = $dtaccess->Fetch($rs);    
    
    // update data pembayaran //
    // jika pembayarannya ada diskon ee //
    if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
    //ambil dulu yang lama 
    $sql = "select pembayaran_yg_dibayar,pembayaran_total,pembayaran_dijamin,pembayaran_hrs_bayar from klinik.klinik_pembayaran
            where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $DataPembayaranLama = $dtaccess->Fetch($rs);    
    
    //tambahkan dengan hasil POST    
    $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
    //echo $DataPembayaranLama["pembayaran_yg_dibayar"]."-".$_POST["total_harga"]; die();
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"]){
    $pembayaranDijamin=$_POST["total_dijamin"];
    } else {
    $pembayaranDijamin=$DataPembayaranLama["pembayaran_dijamin"]+$_POST["inacbg_topup"];
    }
    $totalPembayaran=StripCurrency($_POST["total_harga"]);
    
    $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"] && $_POST["total_harga"]<>0){
    $pembayaranHrsBayar = $_POST["total_harga"];
    }else{ 
    $pembayaranHrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+StripCurrency($_POST["txtTotalDibayar"]);
    }

    if ($pembayaranHrsBayar<0) $pembayaranHrsBayar=0;
    //if ($pembayaranDijamin >= $_POST["total_biaya"]) $pembayaranHrsBayar=$pembayaranDijamin-$_POST["total_biaya"];
    //elseif ($pembayaranDijamin >= $_POST["total_biaya"]) $pembayaranHrsBayar=$_POST["total_harga"];
    
    if($pembayaranDijamin > $_POST["total_biaya"]){
      $pembayaranSelisih = $pembayaranDijamin - $_POST["total_biaya"] + $pembayaranHrsBayar;
      
    } else  {
    //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $pembayaranSelisih = $_POST["total_biaya"] - $pembayaranDijamin - $pembayaranHrsBayar;
      
    } 
      //echo $pembayaranSelisih ." dan ".$pembayaranHrsBayar." dan ". $pembayaranDijamin ." - ".$_POST["total_biaya"];
      
    if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      //$pembayaranHrsBayar=$pembayaranHrsBayar+$_POST["uangmuka"];
    } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      //$pembayaranHrsBayar=$_POST["uangmuka"] - $pembayaranHrsBayar;
      //echo "hrs bayar = ".$pembayaranHrsBayar; die();
    }
    
    //update pembayaran
    $sql =  "update klinik.klinik_pembayaran set pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$Doktere["usr_name"]).",                                                                                                                                                                                                       
             pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d")).", 
             pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s")).", 
             pembayaran_jenis = 'T', pembayaran_diskon =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])).", 
             pembayaran_diskon_persen =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])).",
             id_jbayar =".QuoteValue(DPE_CHAR,StripCurrency($_POST["id_jbayar"])).",
             pembayaran_service_cash =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]))."
             where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             //echo $sql;die();
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"]){
      $sql = "update klinik.klinik_pembayaran set pembayaran_dijamin=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_dijamin"]))."
              where pembayaran_id=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
      $rs = $dtaccess->Execute($sql);
    }
    
    if(StripCurrency($_POST["txtDibayar"][0]) < StripCurrency($_POST["txtTotalDibayar"])){
      $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
              pembayaran_total = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
              pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0])).", 
              pembayaran_hrs_bayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
              where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
              //echo StripCurrency($_POST["txtDibayar"][0]) ."<". $_POST["total_harga"]."-".$sql; die();
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      
      if($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin >= $_POST["total_biaya"]){
        if($_POST["reg_tipe_jkn"]=="1" || $_POST["reg_tipe_layanan"]=="1"){
          $sql = "update klinik.klinik_pembayaran set pembayaran_subsidi=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_negatif = '0' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        } else {
          $sql = "update klinik.klinik_pembayaran set pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_negatif = '0' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        }
      } elseif($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin <= $_POST["total_biaya"]){
        if($_POST["reg_tipe_jkn"]=="1" || $_POST["reg_tipe_layanan"]=="1"){
          $sql = "update klinik.klinik_pembayaran set pembayaran_subsidi=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_positif = '0', pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))." 
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        } else {
          $sql = "update klinik.klinik_pembayaran set pembayaran_selisih_positif = '0', 
                  pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))."
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        }
      }
    } else {
      if($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin > 0.00 && $_POST["id_poli"]==$_POST["op"]){
				if($pembayaranDijamin > $_POST["total_biaya"] && $_POST["total_harga"]==0){ //selisih positif
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = '0', pembayaran_subsidi = '0', pembayaran_hrs_bayar = '0', 
								    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih))).", 
                    pembayaran_selisih_negatif = '0' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
						        //echo "naik kelas positif1 ".$sql; die();
				} elseif($pembayaranDijamin > $_POST["total_biaya"] && $_POST["total_harga"]<>0){ //selisih 
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", pembayaran_subsidi = '0', 
                    pembayaran_hrs_bayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih))).", 
                    pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))."
								    where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
						        //echo "naik kelas positif>  ".$sql; die();
				}
				else if($pembayaranDijamin < $_POST["total_biaya"]){ // selisih negatif
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", pembayaran_subsidi = 0, 
                    pembayaran_hrs_bayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih)))." 
                    where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
					//echo "naik kelas negatif <  ".$sql; die();
				}
			} else {
        $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'y', 
                pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                pembayaran_hrs_bayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
                where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                //echo $sql; die();
      }
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    }
    
    // jika gk ada diskon eee//
    } else {  
    //ambil dulu yang lama 
    $sql = "select pembayaran_yg_dibayar,pembayaran_total,pembayaran_dijamin,pembayaran_hrs_bayar from klinik.klinik_pembayaran
            where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $DataPembayaranLama = $dtaccess->Fetch($rs);    
    
    //tambahkan dengan hasil POST    
    $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
    //echo $DataPembayaranLama["pembayaran_yg_dibayar"]."-".$_POST["total_harga"]; die();
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"]){
    $pembayaranDijamin=$_POST["total_dijamin"];
    } else {
    $pembayaranDijamin=$DataPembayaranLama["pembayaran_dijamin"];
    }
    $totalPembayaran=StripCurrency($_POST["total_harga"]);
    
    $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"] && $_POST["total_harga"]<>0){
    $pembayaranHrsBayar = $_POST["total_harga"];
    }else{ 
    $pembayaranHrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"]-$pembayaranDijamin;
    }

    if ($pembayaranHrsBayar<0) $pembayaranHrsBayar=0;
    //if ($pembayaranDijamin >= $_POST["total_biaya"]) $pembayaranHrsBayar=$pembayaranDijamin-$_POST["total_biaya"];
    //elseif ($pembayaranDijamin >= $_POST["total_biaya"]) $pembayaranHrsBayar=$_POST["total_harga"];
    
    if($pembayaranDijamin > $_POST["total_biaya"]){
      $pembayaranSelisih = $pembayaranDijamin - $_POST["total_biaya"] + $pembayaranHrsBayar;
      
    } else  {
    //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $pembayaranSelisih = $_POST["total_biaya"] - $pembayaranDijamin - $pembayaranHrsBayar;
      
    } 
      //echo $pembayaranSelisih ." dan ".$pembayaranHrsBayar." dan ". $pembayaranDijamin ." - ".$_POST["total_biaya"];
    
    if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      //$pembayaranHrsBayar=$pembayaranHrsBayar+$_POST["uangmuka"];
    } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      //$pembayaranHrsBayar=$_POST["uangmuka"] - $pembayaranHrsBayar;
      //echo "hrs bayar = ".$pembayaranHrsBayar; die();
    }
    
    //update pembayaran
    $sql =  "update klinik.klinik_pembayaran set pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$Doktere["usr_name"]).",                                                                                                                                                                                                       
             pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d")).", 
             pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s")).", 
             pembayaran_jenis = 'T', pembayaran_diskon =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])).", 
             pembayaran_diskon_persen =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])).",
             id_jbayar =".QuoteValue(DPE_CHAR,StripCurrency($_POST["id_jbayar"])).",
             pembayaran_service_cash =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]))."
             where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             //echo $sql;die();
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    if($_POST["reg_jenis_pasien"]=="5" && $_POST["id_poli"]==$_POST["op"]){
      $sql = "update klinik.klinik_pembayaran set pembayaran_dijamin=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_dijamin"]))."
              where pembayaran_id=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
      $rs = $dtaccess->Execute($sql);
    }
    
    if(StripCurrency($_POST["txtDibayar"][0]) < StripCurrency($_POST["total_harga"])){
      $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
              pembayaran_total = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
              pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0])).", 
              pembayaran_hrs_bayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
              where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
              //echo StripCurrency($_POST["txtDibayar"][0]) ."<". $_POST["total_harga"]."-".$sql; die();
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      
      if($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin >= $_POST["total_biaya"]){
        if($_POST["reg_tipe_jkn"]=="1" || $_POST["reg_tipe_layanan"]=="1"){
          $sql = "update klinik.klinik_pembayaran set pembayaran_subsidi=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_negatif = '0', pembayaran_yg_dibayar='0' 
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        } else {
          $sql = "update klinik.klinik_pembayaran set pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_negatif = '0', pembayaran_yg_dibayar='0' 
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        }
      } elseif($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin <= $_POST["total_biaya"]){
        if($_POST["reg_tipe_jkn"]=="1" || $_POST["reg_tipe_layanan"]=="1"){
          $sql = "update klinik.klinik_pembayaran set pembayaran_subsidi=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                  pembayaran_selisih_positif = '0', pembayaran_yg_dibayar='0', 
                  pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))." 
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        } else {
          $sql = "update klinik.klinik_pembayaran set pembayaran_selisih_positif = '0', 
                  pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))."
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
          $rs = $dtaccess->Execute($sql);
        }
      }
    } else {
      if($_POST["reg_jenis_pasien"]=="5" && $pembayaranDijamin > 0.00 && $_POST["id_poli"]==$_POST["op"]){
				if($pembayaranDijamin > $_POST["total_biaya"] && $_POST["total_harga"]==0){ //selisih positif
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = '0', pembayaran_subsidi = '0', pembayaran_hrs_bayar = '0', 
								    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih))).", 
                    pembayaran_selisih_negatif = '0' where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
						        //echo "naik kelas positif1 ".$sql; die();
				} elseif($pembayaranDijamin > $_POST["total_biaya"] && $_POST["total_harga"]<>0){ //selisih 
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", pembayaran_subsidi = '0', 
                    pembayaran_hrs_bayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih))).", 
                    pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))."
								    where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
						        //echo "naik kelas positif>  ".$sql; die();
				}
				else if($pembayaranDijamin < $_POST["total_biaya"]){ // selisih negatif
						$sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'n', 
                    pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"])).", 
								    pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", pembayaran_subsidi = 0, 
                    pembayaran_hrs_bayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                    pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,abs(StripCurrency($pembayaranSelisih)))." 
                    where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
					//echo "naik kelas negatif <  ".$sql; die();
				}
			} else {
        $sql = "update klinik.klinik_pembayaran set pembayaran_flag = 'y', 
                pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"])).", 
                pembayaran_hrs_bayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
                where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                //echo $sql; die();
      }
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    }
    }
    
    $sql = "update klinik.klinik_pembayaran_uangmuka set uangmuka_tgl_lunas=".QuoteValue(DPE_DATE,date("Y-m-d"))." 
            where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $dtaccess->Execute($sql);
    
    $sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det 
                      where id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
              $rs = $dtaccess->Execute($sql);
              $Maxs = $dtaccess->Fetch($rs);
              $MaksUrut = ($Maxs["total"]+1);
              //$MaksUrut = "1";
                    
            $sql="select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
            $dataPembayaran = $dtaccess->Fetch($sql);
            
            $kurang = $dataPembayaran["pembayaran_total"] - $dataPembayaran["pembayaran_yg_dibayar"];
            $selisih = $dataPembayaran["pembayaran_total"] - $dataPembayaran["pembayaran_dijamin"];
            //if($kurang<0) $kurang=0;
            
            $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
            $rs = $dtaccess->Execute($sql);
            $uangMuka = $dtaccess->Fetch($rs);
            
            $bayar = $dataPembayaran["pembayaran_yg_dibayar"] - $uangMuka["total"];
             
             if($_POST["reg_jenis_pasien"]=="2"){ 
                //insert pembayaran det iur      
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "pembayaran_det_dibayar";
                $dbField[14] = "pembayaran_det_tipe_piutang";
                
               $byrHonorId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
               $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
               // jika ada diskon ee
               if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
               } elseif($uangMuka["total"]>0){
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($bayar));
               } elseif($dataPembayaran["pembayaran_total"]<>$dataPembayaran["pembayaran_yg_dibayar"]) {
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
               } else {
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["bayar"]));
               }
               $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
               $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,"T");
               if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
               } elseif($dataPembayaran["pembayaran_total"]<>$dataPembayaran["pembayaran_yg_dibayar"]) {
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
               } else {
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["bayar"]));
               }
               $dbValue[14] = QuoteValue(DPE_CHAR,'T');
               
               //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
               
               if($dataPembayaran["pembayaran_total"]<>$dataPembayaran["pembayaran_yg_dibayar"]){
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorIdNew = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorIdNew);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+1));
                 // jika ada diskon ee
                  if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                  } else{
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($kurang));
                  }
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,'P');
                 
                 //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                }
              }
              elseif($_POST["reg_jenis_pasien"]=="5"){
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_tipe_jkn";
                $dbField[14] = "pembayaran_det_dibayar";
                $dbField[15] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorId = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d")); 
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut));
                 if($uangMuka){
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($bayar));
                 } else {
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($selisih));
                 }
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 if($dataPembayaran["pembayaran_total"]<$dataPembayaran["pembayaran_dijamin"]){
                 $dbValue[12] = QuoteValue(DPE_CHAR,"S");
                 } else {
                 $dbValue[12] = QuoteValue(DPE_CHAR,"T");
                 }
                 $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_tipe_jkn"]);
                 $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($selisih));
                 $dbValue[15] = QuoteValue(DPE_CHAR,"T");
                 
                  //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                 
                 if($dataPembayaran["pembayaran_dijamin"]>0){
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_tipe_jkn";
                $dbField[14] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorIdNew = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorIdNew);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut+1);
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_dijamin"]+$_POST["inacbg_topup"]));
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_tipe_jkn"]);
                 $dbValue[14] = QuoteValue(DPE_CHAR,"J");
                 
                  //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                }
                 
                if($dataPembayaran["pembayaran_yg_dibayar"]<>$dataPembayaran["pembayaran_hrs_bayar"]){
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_tipe_jkn";
                $dbField[14] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorIdNew = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorIdNew);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d")); 
                 if($dataPembayaran["pembayaran_dijamin"]>0){
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+2));
                 } else {                               
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+1));
                 }
                 // jika ada diskon ee
                  if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                  } else{
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($kurang));
                  }
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_tipe_jkn"]);
                 $dbValue[14] = QuoteValue(DPE_CHAR,"P");
                 
                 //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                } 
              }
              elseif($_POST["reg_jenis_pasien"]=="7"){
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_perusahaan";
                $dbField[14] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorId = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_dijamin"]));
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
                 $dbValue[14] = QuoteValue(DPE_CHAR,"J");
                 
                  //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                 
              }
              elseif($_POST["reg_jenis_pasien"]=="18"){
                $sql = "select * from global.global_jamkesda_kota where jamkesda_kota_id=".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
                $rs = $dtaccess->Execute($sql);
                $dataJamkesda = $dtaccess->Fetch($rs);
                                                                                                      
                $kota = $dataJamkesda["jamkesda_kota_persentase_kota"]/100*$dataPembayaran["pembayaran_dijamin"];
                $prop = $dataJamkesda["jamkesda_kota_persentase_prov"]/100*$dataPembayaran["pembayaran_dijamin"];
                $totDijamin = $kota+$prop;
                
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_jamkesda_kota";
                $dbField[14] = "id_kota";
                $dbField[15] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorId = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($kota));
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
                 $dbValue[14] = QuoteValue(DPE_NUMERIC,$dataJamkesda["id_kota"]);
                 $dbValue[15] = QuoteValue(DPE_CHAR,"J");
                 
                  //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_flag";
                $dbField[13] = "id_jamkesda_prop";
                $dbField[14] = "id_prop";
                $dbField[15] = "pembayaran_det_tipe_piutang";
                
                 $byrHonorIdNew = $dtaccess->GetTransID();
                 $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorIdNew);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                 $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                 $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+1));
                 $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($prop));
                 $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                 $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                 $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                 $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                 $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                 $dbValue[12] = QuoteValue(DPE_CHAR,"P");
                 $dbValue[13] = QuoteValue(DPE_CHAR,$dataJamkesda["id_jamkesda_prop"]);
                 $dbValue[14] = QuoteValue(DPE_NUMERIC,$dataJamkesda["id_prop"]);
                 $dbValue[15] = QuoteValue(DPE_CHAR,"J");
                 
                  //print_r($dbValue); die();
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                 
                 $dtmodel->Insert() or die("insert  error");
                 
                 unset($dbField);
                 unset($dtmodel);
                 unset($dbValue);
                 unset($dbKey);
                 
                 if($kota>$dataJamkesda["maksimal_dijamin"] && $dataJamkesda["maksimal_dijamin"]<>0){
                    $dbTable = "klinik.klinik_pembayaran_det";
                    $dbField[0] = "pembayaran_det_id"; // PK
                    $dbField[1] = "id_pembayaran";
                    $dbField[2] = "pembayaran_det_create";
                    $dbField[3] = "pembayaran_det_tgl";
                    $dbField[4] = "pembayaran_det_ke";
                    $dbField[5] = "pembayaran_det_total";
                    $dbField[6] = "id_dep";
                    $dbField[7] = "pembayaran_det_service_cash";
                    $dbField[8] = "id_dokter";
                    $dbField[9] = "who_when_update";
                    $dbField[10] = "id_jbayar";
                    $dbField[11] = "id_jenis_pasien";
                    $dbField[12] = "pembayaran_det_flag";
                    $dbField[13] = "id_jamkesda_kota";
                    $dbField[14] = "pembayaran_det_dibayar";
                    $dbField[15] = "pembayaran_det_tipe_piutang";
                    
                     $byrHonorIdNew2 = $dtaccess->GetTransID();
                     $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorIdNew2);
                     $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                     $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                     $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                     $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+2));
                     $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($kota-$dataJamkesda["maksimal_dijamin"]));
                     $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                     $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                     $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                     $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                     $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                     $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                     $dbValue[12] = QuoteValue(DPE_CHAR,"T");
                     $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
                     $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($kota-$dataJamkesda["maksimal_dijamin"]));
                     $dbValue[15] = QuoteValue(DPE_CHAR,"T");
                     
                      //print_r($dbValue); die();
                     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                     
                     $dtmodel->Insert() or die("insert  error");
                     
                     unset($dbField);
                     unset($dtmodel);
                     unset($dbValue);
                     unset($dbKey);
                 } 
              }
              else{
                $dbTable = "klinik.klinik_pembayaran_det";
                $dbField[0] = "pembayaran_det_id"; // PK
                $dbField[1] = "id_pembayaran";
                $dbField[2] = "pembayaran_det_create";
                $dbField[3] = "pembayaran_det_tgl";
                $dbField[4] = "pembayaran_det_ke";
                $dbField[5] = "pembayaran_det_total";
                $dbField[6] = "id_dep";
                $dbField[7] = "pembayaran_det_service_cash";
                $dbField[8] = "id_dokter";
                $dbField[9] = "who_when_update";
                $dbField[10] = "id_jbayar";
                $dbField[11] = "id_jenis_pasien";
                $dbField[12] = "pembayaran_det_dibayar";
                $dbField[13] = "pembayaran_det_tipe_piutang";
                $dbField[14] = "pembayaran_det_flag";
                
                     $byrHonorId = $dtaccess->GetTransID();
                     $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
                     $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                     $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                     $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                     $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
                     // jika ada diskon ee
                      if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                     $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                      } elseif($_POST["reg_jenis_pasien"]<>"2" && $_POST["reg_jenis_pasien"]<>"5"){
                     $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_total"]));
                      } elseif($_POST["reg_jenis_pasien"]=="2" && $_POST["reg_jenis_pasien"]=="5"){
                     $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
                      } else {
                     $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
                      }
                     $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                     $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
                     $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
                     $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
                     $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
                     $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
                     if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                     $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                      } elseif($_POST["reg_jenis_pasien"]<>"2" && $_POST["reg_jenis_pasien"]<>"5"){
                     $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_total"]));
                      } elseif($_POST["reg_jenis_pasien"]=="2" && $_POST["reg_jenis_pasien"]=="5"){
                     $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
                      } else {
                     $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaran["pembayaran_yg_dibayar"]));
                      }
                      $dbValue[13] = QuoteValue(DPE_CHAR,"T");
                      $dbValue[14] = QuoteValue(DPE_CHAR,"T");
                     
                      //print_r($dbValue); die();
                     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                     
                     $dtmodel->Insert() or die("insert  error");
                     
                     unset($dbField);
                     unset($dtmodel);
                     unset($dbValue);
                     unset($dbKey);
              }
  
          //insert pelunasan uangmuka
          if($uangMuka){
            $dbTable = "klinik.klinik_pembayaran_uangmuka";
            $dbField[0] = "uangmuka_id";
            $dbField[1] = "id_reg";
            $dbField[2] = "id_pembayaran";
            $dbField[3] = "uangmuka_jml";
            $dbField[4] = "uangmuka_tgl";
            $dbField[5] = "id_jbayar";
            $dbField[6] = "who_update";
            $dbField[7] = "uangmuka_tgl_lunas";
            $dbField[8] = "id_pembayaran_det";
            
            $uangmukaId = $dtaccess->GetTransID();
            $dbValue[0] = QuoteValue(DPE_CHAR,$uangmukaId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
            $dbValue[3] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($uangMuka["total"]));
            $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
            $dbValue[6] = QuoteValue(DPE_CHAR,$userName);
            $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
            
            $dbKey[0] = 0;
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                     
            $dtmodel->Insert() or die("insert  error");
             
            unset($dbField);
            unset($dtmodel);
            unset($dbValue);
            unset($dbKey);
          }
  //}                 
                   
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal "; 
    $sql .= " , fol_diskon_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])); 
    //$sql .= " , fol_pembulatan_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"])); 
    $sql .= " , fol_diskon_persen_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]));
    $sql .= " , fol_total_harga = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
    $sql .= " , id_pembayaran_det = ".QuoteValue(DPE_CHAR,$byrHonorId);
    //$sql .= " , id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    $sql .= " , who_when_update = ".QuoteValue(DPE_CHAR,$userId);
    $sql .= " , fol_dibayar_when = CURRENT_TIMESTAMP where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
            and id_dep=".QuoteValue(DPE_CHAR,$depId)." and fol_lunas='n'";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
     $sql = "select fol_diskon_persen_penjualan,fol_pembulatan_penjualan,fol_diskon_penjualan,fol_total_harga from  klinik.klinik_folio
			       where (fol_jenis like '%T%' or fol_jenis like '%WA%' or fol_jenis like '%R%') 
             and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' and id_dep = ".QuoteValue(DPE_CHAR,$depId); 
		 $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataLaba = $dtaccess->Fetch($rs);
     
     $_POST["diskon"] = $dataLaba["fol_diskon_penjualan"];
     $_POST["diskonpersen"] = $dataLaba["fol_diskon_persen_penjualan"];
     $_POST["total"] = $dataLaba["fol_total_harga"];
     $_POST["pembulatan"] = $dataLaba["fol_pembulatan_penjualan"];  
     
      $sql = "select dep_konf_cetak_kasir from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_cetak_kasir"] = $row_edit["dep_konf_cetak_kasir"]; 
      
      $sql="select * from klinik.klinik_folio a
            join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
            where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
		 $rs = $dtaccess->Execute($sql);
     $dataFolioPas = $dtaccess->FetchAll($rs);
     
     $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
     $total = $dtaccess->Fetch($sql);
     
     // cari isi pembayaran
     $sql="select * from klinik.klinik_pembayaran a
          where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     //echo $sql;
     //die();
     $rs = $dtaccess->Execute($sql);
     $dataPembayaranPas = $dtaccess->Fetch($rs);

     $sql="select * from klinik.klinik_registrasi a
          left join global.global_customer_user b on a.id_cust_usr= b.cust_usr_id
          where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPas = $dtaccess->Fetch($rs);

     if($dataPas["reg_jenis_pasien"]=="5" && !$dataPas["reg_tipe_jkn"]){
     $sql="select reg_tipe_jkn from klinik.klinik_registrasi
          where id_cust_usr = ".QuoteValue(DPE_CHAR,$dataPas["id_cust_usr"])." and id_dep=".QuoteValue(DPE_CHAR,$depId)." 
          and reg_tipe_jkn is not null order by reg_tipe_jkn desc";
     $rs = $dtaccess->Execute($sql);
     $dataRegJkn = $dtaccess->FetchAll($rs);
//echo $sql;
//die();
     $sql="update klinik.klinik_registrasi set reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$dataRegJkn["reg_tipe_jkn"])."
          where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
		 $rs = $dtaccess->Execute($sql);
     }
     
     if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]=="1") $dataPas["reg_tipe_layanan"]="2";
     if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]<>"1") $dataPas["reg_tipe_layanan"]="1";
          
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'PENDPOST-%' 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Pendapatan a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Pendapatan a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'PENDPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'PE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrHonorId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
      
      $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka 
              where uangmuka_jml > 0 and id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
      $dataUM = $dtaccess->Fetch($sql);
   
    if($dataUM["total"]>0){
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'02010201');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataUM["total"]));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020102');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.StripCurrency($dataUM["total"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
        }
        
      $sqlhitungselisih = "select fol_total_harga, pembayaran_dijamin,pembayaran_hrs_bayar from klinik.klinik_folio a
                          left join klinik.klinik_pembayaran b on a.id_pembayaran = b.pembayaran_id
                          where  a.id_pembayaran = '".$_POST["pembayaran_id"]."'";
      $datahitungselisih = $dtaccess->Fetch($sqlhitungselisih); 

      //INSERT PIUTANG DAN SELISIH
       //JIKA JKN dan OP
       if($dataPas["reg_jenis_pasien"]=="5" && $dataPas["id_poli"]==$_POST["op"]){
        if ($dataPembayaranPas["pembayaran_dijamin"] == 0){
          $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit 
                  from klinik.klinik_biaya_prk where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$i]["fol_jenis_pasien"]);
          if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["id_poli"]);         
          $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["biaya_kategori_akuntansi"])." 
                  and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])." 
                  and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
       //echo $sql." perulangan 1 "; 
       //die();
       $dataPrkFolio = $dtaccess->Fetch($sql);
      
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_yg_dibayar"])));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
          
       }
      if ($dataPembayaranPas["pembayaran_dijamin"]>=$dataPembayaranPas["pembayaran_total"] && $dataPembayaranPas["pembayaran_dijamin"] <> 0){ 
        if( $pembayaranSelisih>$pembayaranHrsBayar){
          $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit 
                  from klinik.klinik_biaya_prk where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$i]["fol_jenis_pasien"]);
          if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["id_poli"]);         
          $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["biaya_kategori_akuntansi"])." 
                  and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])." 
                  and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
       //echo $sql; 
       $dataPrkFolio = $dtaccess->Fetch($sql);
         //echo " perulangan 44 "; 
       //die()
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010103010601');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_dijamin"]+$_POST["topup"])));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
      
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
          $beda = $_POST["total_harga"]-StripCurrency($_POST["txtDibayar"][0]);
    
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040310');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'0101030105');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040311');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPembayaranPas["pembayaran_selisih_positif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
  } else {
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040310');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaranPas["pembayaran_selisih_negatif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040311');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPembayaranPas["pembayaran_selisih_positif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
    }      
  } else {
            $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit, id_prk_tambahan_debet, id_prk_tambahan_kredit 
                    from klinik.klinik_biaya_prk where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$i]["fol_jenis_pasien"]);
          if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["id_poli"]);         
          $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["biaya_kategori_akuntansi"])." 
                    and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])." 
                    and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
       //echo $sql; 
       $dataPrkFolio = $dtaccess->Fetch($sql);
       //echo $sql." perulangan 2 "; 
       //die();
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010103010601');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_dijamin"]+$_POST["inacbg_topup"])));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040311');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPembayaranPas["pembayaran_selisih_positif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
       }
     }
      else if($dataPembayaranPas["pembayaran_dijamin"]<$dataPembayaranPas["pembayaran_total"] && $dataPembayaranPas["pembayaran_dijamin"] <> 0 && $_POST["total_harga"] <> 0){
         $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit, id_prk_tambahan_debet, id_prk_tambahan_kredit 
                from klinik.klinik_biaya_prk where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$i]["fol_jenis_pasien"]);
          if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["id_poli"]);         
          $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["biaya_kategori_akuntansi"])." 
                and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])." 
                and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
       //echo $sql; 
       $dataPrkFolio = $dtaccess->Fetch($sql);
         //echo " perulangan 3 "; 
       //die()
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010103010601');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_dijamin"]+$_POST["inacbg_topup"])));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
      
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
          $beda = $_POST["total_harga"]-StripCurrency($_POST["txtDibayar"][0]);
    
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040310');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'0101030105');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040311');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPembayaranPas["pembayaran_selisih_positif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
  } else {
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040310');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaranPas["pembayaran_selisih_negatif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'040311');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPembayaranPas["pembayaran_selisih_positif"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
      }
    }    
  }

//piutang Umum
if($dataPas["reg_jenis_pasien"]=="2") {
  if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=2;
  if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
  
  if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
    $beda = $_POST["total_harga"]-StripCurrency($_POST["txtDibayar"][0]);
    
    $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0]));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'0101030105');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
  } else {
  $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaranPas["pembayaran_yg_dibayar"]));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
   }
}

 // Piutang JAMKESDA
if($dataPas["reg_jenis_pasien"]=="18") {

        if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=18;
        $sql = "select * from global.global_jamkesda_kota where jamkesda_kota_id = ".QuoteValue(DPE_CHAR,$dataPas["id_jamkesda_kota"]);
        $dataJamkesdaProp = $dtaccess->Fetch($sql);
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[0]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["id_jamkesda_kota"])." 
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"])." 
                      and id_prop = ".QuoteValue(DPE_NUMERIC,$dataJamkesdaProp["id_prop"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
 
// Hitung Total piutang Jamkesda Kota & Propinsi
$sql = "select sum(fol_dijamin2) as total_kota, sum(fol_dijamin1) as total_prop from klinik.klinik_folio
        where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
$dataPiutangJamkesda = $dtaccess->Fetch($sql);         
 // Piutang Jamkesda Kota           
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPiutangJamkesda["total_kota"]));
//        echo"rrrrr";  print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
  //Piutang Jamkesda Propinsi
if($dataJamkesdaProp["id_prop"]==13){                   

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPiutangJamkesda["total_prop"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

       }else{

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPiutangJamkesda["total_prop"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
       
       }

       $dataPiutangJamkesdaTotal =  $dataPiutangJamkesda["total_kota"] + $dataPiutangJamkesda["total_prop"];
       $selisihBayarJamkesda =  $dataPembayaranPas["pembayaran_total"] - $dataPiutangJamkesdaTotal;
// Pembayaran IUR PASIEN JAMKESDA
if($dataPiutangJamkesdaTotal <  $dataPembayaranPas["pembayaran_total"]){       

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($selisihBayarJamkesda));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

}
        
          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
} 
 // Piutang IKS
if($dataPas["reg_jenis_pasien"]=="7") {

        if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=7;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$i]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$i]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["id_perusahaan"])." 
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
 
// Hitung Total piutang IKS
$sql = "select sum(fol_dijamin) as total from klinik.klinik_folio
        where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
$dataPiutangIks = $dtaccess->Fetch($sql);         
 // Piutang IKS         
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPiutangIks["total"]));
//        echo"rrrrr";  print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

$selisihBayarIks =  $dataPembayaranPas["pembayaran_total"] - $dataPiutangIks["total"];
// Pembayaran IUR PASIEN IKS
if($dataPiutangIks["total"] <  $dataPembayaranPas["pembayaran_total"]){       

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($selisihBayarIks));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

}

          if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]){
            $dbTable = "gl.gl_buffer_transaksidetil";
          
            $dbField[0]  = "id_trad";   // PK
            $dbField[1]  = "tra_id";
            $dbField[2]  = "prk_id";
            $dbField[3]  = "ket_trad";
            $dbField[4]  = "job_id";
            $dbField[5]  = "dept_id";
            $dbField[6]  = "jumlah_trad";
  
            $transaksiDetailId = $dtaccess->GetTransId();
            
            $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
            $dbValue[2] = QuoteValue(DPE_CHAR,'05011010');
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(($dataPembayaranPas["pembayaran_diskon"])));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
} 
 // Piutang PKMS SILVER
if($dataPas["reg_jenis_pasien"]=="22") {

        if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=22;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[0]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["biaya_kategori_akuntansi"])."
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
 
         
 // Piutang PKMS Silver         
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,2000000);
//        echo"rrrrr";  print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

$selisihBayarPkms =  $dataPembayaranPas["pembayaran_total"] - 2000000;
// Pembayaran IUR PASIEN PKMS
if($selisihBayarPkms <=  $dataPembayaranPas["pembayaran_total"]){       

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($selisihBayarPkms));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

}
} 

 // Piutang PKMS Gold
if($dataPas["reg_jenis_pasien"]==$_POST["op"]) {

        if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=$_POST["op"];
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[0]["fol_jenis_pasien"]);
          if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["id_poli"]);         
          $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[0]["biaya_kategori_akuntansi"])."
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
 
         
 // Piutang PKMS Silver         
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_debet"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,5000000);
//        echo"rrrrr";  print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

$selisihBayarPkms =  $dataPembayaranPas["pembayaran_total"] - 5000000;
// Pembayaran IUR PASIEN PKMS
if($selisihBayarPkms <=  $dataPembayaranPas["pembayaran_total"]){       

      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($selisihBayarPkms));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 

}
} 
       
     for($m=0,$n=count($dataFolioPas);$m<$n;$m++){
// Pendapatan JKN PBI
if($dataPas["reg_jenis_pasien"]=="5" && $dataPas["reg_tipe_jkn"]=="1" ) {
        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=5;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])."and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
                   
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);

    } 

    // Pendapatan JKN Non PBI
if($dataPas["reg_jenis_pasien"]=="5" && $dataPas["reg_tipe_jkn"]=="2" ) {

        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=5;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"])."and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
            
                   
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
    }

// Pendapatan Jamkesda
if($dataPas["reg_jenis_pasien"]=="18") {
        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=18;

        $sql = "select * from global.global_jamkesda_kota where jamkesda_kota_id = ".QuoteValue(DPE_CHAR,$dataPas["id_jamkesda_kota"]);
        $dataJamkesdaProp = $dtaccess->Fetch($sql);
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["id_jamkesda_kota"])." 
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"])." 
                      and id_prop = ".QuoteValue(DPE_NUMERIC,$dataJamkesdaProp["id_prop"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);

          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
    }

    // Pendapatan IKS
    if($dataPas["reg_jenis_pasien"]=="7") {
        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=7;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
                      and id_detail = ".QuoteValue(DPE_CHAR,$dataPas["id_perusahaan"])." 
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);

          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
    }

// Pendapatan PKMS Silver
    if($dataPas["reg_jenis_pasien"]=="22") {
        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=22;
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])."
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);

          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
    }

    // Pendapatan PKMS GOLD
    if($dataPas["reg_jenis_pasien"]==$_POST["op"]) {
        if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=$_POST["op"];
        
              $sql = "select id_prk_piutang_debet, id_prk_piutang_kredit,id_prk_tambahan_debet, id_prk_tambahan_kredit from klinik.klinik_biaya_prk
                      where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
              if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
              $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
                      and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]) ;
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);

          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_piutang_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
    }

//     echo $i;
// Posting Pendapatan dan bank Pasien UMUM
     if($dataPas["reg_jenis_pasien"]=="2") {
      if(!$dataFolioPas[$m]["fol_jenis_pasien"]) $dataFolioPas[$m]["fol_jenis_pasien"]=2;
      if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
           
    $sql = "select id_prk_lunas_debet, id_prk_lunas_kredit from klinik.klinik_biaya_prk
            where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$dataFolioPas[$m]["fol_jenis_pasien"]);
    if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
    $sql .= " and id_kategori_tindakan = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["biaya_kategori_akuntansi"])." 
            and id_tipe_layanan = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_layanan"]);

   $dataPrkFolio = $dtaccess->Fetch($sql);
            
          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_lunas_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
  //        print_r($dbValue);    die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
        
        
    } 
             
     }      $cetak = "y";
//     if($_POST["dep_konf_cetak_kasir"]=='n'){
//     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];
//     }else{
//     $next = "kasir_pemeriksaan_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];
//     }
//     header("location:".$next);
//     exit();
     
  }

	// ----- update data ----- //
	if ($_POST["btnTidak"]) {	
  	
    // update registrasi // 
		$sql = "update  klinik.klinik_registrasi set reg_status='E0',reg_bayar='n', 
    reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' ,
    id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
    id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]).",
    reg_jenis_pasien = 15 where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
    " and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql);
    
    // cari dokter e //
    $sql = "select usr_name from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    $rs = $dtaccess->Execute($sql);
    $Doktere = $dtaccess->Fetch($rs);    
    
    // update data pembayaran //
    
    $sql =  "update klinik.klinik_pembayaran set pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$Doktere["usr_name"])." , 
             pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d"))." , pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s"))." , 
             pembayaran_flag = 'n' where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
                    
                   
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal "; 
    $sql .= " , fol_diskon_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])); 
    $sql .= " , fol_diskon_persen_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]));
    $sql .= " , fol_total_harga = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
    $sql .= " , who_when_update = ".QuoteValue(DPE_CHAR,$userId);
    $sql .= " , fol_dibayar_when =  CURRENT_TIMESTAMP where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

    $next = "kasir_pemeriksaan_view.php";

     header("location:".$next);
     exit();
    
  }

	
   if ($_GET["del"] || $_GET["id_register"] || $_GET["id_biaya"] || $_GET["id_pembayaran"]) { 
           $folId = $_GET["id"];
           
           // hapus tindakan di klinikk perawatan tindakan --
           $sql = "select id_biaya from klinik.klinik_folio where fol_id =".QuoteValue(DPE_CHAR,$folId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
           $dataFolio = $dtaccess->Fetch($rs);
           
           $sql_perawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_register"])." 
                          and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $dataRawat= $dtaccess->Fetch($sql_perawat);	
 
           $sql = "select * from klinik.klinik_perawatan_tindakan where id_rawat=".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"])." 
                    and id_tindakan =".QuoteValue(DPE_CHAR,$dataFolio["id_biaya"]);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
           $dataTindakan = $dtaccess->FetchAll($rs);
           
           for($i=0,$n=count($dataTindakan);$i<$n;$i++) {      
           $sql = "delete from  klinik.klinik_perawatan_tindakan where rawat_tindakan_id = ".QuoteValue(DPE_CHAR,$dataTindakan[$i]["rawat_tindakan_id"]);
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
           }


           // hapus biaya tindakan di klinik folio nya --
           $sql = "delete from  klinik.klinik_folio where fol_id = ".QuoteValue(DPE_CHAR,$folId)." and id_dep = ".QuoteValue(DPE_CHAR,$depId);
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
            
           // hapus biaya tindakan di klinik folio splitnya -- 
           $sql = "select * from klinik.klinik_folio_split where id_fol = ".QuoteValue(DPE_CHAR,$folId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
           $dataSplitFolio = $dtaccess->FetchAll($rs);
           
           for($i=0,$n=count($dataSplitFolio);$i<$n;$i++) {  
           $sql = "delete from  klinik.klinik_folio_split where folsplit_id = ".QuoteValue(DPE_CHAR,$dataSplitFolio[$i]["folsplit_id"]);
           $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);   
           }
       
          // kembali ke atas      
          $kembali = "kasir_pemeriksaan_proses.php?id_reg=".$_GET["id_register"]."&pembayaran_id=".$_GET["id_pembayaran"];
          header("location:".$kembali);
          exit();    
     } 
     
       // buat ambil tindakan --
     	 $sql = "select * from klinik.klinik_biaya where biaya_jenis = 'TA' and 
               id_dep =".QuoteValue(DPE_CHAR,$depId)." order by biaya_nama";
		   $datatindakan= $dtaccess->FetchAll($sql);
       
       // buat nyari user Dokter ee --
     	 $sql = "select id_pgw from klinik.klinik_honor_dokter where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and honor_flag = 'RS' and id_dep =".QuoteValue(DPE_CHAR,$depId);
		   $dataUserDok = $dtaccess->Fetch($sql); 
       
       $sql = "select id_poli from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		   $dataPoliReg = $dtaccess->Fetch($sql); 
		   
		   // buat ambil jenis bayar --
     	 $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' and jbayar_id = '01' order by jbayar_id asc";
		   $dataJenisBayar= $dtaccess->FetchAll($sql); 
          //echo $sql;
       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
		   $dataJenisBayar2= $dtaccess->FetchAll($sql);              
        
       // cari jenis bayar ee //
       $sql = "select count(jbayar_id) as total from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' or jbayar_id = '01'";
		   $jsBayar= $dtaccess->Fetch($sql);
       
       $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_reg"] = $row_edit["dep_konf_reg"];
      $_POST["dep_konf_kons"] = $row_edit["dep_konf_kons"];  
		   
		  if($_POST["dep_konf_kons"]=='y' && $_POST["dep_konf_reg"]=='y'){ 
        $sql = "select * from  klinik.klinik_kategori_tindakan where id_dep=".QuoteValue(DPE_CHAR,$depId)." order by kategori_urut"; 
  		 $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
       $dataKategori = $dtaccess->FetchAll($rs_edit);    
      }else{
        $sql = "select * from  klinik.klinik_kategori_tindakan where id_dep=".QuoteValue(DPE_CHAR,$depId)." and kategori_tindakan_id>'1' order by kategori_urut"; 
  		 $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
       $dataKategori = $dtaccess->FetchAll($rs_edit);  
      }                      
      
      
     $sql = "select * from global.global_auth_user where (id_rol = '6' or id_rol='2') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTipeLayanan = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJenis = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_perusahaan order by perusahaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPerusahaan = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_jkn order by jkn_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJkn = $dtaccess->FetchAll($rs);
                      
?>

<?php echo $view->RenderBody("module.css",true,true,"KASIR PEMBAYARAN TUNAI"); ?>
<br /><br /><br /><br />                                                                                                                                     

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>     
<script type="text/javascript" src="ajax.js"></script>
<script src="<?php echo $ROOT;?>lib/script/selecttindakan1.js"></script>
<script type="text/javascript">

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan membatalkan tindakan ini???'));
  else return false;
}

function CekTindakan(frm) {

    if(!frm.id_tindakan_0.value){
		alert('Pilih dahulu Tindakan yang akan dimasukkan');
		frm.id_tindakan_0.focus();
          return false;
	}

     	return true;      
}

function CekData()
{                              
//    if(!document.getElementById('txtcek').value || document.getElementById('txtcek').value =='0')
//    {
//      alert('Belum dibayar');
//      document.getElementById('txtcek').focus();
//      return false;
//    }    

/*
    if(document.getElementById('id_dokter').value == '--')
    {
      alert('Maaf Dokter Harus Dipilih');
      document.getElementById('id_dokter').focus();
      return false;
    }  */
         
    <?php //if($_POST["reg_jenis_pasien"]=="5"){ ?>
     /* if(document.getElementById('txtBack').value > '0')
      {
        alert('Maaf uang anda kurang');
        document.getElementById('txtBack').focus();
        return false;
      }*/
    <?php //} ?>
    
    if(document.getElementById('reg_jenis_pasien').value=='5'){ 
      if(document.getElementById('cust_usr_jkn').value=='--')
      {
        alert('Maaf Tipe JKN harus diisi, silahkan hubungi loket untuk memperbaikinya');
        document.getElementById('cust_usr_jkn').focus();
        return false;
      }
    }
    
    /*if(document.getElementById('reg_jenis_pasien').value=='5'){ 
      <?php if($_POST["inacbg_appv"]=='n' || $_POST["inacbg_appv"]=='') {?>
        alert('Maaf hasil bridging belum di approve');
        document.getElementById('reg_jenis_pasien').focus();
        return false;
      <?php } ?>
    }*/
    
    return true;
}

function GantiKembalian(dibayar) {
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     dibayar_format = dibayar.toString().replace(/\,/g,"");
     document.getElementById('txtDibayar').value = formatCurrency(dibayar_format);
     dibayar_format_int=dibayar_format*1;
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     totalnyaInt=totalnya*1;        
     
     document.getElementById('txtKembalian').value = formatCurrency(dibayar_format_int-totalnyaInt);
     document.getElementById('btnBayar').focus();
}

var grandTotal = '<?php echo $grandTotalHarga;?>';
function GantiPengurangan(terima,urut) {
     var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtDibayar'+urut).value.toString().replace(/\,/g,"");
     var byr_urt = document.getElementById('byr'+urut+'_int').value.toString().replace(/\,/g,"");
     var aslinya = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     dibayar_int=bayaren*1;
     totalnyaInt=totalnya*1;
     asliInt=aslinya*1;        
     
     // pembayaran pertama     
     if(terima && urut=='0') {     
     
     var byr1 = terima;

	   var id_awal = byr1.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     } 
     
     byr0_int=hasil*1;	  
     
     if(byr0_int) {
         document.getElementById('byr0_int').value = byr0_int;
         } else {
         document.getElementById('byr0_int').value = 0;
         }
     
     var cekz = '<?php echo $jsBayar["total"];?>'     
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }

     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot1_int = dibayar_int-hasilzInt;
     
         document.getElementById('txtIsi').innerHTML = formatCurrency(tot1_int);
         document.getElementById('txtBack').value = formatCurrency(tot1_int);
         document.getElementById('txtcek').value = formatCurrency(hasilzInt);
          
     
     }  
     
     //pembayaran kedua
     if(terima && urut=='1') {     

     var byr2 = terima;
	   var id_awal = byr2.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr1_int=hasil*1;
       if(byr1_int) {
       document.getElementById('byr1_int').value = byr1_int;
       } else {
       document.getElementById('byr1_int').value = 0;
       }       
           
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     //tot2_int = (byr1_int)+dibayar_int;
     tot2_int = dibayar_int-hasilzInt;
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot2_int);
     document.getElementById('txtBack').value = formatCurrency(tot2_int);             
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     } 
     
     //pembayaran ketiga
     if(terima && urut=='2') {     

     var byr3 = terima;
	   var id_awal = byr3.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr2_int=hasil*1;     
       if(byr2_int) {
       document.getElementById('byr2_int').value = byr2_int;
       } else {
       document.getElementById('byr2_int').value = 0;
       }
       
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
          
     tot3_int = dibayar_int-hasilzInt;
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot3_int);
     document.getElementById('txtBack').value = formatCurrency(tot3_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot3_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     } 
     
     // pembayaran ke-empat
     if(terima && urut=='3') {     

     var byr4 = terima;
	   var id_awal = byr4.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr3_int=hasil*1;
    if(byr3_int) {
       document.getElementById('byr3_int').value = byr3_int;
       } else {
       document.getElementById('byr3_int').value = 0;     
       }
     
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
          
     tot4_int = dibayar_int-hasilzInt;
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot4_int);
     document.getElementById('txtBack').value = formatCurrency(tot4_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot4_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);     
     }  
     
     // pembayaran ke-lima
     if(terima && urut=='4') {     

     var byr5 = terima;
	   var id_awal = byr5.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
         
     byr4_int=hasil*1;

       if(byr4_int) {
       document.getElementById('byr4_int').value = byr4_int;
       } else {
       document.getElementById('byr4_int').value = 0;
       }
       
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot5_int = dibayar_int-hasilzInt;
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot5_int);
     document.getElementById('txtBack').value = formatCurrency(tot5_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot5_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }  
     
     // pembayaran ke-enam
     if(terima && urut=='5') {     

     var byr6 = terima;
	   var id_awal = byr6.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr5_int=hasil*1;
     
       if(byr5_int) {
       document.getElementById('byr5_int').value = byr5_int;
       } else {
       document.getElementById('byr5_int').value = 0;
       }
 
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot6_int = dibayar_int-hasilzInt;
     
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot6_int);
     document.getElementById('txtBack').value = formatCurrency(tot6_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot6_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }
     
     //pembayaran ke-tujuh
     if(terima && urut=='6') {     

     var byr7 = terima;
	   var id_awal = byr7.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr6_int=hasil*1;
     
       if(byr6_int) {
       document.getElementById('byr6_int').value = byr6_int;
       } else {
       document.getElementById('byr6_int').value = 0;
       }
 
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot7_int = dibayar_int-hasilzInt;
     
     
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot7_int);
     document.getElementById('txtBack').value = formatCurrency(tot7_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot6_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }
     
     
     //pembayaran ke-delapan
     if(terima && urut=='7') {     

     var byr8 = terima;
	   var id_awal = byr8.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr7_int=hasil*1;
     
       if(byr7_int) {
       document.getElementById('byr7_int').value = byr7_int;
       } else {
       document.getElementById('byr7_int').value = 0;
       }
 
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot8_int = dibayar_int-hasilzInt;
          
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot8_int);
     document.getElementById('txtBack').value = formatCurrency(tot8_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot6_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }
     
     
     //pembayaran ke-sembilan
     if(terima && urut=='8') {     

     var byr9 = terima;
	   var id_awal = byr9.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr8_int=hasil*1;
     
       if(byr8_int) {
       document.getElementById('byr8_int').value = byr8_int;
       } else {
       document.getElementById('byr8_int').value = 0;
       }
 
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot9_int = dibayar_int-hasilzInt;
          
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot9_int);
     document.getElementById('txtBack').value = formatCurrency(tot9_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot6_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }


     //pembayaran ke-sepuluh
     if(terima && urut=='9') {     

     var byr10 = terima;
	   var id_awal = byr10.split(',');     
     if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3] && id_awal[4]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3]+''+id_awal[4];
     } else if(id_awal[0] && id_awal[1] && id_awal[2] && id_awal[3]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2]+''+id_awal[3];
     } else if(id_awal[0] && id_awal[1] && id_awal[2]) {
     var hasil = id_awal[0]+''+id_awal[1]+''+id_awal[2];
     } else if(id_awal[0] && id_awal[1]) {
     var hasil = id_awal[0]+''+id_awal[1];
     } else if(id_awal[0]) {
     var hasil = id_awal[0];
     }
     
     byr9_int=hasil*1;
     
       if(byr9_int) {
       document.getElementById('byr9_int').value = byr9_int;
       } else {
       document.getElementById('byr9_int').value = 0;
       }
 
     var cekz = '<?php echo $jsBayar["total"];?>'
     if(cekz=='10') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
       var ti_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
     } else if(cekz=='9') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
       var ti_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
            
     } else if(cekz=='8') { 
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       var ti_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");       
     
     } else if(cekz=='7') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       var ti_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='6') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       var ti_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
       
     } else if(cekz=='5') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       var ti_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='4') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       var ti_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='3') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       var ti_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
       
     } else if(cekz=='2') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
       var ti_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
       
     } else if(cekz=='1') {
       var ti_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     }
           
     tisatuInt=ti_0*1;
     tiduaInt=ti_1*1;
     titigaInt=ti_2*1;
     tiempatInt=ti_3*1;
     tilimaInt=ti_4*1;
     tienamInt=ti_5*1;                                  
     titujuhInt=ti_6*1;
     tidelapanInt=ti_7*1;
     tisembilanInt=ti_8*1;
     tisepuluhInt=ti_9*1;  
     
     if(cekz=='10') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt+tisepuluhInt;
     } else if(cekz=='9') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt+tisembilanInt;
     } else if(cekz=='8') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt+tidelapanInt;
     } else if(cekz=='7') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt+titujuhInt;
     } else if(cekz=='6') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt+tienamInt;
     } else if(cekz=='5') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt+tilimaInt;
     } else if(cekz=='4') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt+tiempatInt;
     } else if(cekz=='3') {
     hasilzInt=tisatuInt+tiduaInt+titigaInt;
     } else if(cekz=='2') {
     hasilzInt=tisatuInt+tiduaInt;
     } else if(cekz=='1') {
     hasilzInt=tisatuInt;
     }
     
     tot10_int = dibayar_int-hasilzInt;
          
     document.getElementById('txtIsi').innerHTML = formatCurrency(tot10_int);
     document.getElementById('txtBack').value = formatCurrency(tot10_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(tot6_int);
     document.getElementById('txtcek').value = formatCurrency(hasilzInt);
     }
     
     
     // jika pembayarannya kosong //
     if(terima=='0') {
     
     var cek = '<?php echo $jsBayar["total"];?>'         
     var tindakan_0 = document.getElementById('byr0_int').value.toString().replace(/\,/g,"");
     var tindakan_1 = document.getElementById('byr1_int').value.toString().replace(/\,/g,"");     
     var tindakan_2 = document.getElementById('byr2_int').value.toString().replace(/\,/g,"");    
     var tindakan_3 = document.getElementById('byr3_int').value.toString().replace(/\,/g,"");    
     var tindakan_4 = document.getElementById('byr4_int').value.toString().replace(/\,/g,"");     
     var tindakan_5 = document.getElementById('byr5_int').value.toString().replace(/\,/g,"");
     var tindakan_6 = document.getElementById('byr6_int').value.toString().replace(/\,/g,"");
     var tindakan_7 = document.getElementById('byr7_int').value.toString().replace(/\,/g,"");
     var tindakan_8 = document.getElementById('byr8_int').value.toString().replace(/\,/g,"");
     var tindakan_9 = document.getElementById('byr9_int').value.toString().replace(/\,/g,"");
     
  
     tindsatuInt=tindakan_0*1;
     tindduaInt=tindakan_1*1;
     tindtigaInt=tindakan_2*1;
     tindempatInt=tindakan_3*1;
     tindlimaInt=tindakan_4*1;
     tindenamInt=tindakan_5*1;                                  
     tindtujuhInt=tindakan_6*1;
     tinddelapanInt=tindakan_7*1;
     tindsembilanInt=tindakan_8*1;
     tindsepuluhInt=tindakan_9*1;
     

     if(cek=='10') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt+tinddelapanInt+tindsembilanInt+tindsepuluhInt;
     } else if(cek=='9') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt+tinddelapanInt+tindsembilanInt;
     } else if(cek=='8') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt+tinddelapanInt;
     } else if(cek=='7') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt+tindtujuhInt;
     } else if(cek=='6') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt+tindenamInt;
     } else if(cek=='5') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt+tindlimaInt;
     } else if(cek=='4') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt+tindempatInt;
     } else if(cek=='3') {
     sisaInt=tindsatuInt+tindduaInt+tindtigaInt;
     } else if(cek=='2') {
     sisaInt=tindsatuInt+tindduaInt;
     } else if(cek=='1') {
     sisaInt=tindsatuIntt;
     }
         
         document.getElementById('txtIsi').innerHTML = formatCurrency(sisaInt-asliInt);
         document.getElementById('txtBack').value = formatCurrency(sisaInt-asliInt); 
         //document.getElementById('txtTotalDibayar').value = formatCurrency(sisaInt-asliInt);
         document.getElementById('txtcek').value = formatCurrency(sisaInt);
                         
     } 
     
           
     //document.getElementById('txtIsi').innerHTML = formatCurrency(totalnyaInt-dibayar_int);
     //document.getElementById('txtBack').value = formatCurrency(totalnyaInt-dibayar_int);
     //document.getElementById('txtTotalDibayar').value = formatCurrency(dibayar_int-totalnyaInt);
     //document.getElementById('txtIsi').focus();
}










function GantiDiskon(diskon,total) {
     var dibayar = document.getElementById('txtDibayar0').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var diskon_harga = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var biayaRacikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"");
     var biayaBhps = document.getElementById('txtBiayaBhps').value.toString().replace(/\,/g,""); 
     var biayaResep = document.getElementById('txtBiayaResep').value.toString().replace(/\,/g,"");
     var biayaPembulatan = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"");

     dibayarInt = dibayar*1; 
     pajakInt = pajak*1; 
     totalInt = total*1;
     biayaRacikanInt = biayaRacikan*1;
     biayaResepInt = biayaResep*1;
     biayaBhpsInt = biayaBhps*1;
     biayaPembulatanInt = biayaPembulatan*1;
     diskon_format = diskon_harga*1;
     diskonpersen = (diskon_harga*1)*100/(total_bayar*1);
     totalBiayaTambahan = biayaRacikanInt+biayaResepInt+biayaBhpsInt; // total biaya Tambahan
     
     //alert(total_bayar);
     if(document.getElementById('txtDiskon').value){
     //document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskonpersen);
     document.getElementById('txtTotalDibayar').value = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtIsi').innerHTML = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format)));
     document.getElementById('txtServiceCash').value = formatCurrency('0');
     }  else {
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskon_format);
     document.getElementById('txtTotalDibayar').value = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtIsi').innerHTML = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format)));
     document.getElementById('txtServiceCash').value = formatCurrency('0');
     } 
     document.getElementById('txtDibayar').focus();
     
}

function Diskon(diskon,total) {     
     var diskonpersen = document.getElementById('txtDiskonPersen').value.toString().replace(/\,/g,"");
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var dibayar = document.getElementById('txtDibayar0').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var diskon_harga = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     
     dibayarInt = dibayar*1; 
     pajakInt = pajak*1; 
     totalInt = total*1;
     diskon_format = diskon_harga*1;  
     diskon_persen = (diskonpersen*1)/100*(total_bayar*1);
     
    if(document.getElementById('txtDiskonPersen').value)
    {
      document.getElementById('txtDiskon').value = formatCurrency(diskon_persen);
      document.getElementById('txtTotalDibayar').value = formatCurrency(totalInt+(pajakInt-diskon_persen));
      document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-(totalInt+(pajakInt-diskon_persen)));
      document.getElementById('txtIsi').innerHTML = formatCurrency(totalInt+(pajakInt-diskon_persen));
      document.getElementById('txtServiceCash').value = formatCurrency('0');
      
    }else{
      document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
      document.getElementById('txtTotalDibayar').value = formatCurrency(totalInt+(pajakInt-diskon_format));
      document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-(totalInt+(pajakInt-diskon_format)));
      document.getElementById('txtIsi').innerHTML = formatCurrency(totalInt+(pajakInt-diskon_persen));
      document.getElementById('txtServiceCash').value = formatCurrency('0');
      
    }
    
     document.getElementById('txtDibayar').focus();
}

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
//     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];

<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?'))
       <?php if(($_POST["reg_jenis_pasien"]=='5' and ($_POST["reg_tipe_jkn"]=='1' or $_POST["reg_tipe_layanan"]=='1') && $_POST["id_poli"]<>$_POST["op"]) or $_POST["reg_jenis_pasien"]=='7' or $_POST["reg_jenis_pasien"]=='18'){ ?>
       BukaWindow('kasir_dot_cetak_sementara.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
       document.location.href='<?php echo $thisPage;?>';
       <?php } else if($_POST["id_poli"]==$_POST["op"]){ ?>
       BukaWindow('kasir_pemeriksaan_dot_kurang_cetak.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
       document.location.href='<?php echo $thisPage;?>';
       <?php } else { ?>
       BukaWindow('cetak_kwitansi.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>&pembayaran_det_id=<?php echo $byrHonorId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi'); 
       //BukaWindow('kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
	     document.location.href='<?php echo $thisPage;?>';
       <?php } ?>
   
<?php } ?>


</script>
<!--<body onLoad="GantiPengurangan('<?php echo $grandTotalHarga;?>',0)";>-->
<body>


<div id="body">
<div id="scroller">

<?php if($dataPasien) {  ?>
<form name="frmEdit" method="POST" autocomplete="off" action="<?php echo $_SERVER["PHP_SELF"]?>" >
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td width="100%">
     <fieldset>                                                     
     <legend><strong>Data Pasien</strong></legend>
      <div id="kasir">
      <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18') { ?>
                <td width= "5%" align="center" class="tablecontent" rowspan="9"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                <?php } elseif($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500' && $dataPasien["reg_jenis_pasien"]=='2'){ ?>
                <td width= "5%" align="center" class="tablecontent" rowspan="6"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                <?php } else {?>
               <td width= "5%" align="center" class="tablecontent" rowspan="8"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               <?php } ?>
               <td width= "15%" align="left" class="tablecontent">No. RM</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
               <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18') { ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="6"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } elseif($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500' && $dataPasien["reg_jenis_pasien"]=='2'){ ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="3"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } else {?>
               <td width= "40%" align="center" class="tablecontent" rowspan="5"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } ?>
          </tr>
          <?php if($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500') { ?>	
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["fol_keterangan"]; ?></label></td>
          </tr>
          <?php } else { ?>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Alamat</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <?php } ?>
          <!--<tr>
               <td width= "15%" align="left" class="tablecontent">Jenis Bayar</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2">
               <select name="jbayar" id="jbayar" onKeyDown="return tabOnEnter(this, event);">
                <option value="" >[ Pilih Jenis Bayar ]</option>
                <?php for($i=0,$n=count($jsBayar);$i<$n;$i++){ ?>
                <option value="<?php echo $jsBayar[$i]["jbayar_id"];?>" <?php if($jsBayar[$i]["jbayar_id"]==$_POST["jbayar"]) echo "selected"; ?>><?php echo $jsBayar[$i]["jbayar_nama"];?></option>
				        <?php } ?>    
          			</select>
                </td>
          </tr>-->
          <?php if(!$dataPasien["id_cust_usr"]=='100' || !$dataPasien["id_cust_usr"]=='500') {?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Sudah Terima Dari</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                <input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                &nbsp;&nbsp;&nbsp;
                </td>
          </tr>
          <?php } ?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Cara Bayar</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="reg_jenis_pasien" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Cara Bayar ]</option>			
				              <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>
          <?php if($_POST["reg_jenis_pasien"]=='18'){?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Nama Kota</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="id_jamkesda_kota" disabled id="id_jamkesda_kota" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Kota ]</option>			
				              <?php for($i=0,$n=count($dataKota);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataKota[$i]["jamkesda_kota_id"];?>" <?php if($_POST["id_jamkesda_kota"]==$dataKota[$i]["jamkesda_kota_id"]) echo "selected"; ?>><?php echo $dataKota[$i]["jamkesda_kota_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>
          <?php } ?>
          <?php if($_POST["reg_jenis_pasien"]=='7'){?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Nama Perusahaan</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="id_perusahaan" disabled id="id_perusahaan" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Perusahaan ]</option>			
				              <?php for($i=0,$n=count($dataPerusahaan);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataPerusahaan[$i]["perusahaan_id"];?>" <?php if($_POST["id_perusahaan"]==$dataPerusahaan[$i]["perusahaan_id"]) echo "selected"; ?>><?php echo $dataPerusahaan[$i]["perusahaan_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>
          <?php } ?>
          <?php if($_POST["reg_jenis_pasien"]=='5'){?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Nama Kategori JKN</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="cust_usr_jkn" disabled id="cust_usr_jkn" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Kategori JKN ]</option>			
				              <?php for($i=0,$n=count($dataJkn);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataJkn[$i]["jkn_id"];?>" <?php if($_POST["cust_usr_jkn"]==$dataJkn[$i]["jkn_id"]) echo "selected"; ?>><?php echo $dataJkn[$i]["jkn_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>
          <?php } ?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Tipe Layanan</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="reg_tipe_layanan" disabled id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Tipe Layanan ]</option>			
				              <?php for($i=0,$n=count($dataTipeLayanan);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataTipeLayanan[$i]["tipe_biaya_id"];?>" <?php if($_POST["reg_tipe_layanan"]==$dataTipeLayanan[$i]["tipe_biaya_id"]) echo "selected"; ?>><?php echo $dataTipeLayanan[$i]["tipe_biaya_nama"];?></option>
				            <?php } ?>
<!--			            </select>&nbsp;<input type="submit" name="btnOk" value="Ganti Data" class="submit" />-->
                </td>
          </tr>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Poli</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="id_poli" disabled id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Klinik ]</option>			
				              <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
				            <?php } ?>
<!--			            </select>&nbsp;<input type="submit" name="btnOk" value="Ganti Data" class="submit" />-->
                </td>
                <td width= "40%" align="center" class="tablecontent"><font color='red' size='3'><?php echo "Uang Muka : ".currency_format($uangmuka["total"]);?></font></td>
          </tr>           
          <!--      
          <tr>
                <td width= "15%" align="left" class="tablecontent">Nama Dokter</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Dokter ]</option>			
				              <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($_POST["id_dokter"]==$dataDokter[$i]["usr_id"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
				            <?php } ?>
			            </select>
                

                </td>
          </tr> -->
          <tr>
           <td width= "15%" align="left" class="tablecontent">Jenis Bayar </td>         
           <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
              <select name="id_jbayar"  id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">		
       				<?php if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php } ?>
           				<?php $counter = -1;
           for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++){
               unset($spacer); 
					$length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";  
        				?>                                                                      
         	  <option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"];?>" <?php if($_POST["id_jbayar"]==$dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"];?></option>
				    <?php } ?>
			    </select>
           </td>
           <td width= "40%" align="center" class="tablecontent"><font color='red' size='3'><?php echo "Retur Uang Muka : ".currency_format($retur);?></font></td>
          </tr>
               
				 <?php for($i=0,$n=count($dataJenisBayar);$i<$n;$i++) { 
               unset($spacer); 
		
    		$length = (strlen($dataJenisBayar[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
    		for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;"; 
        		
         //$_POST["txtDibayar"][$i] = '0';?>
         
          <tr>
           <td class="tablecontent" align="center">&nbsp;</td>         
           <td width= "40%" align="right" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php echo $spacer;?>&nbsp;<b>Total Pembayaran<?php //echo strtoupper($dataJenisBayar[$i]["jbayar_nama"]);?></b> </td>
           <td class="tablecontent" colspan="4">&nbsp;&nbsp;
               <?php if($dataJenisBayar[$i]["jbayar_id"]=='01' || $dataJenisBayar[$i]["jbayar_lowest"]!='n') echo $view->RenderTextBox("txtDibayar[$i]","txtDibayar$i","30","30","","curedit", "",true,'onChange=GantiPengurangan(this.value,'.$i.');');
                     else echo ""; ?></td>
           <input type="hidden" name="byr<?php echo $i;?>_int" id="byr<?php echo $i;?>_int" value="<?php if($_POST["jsByr"][$i]) echo $_POST["jsByr"][$i]; else echo '0';?>" />
           <input type="hidden" name="js_id[<?php echo $i;?>]" id="js_id_<?php echo $i;?>" value="<?php echo $dataJenisBayar[$i]["jbayar_id"];?>" />
          </tr>           
         <?php } ?>
              
          <!--<tr>
                <td  class="tablecontent" colspan="2">&nbsp;</td>
                <td width= "40%" align="right" class="tablecontent-odd">
                Jenis Bayar : 
                <select name="jbayar" id="jbayar" onKeyDown="return tabOnEnter(this, event);">
                <?php for($i=0,$n=count($jsBayar);$i<$n;$i++){ ?>
                <option value="<?php echo $jsBayar[$i]["jbayar_id"];?>" <?php if($jsBayar[$i]["jbayar_id"]==$_POST["jbayar"]) echo "selected"; ?>><?php echo $jsBayar[$i]["jbayar_nama"];?></option>
				        <?php } ?>    
          			</select>
                </td>
                
          <td class="tablecontent" colspan="4">
               <table width="100%" border="0">
               <tr>
               <td width="30%" align="right">Dibayar : </td>
				       <td width="70%" align="left">
               <?php //echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"],"curedit", "",true,'onChange=GantiPengurangan(this.value)');?></td>
				       </tr>
				       </table>
				  </td>
          </tr>-->
          <!--<tr>
          <td class="tablecontent" colspan="5">
          <table width="100%" border="0">
               <tr>
               <td width="75%" align="right">Kembalian :</td>
				       <td width="25%" align="left"><?php //echo $view->RenderTextBox("txtKembalian","txtKembalian","30","30",$_POST["txtHargaTotal"],"curedit", "readonly",null,true);?></td>
				       </tr>
				       </table>
				  </td>
          </tr>-->
          <tr>
               <td width= "50%" align="center" class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
               <td width="50%" align="left">&nbsp;</td>
				       <td width="50%" align="center">
               <?php if($dataTable){ ?>
               <?php if($dataPasien["reg_jenis_pasien"]=="5" || $dataPasien["reg_jenis_pasien"]=="7" || $dataPasien["reg_jenis_pasien"]=="18") {?>
               <input type="submit" name="btnSave" id="btnSave" value="Tutup Transaksi" class="submit" onClick="javascript:return CekData();"/>
               <?php } else {?>
               <input type="submit" name="btnSave" id="btnSave" value="Bayar" class="submit" onClick="javascript:return CekData();"/>     
               <?php } ?>
               <?php } ?>
             <!--  <input type="submit" name="btnTidak" id="btnTidak" value="Tidak Membayar" class="submit" onClick="javascript:return CekData();"/>   -->  
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
				       </td>
				       </tr>
				       </table>
          </td>
          </tr>
           
	   </table>
	        </div>
     </fieldset>

     <fieldset>
     <legend><strong>Data Order</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr class="tablesmallheader">
            <td width="1%" align='center'>No</td>
            <td width="15%" align='center'>Poli</td>
            <td width="15%" align='center'>Dokter Pengirim</td>
            <td width="15%" align='center'>Dokter Tujuan</td>
            <td width="3%" align='center'>Telah Dilayani</td>                            
        </tr>
      	<?php for($i=0,$n=count($dataorderPoli);$i<$n;$i++) { 
              $sql = "select fol_id from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$dataorderPoli[$i]["reg_id"]);
              $datalayani = $dtaccess->Fetch($sql); ?>                                                                      
        <tr class="tablecontent-odd">
            <td width="1%" align='center'><?php echo ($i+1)."."; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["poli_nama"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["dokter_sender"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["usr_name"]; ?></td>
            <?php if(!$datalayani) {?>
            <td width="3%" align='center'>&nbsp;</td>                            
            <?php }else{ ?>
            <td width="3%" align='center'><img hspace="2" width="20" height="20" src="<?php echo $ROOT.'gambar/aktif.png';?>" /></td>
            <?php } ?> 
        </tr>
        <?php } ?>  
     </table>
     </div>
     </fieldset>
     
     <fieldset>
     <legend><strong>Data Pembayaran</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
              <tr class="tablesmallheader">
              <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
              <td width="3%" align='center'>Hapus</td>
              <?php } ?>
              <td width="3%" align='center'>No</td>
              <td width="32%" align='center'>Layanan</td>
			  <td width="17%" align='center'>Nama Dokter</td>
              <td width="10%" align='center'>Biaya</td>
              <td width="5%" align='center'>Quantity</td>
              <td width="10%" align='center'>Tagihan</td>
              <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='20') { ?>
                <td width="10%" align='center'>Dijamin</td>
                <td width="10%" align='center'>Subsidi</td>
                <td width="10%" align='center'>Iur Biaya</td>
                <td width="10%" align='center'>Hrs Bayar</td>
                <? } ?>
                <?php if($_POST["reg_jenis_pasien"]=='18') { ?>
                <td width="10%" align='center'>Dijamin Dinkes Prop</td>
                <td width="10%" align='center'>Dijamin Dinkes Kab</td>
                <td width="10%" align='center'>Iur Biaya</td>
                <td width="10%" align='center'>Hrs Bayar</td>
                <? } ?>
						  </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
						  
              <tr class="tablecontent-odd">
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                <td width="3%" align="center"><?php if($dataTable[$i]["fol_jenis"]=="WA" || $dataTable[$i]["fol_jenis"]=="RJ" || $dataTable[$i]["fol_jenis"]=="RS" || $dataTable[$i]["fol_jenis"]=="RU" || $dataTable[$i]["fol_jenis"]=="RV") { echo "&nbsp;"; } else { echo '<a href="'.$delPage.'&del=1&id='.$dataTable[$i]["fol_id"].'&id_register='.$dataTable[$i]["id_reg"].'&id_biaya='.$dataTable[$i]["id_biaya"].'&id_pembayaran='.$_POST["pembayaran_id"].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/b_drop.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>';} ?></td>
                <?php } ?>
                <td width="3%"><?php echo ($i+1).".";?></td>
                <td width="32%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
                </td>
				        <td width="17%"><?php echo $dataTable[$i]["usr_name"];?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
                <td width="5%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>
                <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='20') { ?>
                <td width="10%" align='right'><?php if($_POST["reg_jenis_pasien"]=='5' && $_POST["id_poli"]<>$op["poli_id"]){ echo "0";} else { echo currency_format($dataTable[$i]["fol_dijamin"]);}?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_subsidi"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_iur_bayar"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td>
                <? } ?>
                <?php if($_POST["reg_jenis_pasien"]=='18') { ?>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin1"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin2"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_iur_bayar"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td>
                <? } ?>

						  </tr>
						  
						  <?php } ?>
						  
              <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
              <tr>
						  <td class="tablecontent-odd" width="3%"></td>
              <td class="tablecontent-odd" width="3%"></td>
              <td class="tablecontent-odd" width="45%" align="left">
              <select name="id_kategori" id="id_kategori" onChange="tindakan(this.value);">
        				<option value="" align="center"> [ Pilih Kategori ] </option>
        					<?php for($i=0,$n=count($dataKategori);$i<$n;$i++){ ?>
        						<?php if(!$_POST["id_reg"]) { ?>
        							<option class="inputField" value="<?php echo $dataKategori[$i]["kategori_tindakan_id"];?>" <?php if($_POST["id_kategori"][0]==$dataKategori[$i]["kategori_tindakan_id"]) echo "selected"; ?>><?php echo $dataKategori[$i]["kategori_tindakan_nama"];?></option>
        						<?php } else { ?>
        							<option class="inputField" value="<?php echo $dataKategori[$i]["kategori_tindakan_id"]."-".$dataKategori[$i]["kategori_tindakan_loket"];?>" <?php //if($_POST["id_kategori"][0]==$dataKategori[$i]["kategori_tindakan_id"]) echo "selected"; ?>><?php echo substr($dataKategori[$i]["kategori_tindakan_nama"], 0, 30);?></option>
        						<?php } ?>
        					<?php } ?>
        			</select> 
              <span id="barang"></span>
              &nbsp;&nbsp;<input type="submit" name="btnTindakan" value="Masukkan Tindakan" class="submit" onClick="javascript:return CekTindakan(document.frmEdit);">
              </td>
              <td class="tablecontent-odd" width="15%" colspan='3'><div id="txtHint"></div></td>
						  </tr>
              <?php } ?> 
             <?php if($_POST["reg_jenis_pasien"]<>'2') { ?>              
              <tr>                                     
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
                  <td align="right" width="10%" class="tablesmallheader" colspan='6'>&nbsp;Keringanan</td>
                <?php } else { ?>                
                  <td align="right" width="10%" class="tablesmallheader" colspan='9'>&nbsp;Keringanan</td>
                <?php } ?>
                  <td width="15%" class="tablesmallheader" align='right'>                                                                         
                    <?php echo $view->RenderTextBox("txtDiskonPersen","txtDiskonPersen","3","30",$_POST["txtDiskonPersen"],"curedit", "",true,'onChange=Diskon(this.value,'.$grandTotalHarga.')');?>  %                                          
                    <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon(this.value,'.$grandTotalHarga.')');?>	                                                   
                </td>
              </tr>
              <?} else { ?>
              <tr>                                     
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
                  <td align="right" width="20%" class="tablesmallheader" colspan='6'>&nbsp;Keringanan</td>
                <?php } else { ?>                
                  <td align="right" width="20%" class="tablesmallheader" colspan='5'>&nbsp;Keringanan</td>
                <?php } ?>
                  <td width="15%" class="tablesmallheader" align='right'>                                                                         
                    <?php echo $view->RenderTextBox("txtDiskonPersen","txtDiskonPersen","3","30",$_POST["txtDiskonPersen"],"curedit", "",true,'onChange=Diskon(this.value,'.$grandTotalHarga.')');?>  %                                          
                    <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon(this.value,'.$grandTotalHarga.')');?>	                                                   
                </td>
              </tr> 
              <?}?>
						  <?php if($_POST["reg_jenis_pasien"]<>'2') { ?>
						  <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="9"><b>Total Tagihan</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="8"><b>Total Tagihan</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($totalBiaya)."</b>";?></td>
						  </tr>
              <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="9"><b>Total Dijamin</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="8"><b>Total Dijamin</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php if($_POST["reg_jenis_pasien"]=='5' && $_POST["id_poli"]<>$op["poli_id"]) {echo "<b>Rp. ".currency_format($dataPasien["pembayaran_dijamin"]+$inacbg["inacbg_topup"])."</b>";} else { echo "<b>Rp. ".currency_format($totalDijamin)."</b>";} ?></td>
						  </tr>
              <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="9"><b>Total Harus Bayar</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="8"><b>Total Harus Bayar</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($grandTotalHarga)."</b>"; ?></td>
						  </tr>
              <?} else { ?>
 						  <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Tagihan</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="4"><b>Total Tagihan</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($grandTotalHarga)."</b>";?></td>
						  </tr>
              <?}?>
 	</table>
	</div>
     </fieldset>
    	        <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandTotalHarga;?>" />
              <input type="hidden" name="total_dijamin" id="total_dijamin" value="<?php echo $totalDijamin;?>" />
              <input type="hidden" name="total_biaya" id="total_biaya" value="<?php echo $totalBiaya;?>" /> 
              <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
              <input type="hidden" name="txtBiayaResep" id="txtBiayaResep" value="<?php echo $_POST["txtDiskon"]; ?>" />
              <input type="hidden" name="txtBiayaRacikan" id="txtBiayaRacikan" value="<?php echo $_POST["txtBiayaRacikan"]; ?>" />
              <input type="hidden" name="txtBiayaBhps" id="txtBiayaBhps" value="<?php echo $_POST["txtBiayaBhps"]; ?>" />
              <input type="hidden" name="txtBiayaPembulatan" id="txtBiayaPembulatan" value="<?php echo $_POST["txtBiayaPembulatan"]; ?>" />
              <input type="hidden" name="txtPPN" id="txtPPN" value="0">
             <!-- <input type="hidden" name="txtDiskonPersen" id="txtDiskonPersen" value="0">
              <input type="hidden" name="txtDiskon" id="txtDiskon" value="0">  -->
              <input type="hidden" name="txtcek" id="txtcek" value="<?php echo $_POST["txtcek"]; ?>">
              <input type="hidden" name="txtTotalDibayar" id="txtTotalDibayar" value="<?php echo $totalHarga?>">
              <!--<input type="hidden" name="txtDibayar" id="txtDibayar" value="<?php echo $_POST["txtDibayar"]; ?>">-->
              <input type="hidden" name="txtKembalian" id="txtKembalian" value="<?php echo $_POST["txtHargaTotal"]; ?>">
              <!--<input type="hidden" name="id_dokter" id="id_dokter" value="<?php echo $_POST["id_dokter"]; ?>"> -->
              <input type="hidden" name="pembayaran_id" id="pembayaran_id" value="<?php echo $_POST["pembayaran_id"]; ?>">
              <input type="hidden" name="bayar" id="bayar" value="<?php echo $grandTotalHarga;?>" />
              <!--<input type="hidden" name="dibayar" id="dibayar" value="<?php echo $_POST["txtDibayar"][0];?>" />-->
		
		</tr>
	</table>

<script>document.frmEdit.txtDibayar0.focus();</script>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
<input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
<input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>"/>
<input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>"/>
<input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>"/>
<input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"]; ?>"/>
<input type="hidden" name="reg_tipe_jkn" value="<?php echo $_POST["reg_tipe_jkn"]; ?>"/>
<input type="hidden" name="reg_tipe_layanan" value="<?php echo $_POST["reg_tipe_layanan"]; ?>"/>
<input type="hidden" name="id_poli" value="<?php echo $_POST["id_poli"]; ?>"/>
<input type="hidden" name="pembayaran_dijamin" value="<?php echo $dataPasien["pembayaran_dijamin"]; ?>"/>
<input type="hidden" name="inacbg_appv" value="<?php echo $inacbg["inacbg_appv"]; ?>"/>
<input type="hidden" name="inacbg_topup" value="<?php echo $inacbg["inacbg_topup"]; ?>"/>
<input type="hidden" name="id_perusahaan" value="<?php echo $_POST["id_perusahaan"]; ?>"/>
<input type="hidden" name="id_jamkesda_kota" value="<?php echo $_POST["id_jamkesda_kota"]; ?>"/>
<input type="hidden" name="uangmuka" value="<?php echo $uangmuka["total"]; ?>"/>
<input type="hidden" name="retur" value="<?php echo $retur; ?>"/>
<input type="hidden" name="op" value="<?php echo $op["poli_id"]; ?>"/>
<input type="hidden" name="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"]; ?>"/>
</form>

<?php } ?>

</div>
</div>
  		<!--table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->
 <?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
