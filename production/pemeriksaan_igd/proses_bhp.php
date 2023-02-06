<?php
  // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
   
  //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
   $depId = $auth->GetDepId();
   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
     
 $idSplit = "1"; //DIPATEN 1 untuk JASA MEDIK    
  
  //cari folio
  if ($_POST['isNewRecord']=='true') {
      $folId = $_POST["fol_id"];
  }else{
      $folId = $_POST["id_fol"];
  }

 $sql = "select * from klinik.klinik_folio where fol_id = '$folId'";
 $dataFolio = $dtaccess->Fetch($sql);

  $sql = "select id_gudang from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$dataFolio['id_poli']);
 $dataGudang = $dtaccess->Fetch($sql);
 $theDep = $dataGudang['id_gudang'];

 $sql = "select * from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$_POST['item_id']);
 $dataItem = $dtaccess->Fetch($sql);



        if($_POST['isNewRecord']=='true') {
          $folPelId = $dtaccess->GetTransID();   
        } else {
          $folPelId = $_POST['fol_pemakaian_id'];

          $sql = "SELECT id_item from klinik.klinik_folio_pemakaian where fol_pemakaian_id = '$folPelId'";
          $old = $dtaccess->Fetch($sql);
        }
        
          # simpan di pelaksana
          $dbTable = "klinik.klinik_folio_pemakaian";
          $dbField[0] = "fol_pemakaian_id";   // PK
          $dbField[1] = "id_fol";
          $dbField[2] = "id_item";
          //$dbField[3] = "id_batch";
          $dbField[3] = "fol_pemakaian_item_nama";
          $dbField[4] = "fol_pemakaian_jumlah";
          $dbField[5] = "id_dep";
          $dbField[6] = "id_biaya";
          $dbField[7] = "who_create";
          $dbField[8] = "when_create";
          $dbField[9] = "id_poli";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
        if ($_POST['isNewRecord']=='true') {
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["fol_id"]);
        }else{
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_fol"]);
        }
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
          //$dbValue[3] = QuoteValue(DPE_CHAR,$dataItem["fol_pelaksana_tipe"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$dataItem['item_nama']);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["fol_pemakaian_jumlah"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[6] = QuoteValue(DPE_CHAR,$dataFolio['id_biaya']);
          $dbValue[7] = QuoteValue(DPE_CHAR,$userName);
          $dbValue[8] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolio["id_poli"]);
           
          $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
          //print_r($dbField);
          //print_r($dbValue);
          //print_r($dbKey);
          //die();
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if($_POST['isNewRecord']=='true') {
            $dtmodel->Insert() or die("insert  error"); 
            echo "sukses insert fol pemakaian => " ;
          } else {
            $dtmodel->Update() or die("insert  error"); 
            echo "sukses update fol pemakaian => " ;
            //delete split dulu
            //$sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$_POST["id_fol"]);
            //$dtaccess->Execute($sql);
            //echo "sukses hapus fol split lama => " ;
          }
            
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);    

          if($_POST['isNewRecord'] !='true') {
            
            $sql = "DELETE FROM logistik.logistik_stok_item where id_pemakaian = '$folPelId' and id_item = ".QuoteValue(DPE_CHAR, $old['id_item']);
            $dtaccess->Execute($sql);
            $saldo = 0;

             $noww = date('Y-m-d H:i:s');
        $firsmonth = date('Y-m-01 00:00:00');

    /* Adjusment */
        $saldo = 0;
        $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$old['id_item'])." order by id_gudang asc, stok_item_create asc";
        $dataAdjustment = $dtaccess->FetchAll($sql);

       $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = ".QuoteValue(DPE_CHAR,$old['id_item'])." order by stok_item_create desc limit 1";
           $lastData = $dtaccess->Fetch($sql);

           if(count($lastData['stok_item_saldo']) == 0){
             $saldo = 0;
           }
           else{
             $saldo = $lastData['stok_item_saldo'];
           }
           
         $arrayAdjusment = [];
         $arrayStokItemId = [];
         $arrayAdjusmentJumlah = [];
     
         /* SQL PENGURUTAN */
    for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
          $StokItemId = $dataAdjustment[$ls]["stok_item_id"];
          $stokItemJumlah = ($dataAdjustment[$ls]["stok_item_flag"]=='O') ? $dataAdjustment[$ls]["stok_item_saldo"] - $saldo : $dataAdjustment[$ls]["stok_item_jumlah"];

          if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
          if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
          if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$dataAdjustment[$ls]["stok_item_saldo"]; //Opname
          if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
          if ($dataAdjustment[$ls]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
          
          $arrayAdjusment[] = " when stok_item_id = '$StokItemId' then $saldo ";
          $arrayAdjusmentJumlah[] = " when stok_item_id = '$StokItemId' then $stokItemJumlah ";
          $arrayStokItemId[] = "'$StokItemId'";

        }

        $strAdjusment = implode(" ", $arrayAdjusment);
        $strAdjusmentJumlah = implode(" ", $arrayAdjusmentJumlah);
        $strStokItemId = implode(", ", $arrayStokItemId);

        $sql = "UPDATE logistik.logistik_stok_item set stok_item_saldo = ( case 
                $strAdjusment
                end ),
                stok_item_jumlah = ( case 
                $strAdjusmentJumlah 
                end)
                where stok_item_id in ($strStokItemId) ";
        $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$old['id_item'])." and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

          }
        
          $sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .="and id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"]);
          $sql .="order by stok_item_create desc"; 
          $dataDep = $dtaccess->Fetch($sql);
          $newStok = $dataDep['stok_item_saldo'] - $_POST['fol_pemakaian_jumlah'];
             
           $dbTable = "logistik.logistik_stok_item";
           $dbField[0]  = "stok_item_id";   // PK
           $dbField[1]  = "stok_item_jumlah"; //Pemakaian
           $dbField[2]  = "id_item";    
           $dbField[3]  = "id_gudang";
           $dbField[4]  = "stok_item_flag";
           $dbField[5]  = "stok_item_create";
           $dbField[6]  = "stok_item_saldo"; //Saldo saat ini
           $dbField[7]  = "id_dep";
           $dbField[8]  = "id_pemakaian";
           
           $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
           $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["fol_pemakaian_jumlah"]);
           $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
           $dbValue[3] = QuoteValue(DPE_CHAR,$theDep);
           $dbValue[4] = QuoteValue(DPE_CHAR,'PP');
           $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
           $dbValue[6] = QuoteValue(DPE_NUMERIC,$newStok);       
           $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
           $dbValue[8] = QuoteValue(DPE_CHAR,$folPelId);
           
           $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
           $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
           
             $dtmodel->Insert() or die("insert  error"); 
             echo "sukses insert fol pemakaian => " ;
           
             
           unset($dbField);
           unset($dbValue);
     
           //SQL STOK DEP
           $sql = "select * from logistik.logistik_stok_dep where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)."
                   and id_item = ".QuoteValue(DPE_CHAR,$_POST['item_id']);
           $dataStokDep = $dtaccess->Fetch($sql);
           if ($dataStokDep) {
             $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$newStok);
             $sql .=" , stok_dep_create = current_timestamp";
             $sql .=" , stok_dep_tgl = current_date";
             $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"]);
             $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           }else{
             $dbTable = "logistik.logistik_stok_dep";
             $dbField[0]  = "stok_dep_id";   // PK
             $dbField[1]  = "id_item";
             $dbField[2]  = "stok_dep_saldo";    
             $dbField[3]  = "stok_dep_create";
             $dbField[4]  = "stok_dep_tgl";
             $dbField[5]  = "id_dep";
             $dbField[6]  = "id_gudang";
             
             $StokDepId = $dtaccess->GetTransID();
             $dbValue[0] = QuoteValue(DPE_CHAR,$StokDepId);
             $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
             $dbValue[2] = QuoteValue(DPE_NUMERIC,$newStok);
             $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
             $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
             $dbValue[5] = QuoteValue(DPE_CHAR,'9999999');
             $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);       
             
             $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
             $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
             $dtmodel->Insert() or die("update  error");
               
             unset($dbField);
             unset($dbValue);
           }
     
               /* SQL PENGURUTAN */
         $noww = date('Y-m-d H:i:s');
        $firsmonth = date('Y-m-01 00:00:00');

    /* Adjusment */
        $saldo = 0;
        $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." order by id_gudang asc, stok_item_create asc";
        $dataAdjustment = $dtaccess->FetchAll($sql);

       $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." order by stok_item_create desc limit 1";
           $lastData = $dtaccess->Fetch($sql);

           if(count($lastData['stok_item_saldo']) == 0){
             $saldo = 0;
           }
           else{
             $saldo = $lastData['stok_item_saldo'];
           }
           
         $arrayAdjusment = [];
         $arrayStokItemId = [];
         $arrayAdjusmentJumlah = [];
     
         /* SQL PENGURUTAN */
    for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
          $StokItemId = $dataAdjustment[$ls]["stok_item_id"];
          $stokItemJumlah = ($dataAdjustment[$ls]["stok_item_flag"]=='O') ? $dataAdjustment[$ls]["stok_item_saldo"] - $saldo : $dataAdjustment[$ls]["stok_item_jumlah"];

          if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
          if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
          if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$dataAdjustment[$ls]["stok_item_saldo"]; //Opname
          if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
          if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
          if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
          if ($dataAdjustment[$ls]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
          
          $arrayAdjusment[] = " when stok_item_id = '$StokItemId' then $saldo ";
          $arrayAdjusmentJumlah[] = " when stok_item_id = '$StokItemId' then $stokItemJumlah ";
          $arrayStokItemId[] = "'$StokItemId'";

        }

        $strAdjusment = implode(" ", $arrayAdjusment);
        $strAdjusmentJumlah = implode(" ", $arrayAdjusmentJumlah);
        $strStokItemId = implode(", ", $arrayStokItemId);

        $sql = "UPDATE logistik.logistik_stok_item set stok_item_saldo = ( case 
                $strAdjusment
                end ),
                stok_item_jumlah = ( case 
                $strAdjusmentJumlah 
                end)
                where stok_item_id in ($strStokItemId) ";
        $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

    
   exit();      
  
?>