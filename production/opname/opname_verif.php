<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");    
     require_once($ROOT."lib/tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table","70%","left");
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
          $skr = date("d-m-Y");
          
     if($_GET["klinik"]) { 
              $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
              $_POST["klinik"] = $_POST["klinik"]; }
      else { 
              $_POST["klinik"] = $depId; 
      }
     
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
       } else if($_POST["klinik"]) { 
        $_POST["klinik"] = $_POST["klinik"]; 
         } else { 
          $_POST["klinik"] = $depId; 
          }          
     
     if($_GET["id_gudang"]) $_POST["id_gudang"]=$_GET["id_gudang"];
     if($_GET["id_periode"]) $_POST["id_periode"]=$_GET["id_periode"];
     if($_GET["tahun"]) $_POST["tahun"]=$_GET["tahun"];
     
     if(!$_POST["tahun"]) $_POST["tahun"]=date('Y');
     
     $plx = new expAJAX("GetPeriode");
     
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


     if($_POST["id_periode"]) $sql_where[] = "c.id_periode = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]); 
     if($_POST["id_gudang"] && $_POST["id_gudang"]<>'--') $sql_where[] = "c.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);

    
     if($sql_where) $sql_where = implode(" and ",$sql_where);

      

  if($_POST['item']){

  	$items = implode("','", $_POST['item']);

    $sql = "SELECT a.*, d.*, c.opname_tanggal, c.opname_waktu, c.opname_id, c.id_periode, c.id_gudang, e.item_nama from logistik.logistik_opname_detail a 
            left join logistik.logistik_opname c on a.id_opname = c.opname_id
            left join logistik.logistik_penerimaan_periode d on c.id_periode = d.penerimaan_periode_id
            left join logistik.logistik_item e on e.item_id = a.id_item
            where opname_detail_id in ('$items')";

    $dataOpname = $dtaccess->FetchAll($sql);

    for($i=0; $i < count($dataOpname); $i++){

      $sql = "DELETE from logistik.logistik_stok_item where id_opname_detail = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_detail_id"]);
      $dtaccess->Execute($sql);

      $opname_id = $dataOpname[$i]['opname_id'];
      $id_gudang = $dataOpname[$i]['id_gudang'];

      /* INSERT / UPDATE STOK ITEM */
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
      $dbField[10]  = "stok_item_sebelumnya"; 
      $dbField[11]  = "stok_item_hpp"; 
      $dbField[12]  = "id_opname_detail"; 

      if(!$_POST['stok_item_id']){
            $stokItemId =  $dtaccess->GetTransID();
        }else{
            $stokItemId =  $_POST['stok_item_id']  ; 
        };


      $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataOpname[$i]["opname_detail_selisih"]);
      $dbValue[2] = QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$id_gudang);
      $dbValue[4] = QuoteValue(DPE_CHAR,'O');
      $dbValue[5] = QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_tanggal"].' '.$dataOpname[$i]["opname_waktu"]);
      $dbValue[6] = QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_detail_jumlah"]);      
      $dbValue[7] = QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_detail_keterangan"]);     
      $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$opname_id);
      $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataOpname[$i]["opname_detail_jumlah_sebelumnya"]);
      $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataOpname[$i]["opname_detail_hpp"]);
      $dbValue[12] = QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_detail_id"]);
      
      $dbKey[0]   = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel    = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      
      $dtmodel->Insert() or die("insert  error");
        
      unset($dbField);
      unset($dbValue);
      /* INSERT / UPDATE STOK ITEM */


      /* UPDATE STOK DEP */
      $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($dataOpname[$i]["opname_detail_jumlah"])).", stok_dep_create = current_timestamp, stok_dep_tgl = current_date  where id_item = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"])." and id_gudang =".QuoteValue(DPE_CHAR,$id_gudang);
      $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      /* UPDATE STOK DEP */

      /* SQL STOK DEP PERIODE */
      $sql = "select * from logistik.logistik_stok_dep_periode where id_item = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"])." and stok_dep_periode_tgl >= '".$dataOpname[$i]['penerimaan_periode_tanggal_awal']."' and stok_dep_periode_tgl <= '".$dataOpname[$i]['penerimaan_periode_tanggal_akhir']."' and id_gudang =".QuoteValue(DPE_CHAR,$id_gudang);              
      $dataStokDepPeriode = $dtaccess->Fetch($sql);
      /* SQL STOK DEP PERIODE */

      /* INSERT / UPDATE STOK DEP PERIODE */
      $dbTable = "logistik.logistik_stok_dep_periode";
             
      $dbField[0]  = "stok_dep_periode_id";   // PK
      $dbField[1]  = "id_item";
      $dbField[2]  = "stok_dep_periode_saldo";
      $dbField[3]  = "stok_dep_periode_create";
      $dbField[4]  = "stok_dep_periode_tgl";    
      $dbField[5]  = "id_gudang";
      $dbField[6]  = "id_dep";
      $dbField[7]  = "id_periode";

      $stokdepPerId = ($dataStokDepPeriode) ? $dataStokDepPeriode["stok_dep_periode_id"] : $dtaccess->GetTransID();

      $dbValue[0] = QuoteValue(DPE_CHAR,$stokdepPerId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"]);
      $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($dataOpname[$i]["opname_detail_jumlah"]));    
      $dbValue[3] = QuoteValue(DPE_DATE,$dataOpname[$i]["opname_tanggal"]);
      $dbValue[4] = QuoteValue(DPE_DATE,$dataOpname[$i]["opname_tanggal"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,$id_gudang);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_CHAR,$dataOpname[$i]["id_periode"]);

      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

      ($dataStokDepPeriode) ? $dtmodel->Update() or die("update  error") : $dtmodel->Insert() or die("insert  error");

      unset($dbTable);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);
      /* INSERT / UPDATE STOK DEP PERIODE */

      /* SQL PENGURUTAN */
      $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$id_gudang)." and id_item = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"])." order by id_gudang asc, stok_item_create asc";
      $dataAdjustment = $dtaccess->FetchAll($sql);
      /* SQL PENGURUTAN */

      $arrayAdjusment = [];
      $arrayStokItemId = [];
      $arrayAdjusmentJumlah = [];
      $saldo = 0;

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
        if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
        
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

      $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo))." where id_item = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["id_item"])." and id_gudang =".QuoteValue(DPE_CHAR,$id_gudang);
      $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

      $sql = "UPDATE logistik.logistik_opname_detail set is_verified = 'y' where opname_detail_id = ".QuoteValue(DPE_CHAR,$dataOpname[$i]["opname_detail_id"]);
      $dtaccess->Execute($sql);

      /// KEBUTUHAN JURNAL
      $HeaderJurnal = "Jurnal Koreksi Stok item ".$dataOpname[$i]['item_nama']." periode ".$dataOpname[$i]['penerimaan_periode_nama'];
      if ($dataOpname[$i]['id_gudang'] == '2') { 
        $Beban = '02010102070202';
        $Persediaan = '010103010904';
        $FlagJurnal = 'KSG';
      }elseif ($dataOpname[$i]['id_gudang'] == '3') { 
        $Beban = '02010102070201';
        $Persediaan = '010103010902';
        $FlagJurnal = 'KSR';
      }
      else if($dataOpname[$i]['id_gudang'] == '1'){
        // $Beban = '02010102070201';
        // $Persediaan = '010103010901'; wes bener
        $FlagJurnal = 'KSP';
      }
      $Hpp = abs($dataOpname[$i]['opname_detail_selisih']) * $dataOpname[$i]['opname_detail_hpp'];

        $dbTable = "gl.gl_buffer_transaksi";
        $dbField[0]  = "id_tra";   // PK
        $dbField[1]  = "ref_tra";   
        $dbField[2]  = "tanggal_tra"; 
        $dbField[3]  = "ket_tra";
        $dbField[4]  = "namauser";
        $dbField[5]  = "real_time";
        $dbField[6]  = "dept_id";
        $dbField[7]  = "ref_tra_urut";
        $dbField[8]  = "flag_jurnal";
        $dbField[9]  = "ref_tra_urutan";

        $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
                where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AP-%' 
                and tanggal_tra = ".QuoteValue(DPE_DATE,$dataOpname[$i]['opname_tanggal'])." 
                order by ref_tra_urut desc";
        $lastKode = $dtaccess->Fetch($sql);
        $noRef = $lastKode["kode"]+1;  

        $Tanggal = date('ymd', strtotime($dataOpname[$i]['opname_tanggal']));

        $transaksiId = $dtaccess->GetTransId();
        $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
        $dbValue[1] = QuoteValue(DPE_CHAR,'AP'."-".$Tanggal.$noRef);
        $dbValue[2] = QuoteValue(DPE_DATE,$dataOpname[$i]['opname_tanggal']);
        $dbValue[3] = QuoteValue(DPE_CHAR,$HeaderJurnal);
        $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
        $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
        $dbValue[8] = QuoteValue(DPE_CHAR,$FlagJurnal); //Flag Pendapatan Rawat Jalan
        $dbValue[9] = QuoteValue(DPE_CHAR,'AP'.$noRef);
        //      print_r($dbValue); die();
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$Beban);
          $dbValue[3] = QuoteValue(DPE_CHAR,$HeaderJurnal);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if ($dataOpname[$i]['opname_detail_selisih'] > 0) {
            $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.StripCurrency(abs($Hpp)));
          }else{
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($Hpp)));
          }
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);

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
          $dbValue[2] = QuoteValue(DPE_CHAR,$Persediaan);
          $dbValue[3] = QuoteValue(DPE_CHAR,$HeaderJurnal);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          if ($dataOpname[$i]['opname_detail_selisih'] > 0) {
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($Hpp)));
          }else{
            $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.StripCurrency(abs($Hpp)));
          }
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);

    }

  }

  if($_POST["btnLanjut"] || $_POST["btnExcel"] || $_POST["id_periode"])   {
           
     $sql = "SELECT * from logistik.logistik_opname_detail a
             left join logistik.logistik_item b on a.id_item = b.item_id 
             left join logistik.logistik_opname c on a.id_opname = c.opname_id 
             left join logistik.logistik_grup_item d on b.id_kategori = d.grup_item_id";
     $sql .= " where opname_detail_selisih <> 0 and opname_flag = 'M' and ".$sql_where;
     $sql .= " order by opname_id, opname_detail_urut";

     $dataTable = $dtaccess->FetchAll($sql);

     // echo $sql;

     $periode = $_POST["id_periode"];
     $sql = "SELECT * from logistik.logistik_penerimaan_periode WHERE penerimaan_periode_id = '$periode' order by penerimaan_periode_tanggal_awal desc limit 1";
     $periodeSeb = $dtaccess->Fetch($sql);
      }

  //*-- config table ---*//
  
  $PageHeader = "VERIFIKASI STOK OPNAME";

  $tableHeader = 'VERIFIKASI STOK OPNAME';
  
  $sql = "select * from global.global_auth_user where id_rol <> '2'"; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 

    $sql = "select * from logistik.logistik_gudang where  gudang_id = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])."
    and  (gudang_flag = 'M' or gudang_flag is null or gudang_flag='') order by gudang_nama asc";           
  $pilihGudang = $dtaccess->Fetch($sql);

   //-- bikin combo box untuk Tujuan --//
    $sql = "select * from logistik.logistik_gudang where id_dep =".QuoteValue(DPE_CHAR,$depId)."
            and  (gudang_flag = 'M' or gudang_flag is null or gudang_flag='') order by gudang_nama asc"; 
    $rs = $dtaccess->Execute($sql);            
    $dataGudang = $dtaccess->FetchAll($rs);

    $id_petugas=$_POST["id_petugas"];
    $tglAwal=format_date($_POST["tanggal_awal"]);
    $tglAkhir=$_POST["tanggal_akhir"];
    $penjualanTipe=$_POST["penjualan_tipe"];
  
          //Data Klinik
          /*$sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);*/
            if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_opname.xls');
     }
          
      if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
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
     
?>


<script language="Javascript">
<?php $plx->Run(); ?>

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

<?php if($_x_mode=="cetak"){ ?> 
  BukaWindow('opname_cetak.php?id_gudang=<?php echo $_POST["id_gudang"];?>&id_periode=<?php echo $_POST["id_periode"];?>','Opname');
  //document.location.href='<?php echo $thisPage;?>';
<?php } ?>

function CariPeriode(id){ 
  document.getElementById('div_periode').innerHTML = GetPeriode(id,'type=r');
}

 

</script>
  <body class="nav-md">
  <?php if(!$_POST["btnExcel"]) { ?>
  <?php require_once($LAY."header.php") ?>


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
                    <h2>Laporan Verifikasi Stok Opname <?php echo $pilihGudang['gudang_nama']; ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
          
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Tahun </label>
          <div>
            <?php echo $view->RenderComboBox("tahun","tahun",$tahun,null,null,"onchange=\"javascript:return CariPeriode(document.getElementById('tahun').value);\"");?>
      </div>
    </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Periode </label>
          <div>
            <div id="div_periode"><?php echo GetPeriode($_POST["tahun"]);?></div>
           
      </div>
    </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;&nbsp;Gudang&nbsp;</label>
      <div>
        <select name="id_gudang" id="id_gudang" class="form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
          <option value="--">[- Pilih Gudang -]</option>
            <?php for($i=0,$n=count($dataGudang);$i<$n;$i++) { ?>
             <option value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
            <?php } ?>               
        </select>
      </div>
      </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
          <div>
      <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary" onClick="javascript:return CheckDataSave(this.form);">
      <input type="submit" class="pull-right btn btn-success"  name="btnExcel" value="Export Excel" class="submit">
      <input type="submit" name="btnCetak" id="btnCetak" class="pull-right btn btn-primary"  value="Cetak" class="pull-right btn btn-default">
          </div>
     </div>
</form>
<?php } ?>
<?php if($_POST["btnExcel"]) {?>

     <table class="table table-striped table-bordered dt-responsive nowrap" width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr width="100%" class="tableheader">
               <td width="100%" align="center" colspan="11">
               <strong>LAPORAN VERIFIKASI STOK OPNAME <?php echo $pilihGudang['gudang_nama']; ?><br />
               <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
               
               </strong>
               </td>          
          </tr>
          <tr class="tableheader">
          <td align="left" colspan="11">
          <?php echo $poliNama; ?><br />
          Periode : <?=$periodeSeb['penerimaan_periode_nama']?><br>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               <br />
          </td>
          </tr>
     </table>
<?php }?> 

<form name="frmVerif" method="POST" id="frmVerif" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table id="opname" class="table table-striped table-bordered" <?=($_POST["btnExcel"]) ? "border='1'" : "" ?> cellpadding="0" cellspacing="0">
  <thead>
  <tr>
    <th rowspan="2" style="text-align: center;">NO</th>
    <?php if(!$_POST["btnExcel"]) { ?>
    	<th rowspan="2" style="text-align: center;">Check <input type="checkbox" id="checkAllll"></th>
	<?php }?>
    <th rowspan="2" style="text-align: center;">Kode</th>
    <th rowspan="2" style="text-align: center;">Nama Obat</th>
    <th rowspan="2" style="text-align: center;">Kel.</th>
    <th rowspan="2" style="text-align: center;">HPP</th>
    <th colspan="2" style="text-align: center;">Stok Tercatat</th>
    <th colspan="2" style="text-align: center;">Stok Fisik</th>
    <th colspan="2" style="text-align: center;">Selisih</th>
    <th rowspan="2" style="text-align: center;">Keterangan</th>
  </tr>
  
  <tr>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
  </tr>
</thead>

  <?php
  $jumlahVerified = 0;
  for($i = 0, $n = count($dataTable); $i < $n; $i++){
    $awal = $dataTable[$i]['opname_detail_jumlah_sebelumnya'];
    $selisih = $dataTable[$i]['opname_detail_selisih'];
    ?>
  <tr>
    <td><?=$i+1?></td>
    <?php if(!$_POST["btnExcel"]) { ?>
    	<td>
        <?php
        if($dataTable[$i]['is_verified'] == 'n'){
        ?>
    		<input type="checkbox" name="item[]" id="itemss" value="<?=$dataTable[$i]['opname_detail_id']?>">
        <?php }
        else{
          $jumlahVerified ++;
        }
         ?>
    	</th>
	<?php }?>
    <td><?=$dataTable[$i]['item_kode']?></td>
    <td><?=$dataTable[$i]['item_nama']?></td>
    <td><?=$dataTable[$i]['grup_item_nama']?></td>
    <td><?=$dataTable[$i]['opname_detail_hpp']?></td>
    <td><?=number_format($awal, 2, ',', '.')?></td>
    <td><?=number_format($awal*$dataTable[$i]["opname_detail_hpp"], 0, ',', '.')?></td>

    <?php
    $hpp = $awal*$dataTable[$i]["opname_detail_hpp"];
    $opname += $dataTable[$i]["opname_detail_jumlah"];
    $hppOpname += $hpp;
    ?>

    <td><?=number_format($dataTable[$i]["opname_detail_jumlah"], 2, ',', '.')?></td>
    <td><?=number_format($dataTable[$i]["opname_detail_jumlah"]*$dataTable[$i]["opname_detail_hpp"], 0, ',', '.')?></td>

    <td><?=number_format($selisih, 2, ',', '.')?></td>
    <td><?=number_format($dataTable[$i]["opname_detail_hpp"]*$selisih, 0, ',', '.')?></td>
    <td><?=$dataTable[$i]["opname_detail_keterangan"]?></td>
  </tr>
    <?php
    $hpp_akhir = $dataTable[$i]["opname_detail_jumlah"]*$dataTable[$i]["opname_detail_hpp"];
    $hpp_selisih = $dataTable[$i]["opname_detail_hpp"]*$selisih;
    $akhir += $dataTable[$i]["opname_detail_jumlah"];
    $hppAkhir += $hpp_akhir;
    $hppSelisih += $hpp_selisih;
  }
  ?>
  <tr>
    <td></td>
    <?php if(!$_POST["btnExcel"]) { ?>
    	<td></th>
	<?php }?>
    <td></td>
    <td>Jumlah</td>
    <td></td>
    <td></td>
    <td><!-- <?=number_format($opname, 2, ',', '.')?> --></td>
    <td><?=number_format($hppOpname, 0, ',', '.')?></td>
    <td><!-- <?=number_format($akhir, 2, ',', '.')?> --></td>
    <td><?=number_format($hppAkhir, 0, ',', '.')?></td>
    <td></td>
    <td><?=number_format($hppSelisih, 0, ',', '.')?></td>
    <td></td>
  </tr>
  
</table>
<input type="hidden" name="id_periode" value="<?=$_POST['id_periode']?>">
<input type="hidden" name="id_gudang" value="<?=$_POST['id_gudang']?>">
</form>
<?php if(!$_POST["btnExcel"]) { ?>
</div>
</div>

<div class="x_panel">
  <div class="x_content">
    
      

      
      <?php if(count($dataTable) > 0 && ($userData["rol"] == '1' || $userData["rol"] == '35') && $jumlahVerified != count($dataTable)) { ?>
        <button class="btn btn-success pull-right" id="verif">Verifikasi</button> 
      <?php } ?>
    
  </div>
</div>
<?php }?>
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <script>
      $(document).ready(function() {
	      $('#opname').DataTable({
	        "paging": false,
	        "searching": false,
	        "info": false,
	        "order": false,
	        "fixedHeader": true
	      });

        <?php
        if($jumlahVerified == count($dataTable)){
          ?>
          $("input#checkAllll").css("display", "none");
          <?php
        }
        ?>
      });

      $("button#verif").click(function() {
      	var confrm = confirm("Apakah anda yakin ?");

      	if(confrm){
      		$("form#frmVerif").submit();
      	}
      });

      $("input#checkAllll").click(function() {
        

        if($(this).is(":checked")){
          $("input#itemss").prop("checked", true);
        }
        else{
          $("input#itemss").prop("checked", false);
        }
      });

      

    </script>
<?php require_once($LAY."js.php") ?>
</div>
</div>
</div>
</div>
</body>
</html>                                   
 
