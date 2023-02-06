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
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
	   
  // echo $theDep;  
     //AUTHENTIFIKASI
   if(!$auth->IsAllowed("apo_penjualan_penjualan",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_penjualan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
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
     $findPaket = "paket_find1.php?transaksi=".$transaksiId;
     
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
	if($_x_mode == "Edit" || $_POST["btnLanjut"] || $_POST["tombol_f2"] || $_POST["btnTambah"]) {  //Jika ditekan Tombol Lanjut atau keyboard F2s
		if($_x_mode=="Edit"){
      $_POST["id_reg"] = $_POST["id_reg"];
    } else {
    $sql = "select a.* from klinik.klinik_registrasi a left join global.global_auth_poli b on b.poli_id=a.id_poli where b.poli_tipe='A' 
            and a.id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"])." and a.id_poli=".QuoteValue(DPE_CHAR,$poli)." order by reg_waktu desc";
    $rs = $dtaccess->Execute($sql);
    $dataReg = $dtaccess->Fetch($rs);
    //echo $sql;
    
    
    if(!$dataReg){
      //echo "daftar apotik"; die();
      $sql = "select * from klinik.klinik_registrasi where reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg_lama"]);
      $rs = $dtaccess->Execute($sql);
      $regLama = $dtaccess->Fetch($rs);
      
      // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_konf_loket_antrian_poli"] = $konfigurasi["dep_konf_loket_antrian_poli"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
     $postPoli = $_POST["dep_posting_poli"];
     $_POST["dep_konf_kode_instalasi"] = $konfigurasi["dep_konf_kode_instalasi"];
      $_POST["dep_konf_kode_poli"] = $konfigurasi["dep_konf_kode_poli"];
      $_POST["dep_konf_urut_registrasi"] = $konfigurasi["dep_konf_urut_registrasi"];
      $_POST["dep_konf_urut_pasien"] = $konfigurasi["dep_konf_urut_pasien"];
      $_POST["dep_konf_reg_poli"] = $konfigurasi["dep_konf_reg_poli"];
      $_POST["dep_konf_kode_sub_instalasi"] = $konfigurasi["dep_konf_kode_sub_instalasi"];
      
      $sql = "select poli_kode, id_instalasi, poli_tipe, id_sub_instalasi from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
      $poliKodeFetch = $dtaccess->Fetch($sql);
	    $kodePoli =  $poliKodeFetch["poli_kode"];
      $instalasiId =  $poliKodeFetch["id_instalasi"];
      $tipePoli = $poliKodeFetch["poli_tipe"];
      $subInsId = $poliKodeFetch["id_sub_instalasi"];
      
      $sql = "select * from global.global_auth_instalasi where instalasi_id=".QuoteValue(DPE_CHAR,$instalasiId);
      $rs = $dtaccess->Execute($sql);
      $dataIns = $dtaccess->Fetch($rs);
      $kodeIns = $dataIns["instalasi_kode"];
      
      $sql = "select * from global.global_auth_sub_instalasi where sub_instalasi_id=".QuoteValue(DPE_CHAR,$subInsId);
      $rs = $dtaccess->Execute($sql);
      $dataSubIns = $dtaccess->Fetch($rs);
      $kodeSubIns = $dataSubIns["sub_instalasi_kode"];

      //ambil kode registrasi
      $sql = "select max(reg_kode_urut) as nomorurut from klinik.klinik_registrasi";
      $noUrut = $dtaccess->Fetch($sql);
	    $kodeUrutReg =  $noUrut["nomorurut"]+1;

      $noantriReg = str_pad($noantri,4,"0",STR_PAD_LEFT);
      
      if($_POST["dep_konf_kode_sub_instalasi"]=="y"){
        if($kodeSubIns){
          if($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeSubIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeSubIns.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeSubIns.".".$kodePoli.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeSubIns.".".$kodePoli.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeSubIns.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeSubIns.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodeSubIns;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeSubIns.".".$kodePoli;
          }
        } else {
          if($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".01.".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = "01.".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".01.".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".01.".$kodePoli.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".01.".$kodePoli.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = "01.".$kodeUrutReg.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = "01.".$kodePoli.".".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = "01.".$kodePoli.".".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = "01.".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = "01.".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".01.".$noantriReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".01.".$kodeUrutReg;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".01.".$kodePoli;
          } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".01";
          } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = "01.".$kodePoli;
          }
        }
      } else {
      if($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodeIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodePoli.".".$kodeUrutReg.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodeIns.".".$kodeUrutReg.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodeIns.".".$kodePoli.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodeIns.".".$kodePoli.".".$kodeUrutReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodeUrutReg.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodePoli.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodePoli.".".$kodeUrutReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodeUrutReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="y"){
        $kodeTrans = $kodeIns.".".$noantriReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="y" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodeIns.".".$kodeUrutReg;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodeIns.".".$kodePoli;
      } elseif($_POST["dep_konf_kode_instalasi"]=="y" && $_POST["dep_konf_kode_poli"]=="n" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodeIns;
      } elseif($_POST["dep_konf_kode_instalasi"]=="n" && $_POST["dep_konf_kode_poli"]=="y" && $_POST["dep_konf_urut_registrasi"]=="n" && $_POST["dep_konf_urut_pasien"]=="n"){
        $kodeTrans = $kodePoli;
      }
      }
      
      //INSERT REG BARU
                // ---- insert ke registrasi ----
                $dbTable = "klinik.klinik_registrasi";
              
                $dbField[0] = "reg_id";   // PK
                $dbField[1] = "reg_tanggal";
                $dbField[2] = "reg_waktu";
                $dbField[3] = "id_cust_usr";
                $dbField[4] = "reg_status";
                $dbField[5] = "reg_who_update";
                $dbField[6] = "reg_when_update";
                $dbField[7] = "reg_jenis_pasien";
                $dbField[8] = "reg_status_pasien";
                $dbField[9] = "id_poli";
                $dbField[10] = "id_dep";
                $dbField[11] = "reg_no_antrian";
                $dbField[12] = "reg_status_cetak_kartu";
                $dbField[13] = "id_jam";
                $dbField[14] = "id_dokter";
                $dbField[15] = "id_info";
                $dbField[16] = "reg_asal";
                $dbField[17] = "reg_umur";
                $dbField[18] = "reg_umur_hari";
                $dbField[19] = "reg_kartu";
                $dbField[20] = "reg_program";
                $dbField[21] = "reg_rujukan_id";
                $dbField[22] = "id_prop";
                $dbField[23] = "id_kota";
                $dbField[24] = "reg_utama";
                $dbField[25] = "id_pembayaran";
                $dbField[26] = "reg_shift";
                $dbField[27] = "reg_tipe_layanan";
                $dbField[28] = "reg_umur_bulan";
                $dbField[29] = "reg_kode_urut";
                $dbField[30] = "reg_kode_trans";
                $dbField[31] = "id_instalasi";
                $dbField[32] = "reg_tipe_rawat";
                $dbField[33] = "reg_urut";
                if($regLama["reg_jenis_pasien"]=='7') { 
                $dbField[34] = "id_perusahaan";
                }elseif($regLama["reg_jenis_pasien"]=='5' || $regLama["reg_jenis_pasien"]=='26') { 
                $dbField[34] = "reg_tipe_jkn";
                $dbField[35] = "reg_no_sep";
                }elseif($regLama["reg_jenis_pasien"]=='18') { 
                $dbField[34] = "id_jamkesda_kota";
                }elseif($regLama["reg_jenis_pasien"]=='25'){
                $dbField[34] = "reg_tipe_paket";
                }
                
                $status = 'M1';  // Status dari Penata Jasa --
                $sqlreg = "select * from klinik.klinik_registrasi 
                     where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"])." and id_poli = '".$poli."'";
                $datastatuspasien= $dtaccess->Fetch($sqlreg);
                 //echo $sqlreg;

                if ($datastatuspasien["id_poli"]){
                $statusPasien = "L";
                } else
                {
                $statusPasien = "B";
                }
               
               /* if($_POST["btnSave"]) $statusPasien =$_POST["reg_status_pasien"];
                else $statusPasien = 'L';   */
      
                $sql = "select max(reg_urut) as urut from klinik.klinik_registrasi where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                $rs = $dtaccess->Execute($sql);
                $max = $dtaccess->Fetch($rs);
                $regUrut = $max["urut"]+1;
      
                $regId = $dtaccess->GetTransID();
                
                if ($_POST["dep_konf_loket_antrian_poli"]=='n') //apabila cetak antrian tidak per klinik
                   $sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi 
                          where reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." and id_dep = ".QuoteValue(DPE_CHAR,$depId);          
                else
                   $sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi 
                          where reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." and id_poli = ".QuoteValue(DPE_CHAR,$poli)." 
                          and id_dep = ".QuoteValue(DPE_CHAR,$depId);
                $noAntrian = $dtaccess->Fetch($sql);
          	    $noantri =  ($noAntrian["nomore"]+1);
          	    
                $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
                $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
                $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
                $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                $dbValue[4] = QuoteValue(DPE_CHAR,$status);
                $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
                $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$regLama["reg_jenis_pasien"]);
                $dbValue[8] = QuoteValue(DPE_CHAR,$statusPasien);
                $dbValue[9] = QuoteValue(DPE_CHAR,$poli);
                $dbValue[10] = QuoteValue(DPE_CHAR,$depId);
                $dbValue[11] = QuoteValue(DPE_NUMERIC,$noantri);
                $dbValue[12] = QuoteValue(DPE_CHAR,'n');
                $dbValue[13] = QuoteValue(DPE_CHAR,$regLama["id_jam"]);
                if(!$_POST["usr"]){
                $dbValue[14] = QuoteValue(DPE_CHAR,$regLama["id_dokter"]);
                } else {
                $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["usr"]);
                }
                $dbValue[15] = QuoteValue(DPE_CHAR,$regLama["id_info"]);
                $dbValue[16] = QuoteValue(DPE_CHAR,$regLama["reg_asal"]);
                $dbValue[17] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur"]);
                $dbValue[18] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur_hari"]);
                $dbValue[19] = QuoteValue(DPE_CHAR,$regLama["reg_kartu"]);
                $dbValue[20] = QuoteValue(DPE_CHAR,$regLama["id_prog"]);
                $dbValue[21] = QuoteValue(DPE_CHAR,$regLama["reg_rujukan_id"]);
                $dbValue[22] = QuoteValue(DPE_CHAR,$regLama["id_prop"]);
                $dbValue[23] = QuoteValue(DPE_CHAR,$regLama["id_kota"]);
                $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["id_reg_lama"]);
                $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
                $dbValue[26] = QuoteValue(DPE_CHAR,$regLama["reg_shift"]);
                $dbValue[27] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_layanan"]);
                $dbValue[28] = QuoteValue(DPE_NUMERIC,$regLama["reg_umur_bulan"]);                
                $dbValue[29] = QuoteValue(DPE_NUMERIC,$kodeUrutReg);
                $dbValue[30] = QuoteValue(DPE_CHAR,$kodeTrans);
                $dbValue[31] = QuoteValue(DPE_CHAR,$poliKodeFetch["id_instalasi"]);
                $dbValue[32] = QuoteValue(DPE_CHAR,'J');
                $dbValue[33] = QuoteValue(DPE_NUMERIC,$regUrut);
                if($regLama["reg_jenis_pasien"]=='7') {
                $dbValue[34] = QuoteValue(DPE_CHAR,$regLama["id_perusahaan"]);
                }elseif($regLama["reg_jenis_pasien"]=='5' || $regLama["reg_jenis_pasien"]=='26') { 
                $dbValue[34] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_jkn"]);
                $dbValue[35] = QuoteValue(DPE_CHAR,$regLama["reg_no_sep"]);
                }elseif($regLama["reg_jenis_pasien"]=='18') { 
                $dbValue[34] = QuoteValue(DPE_CHAR,$regLama["id_jamkesda_kota"]);
                }elseif($regLama["reg_jenis_pasien"]=='25'){
                $dbValue[34] = QuoteValue(DPE_CHAR,$regLama["reg_tipe_paket"]);
                }
                
                //print_r($dbValue);
                //die();
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                
                $dtmodel->Insert() or die("insert error");
                
                //echo $cek_nya."<br />";
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
                
        $_POST["id_reg"] = $regId;
    } else {
      //echo "poli_apotik sdh ada."; die();
      $_POST["id_reg"] = $dataReg["reg_id"];
    }
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
  
//link paket
     $findPaket = "paket_find1.php?transaksi=".$penjualanId;
 
         $sql = "select sum(penjualan_detail_total) as penjualan_total_detail from apotik.apotik_penjualan_detail  where 
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $total = $dtaccess->Fetch($rs);
         
         $_POST["penjualan_total_detail"] = $total["penjualan_total_detail"];


  
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
     if ($_POST["btnBayar"]) {

    // $_POST["penjualan_total_obat"]=$_POST["txtBalik"];
     if ($_POST["obat_id"]) {
     
     $dateSekarang = date('Y-m-d H:i:s');
     $date = $_POST["reg_tanggal"]." ".date("H:i:s");
      //  echo $date;
        // die();
          
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
          unset($_POST["txtTuslag"]);   
          unset($_POST["penjualan_detail_dosis_obat"]);                 
     }
      $isprint = "n";  
      $grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"])-StripCurrency($_POST["txtDiskon"]); 
     //   $newGrand = $_POST["txtBalik"] ;
   //  echo "grandtotal".$grandTotals."<br> newgrand".$newGrand;  
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
      $dbField[24]  = "id_poli";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_detail"]));  
      $dbValue[4] = QuoteValue(DPE_CHAR,'n');
      $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[7] = QuoteValue(DPE_CHAR,'D');
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
      $dbValue[21] = QuoteValue(DPE_CHARKEY,$folId);            
      $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["id_usr"]);
      $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["poli"]);
      
    //  print_r ($dbValue);
   //   die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Update() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
        
          $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
                   where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          		$idPemb = $dtaccess->Fetch($sqlpemb);
//          $sql  ="update klinik.kliniK_registrasi set reg_obat='y' , reg_status = 'E0'
 //                 where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql  ="update klinik.kliniK_registrasi set reg_obat='y'
                  where id_pembayaran=".QuoteValue(DPE_CHARKEY,$_POST["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);

          $rs = $dtaccess->Execute($sql); 
//echo $sqlpemb;
//die();   
 
 // Masuk Kassa Rawat Jalan
 
         /*$sql = "select sum(penjualan_detail_total) as penjualan_total_obat from apotik.apotik_penjualan_detail  where penjualan_detail_flag = 'n' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detailflag = $dtaccess->Fetch($rs);
         
         $_POST["penjualan_total_obat"] = $detailflag["penjualan_total_obat"];*/
         
         $sql = "select sum(penjualan_detail_total) as penjualan_total_obat from apotik.apotik_penjualan_detail y
         left join logistik.logistik_item x on x.item_id = y.id_item where y.penjualan_detail_flag = 'n' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detJual = $dtaccess->Fetch($rs);
          //echo $sql;die();
         $sql = "select sum(penjualan_detail_total) as penjualan_total_obat_fornas from apotik.apotik_penjualan_detail y
         left join logistik.logistik_item x on x.item_id = y.id_item where y.penjualan_detail_flag = 'n' and x.item_fornas = 'y' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detJualFornas = $dtaccess->Fetch($rs);
         
         $sql = "select * from logistik.logistik_item x left join apotik.apotik_penjualan_detail y on y.id_item = x.item_id 
         where y.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ." and y.penjualan_detail_id = ".QuoteValue(DPE_CHAR,$penjualanDetailId);
         $rs = $dtaccess->Execute($sql);
         $detFlag = $dtaccess->Fetch($rs);
         
         //$_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] ;
         $_POST["penjualan_total_obat_fornas"] = $detJualFornas["penjualan_total_obat_fornas"];
         $_POST["penjualan_total_obat_fornas_flag"] = $detFlag["item_fornas"]; 
         
         if ($_POST["penjualan_total_obat_fornas"] <> '0' && ($_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='26')) {
          $_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] -  $detJualFornas["penjualan_total_obat_fornas"];
          //echo  $_POST["penjualan_total_obat"] ; die();
         }
         else
         {
         $_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] ;
          //echo  "2".$_POST["penjualan_total_obat"] ; die();
         }    

         //echo  $_POST["penjualan_total_obat"]." dan " .$sql;
         //die();
        // echo "ini ".$_POST["penjualan_detail_flag"];
         $sql = "select count(id_item) as total_item from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $jumlahTotalObat = $dtaccess->Fetch($rs);

         $sql = "select e_medrec from apotik.apotik_penjualan where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $medrec = $dtaccess->Fetch($rs);
         //if ($detailflag["penjualan_detail_flag"] == 'n') {
         
     //    if($medrec["e_medrec"]=='n'){
          
          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_dibayar_when";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";                   
          $dbField[9] = "id_poli";
          $dbField[10] = "fol_jenis_pasien";
          $dbField[11] = "id_dep";
          $dbField[12] = "id_pembayaran";                          
          $dbField[13] = "fol_nominal";
          $dbField[14] = "fol_dibayar";
          $dbField[15] = "fol_total_harga";
          $dbField[16] = "fol_jumlah";
          $dbField[17] = "fol_catatan";
          $dbField[18] = "id_dokter";
          $dbField[19] = "fol_nominal_satuan";
          $dbField[20] = "who_when_update";
                                                            
       if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26')
              { 
          $dbField[21] = "fol_hrs_bayar";
          $dbField[22] = "fol_dijamin";
          $dbField[23] = "fol_subsidi";
          $dbField[24] = "fol_iur_biaya";
         } elseif ($_POST["reg_jenis_pasien"]=='18') {
          $dbField[21] = "fol_hrs_bayar";
          $dbField[22] = "fol_dijamin";
          $dbField[23] = "fol_dijamin1";
          $dbField[24] = "fol_dijamin2";
         }
      else{           
          $dbField[21] = "fol_hrs_bayar";
        }

	   $sqlJamkesda = "	select a.id_jamkesda_kota, b.jamkesda_kota_nama, b.jamkesda_kota_persentase_kota, b.jamkesda_kota_persentase_prov 
						from klinik.klinik_registrasi a 
						left join global.global_jamkesda_kota b 
						on a.id_jamkesda_kota=b.jamkesda_kota_id 
						where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
					$dataJamkesda = $dtaccess->Fetch($sqlJamkesda);
					$jamkesdaNama=$dataJamkesda["jamkesda_kota_nama"];
					$jamkesdaPesentaseKota=$dataJamkesda["jamkesda_kota_persentase_kota"];
					$jamkesdaPesentaseProv=$dataJamkesda["jamkesda_kota_persentase_prov"];	
          
//         $sqltdk = "select biaya_jenis, biaya_nama, biaya_id from klinik.klinik_biaya where biaya_jenis = 'O' and id_dep =".QuoteValue(DPE_CHAR,$depId);
//         $dataObat = $dtaccess->Fetch($sqltdk);
         $date = date('Y-m-d H:i:s');                

				 if ($_POST["reg_jenis_pasien"]=='18') 
				 {
					$totalTindNom = StripCurrency($grandTotals);
					$jaminDinkesProv=(StripCurrency($totalTindNom)*StripCurrency($jamkesdaPesentaseProv)/100);
					$jaminDinkesKota=(StripCurrency($totalTindNom)*StripCurrency($jamkesdaPesentaseKota)/100);
					$totalJaminan=StripCurrency($jaminDinkesKota)+StripCurrency($jaminDinkesProv);
          $hrsBayar = StripCurrency($totalTindNom)-StripCurrency($totalJaminan);
				  
				 }elseif( $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='26'){
          //$_POST["penjualan_total_obat"]  = 0;
					$totalTindNom = StripCurrency($_POST["penjualan_total_obat"]);
					$dijamin=StripCurrency($totalTindNom);
					$hrsBayar=0;
         } 
				 elseif ($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='7') 
				 {
					$totalTindNom = StripCurrency($grandTotals);
					$dijamin=StripCurrency($totalTindNom);
					$hrsBayar=0;
				 }
          //cari folio penjualan tersebut
         $sql = "select * from  klinik.klinik_folio where fol_catatan = ".QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
         $rs = $dtaccess->Execute($sql);
         $foliojualan = $dtaccess->Fetch($rs);
         
               if(!$foliojualan["fol_id"]){  $folId = $dtaccess->GetTransID();
                }else{
                $folId = $foliojualan["fol_id"];
                }
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_DATE,$date);
               $dbValue[4] = QuoteValue(DPE_CHAR,'OA');
               $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["cust_usr_id"]);
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHARKEY,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
               $dbValue[9] = QuoteValue(DPE_CHARKEY,$_POST["id_poli"]);
               $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_CHAR,$idPemb["id_pembayaran"]);
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
					     $dbValue[16] = QuoteValue(DPE_NUMERIC,$jumlahTotalObat["total_item"]);
               $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["id_usr"]); //apoteker
               $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               if(!$foliojualan["fol_id"]){
               $dbValue[20] = QuoteValue(DPE_CHAR,$usrId); //apoteker
               }else{
               $dbValue[20] = QuoteValue(DPE_CHAR,$foliojualan["who_when_update"]); //apoteker
               }                              
					if ($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26') {
               $dbValue[21] = QuoteValue(DPE_NUMERIC,0);
      				 $dbValue[22] = QuoteValue(DPE_NUMERIC,$dijamin);	
					     $dbValue[23] = QuoteValue(DPE_NUMERIC,0);
					     $dbValue[24] = QuoteValue(DPE_NUMERIC,0);
				        }
				   elseif($_POST["reg_jenis_pasien"]=='18') {
               $dbValue[21] = QuoteValue(DPE_NUMERIC,0);
    					 $dbValue[22] = QuoteValue(DPE_NUMERIC,StripCurrency($totalJaminan));	
		    			 $dbValue[23] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesProv));
				    	 $dbValue[24] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesKota));											
				      }
					else{
               $dbValue[21] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));   
              }
                        
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               if(!$foliojualan["fol_id"]){
               $dtmodel->Insert() or die("insert  error");
               }else{
               $dtmodel->Update() or die("update  error");               
               }
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
            
            //masukkan pelaksana
                 //masukkan dokter dahulu
               if(!$foliojualan["fol_id"]){ 
                $dbTable = "klinik.klinik_folio_pelaksana";
    					
    						$dbField[0] = "fol_pelaksana_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "fol_pelaksana_tipe";            
    						
                $folPelId = $dtaccess->GetTransID();
                  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
    						$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_usr"]);
    						$dbValue[3] = QuoteValue(DPE_CHAR,'1');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey); 

                 //masukkan pelaksana dahulu
                $dbTable = "klinik.klinik_folio_pelaksana";
    					
    						$dbField[0] = "fol_pelaksana_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "fol_pelaksana_tipe";
    						  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
    						$dbValue[2] = QuoteValue(DPE_CHAR,$usrId);
    						$dbValue[3] = QuoteValue(DPE_CHAR,'2');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey);                                          
				        
				    $sql = "select * from  klinik.klinik_split where split_flag = ".QuoteValue(DPE_CHAR,SPLIT_OBAT)." and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by split_id";
            $rs = $dtaccess->Execute($sql,DB_SCHEMA);
            $dataSplit = $dtaccess->Fetch($rs);
            
						$dbTable = "klinik.klinik_folio_split";
					
						$dbField[0] = "folsplit_id";   // PK
						$dbField[1] = "id_fol";
						$dbField[2] = "id_split";
						$dbField[3] = "folsplit_nominal";
							  
						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
						$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit["split_id"]);
						$dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_obat"]));
						 
						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
						
						$dtmodel->Insert() or die("insert error"); 
						
						unset($dtmodel);
						unset($dbField);
						unset($dbValue);
						unset($dbKey); 
                }
            $sql = "update apotik.apotik_penjualan set id_fol=".QuoteValue(DPE_CHAR,$folId)." 
                    where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
         //      }
               
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJual = $dtaccess->FetchAll($rs); 
      // echo $sql; die();
      for($i=0,$n=count($dataJual);$i<$n;$i++){
        //hapus penjualan yang sebelumnya
          $sql = "delete from logistik.logistik_stok_item where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);

          $sql = "delete from logistik.logistik_stok_item_batch where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);
     /*     
           $sql = "select a.*, c.gudang_nama as nama_asal, d.gudang_nama as nama_tujuan
                         from logistik.logistik_stok_item a
                         left join logistik.logistik_gudang b on a.id_gudang = b.gudang_id
                         left join logistik.logistik_gudang c on a.id_dep_asal = c.gudang_id
                         left join logistik.logistik_gudang d on a.id_dep_tujuan = d.gudang_id";
                 $sql .= " where a.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
                 $sql .= " a.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
                 $sql .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
                 $sql .= " order by a.id_gudang asc, a.stok_item_create asc";
                 $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            	   $dataTable1 = $dtaccess->FetchAll($rs);
      // echo $sql; 
                 for($ia=0,$na=count($dataTable1);$ia<$na;$ia++)
                 {
                   if ($dataTable1[$ia]["stok_item_flag"]=='A') //Saldo Awal
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='PP') //Pemakaian
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='B') //Pembelian
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='P') //Penjualan
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='O') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='M') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
            
                      //update saldo stok
//                      if ($saldo>0)
//                      {
                       $sql  ="update logistik.logistik_stok_item 
                               set stok_item_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                              where stok_item_id =".QuoteValue(DPE_CHAR,$dataTable1[$ia]["stok_item_id"]);
                        $df = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
                 //       echo $sql;              
//                       }
                } //akhir looping for stok item
//                      if ($saldo>0)
 //                     {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_dep 
                                set stok_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                                where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and 
                               id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                         $fg = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
//                     }               
                   
           //      echo $sql;

               //Adjustment Item Batch
               $sqlBatch = "select a.item_nama,b.batch_id,b.batch_no,c.* from 
                            logistik.logistik_item a left join 
                            logistik.logistik_item_batch b on b.id_item = a.item_id left join
                            logistik.logistik_stok_item_batch c on b.batch_id = c.id_batch";
               $sqlBatch .= " where c.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
               $sqlBatch .= " c.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
               $sqlBatch .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
               $sqlBatch .= " order by a.item_nama,b.batch_id,a.id_gudang asc, c.stok_item_batch_create asc";
            //  echo $sqlBatch;

               $rsBatch = $dtaccess->Execute($sqlBatch,DB_SCHEMA_LOGISTIK);
          	   $dataBatch = $dtaccess->FetchAll($rsBatch);
                 for($k=0,$l=count($dataBatch);$k<$l;$k++)
                 {
                //   echo "ke".$k;
           
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='A') //Saldo Awal
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='PP') //Pemakaian
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='B') //Pembelian
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='P') //Penjualan
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='O') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='M') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
            
                      //update saldo stok
                    //  if ($saldoBatch>0)
                    //  {
                       $sql  ="update logistik.logistik_stok_item_batch 
                               set stok_item_batch_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                              where stok_item_batch_id =".QuoteValue(DPE_CHAR,$dataBatch[$k]["stok_item_batch_id"]);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
                     //  }
            
                   //   if ($saldoBatch>0)
                   //   {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_batch_dep 
                                set stok_batch_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                                where id_batch =".QuoteValue(DPE_CHAR,$dataBatch[$k]["batch_id"])." and 
                                id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
                    // }              
                    //echo "Adjust Batch : ".$dataBatch[$k]["batch_no"]." Berhasil <br>";

                    if($dataBatch[$k]["batch_id"]!=$dataBatch[$k+1]["batch_id"]) unset($saldoBatch);

                   } //end for batch
         
       */   
      // cek apakah ada dua batch atau lebih yg di input //
        if($dataJual[$i]["id_batch"]!=$dataJual[$i-1]["id_batch"]) {        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
 //echo $sql; //die();         
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
//echo $sql; die();           
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
    } 
    
    }  
     //$isprint = "y";
      $_x_mode = "cetak" ;
	   //$next = "penjualan_cetak.php?id=".$penjualanId;
     //header("location:".$thisPage);
     //exit();
      
     }

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
     
     if($_GET["del"]) {
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
     if($totaljual["total"]<>null){
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
    }else{
     
     
     if($nojual["penjualan_nomor"]<>'' || $nojual["penjualan_nomor"]<>null){
     $sql = "delete from klinik.klinik_folio where fol_catatan = '".$nojual["penjualan_nomor"]."'";
     $rs = $dtaccess->Execute($sql);
    }
    
    $sql = "delete from apotik.apotik_penjualan where penjualan_id = '".$_GET["transe"]."'";
     $rs = $dtaccess->Execute($sql);
      
      $kembali = "penjualan_view.php"; 
          header("location:".$kembali);
          exit();     
    }             
       
     }
        
     $sql = "select penjualan_keterangan from apotik.apotik_penjualan
             where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);       
     $rs = $dtaccess->Execute($sql);
     $tanggungan = $dtaccess->Fetch($rs);  
     
     $_POST["fol_keterangan"] = $tanggungan["penjualan_keterangan"];  
        
     $sql = "select *,b.item_nama,b.item_kode,c.jenis_nama,d.petunjuk_nama, f.batch_no, f.batch_tgl_jatuh_tempo
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
     
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') order by usr_name ";
          $rs = $dtaccess->Execute($sql);
          $dataDokter = $dtaccess->FetchAll($rs);
          
      $sql = "select * from global.global_auth_poli where (poli_tipe='J' or poli_tipe='M' or poli_tipe='A') order by poli_tree asc ";
      $rs = $dtaccess->Execute($sql);
      $dataPoli = $dtaccess->FetchAll($rs);
          
    $sql = "select * from global.global_auth_user where usr_id = '".$_POST["id_dokter"]."'";
          $rs = $dtaccess->Execute($sql);
          
    $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konf = $dtaccess->Fetch($rs);
  
?>

<?php //echo $view->RenderBody("module.css",true,true,"PENJUALAN"); ?>
<br /><br /><br /><br />

<?php //echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">
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

     
</script>  

     <?php if ($_x_mode=='New') { ?>
     <body>

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
                  <td align="left" width="30%" class="tablecontent">&nbsp;No. Penjualan&nbsp;</td>                                       
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <?php echo $view->RenderTextBox("penjualan_no","penjualan_no","30","100",$_POST["penjualan_no"],"inputField", "",false);?>                     </td>                                      
                </tr> 
               <!--<tr>
              		<td width= "5%" align="left" class="tablecontent">&nbsp;Dokter</td>
              		<td width= "50%" align="left" class="tablecontent-odd">
                             <input  type="text" name="dokter_nama" id="dokter_nama" size="25" maxlength="25" value="<?php echo $_POST["dokter_nama"];?>"/>
                             <a href="<?php echo $findDokterPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($ROOT);?>gambar/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                              <input type="hidden" name="id_dokter" id="id_dokter" value="<?php echo $_POST["id_dokter"];?>"//>
                    </td>
                </tr>-->     
                <tr>
              		<td width= "5%" align="left" class="tablecontent">&nbsp;Nomor RM</td>
              		<td width= "50%" align="left" class="tablecontent-odd">
                             <a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>" readonly="readonly" /></a>
                             <a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($ROOT);?>gambar/finder.png" border="0" align="top" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
                             <!--<input type="submit" name="btnLanjut" value="Lanjut" class="submit"/>-->
                             <!--<input type="submit" name="btnAdd" value="Tambah" class="button"/>-->
                              <input type="hidden" name="custTambah" id="custTambah"/>
                    </td>
                </tr>
                                               
                <tr>                                       
                  <td align="left" width="30%" class="tablecontent">&nbsp;Nama Pasien&nbsp;</td>                                 
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <a href="<?php echo $findPage;?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","30","100",$_POST["cust_usr_nama"],"inputField", "readonly",false);?></a>                                           
                    <!--<a href="<?php //echo $pasienFind;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php //echo $outlet; ?>" class="thickbox" title="Pilih pasien"><img src="<?php //echo $ROOT;?>gambar/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" /></a>-->                                           
                    <?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["cust_usr_id"]); ?>
                    <?php echo $view->RenderHidden("id_reg_lama","id_reg_lama",$_POST["id_reg_lama"]); ?>
                    <?php echo $view->RenderHidden("id_pembayaran","id_pembayaran",$_POST["id_pembayaran"]); ?>                                    
                </tr> 
                <!--<tr>
                		<td width= "20%" class="tablecontent">&nbsp;Alamat</td>
                		<td class="tablecontent-odd">
                			<table border=0 cellpadding=1 cellspacing=0 width="100%">
                				<tr>
                					<td colspan="2">
                				 <input type="text" name="cust_usr_alamat" id="cust_usr_alamat" readonly size="65" maxlength="225" value="<?php echo $_POST["cust_usr_alamat"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                						
                        	</td>
                				</tr>
                			</table>
                		</td>
                	</tr>-->
                <!--<tr>
              		<td class="tablecontent">&nbsp;Jenis Pasien</td>
                  <td colspan="2" class="tablecontent-odd" >
              			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
         <!-- <option value="" >[ Pilih Jenis Pasien ]</option>
          <?php //for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
          <option value="<?php //echo $jenisPasien[$i]["jenis_id"];?>" <?php //if($jenisPasien[$i]["jenis_id"]==$_POST["cust_usr_jenis"]) echo "selected"; ?>><?php //echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
				<?php //} ?>
                    </select>
              		</td>
              	</tr>  --> 
                <tr>                     
                    <td colspan="4">&nbsp;<?php echo $view->RenderHidden("tombol_f2","tombol_f2",$_POST["tombol_f2"]); ?></td>                  
                  </tr> 
                <?php if ($_x_mode!='Edit') { ?>   
                <tr>                     
                  <td colspan="4" class="tablecontent"><input type="submit" id="btnTambah" name="btnDaftar" value="Tambahkan" class="submit" onClick="javascript:return CekDataTambah();"/></td>
                    <!--<td colspan="4" class="tablecontent">Tekan tombol F2 untuk memasukkan Obat</td>   -->               
                  </tr> 
                 <? } ?>                       
              </table>
            </td>              
        </tr> 
        
        <?php if ($_pasien_salah) { //JIKA MEMASUKKAN OBAT?>
        <tr class="tableheader">
            <td colspan="4"><font color="red" size="1"><strong>Kode Pasien Tidak Ditemukan</strong></font></td>
        </tr>
        <?php } ?>
      
       </form> 
      </table>
      </div>
		  </div>

  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table> -->  
       <?php } //END IF NEW ?>
        
       <?php if ($_x_mode=='Edit') { //JIKA MEMASUKKAN OBAT?>
      <body>
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
                  <td align="left" width="5%" class="tablecontent" rowspan="4"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
                  <td align="left" width="10%" class="tablecontent">&nbsp;No. Penjualan&nbsp;</td>                                       
                  <td align="left" width="45%" class="tablecontent-odd">&nbsp;<?php echo $_POST["penjualan_no"];?></td>                                      
                  <td align="center" width="40%" class="tablecontent" rowspan="4" valign="top"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($Grand);?></span></font></td>
                </tr> 
                <!--<tr>                                       
                  <td align="left" width="30%" class="tablecontent">&nbsp;Nama Dokter&nbsp;</td>                                 
                  <td align="left" width="70%" class="tablecontent-odd">                                           
                    <?php echo $view->RenderTextBox("dokter_nama","dokter_nama","30","100",$_POST["dokter_nama"],"inputField", "readonly",false);?>                                                                               
                </tr>-->
                <tr>
              		<td width="10%" align="left" class="tablecontent">&nbsp;Nama Pasien</td>
              		<td width="45%" align="left" class="tablecontent-odd">&nbsp;<?php echo $_POST["cust_usr_nama"]." (".$_POST["cust_usr_kode"].")";?>
                  <input type="hidden" name="custTambah" id="custTambah"/>
                  <?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]); ?></td>
                  
                </tr>                                
                <tr>                                       
                  <td align="left" width="10%" class="tablecontent" rowspan="1">&nbsp;Resep Dokter&nbsp;</td>                                 
                  <td width="45%" class="tablecontent-odd" align="left">&nbsp;<?php echo $view->RenderTextArea("rawat_terapi","rawat_terapi","2","45",$_POST["rawat_terapi"],"inputField", "readonly",false);?> 
                  </td>                                                                                
                </tr>
                <tr>
                <td width= "15%" align="left" class="tablecontent">Ditanggung Oleh</td>
                <td width= "40%" align="left" class="tablecontent-odd">
                <input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                &nbsp;&nbsp;&nbsp;
                <input type="submit" name="btnOk" value="OK" class="submit" />
                </td>
          </tr>

                          <tr>
                          <td>    </td>
              		<td width="10%" align="left" class="tablecontent">&nbsp;Nama Dokter</td>
              		<td><select class="inputField" name="id_usr" id="id_usr" >
                  <option class="inputField" value="0" >[ Pilih Dokter ]</option>
                                   <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>"<?php if($dataDokter[$i]["usr_id"]==$_POST["id_usr"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>
                                    <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                                   <?php } ?>
                              </select> 
                              
                              
                                </td>
                </tr>                                

                          <tr>
                          <td>    </td>
              		<td width="10%" align="left" class="tablecontent">&nbsp;Poli</td>
              		<td><select class="inputField" name="poli" id="poli">
                  <option class="inputField" value="0" >[ Pilih Poli ]</option>
                                   <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["poli"]) echo "selected";?>><?php echo  $dataPoli[$i]["poli_nama"] ;?></option>
                                    <!--    <option class="inputField" value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected";?>><?php echo  $dataDokter[$i]["usr_name"] ;?></option>    -->

                                   <?php } ?>
                              </select> 
                              
                              
                                </td>
                </tr>                                


          <tr>
               <td class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
               <td width="75%" align="right">Dibayar : </td>
				       <td width="25%" align="left">
				       <?php echo $view->RenderHidden("Grandstotal","Grandstotal",currency_format($Grand),"curedit", "readonly",false);?>


               <?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtBalik"],"curedit", "",true,'onChange=GantiPengurangan(this.value)');?></td>   
				       </tr>
				       </table>
      				 </td>
               </tr>
          <tr>
               <td width= "50%" align="center" class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
      			   <td width="20%" align="left">&nbsp;</td>
               <td width="20%" align="left" valign="middle"><img src="<?php echo $ROOT;?>gambar/pointer.gif"/> <font color="#FF0000">Klik untuk isi nama obat</font></td>
				       <td width="30%" align="center">
               <input type="submit" name="btnRefresh" id="btnRefresh" value="Refresh" class="submit"/>
               <input type="submit" name="btnBayar" id="btnBayar" value="Proses" class="submit" onClick="javascript:return CekData();"/>     
				       <input type="button" name="simpan" id="simpan" value="Tunda Proses" class="submit" onClick="document.location.href='penjualan_view.php'";/>     
				       </td>
				       </tr>
				       </table>
          </td>
          </tr> 
                  <!--<tr>
              		<td class="tablecontent">&nbsp;Jenis Pasien</td>
                  <td colspan="2" class="tablecontent-odd">&nbsp;<?php //echo $bayarPasien[$_POST["cust_usr_jenis"]];?></td>
              	</tr>-->
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
                      <td width="2%" align="center"><?php echo '<a href="'.$thisPage.'?del=1&id='.$dataTable[$i]["penjualan_detail_id"].'&transe='.$penjualanId.'&kodenya='.$_POST["cust_usr_kode"].'&id_regnya='.$_POST["id_reg"].'&id_pembayaran='.$_POST["id_pembayaran"].'&id_dokter='.$_POST["id_usr"].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/b_drop.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>'; ?>
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
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
                      <?php echo $view->RenderTextBox("obat_kode","obat_kode","10","10",$_POST["obat_kode"],"inputField",false,false);?>                                        
                      <input type="hidden" name="obat_id" id="obat_id" value="<?php echo $_POST["obat_id"];?>" />   
                      <input type="hidden" name="id_batch" id="id_batch" value="<?php echo $_POST["id_batch"];?>" />   
                      <!--<img src="<?php //echo $ROOT;?>gambar/finder.png" border="0" align="middle" class="tombol" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" />--></a>
                      </td>                                                                                                                                  
                      <td align="left" width="20%" class="tablecontent-odd">
                      <a href="<?php echo $findPaket;?>&TB_iframe=true&height=400&width=600&modal=true&transaksi=<?php echo $penjualanId; ?>" class="thickbox" title="Pilih Paket">
                      <?php echo $view->RenderTextBox("paket_nama","paket_nama","30","100",$_POST["paket_nama"],"inputField", "",false);?></a></td>                                                                                                                                                         
                      <td align="left" width="20%" class="tablecontent-odd">
                      <a href="<?php echo $findPage1;?>&TB_iframe=true&height=400&width=500&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat">
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
                      <td align="center" width="2%" colspan="2"><input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CekTindakan(document.frmEdit);"></td>
                      <td align="right" width="2%" colspan="5">TOTAL YANG HARUS DIBAYAR&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4"><?php echo currency_format($grandtotal);?></td>
                      </tr>
                     
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="5">DISKON&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4">
                      <?php echo $view->RenderHidden("total","total",currency_format($grandtotal),"curedit", "readonly",false);?>
                      <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","15","15",currency_format($_POST["txtDiskon"]),"curedit", "",true,'onChange=GantiGrandHarga(this.value)');?>
                      </td>
                      </tr>
                      
                      <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="2">&nbsp;</td>
                      <td align="right" width="2%" colspan="5">BIAYA RESEP&nbsp;&nbsp;</td>
                      <td align="left" width="2%" colspan="4">
                      <?php echo $view->RenderTextBox("txtResep","txtResep","15","15",currency_format($_POST["txtResep"]),"curedit", "",true,'onChange=GantiResepHarga(this.value)');?>
                      </td>
                      </tr>
                      
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="5">BIAYA RACIKAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="4">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaRacikan","txtBiayaRacikan","15","15",currency_format($_POST["txtBiayaRacikan"]),"curedit", "",true,'onChange=GantiRacikanHarga(this.value)');?>                                        		                                                   
                      </tr>
                      <tr class="tablesmallheader">
                          <td align="center" width="2%" colspan="2">&nbsp;</td>                                    
                          <td align="right" width="2%" colspan="5">BIAYA PEMBULATAN&nbsp;&nbsp;</td>
                          <td align="left" width="2%" colspan="4">                                                                                                                               
                            <?php echo $view->RenderTextBox("txtBiayaPembulatan","txtBiayaPembulatan","15","15",currency_format($_POST["txtBiayaPembulatan"]),"curedit", "",true,'onChange=GantiPembulatanHarga(this.value)');?>                                        		                                                   
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
         </tr>            
        <tr>                       
          <td colspan="4" class="tablecontent">&nbsp;</td>                  
        </tr>              
        <!--<tr>                     
          <td colspan="4">					             
            <span id="div_menu">						               
              <?php //echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>					             
            </span>
            </td>		          
        </tr>-->
                      
        <tr>                   
          <td colspan="4">                       
            <!--<table width="100%" border="0" cellpadding="1" cellspacing="1">                                        
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Catatan</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                            
                  <?php echo $view->RenderTextArea("penjualan_catatan","penjualan_catatan","5","75",$_POST["penjualan_catatan"],"inputfield", "",true);?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr>                              
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Diskon</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                            
                  <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $_POST["total_harga"];?>" />                                            
                  <?php echo $view->RenderTextBox("txtDiskonPersen","txtDiskonPersen","10","30",$_POST["txtDiskonPersen"],"curedit", "",true,'onChange=Diskon(this.value,'.$totalHarga.')');?>  %                                          
                  <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","30","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon(this.value,'.$totalHarga.')');?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr>                              
              <!--<tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;PPN 10%</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                          
                  <?php //echo $view->RenderTextBox("txtPPN","txtPPN","30","30",currency_format($pajak),"curedit", "readonly",null,false);?>                     </td>                    <td>&nbsp;</td>				                               
              </tr>
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Biaya Resep</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                                                                                                                  
                  <?php //echo $view->RenderTextBox("txtBiayaResep","txtBiayaResep","30","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiBiayaResep(this.value)');?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr>
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Biaya Racikan</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                                                                                                                  
                  <?php //echo $view->RenderTextBox("txtBiayaRacikan","txtBiayaRacikan","30","30",$_POST["txtBiayaRacikan"],"curedit", "",true,'onChange=GantiBiayaRacikan(this.value)');?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr>
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;BHPS</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                                                                                                                  
                  <?php //echo $view->RenderTextBox("txtBiayaBhps","txtBiayaBhps","30","30",$_POST["txtBiayaBhps"],"curedit", "",true,'onChange=GantiBiayaBhps(this.value)');?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr> 
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Pembulatan</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                                                                                                                  
                  <?php echo $view->RenderTextBox("txtBiayaPembulatan","txtBiayaPembulatan","30","30",$_POST["txtBiayaPembulatan"],"curedit", "",true,'onChange=GantiBiayaPembulatan(this.value)');?>                                         </td>		                     <td>&nbsp; &nbsp; &nbsp;</td>			                                                   
              </tr> 
              <input type="hidden" name="txtPPN" id="txtPPN" value="0">
              <input type="hidden" name="txtBiayaResep" id="txtBiayaResep" value="0"> 
              <input type="hidden" name="txtBiayaRacikan" id="txtBiayaRacikan" value="0"> 
              <input type="hidden" name="txtBiayaBhps" id="txtBiayaBhps" value="0">                                               
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Total yg harus dibayar</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                          
                  <input type="hidden" name="txtTotalHarga" id="txtTotalHarga" value="<? echo $totalHarga?>" />                                          
                  <?php echo $view->RenderTextBox("txtTotalDibayar","txtTotalDibayar","30","30",currency_format($grandTotalHarga),"curedit", "readonly",null,false);?>                     </td>					                <td>&nbsp;</td>        	                 
              </tr>                              
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Dibayar</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                          
                  <?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"],"curedit", "",true,'onChange=GantiKembalian(this.value)');?>                     </td>					                               
              </tr>                              
              <tr>                                     
                <td align="left" width="20%" class="tablecontent">&nbsp;Kembalian</td>                                     
                <td align="left" width="15%" class="tablecontent-odd">                                            
                  <?php echo $view->RenderTextBox("txtKembalian","txtKembalian","30","30",$_POST["txtHargaTotal"],"curedit", "readonly",null,true);?>                     </td>					                               
              </tr>                                                        
              <tr>                                     
                <td colspan="4" align="left" class="tblCol">                                                                         
                  <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="submit" onClick="javascript:return CekData();"/>                                            
                  <input type="button" name="btnBack2" value="Kembali" class="submit" onClick="document.location.href='<?php echo $sellPage;?>'">                    </td>                              
              </tr>                       
            </table> -->
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
      <input type="hidden" name="id_pembayaran" value="<? echo $_POST["id_pembayaran"]; ?>">
      <input type="hidden" name="reg_tanggal" value="<? echo $_POST["reg_tanggal"]; ?>">
      <input type="hidden" name="id_poli" value="<? echo $_POST["id_poli"]; ?>">
    <!--  <input type="hidden" name="poli" value="<? echo $_POST["poli"]; ?>"> -->
      <input type="hidden" name="id_cust_usr" value="<? echo $_POST["cust_usr_id"]; ?>">
      <input type="hidden" name="id_dokter" value="<? if($_POST["id_dokter"]){echo $_POST["id_dokter"];}else{$_GET["id_dokter"];} ?>">
      <input type="hidden" name="dep_konf_loket_antrian_poli" value="<?php echo $konf["dep_konf_loket_antrian_poli"]; ?>"/>  
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
   <?php// //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
  <?php //echo $view->RenderBodyEnd(); ?>
