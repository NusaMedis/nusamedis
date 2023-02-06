<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."tampilan.php");
 
  //INISIALISAI AWAL LIBRARY
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  
  $fol_id = $dtaccess->GetTransID(); 
  $reg_id = $regId;
  $rt_id = $dtaccess->GetTransID(); 

  
  //cari data registrasi
  $sql = "select a.id_dokter, a.id_poli, a.id_cust_usr, a.id_pembayaran,b.rawat_id from klinik.klinik_registrasi a  
      left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
      where a.reg_id = '$reg_id'";
  $dataReg = $dtaccess->Fetch($sql);

  //cari nama dan nominal biaya
  $sql = "select a.*,b.*,c.*  from  klinik.klinik_biaya_pemeriksaan a                       
                      left join klinik.klinik_biaya_tarif c on c.biaya_tarif_id = a.id_biaya_tarif
                      left join klinik.klinik_biaya b on c.id_biaya = b.biaya_id 
                      where 1=1";
  $sql .= " and a.id_poli=".QuoteValue(DPE_CHAR,$_POST["poli_id"]);
  $biaya = $dtaccess->Fetch($sql);
  
  # simpan klinik folio 
  $dbTable = "klinik.klinik_folio";

  $dbField[0] = "fol_id";   // PK
  $dbField[1] = "id_reg"; 
  $dbField[2] = "id_dokter"; 
  $dbField[3] = "id_poli"; 
  $dbField[4] = "id_cust_usr"; 
  $dbField[5] = "id_biaya"; 
  $dbField[6] = "id_pembayaran"; 
  $dbField[7] = "fol_lunas"; 
  $dbField[8] = "id_dep"; 
  $dbField[9] = "fol_jumlah"; 
  $dbField[10] = "who_when_update"; 
  $dbField[11] = "fol_nama";  
  $dbField[12] = "fol_nominal_satuan";  
  $dbField[13] = "fol_nominal"; 
  $dbField[14] = "fol_hrs_bayar";
  $dbField[15] = "fol_dokter_instruksi";
  $dbField[16] = "fol_pelaksana";
  $dbField[17] = "id_biaya_tarif";
  $dbField[18] = "fol_waktu";
  $dbField[19] = "tindakan_waktu";
  $dbField[20] = "tindakan_tanggal";  
  $dbField[21] = "fol_jenis";
  $dbField[22] = "fol_jenis_sem";

  $tanggal = QuoteValue(DPE_DATE,date("Y-m-d"));
  $waktu = QuoteValue(DPE_DATE,date("H:i:s"));
  
  $dbValue[0] = QuoteValue(DPE_CHAR,$fol_id);
  $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id);
  $dbValue[2] = QuoteValue(DPE_CHAR,$dataReg['id_dokter']);
  $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_poli"]); 
  $dbValue[4] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]); 
  $dbValue[5] = QuoteValue(DPE_CHAR,$biaya["biaya_id"]);
  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
  $dbValue[7] = QuoteValue(DPE_CHAR,'n');
  $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
  $dbValue[9] = QuoteValue(DPE_CHAR,'1');
  $dbValue[10] = QuoteValue(DPE_CHAR,$userName);
  $dbValue[11] = QuoteValue(DPE_CHAR,$biaya['biaya_nama']);
  $dbValue[12] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']);
  $dbValue[13] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']);
  $dbValue[14] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']);
  $dbValue[15] = QuoteValue(DPE_CHAR,$dataReg['id_dokter']);
  $dbValue[16] = QuoteValue(DPE_CHAR,$dataReg['id_dokter']);
  $dbValue[17] = QuoteValue(DPE_CHAR,$biaya['biaya_tarif_id']);
  $dbValue[18] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
  $dbValue[19] = QuoteValue(DPE_DATE,$waktu);
  $dbValue[20] = QuoteValue(DPE_DATE,$tanggal);  
  $dbValue[21] = QuoteValue(DPE_CHAR,$biaya['biaya_jenis']);
  $dbValue[22] = QuoteValue(DPE_CHAR,$biaya['biaya_jenis_sem']); 
  
  $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  
  if ($biaya) { 
    $dtmodel->Insert() or die("insert  error"); 
    echo "sukses insert folio => ";
  }
  
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);  
    
  #update klinik pembayaran
  $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where
   id_pembayaran = ".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]); 
   $rs = $dtaccess->Execute($sql);
   $dataFolio = $dtaccess->Fetch($rs);   
 
  $dbTable = "klinik.klinik_pembayaran";
  $dbField[0] = "pembayaran_id";   // PK
  $dbField[1] = "pembayaran_total";
  
  $dbValue[0] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
  $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataFolio["total"]);            
  
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
  $dtmodel->Update() or die("update  error"); 

  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);   

  #cek perawatan
  $sql_rawat = "select * from klinik.klinik_perawatan 
                where id_reg = ".QuoteValue(DPE_CHAR,$reg_id);
  $dataPerawat= $dtaccess->Fetch($sql_rawat);
  #jika tidak ada maka isi
  if(!$dataPerawat){
    $rawat_id = $dtaccess->GetTransID();          

    $dbTable = " klinik.klinik_perawatan";
    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "id_reg";
    $dbField[2] = "id_cust_usr";
    $dbField[3] = "rawat_waktu_kontrol";
    $dbField[4] = "rawat_tanggal";
    $dbField[5] = "rawat_flag"; 
    $dbField[6] = "rawat_flag_komen"; 
    $dbField[7] = "id_poli"; 
    $dbField[8] = "id_dep";
    $dbField[9] = "rawat_who_update";
    $dbField[10] = "rawat_waktu";         
    
    $dbValue[0] = QuoteValue(DPE_CHAR,$rawat_id);   // PK
    $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id);
    $dbValue[2] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
    $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
    $dbValue[5] = QuoteValue(DPE_CHAR,'J'); 
    $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
    $dbValue[7] = QuoteValue(DPE_CHAR,$dataReg["id_poli"]); 
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
    
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    $dtmodel->Insert() or die("insert  error"); 
    echo " sukses input perawatan =>";

     unset($dtmodel);
     unset($dbValue);
     unset($dbField);
     unset($dbKey);
  }else {
    $rawat_id = $dataReg["rawat_id"];
  }
  
    
  # simpan klinik perawatan tindakan  
  $dbTable = "klinik.klinik_perawatan_tindakan";
  $dbField[0] = "rawat_tindakan_id";   // PK
  $dbField[1] = "id_fol"; 
  $dbField[2] = "id_tindakan"; 
  $dbField[3] = "rawat_tindakan_total"; 
  $dbField[4] = "id_dokter"; 
  $dbField[5] = "id_dep"; 
  $dbField[6] = "id_rawat"; 
  $dbField[7] = "rawat_tindakan_jumlah"; 
  $dbField[8] = "is_sync"; 
  $dbField[9] = "rawat_tindakan_flag"; 

  $dbValue[0] = QuoteValue(DPE_CHAR,$rt_id);
  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
  $dbValue[2] = QuoteValue(DPE_CHAR,$biaya["biaya_id"]);
  $dbValue[3] = QuoteValue(DPE_CHAR,$biaya['biaya_total']);
  $dbValue[4] = QuoteValue(DPE_CHAR,$dataReg['id_dokter']);
  $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
  $dbValue[6] = QuoteValue(DPE_CHAR,$rawat_id);
  $dbValue[7] = QuoteValue(DPE_NUMERIC,'1');
  $dbValue[8] = QuoteValue(DPE_CHAR,"n");
  $dbValue[9] = QuoteValue(DPE_CHAR,"J");
  
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  $dtmodel->Insert() or die("insert  error"); 
  echo "sukses insert perawatan tndakan => " ;
    
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);  
      
  # simpan di pelaksana
  //cari folio
  $sql = "select fol_nominal, fol_jumlah from klinik.klinik_folio
      where fol_id = ".QuoteValue(DPE_CHAR,$fol_id);
  $rs = $dtaccess->Execute($sql);
  $folio = $dtaccess->Fetch($rs);
  
  //cari split biaya
  $sql = "select id_split ,id_biaya, bea_split_persen,bea_split_nominal from klinik.klinik_biaya_split a  
      left join klinik.klinik_split b on a.id_split = b.split_id
      where id_biaya_tarif = ".QuoteValue(DPE_CHAR, $biaya["biaya_tarif_id"]);
  $rs = $dtaccess->Execute($sql);
  $biayaSplit = $dtaccess->FetchAll($rs);
    
  #simpan split folio
  #INSERT FOLIO SPLIT dan Biaya Remunerasi
  for ($i = 0; $i < count ($biayaSplit); $i++) 
  {
    //INSERT KLINIK BIAYA SPLIT
      $dbTable = "klinik.klinik_folio_split";
      $dbField[0] = "folsplit_id";   // PK
      $dbField[1] = "id_fol";
      $dbField[2] = "id_split";
      $dbField[3] = "folsplit_nominal";
      $dbField[4] = "folsplit_nominal_satuan";
      $dbField[5] = "folsplit_jumlah";
      $dbField[6] = "id_dep";
      //$dbField[7] = "id_fol_pelaksana";
      
      $folSplitId = $dtaccess->GetTransID();  
      
      $hasilSatuan = $biayaSplit[$i]["bea_split_nominal"];
      $hasil = ($hasilSatuan)*$folio["fol_jumlah"];
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
      $dbValue[2] = QuoteValue(DPE_CHAR,$biayaSplit[$i]["id_split"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,$hasil);
      $dbValue[4] = QuoteValue(DPE_NUMERIC,$biayaSplit[$i]["bea"]*$hasilSatuan);
      $dbValue[5] = QuoteValue(DPE_NUMERIC,$folio["fol_jumlah"]);
      $dbValue[6] = QuoteValue(DPE_NUMERIC,$depId);
      
      $dbKey[1] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Insert() or die("insert  error"); 
      echo "sukses insert folio split =>".$folSplitId;
      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);          
  } //AKHIR BIAYA SPLIT        

  exit();      
  
?>