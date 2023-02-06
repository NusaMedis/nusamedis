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
     
     $jamSekarang = date("H:i:s");
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     
    if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) && !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  

     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     //$delPage = "penata_jasa_edit_proses.php?";

     $table = new InoTable("table","100%","left");

     //AMBIL DARI TOMBOL GANTI DATA di KLIK
      //AMBIL DARI TOMBOL GANTI DATA di KLIK
     if ($_GET["id_dokter"]) $_POST["id_dokter"]=$_GET["id_dokter"];
     if ($_GET["pembayaran_id"]) $_POST["pembayaran_id"]=$_GET["pembayaran_id"];
     if ($_GET["id_pembayaran"]) $_POST["id_pembayaran"]=$_GET["id_pembayaran"];
     //if ($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
     if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"]=$_GET["reg_jenis_pasien"];
     if ($_GET["reg_shift"]) $_POST["reg_shift"]=$_GET["reg_shift"];
     else $_POST["reg_shift"]=1; 

    


    //HAPUS PENATA JASA
    if ($_GET["delRwt"]) 
    { 
     $rwtId = $_GET["id_rawatinap"];
     $regId = $_GET["id_reg"];
     $pembayaranId = $_GET["pembayaran_id"];
     
     // hapus registrasi salah untuk penata jasa --
     $sql = "delete from klinik.klinik_pembayaran where pembayaran_id =".QuoteValue(DPE_CHAR,$pembayaranId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);

     // hapus registrasi salah untuk penata jasa --
     $sql = "delete from klinik.klinik_registrasi where reg_id =".QuoteValue(DPE_CHAR,$regId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);

     // hapus registrasi salah untuk penata jasa --
     $sql = "delete from klinik.klinik_rawatinap where rawatinap_id =".QuoteValue(DPE_CHAR,$rwtId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);

     $kembali = "kasir_lihat_proses.php";
     header("location:".$kembali);
     exit();    
   }

    //MEMULANGKAN PASIEN
    if ($_GET["pulangRwt"]) 
    { 
     $regId = $_GET["id_reg"];
     
     // hapus registrasi salah untuk penata jasa --
     $sql = "update klinik.klinik_registrasi set reg_status = 'I4' where reg_id =".QuoteValue(DPE_CHAR,$regId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);

     $kembali = "kasir_lihat_proses.php";
     header("location:".$kembali);
     exit();    
   }


	
	if($_GET["id_reg"] || $_GET["pembayaran_id"]) {
  
		$sql = "select a.reg_jenis_pasien, a.reg_kelas,a.reg_shift,a.reg_tipe_layanan,a.id_poli,cust_usr_alamat, cust_usr_nama, cust_usr_kode, b.cust_usr_jenis_kelamin, cust_usr_foto, a.id_dokter,
				    ((current_date - b.cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, a.id_perusahaan, c.fol_keterangan, a.reg_tipe_paket, 
            a.id_jamkesda_kota, b.cust_usr_jkn from  klinik.klinik_registrasi a 
            join  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join klinik.klinik_folio c on c.id_reg=a.reg_id 
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);
//    echo $sql;
    $dataPasien= $dtaccess->Fetch($sql);
    
 
    $_POST['fol_id'] = $_GET["fol_id"];
    $_POST["reg_kelas"] = $dataPasien["reg_kelas"];
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_biaya"] = $_GET["biaya"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    $_POST["reg_status"] = $dataPasien["reg_status"];
    $_POST["reg_shift"] = $dataPasien["reg_shift"];
    $_POST["reg_tipe_layanan"] = $dataPasien["reg_tipe_layanan"];
    $_POST["id_pembayaran_lama"] = $dataPasien["id_pembayaran"];
    $_POST["reg_utama"] = $_GET["id_reg"];
    if (!$_POST["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    if (!$_POST["id_poli"]) $_POST["id_poli"] = $dataPasien["id_poli"];
    if (!$_POST["id_dokter"]) $_POST["id_dokter"] = $dataPasien["id_dokter"];
    if (!$_POST["id_pelaksana"]) $_POST["id_pelaksana"] = $dataPasien["id_pelaksana"];
    if (!$_POST["id_perusahaan"]) $_POST["id_perusahaan"] = $dataPasien["id_perusahaan"];
    if (!$_POST["id_jamkesda_kota"]) $_POST["id_jamkesda_kota"] = $dataPasien["id_jamkesda_kota"];
    if (!$_POST["cust_usr_jkn"]) $_POST["cust_usr_jkn"] = $dataPasien["cust_usr_jkn"];
    $_POST["reg_shift"] = $dataPasien["reg_shift"];
    $_POST["reg_umur_bulan"] = $dataPasien["reg_umur_bulan"];
    $_POST["reg_kode_urut"] = $dataPasien["reg_kode_urut"];
    $_POST["reg_kode_trans"] = $dataPasien["reg_kode_trans"];
    $_POST["reg_rujukan_id"] = $dataPasien["reg_rujukan_id"];
    $_POST["reg_umur"] = $dataPasien["reg_umur"];
    $_POST["reg_umur_hari"] = $dataPasien["reg_umur_hari"];
    $_POST["reg_umur_bulan"] = $dataPasien["reg_umur_bulan"];
    $_POST["fol_keterangan"] = $dataPasien["fol_keterangan"];
    $_POST["reg_tipe_paket"] = $dataPasien["reg_tipe_paket"];
		
		$sql = "select fol_keterangan from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
		$dataKet = $dtaccess->Fetch($sql);
		$_POST["fol_keterangan"] = $dataKet["fol_keterangan"];
		
		$lokasi = $ROOT."gambar/foto_pasien";
		
		 $sql = "select sum(pembayaran_total) as total, sum(pembayaran_yg_dibayar) as dibayar from klinik.klinik_pembayaran a
            where pembayaran_flag = 'n' and pembayaran_jenis = 'C' and id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]); 
     $dataCicilan = $dtaccess->Fetch($sql);
     
     $sisaCicilan = $dataCicilan["total"] - $dataCicilan["dibayar"];   
	}
  

     
     $sql = "select a.*,b.usr_name as dokter_nama from  klinik.klinik_folio a
            left join global.global_auth_user b on a.id_dokter  = b.usr_id  
			       where a.fol_lunas='n' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by fol_waktu asc"; 
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);

    for($i=0,$n=count($dataTable);$i<$n;$i++){
          
          //if($dataTable[$i]["fol_jumlah"]){
            //$total = $dataTable[$i]["fol_jumlah"]*$dataTable[$i]["fol_nominal"];
          //}else{

            $total = $dataTable[$i]["fol_hrs_bayar"];
      
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
  
          $grandTotalHarga = $totalHarga;
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
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_poli"]); 
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


       if($_POST["id_biaya"]){

       //Cek Registrasi dari Front Office atau tidak M0 dari F0 kalau E0 dari Penata Jasa
       $sql = "select id_pembayaran from klinik.klinik_registrasi where 
               reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
               and id_dep =".QuoteValue(DPE_CHAR,$depId);
       $dataReg = $dtaccess->Fetch($sql);


    
        $tindakanId = explode("-", $_POST["id_biaya"]);
    
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
    
               //sementara standart dimasukkan 1
              //$_POST["txtQty"] = 1;
    
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
              $dbField[17] = "id_pembayaran";
              $dbField[18] = "fol_catatan";
              if($_POST["reg_jenis_pasien"]=='1')
              { 
              $dbField[19] = "fol_dijamin";
              $dbField[20] = "fol_subsidi";
              $dbField[21] = "fol_iur_biaya";
              $dbField[22] = "fol_hrs_bayar";
              }
              
             $sqltdk = "select biaya_jenis,biaya_nama,biaya_total,biaya_askes from klinik.klinik_biaya where biaya_id =".QuoteValue(DPE_CHAR,$tindakanId[0])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataTdk= $dtaccess->Fetch($sqltdk);
             
                 if ($_POST["id_biaya"]=='O')
                 {                                          
                   $totalTindNom = StripCurrency($_POST["txtObatTotal"]);
                   $totalTindDijamin = StripCurrency($_POST["txtObatDijamin"]);
                   $totalTindSubsidi = StripCurrency($_POST["txtObatSubsidi"]);
                   $selisihBayar = StripCurrency($totalTindNom)-StripCurrency($totalTindDijamin)-StripCurrency($totalTindSubsidi);
                   $dataTdk["biaya_jenis"]='O';
                   $dataTdk["biaya_nama"]="Penjualan Obat";
                   $dataTdk["biaya_total"]=$totalTindNom;
                   $tindakanId[0]='100';
                   if ($selisihBayar>0)
                   {      
                    $iurBayar = $selisihBayar; 
                    $hrsBayar = $selisihBayar;
                   }
                   else
                   {
                    $iurBayar=0;
                    $hrsBayar=0;
                   }
                   
                 }
                 else
                 {
                     $totalTindNom = StripCurrency($dataTdk["biaya_total"])*$_POST["txtQty"];
                     $totalTindDijamin = StripCurrency($dataTdk["biaya_askes"])*$_POST["txtQty"];
                     $selisihBayar = StripCurrency($totalTindNom)-StripCurrency($totalTindDijamin);
                     if ($selisihBayar>0)
                     {      
                      $iurBayar = $selisihBayar; 
                      $hrsBayar = $selisihBayar;
                     }
                     else
                     {
                      $iurBayar=0;
                      $hrsBayar=0;                      
                     }                                  
                   }                                     
                  
                  
                   $folId = $dtaccess->GetTransID();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
                   $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
                   $dbValue[2] = QuoteValue(DPE_CHAR,$dataTdk["biaya_nama"]);
                   //penjualan obat
                   if ($_POST["id_biaya"]=='O')
                   {
                       $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($hrsBayar));
                   }
                   else
                   {
                       $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindNom));
                   }
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
                   $dbValue[17] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
                   $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["txtObatFaktur"]);
                   if(($_POST["reg_jenis_pasien"]=='1') or ($_POST["reg_jenis_pasien"]=='21') or ($_POST["reg_jenis_pasien"]=='16') or ($_POST["reg_jenis_pasien"]=='18')){ 
                   $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindDijamin));
                   
                   if ($_POST["id_biaya"]=='O')
                   {
                      $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtObatSubsidi"]));
                   }
                   else
                   {
                      $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($totalTindNom)-StripCurrency($totalTindDijamin));
                   }

                   $dbValue[21] = QuoteValue(DPE_NUMERIC,$iurBayar);
                   $dbValue[22] = QuoteValue(DPE_NUMERIC,$hrsBayar);
                   }
                   
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
                   
            }       
                     
          }
               
    $sql = "select * from klinik.klinik_pembayaran where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                        and id_dep =".QuoteValue(DPE_CHAR,$depId);
             $dataBayar = $dtaccess->Fetch($sql);                     
    
    $kembali = "penata_jasa_edit_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."&id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
    header("location:".$kembali);
    exit();
    
    }
    

       
    if ($_POST["btnOk"]) {
  
    $sql = "update  klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
    
    $kembali = "kasir_lihat_proses.php?id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
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
   
////    id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
    
    // update registrasi // 
		$sql = "update  klinik.klinik_registrasi set reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y'
    where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).
    " and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql);
    
    // cari dokter e //
    $sql = "select usr_name from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    $rs = $dtaccess->Execute($sql);
    $Doktere = $dtaccess->Fetch($rs);    
    
    // update data pembayaran //
    
    // jika pembayarannya ada diskon ee //
    if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
    $sql = "update klinik.klinik_pembayaran set pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$Doktere["usr_name"])." , pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d"))." , pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s"))." , pembayaran_flag = 'y' , pembayaran_jenis = 'T' , pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))." , pembayaran_yg_dibayar =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]))." , pembayaran_diskon =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]))." , pembayaran_diskon_persen =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]))." , pembayaran_service_cash =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]))." where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    // jika gk ada diskon eee//
    } else {
    
    $sql =  "update klinik.klinik_pembayaran set pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$Doktere["usr_name"])." , 
             pembayaran_tanggal =".QuoteValue(DPE_DATE,date("Y-m-d"))." , pembayaran_create =".QuoteValue(DPE_DATE,date("Y-m-d H:i:s"))." , 
             pembayaran_flag = 'y' , pembayaran_jenis = 'T' , pembayaran_total =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))." ,
             pembayaran_yg_dibayar =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]))." , 
             pembayaran_diskon =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]))." , 
             pembayaran_diskon_persen =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]))." ,
             pembayaran_service_cash =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]))."
             where pembayaran_id =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    }
    
    // insert  ke pembayaran detailnya //    
    //if($_POST["txtDibayar"][0] || $_POST["txtDibayar"][1] || $_POST["txtDibayar"][2] || $_POST["txtDibayar"][3] || $_POST["txtDibayar"][4] || $_POST["txtDibayar"][5] || $_POST["txtDibayar"][6] || $_POST["txtDibayar"][7] || $_POST["txtDibayar"][8] || $_POST["txtDibayar"][9]) {
    
    for($i=0,$n=count($_POST["txtDibayar"]);$i<$n;$i++) {
    
    $dbTable = "klinik.klinik_pembayaran_multipayment";
              $dbField[0] = "pembayaran_multipayment_id"; // PK
              $dbField[1] = "id_pembayaran";
              $dbField[2] = "pembayaran_multipayment_create";
              $dbField[3] = "pembayaran_multipayment_tgl";
              $dbField[4] = "pembayaran_multipayment_ke";
              // jika ada diskon ee
               if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
               $dbField[5] = "pembayaran_multipayment_total";
               } else {
              $dbField[5] = "pembayaran_multipayment_total";
              }
              $dbField[6] = "id_dep";
              $dbField[7] = "id_jbayar";
              $dbField[8] = "pembayaran_multipayment_service_cash";
              
              //$sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det where id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
              //$rs = $dtaccess->Execute($sql);
              //$Maxs = $dtaccess->Fetch($rs);
              //$MaksUrut = ($Maxs["total"]+1);
              $MaksUrut = "1";
              
                   $byrdetailId = $dtaccess->GetTransID();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrdetailId);
                   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                   $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                   $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                   $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
                   // jika ada diskon ee
                    if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                    } else {
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]));
                    }
                   $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["js_id"][$i]);
                   $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
               
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                   
                   $dtmodel->Insert() or die("insert  error");
                   
                   unset($dbField);
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);
                   
                   $honorTot += $_POST["txtDibayar"][$i];
                                   
    
    }
    
                   $sql = "select * from klinik.klinik_pembayaran_multipayment where id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                   $rs = $dtaccess->Execute($sql);
                   $PembayaranDetz = $dtaccess->FetchAll($rs);
                   
                    for($ii=0,$nn=count($PembayaranDetz);$ii<$nn;$ii++) {
                    
                     if($PembayaranDetz[$ii]["pembayaran_multipayment_total"]=='0.0000') {
                      $sql = "delete from klinik.klinik_pembayaran_multipayment where pembayaran_multipayment_id = ".QuoteValue(DPE_CHAR,$PembayaranDetz[$ii]["pembayaran_multipayment_id"]);
                      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
                      }
                      
                    }
                    
    $dbTable = "klinik.klinik_pembayaran_det";
              $dbField[0] = "pembayaran_det_id"; // PK
              $dbField[1] = "id_pembayaran";
              $dbField[2] = "pembayaran_det_create";
              $dbField[3] = "pembayaran_det_tgl";
              $dbField[4] = "pembayaran_det_ke";
              $dbField[5] = "pembayaran_det_total";
              $dbField[6] = "id_dep";
              $dbField[7] = "pembayaran_det_service_cash";
            
              
                   $byrHonorId = $dtaccess->GetTransID();
                   $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
                   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
                   $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                   $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
                   $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
                   // jika ada diskon ee
                    if($_POST["txtDiskonPersen"] && $_POST["txtDiskon"]) {
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
                    } else {
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]));
                    }
                   $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
               
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                   
                   $dtmodel->Insert() or die("insert  error");
                   
                   unset($dbField);
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);
  
  //}                 
                   
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal "; 
    $sql .= " , fol_diskon_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])); 
    //$sql .= " , fol_pembulatan_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"])); 
    $sql .= " , fol_diskon_persen_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]));
    $sql .= " , fol_total_harga = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
    //$sql .= " , fol_catatan = ".QuoteValue(DPE_CHAR,$_POST["penjualan_catatan"]);
    $sql .= " , who_when_update = ".QuoteValue(DPE_CHAR,$userId);
    $sql .= " , fol_dibayar_when =  CURRENT_TIMESTAMP where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
     $sql = "select fol_diskon_persen_penjualan,fol_pembulatan_penjualan,fol_diskon_penjualan,fol_total_harga from  klinik.klinik_folio
			       where ( fol_jenis like  '%T%' or fol_jenis like  '%WA%' or fol_jenis like  '%R%' ) and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' 
             and id_dep = ".QuoteValue(DPE_CHAR,$depId); 
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
      
     $cetak = "y";
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
//    id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]).",
  	
    // update registrasi // 
		$sql = "update  klinik.klinik_registrasi set reg_status='E0',reg_bayar='n', 
    reg_waktu = CURRENT_TIME , reg_msk_apotik = 'y' ,
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

    $next = "penata_jasa_edit_view.php";

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

           // hapus tindakan di klinikk perawatan tindakan --
           $sql = "select reg_jenis_pasien from klinik.klinik_registrasi where reg_id =".QuoteValue(DPE_CHAR,$_GET["id_register"]);          
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
           $dataRegJenis = $dtaccess->Fetch($rs);

          
          // kembali ke atas      
          $kembali = "penata_jasa_edit_proses.php?id_reg=".$_GET["id_register"]."&reg_jenis_pasien=".$dataRegJenis["reg_jenis_pasien"]."&pembayaran_id=".$_GET["id_pembayaran"];
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
     	 $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' or jbayar_id = '01' order by jbayar_id asc";
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
		   
       $sql = "select a.* from klinik.klinik_kategori_tindakan_header a
       order by a.kategori_tindakan_header_nama"; 
  		 $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
       $dataKategori = $dtaccess->FetchAll($rs_edit);    
      
     $sql = "select * from global.global_auth_user where (id_rol = '5' or id_rol='2') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJenis = $dtaccess->FetchAll($rs);       
	 
	 $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);  
	 
     $sql = "select * from klinik.klinik_kelas order by kelas_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKelas = $dtaccess->FetchAll($rs);       
     
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
<?php echo $view->RenderBody("module.css",true,false,"KASIR lIHAT SEMENTARA"); ?>
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

    if(!frm.id_biaya.value){
		alert('Pilih dahulu Tindakan yang akan dimasukkan');
		frm.id_biaya.focus();
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

    if(document.getElementById('id_dokter').value == '--')
    {
      alert('Maaf Dokter Harus Dipilih');
      document.getElementById('id_dokter').focus();
      return false;
    }
         
    if(document.getElementById('txtBack').value > '0')
    {
      alert('Maaf uang anda kurang');
      document.getElementById('txtBack').focus();
      return false;
    }
    
    return true;
}



var grandTotal = '<?php echo $grandTotalHarga;?>';

function GantiBiayaResep(biayaResep) {
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var biayaRacikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"");
     var biayaBhps = document.getElementById('txtBiayaBhps').value.toString().replace(/\,/g,""); 
     var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalPembayaran = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
     var biayaPembulatan = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"");

  
     var biayaResep = biayaResep.toString().replace(/\,/g,"");
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     biayaResepInt=biayaResep*1;    
     biayaRacikanInt=biayaRacikan*1;  
     biayaBhpsInt=biayaBhps*1;  
     biayaPembulatanInt = biayaPembulatan*1;
     total_bayarInt=total_bayar*1;       //Total Obat
     totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
     totalPembayaranInt=totalPembayaran*1;  //Total Pembayaran
     
    document.getElementById('txtTotalDibayar').value = formatCurrency((total_bayarInt+biayaPembulatanInt-diskonInt)+(biayaResepInt+biayaRacikanInt+biayaBhpsInt)); 
    if (totalPembayaranInt>0)
    {
    
       var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
       totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
      
       document.getElementById('txtKembalian').value = formatCurrency(totalPembayaranInt-(totalDibayarInt));
    }
  //  document.getElementById('btnBayar').focus();
}


function GantiBiayaRacikan(biayaRacikan) {
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var biayaResep = document.getElementById('txtBiayaResep').value.toString().replace(/\,/g,"");
     var biayaBhps = document.getElementById('txtBiayaBhps').value.toString().replace(/\,/g,""); 
     var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalPembayaran = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
      var biayaPembulatan = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"");

  
     var biayaRacikan = biayaRacikan.toString().replace(/\,/g,"");
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     biayaResepInt=biayaResep*1;    
     biayaRacikanInt=biayaRacikan*1;  
     biayaBhpsInt=biayaBhps*1;  
     biayaPembulatanInt = biayaPembulatan*1;
     total_bayarInt=total_bayar*1;       //Total Obat
     totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
     totalPembayaranInt=totalPembayaran*1;  //Total Pembayaran
   //  alert(biayaRacikanInt);
    document.getElementById('txtTotalDibayar').value = formatCurrency((total_bayarInt+biayaPembulatanInt-diskonInt)+(biayaResepInt+biayaRacikanInt+biayaBhpsInt)); 
    if (totalPembayaranInt>0)
    {
    
       var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
       totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
      
       document.getElementById('txtKembalian').value = formatCurrency(totalPembayaranInt-(totalDibayarInt));
    }
    //document.getElementById('btnBayar').focus();
}



function GantiBiayaBhps(biayaBhps) {
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var biayaResep = document.getElementById('txtBiayaResep').value.toString().replace(/\,/g,"");
     var biayaRacikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,""); 
     var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalPembayaran = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
     var biayaPembulatan = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"");

  
     var biayaBhps = biayaBhps.toString().replace(/\,/g,"");
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     biayaResepInt=biayaResep*1;    
     biayaRacikanInt=biayaRacikan*1;  
     biayaBhpsInt=biayaBhps*1;  
     biayaPembulatanInt = biayaPembulatan*1;
     total_bayarInt=total_bayar*1;       //Total Obat
     totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
     totalPembayaranInt=totalPembayaran*1;  //Total Pembayaran
   //  alert(biayaRacikanInt);
    document.getElementById('txtTotalDibayar').value = formatCurrency((total_bayarInt+biayaPembulatanInt-diskonInt)+(biayaResepInt+biayaRacikanInt+biayaBhpsInt)); 
    if (totalPembayaranInt>0)
    {
    
       var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
       totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
      
       document.getElementById('txtKembalian').value = formatCurrency(totalPembayaranInt-(totalDibayarInt));
    }
    //document.getElementById('btnBayar').focus();
}

function GantiBiayaPembulatan(biayaPembulatan) {
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     var biayaResep = document.getElementById('txtBiayaResep').value.toString().replace(/\,/g,"");
     var biayaBhps = document.getElementById('txtBiayaBhps').value.toString().replace(/\,/g,""); 
     var biayaRacikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,""); 
      
     var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalPembayaran = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
  
  
     var biayaPembulatan = biayaPembulatan.toString().replace(/\,/g,"");
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     biayaResepInt=biayaResep*1;    
     biayaRacikanInt=biayaRacikan*1;  
     biayaBhpsInt=biayaBhps*1; 
     biayaPembulatanInt=biayaPembulatan*1; 
     total_bayarInt=total_bayar*1;       //Total Obat
     totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
     totalPembayaranInt=totalPembayaran*1;  //Total Pembayaran
   //  alert(biayaRacikanInt);
    document.getElementById('txtTotalDibayar').value = formatCurrency((total_bayarInt-diskonInt+biayaPembulatanInt)+(biayaResepInt+biayaRacikanInt+biayaBhpsInt)); 
    if (totalPembayaranInt>0)
    {
    
       var totalDibayar = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
       totalDibayarInt=totalDibayar*1;     //Yang Harus Dibayar
      
       document.getElementById('txtKembalian').value = formatCurrency(totalPembayaranInt-(totalDibayarInt));
    }
    //document.getElementById('btnBayar').focus();
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
     document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskonpersen);
     document.getElementById('txtTotalDibayar').value = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtIsi').innerHTML = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format)));
     document.getElementById('txtServiceCash').value = formatCurrency('0');
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
       BukaWindow('kasir_cetak_sementara.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>','Kwitansi');
	 document.location.href='<?php echo $thisPage;?>';
<?php } ?>


</script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/script.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/kinetic-v3.js"></script>
<script type="text/javascript" language="javascript" src="ajax.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.js"></script>
<style>

</style>
<link rel="stylesheet" href="<?php echo $ROOT;?>lib/script/jquery/autocomplete/jquery.autocomplete.css" type="text/css" />

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
                <td width= "5%" align="center" class="tablecontent" rowspan="6"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                <?php } elseif($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500' && $dataPasien["reg_jenis_pasien"]=='2'){ ?>
                <td width= "5%" align="center" class="tablecontent" rowspan="4"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                <?php } else {?>
               <td width= "5%" align="center" class="tablecontent" rowspan="5"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               <?php } ?>
               <td width= "15%" align="left" class="tablecontent">No. RM</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
               <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18') { ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="6"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } elseif($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500' && $dataPasien["reg_jenis_pasien"]=='2'){ ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="4"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } else {?>
               <td width= "40%" align="center" class="tablecontent" rowspan="5"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } ?>
          </tr>
          <?php if($dataPasien["cust_usr_kode"]<>'100' || $dataPasien["cust_usr_kode"]<>'500') { ?>	
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Alamat</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <?php } else { ?>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["fol_keterangan"]; ?></label></td>
          </tr>
          <?php } ?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Cara Bayar</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select name="reg_jenis_pasien" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
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
                <td width= "15%" align="left" class="tablecontent">Klinik</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select disabled name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Klinik ]</option>			
				              <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
				            <?php } ?>
			            </select>&nbsp;
<!--                      <input type="submit" name="btnOk" value="Ganti Data" class="submit" />-->
                </td>
          </tr>
          <!--<tr>
               <td width= "15%" align="left" class="tablecontent">Sudah Terima dari</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
               &nbsp;&nbsp;&nbsp; <input type="submit" name="btnOk" value="Ganti Data" class="submit" />
               </td> 
          </tr>-->
	   </table>
	        </div>
     </fieldset>

     
     <fieldset>
     <legend><strong>Data Pembayaran</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
              <tr class="tablesmallheader">
              <td width="3%" align='center'>No</td>
              <td width="3%" align='center'>Tanggal</td>
              <td width="25%" align='center'>Layanan</td>
              <td width="10%" align='center'>Biaya</td>
              <td width="3%" align='center'>Jml</td>
              <td width="10%" align='center'>Total Tagihan</td>
                <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='21' || $_POST["reg_jenis_pasien"]=='16') { ?>
                <td width="10%" align='center'>Dijamin</td>
                <td width="10%" align='center'>Subsidi</td>
                <td width="10%" align='center'>Iur Biaya</td>
                <td width="10%" align='center'>Hrs Bayar</td>
                <? } else ?>
                <?php if($_POST["reg_jenis_pasien"]=='18')  {?>
                <td width="10%" align='center'>Dijamin Dinkes Prop</td>
                <td width="10%" align='center'>Dijamin Dinkes Kab</td>
                <td width="10%" align='center'>Hrs Bayar</td> 
                <? }?>                
                <td width="10%" align='center'>Pelaksana</td>
						  </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
                    <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
                                || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
                                || $dataTable[$i]["fol_jenis"]=='I'){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                                $rs = $dtaccess->Execute($sql); 
                                $dataFarmasidetail  = $dtaccess->FetchAll($rs); 
                                 }         
                                
                         if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA' 
                              || $dataTable[$i]["fol_jenis"]=='RG' || $dataTable[$i]["fol_jenis"]=='RI' ){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql);
                                $dataReturdetail  = $dtaccess->FetchAll($rs);     }      ?> 						  
              
              <tr class="tablecontent-odd">
              <td width="3%"><?php echo ($i+1).".";?></td>
			        <td class="tablecontent-odd" width="3%"><?php echo $dataTable[$i]["fol_waktu"];?></td>
              <td width="25%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
              </td>
              <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
              <td width="3%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
              <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>

                <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='21' || $_POST["reg_jenis_pasien"]=='16') { ?>
                 <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_subsidi"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_iur_bayar"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td>

                <? } else ?>
                <?php if($_POST["reg_jenis_pasien"]=='18')  {?>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin1"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_dijamin2"])?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_hrs_bayar"])?></td> 
                <?}?>
                <td width="10%" align='right'><?php echo $dataTable[$i]["dokter_nama"]?></td>                
						  </tr>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I' || $dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){  ?>

       <tr class="garis_atas garis_bawah"> 
<?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I') {
                              $sql = "select count(penjualan_detail_id) as total
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                              $rs = $dtaccess->Execute($sql);
                              $totalitem = $dtaccess->Fetch($rs);        
            }
            if($dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){
                               $sql = "select count(retur_penjualan_detail_id) as total
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql); 
            } ?>         
         <td align="left" colspan="2" rowspan="<?php echo $totalitem["total"]+1;?>" ></td>
        <td align="left">Nama Item/Obat</td>
        <td align="right">Harga Satuan</td>
        <td align="right">Quantity</td>                             
        <td align="right">Total</td>
         <td align="left" rowspan="<?php echo $totalitem["total"]+1;?>" ></td>
	    </tr>     

    <?php } ?>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I'){  ?>
    
    <?php for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) {?>
       <tr>

          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>

       </tr>     
       <?php } ?>                 
  
       <?php } ?>
           <?php if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA'
           || $dataTable[$i]["fol_jenis"]=='RI' ||$dataTable[$i]["fol_jenis"]=='RG'){ ?>                        
    <?php for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr class="garis_atas garis_bawah">
          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php } ?>                 
              
           <? } ?>        
     </tr>
     <?php 

          $totalPembayaran += $dataTable[$i]["fol_nominal"]; 
          $totalDijamin += $dataTable[$i]["fol_dijamin"];
          $totalDijamin1 += $dataTable[$i]["fol_dijamin1"];
          $totalDijamin2 += $dataTable[$i]["fol_dijamin2"];          
          $totalSubsidi += $dataTable[$i]["fol_subsidi"];          
          //$totalIur += $dataTable[$i]["fol_iur_bayar"];
          $totalHrsBayar += $dataTable[$i]["fol_hrs_bayar"];
          //perhitungan rumus JKN
            $totalHarga=$totalBiaya-$dijaminHarga;
            if ($totalHarga<0) $totalHarga=0;
     
     ?>
    <?php } ?>
<!--  <tr>
    <td align="center" colspan="8">-----------------------------------------------------------------------------------------------------------------------------------------</td>
  </tr> -->
  <tr class="garis_atas garis_bawah">
  						  
              <?php if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='18' || $_POST["reg_jenis_pasien"]=='21' || $_POST["reg_jenis_pasien"]=='16') { ?>
             	 <tr>                                     
                <td align="right" width="20%" class="tablesmallheader" colspan='11'>
                   <input type="submit" name="btnPrint" id="btnPrint" value="Cetak Rincian Sementara" class="submit"/>     
                   <input type="button" name="kembali" id="kembali" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
                </td>
              </tr>
              <?} else { ?>              
						  <tr>                                     
                <td align="right" width="20%" class="tablesmallheader" colspan='7'>
                   <input type="submit" name="btnPrint" id="btnPrint" value="Cetak Rincian Sementara" class="submit"/>     
                   <input type="button" name="kembali" id="kembali" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
                </td>
              </tr>
              <?}?>


 	</table>
	</div>
  
  <script type="text/javascript">
  function findValue(li) {
  	if( li == null ) return alert("No match!");

  	// if coming from an AJAX call, let's use the CityId as the value
  	if( !!li.extra ) var sValue = li.extra[0];

  	// otherwise, let's just display the value in the text box
  	else var sValue = li.selectValue;
    var values =  sValue.split('~');

  	//alert("The value you selected was: " + sValue);
    document.getElementById('biaya_nama').value=values[0];
    document.getElementById('id_biaya').value=values[1];
    document.getElementById('biaya_nama').focus();
  }

  function selectItem(li) {
    	findValue(li);
  }

  function formatItem(row) 
  {
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('biaya_nama').value=alamat[0];
  document.getElementById('id_biaya').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>";
     
  }
  
  //-------------------ICD 2
  
    function findValue2(li) {
  	if( li == null ) return alert("No match!");

  	// if coming from an AJAX call, let's use the CityId as the value
  	if( !!li.extra ) var sValue = li.extra[0];

  	// otherwise, let's just display the value in the text box
  	else var sValue = li.selectValue;
    var values =  sValue.split('~');

  	//alert("The value you selected was: " + sValue);
    document.getElementById('icd_nama2').value=values[0];
    document.getElementById('id_icd2').value=values[1];
    document.getElementById('icd_nomor2').focus();
  }
  
    function selectItem2(li) {
    	findValue2(li);
  }

  function formatItem2(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('icd_nama2').value=alamat[0];
  document.getElementById('id_icd2').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";
     
  }
  
   //-------------------ICD 3
  
    function findValue3(li) {
  	if( li == null ) return alert("No match!");

  	// if coming from an AJAX call, let's use the CityId as the value
  	if( !!li.extra ) var sValue = li.extra[0];

  	// otherwise, let's just display the value in the text box
  	else var sValue = li.selectValue;
    var values =  sValue.split('~');

  	//alert("The value you selected was: " + sValue);
    document.getElementById('icd_nama3').value=values[0];
    document.getElementById('id_icd3').value=values[1];
    document.getElementById('icd_nomor3').focus();
  }
  
    function selectItem3(li) {
    	findValue3(li);
  }

  function formatItem3(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('icd_nama3').value=alamat[0];
  document.getElementById('id_icd3').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";
     
  }

//--------------------END---------------------------------///

  function lookupAjax() {
    	var oSuggest = $("#CityAjax")[0].autocompleter;
      
      oSuggest.findValue();
    	return false;
  }

  function lookupLocal() {
    	var oSuggest = $("#CityLocal")[0].autocompleter;

    	oSuggest.findValue();
    	return false;
  }
  
  
    $("#CityAjax").autocomplete(
      "autocomplete.php",
      {
  			delay:10,
  			minChars:2,
  			matchSubset:1,
  			matchContains:1,
  			cacheLength:10,
  			onItemSelect:selectItem,
  			onFindValue:findValue,
  			formatItem:formatItem,
  			autoFill:true
  		}
    );
    
    $("#CityAjax2").autocomplete(
      "autocomplete.php",
      {
  			delay:10,
  			minChars:2,
  			matchSubset:1,
  			matchContains:1,
  			cacheLength:10,
  			onItemSelect:selectItem2,
  			onFindValue:findValue2,
  			formatItem:formatItem2,
  			autoFill:true
  		}
    );
    
       $("#CityAjax3").autocomplete(
      "autocomplete.php",
      {
  			delay:10,
  			minChars:2,
  			matchSubset:1,
  			matchContains:1,
  			cacheLength:10,
  			onItemSelect:selectItem3,
  			onFindValue:findValue3,
  			formatItem:formatItem3,
  			autoFill:true
  		}
    );
  
</script>
  
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
		
		</tr>
	</table>

<script>document.frmEdit.CityAjax.focus();</script>

                                                         
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
<input type="hidden" name="id_poli" value="<?php echo $_GET["id_poli"];?>"/>
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
