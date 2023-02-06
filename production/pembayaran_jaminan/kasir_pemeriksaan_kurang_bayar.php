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
     if ($_GET["id_jbayar"]) $_POST["id_jbayar"]=$_GET["id_jbayar"];

	
	if($_GET["id_reg"] || $_GET["pembayaran_id"]) {
		$sql = "select a.reg_jenis_pasien, a.id_poli,cust_usr_alamat, cust_usr_nama, cust_usr_kode, b.cust_usr_jenis_kelamin, cust_usr_foto, a.id_dokter,
				    ((current_date - b.cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr from  klinik.klinik_registrasi a 
            join  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);

    $dataPasien= $dtaccess->Fetch($sql);
    
    $_POST['fol_id'] = $_GET["fol_id"];
    $_POST["id_reg"] = $_GET["id_reg"];  
		$_POST["id_biaya"] = $_GET["biaya"]; 
    $_POST["pembayaran_id"] = $_GET["pembayaran_id"];
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    if (!$_POST["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    if (!$_POST["id_poli"]) $_POST["id_poli"] = $dataPasien["id_poli"];
    
    
    if (!$_POST["id_dokter"]) $_POST["id_dokter"] = $dataPasien["id_dokter"];
		$_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];
		$_POST["pembayaran_id"] = $_GET["pembayaran_id"];
    
		
		$sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
		$dataKet = $dtaccess->Fetch($sql);
		$_POST["fol_keterangan"] = $dataKet["fol_keterangan"];
		
		$lokasi = $ROOT."gambar/foto_pasien";
		
		 $sql = "select a.*,b.pembayaran_det_ke,b.pembayaran_det_tgl,b.pembayaran_det_total from 
            klinik.klinik_pembayaran a  left join klinik.klinik_pembayaran_det b on a.pembayaran_id = b.id_pembayaran
            where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
     $dataPembayaran = $dtaccess->FetchAll($sql);
     $kurangBayar = $dataPembayaran[0]["pembayaran_total"]-$dataPembayaran[0]["pembayaran_yg_dibayar"]-$dataPembayaran[0]["pembayaran_dijamin"];
     
	}
     
     $sql = "select * from  klinik.klinik_folio
			       where fol_lunas='y' and id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and id_dep=".QuoteValue(DPE_CHAR,$depId)." order by fol_waktu asc"; 
		 //echo $sql;
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);
//      echo $sql;
//      die();
    for($i=0,$n=count($dataTable);$i<$n;$i++){
          
          //if($dataTable[$i]["fol_jumlah"]){
            //$total = $dataTable[$i]["fol_jumlah"]*$dataTable[$i]["fol_nominal"];
          //}else{
            /*if ($_POST["reg_jenis_pasien"]=='1')
            {*/
              $total = $dataTable[$i]["fol_hrs_bayar"];
              $dijamin = $dataTable[$i]["fol_dijamin"];
              $totalBiaya = $totalBiaya+$dataTable[$i]["fol_nominal"];
            /*}
            else
            {
              $total = $dataTable[$i]["fol_nominal"];
            } */
              
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
          
          $grandTotalHarga = $totalHarga;
          $totalDijamin += $dijamin;
   }
   
   $sql = "select pembayaran_dijamin from  klinik.klinik_pembayaran
     where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);

   $rs_dijamin = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   $dataDijamin = $dtaccess->Fetch($rs_dijamin);
   
   //total biaya
   $totalBiaya=$totalBiaya;   
   //harga dijamin
   $dijaminHarga = $dataDijamin["pembayaran_dijamin"];
   
   //perhitungan rumus JKN
   if($_POST["reg_jenis_pasien"]=="5"){
   $totalHarga=$totalBiaya-$dijaminHarga;
   } else $totalHarga=$totalHarga;
   if ($totalHarga<0) $totalHarga=0; 
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga;  	 

   
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
              
              $sql_perawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
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
              
             $sqltdk = "select biaya_jenis,biaya_nama,biaya_total from klinik.klinik_biaya where biaya_id =".QuoteValue(DPE_CHAR,$tindakanId[0])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
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
                   $dbValue[7] = QuoteValue(DPE_CHARKEY,'y');
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
            
           /* $sql = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
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

                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                   
                   $dtmodel->Insert() or die("insert  error");
                   
                   unset($dbField);
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);  
                   
            }*/       
                     
          }
               
    $sql = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                        and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataBayar = $dtaccess->Fetch($sql);                     
    
    $kembali = "kasir_pemeriksaan_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."&id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
    header("location:".$kembali);
    exit();
    
    }
       

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {	
   
  $sql = "select * from klinik.klinik_pembayaran where 
          id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
              and id_dep =".QuoteValue(DPE_CHAR,$depId);
  $dataReg = $dtaccess->Fetch($sql);

   $sql = "select * from klinik.klinik_registrasi where 
          reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
              and id_dep =".QuoteValue(DPE_CHAR,$depId);
  $reg = $dtaccess->Fetch($sql);
    
    // update registrasi // 
    /* warning saat registrasi
    /* sementara ditutup
		$sql = "update  klinik.klinik_registrasi set reg_status='E0', 
    reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' , reg_bayar = 'n',
    reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"])."
    where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
    " and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql);
    */
     
    $pembayaran_yg_dibayar=$dataReg["pembayaran_yg_dibayar"]+StripCurrency($_POST["txtDibayar"][0]);    
    if (StripCurrency($_POST["txtDibayar"][0]) <  StripCurrency($_POST["kurang_bayar"])) $flagPembayaran='n';
    if (StripCurrency($_POST["txtDibayar"][0]) >= StripCurrency($_POST["kurang_bayar"])) {
      if($reg["reg_jenis_pasien"]=="5") {
        $flagPembayaran='n';
      } else {
        $flagPembayaran='y';
      }
    }
    //echo StripCurrency($_POST["txtDibayar"][0])." >= ".StripCurrency($_POST["kurang_bayar"]);
    
     // jika pembayarannya ada diskon ee //
    if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
    $sql = "update klinik.klinik_pembayaran set 
            pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d"))." , 
            pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s"))." , 
            pembayaran_flag = ".QuoteValue(DPE_CHAR,$flagPembayaran)." , pembayaran_jenis = 'T' , 
            pembayaran_yg_dibayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaran_yg_dibayar)).", 
            pembayaran_who_create =".QuoteValue(DPE_CHAR,$userName)." 
            where pembayaran_id =".QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"])." and 
            id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);                                                                    

    // jika gk ada diskon eee//
    } else {
    
    $sql =  "update klinik.klinik_pembayaran set pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d")).", 
             pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s")).", 
             pembayaran_flag = ".QuoteValue(DPE_CHAR,$flagPembayaran).", 
             pembayaran_yg_dibayar =".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaran_yg_dibayar)).", 
             pembayaran_who_create =".QuoteValue(DPE_CHAR,$userName)." 
             where pembayaran_id =".QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    //echo $sql;
    //die();
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    }
    
    $sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det 
            where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $Maxs = $dtaccess->Fetch($rs);
    $MaksUrut = ($Maxs["total"]+1);
                    
    $dbTable = "klinik.klinik_pembayaran_det";
    $dbField[0] = "pembayaran_det_id"; // PK
    $dbField[1] = "id_pembayaran";
    $dbField[2] = "pembayaran_det_create";
    $dbField[3] = "pembayaran_det_tgl";
    $dbField[4] = "pembayaran_det_ke";
    $dbField[5] = "pembayaran_det_total";
    $dbField[6] = "id_dep";
    $dbField[7] = "id_jbayar";
    $dbField[8] = "who_when_update";
    $dbField[9] = "id_jenis_pasien";
    $dbField[10] = "pembayaran_det_flag";
    $dbField[11] = "pembayaran_det_tipe_piutang";
    
    $byrDetId = $dtaccess->GetTransID();
    $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrDetId);
    $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"]);
    $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
    $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
    $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
    $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0]));
    $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[9] = QuoteValue(DPE_NUMERIC,$reg["reg_jenis_pasien"]);
    $dbValue[10] = QuoteValue(DPE_CHAR,"T");
    $dbValue[11] = QuoteValue(DPE_CHAR,"T");               
               
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
    $dbField[7] = "id_jbayar";
    $dbField[8] = "who_when_update";
    $dbField[9] = "id_jenis_pasien";
    $dbField[10] = "pembayaran_det_flag";
    $dbField[11] = "pembayaran_det_tipe_piutang";
    
    $byrDetIdNew = $dtaccess->GetTransID();
    $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrDetIdNew);
    $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"]);
    $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
    $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
    $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut+1);
    $dbValue[5] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["txtDibayar"][0]));
    $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[9] = QuoteValue(DPE_NUMERIC,$reg["reg_jenis_pasien"]);
    $dbValue[10] = QuoteValue(DPE_CHAR,"P");
    $dbValue[11] = QuoteValue(DPE_CHAR,"P");               
               
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
    $dtmodel->Insert() or die("insert  error");
    
    unset($dbField);
    unset($dtmodel);
    unset($dbValue);
    unset($dbKey);
    
    /*if(StripCurrency($_POST["txtDibayar"][0])<StripCurrency($_POST["kurang_bayar"])){
      $beda = StripCurrency($_POST["kurang_bayar"]) - StripCurrency($_POST["txtDibayar"][0]);
    
      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "id_jbayar";
      $dbField[8] = "who_when_update";
      $dbField[9] = "id_jenis_pasien";
      $dbField[10] = "pembayaran_det_flag";
      $dbField[11] = "pembayaran_det_tipe_piutang";
      
      $byrDetIdNew = $dtaccess->GetTransID();
      $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrDetIdNew);
      $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["pembayaran_id"]);
      $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
      $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut+1);
      $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
      $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[9] = QuoteValue(DPE_NUMERIC,$reg["reg_jenis_pasien"]);
      $dbValue[10] = QuoteValue(DPE_CHAR,"P");
      $dbValue[11] = QuoteValue(DPE_CHAR,"P");               
                 
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
      $dtmodel->Insert() or die("insert  error");
      
      unset($dbField);
      unset($dtmodel);
      unset($dbValue);
      unset($dbKey);
    }*/
  
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
     
     if(StripCurrency($_POST["txtDibayar"][0])<StripCurrency($_POST["kurang_bayar"])){
     if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
        $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
     }else{
        $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
     }
     } else {
     if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
        $keterangan ="Jurnal Pendapatan Pelunasan Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
     }else{
        $keterangan ="Jurnal Pendapatan Pelunasan Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
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
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrDetId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'PE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrDetId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
      
  if($dataPas["reg_jenis_pasien"]=="2" || $dataPas["reg_jenis_pasien"]=="15") {
  if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=2;
  if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
  
  if (StripCurrency($_POST["txtDibayar"][0]) <  StripCurrency($_POST["kurang_bayar"])){
    $beda = $_POST["kurang_bayar"]-StripCurrency($_POST["txtDibayar"][0]);
    
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["txtDibayar"][0]));
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($beda));
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
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["kurang_bayar"]));
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["kurang_bayar"]));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
   }
}

if($dataPas["reg_jenis_pasien"]=="5") {
  if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=5;
  if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
  
  if (StripCurrency($_POST["txtDibayar"][0]) <  StripCurrency($_POST["kurang_bayar"])){
    $beda = $_POST["kurang_bayar"]-StripCurrency($_POST["txtDibayar"][0]);
    
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["txtDibayar"][0]));
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($beda));
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
          $dbValue[2] = QuoteValue(DPE_CHAR,'010101020101');
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["kurang_bayar"]));
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
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["kurang_bayar"]));
//print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
   }
}
   
    $cetak = "y";   //Print Kwitansi
     
  }    // akhir btnSave


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
     	 $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
		   $dataJenisBayar= $dtaccess->FetchAll($sql);
        
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
      
      
     $sql = "select * from global.global_auth_user where (id_rol = '5' or id_rol='2') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJenis = $dtaccess->FetchAll($rs);       



                       
?>

<?php echo $view->RenderBody("module.css",true,false,"KASIR KURANG BAYAR"); ?>
<br /><br /><br /><br />                                                                                                                                     

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>     
<script type="text/javascript" src="ajax.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.js"></script>
<link rel="stylesheet" href="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.css" type="text/css" />

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
         
    if(document.getElementById('txtBack').value > '0')
    {
      alert('Maaf uang anda kurang');
      document.getElementById('txtBack').focus();
      return false;
    }
    
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
     var totalnya = document.getElementById('txtDibayar'+urut).value.toString().replace(/\,/g,"");
     var grandtotal = document.getElementById('txtKurangBayar').value.toString().replace(/\,/g,"");
     
     totalnyaInt=totalnya*1;
     
     // pembayaran pertama     
     if(terima && urut=='0') 
     {     
         document.getElementById('txtIsi').innerHTML = formatCurrency(grandtotal-totalnyaInt);
                         
     } 
           
}


function GantiDiskon(diskon) {
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var totalHarga = document.getElementById('txtTotalBiaya').value.toString().replace(/\,/g,"");
     var hargaGrand;
     totalHargaInt = totalHarga*1; 
     diskon_angka = diskon*1;  
     
     diskon_persen = (diskon_angka)/(totalHargaInt)*100; //diskon persen
     diskon_angka = (diskon_persen)/100*(totalHargaInt);
         
     hargaGrand = totalHargaInt-diskon_angka;  
     
     
     document.getElementById('txtDiskonPersen').value = diskon_persen;
     document.getElementById('txtDiskonPersen').focus();   
     document.getElementById('txtGrandTotal').value = formatCurrency(hargaGrand);
     document.getElementById('txtIsi').innerHTML = formatCurrency(hargaGrand);
}

function Diskon(diskon) {     
     var diskon = document.getElementById('txtDiskonPersen').value.toString().replace(/\,/g,"");
     var totalHarga = document.getElementById('txtTotalBiaya').value.toString().replace(/\,/g,"");
     var hargaGrand;
     totalHargaInt = totalHarga*1; 
     diskon_format = diskon*1;  
     diskon_persen = (diskon_format)/100*(totalHargaInt);    
     hargaGrand = totalHargaInt-diskon_persen;  
     document.getElementById('txtDiskon').value = formatCurrency(diskon_persen);
     document.getElementById('txtDiskonPersen').focus();   
     document.getElementById('txtGrandTotal').value = formatCurrency(hargaGrand);
     document.getElementById('txtIsi').innerHTML = formatCurrency(hargaGrand);

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
       BukaWindow('kasir_pemeriksaan_dot_kurang_cetak.php?pembayaran_det_id=<?php echo $byrDetId;?>&dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>','Kwitansi');
	 document.location.href='<?php echo $thisPage;?>';
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
               <td width= "5%" align="center" class="tablecontent" rowspan="7"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               <td width= "15%" align="left" class="tablecontent">No. RM</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
               <td width= "40%" align="center" class="tablecontent" rowspan="7"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($kurangBayar);?></span></font></td>
          </tr>	
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Alamat</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Jenis Pasien</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select disabled readonly name="reg_jenis_pasien" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
				              <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>                                    

                    <tr>
               <td width= "15%" align="left" class="tablecontent">Cara Bayar</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2">
               <select name="id_jbayar" id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">
                <?php $counter = -1;
                   for($i=0,$n=count($dataJenisBayar);$i<$n;$i++){
                       unset($spacer); 
        					$length = (strlen($dataJenisBayar[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
        					for($j=0;$j<$length;$j++) $spacer .= "..";  
        				?>
                <option value="<?php echo $dataJenisBayar[$i]["jbayar_id"];?>" <?php if($dataJenisBayar[$i]["jbayar_id"]==$_POST["id_jbayar"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar[$i]["jbayar_nama"];?></option>
				        <?php } ?>    
          			</select>
                </td>
          </tr>     
          <tr>
                <td width= "15%" align="left" class="tablecontent">Nama Dokter</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select disabled name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Dokter ]</option>			
				              <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($_POST["id_dokter"]==$dataDokter[$i]["usr_id"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
				            <?php } ?>
			            </select>
                

                </td>
          </tr> 
          <tr>
           <td class="tablecontent" align="center">&nbsp;</td>         
           <td width= "40%" align="left" colspan="2" class="tablecontent-odd">&nbsp;</td>
          </tr>
          <?php for($i=0,$n=count($dataPembayaran);$i<$n;$i++){ ?>
          <tr>
           <td class="tablecontent" colspan="2" align="center">&nbsp;</td>         
           <td width= "40%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php //echo $spacer;?>&nbsp;<b>Pembayaran <?php echo $dataPembayaran[$i]["pembayaran_det_ke"];?> (<?php echo format_date($dataPembayaran[$i]["pembayaran_det_tgl"]);?>):</b> </td>
           <td class="tablecontent" colspan="4">&nbsp;<?php echo currency_format($dataPembayaran[$i]["pembayaran_det_total"]);?>
          </tr>
          <?php } ?>
          <tr>
           <td class="tablecontent" colspan="2" align="center">&nbsp;</td>         
           <td width= "40%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php //echo $spacer;?>&nbsp;<b>Kurang Bayar :</b> </td>
           <td class="tablecontent" colspan="4">&nbsp;<?php echo $view->RenderTextBox("txtKurangBayar","txtKurangBayar","15","30",currency_format($kurangBayar),"curedit", "readonly",true,'onChange=GantiPengurangan(this.value)');?></td>	                                                   

          </tr>
   
         <?$i=0; //pengganti jenis bayar?>     
          <tr>
           <td class="tablecontent" colspan="2" align="center">&nbsp;</td>         
           <td width= "40%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php //echo $spacer;?>&nbsp;<b>Total Pembayaran :</b> </td>
           <td class="tablecontent" colspan="4">&nbsp;&nbsp;
               <?php echo $view->RenderTextBox("txtDibayar[$i]","txtDibayar$i","30","30",currency_format($kurangBayar),"curedit", "readonly",true,'onChange=GantiPengurangan(this.value,'.$i.');');?></td>
           <input type="hidden" name="byr<?php echo $i;?>_int" id="byr<?php echo $i;?>_int" value="<?php if($_POST["jsByr"][$i]) echo $_POST["jsByr"][$i]; else echo '0';?>" />
          </tr>           
              
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
               <input type="submit" name="btnSave" id="btnSave" value="Bayar" class="submit" onClick="javascript:return CekData();"/>     
				       <input type="button" name="simpan" id="simpan" value="Tunda Pembayaran" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
				       </td>
				       </tr>
				       </table>
          </td>
          </tr>
           
	   </table>
	        </div>
     </fieldset>

     
     <fieldset>
     <legend><strong>Data Pembayaran</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
              <tr class="tablesmallheader">
              <td width="3%" align='center'>No</td>
              <td width="25%" align='center'>Layanan</td>
              <td width="10%" align='center'>Biaya</td>
              <td width="3%" align='center'>Qty</td>
              <td width="10%" align='center'>Tagihan</td>
 						  </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
						  
              <tr class="tablecontent-odd">
                <td width="3%"><?php echo ($i+1).".";?></td>
                <td width="25%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
                </td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
                <td width="3%" align='right'><?php echo currency_format($dataTable[$i]["fol_jumlah"]);?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"]);?></td>
                <?php $biayaTotal=$biayaTotal+$dataTable[$i]["fol_nominal"];?>
						  </tr>
						  
						  <?php } ?>
				
              <tr>                                     
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
                  <td align="right" width="20%" class="tablesmallheader" colspan='5'>&nbsp;Total Semua Tindakan</td>
                <?php } else { ?>                
                  <td align="right" width="20%" class="tablesmallheader" colspan='4'>&nbsp;Total Semua Tindakan</td>
                <?php } ?>
                  <td width="10%" class="tablesmallheader" align='right'>                                                                         
                    Rp. <?php echo $view->RenderTextBox("txtTotalBiaya","txtTotalBiaya","15","30",currency_format($biayaTotal),"curedit", "readonly",true,'onChange=GantiDiskon(this.value,'.$grandTotalHarga.')');?>	                                                   
                </td>
              </tr>
						  

              <!--<tr>                                     
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
                  <td align="right" width="20%" class="tablesmallheader" colspan='5'>&nbsp;Diskon</td>
                <?php } else { ?>                
                  <td align="right" width="20%" class="tablesmallheader" colspan='4'>&nbsp;Diskon</td>
                <?php } ?>
                  <td width="10%" class="tablesmallheader" align='right'>                                                                         
                    <?php echo $view->RenderTextBox("txtDiskonPersen","txtDiskonPersen","3","30",$dataPembayaran[0]["pembayaran_diskon_persen"],"curedit", "readonly",true,'onChange=Diskon(this.value)');?>  %                                          
                    <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","30",currency_format($dataPembayaran[0]["pembayaran_diskon"]),"curedit", "readonly",true,'onChange=GantiDiskon(this.value)');?>	                                                   
                </td>
              </tr>-->
              <?php if($_POST["reg_jenis_pasien"]<>'2') { ?>
              <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Dijamin</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="4"><b>Total Dijamin</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php if($_POST["reg_jenis_pasien"]=='5') {echo "<b>Rp. ".currency_format($dataPembayaran[0]["pembayaran_dijamin"])."</b>";} else { echo "<b>Rp. ".currency_format($totalDijamin)."</b>";} ?></td>
						  </tr>
              <tr>
                  <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>              
                  <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Harus Bayar</b></td>
                  <?php } else { ?>
                  <td class="tablesmallheader" width="45%" align="right" colspan="4"><b>Total Harus Bayar</b></td>              
                  <?php } ?>
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($kurangBayar)."</b>"; ?></td>
						  </tr>
              <?} else { ?>
              <tr>                                                                    
                <?php if($_POST["dep_kasir_tindakan"]=='y') { ?>
                  <td align="right" width="20%" class="tablesmallheader" colspan='5'>&nbsp;Grand Total</td>
                <?php } else { ?>                
                  <td align="right" width="20%" class="tablesmallheader" colspan='4'>&nbsp;Grand Total</td>
                <?php } ?>
                  <td width="10%" class="tablesmallheader" align='right'>                                                                         
                    Rp. <?php echo $view->RenderTextBox("txtGrandTotal","txtGrandTotal","15","30",currency_format($dataPembayaran[0]["pembayaran_total"]),"curedit", "readonly",true,'onChange=GantiDiskon(this.value,'.$grandTotalHarga.')');?>	                                                   
                </td>
              </tr>
              <?php } ?>
 	</table>
	</div>                                                      
  
  
 
  
     </fieldset>
    	        <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandTotalHarga;?>" /> 
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
              <input type="hidden" name="kurang_bayar" id="kurang_bayar" value="<?php echo $kurangBayar; ?>">
		
		</tr>
	</table>

<script>document.frmEdit.CityAjax1.focus();</script>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>

<input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
<input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
<input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>"/>
<input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>"/>
<input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>"/>
<input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>"/>
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
