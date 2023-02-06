<?php   
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");                                                             
                                           
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $plx = new expAJAX("GetBiaya");
     
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
     $_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
     $_POST["dep_posting_split"] = $konfigurasi["dep_posting_split"]; 
     $_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
     $_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
     $_POST["dep_posting_beban"] = $konfigurasi["dep_posting_beban"];
     $_POST["dep_cetak_rincian"] = $konfigurasi["dep_cetak_rincian"];
     
    if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) && !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     } 

     $_x_mode = "New";
     $thisPage = "kasir_pemeriksaan_view.php";
     $delPage = "kasir_pemeriksaan_proses.php?";

     $table = new InoTable("table","100%","left");

     
     if ($_GET["id_dokter"]) $_POST["id_dokter"]=$_GET["id_dokter"];
     if ($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
     if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"]=$_GET["reg_jenis_pasien"];
     
     //FUNGSI AJAX
     function GetBiaya($katHeaderId,$id_layanan,$id_shift,$biayaId=null)
	   {
       global $dtaccess,$view,$id_biaya,$depId;    
     	 $sql = "select a.*,b.kategori_tindakan_nama from klinik.klinik_biaya a
        left join klinik.klinik_kategori_tindakan b on a.biaya_kategori = b.kategori_tindakan_id
        left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id = b. id_kategori_tindakan_header
        where a.biaya_jenis = 'TA' and 
        a.id_shift =".QuoteValue(DPE_CHAR,$id_shift)." and  
        a.id_tipe_biaya =".QuoteValue(DPE_CHAR,$id_layanan)." and  
        a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and 
        b.id_kategori_tindakan_header =".QuoteValue(DPE_CHAR,$katHeaderId)."     
        order by a.biaya_nama asc";
     //return $sql;   
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataBiaya = $dtaccess->FetchAll($rs_edit);
  			unset($biaya);
  			$biaya[0] = $view->RenderOption("--","[Pilih Biaya]",$show);
  			$i = 1;
			
         for($i=0,$n=count($dataBiaya);$i<$n;$i++)
         {   
             if($biayaId==$dataBiaya[$i]["biaya_id"]) $show = "selected";
             $labelBiaya=substr($dataBiaya[$i]["biaya_nama"], 0, 35)."(".substr($dataBiaya[$i]["kategori_tindakan_nama"], 0, 35).") Rp. ".currency_format($dataBiaya[$i]["biaya_total"]);
             $biaya[$i+1] = $view->RenderOption($dataBiaya[$i]["biaya_id"]."-".$dataBiaya[$i]["biaya_total"],$labelBiaya,$show);
             unset($show);
         }
  			$str = $view->RenderComboBox("id_biaya","id_biaya",$biaya,null,null,null)."&nbsp;Jumlah : ".
        $view->RenderTextBox("fol_jumlah","fol_jumlah","5","5","1","inputField", null,false);                       
  	 return $str;
    }

	
	if($_GET["id_reg"] || $_GET["pembayaran_id"]) 
  {
		$sql = "select a.reg_jenis_pasien, a.reg_tipe_jkn, a.id_poli, a.id_dokter, a.id_cust_usr, a.id_perusahaan,
            a.id_jamkesda_kota, a.reg_tipe_layanan, a.id_poli, a.reg_tipe_paket, 
            a.reg_tipe_layanan, a.reg_shift, b.pembayaran_dijamin,  
            c.cust_usr_alamat, c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_jenis_kelamin, 
            c.cust_usr_foto,  ((current_date - c.cust_usr_tanggal_lahir)/365) as umur, c.cust_usr_jkn,   
            d.fol_keterangan, e.perusahaan_diskon, e.perusahaan_plafon, f.* from  
            klinik.klinik_registrasi a 
            left join klinik.klinik_pembayaran b on b.pembayaran_id = a.id_pembayaran 
            join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
            left join klinik.klinik_folio d on d.id_reg=a.reg_id
            left join global.global_perusahaan e on e.perusahaan_id=a.id_perusahaan
            left join global.global_jamkesda_kota f on f.jamkesda_kota_id=a.id_jamkesda_kota
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId);
   $rs_pasien = $dtaccess->Execute($sql);
   $dataPasien= $dtaccess->Fetch($sql);
    
    $_POST['fol_id'] = $_GET["fol_id"];		
    $_POST["id_reg"] = $_GET["id_reg"];  
		$_POST["id_biaya"] = $_GET["biaya"]; 
    $_POST["pembayaran_id"] = $_GET["pembayaran_id"];

		$view->CreatePost($dataPasien);
		$lokasi = $ROOT."gambar/foto_pasien";
	}


     //AMBIL GRAND TOTAL
     $sql = "select * from  klinik.klinik_folio a left join global.global_auth_user b on a.id_dokter = b.usr_id
             left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya
			       where fol_lunas='n' and id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by fol_waktu asc"; 
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);
    for($i=0,$n=count($dataTable);$i<$n;$i++)
    {
 
              $total = $dataTable[$i]["fol_hrs_bayar"];
              $totalBiaya = $totalBiaya+$dataTable[$i]["fol_nominal"];
              $dijamin = $dataTable[$i]["fol_dijamin"];
              //Jika Paket
              if($dataTable[$i]["biaya_paket"]=="n")
              {
              $totalNonPaket += $dataTable[$i]["fol_nominal"];
              }
          //}
          $totalHarga+=$total;
          $minHarga = 0-$totalHarga;
          $totalDijamin+=$dijamin;
          //$grandTotalHarga = $totalHarga;
   } 

   //-- RUMUS PEMBULATAN dan Penambahan Uang Muka
    require_once('pembayaran_total_harga.php');
   //tampilan atas yang merah
   $grandTotalHarga = $totalHarga-$uangmuka["total"];   
   //echo "total ".$totalHarga."-".$inacbg["inacbg_topup"];
   
   if($uangmuka["total"]>0)
   {
   $retur = $uangmuka["total"] - $totalHarga;
   if($retur<0) $retur=0;
   } 	 


    
    if ($_POST["btnOk"])  //Jika klik tombol ganti data diatas
    {
      $sql = "update  klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $dtaccess->Execute($sql,DB_SCHEMA_KLINIK); 
      
      $kembali = "kasir_pemeriksaan_proses.php?id_dokter=".$_POST["id_dokter"]."&reg_jenis_pasien=".$_POST["reg_jenis_pasien"]."
                  &id_poli=".$_POST["id_poli"]."&id_reg=".$_POST["id_reg"]."&pembayaran_id=".$dataBayar["pembayaran_id"];
      header("location:".$kembali);
      exit();    
    }
	
	
	// Jika Klik tombol Bayar //
	if ($_POST["btnBayar"]) 
  {	
    
    $sql = "select * from klinik.klinik_pembayaran where 
            id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
            and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $dataReg = $dtaccess->Fetch($sql);

   if($_POST["fol_keterangan"])
   {
    $sql = "update klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST["fol_keterangan"])." 
            where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   }

   //---- AWAL UPDATE REG_TANGGAL PADA KLINIK REGISTRASI  
   $sql = "select reg_utama from klinik.klinik_registrasi where 
          (id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." or reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]).") 
          and reg_utama is not null";
   $reg = $dtaccess->Execute($sql);
   $regUtama= $dtaccess->FetchAll($reg);
   
   $sql = "select reg_id from klinik.klinik_registrasi where reg_utama = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
   $rs = $dtaccess->Execute($sql);
   $allReg = $dtaccess->FetchAll($rs);
   
    for($i=0,$n=count($allReg);$i<$n;$i++)
    {
  		$sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status='E1',
              reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$allReg[$i]["reg_id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
    }
     
    //Update yg reg_utama
		$sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status_kondisi='U', reg_status='E0',
            reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);  
    $dtaccess->Execute($sql);
    //--- AKHIR UPDATE REG_TANGGAL PADA KLINIK REGISTRASI 

    // cari dokter e //
    $sql = "select usr_name from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    $rs = $dtaccess->Execute($sql);
    $Doktere = $dtaccess->Fetch($rs);    
    
    //Update klinik pembayaran kassa
    require_once('update_klinik_pembayaran_kassa.php');
    
    
    //---UPDATE KLINIK PEMBAYARAN UANG MUKA
    $sql = "update klinik.klinik_pembayaran_uangmuka set uangmuka_tgl_lunas=".QuoteValue(DPE_DATE,date("Y-m-d"))." 
            where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $dtaccess->Execute($sql);
    //- AKHIR PEMBAYARAN UANG MUKA
    
    
    //-- INSERT PEMBAYARAN DET
    require_once('insert_pembayaran_det_kassa.php');
   //-- AKHIR INSERT PEMBAYARAN DET
   
    $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $uangMuka = $dtaccess->Fetch($rs);
    
    $bayar = $dataPembayaran["pembayaran_yg_dibayar"] - $uangMuka["total"];
  
          //insert pelunasan uangmuka
          if($uangMuka){
            $dbTable = "klinik.klinik_pembayaran_uangmuka";
            $dbField[0] = "uangmuka_id";
            $dbField[1] = "id_reg";
            $dbField[2] = "id_pembayaran";
            $dbField[3] = "uangmuka_jml";
            $dbField[4] = "uangmuka_tgl";
            $dbField[5] = "id_jbayar";
            $dbField[6] = "who_update";
            $dbField[7] = "uangmuka_tgl_lunas";
            $dbField[8] = "id_pembayaran_det";
            
            $uangmukaId = $dtaccess->GetTransID();
            $dbValue[0] = QuoteValue(DPE_CHAR,$uangmukaId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
            $dbValue[3] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($uangMuka["total"]));
            $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
            $dbValue[6] = QuoteValue(DPE_CHAR,$userName);
            $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
            
            $dbKey[0] = 0;
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                     
            $dtmodel->Insert() or die("insert  error");
             
            unset($dbField);
            unset($dtmodel);
            unset($dbValue);
            unset($dbKey);
          }
  //}                 
    
    //UPDATE             
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal "; 
    $sql .= " , fol_diskon_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"])); 
    //$sql .= " , fol_pembulatan_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"])); 
    $sql .= " , fol_diskon_persen_penjualan = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"]));
    $sql .= " , fol_total_harga = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));
    $sql .= " , id_pembayaran_det = ".QuoteValue(DPE_CHAR,$byrHonorId);
    //$sql .= " , id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    //$sql .= " , who_when_update = ".QuoteValue(DPE_CHAR,$userId);
    $sql .= " , fol_dibayar_when = CURRENT_TIMESTAMP where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
            and id_dep=".QuoteValue(DPE_CHAR,$depId)." and fol_lunas='n'";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    // --- AKHIR UPDATE KLINIK FOLIO fol_dibayar=fol_nominal 
    
     $sql = "select fol_diskon_persen_penjualan,fol_pembulatan_penjualan,fol_diskon_penjualan,fol_total_harga from  klinik.klinik_folio
			       where (fol_jenis like '%T%' or fol_jenis like '%WA%' or fol_jenis like '%R%') 
             and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' and id_dep = ".QuoteValue(DPE_CHAR,$depId); 
		 $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataLaba = $dtaccess->Fetch($rs);
     
     $_POST["diskon"] = $dataLaba["fol_diskon_penjualan"];
     $_POST["diskonpersen"] = $dataLaba["fol_diskon_persen_penjualan"];
     $_POST["total"] = $dataLaba["fol_total_harga"];
     $_POST["pembulatan"] = $dataLaba["fol_pembulatan_penjualan"];  
     
      $sql = "select dep_konf_cetak_kasir from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_cetak_kasir"] = $row_edit["dep_konf_cetak_kasir"]; 
      
      $sql="select * from klinik.klinik_folio a
            join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
            where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." and fol_lunas='n'";
		 $rs = $dtaccess->Execute($sql);
     $dataFolioPas = $dtaccess->FetchAll($rs);
     
     $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
     $total = $dtaccess->Fetch($sql);
     
     // cari isi pembayaran
     $sql="select * from klinik.klinik_pembayaran a
          where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPembayaranPas = $dtaccess->Fetch($rs);

     $sql="select * from klinik.klinik_registrasi a
          left join global.global_customer_user b on a.id_cust_usr= b.cust_usr_id
          where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPas = $dtaccess->Fetch($rs);
     
     if($dataPas["reg_jenis_pasien"]==TIPE_PASIEN_JKN && !$dataPas["reg_tipe_jkn"]){
     $sql="select reg_tipe_jkn from klinik.klinik_registrasi
          where id_cust_usr = ".QuoteValue(DPE_CHAR,$dataPas["id_cust_usr"])." and id_dep=".QuoteValue(DPE_CHAR,$depId)." 
          and reg_tipe_jkn is not null order by reg_tipe_jkn desc";
     $rs = $dtaccess->Execute($sql);
     $dataRegJkn = $dtaccess->FetchAll($rs);

     $sql="update klinik.klinik_registrasi set reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$dataRegJkn["reg_tipe_jkn"])."
          where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
		 $rs = $dtaccess->Execute($sql);
     }
     
     //POSTING ke GL
     
     
     if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]=="1") $dataPas["reg_tipe_layanan"]="2";
     if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]<>"1") $dataPas["reg_tipe_layanan"]="1";
          
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'PENDPOST-%' 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Pendapatan Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Pendapatan a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Pendapatan a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'PENDPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'PE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrHonorId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
      
      //--GL POSTING UANG MUKA        
    require_once('gl_uang_muka.php');
    //-- akhir posting UM    
 
       //--GL POSTING DISKON       
    require_once('gl_posting_diskon.php');
    //-- akhir posting DISKON

       //--GL POSTING PEMBULATAN       
    require_once('gl_posting_pembulatan.php');
    //-- akhir posting PEMBULATAN

       //--GL POSTING SERVICE CHARGE       
    require_once('gl_posting_service_cash.php');
    //-- akhir posting SERVICE CHARGE

       //--GL POSTING PASIEN UMUM       
    require_once('gl_posting_umum.php');
    //-- akhir posting UMUM
             
       //--GL POSTING PASIEN JKN       
    require_once('gl_posting_jkn.php');
    //-- akhir posting JKN

 // Piutang JAMKESDA
    require_once('gl_posting_jamkesda.php'); 

 // Piutang IKS
    require_once('gl_posting_iks.php'); 
 
 // Piutang PKMS SILVER
     require_once('gl_posting_pkms.php');

       //--GL POSTING PASIEN JASARAHARAJA        
    require_once('gl_posting_jasa_raharja.php');
    //-- akhir posting JASARAHARAJA

 // Piutang JKN+JASARAHARAJA
    require_once('gl_posting_jkn_jasaraharja.php'); 
//-- Akhir JKN+JASARAHARAJA

    // Piutang Fasilitas
    require_once('gl_posting_fasilitas.php'); 
    //-- Akhir Fasilitas
    
    // Piutang JKN+Fasilitas
    require_once('gl_posting_jkn_fasilitas.php'); 
//-- Akhir JKN+Fasilitas

     for($m=0,$n=count($dataFolioPas);$m<$n;$m++){
// Pendapatan IRJ
     require('gl_pendapatan_irj.php');
             
     } 
     
     if($_POST["dep_posting_beban"]=='y'){
//POsting Biaya
     //POSTING ke GL
     
//cari yang split-nya ada angkanya
      $sql = "select a.folsplit_nominal from klinik.klinik_folio_split a
             left join klinik.klinik_folio b on a.id_fol = b.fol_id
             left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
             where c.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and
             a.folsplit_nominal > '0'";
      $rs = $dtaccess->Execute($sql);
      $postbeban = $dtaccess->Fetch($rs);            
     
     
     if ($postbeban["folsplit_nominal"]) {
           
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
            
      if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'BEBANPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'BE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 

     //masukkan biaya bebannya
     for($m=0,$n=count($dataFolioPas);$m<$n;$m++){
      // Pendapatan IRJ
      require('gl_posting_split.php');
      //--GL POSTING PASIEN UMUM       
      require('gl_posting_beban_umum.php');
             
      }      
    }
  }
          $cetak = "y";
  }

     
       // buat ambil tindakan --
     	 $sql = "select * from klinik.klinik_biaya where biaya_jenis = 'TA' and 
               id_dep =".QuoteValue(DPE_CHAR,$depId)." order by biaya_nama";
		   $datatindakan= $dtaccess->FetchAll($sql);
       
       // buat nyari user Dokter ee --
     	 $sql = "select id_pgw from klinik.klinik_honor_dokter where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and honor_flag = 'RS' and id_dep =".QuoteValue(DPE_CHAR,$depId);
		   $dataUserDok = $dtaccess->Fetch($sql); 
       
       $sql = "select id_poli from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		   $dataPoliReg = $dtaccess->Fetch($sql); 
		   
		   // buat ambil jenis bayar --
     	 $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' and jbayar_id = '01' order by jbayar_id asc";
		   $dataJenisBayar= $dtaccess->FetchAll($sql); 
          //echo $sql;
       $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
		   $dataJenisBayar2= $dtaccess->FetchAll($sql);              
        
       // cari jenis bayar ee //
       $sql = "select count(jbayar_id) as total from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_lowest<>'n' or jbayar_id = '01'";
		   $jsBayar= $dtaccess->Fetch($sql);
       
       $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_reg"] = $row_edit["dep_konf_reg"];
      $_POST["dep_konf_kons"] = $row_edit["dep_konf_kons"];  
		   
		  /*if($_POST["dep_konf_kons"]=='y' && $_POST["dep_konf_reg"]=='y'){ 
        $sql = "select * from  klinik.klinik_kategori_tindakan where id_dep=".QuoteValue(DPE_CHAR,$depId)." order by kategori_urut"; 
  		 $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
       $dataKategori = $dtaccess->FetchAll($rs_edit);    
      }else{
        $sql = "select * from  klinik.klinik_kategori_tindakan where id_dep=".QuoteValue(DPE_CHAR,$depId)." and kategori_tindakan_id>'1' order by kategori_urut"; 
  		 $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
       $dataKategori = $dtaccess->FetchAll($rs_edit);  
      }*/                      
     
     //default dari setting mana yang default untuk klinik kategori tindakan header per poli
      $sql = "select id_kategori_tindakan_header from klinik.klinik_biaya_poli
             where biaya_poli_default='y' and id_poli =".QuoteValue(DPE_CHAR,$_POST["id_poli"]).
             " and id_tahun_tarif=".QuoteValue(DPE_CHAR,$tahunTarif); 
		  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataKategoriDefault = $dtaccess->Fetch($rs_edit);
      $_POST["id_kat_header_default"] = $dataKategoriDefault["id_kategori_tindakan_header"]; 
     
      //combo kategori tindakan header
      $sql = "select a.* from klinik.klinik_kategori_tindakan_header a 
             left join klinik.klinik_biaya_poli c on a.kategori_tindakan_header_id=c.id_kategori_tindakan_header
             where c.id_poli =".QuoteValue(DPE_CHAR,$_POST["id_poli"])." and a.id_tahun_tarif=".QuoteValue(DPE_CHAR,$tahunTarif)."
             order by a.kategori_tindakan_header_nama"; 
		  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataKategori = $dtaccess->FetchAll($rs_edit);                                          
      $katTindakanHeader[0] = $view->RenderOption("--","[Pilih Kategori]",$show);
      //echo $sql;
      for($i=0,$n=count($dataKategori);$i<$n;$i++)
      {
        unset($show);
        if($_POST["id_kat_header_default"]==$dataKategori[$i]["kategori_tindakan_header_id"]) $show = "selected";
        $katTindakanHeader[$i+1] = $view->RenderOption($dataKategori[$i]["kategori_tindakan_header_id"],$dataKategori[$i]["kategori_tindakan_header_nama"],$show);               
      } 
      
     $sql = "select * from global.global_auth_user where (id_rol = '6' or id_rol='2') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_auth_poli where id_dep =".QuoteValue(DPE_CHAR,$depId)." order by poli_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTipeLayanan = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJenis = $dtaccess->FetchAll($rs);       

     $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKota = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_perusahaan order by perusahaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPerusahaan = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from global.global_jkn order by jkn_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJkn = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_detail_paket order by detail_paket_nama";
     $rs = $dtaccess->Execute($sql);
     $dataPaket = $dtaccess->Fetch($rs);
     
     
     // data Order Poli
     $sql = "select reg_id,poli_nama,c.usr_name,d.usr_name as dokter_sender,reg_who_update
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id
            left join global.global_auth_user c on a.id_dokter = c.usr_id
            left join global.global_auth_user d on a.reg_dokter_sender = d.usr_id
            where a.id_dep =".QuoteValue(DPE_CHAR,$depId)." and a.id_pembayaran =".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
     $sql .= " order by reg_tanggal, reg_waktu asc";
 		 $dataorderPoli= $dtaccess->FetchAll($sql);
     

     
                      
?>
                                                                                                                                     
 
<script type="text/javascript" src="ajax.js"></script>
<script src="<?php echo $ROOT;?>lib/script/selecttindakan1.js"></script>
<script type="text/javascript">

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan membatalkan tindakan ini???'));
  else return false;
}

function CekTindakan(frm) {

    if(!frm.id_tindakan_0.value){
		alert('Pilih dahulu Tindakan yang akan dimasukkan');
		frm.id_tindakan_0.focus();
          return false;
	}

     	return true;      
}


function CekData()
{                              
//    if(!document.getElementById('txtcek').value || document.getElementById('txtcek').value =='0')
//    {
//      alert('Belum dibayar');
//      document.getElementById('txtcek').focus();
//      return false;
//    }    

/*
    if(document.getElementById('id_dokter').value == '--')
    {
      alert('Maaf Dokter Harus Dipilih');
      document.getElementById('id_dokter').focus();
      return false;
    }  */
         
    <?php //if($_POST["reg_jenis_pasien"]=="5"){ ?>
     /* if(document.getElementById('txtBack').value > '0')
      {
        alert('Maaf uang anda kurang');
        document.getElementById('txtBack').focus();
        return false;
      }*/
    <?php //} ?>
    
    if(document.getElementById('reg_jenis_pasien').value=='5'){ 
      if(document.getElementById('cust_usr_jkn').value=='--')
      {
        alert('Maaf Tipe JKN harus diisi, silahkan hubungi loket untuk memperbaikinya');
        document.getElementById('cust_usr_jkn').focus();
        return false;
      }
    }
    
    /*if(document.getElementById('reg_jenis_pasien').value=='5'){ 
      <?php if($_POST["inacbg_appv"]=='n' || $_POST["inacbg_appv"]=='') {?>
        alert('Maaf hasil bridging belum di approve');
        document.getElementById('reg_jenis_pasien').focus();
        return false;
      <?php } ?>
    }*/
    
    return true;
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


function GantiPengurangan(terima,urut) {
     var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtDibayar'+urut).value.toString().replace(/\,/g,"");
     var byr_urt = document.getElementById('byr'+urut+'_int').value.toString().replace(/\,/g,"");
     var kembalian;
     //var aslinya = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     dibayar_int=bayaren*1;  //total tagihan
     terima_int=terima*1;
     kembalian = dibayar_int-terima_int;
     //alert(formatCurrency(kembalian));
     document.getElementById('txtIsi').innerHTML = formatCurrency(kembalian);
     document.getElementById('btnBayar').focus();
 
}



var grandTotal = '<?php echo $grandTotalHarga;?>';
var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
//     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];

<?php if($cetak=="y"){ ?>
//    if(confirm('Cetak Invoice?'))
       <?php if((($_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='26') and ($_POST["reg_tipe_jkn"]=='1' or $_POST["reg_tipe_layanan"]=='1') && $_POST["id_poli"]<>$_POST["op"]) or $_POST["reg_jenis_pasien"]=='7' or $_POST["reg_jenis_pasien"]=='18'){ ?>
       BukaWindow('kasir_dot_cetak_sementara.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
       document.location.href='<?php echo $thisPage;?>';
       <?php } else if($_POST["id_poli"]==$_POST["op"]){ ?>
       BukaWindow('kasir_pemeriksaan_dot_kurang_cetak.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
       document.location.href='<?php echo $thisPage;?>';
       <?php } else { ?>
       <?php if($_POST["dep_cetak_rincian"]=='y'){ ?>
       BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $byrHonorId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } else { ?>
       BukaWindow('cetak_kwitansi.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total_harga"];?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]);?>&pembayaran_det_id=<?php echo $byrHonorId;?>&uangmuka_id=<?php echo $uangmukaId;?>','Kwitansi');
       <?php } ?> 
       //BukaWindow('kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>&pembayaran_det_id=<?php echo $byrHonorId;?>','Kwitansi');
	     document.location.href='<?php echo $thisPage;?>';
       <?php } ?>
   
<?php } ?>



</script>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>


<body  onLoad="GantiPembulatan('<?php echo $_POST["txtBiayaPembulatan"];?>','<?php echo $grandTotalHarga;?>')"; >

<div id="body">
<div id="scroller">


<form name="frmEdit" method="POST" autocomplete="off" action="<?php echo $_SERVER["PHP_SELF"]?>" >
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td width="100%">
     <fieldset>                                                     
     <legend><strong>Data Pasien</strong></legend>
      <div id="kasir">                                                                      
      <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               <td width= "15%" align="left" class="tablecontent">No. RM</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
               <td width= "40%" align="center" class="tablecontent" rowspan="2"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
          </tr>
          <?php if($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500') { ?>	
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo $dataPasien["fol_keterangan"]; ?></label></td>
          </tr>
          <?php } else { ?>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php if($dataPasien["umur"]) echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["umur"]." Tahun"; else echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "15%" align="left" class="tablecontent">Alamat</td>
               <td width= "40%" align="left" class="tablecontent-odd" colspan="2"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <?php } ?>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Sudah Terima Dari</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                <input type="text" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                &nbsp;&nbsp;&nbsp;
                </td>
          </tr>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Cara Bayar</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="reg_jenis_pasien" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Cara Bayar ]</option>			
				              <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
				            <?php } ?>
			            </select>
                </td>
          </tr>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Tipe Layanan</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="reg_tipe_layanan" disabled id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Tipe Layanan ]</option>			
				              <?php for($i=0,$n=count($dataTipeLayanan);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataTipeLayanan[$i]["tipe_biaya_id"];?>" <?php if($_POST["reg_tipe_layanan"]==$dataTipeLayanan[$i]["tipe_biaya_id"]) echo "selected"; ?>><?php echo $dataTipeLayanan[$i]["tipe_biaya_nama"];?></option>
				            <?php } ?>
			            </select>&nbsp;<input type="submit" name="btnOk" value="Ganti Data" class="submit" />
                </td>
                <td width= "40%" align="center" class="tablecontent">&nbsp;</td>
          </tr>
          <tr>
                <td width= "15%" align="left" class="tablecontent">Klinik</td>
                <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
                 <select readonly name="id_poli" disabled id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                   <option value="--">[ Pilih Klinik ]</option>			
				              <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
         	         <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
				            <?php } ?>
          <tr>
           <td width= "15%" align="left" class="tablecontent">Jenis Bayar </td>         
           <td width= "40%" align="left" colspan="2" class="tablecontent-odd">
              <select name="id_jbayar"  id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">		
       				<?php if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php } ?>
           				<?php $counter = -1;
           for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++){
               unset($spacer); 
					$length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";  
        				?>                                                                      
         	  <option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"];?>" <?php if($_POST["id_jbayar"]==$dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"];?></option>
				    <?php } ?>
			    </select>
           </td>
           <td width= "40%" align="center" class="tablecontent">&nbsp;</td>
          </tr>
               
				 <?php for($i=0,$n=count($dataJenisBayar);$i<$n;$i++) { 
               unset($spacer); 
		
    		$length = (strlen($dataJenisBayar[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
    		for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;"; 
        		
         //$_POST["txtDibayar"][$i] = '0';?>
          <?
          $ttotal = currency_format($grandTotalHarga);
          ?>
          <tr>
           <td class="tablecontent" align="center">&nbsp;</td>         
           <td width= "40%" align="right" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php echo $spacer;?>&nbsp;<b>Total Pembayaran<?php //echo strtoupper($dataJenisBayar[$i]["jbayar_nama"]);?></b> </td>
           <td class="tablecontent" colspan="4">&nbsp;&nbsp;
           <? if($_POST["reg_jenis_pasien"]=="5" || $_POST["reg_jenis_pasien"]=="26"){
              $showTotal = $ttotal;
           } ?>
               <?php if($dataJenisBayar[$i]["jbayar_id"]=='01' || $dataJenisBayar[$i]["jbayar_lowest"]!='n') echo 
               $view->RenderTextBox("txtDibayar[$i]","txtDibayar$i","30","30",$showTotal,"curedit", "",true,'onChange=GantiPengurangan(this.value,'.$i.');');
                     else echo ""; ?></td>
           <input type="hidden" name="byr<?php echo $i;?>_int" id="byr<?php echo $i;?>_int" value="<?php if($_POST["jsByr"][$i]) echo $_POST["jsByr"][$i]; else echo '0';?>" />
           <input type="hidden" name="js_id[<?php echo $i;?>]" id="js_id_<?php echo $i;?>" value="<?php echo $dataJenisBayar[$i]["jbayar_id"] ;?>" />
          </tr>           
         <?php } ?>
          <tr>
               <td width= "50%" align="center" class="tablecontent" colspan="5">
               <table width="100%" border="0">
               <tr>
               <td width="50%" align="left">&nbsp;</td>
				       <td width="50%" align="center">
               <?php if($dataTable){ ?>
               <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="submit" onClick="javascript:return CekData();"/>     
               <?php } ?>
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
				       </td>
				       </tr>
				       </table>
          </td>
          </tr>
           
	   </table>
	        </div>
     </fieldset>

     <fieldset>
     <legend><strong>Data Order</strong></legend>
     <div id="kasir">
     
     
     
     
     
     
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr class="tablesmallheader">
            <td width="1%" align='center'>No</td>
            <td width="15%" align='center'>Poli</td>
            <td width="15%" align='center'>Dokter Pengirim</td>
            <td width="15%" align='center'>Dokter Tujuan</td>
            <td width="3%" align='center'>Telah Dilayani</td>                            
        </tr>
      	<?php for($i=0,$n=count($dataorderPoli);$i<$n;$i++) { 
              $sql = "select fol_id from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$dataorderPoli[$i]["reg_id"]);
              $datalayani = $dtaccess->Fetch($sql); ?>                                                                      
        <tr class="tablecontent-odd">
            <td width="1%" align='center'><?php echo ($i+1)."."; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["poli_nama"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["dokter_sender"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["usr_name"]; ?></td>
            <?php if(!$datalayani) {?>
            <td width="3%" align='center'>&nbsp;</td>                            
            <?php }else{ ?>
            <td width="3%" align='center'><img hspace="2" width="20" height="20" src="<?php echo $ROOT.'gambar/aktif.png';?>" /></td>
            <?php } ?> 
        </tr>
        <?php } ?>  
     </table>
     </div>
     </fieldset>
     

     <fieldset>
     <legend><strong>Data Pembayaran</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
              <tr class="tablesmallheader">
              <td width="3%" align='center'>No</td>
              <td width="32%" align='center'>Layanan</td>
			        <td width="17%" align='center'>Nama Dokter</td>
              <td width="10%" align='center'>Biaya</td>
              <td width="5%" align='center'>Quantity</td>
              <td width="10%" align='center'>Tagihan</td>
						  </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
						  
                        <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
                                || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
                                || $dataTable[$i]["fol_jenis"]=='I'){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                                $rs = $dtaccess->Execute($sql); 
                                $dataFarmasidetail  = $dtaccess->FetchAll($rs); 
                                 }         
                                
                         if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA' 
                              || $dataTable[$i]["fol_jenis"]=='RG' || $dataTable[$i]["fol_jenis"]=='RI' ){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql);
                                $dataReturdetail  = $dtaccess->FetchAll($rs);     }      ?> 
                                	
              <tr class="tablecontent-odd">
                <td width="3%"><?php echo ($i+1).".";?></td>
                <td width="32%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
                </td>
				        <td width="17%"><?php echo $dataTable[$i]["usr_name"];?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
                <td width="5%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>
						  </tr>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I' || $dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){  ?>

       <tr class="garis_atas garis_bawah"> 
<?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I') {
                              $sql = "select count(penjualan_detail_id) as total
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                              $rs = $dtaccess->Execute($sql);
                              $totalitem = $dtaccess->Fetch($rs);        
            }
            if($dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){
                               $sql = "select count(retur_penjualan_detail_id) as total
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql); 
            } ?>         
         <td align="left" rowspan="<?php echo $totalitem["total"]+1;?>" ></td>
        <td align="left">Nama Item/Obat</td>
        <td align="right">Harga Satuan</td>
        <td align="right">Quantity</td>                             
        <td align="right">Total</td>
        <td align="right"></td>
	    </tr>     

    <?php } ?>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I'){  ?>
    
    <?php for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) {?>
       <tr>

          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>
        <td align="right"></td>
       </tr>     
       <?php } ?>                 
  
       <?php } ?>
           <?php if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA'
           || $dataTable[$i]["fol_jenis"]=='RI' ||$dataTable[$i]["fol_jenis"]=='RG'){ ?>                        
    <?php for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr class="garis_atas garis_bawah">
          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php } ?>                 
              
           <? } ?>        
     </tr>
     <?php 

          $totalPembayaran += $dataTable[$i]["fol_nominal"]; 
          $totalHarga=$totalBiaya-$dijaminHarga;
          if ($totalHarga<0) $totalHarga=0;
     
     ?>						  
						  <?php } ?>
              
              <tr>
                  <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Tagihan</b></td>              
                  <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($totalBiaya)."</b>";?></td>
						  </tr>
 	</table>
	</div>
     </fieldset>

    	      <input type="hidden" name="total_harga" id="total_harga" value="<?php echo $grandTotalHarga;?>" />
              <input type="hidden" name="total_dijamin" id="total_dijamin" value="<?php echo $totalDijamin;?>" />
              <input type="hidden" name="total_biaya" id="total_biaya" value="<?php echo $totalBiaya;?>" /> 
              <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
              <input type="hidden" name="txtDiskon" id="txtDiskon" value="0"> 
              <input type="hidden" name="txtcek" id="txtcek" value="<?php echo $_POST["txtcek"]; ?>">
              <input type="hidden" name="txtTotalDibayar" id="txtTotalDibayar" value="<?php echo $totalHarga?>">
              <input type="hidden" name="txtKembalian" id="txtKembalian" value="<?php echo $_POST["txtHargaTotal"]; ?>">
              <input type="hidden" name="pembayaran_id" id="pembayaran_id" value="<?php echo $_POST["pembayaran_id"]; ?>">
              <input type="hidden" name="bayar" id="bayar" value="<?php echo $grandTotalHarga;?>" />
		</tr>
	</table>

<script>document.frmEdit.txtDibayar0.focus();</script>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>
<input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
<input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>"/>
<input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>"/>
<input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>"/>
<input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"]; ?>"/>
<input type="hidden" name="reg_tipe_layanan" value="<?php echo $_POST["reg_tipe_layanan"]; ?>"/>
<input type="hidden" name="id_poli" value="<?php echo $_POST["id_poli"]; ?>"/>
<input type="hidden" name="id_dokter" value="<?php echo $_POST["id_dokter"]; ?>"/>
<input type="hidden" name="retur" value="<?php echo $retur; ?>"/>
<input type="hidden" name="op" value="<?php echo $op["poli_id"]; ?>"/>
<input type="hidden" name="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"]; ?>"/>
<input type="hidden" name="reg_tipe_paket" value="<?php echo $_POST["reg_tipe_paket"]; ?>"/>
<input type="hidden" name="dep_posting_beban" value="<?php echo $_POST["dep_posting_beban"];?>"/>
<input type="hidden" name="operasi" value="<?php echo $operasi["preop_id"];?>"/>
<input type="hidden" name="dep_cetak_rincian" value="<?php echo $_POST["dep_cetak_rincians"];?>"/>
</form>

</div>
</div>
<!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      
<!-- jQuery -->
<?php require_once($LAY."js.php") ?>

  </body>
</html>













<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <body  onLoad="GantiPembulatan('<?php echo $_POST["txtBiayaPembulatan"];?>','<?php echo $grandTotalHarga;?>')"; >
  <body class="nav-sm">
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
                <h3>Pembayaran</h3>
              </div>
            </div>

            <div class="clearfix"></div>
			<?php if($simpan) { ?>
				<font color="red"><strong>Konfigurasi telah disimpan, klik tombol KELUAR pada MENU UTAMA agar perubahan Konfigurasi terjadi.</strong></font>
			<?php } ?> 
            <div class="row"> <!-- ==== BARIS ===== -->
			<!-- ==== kolom kiri ===== -->
			<!-- ==== mulai form ===== -->
			<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-6 col-sm-6 col-xs-12">

			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No. RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
						<input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_kode"]; ?>">
            			</div>
                      </div>                      
                      <?php if($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500') { ?>	
         			<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
						<input readonly type="text" class="form-control" value="<?php echo $dataPasien["fol_keterangan"]; ?>">
            			</div>
                      </div>
                      <?php } else { ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
						<input readonly type="text" class="form-control" value="<?php echo nl2br($dataPasien["cust_usr_alamat"]); ?>">
            			</div>
                      </div>
                      <?php } ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Sudah Terima Dari
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" class="form-control" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                		</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Cara Bayar
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        	<select readonly name="reg_jenis_pasien" class="select2_single form-control" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                  			 <option value="--">[ Pilih Cara Bayar ]</option>			
				              <?php for($i=0,$n=count($dataJenis);$i<$n;$i++){ ?>
         	        			 <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["reg_jenis_pasien"]==$dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
				            <?php } ?>
			            </select>
			            </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Layanan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        	<select readonly name="reg_tipe_layanan" class="select2_single form-control" disabled id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);">
                   			<option value="--">[ Pilih Tipe Layanan ]</option>			
				              <?php for($i=0,$n=count($dataTipeLayanan);$i<$n;$i++){ ?>
         	         			<option value="<?php echo $dataTipeLayanan[$i]["tipe_biaya_id"];?>" <?php if($_POST["reg_tipe_layanan"]==$dataTipeLayanan[$i]["tipe_biaya_id"]) echo "selected"; ?>><?php echo $dataTipeLayanan[$i]["tipe_biaya_nama"];?></option>
				            <?php } ?>
			            		</select>&nbsp;<input type="submit" name="btnOk" value="Ganti Data" class="submit" />
                			</div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Klinik
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <select readonly class="select2_single form-control" name="id_poli" disabled id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                   				<option value="--">[ Pilih Klinik ]</option>			
				                <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
         	        			 <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
				               <?php } ?>
				               </select>
				            </div>
                      </div>
                      
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jenis Bayar
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <select name="id_jbayar" class="select2_single form-control" id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">		
       					<?php if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Cara Bayar  -</option><?php } ?>
           				<?php $counter = -1;
           				for($i=0,$n=count($dataJenisBayar2);$i<$n;$i++){
               			unset($spacer); 
						$length = (strlen($dataJenisBayar2[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
						for($j=0;$j<$length;$j++) $spacer .= "..";  
        				?>                                                                      
         	  			<option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"];?>" <?php if($_POST["id_jbayar"]==$dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenisBayar2[$i]["jbayar_nama"];?></option>
				    	<?php } ?>
			    		</select>
				            </div>
                      </div>

					 
                  </div>
                </div>
			<!-- ==== //panel putih ===== -->
			<!-- ==== panel putih ===== -->
			
			<div class="x_panel">
                  <div class="x_title">
                    <h2>Foto Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td>
               		</div>					  
                  </div>
                </div>

			  </div>
			  <!-- ==== // kolom kiri ===== -->
			  
			  <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">            
              <div class="x_panel">
                  <div class="x_title">
                    <h2>Total Tagihan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php if($dataPasien["reg_jenis_pasien"]=='5' || $dataPasien["reg_jenis_pasien"]=='7' || $dataPasien["reg_jenis_pasien"]=='18' || $dataPasien["reg_jenis_pasien"]=='26' || $dataPasien["reg_jenis_pasien"]=='25') { ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="6"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } elseif($dataPasien["id_cust_usr"]=='100' || $dataPasien["id_cust_usr"]=='500' && $dataPasien["reg_jenis_pasien"]=='2'){ ?>
               <td width= "40%" align="center" class="tablecontent" rowspan="2"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } else {?>
               <td width= "40%" align="center" class="tablecontent" rowspan="5"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($grandTotalHarga);?></span></font></td>
               <?php } ?></div>
                      </div>
                    
                      <?php for($i=0,$n=count($dataJenisBayar);$i<$n;$i++) { 
               unset($spacer); 
		
    		$length = (strlen($dataJenisBayar[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
    		for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;"; 
        		
         //$_POST["txtDibayar"][$i] = '0';?>
          <?
          $ttotal = currency_format($grandTotalHarga);
          ?>
          <tr>
           <td class="tablecontent" align="center">&nbsp;</td>         
           <td width= "40%" align="right" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;<?php echo $spacer;?>&nbsp;<b>Total Pembayaran<?php //echo strtoupper($dataJenisBayar[$i]["jbayar_nama"]);?></b> </td>
           <td class="tablecontent" colspan="4">&nbsp;&nbsp;
           <? if($_POST["reg_jenis_pasien"]=="5" || $_POST["reg_jenis_pasien"]=="26"){
              $showTotal = $ttotal;
           } ?>
               <?php if($dataJenisBayar[$i]["jbayar_id"]=='01' || $dataJenisBayar[$i]["jbayar_lowest"]!='n') echo 
               $view->RenderTextBox("txtDibayar[$i]","txtDibayar$i","30","30",$showTotal,"curedit", "",true,'onChange=GantiPengurangan(this.value,'.$i.');');
                     else echo ""; ?></td>
           <input type="hidden" name="byr<?php echo $i;?>_int" id="byr<?php echo $i;?>_int" value="<?php if($_POST["jsByr"][$i]) echo $_POST["jsByr"][$i]; else echo '0';?>" />
           <input type="hidden" name="js_id[<?php echo $i;?>]" id="js_id_<?php echo $i;?>" value="<?php echo $dataJenisBayar[$i]["jbayar_id"] ;?>" />
          </tr>           
         <?php } ?>
         <br></br>
				       <td width="50%" align="center">
               			<?php if($dataTable){ ?>
               			<input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="submit" onClick="javascript:return CekData();"/>     
               			<?php } ?>
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_view.php'";/>     
				       </td>				      
          </div>				 						 
      </div>
              
              
                   <div class="x_panel">
                  <div class="x_content">
                  <fieldset>
     <legend><strong>Data Order</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr class="tablesmallheader">
            <td width="1%" align='center'>No</td>
            <td width="15%" align='center'>Poli</td>
            <td width="15%" align='center'>Dokter Pengirim</td>
            <td width="15%" align='center'>Dokter Tujuan</td>
            <td width="3%" align='center'>Telah Dilayani</td>                            
        </tr>
      	<?php for($i=0,$n=count($dataorderPoli);$i<$n;$i++) { 
              $sql = "select fol_id from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR,$dataorderPoli[$i]["reg_id"]);
              $datalayani = $dtaccess->Fetch($sql); ?>                                                                      
        <tr class="tablecontent-odd">
            <td width="1%" align='center'><?php echo ($i+1)."."; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["poli_nama"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["dokter_sender"]; ?></td>
            <td width="15%" align='left'><?php echo $dataorderPoli[$i]["usr_name"]; ?></td>
            <?php if(!$datalayani) {?>
            <td width="3%" align='center'>&nbsp;</td>                            
            <?php }else{ ?>
            <td width="3%" align='center'><img hspace="2" width="20" height="20" src="<?php echo $ROOT.'gambar/aktif.png';?>" /></td>
            <?php } ?> 
        </tr>
        <?php } ?>  
     </table>
     </div>
     </fieldset>
     </div>
                </div>
              
              
              
              
              
              
              
              
                
                 <div class="x_panel">                  
                  <div class="x_content">
                      <div class="form-group">
                        <fieldset>
     <legend><strong>Data Pembayaran</strong></legend>
     <div id="kasir">
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
              <tr class="tablesmallheader">
              <td width="3%" align='center'>No</td>
              <td width="32%" align='center'>Layanan</td>
			        <td width="17%" align='center'>Nama Dokter</td>
              <td width="10%" align='center'>Biaya</td>
              <td width="5%" align='center'>Quantity</td>
              <td width="10%" align='center'>Tagihan</td>
						  </tr>
						  
						  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
						  
                        <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
                                || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
                                || $dataTable[$i]["fol_jenis"]=='I'){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                                $rs = $dtaccess->Execute($sql); 
                                $dataFarmasidetail  = $dtaccess->FetchAll($rs); 
                                 }         
                                
                         if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA' 
                              || $dataTable[$i]["fol_jenis"]=='RG' || $dataTable[$i]["fol_jenis"]=='RI' ){
                         $sql = "select item_nama, a.* ,satuan_nama
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql);
                                $dataReturdetail  = $dtaccess->FetchAll($rs);     }      ?> 
                                	
              <tr class="tablecontent-odd">
                <td width="3%"><?php echo ($i+1).".";?></td>
                <td width="32%">
                    <?php if($dataTable[$i]["fol_jenis"]=="O" || $dataTable[$i]["fol_jenis"]=="OA" || $dataTable[$i]["fol_jenis"]=="OG" || 
                             $dataTable[$i]["fol_jenis"]=="OI" || $dataTable[$i]["fol_jenis"]=="R" || $dataTable[$i]["fol_jenis"]=="RA" || 
                             $dataTable[$i]["fol_jenis"]=="RA" || $dataTable[$i]["fol_jenis"]=="RG" || $dataTable[$i]["fol_jenis"]=="RI"){
                            echo $dataTable[$i]["fol_nama"]." (".$dataTable[$i]["fol_catatan"].")";
                          } else echo $dataTable[$i]["fol_nama"];?>
                </td>
				        <td width="17%"><?php echo $dataTable[$i]["usr_name"];?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal_satuan"]);?></td>
                <td width="5%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]);?></td>
                <td width="10%" align='right'><?php echo currency_format($dataTable[$i]["fol_nominal"])?></td>
						  </tr>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I' || $dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){  ?>

       <tr class="garis_atas garis_bawah"> 
<?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I') {
                              $sql = "select count(penjualan_detail_id) as total
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_id"]);
                              $rs = $dtaccess->Execute($sql);
                              $totalitem = $dtaccess->Fetch($rs);        
            }
            if($dataTable[$i]["fol_jenis"]=='R'|| 
            $dataTable[$i]["fol_jenis"]=='RI'
            || $dataTable[$i]["fol_jenis"]=='RA' ||$dataTable[$i]["fol_jenis"]=='RG'){
                               $sql = "select count(retur_penjualan_detail_id) as total
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["fol_catatan"]);
                                $rs = $dtaccess->Execute($sql); 
            } ?>         
         <td align="left" rowspan="<?php echo $totalitem["total"]+1;?>" ></td>
        <td align="left">Nama Item/Obat</td>
        <td align="right">Harga Satuan</td>
        <td align="right">Quantity</td>                             
        <td align="right">Total</td>
        <td align="right"></td>
	    </tr>     

    <?php } ?>
     <?php if($dataTable[$i]["fol_jenis"]=='O'||$dataTable[$i]["fol_jenis"]=='OI'
            || $dataTable[$i]["fol_jenis"]=='OA' ||$dataTable[$i]["fol_jenis"]=='OG'
            || $dataTable[$i]["fol_jenis"]=='I'){  ?>
    
    <?php for($x=0,$y=count($dataFarmasidetail);$x<$y;$x++) {?>
       <tr>

          <td align="left"> -  <?php echo $dataFarmasidetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"]);?></td>
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"]);?>  <?php echo $dataFarmasidetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataFarmasidetail[$x]["penjualan_detail_total"]);?></td>
        <td align="right"></td>
       </tr>     
       <?php } ?>                 
  
       <?php } ?>
           <?php if($dataTable[$i]["fol_jenis"]=='R' || $dataTable[$i]["fol_jenis"]=='RA'
           || $dataTable[$i]["fol_jenis"]=='RI' ||$dataTable[$i]["fol_jenis"]=='RG'){ ?>                        
    <?php for($x=0,$y=count($dataReturdetail);$x<$y;$x++) {?>
       <tr class="garis_atas garis_bawah">
          <td align="left"> -  <?php echo $dataReturdetail[$x]["item_nama"];?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_total"]);?></td>
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"]);?>  <?php echo $dataReturdetail[$x]["satuan_nama"];?></td>          
          <td align="right"><?php echo currency_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"]);?></td>

       </tr>     
       <?php } ?>                 
              
           <? } ?>        
     </tr>
     <?php 

          $totalPembayaran += $dataTable[$i]["fol_nominal"]; 
          $totalHarga=$totalBiaya-$dijaminHarga;
          if ($totalHarga<0) $totalHarga=0;
     
     														?>						  
						  			<?php } ?>
              
              						<tr>
                  				<td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Tagihan</b></td>              
                  				<td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. ".currency_format($totalBiaya)."</b>";?></td>
						  		</tr>
 							</table>
							</div>
     					</fieldset>                          
                      </div>
                  </div>				 						 
                </div>

			  <!-- ==== // KHUSUS BUTTON ===== -->
              </div>
              <?php echo $view->RenderHidden("konf_reg_id","konf_reg_id",$_POST["konf_reg_id"]);?> 
			</form>	<!-- ==== Akhir form ===== -->
			<!-- ==== // kolom kanan ===== -->
            </div> <!-- ==== // BARIS ===== -->
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