<?php
     //LIBRARY 
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");    
     require_once($LIB."tampilan.php");
 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();             
     $auth = new CAuth();
     $skr = date("Y-m-d");
     $time = date("H:i:s");
     $usrId = $auth->GetUserId();	
     $table = new InoTable("table","100%","left");    
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $poli = $auth->GetPoli();
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];
     //echo $theDep;
     
     //AUTHENTIFIKASI
    /* if(!$auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
      */
     //VARIABLE AWAL
    
     if (!$_GET["transaksi"]) 
     {
         $transaksiId=$_POST["transaksi_paket_id"];
     } else {
        $transaksiId=$_GET["transaksi"];
     }
      
     
     $thisPage = "penjualan_bebas.php";
     $findPage = "pasien_luar_find.php?";
     $findDokterPage = "dokter_find.php?";
     $findPage1 = "obat_find.php?jenis_id=".$_POST["cust_usr_jenis"];
     $findPaket = "paket_find1.php?transaksi=".$transaksiId;
     $sellPage = "penjualan_view.php"; 
     $judulForm = "Penjualan Apotik";
     
     if($_GET["id_dokter"]) $_POST["usr"] = $_GET["id_dokter"];
     
     //$sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     //$rs = $dtaccess->Execute($sql);
     //$gudang = $dtaccess->Fetch($rs);
     
     //if($gudang["conf_gudang_obat"]=='L'){
          //$theDep = "1";
     //}else{
          //$theDep = "2";
     //}
     
     if($_x_mode=="New") 
       $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") 
       $privMode = PRIV_UPDATE;
     else 
       $privMode = PRIV_DELETE;    
      
    	if($_POST["x_mode"]) 
        $_x_mode = & $_POST["x_mode"];
    	else 
        $_x_mode = "New";
    
      if(!$_POST["faktur_tanggal"]) $_POST["faktur_tanggal"] = format_date($skr);
      if($_POST["GrandHargaTotals"]) $grandTotals = $_POST["GrandHargaTotals"];
      //echo "pepo".$grandTotals;
      
      //ambil data penjualan baru
      if($_GET["transaksi"])                                   
      {
        $penjualanId = $_GET["transaksi"];
        $penjualan_edit=1;
        $_x_mode = "Edit";
        //if(!$_POST["cust_usr_kode"]) $_POST["cust_usr_kode"] = $enc->Decode($_GET["kode"]);
       // if(!$_POST["id_reg"]) $_POST["id_reg"] = $enc->Decode($_GET["idreg"]);
      } 
      else if($_POST["penjualan_id"]) 
      {
        $penjualanId = $_POST["penjualan_id"]; 
      } 
      else 
      { 
        unset($penjualanId);
       }
     
      if($_POST["penjualan_edit"]) $penjualan_edit=$_POST["penjualan_edit"];
      
      //-- nomor nota otomatis --//  
      if(!$penjualanId)
      {
        //$urut = $dtaccess->GetNewID("apotik.apotik_penjualan","penjualan_urut");
        //$tgl = explode("-",$skr);
        //$_POST["penjualan_no"] = "APRJ".str_pad($urut,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
        //$_POST["hidUrut"] = $urut;
        
            $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId)." and penjualan_flag = 'L'";
            $lastKode = $dtaccess->Fetch($sql);
            $tgl = explode("-",$skr);
            $_POST["penjualan_no"] = "LPRJ".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
            $_POST["hidUrut"] = $lastKode["urut"]+1;
       }
      
       if(!$_POST["penjualan_no"])
       {
         $sql = "select penjualan_nomor from apotik.apotik_penjualan where penjualan_id =".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $jualNom = $dtaccess->Fetch($rs);
         $_POST["penjualan_no"] = $jualNom["penjualan_nomor"];
       }
  
  //PROSES-PROSES SUBMIT
  //AMBIL DATA AWAL UNTUK EDIT
	if($_x_mode == "Edit" || $_POST["btnLanjut"] || $_POST["tombol_f2"] || $_POST["btnTambah"]) {  //Jika ditekan Tombol Lanjut atau keyboard F2s
	
		/*$sql = "select a.*, c.reg_jenis_pasien , c.reg_status , c.reg_tanggal, c.reg_id, c.id_poli, d.rawat_terapi  from global.global_customer_user a
				    left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
            left join klinik.klinik_perawatan d on d.id_reg = c.reg_id 
				    where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"])."  and c.id_dep = ".QuoteValue(DPE_CHAR,$depId)."
            and reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." order by c.reg_tanggal desc,c.reg_waktu desc"; 
		$dataPasien = $dtaccess->Fetch($sql);
    //echo $sql;
    //die();
    
		$_POST["cust_nama"] = htmlspecialchars($dataPasien["cust_nama"]); 
		$_POST["cust_usr_id"] = $dataPasien["cust_usr_id"];
    $_POST["id_poli"] = $dataPasien["id_poli"]; 
		$_POST["cust_usr_nama"] = htmlspecialchars($dataPasien["cust_usr_nama"]); 
		$_POST["cust_usr_alamat"] = htmlspecialchars($dataPasien["cust_usr_alamat"]); 
		$_POST["cust_usr_jenis"] = $dataPasien["reg_jenis_pasien"]; 
    $_POST["id_reg"] = $dataPasien["reg_id"];
    $_POST["reg_tanggal"] = $dataPasien["reg_tanggal"];
    $_POST["rawat_terapi"] = $dataPasien["rawat_terapi"];
    $_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];  */
    
    $sql = "select * from global.global_auth_user where usr_id = '".$_POST["usr"]."' "   ;
    $namaDokter = $dtaccess->Fetch($sql);
    
    $_POST["usr_name"] = $namaDokter["usr_name"];
   
   if($penjualanId){ 
    
    $sql = "select a.* from apotik.apotik_penjualan a 
				    where a.penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId)."  and a.id_dep = ".QuoteValue(DPE_CHAR,$depId); 
		$dataPasien = $dtaccess->Fetch($sql);
    //echo $sql;
    //die();
    
		$_POST["cust_usr_nama"] = $dataPasien["cust_usr_nama"];
    $_POST["penjualan_alamat"] = $dataPasien["penjualan_alamat"]; 
    $_POST["penjualan_nomor"] = $dataPasien["penjualan_nomor"];
    
    
    }
  //  $lokasi = $ROOT."gambar/foto_pasien";    
    
    if (!$dataPasien && $_x_mode=="New")  //Cek Data Pasien jika Mode Pilih Pasien
    {
        $_x_mode = "New";
        $_pasien_salah = TRUE;
    }
    else if ($dataPasien && $_POST["tombol_f2"]==1)
    {
        $_x_mode = "Edit"; //Mode memasukkan Obat
    }                             
	}//end lanjut     
 
 // if ($_x_mode == "Edit" && !$penjualanId)  //Jika menyimpan penjualan
  if ($_POST["btnTambah"] && !$dataPasien) 
  {
      $dbTable = "apotik.apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_nomor";   
      $dbField[2]  = "penjualan_urut"; 
      $dbField[3]  = "id_cust_usr";
      $dbField[4]  = "cust_usr_nama";
      $dbField[5]  = "id_jenis_pasien";
      $dbField[6]  = "penjualan_flag";
      $dbField[7]  = "penjualan_create";
      $dbField[8]  = "who_update";
      $dbField[9]  = "id_gudang";
      $dbField[10]  = "id_dokter";
      $dbField[11]  = "dokter_nama";
      $dbField[12]  = "id_dep";
      $dbField[13]  = "id_reg";
      $dbField[14]  = "penjualan_alamat";
   
      $penjualanId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["hidUrut"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,'100');
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
      $dbValue[5] = QuoteValue(DPE_NUMERIC,'2');
      $dbValue[6] = QuoteValue(DPE_CHAR,'L');
      $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[8] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
      $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["dokter_nama"]);
      $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[13] = QuoteValue(DPE_CHAR,$regId);
      $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["penjualan_alamat"]);
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue); 
      
     $next = "penjualan_bebas.php?transaksi=".$penjualanId;
     header("location:".$next);
     exit();                                        
  }
  
  
  //JIKA MELAKUKAN PEMESANAN OBAT 
     if ($_POST["btnUpdate"] || $_POST["btnSave"]) {
          
          $dateSekarang = date('Y-m-d H:i:s');
          
          $dbTable = "apotik.apotik_penjualan_detail";
          $dbField[0]  = "penjualan_detail_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "penjualan_detail_harga_jual";
          $dbField[4]  = "penjualan_detail_jumlah";
          $dbField[5]  = "penjualan_detail_total";
          $dbField[6]  = "penjualan_detail_flag";
          $dbField[7]  = "penjualan_detail_create";
          $dbField[8]  = "id_petunjuk";
          $dbField[9]  = "id_dep";
          $dbField[10]  = "penjualan_detail_sisa";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "penjualan_detail_tuslag";
          
          if (!$_POST["btn_edit"])         //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["btnSave"]);
          unset($_POST["obat_id"]);
          unset($_POST["obat_kode"]);
          unset($_POST["obat_nama"]);
          unset($_POST["txtTuslag"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["txtDibayar"]);
          unset($_POST["txtBalik"]);
          unset($_POST["txtBack"]);
          unset($_POST["txtDiskon"]);
         /* 
          if ($_POST["obat_id"]) {
           
          $idstok = $dtaccess->GetTransID();
          $date1 = date('Y-m-d H:i:s');
          
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_item_jumlah";
          $dbField[3]  = "stok_item_create";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "stok_item_saldo";
          
          $sql = "select stok_item_saldo from logistik.logistik_stok_item
          where id_gudang = '2' and id_item =".QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $sql .= "order by stok_item_create desc";
          $rs = $dtaccess->Execute($sql);
          $dataStok = $dtaccess->Fetch($rs);
          
          $jumlahStok = $dataStok["stok_item_saldo"] - StripCurrency($_POST["txtJumlah"]);
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$idstok);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[3] = QuoteValue(DPE_DATE,$date1);
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_CHAR,'2');   //id departemen apotik
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$jumlahStok);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["btnSave"]);
          unset($_POST["item_nama"]);
          unset($_POST["item_kode"]);
          unset($_POST["stok"]);
   }        */
}    //AKHIR PEMESANAN OBAT

         $sql = "select sum(penjualan_detail_total) as penjualan_total_detail from apotik.apotik_penjualan_detail  where 
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $total = $dtaccess->Fetch($rs);
         
         $_POST["penjualan_total_detail"] = $total["penjualan_total_detail"];

     
         //Jika Melakukan Pembayaran
     if ($_POST["btnBayar"]) {
     
    // $_POST["penjualan_total_obat"]=$_POST["txtBalik"];

          
     if ($_POST["obat_id"]) {
     
     $dateSekarang = date('Y-m-d H:i:s');
          
          $dbTable = "apotik.apotik_penjualan_detail";
          $dbField[0]  = "penjualan_detail_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "penjualan_detail_harga_jual";
          $dbField[4]  = "penjualan_detail_jumlah";
          $dbField[5]  = "penjualan_detail_total";
          $dbField[6]  = "penjualan_detail_flag";
          $dbField[7]  = "penjualan_detail_create";
          $dbField[8]  = "id_petunjuk";
          $dbField[9]  = "id_dep";
          $dbField[10]  = "penjualan_detail_sisa";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "penjualan_detail_tuslag";          
          if (!$_POST["btn_edit"]) //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
                    
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
          unset($_POST["btnSave"]);
          unset($_POST["obat_id"]);
          unset($_POST["obat_kode"]);
          unset($_POST["obat_nama"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["txtTuslag"]);
          
     }
     
      $isprint = "n"; 

      $grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"]);
      
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJualCheck = $dtaccess->FetchAll($rs);
      
      for($x=0,$a=count($dataJualCheck);$x<$a;$x++){
      
       // jika si user memasukan 2 item batch yg sama maka di ambil salah satu dahulu utk di cek //
       if($dataJualCheck[$x]["id_batch"]!=$dataJualCheck[$x-1]["id_batch"]) {
       
          // Cek total stok yg akan di trasfer ke gudang tujuan //
          $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                  where id_batch = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"])." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataTransStokuff = $dtaccess->Fetch($rs);
         
          // Cek total Saldo di tabel item Batch //
          $sql = "select sum(batch_stok_saldo) as total from logistik.logistik_item_batch
                  where batch_id = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataStokBatch = $dtaccess->Fetch($rs);
          
          // Checking apakah Stok yg di masukkan tidak kelebihan . more than, oh no !!! -,-
/*          if($dataTransStokuff["total"]>$dataStokBatch["total"]) {
          
             // Lihat Nama Item , No. Batch yg terkena penalty krn kebanyakan //
             $sql = "select item_nama, batch_no from logistik.logistik_item a
                     join logistik.logistik_item_batch b on b.id_item = a.item_id
                     where b.batch_id = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and b.id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"]);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
             $dataStokBatchAlert = $dtaccess->Fetch($rs);
             
             // kasih alert biar si user mengerti kalau si dy input kelebihan //       
             echo "<script>alert('Maaf data yang akan ditransfer terlalu banyak, ITEM : ".$dataStokBatchAlert["item_nama"].", BATCH NO : ".$dataStokBatchAlert["batch_no"].", MAX. STOK : ".currency_format($dataStokBatch["total"])."');</script>";
             echo "<script>document.location.href='penjualan_bebas.php?transaksi=".$penjualanId."'</script>;";
             exit();           
          }  */                          
        } 
      }  
        //die();
        //die();

      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "apotik.apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_create";
      $dbField[2]  = "penjualan_nomor";
      $dbField[3]  = "penjualan_total";     
      $dbField[4]  = "penjualan_terbayar";
      $dbField[5]  = "who_update";
      $dbField[6]  = "id_gudang";
      $dbField[7]  = "penjualan_flag";
      $dbField[8]  = "penjualan_catatan";
      $dbField[9]  = "penjualan_pajak";
      $dbField[10]  = "penjualan_diskon";
      $dbField[11]  = "penjualan_diskon_persen";
      $dbField[12]  = "penjualan_biaya_resep";
      $dbField[13]  = "penjualan_biaya_racikan";
      $dbField[14]  = "penjualan_biaya_bhps";
      $dbField[15]  = "penjualan_biaya_pembulatan";
      $dbField[16]  = "id_dep";
      $dbField[17]  = "penjualan_grandtotal";
      $dbField[18]  = "penjualan_bayar";
      $dbField[19]  = "penjualan_keterangan";
      $dbField[20]  = "penjualan_tuslag";
      $dbField[21]  = "id_fol";
      $dbField[22]  = "id_dokter";
      $dbField[23]  = "dokter_nama";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_detail"]));  
      $dbValue[4] = QuoteValue(DPE_CHAR,'y');
      $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[7] = QuoteValue(DPE_CHAR,'L');
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["penjualan_catatan"]);
      $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtPPN"])); 
      $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
      $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])); 
      $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtResep"]));
      $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaRacikan"]));
      $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaBhps"])); 
      $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"]));
      $dbValue[16] = QuoteValue(DPE_CHAR,$depId); 
      $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
      $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"]));
      $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["fol_keterangan"]); 
      $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
      $dbValue[21] = QuoteValue(DPE_CHAR,$folId);
      $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["usr"]);
      $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      
            
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
      $dtmodel->Update() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
          
              
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJual = $dtaccess->FetchAll($rs);
      for($i=0,$n=count($dataJual);$i<$n;$i++){
      
       // cek apakah ada dua batch atau lebih yg di input //
        if($dataJual[$i]["id_batch"]!=$dataJual[$i-1]["id_batch"]) {        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
          
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
           
           //stok batch yg lama - stok baru (dikurangi)
           $stokBatchNow[$i] = $dataDepBatch["total"] - $dataPenjualanStok["total"];
          
          
          // Langsung Update Stok Batch di Gudangnya //
          $sql  ="update logistik.logistik_stok_batch_dep set 
                  stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokBatchNow[$i]);
          $sql .=" , stok_batch_dep_create = current_timestamp";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
          $rs = $dtaccess->Execute($sql);
         
         
         //END UPDATE POSISI STOK BATCH TERAKHIR 
         
         
         //UPDATE POSISI STOK TERAKHIR
         
         //cek di stok_dep untuk melihat stokterakhir
         $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
         $sql .="and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDep = $dtaccess->Fetch($rs);         
         
         //stok lama - stok baru (dikurangi)
          $stokNow[$i] = $dataDep["stok_dep_saldo"] - $dataJual[$i]["penjualan_detail_jumlah"];

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokNow[$i]);
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
          
          //---------------- END UPDATE POSISI STOK TERAKHIR
          //cari harga beli terakhir item
          $sql = " select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $rs = $dtaccess->Execute($sql);
           $dataHargabeli = $dtaccess->Fetch($rs);
          
          //insert kartu stok untuk histry batch untuk penjualan
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_batch_hpp";
          $dbField[11]  = "stok_item_batch_hna";
          $dbField[12]  = "stok_item_batch_hna_ppn_minus_diskon";
          $dbField[13]  = "id_batch";
          
          $date = date("Y-m-d H:i:s");
          $stobatkid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stobatkid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //insert kartu stok untuk penjualan
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";         
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_hpp";
          $dbField[11]  = "stok_item_hna";
          $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);      
         } // end detail
      } 


                //INSERT KLINIK REGISTRASI
                $dbTable = "klinik.klinik_registrasi";
           
                $dbField[0] = "reg_id";   // PK
                $dbField[1] = "reg_tanggal";
                $dbField[2] = "reg_waktu";
                $dbField[3] = "id_cust_usr";
                $dbField[4] = "reg_status";
                $dbField[5] = "reg_who_update";
                $dbField[6] = "reg_when_update";
                $dbField[7] = "id_dep";
                $dbField[8] = "id_pembayaran";
                $dbField[9] = "reg_jenis_pasien";
                $dbField[10] = "id_poli";
                $dbField[11] = "reg_tipe_rawat";
                
                $status = 'A0';  // Status Apotik --
                $regId = $dtaccess->GetTransID();      
                $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
                $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
                $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
                $dbValue[3] = QuoteValue(DPE_CHAR,'100');//DIPATEN 100 untuk Penjualan Obat dari Luar
                $dbValue[4] = QuoteValue(DPE_CHAR,$status);
                $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                $dbValue[8] = QuoteValue(DPE_CHAR,$byrId);
                $dbValue[9] = QuoteValue(DPE_CHAR,'2');
                $dbValue[10] = QuoteValue(DPE_CHAR,$poli);
                $dbValue[11] = QuoteValue(DPE_CHAR,'J');
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                
                $dtmodel->Insert() or die("insert error"); 

                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);

         
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
              $dbField[9] = "pembayaran_yg_dibayar";
              
               $byrId = $dtaccess->GetTransID();

               $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrId);
               $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
               $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
               $dbValue[4] = QuoteValue(DPE_CHAR,$regId);
               $dbValue[5] = QuoteValue(DPE_CHAR,'100');
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$beaNominale);
               $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[8] = QuoteValue(DPE_CHAR,'n');
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'0.00');
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);

           $sql = "update klinik.klinik_registrasi set id_pembayaran = ".
           QuoteValue(DPE_CHAR,$byrId)." where reg_id = ".
           QuoteValue(DPE_CHAR,$regId);
           $rs = $dtaccess->Execute($sql);

           $sql = "update apotik.apotik_penjualan set id_reg = ".QuoteValue(DPE_CHAR,$regId)." where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql);

          $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
                   where reg_id=".QuoteValue(DPE_CHARKEY,$regId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $idPemb = $dtaccess->Fetch($sqlpemb);

        $sql = "select count(id_item) as total_item from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
        $rs = $dtaccess->Execute($sql);
        $jumlahTotalObat = $dtaccess->Fetch($rs);

          //INSERT FOLIO
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
          $dbField[12] = "fol_dibayar";
          $dbField[13] = "fol_dibayar_when";
          $dbField[14] = "fol_total_harga";
          $dbField[15] = "id_pembayaran";
          $dbField[16] = "fol_keterangan";
          $dbField[17] = "fol_hrs_bayar";
          $dbField[18] = "fol_catatan";
          $dbField[19] = "fol_nominal_satuan";
          $dbField[20] = "fol_jumlah";
          $dbField[21] = "id_dokter";
          $dbField[22] = "who_when_update";
                                                  
         $sqltdk = "select biaya_jenis, biaya_nama, biaya_id from klinik.klinik_biaya where biaya_jenis = 'O' and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $dataObat = $dtaccess->Fetch($sqltdk);
         $date = date('Y-m-d H:i:s');                
               
               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[4] = QuoteValue(DPE_CHAR,'OA');
               $dbValue[5] = QuoteValue(DPE_CHAR,'100');//DIPATEN 100 untuk Penjualan Obat dari Luar
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
               $dbValue[9] = QuoteValue(DPE_CHARKEY,$poli);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,'2');
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[13] = QuoteValue(DPE_DATE,$date);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[15] = QuoteValue(DPE_CHAR,$byrId); 
               $dbValue[16] = "'".$_POST["cust_usr_nama"]." (".$_POST["penjualan_alamat"].")'";
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["penjualan_nomor"]);     
               $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[20] = QuoteValue(DPE_NUMERIC,$jumlahTotalObat["total_item"]);
               $dbValue[21] = QuoteValue(DPE_CHAR,$usrId);
               $dbValue[22] = QuoteValue(DPE_CHAR,$usrId);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);

               $sql = "update apotik.apotik_penjualan set id_fol=".QuoteValue(DPE_CHAR,$folId)."  , id_reg = ".QuoteValue(DPE_CHAR,$regId)."
                    where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
      
     //$isprint = "y";
     $cetak = "y";
	   //$next = "penjualan_bebas_cetak.php?id=".$penjualanId;
     //header("location:".$next);
     //exit();
      
     }
      /*
          $transaksiId = $dtaccess->GetTransID();
          
          $dbTable = "apotik.apotik_transaksi";
          $dbField[0]  = "trans_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "trans_jumlah";
          $dbField[4]  = "trans_harga_jual";
          $dbField[5]  = "trans_create";
          $dbField[6]  = "trans_flag";
          $dbField[7]  = "trans_tipe";
          $dbField[8]  = "trans_petunjuk";
          
          
          if($_POST["obat_id"]) $obat_id = $_POST["obat_id"]; 
          if($_POST["txtJumlah"]) $jumlah = $_POST["txtJumlah"];
          if($_POST["txtHargaSatuan"]) $harga_jual = $_POST["txtHargaSatuan"];
          if($_POST["id_petunjuk"] && $_POST["id_petunjuk"]!='--') $petunjuknya = $_POST["id_petunjuk"]; 
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$obat_id);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($jumlah));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($harga_jual));
          $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_CHAR,'J');
          $dbValue[8] = QuoteValue(DPE_CHAR,$petunjuknya);
        
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["btnSave"]);
          unset($_POST["obat_nama"]);
          unset($_POST["obat_kode"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["obat_id"]);
          unset($obat_id);
          unset($jumlah);
          unset($harga_jual);
     }
          
       $sql = "select a.*,b.*,c.* from apotik.apotik_transaksi a
               join logistik.logistik_stok_item b on a.id_item = b.id_item
               left join logistik.logistik_item c on c.item_id = b.id_item 
               where b.id_gudang = '2' and a.id_penjualan = ".QuoteValue(DPE_CHAR, $penjualanId)."
               order by b.stok_item_create desc";
       //echo $sql;        
       $rs_edit = $dtaccess->Execute($sql);
       $dataTable = $dtaccess->FetchAll($rs_edit);
     
		$sql = "select trans_racik_jumlah as racik from apotik.apotik_transaksi a
            where trans_racik_jumlah notnull and a.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId); 
		$dataRacik = $dtaccess->FetchAll($sql);
		//echo $sql;
  
		if($dataRacik!=NULL){
    $sql = "select * from apotik.apotik_ongkos_resep a
            where a.ongkos_inisial = 'racikan'"; 
		$dataracikan = $dtaccess->Fetch($sql);    
    }elseif(!$dataRacik){
    $sql = "select * from apotik.apotik_ongkos_resep a
            where a.ongkos_inisial = 'nonracikan'"; 
		$dataracikan = $dtaccess->Fetch($sql);    
    }  
   */ 
     $tableHeader = "&nbsp;Detail menu penjualan";
     
     $isAllowedDel = $auth->IsAllowed("setup_role",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_role",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_role",PRIV_CREATE);
     
     // --- Buat Tabel Penjualan Detail ---- //
     $counterHeader = 0;
     
     // Jika Obat Dihapus
    /* if($_POST["btnDelete"])
     {
     $penjualanDetailId = & $_POST["cbDelete"];
     for($i=0,$n=count($penjualanDetailId);$i<$n;$i++){
         $sql = "DELETE FROM apotik.apotik_penjualan_detail WHERE penjualan_detail_id = '".$penjualanDetailId[$i]."'";
         $dtaccess->Execute($sql);
     }
     unset($_POST["btnDelete"]);
     }*/
     
     if ($_POST["btnOk"]) {
    
    $sql = "update  apotik.apotik_penjualan set penjualan_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
            where penjualan_id=".QuoteValue(DPE_CHAR,$_POST["penjualan_id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql); 
    
    $kembali = "penjualan_bebas.php?transaksi=".$_POST["penjualan_id"];
    header("location:".$kembali);
    exit();    
    }
     
     if($_GET["del"]) {
     $penjualanDetailId = $_GET["id"];

     $sql = "DELETE FROM apotik.apotik_penjualan_detail WHERE penjualan_detail_id = '".$penjualanDetailId."'";
     $dtaccess->Execute($sql);
     
          $transaksie = $_GET["transe"];      
          $kembali = "penjualan_bebas.php?transaksi=".$transaksie;
          header("location:".$kembali);
          exit();
     }
       
     $sql = "select penjualan_keterangan from apotik.apotik_penjualan
             where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);       
     $rs = $dtaccess->Execute($sql);
     $tanggungan = $dtaccess->Fetch($rs);  
     
     $_POST["fol_keterangan"] = $tanggungan["penjualan_keterangan"];    
        
     $sql = "select *,b.item_nama,b.item_kode,c.jenis_nama,d.petunjuk_nama, e.batch_no, e.batch_tgl_jatuh_tempo
             from apotik.apotik_penjualan_detail a
             left join logistik.logistik_item b on a.id_item=b.item_id 
             left join global.global_jenis_pasien c on b.item_tipe_jenis=c.jenis_id
             left join apotik.apotik_obat_petunjuk d on a.id_petunjuk=d.petunjuk_id
             left join logistik.logistik_item_batch e on e.batch_id = a.id_batch
             where a.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId)."
             order by a.penjualan_detail_create desc";       
     $rs_edit = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs_edit);
     $tableHeader = "&nbsp;Detail Penjualan Obat";

     for($i=0,$n=count($dataTable);$i<$n;$i++) {  
     
     $grandtotalese += $dataTable[$i]["penjualan_detail_total"];
     $Grand = ($grandtotalese+$gudang["conf_biaya_resep"]);
     }
     
    if($_POST["transaksi_id"])
      {  
       $transaksiId = & $_POST["transaksi_id"];
       $updateData = true;
      } 

      $sql = "select * from apotik.apotik_obat_petunjuk";
      $rs = $dtaccess->Execute($sql);
      $r=0;
      $opt_dosis[0] = $view->RenderOption("--","[ PILIH DOSIS ]",$show);
      while($data_dosis=$dtaccess->Fetch($rs))
      {
        unset($show);
        $opt_dosis[] = $view->RenderOption($data_dosis["petunjuk_id"],$data_dosis["petunjuk_nama"],$show);
        $r++;
      }  
  
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' ";
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->FetchAll($rs); 
     // cek biaya racikan di konfigurasi --     
     $_POST["txtResep"] = $gudang["conf_biaya_resep"]; 
     
     $sql = "select * from global.global_auth_user where id_rol = '2' or id_rol = '5' order by usr_name ";
     $rs = $dtaccess->Execute($sql);
     $dataDokter = $dtaccess->FetchAll($rs);
  
?>

<?php //echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">
<script language="Javascript">
function CekTindakan(frm) {

  if(!frm.obat_nama.value){
		alert('Diisi dahulu nama obatnya namanya agar data bisa dimasukkan');
		frm.obat_nama.focus();
          return false;
	}

     	return true;      
}

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan mengahapus obat ini???'));
  else return false;
}

/*function Editobat(id,id_detail,id_obat,nama,harga_jual,jumlah,total,dosis,kode) { 
//alert(dosis);
	document.getElementById('penjualan_id').value = id;
	document.getElementById('btn_edit').value = id_detail;   //Penjualan detail
	document.getElementById('obat_id').value = id_obat;
	document.getElementById('obat_nama').value = nama; 
	document.getElementById('txtHargaSatuan').value = harga_jual; 
	document.getElementById('txtJumlah').value = jumlah; 
	document.getElementById('txtHargaTotal').value = total;
	document.getElementById('id_petunjuk').value = dosis; 
	document.getElementById('obat_kode').value = kode;
}*/	

function GantiHarga(dari) {
     var jumlah = document.getElementById('txtJumlah').value.toString().replace(/\,/g,"")*1;
     var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"")*1;
     var tuslag = document.getElementById('txtTuslag').value.toString().replace(/\,/g,"")*1;
     
     document.getElementById('txtIsiTotale').innerHTML = formatCurrency(duit*jumlah); 
     document.getElementById('txtHargaTotal').value = formatCurrency((duit*jumlah)+tuslag);
   
     document.getElementById('id_petunjuk').focus();
}

function GantiGrandHarga(diskon) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
   // var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;
    var pembulatan  = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"")*1;
     
     if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep+racikan+pembulatan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('Grandstotal').value = (total+resep+racikan+pembulatan)-diskon;
     } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('Grandstotal').value = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtIsi').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
      }
}

function GantiResepHarga(resep) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;

    var pembulatan  = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"")*1;
   
    if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep+racikan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('Grandstotal').value = (total+resep+racikan+pembulatan)-diskon;
    } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('Grandstotal').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
    }
}


/*function GantiGrandHarga(grand) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
    var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;
   
    if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total+resep)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep)-diskon;

     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('Grandstotal').value = (total+resep)-diskon;
    } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total+resep)-diskon);
     document.getElementById('Grandstotal').value = formatCurrency((total+resep)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency((total+resep)-diskon);
    }
}
*/
function GantiRacikanHarga(resep) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
    var pembulatan  = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"")*1;
   
    if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep+racikan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep+racikan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('Grandstotal').value = (total+resep+racikan)-diskon;
    } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('Grandstotal').value = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
    }
}

function GantiPembulatanHarga(resep) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var pembulatan  = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
   
    if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (pembulatan+total+resep+racikan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);     
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('Grandstotal').value = (total+resep+racikan+pembulatan)-diskon;
    } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('Grandstotal').value = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency((pembulatan+total+resep+racikan)-diskon);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
    }
}

function GantiPengurangan(total) {
     var Grandtotal = document.getElementById('Grandstotal').value.toString().replace(/\,/g,"")*1;
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
     var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
     var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;        
     
     //alert(Grandtotal);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Grandtotal);
     document.getElementById('txtBack').value = formatCurrency(bayar-Grandtotal);
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
     var dibayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
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
     
     document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskonpersen);
     document.getElementById('txtTotalDibayar').value = formatCurrency((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-((totalInt+totalBiayaTambahan)+(pajakInt+biayaPembulatanInt-diskon_format)));
     document.getElementById('txtDibayar').focus();
}

function Diskon(diskon,total) {     
     var diskonpersen = document.getElementById('txtDiskonPersen').value.toString().replace(/\,/g,"");
     var total_bayar = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var dibayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
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
    }else{
      document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
      document.getElementById('txtTotalDibayar').value = formatCurrency(totalInt+(pajakInt-diskon_format));
      document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-(totalInt+(pajakInt-diskon_format)));
    }
    
     document.getElementById('txtDibayar').focus();
}

function CekData()
{
    if(!document.getElementById('txtDibayar').value || document.getElementById('txtDibayar').value =='0')
    {
      alert('Belum dibayar');
      document.getElementById('txtDibayar').focus();
      return false;
    }
    
    if(!document.getElementById('total_harga').value) {
         alert('Item Barang Belum Dimasukkan');
         return false;
    }
    
    /*if(document.getElementById('txtBack').value < '0')
    {
      alert('Maaf uang anda kurang');
      document.getElementById('txtBack').focus();
      return false;
    }*/
    
    if(!document.getElementById('penjualan_no').value) {
         alert('Nomor Faktur harap diisi');
         return false;
    }
    
      if(!document.getElementById('pasien_nama').value) {
         alert('Nama Pasien harap diisi');
         document.getElementById('pasien_nama').focus();
         return false;
    }
    
     if(document.getElementById('txtJumlah').value==0) {
          alert('Jumlah tidak boleh kosong (0)');
          document.getElementById('txtJumlah').focus();
          return false;
     }
     
     if(document.getElementById('txtHargaTotal').value==0) {
          alert('Harga Total tidak boleh kosong (0)');
          return false;
     }
    return true;
}
/*function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     if(keyCode==113) {  // -- f2 buat fokus ke tipe transaksi ---
        if(!document.getElementById('cust_usr_nama').value) {
         alert('Pasien harap dipilih');
         document.getElementById('pasien_nama').focus();
        }
       /* else if(document.getElementById('cust_usr_jenis').value=='--') {
         alert('Jenis Pasien harap dipilih');
         document.getElementById('pasien_nama').focus();
        }  */
     /*   else
        {
    
         if (confirm('Apakah data Penjualan sudah benar? (Karena tidak bisa dirubah)')==1)
         {
              document.getElementById('custTambah').value = 'tambah';
              document.getElementById('tombol_f2').value = '1'; //tombol F2 TRUE
              document.frmFind.submit();
  
             
          }
        }
          
     }
     if(keyCode==115) {  // -- f4 buat Masukkan Obat kalau salah ---
          document.getElementById('obat_kode').focus();
     }
     if(keyCode==119) {  // -- f8 buat Bayar ---
          document.getElementById('txtDiskon').focus();
     }
     return false;
}    */       //115 F4

function CekDataTambah()
{
    if(!document.getElementById('cust_usr_nama').value) {
         alert('Pasien harap dipilih');
         document.getElementById('cust_usr_nama').focus();
         return false;
        }         
         return true;        
}
var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

     <?php if($cetak=="y"){ ?>
        BukaWindow('penjualan_bebas_cetak.php?id=<?php echo $penjualanId;?>','Nota');
	      document.location.href='<?php echo $sellPage;?>';
     <?php } ?>
     
</script>
<?php //echo $view->RenderBody("module.css",true,false,"PENJUALAN LUAR");?> 
        <?php if ($_x_mode=='New') { ?>
     <body>
<!--      <div id="header">
        <table border="0" width="100%" valign="top">
        <tr>
          <td width="50%" align="left" valign="top">
          <a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
          </td>
          <td width="50%" valign="top" align="right">
          <a href="#" target="_blank"><font size="6">PENJUALAN LUAR</font></a>&nbsp;&nbsp;
          </td>
        </tr>
        </table>
      </div>-->
      <div id="body">
      <div id="scroller">
     <form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">           
      <table width="100%" border="0" cellpadding="1" cellspacing="1">               
        <tr class="tableheader">                   
          <td colspan="4">&nbsp;</td>             
        </tr>             
        <tr>                                                                                              
              <table width="100%" border="0" cellpadding="1" cellspacing="1">                               
                <tr>                                       
                  <td align="left" width="30%" class="tablecontent">&nbsp;&nbsp;No. Penjualan&nbsp;</td>                                       
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <?php echo $view->RenderTextBox("penjualan_no","penjualan_no","30","100",$_POST["penjualan_no"],"inputField", "",false);?>                     </td>                                      
                </tr>                                                   
                <tr>                                       
                  <td align="left" width="30%" class="tablecontent">&nbsp;&nbsp;Nama Pembeli&nbsp;</td>                                 
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","30","100",$_POST["cust_usr_nama"],"inputField","",false);?>                                                                                                                          
                    <?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
                    <!--<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" class="tombol" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a> -->
                </tr>
                <tr>                                       
                  <td align="left" width="30%" class="tablecontent">&nbsp;&nbsp;Alamat Pembeli&nbsp;</td>                                 
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <?php echo $view->RenderTextBox("penjualan_alamat","penjualan_alamat","30","100",$_POST["penjualan_alamat"],"inputField","",false);?>                                                                                                                          
                    <?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
                    <!--<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" class="tombol" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a> -->
                </tr> 
                
                      
                
                <tr>                     
                    <td colspan="4">&nbsp;<?php echo $view->RenderHidden("tombol_f2","tombol_f2",$_POST["tombol_f2"]); ?></td>                  
                  </tr> 
                <?php if ($_x_mode!='Edit') { ?>   
                <tr>                     
                  <td colspan="2" class="tablecontent" align="center"><input type="submit" id="btnTambah" name="btnTambah" value="Tambahkan" class="submit" onClick="javascript:return CekDataTambah();"/>
                  &nbsp;
                  <input type="button" name="Back" id="Back" value="Kembali" class="submit" onClick="document.location.href='penjualan_view.php'";/>
                  </td>               
                  
                  </tr> 
                 <? } ?>                       
              </table>
            </td>              
        </tr>       
       </form> 
      </table>
      </div>
		  </div>

  		<!-- <table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table> -->
         
       <?php } //END IF NEW ?>
        
       <?php if ($_x_mode=='Edit') { //JIKA MEMASUKKAN OBAT?>
      <body>
      <!--<div id="header">
      <table border="0" width="100%" valign="top">
      <tr>
      <td width="50%" align="left" valign="top">
      <a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
      </td>
      <td width="50%" valign="top" align="right">
      <a href="#" target="_blank"><font size="6">PENJUALAN LUAR</font></a>&nbsp;&nbsp;
      </td>
      </tr>
      </table>
      </div>-->
      <div id="body">
      <div id="scroller">
      <tr>                 
          <td colspan="4">                                                                               
              <table width="100%" border="1" cellpadding="2" cellspacing="2">                               
              <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">  
              <tr>                   
                <td colspan="4">&nbsp;</td>             
                </tr> 
                <tr>                                       
                  <td align="left" width="10%" class="tablecontent">&nbsp;No. Penjualan&nbsp;</td>                                       
                  <td align="left" width="45%" class="tablecontent-odd">&nbsp;<?php echo $_POST["penjualan_no"];?></td>                                      
                  <td align="center" width="40%" class="tablecontent" rowspan="4" valign="top"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($Grand);?></span></font></td>
                </tr> 
                <tr>
              		<td width="10%" align="left" class="tablecontent">&nbsp;Nama Pembeli</td>
                  <td width="45%" class="tablecontent-odd" align="left">&nbsp;<?php echo $view->Renderlabel("cust_usr_nama","cust_usr_nama",$_POST["cust_usr_nama"],"inputField",false);?>
                </tr>
                <tr>
                  <td width="10%" align="left" class="tablecontent">&nbsp;Alamat Pembeli</td>
                  <td width="45%" class="tablecontent-odd" align="left">&nbsp;<?php echo $view->Renderlabel("penjualan_alamat","penjualan_alamat",$_POST["penjualan_alamat"],"inputField",false);?>
                </tr> 
                
                <tr>
                 
              		<td width="10%" align="left" class="tablecontent">&nbsp;Nama Dokter</td>
              		<td><select class="inputField" name="usr" >
                  <option class="inputField" value="0" >[ Pilih Dokter ]</option>
                                   <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>"<?php if($dataDokter[$i]["usr_id"]==$_POST["usr"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>
                                    <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                                   <?php } ?>
                              </select> 
                  </td>
                </tr> 
                                               
                <tr>                                                                        
                  <td width="45%" class="tablecontent-odd" align="left" colspan="2">&nbsp;</td>                                                                                
                </tr>
          <tr>
               <td class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
               <td width="75%" align="right">Dibayar : </td>
				       <td width="25%" align="left">
				       <?php echo $view->RenderHidden("Grandstotal","Grandstotal",currency_format($Grand),"curedit", "readonly",false);?>
               <?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtBalik"],"curedit", "",true,'onChange=GantiPengurangan(this.value)');?></td>              
               <!--<?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"]=currency_format($Grand),"curedit", "readonly",true,'onChange=GantiPengurangan(this.value)');?>--></td>
				       </tr>
				       </table>
      				 </td>
               </tr>
          <tr>
               <td width= "50%" align="center" class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
               </td>
               <td width="10%" align="left">&nbsp;</td>
               <td width="20%" align="left" valign="middle"><img src="<?php echo $ROOT; ?>gambar/pointer.gif"/> <font color="#FF0000">Klik untuk isi nama obat</font></td>
				       <td width="30%" align="center">
               <input type="submit" name="btnRefresh" id="btnRefresh" value="Refresh" class="submit"/>
               <input type="submit" name="btnBayar" id="btnBayar" value="Proses" class="submit" onClick="javascript:return CekData();"/>     
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='penjualan_view.php'";/>     
				       </td>
                </tr>
				       </table>
          </td>
          </tr> 
                <tr>                       
					<td colspan="4">&nbsp;</td>                


                  </tr>
                  
              </table>
                                      
            </td>              
        </tr> 
      <table width="100%" border="0" cellpadding="1" cellspacing="1"> 
           
        <tr>                                         
         <table width="100%" border="1" cellpadding="1" cellspacing="1">                                        
              <tr class="subheader">
                      <td align="left" width="2%" >&nbsp;Hapus<?php //echo "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">"; ?></td>
                      <!--<td align="left" width="2%" >&nbsp;Edit&nbsp;</td>-->
                      <td align="left" width="10%" >&nbsp;Kode &nbsp;</td>                                               
                      <td align="left" width="20%" >&nbsp;Nama Obat&nbsp;</td> 
<!--                      <td align="left" width="20%" >&nbsp;Paket&nbsp;</td>    -->
                      <td align="left" width="5%" >&nbsp;Jumlah</td>
                      <td align="left" width="10%" >&nbsp;Harga Jual</td>
                      <td align="left" width="10%" >&nbsp;Tuslag</td>
                      <td align="left" width="10%" >&nbsp;Total Harga</td>
                      <td align="left" width="10%" >&nbsp;No. Batch</td>
                      <td align="left" width="8%" >&nbsp;Expire Date</td>
                      <td align="left" width="15%" >&nbsp;Dosis</td> 
                </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) {  $grandtotal += $dataTable[$i]["penjualan_detail_total"]; ?>
						  
                      <tr class="tablecontent-odd">
                      <td width="2%" align="center"><?php echo '<a href="'.$thisPage.'?&del=1&id='.$dataTable[$i]["penjualan_detail_id"].'&transe='.$penjualanId.'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/b_drop.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>'; ?>
                      <?php //echo '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["penjualan_detail_id"].'">'; ?></td>
                      <!--<td align="left" width="2%" ><?php //echo '<a href="#" onClick="Editobat(\''.$dataTable[$i]["id_penjualan"].'\',\''.$dataTable[$i]["penjualan_detail_id"].'\',\''.$dataTable[$i]["item_id"].'\',\''.$dataTable[$i]["item_nama"].'\',\''.currency_format($dataTable[$i]["penjualan_detail_harga_jual"]).'\',\''.currency_format($dataTable[$i]["penjualan_detail_jumlah"]).'\',\''.currency_format($dataTable[$i]["penjualan_detail_total"]).'\',\''.$dataTable[$i]["id_petunjuk"].'\',\''.$dataTable[$i]["item_kode"].'\')"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>'; ?></td>-->
                      <td align="left" width="10%" ><?php echo $dataTable[$i]["item_kode"];?></td>
                      <td align="left" width="10%" ><?php echo $dataTable[$i]["item_nama"];?></td>                                               
<!--                      <td align="left" width="20%" ><?php echo $dataTable[$i]["paket_nama"];?></td>             -->
                      <td align="left" width="5%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_jumlah"]);?></td>
                      <td align="left" width="10%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_harga_jual"]);?></td>
                      <td align="left" width="10%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_tuslag"])?></td>
                      <td align="left" width="10%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_total"])?></td>
                      <td align="left" width="8%" ><?php echo $dataTable[$i]["batch_no"];?></td>
                      <td align="left" width="8%" ><?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
                      <td align="left" width="15%" ><?php echo $dataTable[$i]["petunjuk_nama"];?></td>
                      </tr>
						  
						  <?php } ?>
                      
                      <tr>
                      <td align="left" width="2%" class="tablecontent-odd">&nbsp;&nbsp;</td>
                      <!--<td align="left" width="2%" class="tablecontent-odd">&nbsp;&nbsp;</td>-->
                      <td align="left" width="10%" class="tablecontent-odd">                                                   
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
                      <?php echo $view->RenderTextBox("obat_kode","obat_kode","10","10",$_POST["obat_kode"],"inputField",false,false);?>                                        
                      <input type="hidden" name="obat_id" id="obat_id" value="<?php echo $_POST["obat_id"];?>" />
                      <input type="hidden" name="id_batch" id="id_batch" value="<?php echo $_POST["id_batch"];?>" />                                                   
                      <!--<img src="<?php //echo $ROOT;?>gambar/finder.png" border="0" align="middle" class="tombol" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" />--></a>
                      </td>        
                                                                                                                                                
                      <td align="left" width="20%" class="tablecontent-odd">
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
                      <?php echo $view->RenderTextBox("obat_nama","obat_nama","30","100",$_POST["obat_nama"],"inputField", "",false);?></a></td>                                                                                                                                                         
                      <td align="left" width="5%" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtJumlah","txtJumlah","5","10",$_POST["txtJumlah"],"curedit", "",true,'onChange=GantiHarga(this)');?></td>					                                                                                        
                      
                      <td align="left" width="10%" class="tablecontent-odd">
                      <input type="hidden" name="txtHargaSatuan" id="txtHargaSatuan" value="<?php echo $_POST["txtHargaSatuan"];?>">
                      <span id="txtSatuanNom"></span>
                      <?php //echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",currency_format($_POST["txtHargaSatuan"]),"curedit", "readonly",false,'onChange=GantiHarga(this)');?></td>					                                                                                        
                      <td align="left" width="5%" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtTuslag","txtTuslag","5","10",$_POST["txtTuslag"],"curedit", "",true,'onChange=GantiHarga(this)');?></td>
                      <td align="left" width="10%" class="tablecontent-odd">
                      <input type="hidden" name="txtHargaTotal" id="txtHargaTotal" value="<?php echo $_POST["txtHargaTotal"];?>">
                      <span id="txtIsiTotale"></span>
                      <?php //echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "readonly",false);?></td>					                                                                                        
                      
                      <td align="left" width="8%" class="tablecontent-odd"><?php echo $view->RenderTextBox("batch_no","batch_no","8","100",$_POST["batch_no"],"inputField", "readonly",false);?></td>
                      <td align="left" width="8%" class="tablecontent-odd"><?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo","batch_tgl_jatuh_tempo","8","100",$_POST["batch_tgl_jatuh_tempo"],"inputField", "readonly",false);?></td>
                      <td align="left" width="15%" class="tablecontent-odd"><?php echo $view->RenderComboBox("id_petunjuk","id_petunjuk",$opt_dosis,"inputField","",false,false);?></td>					                                                                                      
                      </tr> 
                      <tr>

                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2"><input type="submit" name="btnSave" id="btnSave" value="Tambah Obat" class="submit" onClick="javascript:return CekTindakan(document.frmEdit);"></td>
                      <td align="right" width="2%" colspan="4">TOTAL YANG HARUS DIBAYAR&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="5"><?php echo currency_format($grandtotal);?></td>
                      </tr>
                     
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="4">DISKON&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="5">
                      <?php echo $view->RenderHidden("total","total",currency_format($grandtotal),"curedit", "readonly",false);?>
                      <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","15",currency_format($_POST["txtDiskon"]),"curedit", "",true,'onChange=GantiGrandHarga(this.value)');?>
                      </td>
                      </tr>
                      
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="4">BIAYA RESEP&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="5">
                      <?php echo $view->RenderTextBox("txtResep","txtResep","15","15",currency_format($_POST["txtResep"]),"curedit", "",true,'onChange=GantiResepHarga(this.value)');?>
                      </td>
                      </tr>
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="4">BIAYA RACIKAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="3">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaRacikan","txtBiayaRacikan","15","15",currency_format($_POST["txtBiayaRacikan"]),"curedit", "",true,'onChange=GantiRacikanHarga(this.value)');?>                                        		                                                   
                      </tr>
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="4">BIAYA PEMBULATAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="3">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaPembulatan","txtBiayaPembulatan","15","15",currency_format($_POST["txtBiayaPembulatan"]),"curedit", "",true,'onChange=GantiPembulatanHarga(this.value)');?>                                        		                                                   
                      </tr>
                                      
                      <tr class="tablesmallheader">                             
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="4">GRAND TOTAL&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="5">
                      <?php echo $view->RenderHidden("txtBalik","txtBalik",currency_format($Grand),"curedit", "",false);?>
                      <?php echo $view->RenderLabel("txtGrandHargaTotal","txtGrandHargaTotal",currency_format($Grand),"curedit", "",false);?>
                      </td>
                      </tr>

                      </tr> 
                                                     
                  </table>                                                                    
         </tr>            
        <tr>                       
          <td colspan="4" class="tablecontent">&nbsp;</td>                  
        </tr>              
                      
        <tr>                   
          <td colspan="4">                       
            </td>              
        </tr>      
      </table> 
     <script>document.frmFind.cust_usr_kode.focus();</script>  
     <input type="hidden" name="transaksi_paket_id" id="transaksi_paket_id" value="<?php echo $transaksiId;?>" />
      <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandtotalese;?>" />
      <input type="hidden" name="GrandHargaTotals" id="GrandHargaTotals" value="<?php echo $_POST["GrandHargaTotals"];?>" />
      <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
      <input type="hidden" id="penjualan_id" name="penjualan_id" value="<?php echo $penjualanId;?>" />      
      <input type="hidden" id="btn_edit" name="btn_edit" value="<?php echo $btn_edit;?>" />    
      <input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />      
      <input type="hidden" name="transaksi_id" id=="transaksi_id"/>      
      <input type="hidden" name="awal" value="1" />      
      <input type="hidden" name="hidUrut" value="<? echo $_POST["hidUrut"]; ?>">    
      <!--<input type="hidden" name="cust_usr_jenis" value="<? echo $_POST["cust_usr_jenis"]; ?>">--> 
      <input type="hidden" name="cust_usr_kode" value="<? echo $_POST["cust_usr_kode"]; ?>"> 
      <input type="hidden" name="dokter_nama" value="<? echo $_POST["dokter_nama"]; ?>">
      <input type="hidden" name="id_reg" value="<? echo $_POST["id_reg"]; ?>">
      <input type="hidden" name="reg_tanggal" value="<? echo $_POST["reg_tanggal"]; ?>">
      <input type="hidden" name="id_poli" value="<? echo $_POST["id_poli"]; ?>">
      <input type="hidden" name="id_cust_usr" value="<? echo $_POST["cust_usr_id"]; ?>">  
    </form>  
  </div>
      </div>
		  </div>

  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->  
			
<?php } ?>  

<?php if ($_x_mode=='New') { //UNTUK FOCUS ISIAN OBAT?>
     <script>document.frmFind.cust_usr_kode.focus();</script> 
<? } else if ($_x_mode=='Edit') {?>      
      <script>document.frmEdit.obat_kode.focus();</script>
<? } ?>
 
  <?php //echo $view->RenderBodyEnd(); ?>
