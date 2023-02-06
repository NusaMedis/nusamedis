<?php
require_once("penghubung.inc.php");
require_once($ROOT."lib/login.php");
require_once($ROOT."lib/bit.php");
require_once($ROOT."lib/encrypt.php");
require_once($ROOT."lib/datamodel.php");
require_once($ROOT."lib/dateLib.php");
require_once($ROOT."lib/expAJAX.php");
require_once($ROOT."lib/currency.php");
require_once($ROOT."lib/tree.php");
require_once($ROOT."lib/tampilan.php"); 
  
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();
	   $usrId = $auth->GetUserId();
	   $depLowest = $auth->GetDepLowest();
     $err_code = 0;
     
      // cek konfigurasi --
/*	   $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     if($gudang["conf_gudang_obat"]=='L'){
          $_POST["id_dep"] = "1";
     } */
    
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;

     if(!$_POST["klinik"]) $_POST["klinik"] = $depId;     
     /*else{
          $_POST["id_dep"] = "5";
     }*/
     
    /* if(!$auth->IsAllowed("man_import_pasien",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_import_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
       $tree = new CTree("global.global_customer_user","cust_usr_id", TREE_LENGTH);
     
     $delimiter = $_POST["delimiter"];
  //   $delimiter = ";";
     $startLine = 1;
	   
     
     if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
     else $csvFile = $ROOT."temp/";
	   // echo $csvFile;
	   // die();
	  $plx = new expAJAX();  
	  
	   $sql = "select * from logistik.logistik_grup_item order by grup_item_nama ";
     $rs = $dtaccess->Execute($sql);
     $dataKat = $dtaccess->FetchAll($rs);
     
    $sql = "select * from klinik.klinik_kategori_tindakan order by kategori_tindakan_nama ";
     $rs = $dtaccess->Execute($sql);
     $dataKatTind = $dtaccess->FetchAll($rs);
	  
	                 // --- buat nge check --- 
              $sql = "select item_id, item_nama ,item_stok
              from logistik.logistik_item order by item_nama ";                 
              $rs = $dtaccess->Execute($sql);

          while($row = $dtaccess->Fetch($rs)) {
          $barang[$row["item_nama"]] = $row["item_id"];
     }

     if(isset($_POST["btnNext"])){          
          
          if($_FILES["csv_file"]["tmp_name"]){
               $err = false;
          } else {
               $err=true;
          }
         
          if(!$err){
               if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
                    $csvFile .= $_FILES["csv_file"]["name"];
                    copy($_FILES["csv_file"]["tmp_name"], $csvFile);
               }
          }
          
          if ((!$myFile = @fopen(stripslashes($csvFile), "r")) || $err==true) {
               $err = true;
          } else {
          
               // --- buat nge check data e uda ada ga ---
               $sql = "select item_id, item_nama ,item_stok
              from logistik.logistik_item";                 
              $rs = $dtaccess->Execute($sql);
               
                  //reset($barang);        
               while ($data = fgetcsv($myFile, 500000, $delimiter)) {
                    //echo $data[0]."&nbsp;".$data[1]."&nbsp;".$data[2]."&nbsp;".$data[3]."&nbsp;".$data[4]."<br />";
                    
      if($data[0] || $data[1] || $data[2] || $data[3] || $data[4] || $data[5] || $data[6] 
          || $data[7] || $data[8] || $data[9] || $data[10] || $data[11] || $data[12] || $data[13] || $data[14]) {
                                                       
         // KONFIGURASI
      $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);
    	
    	$sql = "select satuan_id from logistik.logistik_item_satuan where upper(satuan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($data[5]))."
              and satuan_tipe='B'";
    	$rs = $dtaccess->Execute($sql);
    	$satuanBeli = $dtaccess->Fetch($rs);
 
      if(!$satuanBeli)
      {
               $dbTable = "logistik.logistik_item_satuan";
               
               $dbField[0] = "satuan_id";   // PK
               $dbField[1] = "satuan_nama";
			         $dbField[2] = "satuan_tipe";
			         $dbField[3] = "satuan_jumlah";
               $dbField[4] = "id_dep";
               
               $satuanId = $dtaccess->GetTransId();
               $satuanBeli["satuan_id"] = $satuanId;
                  
               $dbValue[0] = QuoteValue(DPE_CHAR,$satuanBeli["satuan_id"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($data[5])); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,'B'); 
			         $dbValue[3] = QuoteValue(DPE_NUMERIC,1);  
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 	

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               $dtmodel->Insert() or die("insert  error");	
               
               unset($dbTable);   
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);


      }
    	
    	$sql = "select satuan_id from logistik.logistik_item_satuan where upper(satuan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($data[6]))."
              and satuan_tipe='J'";
    	$rs = $dtaccess->Execute($sql);
    	$satuanJual = $dtaccess->Fetch($rs);

      if(!$satuanJual)
      {
               $dbTable = "logistik.logistik_item_satuan";
               
               $dbField[0] = "satuan_id";   // PK
               $dbField[1] = "satuan_nama";
			         $dbField[2] = "satuan_tipe";
			         $dbField[3] = "satuan_jumlah";
               $dbField[4] = "id_dep";
               
               $satuanJualId = $dtaccess->GetTransId();
               $satuanJual["satuan_id"] = $satuanJualId;
                  
               $dbValue[0] = QuoteValue(DPE_CHAR,$satuanJual["satuan_id"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($data[6])); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,'J'); 
			         $dbValue[3] = QuoteValue(DPE_NUMERIC,1);  
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 	

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               $dtmodel->Insert() or die("insert  error");	
               
               unset($dbTable);   
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);


      }

    	$sql = "select grup_item_id from logistik.logistik_grup_item where upper(grup_item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($data[11]))."
              and item_flag='M'";
    	$rs = $dtaccess->Execute($sql);
    	$groupItem = $dtaccess->Fetch($rs);

      if(!$groupItem)
      {
               $dbTable = "logistik.logistik_grup_item";
               
               $dbField[0] = "grup_item_id";   // PK
               $dbField[1] = "grup_item_nama";
			         $dbField[2] = "item_flag";
			         $dbField[3] = "id_dep";
               
               $groupItemId = $dtaccess->GetTransId();
               $groupItem["grup_item_id"] = $groupItemId;
                  
               $dbValue[0] = QuoteValue(DPE_CHAR,$groupItem["grup_item_id"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($data[11])); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,'M');   
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 	

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               $dtmodel->Insert() or die("insert  error");	
//              print_r($dbValue); die(); 
               unset($dbTable);   
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);


      }


      $_POST["dep_konf_no_depan"] =  $row_edit["dep_konf_no_depan"];       

    	$sql = "select item_id from logistik.logistik_item where 
              upper(item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($data[1]));
    	$rs = $dtaccess->Execute($sql);
    	$dataitem = $dtaccess->Fetch($rs);           

      if ($dataitem) 
      {
       $itemId=$dataitem["item_id"];
       

 
       
       $sql = "select * from logistik.logistik_stok_item where id_item = ".QuoteValue(DPE_CHAR,$itemId)."
                and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and stok_item_flag ='A'
                and id_dep = ".QuoteValue(DPE_CHAR,$depId);
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
       $stokitemawal = $dtaccess->Fetch($sql);
       
       if(!$stokitemawal){                     //jika tidak ada stok item awal
                    //insert stok item awal
        $dbTable = "logistik.logistik_stok_item";
              $dbField[0]  = "stok_item_id";   // PK
              $dbField[1]  = "stok_item_jumlah";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_gudang";
              $dbField[4]  = "stok_item_flag";
              $dbField[5]  = "stok_item_create";
              $dbField[6]  = "stok_item_saldo";
              $dbField[7]  = "id_dep";
              
              $stokItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
              $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_CHAR,'A');               // A adalah saldo
              $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4])); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("update  error");
              	
 
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);          
 
        }else{   //jika ada stok item awal maka diupdate
       
       $sql  ="update logistik.logistik_stok_item 
              set stok_item_jumlah =".QuoteValue(DPE_NUMERIC,StripCurrency($data[7]));
       $sql .=" , stok_item_saldo = ".QuoteValue(DPE_NUMERIC,StripCurrency($data[7]));
       $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$itemId)." and stok_item_flag ='A'";
       $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
       $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);       
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 
                      
        }
      
        $sql = "select * from logistik.logistik_stok_dep where id_item = ".QuoteValue(DPE_CHAR,$itemId)."
                and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
                id_dep = ".QuoteValue(DPE_CHAR,$depId);
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
       $stokdepawal = $dtaccess->Fetch($sql);
  
       if(!$stokdepawal){      //jika tidak ada stok dep 
                               //insert stok dep
       $dbTable = "logistik.logistik_stok_dep";
              $dbField[0]  = "stok_dep_id";   // PK
              $dbField[1]  = "id_item";
              $dbField[2]  = "stok_dep_saldo";
              $dbField[3]  = "stok_dep_create";
              $dbField[4]  = "stok_dep_tgl";    
              $dbField[5]  = "id_gudang";
              $dbField[6]  = "id_dep";
              
              $stokDepId = $dtaccess->GetTransID();
    
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokDepId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
              $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));    
              $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    			    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
              $dtmodel->Insert() or die("insert  error");	
              
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);
              
       }else{        //jika ada stok dep maka diupdate
       
        $sql  ="update logistik.logistik_stok_dep 
                set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
        $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$itemId)." and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
        $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      
       }   //akhir stok dep
                     
      } //akhir jika ada item 
      
      if (!$dataitem)
      {               
               $jumlahObat=0;   
               $dbTable = "logistik.logistik_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_nama";
			         $dbField[2] = "item_harga_beli";
			         $dbField[3] = "item_harga_jual";
			         $dbField[4] = "item_berlaku";
			         $dbField[5] = "id_satuan_beli";
			         $dbField[6] = "id_dep";
			         $dbField[7] = "id_satuan_jual";
               $dbField[8] = "item_stok";
               $dbField[9] = "item_keterangan";
               $dbField[10] = "item_tipe_jenis"; 
               $dbField[11] = "id_kategori";                                 
               $dbField[12] = "id_kategori_tindakan";
               $dbField[13] = "item_nama_generik";
               $dbField[14] = "item_kekuatan";
               $dbField[15] = "item_flag";                               
               
               $itemId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$data[1]); 
			         $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($data[2])); 
			         $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($data[3]));  
			         $dbValue[4] = QuoteValue(DPE_CHAR,$data[8]); 	
			         $dbValue[5] = QuoteValue(DPE_CHAR,$satuanBeli["satuan_id"]);
			         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
			         $dbValue[7] = QuoteValue(DPE_CHAR,$satuanJual["satuan_id"]);
			         $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
			         $dbValue[9] = QuoteValue(DPE_CHAR,$data[1]); 
			         $dbValue[10] = QuoteValue(DPE_CHAR,'2'); 
			         $dbValue[11] = QuoteValue(DPE_CHAR,$groupItem["grup_item_id"]);  
			         $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan"]); 
               $dbValue[13] = QuoteValue(DPE_CHAR,$data[9]);
               $dbValue[14] = QuoteValue(DPE_CHAR,$data[10]); 
               $dbValue[15] = QuoteValue(DPE_CHAR,'M');
                                             
               $jumlahObat=$jumlahObat+StripCurrency($data[4]);
			         //print_r($dbValue);
			         //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               $dtmodel->Insert() or die("insert  error");	
                  
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

            
              $dbTable = "logistik.logistik_stok_item";
              $dbField[0]  = "stok_item_id";   // PK
              $dbField[1]  = "stok_item_jumlah";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_gudang";
              $dbField[4]  = "stok_item_flag";
              $dbField[5]  = "stok_item_create";
              $dbField[6]  = "stok_item_saldo";
              $dbField[7]  = "id_dep";
              
              $stokItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
              $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_CHAR,'A');               // A adalah saldo
              $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4])); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("update  error");
              	
 
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel); 
              
              $dbTable = "logistik.logistik_stok_dep";
              $dbField[0]  = "stok_dep_id";   // PK
              $dbField[1]  = "id_item";
              $dbField[2]  = "stok_dep_saldo";
              $dbField[3]  = "stok_dep_create";
              $dbField[4]  = "stok_dep_tgl";    
              $dbField[5]  = "id_gudang";
              $dbField[6]  = "id_dep";
              
              $stokDepId = $dtaccess->GetTransID();
    
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokDepId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
              $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));    
              $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    			    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
              $dtmodel->Insert() or die("insert  error");	
              
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel); 
            
            
       } //AKHIR JIKA TIDAK ADA DATA ITEM     
       
        //// akhir urusan dengan logistik ITEM //////
        
        //cari batch item
              
              $sql = "select * from logistik.logistik_item_batch where batch_no =".QuoteValue(DPE_CHAR,$data[7])." 
              and id_item =".QuoteValue(DPE_CHAR,$itemId);              
              $rs = $dtaccess->Execute($sql);
            	$dataBatch = $dtaccess->Fetch($rs);
           
           
           if($dataBatch){  //jika ada batch-nya
           
           
            $sql = "select * from logistik.logistik_stok_item_batch where id_batch = ".QuoteValue(DPE_CHAR,$dataBatch["batch_id"])."
                    and id_item = ".QuoteValue(DPE_CHAR,$itemId)." and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])."
                    and stok_item_batch_flag ='A'";
            $rs = $dtaccess->Execute($sql);
            $stokBatchawal = $dtaccess->Fetch($rs);        
             
             if(!$stokBatchawal){
             
              $dbTable = "logistik.logistik_stok_item_batch";
              $dbField[0]  = "stok_item_batch_id";   // PK
              $dbField[1]  = "stok_item_batch_jumlah";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_batch";
              $dbField[4]  = "id_dep";
              $dbField[5]  = "stok_item_batch_flag";
              $dbField[6]  = "stok_item_batch_create";
              $dbField[7]  = "stok_item_batch_saldo";              
              $dbField[8]  = "id_gudang";
                          
              $stokBatchItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchItemId);
              $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$dataBatch["batch_id"]);  
              $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]);              
              $dbValue[5] = QuoteValue(DPE_CHAR,'A');  
			        $dbValue[6] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
                            
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);         
              $dtmodel->Insert() or die("update  error");              	
               
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);
             
             }else{
             
             $sql = " update logistik.logistik_stok_item_batch set stok_item_batch_saldo = ".QuoteValue(DPE_NUMERIC,StripCurrency($data[4])).", 
                      stok_item_batch_jumlah = ".QuoteValue(DPE_NUMERIC,StripCurrency($data[4]))."
                      where id_batch = ".QuoteValue(DPE_CHAR,$dataBatch["batch_id"])."
                    and id_item = ".QuoteValue(DPE_CHAR,$itemId)." and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])."
                    and stok_item_batch_flag ='A'";
             $rs = $dtaccess->Execute($sql);
                    
             }   //akhir stok_batch per gudang
             
              $sql = "select * from logistik.logistik_stok_batch_dep where id_batch = ".QuoteValue(DPE_CHAR,$dataBatch["batch_id"])."
                    and id_item = ".QuoteValue(DPE_CHAR,$itemId)." and id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
            $rs = $dtaccess->Execute($sql);
            $stokBatchdep = $dtaccess->Fetch($rs);
            
            if(!$stokBatchdep) {
              
              $dbTable = "logistik.logistik_stok_batch_dep";
              $dbField[0]  = "stok_batch_dep_id";   // PK
              $dbField[1]  = "id_item";
              $dbField[2]  = "id_batch";    
              $dbField[3]  = "stok_batch_dep_saldo";
              $dbField[4]  = "stok_batch_dep_create";
              $dbField[5]  = "stok_batch_dep_tgl";
              $dbField[6]  = "id_dep";
              $dbField[7]  = "id_gudang";              
                          
              $stokBatchDepId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchDepId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[2] = QuoteValue(DPE_CHAR,$dataBatch["batch_id"]);              
              $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));  
              $dbValue[4] = QuoteValue(DPE_CHAR,date_db($_POST["tanggal_awal"]));              
              $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));  
			        $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
                            
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);         
              $dtmodel->Insert() or die("update  error");              	
               
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);

            
            }else{
            
            $sq = " update logistik.logistik_stok_batch_dep set 
                    stok_batch_dep_saldo = ".QuoteValue(DPE_CHAR,date_db($_POST["tanggal_awal"]))."
                    ,stok_batch_dep_tgl = ".QuoteValue(DPE_CHAR,date_db($_POST["tanggal_awal"])).",
                    stok_dep_create = ".QuoteValue(DPE_CHAR,date_db($_POST["tanggal_awal"]))."
                    where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." 
                    and id_batch =".QuoteValue(DPE_CHAR,$dataBatch["batch_id"])." and 
                    id_item = ".QuoteValue(DPE_CHAR,$itemId);
             $rs = $dtaccess->Execute($sql);       
            
            } //akhir batch dep per gudang
           
           }else{   //jika batch tidak ada maka diinsert
           
           $dbTable = "logistik.logistik_item_batch";
              $dbField[0]  = "batch_id";   // PK
              $dbField[1]  = "batch_no";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_gudang";
              $dbField[4]  = "batch_flag";
              $dbField[5]  = "batch_create";
              $dbField[6]  = "batch_stok_saldo";
              $dbField[7]  = "id_dep";
              $dbField[8]  = "batch_tgl_jatuh_tempo";
              
              $batchItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$batchItemId);
              $dbValue[1] = QuoteValue(DPE_CHAR,($data[7]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_CHAR,'A');               // A adalah saldo
              $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4])); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[8] = QuoteValue(DPE_DATE,date_db($data[8]));
                            
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("update  error");              	
               
     
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);                           
              unset($dtmodel);
                                              
              $dbTable = "logistik.logistik_stok_item_batch";
              $dbField[0]  = "stok_item_batch_id";   // PK
              $dbField[1]  = "stok_item_batch_jumlah";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_batch";
              $dbField[4]  = "id_dep";
              $dbField[5]  = "stok_item_batch_flag";
              $dbField[6]  = "stok_item_batch_create";
              $dbField[7]  = "stok_item_batch_saldo";              
              $dbField[8]  = "id_gudang";
                          
              $stokBatchItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchItemId);
              $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$batchItemId);  
              $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]);              
              $dbValue[5] = QuoteValue(DPE_CHAR,'A');  
			        $dbValue[6] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
              $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));
              $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
                            
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);         
              $dtmodel->Insert() or die("update  error");              	
               
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);
              
              $dbTable = "logistik.logistik_stok_batch_dep";
              $dbField[0]  = "stok_batch_dep_id";   // PK
              $dbField[1]  = "id_item";
              $dbField[2]  = "id_batch";    
              $dbField[3]  = "stok_batch_dep_saldo";
              $dbField[4]  = "stok_batch_dep_create";
              $dbField[5]  = "stok_batch_dep_tgl";
              $dbField[6]  = "id_dep";
              $dbField[7]  = "id_gudang";              
                          
              $stokBatchDepId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchDepId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[2] = QuoteValue(DPE_CHAR,$batchItemId);              
              $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($data[4]));  
              $dbValue[4] = QuoteValue(DPE_CHAR,date_db($_POST["tanggal_awal"]));              
              $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));  
			        $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
                            
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);         
              $dtmodel->Insert() or die("update  error");              	
               
              unset($dbTable);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);  
              unset($dtmodel);
          
                }  //akhir batch gk ada                 
                         
                         } else {
                              $error[] = $startLine;
                         }
                    
                    
                    $startLine ++;
               }
          }
          
          if($error) $error = implode("<br>Data Excel Baris ke ",$error);
     } 
     
      if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     } 
     
      $sql = "select * from logistik.logistik_gudang where id_dep = ".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataGudang = $dtaccess->FetchAll($rs);

?>
<br /><br /><br /><br />
<?php echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">

<?php $plx->Run();?>

$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '60%',
'height' : '110%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<div id="bodyku">
<br />
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">

<fieldset>
     <legend><strong>Import Item</strong></legend>
     <table width="80%" border="0" cellpadding="1" cellspacing="1">
     <tr class="tablecontent" align="center">
          <td width="40%" align="right" class="tablecontent">&nbsp;&nbsp;Klinik&nbsp;&nbsp;</td>
          <td width="60%" align="left" class="tablecontent">
			 <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--">- Semua Klinik -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
				?>
					<option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
				<?php if (!$_POST["klinik"]) {?>
			         <?php echo "<font color='red'>Harap Pilih Klinik terlebih Dahulu </font>"; ?>
               <?php } ?>
		  </td>
		 </tr>
    <tr>
          		 <td width="25%" align="right" class="tablecontent">&nbsp;&nbsp;  Pemisah Delimiter&nbsp;</td>
               <td width="75%" class="tblCol">
               <select name="delimiter" id="delimiter" class="inputField" onKeyDown="return tabOnEnter_select(this, event);" onChange="this.form.submit();">
               <option class="inputField" value="," <?php if($_POST["delimiter"]==",") echo "selected"; ?>>Koma (,)</option>
               <option class="inputField" value=";" <?php if($_POST["delimiter"]==";") echo "selected"; ?>>Titik Koma (;)</option>
			      	 </td>
                
          </tr>
           <tr>
          		 <td width="25%" align="right" class="tablecontent">&nbsp;&nbsp;  Kategori Tindakan&nbsp;</td>
               <td width="75%" class="tblCol">
               <select name="id_kategori_tindakan" id="id_kategori_tindakan" class="inputField" onKeyDown="return tabOnEnter_select(this, event);" onChange="this.form.submit();">
				       <option class="inputField" value="<?php if ($_POST["id_kategori_tindakan"]=="--") echo"selected"?>">- Pilih Kategori -</option>
			      	 <?php for($i=0,$n=count($dataKatTind);$i<$n;$i++){ ?>
				    	 <option class="inputField" value="<?php echo $dataKatTind[$i]["kategori_tindakan_id"];?>" <?php if($dataKatTind[$i]["kategori_tindakan_id"]==$_POST["id_kategori_tindakan"]) echo "selected"; ?>><?php echo $dataKatTind[$i]["kategori_tindakan_nama"];?></option>
			       	 <?php } ?>
			         </select>
			         <?php if ($_GET["pilih"]) {?>
			         <?php echo "<font color='red' size='4'>Harap Pilih Kategori terlebih Dahulu </font>"; ?>
               <?php } ?>
               </td>
                
          </tr>
           <tr>
          		 <td width="25%" align="right" class="tablecontent">&nbsp;&nbsp;  Nama Gudang&nbsp;</td>
               <td width="75%" class="tblCol">
               <select name="id_gudang" id="id_gudang" class="inputField" onKeyDown="return tabOnEnter_select(this, event);" onChange="this.form.submit();">
				       <option class="inputField" value="<?php if ($_POST["id_gudang"]=="--") echo"selected"?>">- Pilih Gudang -</option>
			      	 <?php for($i=0,$n=count($dataGudang);$i<$n;$i++){ ?>
				    	 <option class="inputField" value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($dataGudang[$i]["gudang_id"]==$_POST["id_gudang"]) echo "selected"; ?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
			       	 <?php } ?>
			         </select>
			         <?php if ($_GET["pilih"]) {?>
			         <?php echo "<font color='red' size='4'>Harap Pilih Kategori terlebih Dahulu </font>"; ?>
               <?php } ?>
               </td>
                
          </tr>

     <tr>
          <td align="right" width="15%" class="tablecontent">&nbsp;Tanggal Saldo Awal</td>
          <td class="tablecontent" colspan="4">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
         </td>
         </tr> 
          <tr>
               <td width="25%" align="right" class="tablecontent"><strong>CSV File<?php if($err){?> <font color="red">(*)</font><?php } ?>&nbsp;</strong></td>
               <td width="75%" class="tblCol">
                    <input type="file" name="csv_file" size=25 class="submit">
                    <span id="div_uh"></span>
                    <input type="submit" name="btnNext" value="Proses" class="submit" OnClick="document.frmEdit.btnNext.value = 'Please Wait'">
               </td>
          </tr>
     </table>
</fieldset>
<br />
<font size="3" color="red">
&nbsp;&nbsp;Data yang harus diisi :
<br /><br />
</font>
<font size="2" color="red">
&nbsp;&nbsp;Kolom 1  : Nomer <br />
&nbsp;&nbsp;Kolom 2  : Nama Barang <br />
&nbsp;&nbsp;Kolom 3  : Harga Beli <br />
&nbsp;&nbsp;Kolom 4  : Harga Jual <br />
&nbsp;&nbsp;Kolom 5  : Stok <br /> 
&nbsp;&nbsp;Kolom 6  : Satuan Beli<br />
&nbsp;&nbsp;Kolom 7  : Satuan Jual<br />
&nbsp;&nbsp;Kolom 8  : Nomer Batch<br />
&nbsp;&nbsp;Kolom 9  : Tgl Expire(2015-05-31)<br />
&nbsp;&nbsp;Kolom 10 : Nama Generik<br />
&nbsp;&nbsp;Kolom 11 : Kekuatan<br />
&nbsp;&nbsp;Kolom 12 : Kategori Barang<br />
<br />
<br />
&nbsp;&nbsp;Hapus semua baris yang tidak diperlukan untuk import (misal baris paling atas jika ada header kolomnya)<br />
&nbsp;&nbsp;Untuk Contoh Excel data silahkan klik <a href="obat.xlsx">disini</a><br />
&nbsp;&nbsp;Untuk Contoh CSV data silahkan klik <a href="obat.csv">disini</a><br />
&nbsp;&nbsp;Pemisah antar kolom adalah tanda &nbsp; ;<br />


</font>
</form>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
</script>
<?php if($_POST["btnNext"] && !$err) {?>
    <font style="font-size:14px">Proses Import Sudah Selesai</font>
<?php }?>

<?php if($err){?><label><font color="red" style="font-size:12px; font-weight:bold;">&nbsp;Pilih File yang akan di Import Terlebih Dahulu</font></label><?php } ?>

<?php if($error) {?>
     <br /><br />
     <font color="red">
          Ada Beberapa Data yang tidak Valid<br />
          <?php echo "Data Excel Baris ke ".$error;?>
     </font>
<?php }?>
<!--------Buat Helpicon----------->
</div>
