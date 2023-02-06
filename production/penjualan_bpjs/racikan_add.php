<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();


//logistik item
          $dbTable = "logistik.logistik_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_nama";
               $dbField[2] = "id_dep";
               $dbField[3] = "item_racikan";
               $dbField[4] = "id_satuan_beli";
               $dbField[5] = "id_satuan_jual";
               
               $itemId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["racikan_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId); 
               $dbValue[3] = QuoteValue(DPE_CHAR,'y');
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_satuan_beli"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_satuan_jual"]);
                              
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

               $dtmodel->Insert() or die("insert  error"); 
                  
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
//input batch
              $dbTable = "logistik.logistik_item_batch";
               
               $dbField[0] = "batch_id";   // PK
               $dbField[1] = "id_item";
               $dbField[2] = "batch_no";
               $dbField[3] = "batch_create";
               $dbField[4] = "id_dep";
               $dbField[5] = "batch_flag";
               
               $batchId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$itemId); 
               $dbValue[2] = QuoteValue(DPE_CHAR,''); 
               $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));    
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[5] = QuoteValue(DPE_CHAR,'A');
              
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
                        
                        $date = date("Y-m-d H:i:s");
                        $stokid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'A');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_awal"])); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

$dbTable = "logistik.logistik_stok_dep";
                      $dbField[0]  = "stok_dep_id";   // PK
                      $dbField[1]  = "stok_dep_saldo";
                      $dbField[2]  = "id_item";    
                      $dbField[3]  = "id_gudang";
                      $dbField[4]  = "stok_dep_tgl";
                      $dbField[5]  = "stok_dep_create";         
                      $dbField[6]  = "id_dep";
                      
                      $date = date("Y-m-d H:i:s");
                      $stokdepid = $dtaccess->GetTransID();
                      $dbValue[0] = QuoteValue(DPE_CHAR,$stokdepid);
                      $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_awal"]));  
                      $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
                      $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
                      $dbValue[5] = QuoteValue(DPE_DATE,$date); 
                      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                      
                      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
            
                      $dtmodel->Insert() or die("insert  error"); 
                      
                      unset($dbTable);
                      unset($dbField);
                      unset($dbValue);
                      unset($dbKey); 

                      $dbTable = "logistik.logistik_stok_item_batch";
                        $dbField[0]  = "stok_item_batch_id";   // PK
                        $dbField[1]  = "stok_item_batch_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_batch_flag";
                        $dbField[5]  = "stok_item_batch_create";         
                        $dbField[6]  = "stok_item_batch_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_batch";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokbatchid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'A');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_awal"])); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$batchId);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

                         $dbTable = "logistik.logistik_stok_batch_dep";
                      $dbField[0]  = "stok_batch_dep_id";   // PK
                      $dbField[1]  = "stok_batch_dep_saldo";
                      $dbField[2]  = "id_item";    
                      $dbField[3]  = "id_gudang";
                      $dbField[4]  = "stok_batch_dep_tgl";
                      $dbField[5]  = "stok_batch_dep_create";         
                      $dbField[6]  = "id_dep";
                      $dbField[7]  = "id_batch";
                      
                      $date = date("Y-m-d H:i:s");
                      $stokbatchdepid = $dtaccess->GetTransID();
                      $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchdepid);
                      $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_awal"]));  
                      $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
                      $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
                      $dbValue[5] = QuoteValue(DPE_DATE,$date); 
                      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                      $dbValue[7] = QuoteValue(DPE_CHAR,$batchId);
                      
                      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
            
                      $dtmodel->Insert() or die("insert  error"); 
                      
                      unset($dbTable);
                      unset($dbField);
                      unset($dbValue);
                      unset($dbKey); 

?>     