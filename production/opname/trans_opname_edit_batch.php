<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $usrId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();


     if($_GET["id"]) $_POST["id_opname"] = $_GET["id"];
     if($_GET["id_gudang"]) $_POST["id_gudang"] = $_GET["id_gudang"];
     if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
     if($_GET["tahun"]) $_POST["tahun"] = $_GET["tahun"]; 
     if($_GET["id_periode"]) $_POST["id_periode"] = $_GET["id_periode"];
     if(!$_POST["klinik"]) $_POST["klinik"]=$depId;

     $plx = new expAJAX("GetPeriode,GetStokAkhir");
     
     function GetPeriode($thn){
        global $dtaccess,$view,$depId,$ROOT; 
         $sql = "select * from logistik.logistik_penerimaan_periode where extract(year from penerimaan_periode_tanggal_awal)=".QuoteValue(DPE_CHAR,$thn)." 
                order by penerimaan_periode_tanggal_awal asc";
         $rs = $dtaccess->Execute($sql); 
      	 $dataPeriode = $dtaccess->FetchAll($rs);
    			unset($periode);
    			$periode[0] = $view->RenderOption("","[Pilih Periode]",$show);
    			$i = 1;
    			
         for($i=0,$n=count($dataPeriode);$i<$n;$i++){   
             if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) $show = "selected";
             $periode[$i+1] = $view->RenderOption($dataPeriode[$i]["penerimaan_periode_id"],$dataPeriode[$i]["penerimaan_periode_nama"],$show);
             unset($show);
         }
    			$str = $view->RenderComboBox("id_periode","id_periode",$periode,null,null,null);
    	 return $str;
     } 
         
     function GetStokAkhir($stokbat,$batch,$itemku,$gudang){
        global $dtaccess,$view,$depId,$ROOT; 
         $sql = "update logistik.logistik_stok_batch_dep set stok_batch_dep_saldo = ".QuoteValue(DPE_NUMERIC,$stokbat)." where 
                id_item = ".QuoteValue(DPE_CHAR,$itemku)." and 
                id_batch = ".QuoteValue(DPE_CHAR,$batch)." and
                id_gudang = ".QuoteValue(DPE_CHAR,$gudang);
         $rs = $dtaccess->Execute($sql); 
        //  return $sql;
         $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep where 
                id_item = ".QuoteValue(DPE_CHAR,$itemku)." and 
                id_gudang = ".QuoteValue(DPE_CHAR,$gudang);
         $rs = $dtaccess->Execute($sql); 
      	 $dataBatchku = $dtaccess->Fetch($rs);
       //   return $sql;      
      $_POST["stokReal_$itemku"] = $dataBatchku["total"];
      $str = $view->RenderTextBox("stokReal_$itemku","stokReal_$itemku","8","30",currency_format($_POST["stokReal_$itemku"]),"curedit", "", null,true); 
    	 return $str;     
     } 
     
     $backPage = "trans_opname.php?klinik=".$_POST["klinik"]."&id_gudang=".$_POST["id_gudang"]."&id_periode=".$_POST["id_periode"]."&tahun=".$_POST["tahun"];
     $thisPage = "trans_opname_edit.php?klinik=".$_POST["klinik"]."&id_gudang=".$_POST["id_gudang"]."&id_periode=".$_POST["id_periode"]."&tahun=".$_POST["tahun"];

     $skr = date("d-m-Y");
     $sql_where[] = "b.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $sql_where[] = "h.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
    if($_POST["id_kategori"] && $_POST["id_kategori"]!="--" && $_POST["id_kategori"]!="nn") $sql_where[] = "a.id_kategori = ".QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
        elseif($_POST["id_kategori"]=="nn") $sql_where[] = "(a.id_kategori = '' or a.id_kategori is null or a.id_kategori = '--') ";
    if($_POST["id_sup"] && $_POST["id_sup"]!="--") $sql_where[] = "a.id_sup = ".QuoteValue(DPE_CHAR,$_POST["id_sup"]);    
    if($sql_where) $sql_where = implode(" and ",$sql_where);
         
     if(!$_POST["opname_tanggal"]) $_POST["opname_tanggal"]= $skr;

	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["opname_id"])  $opnameId = & $_POST["opname_id"];
	 if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opnameId = $_GET["id"];
          }
         
          $sql = "select a.*, b.penerimaan_periode_tanggal_awal from logistik_opname a 
                  left join logistik_penerimaan_periode b on b. penerimaan_periode_id=a.id_periode
                  where a.opname_flag='M' and a.opname_id = ".QuoteValue(DPE_CHAR,$opnameId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["opname_tanggal"] = format_date($row_edit["opname_tanggal"]);
          $_POST["id_periode"] = $row_edit["id_periode"];
          $_POST["id_gudang"] = $row_edit["id_gudang"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $_POST["opname_flag"] = $row_edit["opname_flag"];
                    
     }



     if($_POST["btnLanjut"]){
     $opnameId = & $_POST["id_opname"];
          
  //   echo "opname ID ".$opnameId."<br> Tambah ".$tamabh;

          
  $sql  = "select g.batch_id, g.id_item, a.item_id, a.item_nama ,a.id_kategori,kategori_tindakan_nama, a.id_kategori_tindakan, a.item_tipe_jenis, a.id_dep, b.stok_dep_saldo ,
          c.gudang_nama, d.dep_nama as departemen ,e.grup_item_nama, g.batch_create, g.batch_no, g.batch_tgl_jatuh_tempo, h.stok_batch_dep_saldo
          from logistik.logistik_item a                      
          left join logistik.logistik_stok_dep b on b.id_item = a.item_id
          left join logistik.logistik_gudang c on c.gudang_id = b.id_gudang
          left join global.global_departemen d on d.dep_id = a.id_dep
          left join logistik.logistik_grup_item e on e.grup_item_id=a.id_kategori
          left join klinik.klinik_kategori_tindakan f on a.id_kategori_tindakan = f.kategori_tindakan_id
          join logistik.logistik_item_batch g on g.id_item = a.item_id
          join logistik.logistik_stok_batch_dep h on h.id_batch = g.batch_id";
          
     if($sql_where) $sql .= " where a.item_flag ='M' and ".$sql_where." order by grup_item_nama asc, item_nama, batch_create asc";
     $rs = $dtaccess->Execute($sql);
     $dataItem = $dtaccess->FetchAll($rs);     
     
    // $backPage = "trans_opname_edit.php?tambah=1&id_periode=".$_POST["id_periode"]."&id=".$_POST["id_opname"]."&id_gudang=".$_POST["id_gudang"]."&tahun=".$_POST["tahun"];
    //   header('location:'.$backPage);
 //    echo $sql; //die();
    // }

   for($i=0,$n=count($dataItem);$i<$n;$i++) {
   
      if($dataItem[$i]["id_item"]==$dataItem[$i-1]["id_item"]){
          $hitung[$dataItem[$i]["id_item"]] += 1;
       }

       $sql = "select * from logistik.logistik_stok_item";
       $sql .= " where id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
       $sql .= " and id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["id_item"]);
       $sql .= " order by id_gudang asc, stok_item_create asc";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
  	   $dataAdjustment = $dtaccess->FetchAll($rs);
   //  echo $sql;
    for($l=0,$q=count($dataAdjustment);$l<$q;$l++)
       {
         if ($dataAdjustment[$l]["stok_item_flag"]=='A') //Saldo Awal
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='PP') //Pemakaian
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='T' && $dataAdjustment[$l]["id_dep_tujuan"]==null) //Transfer Penerimaan
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='T' && $dataAdjustment[$l]["id_dep_tujuan"]!=null) //Transfer Keluar
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='B') //Pembelian
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='P') //Penjualan
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='O') //Opname
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='K') //Retur Pembelian
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='L' && $dataAdjustment[$l]["id_dep_tujuan"]==null) //Retur ke Gudang Penerimaan
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='L' && $dataAdjustment[$l]["id_dep_tujuan"]!=null) //Retur ke Gudang Keluar
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];  
         if ($dataAdjustment[$l]["stok_item_flag"]=='M') //Retur Penjualan
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];    
           
        $sql  ="update logistik.logistik_stok_item 
                set stok_item_saldo=".$saldo." 
                where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$l]["stok_item_id"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 
      }

      //Update logistik stok dep sesuai dengan saldo adjustmen di gudang asal
      $sql  ="update logistik.logistik_stok_dep 
      set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo));
      $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["id_item"]);
      $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

       //     unset($saldo);
            
   //    echo $sql; // die();
        //   die();
         // UPDATE LOGISTIK STOK BATCH DEP
         // ADJUSTMENT STOK BATCH DEP
          $sql = "select * from logistik.logistik_stok_item_batch";
           $sql .= " where id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
           $sql .= " and id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"]);           
           $sql .= " order by id_gudang asc, stok_item_batch_create asc";
      	   $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      	   $dataAdjustmentBatch = $dtaccess->FetchAll($rs);
     //  echo $sql; //die();
          for($m=0,$o=count($dataAdjustmentBatch);$m<$o;$m++)
           {
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='A') //Saldo Awal
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='PP') //Pemakaian
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='T' && $dataAdjustmentBatch[$m]["id_dep_tujuan"]==null) //Transfer Penerimaan
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='T' && $dataAdjustmentBatch[$m]["id_dep_tujuan"]!=null) //Transfer Keluar
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='B') //Pembelian
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='P') //Penjualan
               $saldoBatch=$saldoBatch-$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
             if ($dataAdjustmentBatch[$m]["stok_item_batch_flag"]=='O') //Opname
               $saldoBatch=$saldoBatch+$dataAdjustmentBatch[$m]["stok_item_batch_jumlah"];
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
                where stok_item_batch_id =".QuoteValue(DPE_CHAR,$dataAdjustmentBatch[$m]["stok_item_batch_id"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 
          }
          
          $sql  ="update logistik.logistik_stok_batch_dep 
          set stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldoBatch));
          $sql .=" where id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"]); 
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      // echo $sql; // die();
                   unset($saldo);
                   unset($saldoBatch);
   }
   }
    /* if($_POST["btnLanjut"]){

     $sql = "select a.*, b.batch_no,b.batch_tgl_jatuh_tempo,b.batch_create,c.item_nama from logistik.logistik_stok_item_batch a 
             left join logistik.logistik_item_batch b on b.batch_id=a.id_batch
             left join logistik.logistik_item c on c.item_id=a.id_item
            where stok_item_batch_flag='O' and a.id_opname=".QuoteValue(DPE_CHAR,$opnameId)."
            order by stok_item_batch_create asc";
     $rs = $dtaccess->Execute($sql);
     $dataItemOpname = $dtaccess->FetchAll($rs);
      echo $sql;
    
     }  */
     
     if($_POST["btnSave"]){
     $tglcreate = date_db($_POST["opname_tanggal"])." ".date('H:i:s');
          
     $sql = "select penerimaan_periode_tanggal_awal,penerimaan_periode_tanggal_akhir
            from logistik.logistik_penerimaan_periode where
            penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     $rs = $dtaccess->Execute($sql);
     $dataPeriode = $dtaccess->Fetch($rs);
     
     $awalbulan = $dataPeriode["penerimaan_periode_tanggal_awal"];
     $akhitrbulan = $dataPeriode["penerimaan_periode_tanggal_akhir"];
     
     $_POST["id_opname"] = & $_POST["id_opname"];
  //   $depId = & $_POST["klinik"];
     
     $dbTable = "logistik.logistik_opname";
     
     $dbField[0] = "opname_id";   // PK
     $dbField[1] = "opname_tanggal";
     $dbField[2] = "id_dep";
     $dbField[3] = "id_gudang";
     $dbField[4] = "id_periode";
     $dbField[5] = "opname_flag";
          
     if(!$_POST["id_opname"]) $opnameId = $dtaccess->GetTransID();
     else
     $opnameId = $_POST["id_opname"];
     $dbValue[0] = QuoteValue(DPE_CHAR,$opnameId);
     $dbValue[1] = QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]));
     $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
     $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
     $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     $dbValue[5] = QuoteValue(DPE_CHAR,'M');
          
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
    // print_r($dbValue); die();
     
     if(!$_POST["id_opname"]) $dtmodel->Insert() or die("insert  error");
     else
     $dtmodel->Update() or die("update  error");	
     
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey); 

    //  die();
     for($i=0,$n=count($_POST["batch_id"]);$i<$n;$i++) {
     

    // echo date_db($_POST["tgl_awal"])." <br /> tahun ".$tahunopname." <br /> ".$bulanopname;
      //ambil data item
     $sql = "select id_item from logistik.logistik_item_batch where batch_id=".QuoteValue(DPE_CHAR,$_POST["batch_id"][$i]);
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
          unset($dbKey);
                   
     //$_POST["stokReal".$i] = number_format($_POST["stokReal".$i]);     ||     data stok sebelum ada batch//
       // sum data stok di item batch //
     //cari stok item batch saldo sebelumnya
     $sql = "select stok_item_saldo from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])." and 
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_create) < ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_create desc";
      $rs = $dtaccess->Execute($sql);
      $datastoksebelumOpname[$i] = $dtaccess->Fetch($rs);
 //     echo $sql;
		 $sql = "select sum(b.stok_batch_dep_saldo) as total from logistik.logistik_item_batch a
               left join logistik.logistik_stok_batch_dep b on a.batch_id = b.id_batch
               where b.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and a.id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])." 
               and a.id_dep =".QuoteValue(DPE_CHAR,$depId); 
     $dataStokBatch = $dtaccess->Fetch($sql);
       
     $_POST["stokRealtot".$i] = StripCurrency($dataStokBatch["total"]);
// echo  "<br>stok real input".$_POST["stokRealtot".$i];
    
     //selisih real - tercatat
     $stokInt[$i] = $_POST["stokRealtot".$i]-$datastoksebelumOpname[$i]["stok_item_saldo"];      
  //    echo "<br> stok item saldo selisih ".$stokInt[$i]."<br>";
     //Jika item ini stok sebelumnya dan stok opname sama tidak di proses
     //dianggap itemnya tidak di opname  -- Start
 //echo "<br>selisih stok real input".$stokInt[$i];
// echo $sql; //die();   
 //    if($stokInt[$i]!=0){     

     //cari berdasarkan opnamenya
     $sql = "select stok_item_id from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])."
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
      
      if($stokitemopname) $stokItemId =$stokitemopname["stok_item_id"];
      else
      $stokItemId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,$stokInt[$i]);
      $dbValue[2] = QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $dbValue[4] = QuoteValue(DPE_CHAR,'O');
      $dbValue[5] = QuoteValue(DPE_DATE,$tglcreate);
			$dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokRealtot".$i]));      
			$dbValue[7] = QuoteValue(DPE_CHAR,$_POST["stokKet".$i]);     
      $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$opnameId);
 //     echo "STOK ITEM"; print_r($dbValue);
            
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
     $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
     $sql .="order by stok_dep_create desc"; 
     $rs = $dtaccess->Execute($sql);
     $dataDep = $dtaccess->Fetch($rs);
     //if($_POST["id_item"][$i]=="c3ae3a0364c0e97e0b1bf54252bbeac3") echo $sql; die();
    
          if(!$dataDep){         
          $dbTable = "logistik.logistik_stok_dep";
          $dbField[0]  = "stok_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_dep_saldo";
          $dbField[3]  = "stok_dep_create";
          $dbField[4]  = "stok_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          
          $depId = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataItemS["id_item"]);//QuoteValue(DPE_NUMERIC,number_format($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokRealtot".$i]));    
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
    
          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokRealtot".$i]));
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          //echo $sql; die();              
          }
          
             //jika item ini stok sebelumnya dan stok selanjutnya sama tidak di proses -- end
        //masukkan stok akhir stok opname ke dalam tabel agar bisa jadi stok awal berikutnya
     //cari data stok batch untuk stok awal bulan berikutnya jika ada update jika tidak insert
     $sql = "select * from logistik.logistik_stok_dep_periode 
              where id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"])." and
              stok_dep_periode_tgl >= '".$awalbulan."'  and
              stok_dep_periode_tgl <= '".$akhitrbulan."' and
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
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataItemS["id_item"]);//QuoteValue(DPE_NUMERIC,number_format($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stokRealtot".$i]));    
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
       $sql .= " and id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
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
         if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$l]["id_dep_tujuan"]==null) //Transfer Penerimaan
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$l]["id_dep_tujuan"]!=null) //Transfer Keluar
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='B') //Pembelian
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='P') //Penjualan
           $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$ls]["stok_item_flag"]=='O') //Opname
           $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='K') //Retur Pembelian
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='L' && $dataAdjustment[$l]["id_dep_tujuan"]==null) //Retur ke Gudang Penerimaan
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];
         if ($dataAdjustment[$l]["stok_item_flag"]=='L' && $dataAdjustment[$l]["id_dep_tujuan"]!=null) //Retur ke Gudang Keluar
           $saldo=$saldo-$dataAdjustment[$l]["stok_item_jumlah"];  
         if ($dataAdjustment[$l]["stok_item_flag"]=='M') //Retur Penjualan
           $saldo=$saldo+$dataAdjustment[$l]["stok_item_jumlah"];  

        $sql  ="update logistik.logistik_stok_item 
                set stok_item_saldo=".$saldo." 
                where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 
      }

      //Update logistik stok dep sesuai dengan saldo adjustmen di gudang asal
      $sql  ="update logistik.logistik_stok_dep 
      set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo));
      $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataItemS["id_item"]);
      $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
unset($saldo); 
     //  echo $sql;
        //   die();
         // UPDATE LOGISTIK STOK BATCH DEP
         // ADJUSTMENT STOK BATCH DEP

     } //die();  //end for $i
     
//    die();
     echo "<script>document.location.href='trans_opname_implan.php?klinik=".$_POST["klinik"]."&id_gudang=".$_POST["id_gudang"]."&id_jenis=".$_POST["id_jenis"]."';</script>"; 
     
     }       

   if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
            //echo $sql;
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
            //echo $sql;
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id asc";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }

		// bikin combo box untuk gudang //
   	$sql = "select * from logistik.logistik_gudang where (gudang_flag='M' or gudang_flag is null) and id_dep =".QuoteValue(DPE_CHAR,$depId)."
            order by gudang_nama asc"; 
    $rs = $dtaccess->Execute($sql);            
		$dataGudang = $dtaccess->FetchAll($rs);

    $year = date('Y')+5;
    $a=0;
    $tahun[0] = $view->RenderOption("","[Pilih Tahun]",$show);
      for($i=2010;$i<=$year;$i++){
             if($_POST["tahun"]==$i) $show = "selected";
             $tahun[$a+1] = $view->RenderOption($i,$i,$show);
             $a++;   
             unset($show);            
        }
     
    //data kategori barang
    $sql = "select grup_item_id,grup_item_nama from logistik.logistik_grup_item 
            where item_flag = 'M' and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by grup_item_nama asc"; 
		$dataKatgudang = $dtaccess->FetchAll($sql);

?>
<?php echo $view->RenderBody("ipad_depans.css",true,"STOK OPNAME"); ?>
<script language="JavaScript">
<?php $plx->Run(); ?>

function CheckDataSave(frm)
{  
  if(!document.getElementById('id_gudang').value || document.getElementById('id_gudang').value=='--'){
    alert('Gudang harus dipilih!');
    document.getElementById('id_gudang').focus();
    return false;
  }	
  if(!document.getElementById('id_periode').value || document.getElementById('id_periode').value=='--'){
    alert('Periode harus dipilih!');
    document.getElementById('id_periode').focus();
    return false;
  }	
  
    /*if(!document.getElementById('id_kategori').value || document.getElementById('id_kategori').value=='--'){
    alert('Kategori harus dipilih!');
    document.getElementById('id_kategori').focus();
    return false;
  }*/	  
	return true;
          
}

function CariPeriode(id){ 
	document.getElementById('div_periode').innerHTML = GetPeriode(id,'type=r');
}

function CariStokAKhir(stok,batch,item,gudang){ 
	document.getElementById('div_stok'+item).innerHTML = GetStokAkhir(stok,batch,item,gudang,'type=r');
}

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=900,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
} 

function GantiTotal(terima,urut) {
    
    var Sisa = document.getElementById('stokHandBatch'+urut).value.toString().replace(/\,/g,"");
    var ke = document.getElementById('urutan').value.toString().replace(/\,/g,"");

    var item = document.getElementById('id_item_batch'+urut).value;
    var itemSebelum = document.getElementById('id_item_batch_'+urut).value;
    var curSis = Sisa;
     //alert(ke); 
    
    if(item==itemSebelum ){
    var mundur = urut-1;   
    //var SisaSebelum = document.getElementById('stokHandBatch'+mundur).value.toString().replace(/\,/g,"");
    var SisaSebelum = document.getElementById('stokReal'+ke).value.toString().replace(/\,/g,"");
    TotSisa = eval(SisaSebelum)+eval(Sisa);
    //alert(TotSisa);
    
    document.getElementById('stokReal'+ke).value = TotSisa;
    } else { 
    document.getElementById('stokReal'+urut).value = curSis;
    }
    
}
</script>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<?php if($_GET["id"]){ ?>
<a href="#" target="_blank"><font size="6">EDIT OPNAME</font></a>&nbsp;&nbsp;
<?php } else { ?>
<a href="#" target="_blank"><font size="6">TAMBAH OPNAME</font></a>&nbsp;&nbsp;
<?php } ?>
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">
 <br />
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>DETAIL OPNAME BARANG</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
 
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Tahun&nbsp;</td>
          		  <td class="tablecontent-odd" colspan="2">
                  <?php echo $view->RenderComboBox("tahun","tahun",$tahun,null,null,"onchange=\"javascript:return CariPeriode(document.getElementById('tahun').value);\"");?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Periode&nbsp;</td>
          		  <td class="tablecontent-odd" colspan="2">
                  <div id="div_periode"><?php echo GetPeriode($_POST["tahun"]);?></div>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">&nbsp;Tanggal Opname</td>
               <td align="left" colspan="2" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("opname_tanggal","opname_tanggal","15","30",$_POST["opname_tanggal"],"inputField", "readonly",null,false);?>
                    <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_opname_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>          
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Gudang&nbsp;</td>
          		  <td class="tablecontent-odd" colspan="2">
                  <select name="id_gudang" id="id_gudang" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <option value="--">[- Pilih Gudang -]</option>
                      <?php for($i=0,$n=count($dataGudang);$i<$n;$i++) { ?>
          						 <option value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
          						<?php } ?>               
                  </select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Kategori&nbsp;</td>
          		  <td class="tablecontent-odd" colspan="2">
                  <select name="id_kategori" id="id_kategori">
              <option value="--">[- Pilih Kategori Barang-]</option>
              <option value="nn" <?php if($_POST["id_kategori"]== 'nn') {echo "selected";} ?> >Tanpa Kategori</option>
              <?php for($i=0,$n=count($dataKatgudang);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataKatgudang[$i]["grup_item_id"];?>" <?php if($_POST["id_kategori"]==$dataKatgudang[$i]["grup_item_id"]) echo "selected";?>><?php echo $dataKatgudang[$i]["grup_item_nama"];?></option>
						  <?php } ?>               
              </select>
               </td>
          </tr>
<!--           <tr>
               <td align="right" class="tablecontent" valign="top">&nbsp;Supplier&nbsp;</td>
          		  <td class="tablecontent-odd" colspan="2">
                  <select name="id_sup" id="id_sup">
              <option value="--">[- Pilih Supplier Implan -]</option>
              <?php for($i=0,$n=count($dataSupplier);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataSupplier[$i]["sup_id"];?>" <?php if($_POST["id_sup"]==$dataSupplier[$i]["sup_id"]) echo "selected";?>><?php echo $dataSupplier[$i]["sup_nama"];?></option>
						  <?php } ?>               
              </select>
               </td>
          </tr> -->        
<tr>
<td colspan="2" align ="center">
<?php if($_GET["tambah"]){ ?>
<?php echo $view->RenderHidden("tambah","tambah",$_POST["tambah"]);?>
<? } ?>
<?php echo $view->RenderHidden("id_opname","id_opname",$_POST["id_opname"]);?>
 <input type="submit" name="btnLanjut" value="Lanjut" class="submit" onClick="javascript:return CheckDataSave(this.form);">
</td>
</tr>
    </table>
    </fieldset>
    <br />
<?php if($_POST["btnLanjut"] || $_POST["btnTambah"] || $_POST["btnTambahLagi"]){ ?>     
     <fieldset>
     <legend><strong>DETAIL OPNAME BARANG</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
              <tr>  
               <td align="center" class="subheader" width="2%">No</td>                                         
               <td align="center" class="subheader" width="10%">Kategori</td>
               <td align="center" class="subheader" width="15%">Nama Item</td>
               <td align="center" class="subheader" width="5%">Gudang</td>
               <td align="center" class="subheader" width="8%">No Batch</td>
               <td align="center" class="subheader" width="8%">Expire Date</td>
               <td align="center" class="subheader" width="8%">Batch Create</td>
               <td align="center" class="subheader" width="5%">Stok Batch</td>
               <td align="center" class="subheader" width="5%">Stok Tercatat</td>               
               <td align="center" class="subheader" width="5%">Stok Sebenarnya</td>
				       <td align="center" class="subheader" width="8%">Keterangan</td>
               <td align="center" class="subheader" width="7%">Tambah Batch</td>                                      
          </tr>
          <?php for($i=0,$j=0,$counter=0,$n=count($dataItem);$i<$n;$i++,$counter=0,$j++){ 

         if(!$opnameId){
               //cari saldo batch sebelum opname
     $sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_batch_create) < ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_batch_create desc";
      $rs = $dtaccess->Execute($sql);
      $saldoBatchPraOpname[$i] = $dtaccess->Fetch($rs);

               //cari saldo batch sebelum opname
     $sql = "select stok_item_saldo from logistik.logistik_stok_item where
             id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["id_item"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             date(stok_item_create) < ".QuoteValue(DPE_DATE,date_db($_POST["opname_tanggal"]))."
             order by stok_item_create desc";
      $rs = $dtaccess->Execute($sql);
      $saldoPraOpname[$i] = $dtaccess->Fetch($rs); 
      
      }else{
      $sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where
             id_batch = ".QuoteValue(DPE_CHAR,$dataItem[$i]["batch_id"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             id_opname = ".QuoteValue(DPE_CHAR,$opnameId);
      $rs = $dtaccess->Execute($sql);
      $saldoBatchPraOpname[$i] = $dtaccess->Fetch($rs);

     $sql = "select stok_item_saldo from logistik.logistik_stok_item where      
             id_item = ".QuoteValue(DPE_CHAR,$dataItem[$i]["id_item"])." and
             id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and
             id_opname = ".QuoteValue(DPE_CHAR,$opnameId);
      $rs = $dtaccess->Execute($sql);      
      $saldoPraOpname[$i] = $dtaccess->Fetch($rs);
      }                                      
          ?>
          <tr  class="<?php if($i%2==0) echo 'tablecontent-odd'; else echo 'tablecontent'; ?>">  
            <?php if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { 
                     $dataSpan["jml_span"] = $hitung[$dataItem[$i]["id_item"]] += 1; 
                     $m++; ?>
                     
                      <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $m?>                  
               </td>

               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $view->RenderLabel("grup_item_nama","grup_item_nama",$dataItem[$i]["grup_item_nama"], null,false);?>                  
               </td>
               <td align="left" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">     
                    <?php echo $view->RenderLabel("item1","item1",$dataItem[$i]["item_nama"], null,false);?>
                    <?php echo $view->RenderHidden("id_item[$i]","id_item[$i]",$dataItem[$i]["item_id"]);?>                                
                    <!--<input type="text" name="id_item[<?php echo $i;?>]" id="id_item_<?php echo $i;?>" value="<?php echo $dataItem[$i]["item_id"];?>" />-->
               </td>              
               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $view->RenderLabel("gudang_nama","gudang_nama",$dataItem[$i]["gudang_nama"], null,false);?>                  
               </td>
               <?php } ?>
                                          
               <td align="center">
                    <?php echo $view->RenderTextBox("batch_no[$i]","batch_no[$i]","8","8",$dataItem[$i]["batch_no"],"", null,false);?>
                    <input type="hidden" name="batch_id[<?php echo $i;?>]" id="batch_id_<?php echo $i;?>" value="<?php echo $dataItem[$i]["batch_id"];?>" />
                    <?php echo $view->RenderHidden("id_item_batch$i","id_item_batch$i",$dataItem[$i]["item_id"]);?>
                    <?php echo $view->RenderHidden("id_item_batch_$i","id_item_batch_$i",$dataItem[$i-1]["item_id"]);?>
                    <?php //echo $view->RenderTextBox("id_item_batch[$i]","id_item_batch[$i]","8","30",$dataItem[$i]["item_id"]);?>
               </td>
               <td align="center">
                    <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo$i","batch_tgl_jatuh_tempo$i","10","10",format_date($dataItem[$i]["batch_tgl_jatuh_tempo"]),"","readonly", null,false);?>
               </td>
               <td align="center">
                    <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo$i","batch_tgl_jatuh_tempo$i","16","30",formatTimestamp($dataItem[$i]["batch_create"]),"","readonly", null,false);?>
               </td>

               <td align="center">
                    <?php echo $view->RenderTextBox("stokHandBatch$i","stokHandBatch$i","5","30",number_format($saldoBatchPraOpname[$i]["stok_item_batch_saldo"],4),"curedit", "",true,"onchange=\"javascript:return CariStokAKhir(document.getElementById('stokHandBatch$i').value,document.getElementById('batch_id_$i').value,document.getElementById('id_item_batch$i').value,document.getElementById('id_gudang').value);\"")?>
               </td>
               
               <?php if($dataItem[$i]["id_item"]!=$dataItem[$i-1]["id_item"]) { $dataSpan["jml_span"]; ?>
               <td align="left" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">                    
                    <?php echo $view->RenderTextBox("stokHand$i","stokHand$i","5","30",currency_format($saldoPraOpname[$i]["stok_item_saldo"]),"curedit", "readonly",true);?>
                    <?php echo $view->RenderHidden("urutan","urutan",$i);?>                   
               </td>               
               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    
                  <div id="div_stok<?php echo $_POST["id_item_batch$i"];?>"><?php echo GetStokAkhir($_POST["stokHandBatch$i"],$_POST["batch_id$i"],$_POST["id_item_batch$i"],$_POST["id_gudang"]);?></div>               
               </td>              
               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                    <?php echo $view->RenderTextBox("stokKet$i","stokKet$i","30","30",$dataItem[$i]["stok_item_keterangan"],"curedit", "", null,true);?>                    
               </td>              

               <td align="center" style="padding-left:5px;" rowspan="<?php echo $dataSpan["jml_span"];?>">
                <?php echo '<a href="tambah_batch.php?id_item='.$dataItem[$i]["item_id"].'&klinik='.$dataItem[$i]["id_dep"].'&id_jenis='.$dataItem[$i]["item_tipe_jenis"].'&id_gudang='.$_POST["id_gudang"].'"><img src="'.$ROOT.'gambar/add.png" border="0" alt="Pilih" title="Pilih" width="18" height="18" class="img-button")"/></a>'; ?>
               </td>
               <?php } ?>
          </tr>
          <?php } ?>
                <tr>
                     
                  <td colspan="12" class="tablecontent">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <!--<input type="submit" name="btnSave" value="Simpan" class="submit" onClick="javascript:return CheckDataSave(this.form);">-->
                    <input type="button" name="btnBack" value="Kembali" id="button" class="submit" onClick="document.location.href='<?php echo $backPage;?>'">              
                  </tr> 
          </table>
              </fieldset>
       <? } ?>       
    <br />
    </td>
    </tr>
    </table>
    </form>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "opname_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_opname_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
</script>
<?php echo $view->RenderBodyEnd(); ?>
    