<?php
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."bit.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."expAJAX.php");
require_once($LIB."currency.php");
require_once($LIB."tampilan.php"); 
  
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
    
    if($_GET["tahun"]) $_POST["tahun"]=$_GET["tahun"];
     
     if(!$_POST["tahun"]) $_POST["tahun"]=date('Y');
     
    $plx = new expAJAX("GetPeriode");
     
     function GetPeriode($thn){
        global $dtaccess,$view,$depId,$ROOT; 
         $sql = "select * from logistik.logistik_penerimaan_periode where extract(year from penerimaan_periode_tanggal_awal)=".QuoteValue(DPE_CHAR,$thn)." 
                order by penerimaan_periode_tanggal_awal asc";
         $rs = $dtaccess->Execute($sql); 
         $dataPeriode = $dtaccess->FetchAll($rs);
         //echo $sql;
          unset($periode);
          $periode[0] = $view->RenderOption("","[Pilih Periode]",$show);
          $i = 1;
          
         for($i=0,$n=count($dataPeriode);$i<$n;$i++){   
             if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) $show = "selected";
             $periode[$i+1] = $view->RenderOption($dataPeriode[$i]["penerimaan_periode_id"],$dataPeriode[$i]["penerimaan_periode_nama"],$show);
             unset($show);
         }
          $str = $view->RenderComboBox("id_periode","id_periode",$periode,"required='required'",null,null);
       return $str;
     }

     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;

     //if(!$_POST["klinik"]) $_POST["klinik"] = $depId;     
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
     
     $delimiter = $_POST["delimiter"];
  //   $delimiter = ";";
     $startLine = 1;
	   
     
     if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
     else $csvFile = $ROOT."temp/";
	   // echo $csvFile;
	   // die(); 
	   
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
                    //echo $data[0]."&nbsp;".$data[1]."&nbsp;".$data[1]."&nbsp;".$data[3]."&nbsp;".$data[4]."<br />";
                    
      if($data[0] || $data[1] || $data[2] || $data[3] || $data[4]) {
        //print_r("0 ".$data[0]."<br>".$data[1]."<br>".$data[2]); die();

        //proses data disini.....
        // INSERT LOGISTIK OPNAME
        //echo $depId; die();

     $tglcreate = date_db($_POST["opname_tanggal"])." ".date('H:i:s');
     $dbTable = "logistik.logistik_opname";
     
     $dbField[0] = "opname_id";   // PK
     $dbField[1] = "opname_tanggal";
     $dbField[2] = "id_dep";
     $dbField[3] = "id_gudang";
     $dbField[4] = "id_periode";
     $dbField[5] = "opname_flag";
          
    $opnameId = $dtaccess->GetTransID();
    
     $dbValue[0] = QuoteValue(DPE_CHAR,$opnameId);
     $dbValue[1] = QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]));
     $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
     $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     $dbValue[5] = QuoteValue(DPE_CHAR,'M');

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

     $dtmodel->Insert() or die("insert  error");
     //print_r($dbValue);

     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey); 


    // echo date_db($_POST["tgl_awal"])." <br /> tahun ".$tahunopname." <br /> ".$bulanopname;
      //ambil data item
     /*$sql = "select id_item from logistik.logistik_item_batch where batch_id=".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
     $rsItem = $dtaccess->Execute($sql);
     $dataItemS = $dtaccess->Fetch($rsItem); 

     //cari stok item batch saldo sebelumnya
     $sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i])." and 
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and 
             date(stok_item_batch_create) < ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_batch_create desc";
      $rs = $dtaccess->Execute($sql);
      $datastokbatchsebelumOpname = $dtaccess->Fetch($rs);
   //   echo $sql;
     if($_POST["stokHandBatch".$i]!=$datastokbatchsebelumOpname["stok_item_batch_saldo"]){
     //cari berdasarkan opnamenya
     $sql = "select stok_item_batch_id from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i])."
             and id_opname = ".QuoteValue(DPE_CHAR,$opnameId)." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $rs = $dtaccess->Execute($sql);
     $stokbatchopname =$dtaccess->Fetch($rs);
        $selisihBatch = Stripcurrency($_POST["stokHandBatch".$i])-$datastokbatchsebelumOpname["stok_item_batch_saldo"];
 //  echo "selisih batch transaksi ".$selisihBatch."<br>"; //die();  
      //masukkan ke stok item batch:: Keterangan Opname Selisih
      $dbTable = "logistik.logistik_stok_item_batch";
      $dbField[0]  = "stok_item_batch_id";   // PK
      $dbField[1]  = "stok_item_batch_jumlah";
      $dbField[2]  = "id_item";    
      $dbField[3]  = "id_gudang";
      $dbField[4]  = "stok_item_batch_flag";
      $dbField[5]  = "stok_item_batch_create";
      $dbField[6]  = "stok_item_batch_saldo";
      $dbField[7]  = "stok_item_keterangan";
      $dbField[8]  = "id_dep";
      $dbField[9]  = "id_batch";
      $dbField[10]  = "id_opname";      
      
      if($stokbatchopname) $stokItemBatchId = $stokbatchopname["stok_item_batch_id"];
      else
      $stokItemBatchId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemBatchId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,$selisihBatch);
      $dbValue[2] = QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $dbValue[4] = QuoteValue(DPE_CHAR,'O');
      $dbValue[5] = QuoteValue(DPE_DATE,$tglcreate);
      $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokHandBatch".$i]));      
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["stokKet".$i]);     
      $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
      $dbValue[10] = QuoteValue(DPE_CHAR,$opnameId);
//      echo "STOK ITEM BATCH"; print_r($dbValue); //die();           
      $dbKey[0]   = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel    = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
      if($stokbatchopname)      $dtmodel->Update() or die("update  error");
      else
      $dtmodel->Insert() or die("insert  error");
        
      unset($dbField);
      unset($dbValue);
             
      }        
      // Update stok item batch dep //
      //$stokHandz[$i]  =  number_format($_POST["stokHandBatch".$i]);
      $stokHandz[$i]  =  StripCurrency($_POST["stokHandBatch".$i]);
      
          $sql = "select * from logistik.logistik_stok_item_batch";
           $sql .= " where id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
           $sql .= " and id_batch = ".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
     //      $sql .= " and date(stok_item_batch_create) >= ".QuoteValue(DPE_DATE,$dataOpnameTgl["opname_tanggal"]);           
           $sql .= " order by id_gudang asc, stok_item_batch_create asc";
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataAdjustmentBatch = $dtaccess->FetchAll($rs);
 //       echo $sql;
          for($ms=0,$os=count($dataAdjustmentBatch);$ms<$os;$ms++)
           {
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='A') //Saldo Awal
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='PP') //Pemakaian
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='T' && $dataAdjustmentBatch[$ms]["id_dep_tujuan"]==null) //Transfer Penerimaan
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='T' && $dataAdjustmentBatch[$ms]["id_dep_tujuan"]!=null) //Transfer Keluar
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='B') //Pembelian
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='P') //Penjualan
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='O') //Opname
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='K') //Retur Pembelian
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='L' && $dataAdjustmentBatch[$ms]["id_dep_tujuan"]==null) //Retur ke Gudang Penerimaan
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='L' && $dataAdjustmentBatch[$ms]["id_dep_tujuan"]!=null) //Retur ke Gudang Keluar
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];  
             if ($dataAdjustmentBatch[$ms]["stok_item_batch_flag"]=='M') //Retur Penjualan
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$ms]["stok_item_batch_jumlah"];    
      
        $sql  ="update logistik.logistik_stok_item_batch 
                set stok_item_batch_saldo=".$saldoBatch." 
                where stok_item_batch_id =".QuoteValue(DPE_CHAR,$dataAdjustmentBatch[$ms]["stok_item_batch_id"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);                

          }
          
          $sql  ="update logistik.logistik_stok_batch_dep 
          set stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldoBatch));
          $sql .=" where id_batch = ".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
        //     echo $sql; //   die();

                
       unset($saldoBatch);       //}

     //cari data stok batch untuk stok awal bulan berikutnya jika ada update jika tidak insert
     $sql = "select * from logistik.logistik_stok_batch_dep_periode 
              where id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])." and
              id_batch = ".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i])." and
              stok_batch_dep_periode_tgl >= '".$awalbulan."'  and
              stok_batch_dep_periode_tgl <= '".$akhitrbulan."' and 
              id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    $rs = $dtaccess->Execute($sql);
    $dataStokBatchPeriode = $dtaccess->Fetch($rs);
    
           $dbTable = "logistik.logistik_stok_batch_dep_periode";
           
          $dbField[0]  = "stok_batch_dep_periode_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_batch_dep_periode_saldo";
          $dbField[3]  = "stok_batch_dep_periode_create";
          $dbField[4]  = "stok_batch_dep_periode_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";
          $dbField[8]  = "id_periode";
                    
          if($dataStokBatchPeriode){ 
          $stokbatchdepPerId = $dataStokBatchPeriode["stok_batch_dep_periode_id"]; }else{ 
          $stokbatchdepPerId = $dtaccess->GetTransID(); }
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchdepPerId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataItemS["id_item"]);//QuoteValue(DPE_NUMERIC,number_format($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokHandBatch".$i]));    
          $dbValue[3] = QuoteValue(DPE_DATE,$tglcreate);
          $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_periode"]);
   //   echo "<br>logistik_stok_batch_dep_periode"; print_r($dbValue);     
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          if($dataStokBatchPeriode){
          $dtmodel->Update() or die("update  error");          
          }else{
          $dtmodel->Insert() or die("insert  error"); 
          }
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);*/
                   
     //$data[3] = number_format($data[3]);     ||     data stok sebelum ada batch//
       // sum data stok di item batch //
     //cari stok item batch saldo sebelumnya
     $sql = "select stok_item_saldo from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$data[1])." and 
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_create) < ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_create desc";
      $rs = $dtaccess->Execute($sql);
      $datastoksebelumOpname[$i] = $dtaccess->Fetch($rs);
      //echo $sql; die();
     /*$sql = "select sum(b.stok_batch_dep_saldo) as total from logistik.logistik_item_batch a
               left join logistik.logistik_stok_batch_dep b on a.batch_id = b.id_batch
               where b.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and a.id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])." 
               and a.id_dep =".QuoteValue(DPE_CHAR,$depId); 
     $dataStokBatch = $dtaccess->Fetch($sql);*/
     $sql = "select sum(b.stok_dep_saldo) as total from logistik.logistik_item a
               left join logistik.logistik_stok_dep b on a.item_id = b.id_item
               where b.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and b.id_item = ".QuoteValue(DPE_CHAR,$data[1])." 
               and a.id_dep =".QuoteValue(DPE_CHAR,$depId); 
     $dataStokBatch = $dtaccess->Fetch($sql);     
       
     $_POST["stokRealtot".$i] = StripCurrency($dataStokBatch["total"]);
// echo  "<br>stok real input".$_POST["stokRealtot".$i];
    
     //selisih real - tercatat
     //$stokInt[$i] = $_POST["stokRealtot".$i]-$datastoksebelumOpname[$i]["stok_item_saldo"];      
     if($datastoksebelumOpname[$i]["stok_item_saldo"]>=0){
     $stokInt[$i] = StripCurrency($data[3])-$datastoksebelumOpname[$i]["stok_item_saldo"];
     } else {
     $stokInt[$i] = StripCurrency($data[3])+abs($datastoksebelumOpname[$i]["stok_item_saldo"]);
     }
     //echo $stokInt[$i]; die();
      //echo "<br> stok item saldo selisih ".$stokInt[$i]."<br>".$data[3]; die();
     //Jika item ini stok sebelumnya dan stok opname sama tidak di proses
     //dianggap itemnya tidak di opname  -- Start
 //echo "<br>selisih stok real input".$stokInt[$i];
// echo $sql; //die();   
     //if($stokInt[$i]<>0){     

     //cari berdasarkan opnamenya
     $sql = "select stok_item_id from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$data[1])."
             and id_opname = ".QuoteValue(DPE_CHAR,$opnameId)." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $rs = $dtaccess->Execute($sql);
     $stokitemopname =$dtaccess->Fetch($rs);
      
      //masukkan ke stok item :: Keterangan Opname Selisih
      $dbTable = "logistik.logistik_stok_item";
      $dbField[0]  = "stok_item_id";   // PK
      $dbField[1]  = "stok_item_jumlah";
      $dbField[2]  = "id_item";    
      $dbField[3]  = "id_gudang";
      $dbField[4]  = "stok_item_flag";
      $dbField[5]  = "stok_item_create";
      $dbField[6]  = "stok_item_saldo";
      $dbField[7]  = "stok_item_keterangan";
      $dbField[8]  = "id_dep";
      $dbField[9]  = "id_opname";         
      
      $stokItemId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,$stokInt[$i]);
      $dbValue[2] = QuoteValue(DPE_CHAR,$data[1]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $dbValue[4] = QuoteValue(DPE_CHAR,'O');
      $dbValue[5] = QuoteValue(DPE_DATE,$tglcreate);
      $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($data[3]));      
      $dbValue[7] = QuoteValue(DPE_CHAR,$data[4]);     
      $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$opnameId);
      //echo "STOK ITEM"; print_r($dbValue); die();
            
      $dbKey[0]   = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel    = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      if($stokitemopname)      $dtmodel->Update() or die("update  error");
      else
      $dtmodel->Insert() or die("insert  error");
        
      unset($dbField);
      unset($dbValue);
 //      }
      
      //cek di stok_dep ada item nya apa ga , jika ga ada maka di input jika ada update
     $sql = "select id_item from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $sql .="and id_item =".QuoteValue(DPE_CHAR,$data[1]);
     $sql .="order by stok_dep_create desc"; 
     $rs = $dtaccess->Execute($sql);
     $dataDep = $dtaccess->Fetch($rs);
     //if($data[1]=="c3ae3a0364c0e97e0b1bf54252bbeac3") echo $sql; die();
    
          if(!$dataDep){         
          $dbTable = "logistik.logistik_stok_dep";
          $dbField[0]  = "stok_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_dep_saldo";
          $dbField[3]  = "stok_dep_create";
          $dbField[4]  = "stok_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          
          $StokdepId = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$StokdepId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$data[1]);//QuoteValue(DPE_NUMERIC,number_format($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($data[3]));    
          $dbValue[3] = QuoteValue(DPE_DATE,$tglcreate);
          $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Insert() or die("insert  error"); 
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
                    
          }else{
    
          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($data[3]));
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$data[1]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          //echo $sql; die();              
          }
          
             //jika item ini stok sebelumnya dan stok selanjutnya sama tidak di proses -- end
        //masukkan stok akhir stok opname ke dalam tabel agar bisa jadi stok awal berikutnya
     //cari data stok batch untuk stok awal bulan berikutnya jika ada update jika tidak insert

         $sql = "select penerimaan_periode_tanggal_awal,penerimaan_periode_tanggal_akhir
            from logistik.logistik_penerimaan_periode where
            penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$_POST['id_periode']);
         $rs = $dtaccess->Execute($sql); 
         $dataPeriodee = $dtaccess->Fetch($rs);
         //echo $sql;
         //echo "aa".$dataPeriodee["penerimaan_periode_tanggal_awal"]."<br>";
         //echo "ak".$dataPeriodee["penerimaan_periode_tanggal_akhir"];
     $awalbulan = $dataPeriodee["penerimaan_periode_tanggal_awal"];
     $akhitrbulan = $dataPeriodee["penerimaan_periode_tanggal_akhir"];

     $sql = "select * from logistik.logistik_stok_dep_periode 
              where id_item = ".QuoteValue(DPE_CHAR,$data[1])." and
              stok_dep_periode_tgl >= ".QuoteValue(DPE_DATE,$dataPeriodee["penerimaan_periode_tanggal_awal"])."  and
              stok_dep_periode_tgl <= ".QuoteValue(DPE_DATE,$dataPeriodee["penerimaan_periode_tanggal_akhir"])." and
              id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);              
    $rs = $dtaccess->Execute($sql);
    $dataStokDepPeriode = $dtaccess->Fetch($rs);        
  //     echo $sql;
           $dbTable = "logistik.logistik_stok_dep_periode";
           
          $dbField[0]  = "stok_dep_periode_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_dep_periode_saldo";
          $dbField[3]  = "stok_dep_periode_create";
          $dbField[4]  = "stok_dep_periode_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_periode";
                    
          if($dataStokDepPeriode){ 
          $stokdepPerId = $dataStokDepPeriode["stok_dep_periode_id"]; }else{ 
          $stokdepPerId = $dtaccess->GetTransID(); }
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokdepPerId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$data[1]);//QuoteValue(DPE_NUMERIC,number_format($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($data[3]));    
          $dbValue[3] = QuoteValue(DPE_DATE,$tglcreate);
          $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_periode"]);         // print_r($dbValue);
 //          echo "<br>logistik_stok_dep_periode"; print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
         
          if($dataStokDepPeriode){
          $dtmodel->Update() or die("update  error");          
          }else{
          $dtmodel->Insert() or die("insert  error"); 
          }
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
    //FUNGSI ADJUSTMENT ITEM dimulai dari waktu Opname
    //UNTUK UPDATE STOK AKHIR di STOK ITEM
    //cari tanggal opname terakhir item dan gudang tersebut
     $sql = "select opname_tanggal from logistik.logistik_opname
             where id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and opname_flag='M'";
       $sql .= " order by opname_tanggal desc";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
       $dataOpnameTgl = $dtaccess->Fetch($rs);
 //       echo $sql;
       $sql = "select * from logistik.logistik_stok_item";
       $sql .= " where id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
       $sql .= " and id_item = ".QuoteValue(DPE_CHAR,$data[1]);
//       $sql .= " and date(stok_item_create) >= ".QuoteValue(DPE_DATE,$dataOpnameTgl["opname_tanggal"]);
       $sql .= " order by id_gudang asc, stok_item_create asc";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
       $dataAdjustment = $dtaccess->FetchAll($rs);
 //       echo $sql;
    for($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++)
       {
         if ($dataAdjustment[$ls]["stok_item_flag"]=='A') //Saldo Awal
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') //Pemakaian
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) //Transfer Penerimaan
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) //Transfer Keluar
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='B') //Pembelian
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='P') //Penjualan
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='O') //Opname
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='K') //Retur Pembelian
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) //Retur ke Gudang Penerimaan
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) //Retur ke Gudang Keluar
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];  
         if ($dataAdjustment[$l]["stok_item_flag"]=='M') //Retur Penjualan
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];  

        $sql  ="update logistik.logistik_stok_item 
                set stok_item_saldo=".$saldo." 
                where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 
      }

      //Update logistik stok dep sesuai dengan saldo adjustmen di gudang asal
      $sql  ="update logistik.logistik_stok_dep 
      set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo));
      $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$data[1]);
      $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      unset($saldo); 
     //  echo $sql;
        //   die();
         // UPDATE LOGISTIK STOK BATCH DEP
         // ADJUSTMENT STOK BATCH DEP
         //}

                         
                         } else {
                              $error[] = $startLine;
                         }
                    
                    
                    $startLine ++;
               }
          }
          
          if($error) $error = implode("<br>Data Excel Baris ke ",$error);
     } 
     
     //konfigurasi rs
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $konfigurasi = $dtaccess->Fetch($rs);
 //combo tahun
 $year = date('Y')+5;
    //echo $year;
    $a=0;
    $tahun[0] = $view->RenderOption("","[Pilih Tahun]",$show);
      for($i=2010;$i<=$year;$i++){
             if($_POST["tahun"]==$i) $show = "selected";
             $tahun[$a+1] = $view->RenderOption($i,$i,$show);
             $a++;   
             unset($show);            
        }
        
// bikin combo box untuk gudang //
    $sql = "select * from logistik.logistik_gudang where (gudang_flag='M' or gudang_flag is null) and id_dep =".QuoteValue(DPE_CHAR,$depId)."
            order by gudang_nama asc"; 
    $rs = $dtaccess->Execute($sql);            
    $dataGudang = $dtaccess->FetchAll($rs);
    
?>
<?php// echo $view->InitUpload(); ?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="JavaScript">
<?php $plx->Run(); ?>

function CariPeriode(id){ 
  document.getElementById('div_periode').innerHTML = GetPeriode(id,'type=r');
}  
</script>

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
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Import Stok Opname</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
     <table width="100%" border="0" cellpadding="1" cellspacing="1">             
     <tr>
      <td>
      <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Tahun Opname</label>
       </div><div class="col-md-3 col-sm-3 col-xs-6">      
              <?php echo $view->RenderComboBox("tahun","tahun",$tahun,"required",null,"onchange=\"javascript:return CariPeriode(document.getElementById('tahun').value);\"");?>
              </div>
       </td></tr>
       <tr><td>     
      <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Opname</label>
              </div><div id="div_periode" class="col-md-3 col-sm-3 col-xs-6">  
              <?php echo GetPeriode($_POST["tahun"]);?>                  
            </div>
            </td></tr>
       <tr><td>     
      <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Gudang</label>
              </div><div class="col-md-3 col-sm-3 col-xs-6">  
              <select class="form-control"name="id_gudang" id="id_gudang" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" required="required">
              <option value="">[- Pilih Gudang -]</option>
             <?php for($i=0,$n=count($dataGudang);$i<$n;$i++) { ?>
             <option value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
            <?php } ?>               
            </select>                  
            </div>
         </td></tr>
       <tr><td>     
            <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Tanggal Opname (DD-MM-YYYY)</label>
              </div><div class="col-md-3 col-sm-3 col-xs-6">  
              <div class='input-group date' id='datepicker'>
              <input name="opname_tanggal" type='text' class="form-control" 
              value="<?php if ($_POST['opname_tanggal']) { echo $_POST['opname_tanggal']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
            </div>
            </td></tr>
       <tr><td>     
      <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Pemisah / Delimiter Data</label>
              </div><div class="col-md-3 col-sm-3 col-xs-6">  
              <select class="select2_single form-control" name="delimiter" id="delimiter" onKeyDown="return tabOnEnter(this, event);" required="required"> <!--onChange="this.form.submit();" -->
                        <option value="" >[ Pilih Jenis Pasien ]</option>
                    <option value=";" <?php if(';'==$_POST["delimiter"]) echo "selected"; ?> >Titik Koma ( ; )</option>
                        <option value="," <?php if(','==$_POST["delimiter"]) echo "selected"; ?> >Koma ( , )</option>
                </select>                   
            </div>
         
         </td></tr>
       <tr><td>
          <div class="col-md-4 col-sm-6 col-xs-12" align="right">
              <label class="control-label col-md-12 col-sm-12 col-xs-12"><strong>CSV File<?php if($err){?> <font color="red">(*)</font><?php } ?>&nbsp;</strong></label>
                </div><div class="col-md-3 col-sm-3 col-xs-6">  
                <input type="file" name="csv_file" size=25 class="pull-right btn btn-success">
                    <span id="div_uh"></span>
                    <input type="submit" name="btnNext" value="Proses" class="center btn btn-primary" OnClick="document.frmEdit.btnNext.value = 'Please Wait'">                
            </div>
          </td>     
          </tr>
     </table>
</form>
                  </div>
                </div>
              </div>
            </div>
      <!-- //row filter -->


              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">

                  <div class="x_content">
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
<br />
<font size="3" color="red">
&nbsp;&nbsp;Data yang harus diisi :
<br /><br />
</font>
<font size="2" color="red">
&nbsp;&nbsp;Kolom 1  : No<br />
&nbsp;&nbsp;Kolom 2  : ID Barang <br />
&nbsp;&nbsp;Kolom 3  : Nama Barang <br />
&nbsp;&nbsp;Kolom 4  : Stok Akhir <br />
&nbsp;&nbsp;Kolom 5  : Keterangan <br />
<br />
<br />
&nbsp;&nbsp;Hapus semua baris yang tidak diperlukan untuk import (misal baris paling atas jika ada header kolomnya)<br />
&nbsp;&nbsp;Untuk Contoh Excel data silahkan klik <a href="obat.xlsx">disini</a><br />
&nbsp;&nbsp;Untuk Contoh CSV data silahkan klik <a href="obat.csv">disini</a><br />
</font>

      
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>