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
     //DIPATEN SEMENTARA
     $poli = "33"; //POLI APOTIK IRJ
     
	   $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
     $_POST["txtResep"] = $gudang["conf_biaya_resep"];  //Konfigurasi Resep Pasien
	   
  // echo $theDep;  
     //AUTHENTIFIKASI
   if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }
    
     //echo "transaki".$_POST["penjualan_id"];
     //VARIABLE AWAL
     $thisPage = "penjualan.php";
     $findPage = "pasien_find.php?";
     $findDokterPage = "dokter_find.php?";
     $findPage1 = "obat_find.php?jenis_id=".$_POST["cust_usr_jenis"];
     $sellPage = "penjualan_view.php"; 
     $judulForm = "Penjualan Apotik";
     $findPaket = "paket_find1.php";
     
     if($_GET["id_dokter"]) $_POST["usr"] = $_GET["id_dokter"];
     if($_GET["id_poli"]) $_POST["poli"] = $_GET["id_poli"];
     if($_POST["id_pembayaran"]) $_POST["id_pembayaran"] = $_POST["id_pembayaran"];
     if($_GET["id_dokter"]){$_POST["id_usr"] = $_GET["id_dokter"];}
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
      if($_GET["transaksi"] && $_GET["kode"])  
      {
        $penjualanId = $enc->Decode($_GET["transaksi"]);
        $penjualan_edit=1;
        $_x_mode = "Edit";
        if(!$_POST["cust_usr_kode"]) $_POST["cust_usr_kode"] = $enc->Decode($_GET["kode"]);
        if(!$_POST["id_reg"]) $_POST["id_reg"] = $enc->Decode($_GET["idreg"]);
        if(!$_POST["id_pembayaran"]) $_POST["id_pembayaran"] = $_GET["id_pembayaran"];
      } 
      else if($_GET["transaksi"])  
      {
        $penjualanId = $_GET["transaksi"];
        $penjualan_edit=1;
        $_x_mode = "Edit";
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
        
            $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId)." and penjualan_flag = 'D'";
            $lastKode = $dtaccess->Fetch($sql);
            $tgl = explode("-",$skr);
            $_POST["penjualan_no"] = "APRJ".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
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
	if($_x_mode == "Edit"  || $_POST["tombol_f2"]) 
  {  //Jika ditekan Tombol Lanjut atau keyboard F2s
		if($_x_mode=="Edit")
    {
      $_POST["id_reg"] = $_POST["id_reg"];
    } 
    else 
    {
    //SATU ID PEMBAYARAN SATU ID REGISTRASI SEMENTARA DITUTUP DULU
    /*$sql = "select a.* from klinik.klinik_registrasi a left join global.global_auth_poli b on b.poli_id=a.id_poli where b.poli_tipe='A' 
            and a.id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and a.id_poli=".QuoteValue(DPE_CHAR,$poli)." order by reg_waktu desc";
    //echo $sql;
    //die();
    $rs = $dtaccess->Execute($sql);
    $dataReg = $dtaccess->Fetch($rs); */

    //if(!$dataReg)
    //{
       require_once('proses_registrasi_apotik.php');
        $_POST["id_reg"] = $regId;
    //  } 
    //  else 
    //  {
        //echo "poli_apotik sdh ada."; die();
     //   $_POST["id_reg"] = $dataReg["reg_id"];
     // }
    }
    
    $sql = "select a.*,f.*, c.reg_jenis_pasien , c.reg_status , c.reg_tanggal, c.reg_id, c.id_poli, d.rawat_terapi, c.id_dokter,  
            c.id_pembayaran from global.global_customer_user a
				    left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
            left join klinik.klinik_perawatan d on d.id_reg = c.reg_utama
            left join global.global_auth_user f on f.usr_id = c.id_dokter 
				    where c.id_dep = ".QuoteValue(DPE_CHAR,$depId)."
            and reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." order by c.reg_tanggal desc,c.reg_waktu desc"; 
		$dataPasien = $dtaccess->Fetch($sql);
    //echo $sql; die();
    
		$_POST["cust_nama"] = htmlspecialchars($dataPasien["cust_nama"]); 
		$_POST["cust_usr_id"] = $dataPasien["cust_usr_id"];
    $_POST["id_poli"] = $dataPasien["id_poli"]; 
		$_POST["cust_usr_nama"] = htmlspecialchars($dataPasien["cust_usr_nama"]); 
		$_POST["cust_usr_kode"] = htmlspecialchars($dataPasien["cust_usr_kode"]); 
		$_POST["cust_usr_alamat"] = htmlspecialchars($dataPasien["cust_usr_alamat"]); 
		$_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"]; 
    $_POST["id_reg"] = $dataPasien["reg_id"];
    $_POST["reg_tanggal"] = $dataPasien["reg_tanggal"];
    $_POST["rawat_terapi"] = $dataPasien["rawat_terapi"];
    $_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];
    $_POST["id_dokter"] = $dataPasien["id_dokter"];
   // $_POST["usr_name"] = $dataPasien["usr_name"];
 //   $_POST["usr"] = $dataPasien["id_dokter"]; 
    $_POST["poli"] = $dataPasien["id_poli"];
    $_POST["id_pembayaran"] = $dataPasien["id_pembayaran"];
    //echo $_POST["id_pembayaran"];
   
   //$_POST["txtDibayar"] = currency_format($Grand);
    
    $sql = "select * from global.global_auth_user where usr_id = '".$_POST["id_usr"]."' "   ;
    $namaDokter = $dtaccess->Fetch($sql);
    
    $_POST["usr_name"] = $namaDokter["usr_name"];
    $lokasi = $ROOT."gambar/foto_pasien";
    
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
 
  if ($_x_mode == "Edit" && !$penjualanId)  //Jika menyimpan penjualan
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
   
      $penjualanId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["hidUrut"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
      $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]); 
      $dbValue[6] = QuoteValue(DPE_CHAR,'D');
      $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[8] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[9] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_usr"]);
      $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue); 
      
     // $next = "penjualan.php?transaksi=".$penjualanId;
      //header("location:".$next);
      //exit(); 
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

          if (!$_POST["btn_edit"])         //jika tombol edit di klik
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
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["penjualan_detail_dosis_obat"]);
          
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
          unset($_POST["txtDibayar"]);
          unset($_POST["txtBalik"]);
          unset($_POST["txtBack"]);
          unset($_POST["txtDiskon"]);
          unset($_POST["txtTuslag"]);
          unset($_POST["penjualan_detail_dosis_obat"]);                   /* 
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






     
     //Jika Melakukan Pembayaran
     if ($_POST["btnBayar"]) 
     {
       require_once('proses_pembayaran_apotik.php');
       //$isprint = "y";
       $_x_mode = "cetak" ;
  	   //$next = "penjualan_cetak.php?id=".$penjualanId;
      //header("location:".$thisPage);
      //exit();
     }
     // AKHIR PROSES BTN PEMBAYARAN


   //JIKA TOMBOL OK DITEKAN  
   if ($_POST["btnOk"]) 
   {  
      $sql = "update  klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId)."
              and fol_jenis='OA'";
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
      
      $sql = "update  apotik.apotik_penjualan set penjualan_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
              where penjualan_id=".QuoteValue(DPE_CHAR,$_POST["penjualan_id"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $dtaccess->Execute($sql); 
      
      $kembali = "penjualan.php?idreg=".$enc->Encode($_POST["id_reg"])."&transaksi=".$enc->Encode($_POST["penjualan_id"])."&kode=".$enc->Encode($_POST["cust_usr_kode"])."&id_dokter=".$_POST["usr"]."&id_poli=".$_POST["poli"]."&id_pembayaran=".$_POST["id_pembayaran"];
      header("location:".$kembali);
      exit();    
    }
    //AKHIR TOMBOL OKE
     
     //JIKA TOMBOL DEL OBAT DILAKUKAN
     if($_GET["del"]) 
     {
           $penjualanDetailId = $_GET["id"];
      
           $sql = "DELETE FROM apotik.apotik_penjualan_detail WHERE penjualan_detail_id = '".$penjualanDetailId."'";
           $dtaccess->Execute($sql);
                              
           $sql = "select sum(penjualan_detail_total) as total from apotik.apotik_penjualan_detail where id_penjualan = '".$_GET["transe"]."'";
           $rs = $dtaccess->Execute($sql);
           $totaljual = $dtaccess->Fetch($rs);
           
           $sql = "select penjualan_nomor from apotik.apotik_penjualan where penjualan_id = '".$_GET["transe"]."'";
           $rs = $dtaccess->Execute($sql);
           $nojual = $dtaccess->Fetch($rs);     
          //echo $sql; 
           //update folio dan penjualannya
           if($totaljual["total"]<>null)
           {
               $sql = "update apotik.apotik_penjualan set
                      penjualan_total = '".$totaljual["total"]."',
                      penjualan_grandtotal= '".$totaljual["total"]."',
                      penjualan_bayar ='".$totaljual["total"]."' where
                      penjualan_id ='".$_GET["transe"]."'";
               $rs = $dtaccess->Execute($sql);
          
               $sql = "update klinik.klinik_folio set
                      fol_nominal = '".$totaljual["total"]."',
                      fol_hrs_bayar='".$totaljual["total"]."',
                      fol_dibayar ='".$totaljual["total"]."' where
                      fol_catatan ='".$nojual["penjualan_nomor"]."'";
               $rs = $dtaccess->Execute($sql);     
      
              $transaksie = $enc->Encode($_GET["transe"]);
              $kodenya = $enc->Encode($_GET["kodenya"]);
              $idreg = $enc->Encode($_GET["id_regnya"]);           
              $kembali = "penjualan.php?kode=".$kodenya."&transaksi=".$transaksie."&idreg=".$idreg."&id_pembayaran=".$_GET["id_pembayaran"]."&id_dokter=".$_GET["id_dokter"]; 
              header("location:".$kembali);
              exit();
          }
          else
          {
           if($nojual["penjualan_nomor"]<>'' || $nojual["penjualan_nomor"]<>null)
           {
             $sql = "delete from klinik.klinik_folio where fol_catatan = '".$nojual["penjualan_nomor"]."'";
             $rs = $dtaccess->Execute($sql);
           }
          
           $sql = "delete from apotik.apotik_penjualan where penjualan_id = '".$_GET["transe"]."'";
           $rs = $dtaccess->Execute($sql);
            
            $kembali = "penjualan_view.php"; 
            header("location:".$kembali);
            exit();     
          }                    
     } //AKHIR HAPUS OBAT


     // VIEW PENJUALAN OBAT
     $tableHeader = "Penjualan Obat Pasien Umum";
     
     $isAllowedDel = $auth->IsAllowed("setup_role",PRIV_DELETE);                    
     $isAllowedUpdate = $auth->IsAllowed("setup_role",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_role",PRIV_CREATE);
     
     // --- Buat Tabel Penjualan Detail ---- //
     $counterHeader = 0;

     $sql = "select penjualan_keterangan from apotik.apotik_penjualan
             where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);       
     $rs = $dtaccess->Execute($sql);
     $tanggungan = $dtaccess->Fetch($rs);  
     
     $_POST["fol_keterangan"] = $tanggungan["penjualan_keterangan"];  
        
     $sql = "select a.*,b.item_nama,b.item_kode,c.jenis_nama,d.petunjuk_nama, f.batch_no, f.batch_tgl_jatuh_tempo
             from apotik.apotik_penjualan_detail a
             left join logistik.logistik_item b on a.id_item=b.item_id 
             left join global.global_jenis_pasien c on b.item_tipe_jenis=c.jenis_id
             left join apotik.apotik_obat_petunjuk d on a.id_petunjuk=d.petunjuk_id
             left join apotik.apotik_jenis_racikan e on a.id_jenis_racikan = e.jenis_racikan_id
             left join logistik.logistik_item_batch f on f.batch_id = a.id_batch
             where a.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId)."
             order by id_jenis_racikan desc, penjualan_detail_nama_racikan asc";       
     $rs_edit = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs_edit);

     //UNTUK MENGHITUNG JUMLAH TAGIHAN OBAT
     for($i=0,$n=count($dataTable);$i<$n;$i++) 
     {       
       $grandtotalese += $dataTable[$i]["penjualan_detail_total"];
       $Grand = ($grandtotalese+$gudang["conf_biaya_resep"]);
     }
     
      /* SEMENTARA DITUTUP NGGA DIPAKAI KARENA DOSIS / PETUNJUK menggunakan inputan text
      $sql = "select * from apotik.apotik_obat_petunjuk";
      $rs = $dtaccess->Execute($sql);
      $r=0;
      $opt_dosis[0] = $view->RenderOption("--","[ PILIH DOSIS ]",$show);
      while($data_dosis=$dtaccess->Fetch($rs))
      {
        unset($show);
        $opt_dosis[] = $view->RenderOption($data_dosis["petunjuk_id"],$data_dosis["petunjuk_nama"],$show);
        $r++;
      } */ 

    $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') order by usr_name ";
    $rs = $dtaccess->Execute($sql);
    $dataDokter = $dtaccess->FetchAll($rs);
 
    $sql = "select * from global.global_auth_poli where (poli_tipe='J' or poli_tipe='M' or poli_tipe='A') order by poli_tree asc ";
    $rs = $dtaccess->Execute($sql);
    $dataPoli = $dtaccess->FetchAll($rs);
  
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
         BukaWindow('penjualan_cetak.php?id=<?php echo $penjualanId;?>','Nota');
	       document.location.href='<?php echo $sellPage;?>';
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
     
     document.getElementById('txtIsiTotale').innerHTML = formatCurrency((duit*jumlah)+tuslag); 
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



function CekData()
{
    if(!document.getElementById('txtDibayar').value || document.getElementById('txtDibayar').value =='0')
    {
      alert('Belum dibayar');
      document.getElementById('txtDibayar').focus();
      return false;
    }
    
  //  if(document.getElementById('txtBack').value < '0')
    //{
      //alert('Maaf uang anda kurang');
      //document.getElementById('txtBack').focus();
      //return false;
    //}
    
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
    if(!document.getElementById('cust_usr_nama').value) 
    {
         alert('Pasien harap dipilih');
         document.getElementById('pasien_nama').focus();
     }
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
                    <h2>Penjualan Obat Pasien Umum</h2>
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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">No RM <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>" readonly="readonly" /></a>
						<a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
						<input type="hidden" name="custTambah" id="custTambah"/></div>
                      </div>
                                     
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Pasien</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                        <a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","30","100",$_POST["cust_usr_nama"],"inputField", "readonly",false);?></a>                                           
						<!--<a href="<?php //echo $pasienFind;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php //echo $outlet; ?>" class="thickbox" title="Pilih pasien"><img src="<?php //echo $ROOT;?>gambar/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" /></a>-->                                           
						<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
						<?php echo $view->RenderHidden("id_reg_lama","id_reg_lama",$_POST["id_reg_lama"]); ?>
						<?php echo $view->RenderHidden("id_pembayaran","id_pembayaran",$_POST["id_pembayaran"]); ?>   
                        </div>
                      </div>                     
                      <div class="ln_solid"></div>
                     
                     <?php echo $view->RenderHidden("tombol_f2","tombol_f2",$_POST["tombol_f2"]); ?>                 
						<?php if ($_x_mode!='Edit') { ?>   
						<tr>                     
						<td colspan="4" class="tablecontent"><input type="submit" id="btnTambah" name="btnTambah" value="Tambahkan" class="btn btn-Primary" onClick="javascript:return CekDataTambah();"/></td>
						<!--<td colspan="4" class="tablecontent">Tekan tombol F2 untuk memasukkan Obat</td>   -->               
						</tr> 
						<? } ?> 
						
							<?php if ($_pasien_salah) { //JIKA MEMASUKKAN OBAT?>
							<tr class="tableheader">
							<td colspan="4"><font color="red" size="1"><strong>Kode Pasien Tidak Ditemukan</strong></font></td>
							</tr>
							<?php } ?>
							 <?php } //END IF NEW ?> 
                    </form>
					
					<form name="frmFind" id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <?php if ($_x_mode=='Edit') { //JIKA MEMASUKKAN OBAT?>
					<div class="control-label col-md-6 col-sm-6 col-xs-12"> 
					<div class="form-group">
                         <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No Penjualan <span class="required">*</span>
                         </label>
                         <div class="col-md-6 col-sm-6 col-xs-12">
						 <input type="text" id="penjualan_no" name="penjualan_no" value="<?php echo $_POST["penjualan_no"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     
							</div>
                      </div>
						           <!--
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Resep Dokter </span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<input type="text" id="kamar_kode" name="kamar_kode" value="<?php echo $_POST["rawat_terapi"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     
						</div>
                         </div>   -->
						 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Pasien</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
						<input type="text" id="cust_usr_nama" name="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">							                                     
						<input type="hidden" name="custTambah" id="custTambah"/>
						<?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]); ?>
						</div>
                      </div> 
					  
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Ditanggung Oleh <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						 <input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
							&nbsp;&nbsp;&nbsp;
							<input type="submit" name="btnOk" value="OK" class="submit" />
						</div>
                         </div>
						 
						 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Dokter <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						<select class="form-control" name="id_usr" id="id_usr" required="required" >
							<option  value="" >[ Pilih Dokter ]</option>
                                   <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>"<?php if($dataDokter[$i]["usr_id"]==$_POST["id_usr"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>
                                    <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                                   <?php } ?>
                              </select>
							  </div>
                         </div>
						 
						 
						 <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Poli <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
						 <select class="form-control" name="poli" id="poli">
                  <option class="inputField" value="0" >[ Pilih Poli ]</option>
                       <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                            <option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["poli"]) echo "selected";?>><?php echo  $dataPoli[$i]["poli_nama"] ;?></option>
                        <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                       <?php } ?>
                  </select>
                  </div>
             </div>
						 </div>
						 
						 <div class="control-label col-md-6 col-sm-6 col-xs-12"> 
						 <div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12">
						   <td align="center" width="40%" class="tablecontent" rowspan="4" valign="top">&nbsp;</td>
							</div>	
                        <div class="col-md-6 col-sm-6 col-xs-12">
						 <label class="control-label col-md-6 col-sm-6 col-xs-12" for="last-name">Total Biaya Obat
                        </label>
									<?php echo $view->RenderHidden("Grandstotal","Grandstotal",currency_format($Grand),"curedit", "readonly",false);?>
				          <?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"] = currency_format($Grand),"curedit", "readonly",true,'onChange=GantiPengurangan(this.value)');?></td>
												
							</div>
                         </div>
						 </div>
					<div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
						 <td width="20%" align="left" valign="middle">&nbsp;</td>
				       <td width="30%" align="center">
               <input type="submit" name="btnRefresh" id="btnRefresh" value="Refresh" class="submit"/>
               <input type="submit" name="btnBayar" id="btnBayar" value="Simpan Penjualan" class="submit" onClick="javascript:return CekData();"/>     
				       <input type="button" name="kembali" id="kembali" value="Kembali" class="submit" onClick="document.location.href='penjualan_view.php'";/>     
				       </td>
							</div>
                         </div>
						 
						 <table width="100%" border="0" cellpadding="1" cellspacing="1"> 
           
        <tr>                                         
 <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <tr class="subheader">
                      <td align="left" width="2%" >&nbsp;Hapus<?php //echo "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">"; ?></td>
                      <!--<td align="left" width="2%" >&nbsp;Edit&nbsp;</td>-->
                      <td align="left" width="10%" >&nbsp;Kode &nbsp;</td>                                               
                      <td align="left" width="20%" >&nbsp;Paket&nbsp;</td>
                      <td align="left" width="20%" >&nbsp;Nama Obat&nbsp;</td> 
                      <td align="left" width="5%" >&nbsp;Jumlah</td>
                      <td align="left" width="15%" >&nbsp;Harga Jual</td>
                      <td align="left" width="10%" >&nbsp;Tuslag</td>
                      <td align="left" width="15%" >&nbsp;Total Harga</td>
                      <td align="left" width="10%" >&nbsp;No. Batch</td>
                      <td align="left" width="8%" >&nbsp;Expire Date</td>
                      <td align="left" width="20%" >&nbsp;Dosis</td> 
                      </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) {  $grandtotal += $dataTable[$i]["penjualan_detail_total"]; 
                    if($dataTable[$i]["penjualan_detail_nama_racikan"] || $dataTable[$i]["id_jenis_racikan"]) $tambahan = "(".$dataTable[$i]["penjualan_detail_nama_racikan"]."&nbsp;-&nbsp;".$dataTable[$i]["jenis_racikan_nama"].")";
                    else $tambahan = "&nbsp;";
              ?>
						  
                      <tr class="tablecontent-odd">
                      <td width="2%" align="center"><?php echo '<a href="'.$thisPage.'?del=1&id='.$dataTable[$i]["penjualan_detail_id"].'&transe='.$penjualanId.'&kodenya='.$_POST["cust_usr_kode"].'&id_regnya='.$_POST["id_reg"].'&id_pembayaran='.$_POST["id_pembayaran"].'&id_dokter='.$_POST["id_usr"].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>'; ?>
                      <?php //echo '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["penjualan_detail_id"].'">'; ?></td>
                      <!--<td align="left" width="2%" ><?php //echo '<a href="#" onClick="Editobat(\''.$dataTable[$i]["id_penjualan"].'\',\''.$dataTable[$i]["penjualan_detail_id"].'\',\''.$dataTable[$i]["item_id"].'\',\''.$dataTable[$i]["item_nama"].'\',\''.currency_format($dataTable[$i]["penjualan_detail_harga_jual"]).'\',\''.currency_format($dataTable[$i]["penjualan_detail_jumlah"]).'\',\''.currency_format($dataTable[$i]["penjualan_detail_total"]).'\',\''.$dataTable[$i]["id_petunjuk"].'\',\''.$dataTable[$i]["item_kode"].'\')"><img hspace="2" width="22" height="22" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>'; ?></td>-->
                      <td align="left" width="10%" ><?php echo $dataTable[$i]["item_kode"];?></td>                                               
                      <td></td>
                      <td align="left" width="20%" ><?php echo $dataTable[$i]["item_nama"]."&nbsp;".$tambahan;?></td> 
                      <td align="left" width="5%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_jumlah"],2);?></td>
                      <td align="left" width="15%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_harga_jual"]);?></td>
                      <td align="left" width="10%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_tuslag"])?></td>
                      <td align="left" width="15%" ><?php echo currency_format($dataTable[$i]["penjualan_detail_total"])?></td>
                      <td align="left" width="8%" ><?php echo $dataTable[$i]["batch_no"];?></td>
                      <td align="left" width="8%" ><?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
                      <td align="left" width="20%" ><?php echo $dataTable[$i]["penjualan_detail_dosis_obat"];?></td>
                      </tr>
						  
						  <?php } ?>
                      
                      <tr>
                      <td align="left" width="2%" class="tablecontent-odd">&nbsp;&nbsp;</td>
                      <!--<td align="left" width="2%" class="tablecontent-odd">&nbsp;&nbsp;</td>-->
                      <td align="left" width="10%" class="tablecontent-odd">   
					  
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=550&width=800&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
                      <?php echo $view->RenderTextBox("obat_kode","obat_kode","10","10",$_POST["obat_kode"],"inputField",false,false);?>                                        
                      <input type="hidden" name="obat_id" id="obat_id" value="<?php echo $_POST["obat_id"];?>" />   
                      <input type="hidden" name="id_batch" id="id_batch" value="<?php echo $_POST["id_batch"];?>" />   
                      <!--<img src="<?php //echo $ROOT;?>gambar/finder.png" border="0" align="middle" class="tombol" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" />-->
                      </a>
					  
					  </td>                                                                                                                                  
                      <td align="left" width="20%" class="tablecontent-odd">  
                      <a href="<?php echo $findPaket;?>?TB_iframe=true&height=400&width=600&modal=true&transaksi=<?php echo $penjualanId; ?>" class="thickbox" title="Pilih Paket">
                      <?php echo $view->RenderTextBox("paket_nama","paket_nama","30","100",$_POST["paket_nama"],"inputField", "",false);?></a></td>                                                                                                                                                         
                      <td align="left" width="20%" class="tablecontent-odd">
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=550&width=500&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
                      <?php echo $view->RenderTextBox("obat_nama","obat_nama","30","100",$_POST["obat_nama"],"inputField", "",false);?></a></td>                                                                                                                                                         
                      <td align="left" width="5%" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtJumlah","txtJumlah","4","4",$_POST["txtJumlah"],"curedit", "",false,'onChange=GantiHarga(this)');?></td>					                                                                                        
                      <td align="left" width="15%" class="tablecontent-odd">
                      <input type="hidden" name="txtHargaSatuan" id="txtHargaSatuan" value="<?php echo $_POST["txtHargaSatuan"];?>">
                      <span id="txtSatuanNom"></span>
                      <?php //echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",currency_format($_POST["txtHargaSatuan"]),"curedit", "readonly",false,'onChange=GantiHarga(this)');?></td>					                                                                                        
                      <td align="left" width="5%" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtTuslag","txtTuslag","5","10",$_POST["txtTuslag"],"curedit", "",true,'onChange=GantiHarga(this)');?></td>
                      <td align="left" width="15%" class="tablecontent-odd">
                      <input type="hidden" name="txtHargaTotal" id="txtHargaTotal" value="<?php echo $_POST["txtHargaTotal"];?>">
                      <span id="txtIsiTotale"></span>
                      <?php //echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "readonly",false);?></td>					                                                                                        
                      <td align="left" width="8%" class="tablecontent-odd"><?php echo $view->RenderTextBox("batch_no","batch_no","8","100",$_POST["batch_no"],"inputField", "readonly",false);?></td>
                      <td align="left" width="8%" class="tablecontent-odd"><?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo","batch_tgl_jatuh_tempo","8","100",$_POST["batch_tgl_jatuh_tempo"],"inputField", "readonly",false);?></td>

                      <td align="left" width="20%" class="tablecontent-odd"><?php echo $view->RenderTextBox("penjualan_detail_dosis_obat","penjualan_detail_dosis_obat","15","15",$_POST["penjualan_detail_dosis_obat"],"inputField","",false);?></td>					                                                                                      
                      </tr> 
                      <tr>

                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2"><input type="submit" name="btnSave" id="btnSave" value="Tambah Obat" class="submit" onClick="javascript:return CekTindakan(document.frmEdit);"></td>
                      <td align="right" width="2%" colspan="5">TOTAL YANG HARUS DIBAYAR&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4"><?php echo currency_format($grandtotal);?></td>
                      </tr>
                     
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="5">DISKON&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4">
                      <?php echo $view->RenderHidden("total","total",currency_format($grandtotal),"curedit", "readonly",false);?>
                      <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","15",currency_format($_POST["txtDiskon"]),"curedit", "readonly",true,'onChange=GantiGrandHarga(this.value)');?>
                      </td>
                      </tr>
                      <!-- Untuk RSPI dipaten 0 saja -->
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="5">BIAYA RESEP&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4">
                      <?php echo $view->RenderTextBox("txtResep","txtResep","15","15",currency_format($_POST["txtResep"]),"curedit", "readonly",true,'onChange=GantiResepHarga(this.value)');?>
                      </td>
                      </tr>
                      
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="5">BIAYA RACIKAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="4">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaRacikan","txtBiayaRacikan","15","15",currency_format($_POST["txtBiayaRacikan"]),"curedit", "readonly",true,'onChange=GantiRacikanHarga(this.value)');?>                                        		                                                   
                      </tr>
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="5">BIAYA PEMBULATAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="4">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaPembulatan","txtBiayaPembulatan","15","15",currency_format($_POST["txtBiayaPembulatan"]),"curedit", "readonly",true,'onChange=GantiPembulatanHarga(this.value)');?>                                        		                                                   
                      </tr>
                      
                      <tr class="tablesmallheader">                             
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="5">GRAND TOTAL&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4">
                      <?php echo $view->RenderHidden("txtBalik","txtBalik",currency_format($Grand),"curedit", "",false);?>
                      <?php echo $view->RenderLabel("txtGrandHargaTotal","txtGrandHargaTotal",currency_format($Grand),"curedit", "",false);?>
                      </td>
                      </tr>
                      
                      <!--<td colspan="8" align="left" class="tblCol">                                                      
                      <?php ///echo '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="submit">&nbsp;';?>&nbsp;
                      </td>-->
                      </tr> 
                                                     
                  </table>
				  
				  <script>document.frmFind.cust_usr_kode.focus();</script>  

      <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandtotalese;?>" />
      <input type="hidden" name="GrandHargaTotals" id="GrandHargaTotals" value="<?php echo $_POST["GrandHargaTotals"];?>" />
      <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
      <input type="hidden" id="penjualan_id" name="penjualan_id" value="<?php echo $penjualanId;?>" />      
      <input type="hidden" id="btn_edit" name="btn_edit" value="<?php echo $btn_edit;?>" />    
      <input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />      
      <input type="hidden" name="awal" value="1" />      
      <input type="hidden" name="hidUrut" value="<? echo $_POST["hidUrut"]; ?>">    
      <input type="hidden" name="cust_usr_kode" value="<? echo $_POST["cust_usr_kode"]; ?>"> 
      <input type="hidden" name="dokter_nama" value="<? echo $_POST["dokter_nama"]; ?>">
      <input type="hidden" name="id_reg" value="<? echo $_POST["id_reg"]; ?>">
      <input type="hidden" name="id_pembayaran" value="<? echo $_POST["id_pembayaran"]; ?>">
      <input type="hidden" name="reg_tanggal" value="<? echo $_POST["reg_tanggal"]; ?>">
      <input type="hidden" name="id_poli" value="<? echo $_POST["id_poli"]; ?>">
      <input type="hidden" name="id_cust_usr" value="<? echo $_POST["cust_usr_id"]; ?>">
      <input type="hidden" name="id_dokter" value="<? if($_POST["id_dokter"]){echo $_POST["id_dokter"];}else{$_GET["id_dokter"];} ?>">
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