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
//     $poli = $auth->GetPoli();

     $poli = "33"; //POLI APOTIK IRJ     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];
     //echo $theDep;
     
     if($_GET["id_dokter"]) $_POST["usr"] = $_GET["id_dokter"];
     if($_GET["id_poli"]) $_POST["poli"] = $_GET["id_poli"];
     if($_POST["id_pembayaran"]) $_POST["id_pembayaran"] = $_POST["id_pembayaran"];
     if($_GET["id_dokter"]){$_POST["id_dokter"] = $_GET["id_dokter"];}
     
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

    require_once('proses_registrasi_apotik_bebas.php');

        $_POST["id_reg"] = $regId;
        $_POST["id_pembayaran"] = $byrId;

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
           $dbField[13]  = "penjualan_detail_dosis_obat";
          $dbField[14]  = "id_aturan_minum";
          $dbField[15]  = "id_aturan_pakai";
          $dbField[16]  = "item_nama";

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
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["penjualan_detail_dosis_obat"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["obat_nama"]);
          
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
     if ($_POST["btnBayar"]) 
     {
       require_once('proses_pembayaran_apotik_bebas.php');
       //$isprint = "y";
       $_x_mode = "cetak" ;
     }
     // AKHIR PROSES BTN PEMBAYARAN

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
     $tableHeader = "&nbsp;Penjualan Bebas";

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
  
  //combo aturan minum
    $sql = "select * from apotik.apotik_aturan_minum";
      $rs = $dtaccess->Execute($sql);
      $r=0;
      $opt_minum[0] = $view->RenderOption("--","[ PILIH ATURAN MINUM ]",$show);
      while($data_atminum=$dtaccess->Fetch($rs))
      {
        unset($show);
        $opt_minum[] = $view->RenderOption($data_atminum["aturan_minum_id"],$data_atminum["aturan_minum_nama"],$show);
        $r++;
      }
//combo aturan pakai
    $sql = "select * from apotik.apotik_aturan_pakai";
      $rs = $dtaccess->Execute($sql);
      $r=0;
      $opt_pakai[0] = $view->RenderOption("--","[ PILIH DOSIS ]",$show);
      while($data_atpakai=$dtaccess->Fetch($rs))
      {
        unset($show);
        $opt_pakai[] = $view->RenderOption($data_atpakai["aturan_pakai_id"],$data_atpakai["aturan_pakai_nama"],$show);
        $r++;
      }

?>

<!DOCTYPE html>
<html lang="en">
<script language="Javascript">

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

<?php if($_x_mode=="cetak"){ ?>
         BukaWindow('penjualan_bebas_cetak.php?id=<?php echo $penjualanId;?>','Nota');
	       document.location.href='penjualan_bebas_view.php';
    <?php } ?>
     

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

     diskonpersen = (diskon*100)/total;

     if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep+racikan+pembulatan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskonpersen);
     document.getElementById('Grandstotal').value = (total+resep+racikan+pembulatan)-diskon;
     } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('Grandstotal').value = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtIsi').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtDiskonPersen').value = formatCurrency(diskonpersen);
      }
} 

function GantiResepHarga(resep) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
   // var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;
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

function GantiRacikanHarga(resep) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
  //  var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;
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

function GantiGrandHargaPersen(diskonpersen) {
    var total = document.getElementById('total').value.toString().replace(/\,/g,"")*1;
    var diskonpersen = document.getElementById('txtDiskonPersen').value.toString().replace(/\,/g,"")*1;
    var resep = document.getElementById('txtResep').value.toString().replace(/\,/g,"")*1;
    var racikan = document.getElementById('txtBiayaRacikan').value.toString().replace(/\,/g,"")*1;
    var grand = document.getElementById('txtBack').value.toString().replace(/\,/g,"")*1;
   // var bayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"")*1;
    var pembulatan  = document.getElementById('txtBiayaPembulatan').value.toString().replace(/\,/g,"")*1;
     persendiskint = diskonpersen*1;
     diskon = (persendiskint*total)/100;
     
     if(grand == "0" || grand != "0"){
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('GrandHargaTotals').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var Harga = document.getElementById('txtBalik').value = (total+resep+racikan+pembulatan)-diskon;
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     var bayar = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtIsi').innerHTML = formatCurrency(bayar-Harga);
     document.getElementById('txtBack').value = formatCurrency(bayar-Harga);
     document.getElementById('txtDiskon').value = formatCurrency(diskon);
     document.getElementById('Grandstotal').value = (total+resep+racikan+pembulatan)-diskon;
     } else {
     document.getElementById('txtGrandHargaTotal').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('Grandstotal').value = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtIsi').innerHTML = formatCurrency((total-diskon)+resep+racikan+pembulatan);
     document.getElementById('txtDibayar').value = formatCurrency((total+resep+racikan+pembulatan)-diskon);
     document.getElementById('txtDiskon').value = formatCurrency(diskon);
      }
}
function CekData()
{
    /*if(document.getElementById('txtDibayar').value < document.getElementById('Grandstotal').value)
    {
      alert('Maaf Uang Anda Kurang !!');
      document.getElementById('txtDibayar').focus();
      return false;
    }*/
    
    if(!document.getElementById('txtDibayar').value || document.getElementById('txtDibayar').value =='0')
    {
      alert('Belum dibayar');
      document.getElementById('txtDibayar').focus();
      return false;
    }
    
    /*if(document.getElementById('txtBack').value < '0')
    {
      alert('Maaf uang anda kurang');
      document.getElementById('txtBack').focus();
      return false;
    } */
    
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
         document.getElementById('pasien_nama').focus();
        }
       /* else if(document.getElementById('cust_usr_jenis').value=='--') {
         alert('Jenis Pasien harap dipilih');
         document.getElementById('pasien_nama').focus();
        }  */
        else
        {
         if (confirm('Apakah data Penjualan sudah benar? (Karena tidak bisa dirubah)')==1)
         {
              document.getElementById('custTambah').value = 'tambah';
              document.getElementById('tombol_f2').value = '1'; //tombol F2 TRUE
              document.frmFind.submit();             
          } 
         return false;        
}
}
  
</script> 
  <?php require_once($LAY."header.php"); ?>
  <script type="text/javascript">
   $(document).ready(function(){
    //auto complete
    $('#obat_nama').autocomplete({
      serviceUrl: 'get_obat.php?jenis_id=<?php echo $dataPasien['reg_jenis_pasien']; ?>',
      paramName: 'item_nama',
      transformResult: function(response) {
      var data = jQuery.parseJSON(response);
      return {
        suggestions: $.map(data, function(item) {
          return {
            value: item.item_nama,
            data: { 
              item_kode: item.item_kode,
              item_nama: item.item_nama,
              item_id: item.item_id,
              item_harga_beli: item.item_harga_beli,
              item_harga_jual: item.item_harga_jual,
              batch_id: item.batch_id,
              batch_no: item.batch_no,
              batch_tgl_jatuh_tempo: item.batch_tgl_jatuh_tempo,
            } 
          };
        })
      };
      },
      onSelect: function (suggestion) {
      $('#obat_kode').val(suggestion.data.item_kode);
      $('#obat_nama').val(suggestion.data.item_nama);
      $('#obat_id').val(suggestion.data.item_id);
      $('#txtHargaSatuan').val(suggestion.data.item_harga_jual);
      $('#txtHargaTotal').val(suggestion.data.item_harga_jual);
      $('#txtJumlah').val('1');
      $('#id_batch').val(suggestion.data.batch_id);
      $('#batch_no').val(suggestion.data.batch_no);
      $('#batch_tgl_jatuh_tempo').val(suggestion.data.batch_tgl_jatuh_tempo);
      $('#txtSatuanNom').text(suggestion.data.item_harga_jual);
      $('#txtIsiTotale').text(suggestion.data.item_harga_jual);
      }
    });
    //auto complete
    $('#obat_kode').autocomplete({
      serviceUrl: 'get_obat.php',
      paramName: 'item_kode',
      transformResult: function(response) {
      var data = jQuery.parseJSON(response);
      return {
        suggestions: $.map(data, function(item) {
          return { value: item.item_kode };
        })
      };
      },
     onSelect: function (suggestion) {
      $('#obat_kode').val(suggestion.data.item_kode);
      $('#obat_nama').val(suggestion.data.item_nama);
      $('#obat_id').val(suggestion.data.item_id);
      $('#txtHargaSatuan').val(suggestion.data.item_harga_beli);
      $('#txtHargaTotal').val(suggestion.data.item_harga_beli);
      $('#txtJumlah').val('1');
      $('#id_batch').val(suggestion.data.batch_id);
      $('#batch_no').val(suggestion.data.batch_no);
      $('#batch_tgl_jatuh_tempo').val(suggestion.data.batch_tgl_jatuh_tempo);
      $('#txtSatuanNom').text(suggestion.data.item_harga_beli);
      $('#txtIsiTotale').text(suggestion.data.item_harga_beli);
      }
    });
  });
  </script>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">        
		<?php require_once($LAY."sidebar.php"); ?>
        <!-- top navigation -->
		<?php require_once($LAY."topnav.php"); ?>
		<!-- /top navigation -->
        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Apotik</h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Penjualan IRJ Penjualan Bebas</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <?php if ($_x_mode=='New') { ?>
					<form name="frmFind" id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
					
                      <div class="form-group">
                         <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No Penjualan <span class="required">*</span>
                         </label>
                         <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("penjualan_no","penjualan_no","30","100",$_POST["penjualan_no"],"inputField", "",false);?>                     </td>                                      
						 </div>
                      </div>				
					  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Pembeli <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						     <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","30","100",$_POST["cust_usr_nama"],"inputField","",false);?>                                                                                                                          
							<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
						<!--<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" class="tombol" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a> -->
						</div>
                      </div>
                                     
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat Pembeli</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
						<?php echo $view->RenderTextBox("penjualan_alamat","penjualan_alamat","30","100",$_POST["penjualan_alamat"],"inputField","",false);?>                                                                                                                          
						<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
						<!--<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" class="tombol" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a> -->
						</div>                     
                      <div class="ln_solid"></div>
						
						<td colspan="4">&nbsp;<?php echo $view->RenderHidden("tombol_f2","tombol_f2",$_POST["tombol_f2"]); ?></td>                  
				
				<?php if ($_x_mode!='Edit') { ?>   
                <tr>                     
                  <td colspan="2" class="tablecontent" align="center"><input type="submit" id="btnTambah" name="btnTambah" value="Tambahkan" class="submit" onClick="javascript:return CekDataTambah();"/>
                  &nbsp;
                  <!-- <input type="button" name="Back" id="Back" value="Kembali" class="submit" onClick="document.location.href='penjualan_view.php'";/>
                   --></td> 
                  </tr> 
                 <? } ?> 
						
						  <?php } //END IF NEW ?>
                    </form>
					
					<form name="frmFind" id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <?php if ($_x_mode=='Edit') { //JIKA MEMASUKKAN OBAT?>
					
					<div class="control-label col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
                         <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No Penjualan <span class="required">*</span>
                         </label>
                         <div class="col-md-6 col-sm-6 col-xs-12">
							 <input type="text" id="kamar_kode" name="kamar_kode" value="<?php echo $_POST["penjualan_no"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     
							 </div>
                      </div>
						
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Pembeli
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<input type="text" id="kamar_kode" name="kamar_kode" value="<?php echo $_POST["cust_usr_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     	 
						</div>
                         </div>
						 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
						<input type="text" id="kamar_kode" name="kamar_kode" value="<?php echo $_POST["penjualan_alamat"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     	 
						</div>
                      </div> 
						 
						 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Dokter <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<select class="form-control" name="usr" >
									<option class="inputField" value="0" >[ Pilih Dokter ]</option>
                                   <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>"<?php if($dataDokter[$i]["usr_id"]==$_POST["usr"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>
                                    <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                                   <?php } ?>
                              </select>
							  </div>
                         </div>
					</div>
					
						 
					<div class="control-label col-md-6 col-sm-6 col-xs-12"> 
					<div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<td align="center" width="40%" class="tablecontent" rowspan="4" valign="top"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($Grand-$Grand);?></span></font></td>							
							</div>
                    </div>					
					<div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12">
						 <label class="control-label col-md-6 col-sm-6 col-xs-12" for="last-name">Di Bayar
                        </label>
							<?php echo $view->RenderHidden("Grandstotal","Grandstotal",currency_format($Grand),"curedit", "readonly",false);?>
							<?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"] = currency_format($Grand),"curedit", "",true,'onChange=GantiPengurangan(this.value)');?></td>		       
							</div>
						</div>
						</div>
					
					
					<div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
						 <td width="20%" align="left" valign="middle"><img src="<?php echo $ROOT; ?>gambar/pointer.gif"/> <font color="#FF0000">Klik untuk isi nama obat</font></td>
				       <td width="30%" align="center">
               <input type="submit" name="btnRefresh" id="btnRefresh" value="Refresh" class="submit"/>
               <input type="submit" name="btnBayar" id="btnBayar" value="Proses" class="submit" onClick="javascript:return CekData();"/>  
               <input type="button" name="Racikan" id="Racikan" value="Racikan" class="submit" onClick="document.location.href='input_racikan_bebas.php?kode=<? echo $enc->Encode($_POST["cust_usr_kode"])?>&transaksi=<?php echo $enc->Encode($penjualanId);?>&id_reg=<? echo $enc->Encode($_POST['id_reg']);?>&id_pembayaran=<? echo $_POST["id_pembayaran"];?>&jenis_id=<? echo $_POST["reg_jenis_pasien"];?>'"/>   
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='penjualan_bebas_view.php'";/>     
				       </td>
							</div>
                         </div>
						 
						 <table width="100%" border="0" cellpadding="1" cellspacing="1"> 
           
        <tr>                                         
        <table id="datatable-responsive" class="table table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <tr class="subheader">
                      <td align="left" width="2%" >&nbsp;Hapus<?php //echo "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">"; ?></td>
                      <!--<td align="left" width="2%" >&nbsp;Edit&nbsp;</td>-->
                      <!-- <td align="left" width="2%" >&nbsp;Paket&nbsp;</td> -->
                      <!--td align="left" width="10%" >&nbsp;Kode &nbsp;</td--> 
                      <td align="left" width="25%">&nbsp;Nama Obat&nbsp;</td> 
                      <td align="left" width="3%">Jml.</td>
                      <td align="left" width="7%">Hg. Jual</td>
                      <td align="left" width="6%">Tuslag</td>
                      <td align="left" width="7%">Tot. Harga</td>
                      <td align="left" width="7%">No. Batch</td>
                      <td align="left" width="7%">Exp. Date</td>
                      <td align="left" width="4%">Dosis</td>
                      <td align="left" width="7%">Aturan Minum</td>
                      <td align="left" width="7%">Aturan Pakai</td> 
                      </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) {  $grandtotal += $dataTable[$i]["penjualan_detail_total"]; 
                //data rincian racikan
                      $sql = "select item_nama from apotik.apotik_detail_racikan where id_nama_racikan =".QuoteValue(DPE_CHAR,$dataTable[$i]["id_item"]);
                      $rs = $dtaccess->Execute($sql);
                      $detailracikan = $dtaccess->FetchAll($rs); 
                     // echo $sql;
                      $editRacikan = "racikan_new.php?q=".$batchId."&item=".$dataTable[$i]["id_item"]."&kode=".$enc->Encode($_POST["cust_usr_kode"])."&transaksi=".$enc->Encode($penjualanId)."&id_reg=".$enc->Encode($_POST["id_reg"])."&id_pembayaran=".$_POST["id_pembayaran"]."&jenis_id=".$_POST['reg_jenis_pasien'];
              ?>
						  
                      <tr class="tablecontent-odd">
                      <td align="center"><?php echo '<a href="'.$thisPage.'?del=1&id='.$dataTable[$i]["penjualan_detail_id"].'&transe='.$penjualanId.'&kodenya='.$_POST["cust_usr_kode"].'&id_regnya='.$_POST["id_reg"].'&id_pembayaran='.$_POST["id_pembayaran"].'&id_dokter='.$_POST["id_usr"].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>'; ?></td>
                      <!-- <td></td> -->
                      <!--td align="left" width="10%" ><?php echo $dataTable[$i]["item_kode"];?></td-->
                      <td align="left" width="20%" ><b><?php echo '<a href="'.$editRacikan.'">'.$dataTable[$i]["item_nama"].'&nbsp;'.$tambahan.'</a>'; ?></b>
                      <?  for($k=0,$l=count($detailracikan);$k<$l;$k++) {
                          $urut = $k+1;
                        echo "<br><font size='1'>".$urut.". ".$detailracikan[$k]["item_nama"]."</font>";
                       }?> </td> 
                      <td align="left" ><?php echo currency_format($dataTable[$i]["penjualan_detail_jumlah"],2);?></td>
                      <td align="left" ><?php echo currency_format($dataTable[$i]["penjualan_detail_harga_jual"]);?></td>
                      <td align="left" ><?php echo currency_format($dataTable[$i]["penjualan_detail_tuslag"])?></td>
                      <td align="left" ><?php echo currency_format($dataTable[$i]["penjualan_detail_total"])?></td>
                      <td align="left" ><?php echo $dataTable[$i]["batch_no"];?></td>
                      <td align="left"  ><?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
                      <td align="left"  ><?php echo $dataTable[$i]["petunjuk_nama"];?></td>
                      <td align="left" ><?php echo $dataTable[$i]["aturan_minum_nama"];?></td>
                      <td align="left"  ><?php echo $dataTable[$i]["aturan_pakai_nama"];?></td>
                      </tr>
              
              <?php } ?>                      
                      <tr>
                      <td align="left" width="0%" class="tablecontent-odd">&nbsp;&nbsp;</td>
                      <!-- <td align="left" width="2%" class="tablecontent-odd">  
                      <a href="<?php echo $findPaket;?>?transaksi=<?php echo $penjualanId; ?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Pilih Paket">
                      <img hspace="2" width="20" height="20" src="<?php echo $ROOT;?>gambar/icon/folder.png" alt="Paket" title="Paket" border="0"></a>
                      </td>   -->                                      
                      <input type="hidden" name="obat_id" id="obat_id" value="<?php echo $_POST["obat_id"];?>" />   
                      <input type="hidden" name="id_batch" id="id_batch" value="<?php echo $_POST["id_batch"];?>" />   
            <!--td align="left" class="tablecontent-odd">
            <input type="text" id="obat_kode" class="form-control">
            </td-->
            <td align="left" class="tablecontent-odd">
            <input type="text" id="obat_nama" name="obat_nama" class="form-control">
            </td>
            <td align="left" class="tablecontent-odd">
            <input type="text" id="txtJumlah" name="txtJumlah" class="form-control" onChange="GantiHarga(this)">
            </td>
                      <td align="left" class="tablecontent-odd">
            <input type="hidden" name="txtHargaSatuan" id="txtHargaSatuan" value="<?php echo $_POST["txtHargaSatuan"];?>">
            <span id="txtSatuanNom"></span>
            </td>
            <td align="left" class="tablecontent-odd">
            <input type="text" id="txtTuslag" name="txtTuslag" class="form-control" onChange="GantiHarga(this)">
            </td>
            
                      <td align="left" class="tablecontent-odd">
                      <input type="hidden" name="txtHargaTotal" id="txtHargaTotal" value="<?php echo $_POST["txtHargaTotal"];?>">
                      <span id="txtIsiTotale"></span>
            </td>
            
            <td align="left" class="tablecontent-odd">
            <input type="text" id="batch_no" name="batch_no" class="form-control">
            </td>
            
            <td align="left"  class="tablecontent-odd">
            <input type="text" id="batch_tgl_jatuh_tempo" name="batch_tgl_jatuh_tempo" class="form-control">
            </td>

                      <td align="left" class="tablecontent-odd">
            <?php echo $view->RenderComboBox("id_petunjuk","id_petunjuk",$opt_dosis,"inputfield",null); ?>
            </td>
                      <td align="left" class="tablecontent-odd">
            <?php echo $view->RenderComboBox("id_aturan_minum","id_aturan_minum",$opt_minum,"inputfield",null); ?>
            </td>
                      <td align="left" class="tablecontent-odd">
            <?php echo $view->RenderComboBox("id_aturan_pakai","id_aturan_pakai",$opt_pakai,"inputfield",null); ?>
            </td>                                                                                     
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
                      <td align="left" width="2%" colspan="2">
                      <?php echo $view->RenderHidden("total","total",currency_format($grandtotal),"curedit", "readonly",false);?>
                      <?php echo $view->RenderTextBox("txtDiskonPersen","txtDiskonPersen","5","5",currency_format($_POST["txtDiskonPersen"]),"curedit", "",true,'onChange=GantiGrandHargaPersen(this.value)');?> %
                      </td>
                      <td align="left" width="2%" colspan="3">
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
					<?php } ?>  
					
<?php if ($_x_mode=='New') { //UNTUK FOCUS ISIAN OBAT?>
     <script>document.frmFind.cust_usr_kode.focus();</script> 
<? } else if ($_x_mode=='Edit') {?>      
      <script>document.frmEdit.obat_kode.focus();</script>
<? } ?>
					</form>
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
