<?php
      require_once("../penghubung.inc.php");
      require_once($LIB."login.php");
      require_once($LIB."encrypt.php");
      require_once($LIB."datamodel.php");
      require_once($LIB."tampilan.php");     
      require_once($LIB."currency.php");
      require_once($LIB."dateLib.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","70%","left");
     $usrId = $auth->GetUserId();
     $depId = $auth->GetDepId();
     
      if($_POST["id"])  
     {
       $transferId = & $_POST["id"];  
     }
     else if($_GET["id"])  
     {
       $transferId = $enc->Decode($_GET["id"]); 
     }
     
     if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
     if(!$_POST["klinik"]) $_POST["klinik"] = $depId;
     
    $skr = date("Y-m-d"); 
    $editPage = "transfer_stok_detail_edit.php?id=".$enc->Encode($transferId);
    $thisPage = "transfer_stok_detail_view.php?id=".$enc->Encode($transferId);
    $backPage = "transfer_stok_view.php";
    $transferPage = "transfer_stok_view.php";
    
 
     
     $sql = "select a.*,b.gudang_nama as dep_asal,c.gudang_nama as dep_tujuan
             from logistik.logistik_transfer_stok a
             left join logistik.logistik_gudang b on a.id_asal = b.gudang_id
             left join logistik.logistik_gudang c on a.id_tujuan = c.gudang_id
             where a.transfer_id = ".QuoteValue(DPE_CHAR,$transferId)." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $dataTransfer = $dtaccess->Fetch($rs);
     
     //jangan dihapus -- untuk lihat tanggal keluar nya nanti di item find
     $_SESSION["tgl"] = $dataTransfer["transfer_tanggal_permintaan"];
     
     $sql = "select a.*, b.item_nama, b.item_tree_kode, c.*,d.sup_nama from logistik.logistik_transfer_stok_detail a
             left join logistik_item b on a.id_item = b.item_id
             left join logistik.logistik_grup_item c on c.grup_item_id=b.id_kategori
             left join global.global_supplier d on b.id_sup = d.sup_id
             where id_transfer = ".QuoteValue(DPE_CHAR,$transferId)." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." 
             order by a.no_urut asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataTable = $dtaccess->FetchAll($rs);

     $sql = "select count(a.transfer_detail_id) as jumlah
             from logistik_transfer_stok_detail a
             where a.id_transfer = ".QuoteValue(DPE_CHAR,$transferId).
             " and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);    
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataJumlah = $dtaccess->Fetch($rs);

     //echo "jumlah stok".$dataJumlah["jumlah"];
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;APPROVE PENGIRIMAN BARANG";
     
     $isAllowedDel = $auth->IsAllowed("transfer_stok",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("transfer_stok",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("transfer_stok",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     $counterHeader++;

    
      $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Barang";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Barang";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;

     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Supplier";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;*/

     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Expire Date";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah Permintaan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah Disetujui";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "HPP";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;

    
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

               $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["grup_item_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_tree_kode"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          /*$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["sup_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;*/

          //$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]); 
          //$tbContent[$i][$counter][TABLE_ALIGN] = "left";
          //$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".number_format($dataTable[$i]["transfer_detail_jumlah_permintaan"], 2, '.', ','); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".number_format($dataTable[$i]["transfer_detail_jumlah"], 2, '.', ','); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".Stripcurrency($dataTable[$i]["hpp"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
        
         
     }
     
     $colspan = count($tbHeader[0]);

     
    $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnApprove" value="Approve" class="submit" onclick="javascript: return Approve();">&nbsp;';
    $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnKembali" value="Kembali" class="submit" onClick="document.location.href=\''.$backPage.'?klinik='.$_POST["klinik"].'\'">&nbsp;';
    
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
     
  if($_POST["btnApprove"]){

        $sql = "SELECT is_approve_terima from logistik.logistik_transfer_stok where transfer_id = ".QuoteValue(DPE_CHAR,$_POST["id_transfer"]);
        $status = $dtaccess->Fetch($sql);
        if($status['is_approve_terima'] == "y"){
              ?>
              <script>
              alert("Barang Telah di Approve");
              window.location.href = "<?=$transferPage?>";
              </script>
              <?php
              exit();
        }
  
   $sql = " update logistik.logistik_transfer_stok set is_approve_terima='y', transfer_jam_pengiriman=".QuoteValue(DPE_DATE,date("H:i:s"))." 
            where transfer_id = ".QuoteValue(DPE_CHAR,$_POST["id_transfer"]);
   $rs = $dtaccess->Execute($sql);

      // LANGAH PERTAMA CHECKING DAHULU //
      // cek apakah item yg di masukkan lebih banyak atau pas //      
      $sql = "select a.id_item, a.id_batch, a.id_dep, a.id_transfer, b.id_asal
              from logistik.logistik_transfer_stok_detail a left join
              logistik.logistik_transfer_stok b on id_transfer = b.transfer_id
              where a.id_transfer = ".QuoteValue(DPE_CHAR,$_POST["id_transfer"])." 
              and a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      $dataStokBuffer = $dtaccess->FetchAll($rs);
      
      // Cek ada apa gak datanya, kalau gaka da di kasih warning saja //
      if(!$dataStokBuffer)  {
             // Tampilkan warning kalau gak ada datanya..
             echo "<script>alert('Maaf , Belum ada Item Barang yang di masukkan');</script>";
             echo "<script>document.location.href='transfer_stok_detail_view.php?klinik=".$_POST["klinik"]."&id=".$enc->Encode($_POST["id_transfer"])."'</script>;";
             exit();
      
      }
      
      for($i=0,$j=count($dataStokBuffer);$i<$j;$i++) {
     
       // jika si user memasukan 2 item batch yg sama maka di ambil salah satu dahulu utk di cek //
       if($dataStokBuffer[$i]["id_item"]!=$dataStokBuffer[$i-1]["id_item"]) {
       
          // Cek total stok yg akan di trasfer ke gudang tujuan //
          $sql = "select sum(transfer_detail_jumlah) as total from logistik.logistik_transfer_stok_detail
                  where id_item = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_item"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_dep"])." and id_transfer =".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_transfer"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataTransStokuff = $dtaccess->Fetch($rs);
         
          // Cek total Saldo di tabel item Batch //
          $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep
                  where id_batch = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]).
                  " and id_gudang = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_asal"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataStokBatch = $dtaccess->Fetch($rs);
          
          // Checking apakah Stok yg di masukkan tidak kelebihan . more than, oh no !!! -,-
          /*if($dataTransStokuff["total"]>$dataStokBatch["total"]) {
              //echo $sql;
              //die();
             // Lihat Nama Item , No. Batch yg terkena penalty krn kebanyakan //
             $sql = "select item_nama, batch_no from logistik.logistik_item a
                     join logistik.logistik_item_batch b on b.id_item = a.item_id
                     where b.batch_id = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"])." and b.id_dep = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_dep"]);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
             $dataStokBatchAlert = $dtaccess->Fetch($rs);
             
             // kasih alert biar si user mengerti kalau si dy input kelebihan //       
             echo "<script>alert('Maaf data yang akan ditransfer terlalu banyak, ITEM : ".$dataStokBatchAlert["item_nama"].", BATCH NO : ".$dataStokBatchAlert["batch_no"].", MAX. STOK : ".currency_format($dataStokBatch["total"])."');</script>";
             echo "<script>document.location.href='transfer_stok_detail_view.php?klinik=".$_POST["klinik"]."&id=".$enc->Encode($transferId)."'</script>;";
             exit();           
          } */                           
        }                 
      }
      
      //die();
      $transferId = $_POST["id_transfer"];
      
      // LANGKAH KEDUA UPDATE DATA BATCH NYA//
      // cek kembali apakah item yg di masukkan lebih banyak atau pas //      
      $sql = "select (a.id_item) as iditem, a.id_batch, a.id_dep, a.id_transfer, (a.hpp/a.transfer_detail_jumlah) as hpp_satuan, b.*
              from logistik.logistik_transfer_stok_detail a
              left join logistik.logistik_item b on b.item_id = a.id_item
              where a.id_transfer = ".QuoteValue(DPE_CHAR,$transferId)." and 
              a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
             // echo $sql; die();
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      $dataStokBufferx = $dtaccess->FetchAll($rs);
      
      for($i=0,$j=count($dataStokBufferx);$i<$j;$i++) {
      // jika si user memasukan 2 item batch yg sama maka di ambil salah satu dahulu utk di cek //
       if($dataStokBufferx[$i]["iditem"]!=$dataStokBufferx[$i-1]["iditem"]) {       
          
          //Ambil Data Transfer Stok
          $sql = "select * from logistik_transfer_stok
                  where transfer_id = ".QuoteValue(DPE_CHAR,$transferId)." and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataTransfer = $dtaccess->Fetch($rs);
          //echo $sql; die();
          
          //Isi Data-data yang diperlukan
          $_POST["id_tujuan"]=$dataTransfer["id_tujuan"];   // ke
          $_POST["id_asal"]=$dataTransfer["id_asal"];      // dari
      
          // Cek total stok yg akan di trasfer ke gudang tujuan //
          $sql = "select sum(transfer_detail_jumlah) as total from logistik.logistik_transfer_stok_detail
                  where id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["id_dep"])." 
                  and id_transfer =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["id_transfer"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataTransStokuffx = $dtaccess->Fetch($rs);
          //echo $sql; die();
         
          // Cek total Saldo di tabel item Batch //
          $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep
                  where id_batch = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["id_batch"]).
                  " and id_dep = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["id_dep"]).
                  " and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataStokBatchx = $dtaccess->Fetch($rs);
        
            // Ngurangi total stok yg di transfer //
            $stokHasil[$i] = $dataStokBatchx["total"] - $dataTransStokuffx["total"];            
         
         /*   
            // Update stok terbaru ke logistik item batch, hasil dari pengurangan ke transfer stok //
            $sql = "update logistik.logistik_stok_batch_dep set 
                    stok_batch_dep_saldo = ".QuoteValue(DPE_NUMERIC,$stokHasil[$i])."
                    where id_batch = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]).
                    " and id_dep = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_dep"])."
                     and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
            $rs = $dtaccess->Execute($sql);
            
            // Jika stok batch sudah jd 0, maka status di update //
            if($stokHasil[$i]=='0.00') {
            $sql = "update logistik.logistik_item_batch set batch_status = ".QuoteValue(DPE_CHAR,'n')."
                    where batch_id = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_dep"])." and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
            $rs = $dtaccess->Execute($sql);            
            }   */          

           
            
      // Langkah Ketiga     
      //Update data terbaru ke logistik transfer Stok
      $dbTable = "logistik_transfer_stok";
               
       $dbField[0] = "transfer_id";   // PK
       $dbField[1] = "transfer_no";
       $dbField[2] = "transfer_nomor";
       $dbField[3] = "transfer_tanggal_permintaan";
       $dbField[4] = "transfer_pengirim";
       $dbField[5] = "transfer_penerima";
       $dbField[6] = "transfer_asal";
       $dbField[7] = "transfer_tanggal_keluar";
       $dbField[8] = "transfer_keterangan";
       $dbField[9] = "id_asal";
       $dbField[10] = "id_tujuan";
       $dbField[11] = "id_dep";
       
       $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_id"]);
       $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataTransfer["transfer_no"]);
       $dbValue[2] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_nomor"]);
       $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_permintaan"]);
       $dbValue[4] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_pengirim"]);
       $dbValue[5] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_penerima"]);
       $dbValue[6] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_asal"]);
       $dbValue[7] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
       $dbValue[8] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_keterangan"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$dataTransfer["id_asal"]);
       $dbValue[10] = QuoteValue(DPE_CHAR,$dataTransfer["id_tujuan"]);
       $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
  
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
  
       $dtmodel->Update() or die("insert  error");	
    
       unset($dtmodel);
       unset($dbField);
       unset($dbValue);
       unset($dbKey);
      
      
      //Ambil Data Transfer Detail Stok
      //$sql = "select * from logistik_transfer_stok_detail
      //        where id_transfer = ".QuoteValue(DPE_CHAR,$transferId)." AND id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
      //$rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      //$dataTransferDetail = $dtaccess->FetchAll($rs);
      
      //Update Semua data Barang yang di Transfer Item
      //for($i=0,$j=count($dataTransferDetail);$i<$j;$i++)
      //{

         $tanggalKeluar = $dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s");
       
         //Hitung stok obat ini di tujuan -- || 
         $sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." and stok_item_create < '$tanggalKeluar'";
         $sql .="order by stok_item_create desc"; 
    
         $rs = $dtaccess->Execute($sql);
         $dataStokTujuan = $dtaccess->Fetch($rs);
             
         //$jumlahStokTujuan - jumlah di stok_tujuan
        if($dataStokTujuan["stok_item_saldo"]) $jumlahStokTujuan = $dataStokTujuan["stok_item_saldo"] + $dataTransStokuffx["total"];
         if(!$dataStokTujuan["stok_item_saldo"]) $jumlahStokTujuan = $dataTransStokuffx["total"];

         $hppSatuan = $dataStokBufferx[$i]['hpp_satuan'];
           
                         
          $stokid = $dtaccess->GetTransID();
          $date = date('Y-m-d H:i:s');
                 
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep_asal";
          $dbField[8]  = "id_dep";
          $dbField[9]  = "id_transfer";
          $dbField[10]  = "stok_item_keterangan";
          $dbField[11]  = "stok_item_hpp";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataTransStokuffx["total"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);   //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'T');
          $dbValue[5] = QuoteValue(DPE_DATE, $tanggalKeluar);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$jumlahStokTujuan); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_id"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_nomor"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$hppSatuan);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);          


         //Hitung stok obat batch ini di tujuan -- || 
         /*$sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
         $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);
         $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);

         $sql .="order by stok_item_batch_create desc";       
         $rs = $dtaccess->Execute($sql);
         $dataStokBatchTujuan = $dtaccess->Fetch($rs);
             
         //$jumlahStokTujuan - jumlah di stok_tujuan
        if($dataStokBatchTujuan["stok_item_batch_saldo"]) $jumlahStokBatchTujuan = $dataStokBatchTujuan["stok_item_batch_saldo"] + $dataTransStokuffx["total"];
         if(!$dataStokBatchTujuan["stok_item_batch_saldo"]) $jumlahStokBatchTujuan = $dataTransStokuffx["total"];


          //insert logistik_stok_item_bacth tujuan
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep_asal";
          $dbField[8]  = "id_dep";
          $dbField[9]  = "id_transfer";
          $dbField[10]  = "stok_item_keterangan";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "id_stok_item";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataTransStokuffx["total"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);   //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'T');
          $dbValue[5] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$jumlahStokBatchTujuan); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_id"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_nomor"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$stokid);
          
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);*/          

  
         //Hitung stok obat ini di stok ASAL -- || 
         $sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="order by stok_item_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataStok = $dtaccess->Fetch($rs);
   
         $last = $dataStok["stok_item_saldo"];
         if($dataStok["stok_item_saldo"]) $jumlahStok = $dataStok["stok_item_saldo"] - $dataTransStokuffx["total"];
         if(!$dataStok["stok_item_saldo"]) $jumlahStok = $dataTransStokuffx["total"];

          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep_tujuan";
          $dbField[8]  = "id_dep";
          $dbField[9]  = "id_transfer";
          $dbField[10]  = "stok_item_keterangan";
          $dbField[11]  = "stok_item_hpp";
          
          $stokid1 = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid1);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataTransStokuffx["total"]); 
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);   //departemen asal         
          $dbValue[4] = QuoteValue(DPE_CHAR,'T');
          $dbValue[5] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$jumlahStok); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_id"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,"( SBBK No : ".$dataTransfer["transfer_nomor"].")");
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$hppSatuan);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);     
          

         //Hitung stok batch obat ini di stok ASAL -- || 
         /*$sql = "select stok_item_batch_saldo from logistik.logistik_stok_item_batch 
         where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="and id_batch =".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);     
         $sql .="order by stok_item_batch_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataStok = $dtaccess->Fetch($rs);
   
         $last = $dataStok["stok_item_batch_saldo"];
         if($dataStok["stok_item_batch_saldo"]) $jumlahStokBatch = $dataStok["stok_item_batch_saldo"] - $dataTransStokuffx["total"];
         if(!$dataStok["stok_item_batch_saldo"]) $jumlahStokBatch = $dataTransStokuffx["total"];


          //stok item batch asal
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep_tujuan";
          $dbField[8]  = "id_dep";
          $dbField[9]  = "id_transfer";
          $dbField[10]  = "stok_item_keterangan";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "id_stok_item";

          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid1);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataTransStokuffx["total"]); 
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);   //departemen asal         
          $dbValue[4] = QuoteValue(DPE_CHAR,'T');
          $dbValue[5] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$jumlahStokBatch); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataTransfer["transfer_id"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,"( SBBK No : ".$dataTransfer["transfer_nomor"].")");
          $dbValue[11] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$stokid1);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);*/     





          //cek di stok_dep [dep_asal] ada item nya apa ga , jika ga ada maka di input jika ada update
         $sql = "select id_item from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDepAsal = $dtaccess->Fetch($rs);
         
          if(!$dataDepAsal){         
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
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStok);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
                    
          }else{
          $sql  ="update logistik.logistik_stok_dep 
          set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$jumlahStok);
          $sql .=" , stok_dep_create = ".QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $sql .=" , stok_dep_tgl = ".QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);              
          }

          $id_gudang = $_POST["id_asal"];

          $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$id_gudang)." and id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." order by id_gudang asc, stok_item_create asc";
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

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo))." where id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." and id_gudang =".QuoteValue(DPE_CHAR,$id_gudang);
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);  


          //cek di stok_batch dep [dep_asal] ada item nya apa ga , jika ga ada maka di input batch depnya jika ada update
         /*$sql = "select stok_batch_dep_id,id_item from logistik.logistik_stok_batch_dep where 
                 id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_asal"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="and id_batch =".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);
         $sql .="order by stok_batch_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataBatchDepAsal = $dtaccess->Fetch($rs);

          if(!$dataBatchDepAsal){         
          $dbTable = "logistik.logistik_stok_batch_dep";
          $dbField[0]  = "stok_batch_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_batch_dep_saldo";
          $dbField[3]  = "stok_batch_dep_create";
          $dbField[4]  = "stok_batch_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";
          
          $depId = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStokBatch);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
                    
          }else{
          $dbTable = "logistik.logistik_stok_batch_dep";
          $dbField[0]  = "stok_batch_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_batch_dep_saldo";
          $dbField[3]  = "stok_batch_dep_create";
          $dbField[4]  = "stok_batch_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";
          

          $dbValue[0] = QuoteValue(DPE_CHAR,$dataBatchDepAsal["stok_batch_dep_id"]);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStokBatch);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Update() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          } */





          //cek di stok_dep [dep_tujuan] ada item nya apa ga , jika ga ada maka di input jika ada update
         $sql = "select id_item from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDepTujuan = $dtaccess->Fetch($rs);
          
          if(!$dataDepTujuan){         
          $dbTable = "logistik.logistik_stok_dep";
          $dbField[0]  = "stok_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_dep_saldo";
          $dbField[3]  = "stok_dep_create";
          $dbField[4]  = "stok_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          
          $depId2 = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$depId2);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStokTujuan);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
                    
          }else{
          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$jumlahStokTujuan);
          $sql .=" , stok_dep_create = ".QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $sql .=" , stok_dep_tgl = ".QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);              
          }  

          $id_gudang = $_POST["id_tujuan"];

          $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$id_gudang)." and id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." order by id_gudang asc, stok_item_create asc";
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

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo))." where id_item = ".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"])." and id_gudang =".QuoteValue(DPE_CHAR,$id_gudang);
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);   


          //cek di stok_dep [dep_tujuan] ada item nya apa ga , jika ga ada maka di input jika ada update
         /*$sql = "select id_item from logistik.logistik_stok_batch_dep where id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);
         $sql .="and id_batch =".QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);
         $sql .="order by stok_batch_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataBatchDepTujuan = $dtaccess->Fetch($rs);


          if(!$dataBatchDepTujuan){         
          $dbTable = "logistik.logistik_stok_batch_dep";
          $dbField[0]  = "stok_batch_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_batch_dep_saldo";
          $dbField[3]  = "stok_batch_dep_create";
          $dbField[4]  = "stok_batch_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";
          
          $depId2 = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$depId2);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStokBatchTujuan);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_tujuan"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
                    
          }else{
          $dbTable = "logistik.logistik_stok_batch_dep";
          $dbField[0]  = "stok_batch_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_batch_dep_saldo";
          $dbField[3]  = "stok_batch_dep_create";
          $dbField[4]  = "stok_batch_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";
          

          $dbValue[0] = QuoteValue(DPE_CHAR,$dataBatchDepTujuan["stok_batch_dep_id"]);
          $dbValue[1] = QuoteValue(DPE_CHAR,$dataStokBufferx[$i]["iditem"]);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$jumlahStokBatchTujuan);    
          $dbValue[3] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]." ".date("H:i:s"));
          $dbValue[4] = QuoteValue(DPE_DATE,$dataTransfer["transfer_tanggal_keluar"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_asal"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataStokBuffer[$i]["id_batch"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
          
          $dtmodel->Update() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          }*/   



          //update 
          $sql  ="update logistik.logistik_transfer_stok set transfer_flag = ".QuoteValue(DPE_CHAR,'y');
          $sql .=" where transfer_id = ".QuoteValue(DPE_CHAR,$_POST["id_transfer"]); 
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);         
      
        //}     // End For Item detail 
      }                 
    }    
      
    require_once('posting_gl.php');
    $cetak = "yes"; 
   
//   header("location:".$backpage);
//   exit();
  
  }
     
?>
<script language="javascript" type="text/javascript">
function CheckDataSave(frm)
{ 
     
  if(!frm.transfer_nomor.value){
		alert('No. Transfer Harus Diisi');
		frm.po_nomor.focus();
          return false;
	} 
  
	if(confirm('Input Permintaan Barang dilakukan dan tidak bisa dirubah, Apakah anda yakin ?')) {
  BukaWindow('transfer_cetak.php?id=<?php echo $transferId;?>','Pemakaian Logistik');
  //document.frmView.submit();  	
  }else{
  document.location.href='<?php echo $transferPage;?>';
  }
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

function CetakYes(){

  BukaWindow('transfer_cetak.php?id=<?php echo $transferId;?>','Pemakaian Logistik');
  document.location.href='<?php echo $transferPage;?>'; 	
	
}

<?php if($cetak=='yes'){ ?>

  BukaWindow('transfer_cetak.php?id=<?php echo $_POST["id_transfer"];?>','Cetak Pengiriman');
  document.location.href='<?php echo $transferPage;?>'; 	
	  
<?php }  ?>

 function Approve(){
 if(confirm('Pengiriman Implan akan dilakukan dan tidak bisa dirubah, Apakah anda yakin ?'));
      else return false;
 }
</script>


<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6"><?php echo $tableHeader;?></font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr>
          <td width="15%">Nomer</td>
          <td width="1%">:</td>
          <td><?php echo $dataTransfer["transfer_nomor"];?></td>
     </tr>
          <tr>
          <td width="15%">Keterangan</td>
          <td width="1%">:</td>
          <td><?php echo $dataTransfer["transfer_keterangan"];?></td>
     </tr>
     <tr>
          <td>Asal Stok</td>
          <td width="1%">:</td>
          <td><?php echo $dataTransfer["dep_asal"];?></td>
     </tr>
     <tr>
          <td>Tujuan Stok</td>
          <td>:</td>
          <td><?php echo $dataTransfer["dep_tujuan"];?></td>
     </tr>
     <tr>
          <td>Tanggal Permintaan</td>
          <td>:</td>
          <td><?php
          $dataTransfer["transfer_tanggal_permintaan"] = explode(" ",$dataTransfer["transfer_tanggal_permintaan"]);
           echo format_date($dataTransfer["transfer_tanggal_permintaan"][0])." ".$dataTransfer["transfer_tanggal_permintaan"][1];
           ?></td>
     </tr>
     <tr>
          <td>Tanggal Pengiriman</td>
          <td>:</td>
          <td><?php
           echo format_date($dataTransfer["transfer_tanggal_keluar"]);
           ?></td>
     </tr>
</table>
<br>
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<?php echo $view->RenderHidden("klinik","klinik",$depId);?>
<?php echo $view->RenderHidden("id_transfer","id_transfer",$transferId);?>
</form>
</div>
</div>
