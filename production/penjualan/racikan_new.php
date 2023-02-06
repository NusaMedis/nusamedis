<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
     $thisPage = "racikan_new.php";
     $findPage1 = "item_find.php";

     if($_GET['kode']){$kode = $enc->Decode($_GET['kode']);}
     else{$kode = $_POST['cust_usr_kode'];}
     if($_GET['transaksi']){$penjualanId = $enc->Decode($_GET['transaksi']);}
     else{$penjualanId = $_POST['penjualan_id'];}
     if($_GET['id_pembayaran']){ $pembayaranId = $_GET['id_pembayaran'];}
     else{$pembayaranId = $_POST['id_pembayaran'];}
     if($_GET['jenis_id'])$_POST['jenis_id']=$_GET['jenis_id'];
     if($_GET['item'])$racikanId = $_GET['item'];
     if($_GET['id_reg']){$idReg = $enc->Decode($_GET['id_reg']);}
    elseif ($_GET['idreg']) {
      $idReg = $enc->Decode($_GET['idreg']);
    }
     else{$idReg = $_POST['id_reg'];}

   // PRIVILLAGE
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     
     $backPage = "penjualan.php?kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId;
    // echo $backPage;
     //gudangnya
     $sql = "select id_gudang from apotik.apotik_penjualan where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
     $dataGudang = $dtaccess->Fetch($sql);
     $poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     //echo $sql; die(); 
     $theDep = $dataGudang["id_gudang"];  //Ambil Gudang yang aktif  
         
//cari master racikan dan stoknya
     $sql = "select * from apotik.apotik_nama_racikan";
    // echo $sql;
     $rs = $dtaccess->Execute($sql);     
     $dataRacikan = $dtaccess->FetchAll($rs);

     if ($_GET['id_penjualan_detail']) {
        $sql = "select a.*,b.item_nama,b.item_kode,b.item_racikan,c.jenis_nama,d.petunjuk_nama, f.batch_no, f.batch_tgl_jatuh_tempo, g.aturan_minum_nama, h.aturan_pakai_nama, i.jam_aturan_pakai_nama
             from apotik.apotik_penjualan_detail a
             left join logistik.logistik_item b on a.id_item=b.item_id 
             left join global.global_jenis_pasien c on b.item_tipe_jenis=c.jenis_id
             left join apotik.apotik_obat_petunjuk d on a.id_petunjuk=d.petunjuk_id
             left join apotik.apotik_jenis_racikan e on a.id_jenis_racikan = e.jenis_racikan_id
             left join logistik.logistik_item_batch f on f.batch_id = a.id_batch
             left join apotik.apotik_aturan_minum g on a.id_aturan_minum=g.aturan_minum_id
             left join apotik.apotik_aturan_pakai h on h.aturan_pakai_id=a.id_aturan_pakai
             left join apotik.apotik_jam_aturan_pakai i on i.jam_aturan_pakai_id = a.id_jam_aturan_pakai
             where a.penjualan_detail_id = ".QuoteValue(DPE_CHAR,$_GET['id_penjualan_detail'])."
             order by id_jenis_racikan desc, penjualan_detail_nama_racikan asc";
        $rs_edit = $dtaccess->Execute($sql);
        $dataTable = $dtaccess->Fetch($rs_edit);
     }
     
if($_GET['new']){
  //cari nama Pasiennya
  $sql = " select cust_usr_nama from global.global_customer_user where cust_usr_kode =".QuoteValue(DPE_CHAR,$kode);
  $namaPasien = $dtaccess->Fetch($sql);

//tambah racikan baru
  //$namaRacikan = "Racikan_".date('YmdHis');
  $namaRacikan = $namaPasien["cust_usr_nama"]."_".date('YmdHis');
  $dbTable = "apotik.apotik_nama_racikan";

          $dbField[0]  = "nama_racikan_id";   // PK
          $dbField[1]  = "nama_racikan_nama";
       
      $racikanId = $dtaccess->GetTransID();
         
          $dbValue[0] = QuoteValue(DPE_CHAR,$racikanId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$namaRacikan);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");     
          
          unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
    
    $dbTable = "logistik.logistik_item";

          $dbField[0]  = "item_id";   // PK
          $dbField[1]  = "item_nama";
          $dbField[2]  = "item_racikan";
       
    //  $racikanId = $dtaccess->GetTransID();
         
          $dbValue[0] = QuoteValue(DPE_CHAR,$racikanId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$namaRacikan);
          $dbValue[2] = QuoteValue(DPE_CHAR,'y');

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");     
          
          unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);

            $dbTable = "logistik.logistik_item_batch";
               
               $dbField[0] = "batch_id";   // PK
               $dbField[1] = "id_item";
               $dbField[2] = "batch_no";
               $dbField[3] = "batch_create";
               $dbField[4] = "id_dep";
               $dbField[5] = "batch_flag";
               
               $batchId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$racikanId); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId); 
               $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));    
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[5] = QuoteValue(DPE_CHAR,'A');
              
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
                    $dtmodel->Insert() or die("insert  error"); 
                  
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);

            $dbTable = "logistik.logistik_stok_item";
                        $dbField[0]  = "stok_item_id";   // PK
                        $dbField[1]  = "stok_item_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_flag";
                        $dbField[5]  = "stok_item_create";         
                        $dbField[6]  = "stok_item_saldo";
                        $dbField[7]  = "id_dep";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'A');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

                      $dbTable = "logistik.logistik_stok_dep";
                      $dbField[0]  = "stok_dep_id";   // PK
                      $dbField[1]  = "stok_dep_saldo";
                      $dbField[2]  = "id_item";    
                      $dbField[3]  = "id_gudang";
                      $dbField[4]  = "stok_dep_tgl";
                      $dbField[5]  = "stok_dep_create";         
                      $dbField[6]  = "id_dep";
                      
                      $date = date("Y-m-d H:i:s");
                      $stokdepid = $dtaccess->GetTransID();
                      $dbValue[0] = QuoteValue(DPE_CHAR,$stokdepid);
                      $dbValue[1] = QuoteValue(DPE_NUMERIC,'0');  
                      $dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
                      $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
                      $dbValue[5] = QuoteValue(DPE_DATE,$date); 
                      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                      
                      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
            
                      $dtmodel->Insert() or die("insert  error"); 
                      
                      unset($dbTable);
                      unset($dbField);
                      unset($dbValue);
                      unset($dbKey); 

                        $dbTable = "logistik.logistik_stok_item_batch";
                        $dbField[0]  = "stok_item_batch_id";   // PK
                        $dbField[1]  = "stok_item_batch_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_batch_flag";
                        $dbField[5]  = "stok_item_batch_create";         
                        $dbField[6]  = "stok_item_batch_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_batch";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokbatchid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'A');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$batchId);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

                      $dbTable = "logistik.logistik_stok_batch_dep";
                      $dbField[0]  = "stok_batch_dep_id";   // PK
                      $dbField[1]  = "stok_batch_dep_saldo";
                      $dbField[2]  = "id_item";    
                      $dbField[3]  = "id_gudang";
                      $dbField[4]  = "stok_batch_dep_tgl";
                      $dbField[5]  = "stok_batch_dep_create";         
                      $dbField[6]  = "id_dep";
                      $dbField[7]  = "id_batch";
                      
                      $date = date("Y-m-d H:i:s");
                      $stokbatchdepid = $dtaccess->GetTransID();
                      $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchdepid);
                      $dbValue[1] = QuoteValue(DPE_NUMERIC,'0');  
                      $dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
                      $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
                      $dbValue[5] = QuoteValue(DPE_DATE,$date); 
                      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
                      $dbValue[7] = QuoteValue(DPE_CHAR,$batchId);
                      
                      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
            
                      $dtmodel->Insert() or die("insert  error"); 
                      
                      unset($dbTable);
                      unset($dbField);
                      unset($dbValue);
                      unset($dbKey); 
     }
     if($_GET['del']){
      //hapus dataracikan
      $sql = "delete from apotik.apotik_detail_racikan where detail_racikan_id = ".QuoteValue(DPE_CHAR,$_GET["id"])."
            and id_nama_racikan = ".QuoteValue(DPE_CHAR,$_GET["item"]);
      $rs = $dtaccess->Execute($sql);
      //hapus stok_itemnya
      $sql = "delete from logistik.logistik_stok_item where id_item =".QuoteValue(DPE_CHAR,$_GET["rinci"])."
            and id_racikan = ".QuoteValue(DPE_CHAR,$_GET["item"]);
      $rs = $dtaccess->Execute($sql);
      //hapus stok_itemnya
      $sql = "delete from logistik.logistik_stok_item_batch where id_item =".QuoteValue(DPE_CHAR,$_GET["rinci"])."
            and id_racikan = ".QuoteValue(DPE_CHAR,$_GET["item"]);
      $rs = $dtaccess->Execute($sql);

       $sql = "select sum(detail_racikan_total) as total from apotik.apotik_detail_racikan
                where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_GET['item']);
        $rs = $dtaccess->Execute($sql);
        $racik = $dtaccess->Fetch($rs);
        //update itemnya
        $sql= "update logistik.logistik_item set item_harga_beli = ".QuoteValue(DPE_NUMERIC,$racik['total'])."
              where item_id = ".QuoteValue(DPE_CHAR,$_GET['item']);
        $rs = $dtaccess->Execute($sql);
            
$addDetailPage = "racikan_new.php?q=".$batchId."&item=".$_GET['item']."&kode=".$_GET["kode"]."&transaksi=".$_GET["transaksi"]."&idreg=".$_GET["idreg"]."&id_pembayaran=".$_GET["id_pembayaran"]."&jenis_id=".$_POST['reg_jenis_pasien']."&id_penjualan_detail=".$_GET["id_penjualan_detail"];
header('location:'.$addDetailPage);
     }
     if($_GET['item']){
      //data racikan
      $sql = "select * from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$_GET["item"]);
      $rs = $dtaccess->Execute($sql);
      $dataRacikanNew = $dtaccess->Fetch($rs);
      
      $namaRacikan = $dataRacikanNew["item_nama"];

      //cari detail racikan
      $sql = "select * from apotik.apotik_detail_racikan a
              left join logistik.logistik_stok_item b on a.id_item = b.id_item and a.id_nama_racikan = b.id_racikan
              where a.id_nama_racikan = ".QuoteValue(DPE_CHAR,$_GET["item"]);
      $rs = $dtaccess->Execute($sql);
      $dataDetRacikan = $dtaccess->FetchAll($rs);
      // echo $sql;
     
     $_POST['penjualan_detail_total']=$dataRacikanNew['item_harga_beli'];
     // echo $_GET['item'];

     }

if($_POST["btnSimpanRacik"]){

    $sqlKonf = "select conf_biaya_tuslag, conf_biaya_tuslag_persen from apotik.apotik_conf";
    $rsKonf = $dtaccess->Execute($sqlKonf);
    $Konfigurasi = $dtaccess->Fetch($rsKonf); 

    $sql = "select sum(detail_racikan_total) as total, sum(item_harga_jual) as harga_jual, sum(detail_racikan_ppn) as ppn, sum(detail_racikan_hpp) as hpp, sum(detail_racikan_harga_pokok) harga_pokok, sum(detail_racikan_tuslag) as tuslag from apotik.apotik_detail_racikan
                where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
    $rs = $dtaccess->Execute($sql);
    $racik = $dtaccess->Fetch($rs);

  if(!$_POST["penjualan_detail_jumlah"]) $_POST["penjualan_detail_jumlah"]="1";
  if($_POST["jenisracikan"] == '1') {
  	

     $sql = "select item_harga_jual * detail_racikan_jumlah as harga from apotik.apotik_detail_racikan
      where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
      $dtd = $dtaccess->FetchAll($sql);
      
      for($i = 0; $i < count($dtd); $i++){
        $harga_jual += intval($dtd[$i]['harga']);
      }
    $hargasatuan = $harga_jual;
  	$totalsatuan = StripCurrency($harga_jual);
  	$totaltuslag = StripCurrency($racik['tuslag']*$_POST["penjualan_detail_jumlah"]);
  	$totalharga = StripCurrency($racik['total']*$_POST["penjualan_detail_jumlah"]);
  	$racik['ppn'] = $racik['ppn'] *$_POST["penjualan_detail_jumlah"];
	  $racik['harga_pokok'] = $racik['harga_pokok'] *$_POST["penjualan_detail_jumlah"];
	  $racik['hpp'] = $racik['hpp'] *$_POST["penjualan_detail_jumlah"];
  }
  else {
  	$hargasatuan = $racik["harga_jual"];///StripCurrency($_POST["penjualan_detail_jumlah"]);
  	$totalsatuan = StripCurrency($racik['harga_jual']);
  	$totaltuslag = StripCurrency($racik['tuslag']);
  	$totalharga = StripCurrency($racik['total']);
  }
  	

  

        $sql= "update logistik.logistik_item set item_harga_beli = ".QuoteValue(DPE_NUMERIC,intval($hargasatuan)).",
              item_harga_jual = ".QuoteValue(DPE_NUMERIC, intval($hargasatuan)).", id_satuan_jual =".QuoteValue(DPE_CHAR,$_POST["id_satuan_jual"]).", item_nama =".QuoteValue(DPE_CHAR,$_POST["racikan_nama"])."
              where item_id = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
        $rs = $dtaccess->Execute($sql);
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
          $dbField[14]  = "id_aturan_pakai";
          $dbField[15]  = "id_aturan_minum";
          $dbField[16] = "id_jam_aturan_pakai";
          $dbField[17]  = "item_nama";
    	  $dbField[18]  = "penjualan_detail_ppn";
          $dbField[19]  = "penjualan_detail_harga_pokok";
          $dbField[20]  = "penjualan_detail_harga_beli";

               $penjualandetId = ($_POST["id_penjualan_detail"]) ? $_POST["id_penjualan_detail"] : $dtaccess->GetTransID();
          
          
          //echo $totalsatuan."+".$row['item_tuslag'].$totaltuslag."=".$totalharga;
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualandetId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_id"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency(intval($hargasatuan)));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["penjualan_detail_jumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$totalharga);  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["penjualan_detail_jumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($totaltuslag));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["id_jam_aturan_pakai"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["racikan_nama"]);
          $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($racik['ppn']));
          $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($racik['harga_pokok']));
          $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($racik['hpp']));
          
          //print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if (!$_POST["id_penjualan_detail"]) $dtmodel->Insert() or die("insert  error");     
          else $dtmodel->Update() or die("Update  error");     
          
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["cbDelete"]);
          unset($_POST["jumlah"]);
          unset($_POST["dosis"]);
          unset($_POST["total"]);
          unset($_POST["id_batch"]);

          $dbTable = "apotik.apotik_nama_racikan";

          $dbField[0]  = "nama_racikan_id";   // PK
          $dbField[1]  = "nama_racikan_jenis";
       
     
         
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jenisracikan"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Update() or die("Update  error");     
          
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $sql = "SELECT * FROM apotik.apotik_detail_racikan where id_nama_racikan = ".QuoteValue(DPE_CHAR, $_POST["id_racikan"]);
          $dataDetail = $dtaccess->FetchAll($sql);

          for($i = 0; $i < count($dataDetail); $i++){

            $racikanitemId = $dataDetail[$i]['id_item'];
            $_POST["jumlah"] = $dataDetail[$i]['detail_racikan_jumlah'];

            $dbTable = "logistik.logistik_stok_item";
                        $dbField[0]  = "stok_item_id";   // PK
                        $dbField[1]  = "stok_item_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_flag";
                        $dbField[5]  = "stok_item_create";         
                        $dbField[6]  = "stok_item_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_racikan";
                        
                        $date = date("Y-m-d H:i:s");

                        $stokid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["jumlah"]);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'P');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$_POST['id_racikan']);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

  //update stok_dep
            $saldo = 0;

            $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$racikanitemId)." order by id_gudang asc, stok_item_create asc";
            $dataAdjustment = $dtaccess->FetchAll($sql);
            /* SQL PENGURUTAN */

            for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
              if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
              if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
              if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Opname
              if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
              if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
              
              $sql  ="update logistik.logistik_stok_item set stok_item_saldo=".$saldo." where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
              $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            }

            $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$racikanitemId)." and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
            $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK); 

   $sql = "select batch_id from logistik.logistik_item_batch where id_item = ".QuoteValue(DPE_CHAR,$racikanitemId);
   $rs = $dtaccess->Execute($sql);
   $dataBatch = $dtaccess->Fetch($rs);                    
  //simpan stok_batch_item
$dbTable = "logistik.logistik_stok_item_batch";
                        $dbField[0]  = "stok_item_batch_id";   // PK
                        $dbField[1]  = "stok_item_batch_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_batch_flag";
                        $dbField[5]  = "stok_item_batch_create";         
                        $dbField[6]  = "stok_item_batch_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_batch";
                        $dbField[9]  = "id_racikan";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokbatchid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["jumlah"]);  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'P');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,$saldo); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$dataBatch["batch_id"]);
                        $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

  //simpan stok_batch_dep
         $sql = "update logistik.logistik_stok_batch_dep set stok_batch_dep_saldo ='$saldo', stok_batch_dep_tgl=".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_batch =".QuoteValue(DPE_CHAR,$dataBatch['batch_id'])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
        $rs = $dtaccess->Execute($sql);


          }

          



header('location:'.$backPage);
}

if($_POST["btnNew"]){
  $addDetailPage = "racikan_new.php?new=1&q=".$batchId."&item=".$itemId."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&id_reg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&jenis_id=".$_POST['jenis_id'];
header('location:'.$addDetailPage)."&id_penjualan_detail=".$_POST["id_penjualan_detail"];
}
 if($_POST["btnSimpanDet"]){

  $racikanitemId = $_POST["item_detail"];
  if(!$racikanitemId){
$addDetailPage = "racikan_new.php?q=".$batchId."&item=".$_POST['id_racikan']."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&id_penjualan_detail=".$_POST["id_penjualan_detail"];
   echo "<script type='text/javascript'>alert('Tidak ada item yang disimpan !'); window.location ='$addDetailPage'</script>";
   
//header('location:'.$addDetailPage);
  }else{

//detail racikan
  $dbTable = "apotik.apotik_detail_racikan";
        $dbField[0] = "detail_racikan_id";
        $dbField[1] = "id_nama_racikan";
        $dbField[2] = "id_item";
        $dbField[3] = "item_nama";
        $dbField[4] = "detail_racikan_jumlah";
        $dbField[5] = "detail_racikan_total";
        $dbField[6] = "item_harga_jual";
        $dbField[7] = "when_create";
        $dbField[8] = "who_create";
        $dbField[9] = "detail_racikan_ppn";
        $dbField[10] = "detail_racikan_hpp";
        $dbField[11] = "detail_racikan_harga_pokok";
        $dbField[12] = "detail_racikan_tuslag";

        if ($_POST['id_penjualan_detail'] != '') {
          $_POST["jumlah"] = $_POST['j_jumlah'];          
        }

        $date = date("Y-m-d H:i:s");
        $detracikId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$detracikId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);  
        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST['item_nama']); 
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['jumlah']));  
        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['detail_total']));
        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['detail_jual'])); 
        $dbValue[7] = QuoteValue(DPE_DATE,$date);
        $dbValue[8] = QuoteValue(DPE_CHAR,$usrId);
        $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['ppn']));
        $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hpp']));
        $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hargamargin']));
        $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['tuslag']));
                      
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
        $dtmodel->Insert() or die("insert  error"); 
                        
        unset($dbTable);
        unset($dbField);
        unset($dbValue);
        unset($dbKey); 

          //simpan stok_item



  

//update harga racikan
        $sql = "select sum(detail_racikan_total) as total from apotik.apotik_detail_racikan
                where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
        $rs = $dtaccess->Execute($sql);
        $racik = $dtaccess->Fetch($rs);
        //update itemnya
        $sql= "update logistik.logistik_item set item_harga_beli = ".QuoteValue(DPE_NUMERIC,$racik['total'])."
              where item_id = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
        $rs = $dtaccess->Execute($sql);
            
$addDetailPage = "racikan_new.php?q=".$batchId."&item=".$_POST['id_racikan']."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&id_penjualan_detail=".$_POST["id_penjualan_detail"];
header('location:'.$addDetailPage);
}
 }
//combo satuan jual
$sql = "select * from logistik.logistik_item_satuan where satuan_tipe ='J'
        order by satuan_nama asc";
$rs = $dtaccess->Execute($sql);
$dataSatuan = $dtaccess->FetchAll($rs);

$sql = "select * from apotik.apotik_jenis_racikan 
        order by jenis_racikan_nama asc";
$jenisRacikan = $dtaccess->FetchAll($sql);

//combo dosis
$sql = "select * from apotik.apotik_obat_petunjuk
        order by petunjuk_nama asc";
$rs = $dtaccess->Execute($sql);
$dataDosis = $dtaccess->FetchAll($rs);

//combo aturan minum
$sql = "select * from apotik.apotik_aturan_minum
        order by aturan_minum_nama asc";
$rs = $dtaccess->Execute($sql);
$dataAtMinum = $dtaccess->FetchAll($rs);

//combo jam aturan pakai
$sql = "select * from apotik.apotik_jam_aturan_pakai
        order by jam_aturan_pakai_nama asc";
$rs = $dtaccess->Execute($sql);
$dataJamPakai = $dtaccess->FetchAll($rs);

//combo aturan pakai
$sql = "select * from apotik.apotik_aturan_pakai
        order by aturan_pakai_nama asc";
$rs = $dtaccess->Execute($sql);
$dataAtPakai = $dtaccess->FetchAll($rs);

$sql = "select item_nama from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$_GET['item']);
$dataItem = $dtaccess->Fetch($sql);

 ?>
 <!DOCTYPE html>
<html lang="en">
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.11.3.min.js"></script>
<script language="JavaScript">
function GantiHarga(){
  var hargajual = document.getElementById('detail_jual').value.toString().replace(/\,/g,"")*1;
  var jumlah = document.getElementById('jumlah').value.toString().replace(/\,/g,"")*1;
  var ppnpokok = document.getElementById('ppnpokok').value.toString().replace(/\,/g,"")*1;
  var tslgpersen = document.getElementById('tslgpersen').value.toString().replace(/\,/g,"")*1;
  var hmargin = document.getElementById('hmargin').value.toString().replace(/\,/g,"")*1;
  var itemhpp = document.getElementById('itemhpp').value.toString().replace(/\,/g,"")*1;
    
    hargajual = hargajual * 1.1;
    totaldetail = hargajual*jumlah;
    j_jumlah = jumlah;
    ppn = ppnpokok * jumlah;
    tuslag = (tslgpersen/100) * hargajual*jumlah;
    hargamargin = hmargin * jumlah;
    totalAll = parseInt(hargamargin) + parseInt(ppn) + parseInt(tuslag);
    hpp = itemhpp * jumlah;

    console.log(hargamargin+" "+ppn+" "+tuslag);
   
  document.getElementById('j_jumlah').value = formatCurrency(j_jumlah);
 document.getElementById('detail_total').value = formatCurrency(parseInt(totalAll));
 document.getElementById('hargamargin').value = formatCurrency(parseInt(hargamargin));
 document.getElementById('ppn').value = formatCurrency(parseInt(ppn));
 document.getElementById('tuslag').value = formatCurrency(parseInt(tuslag));
 document.getElementById('hpp').value = formatCurrency(parseInt(hpp));
    
}
function GantiDetail(){
  var hargajual = document.getElementById('penjualan_detail_total').value.toString().replace(/\,/g,"")*1;
  var jumlah = document.getElementById('penjualan_detail_jumlah').value.toString().replace(/\,/g,"")*1;
  var jenis = document.getElementById('jenisracikan').value.toString();

  	if(jenis == 1){
  		hargasatuan = hargajual*jumlah;
  	}
  	else{
  		hargasatuan = hargajual/jumlah;
  	}
    
   
 document.getElementById('penjualan_detail_harga_jual').value = formatCurrency(parseInt(hargasatuan));
    
}

function Ubahjenis(jenis){
	var hargajual = document.getElementById('penjualan_detail_total').value.toString().replace(/\,/g,"")*1;
  	var jumlah = document.getElementById('penjualan_detail_jumlah').value.toString().replace(/\,/g,"")*1;

  	if(jenis == 1){
  		hargasatuan = hargajual*jumlah;
  	}
  	else{
  		hargasatuan = hargajual/jumlah;
  	}

  	document.getElementById('penjualan_detail_harga_jual').value = formatCurrency(parseInt(hargasatuan));
}

</script>
  <?php require_once($LAY."header.php") ?>
  <script type="text/javascript">
	 $(document).ready(function(){
		 $('#id_racikann').select2();
		//auto complete
		$('#item_nama').autocomplete({
		  serviceUrl: 'get_obat.php?id_gudang=<?=$theDep?>',
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
			                hpp: item.hpp,
			                item_harga_jual: item.item_harga_jual,
			                item_tuslag: item.item_tuslag,
			                batch_id: item.batch_id,
			                batch_no: item.batch_no,
			                ppn: item.ppn,
			                batch_tgl_jatuh_tempo: item.batch_tgl_jatuh_tempo,
			                tuslag: item.tuslag,
			                hmargin: item.item_harga_margin,

			                // untuk alert stok
			                item_stok_alert: item.item_stok_alert,
			                stok_batch_dep_saldo: item.stok_batch_dep_saldo,
						} 
					};
				})
			};
		  },
		  onSelect: function (suggestion) {
			$('#item_nama').val(suggestion.data.item_nama);
			$('#item_detail').val(suggestion.data.item_id);
			$('#detail_jual').val(suggestion.data.item_harga_jual);
			$('#ppnpokok').val(suggestion.data.ppn);
			$('#tslgpersen').val(suggestion.data.tuslag);
			$('#hmargin').val(suggestion.data.hmargin);
			$('#itemhpp').val(suggestion.data.hpp);
		  }
		});
	});
  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Racikan</h3>
              </div>
            </div>
               <div class="clearfix"></div>
               <!-- row filter -->
               <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Input Racikan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
                        <div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Racikan</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                        <select class="form-control" name="id_racikan" id="id_racikan" class="inputField"  >
                              <option value="">[- Pilih Racikan -]</option>
                            <?php for($i=0,$n=count($dataRacikan);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataRacikan[$i]["nama_racikan_id"];?>" <?php if($_POST["id_racikan"]==$dataRacikan[$i]["nama_racikan_id"]) echo "selected";?>><?php echo $dataRacikan[$i]["nama_racikan_nama"];?></option>
                       <?php } ?>               
                         </select>
						 
                        </div>
                    </div>  
                        <br><br><br>
                        <div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">&nbsp;</label>
                              <input type="submit" name="btnPilih" value="Pilih Racikan" class="btn btn-success" />
                              <input type="submit" name="btnNew" value="Tambah Master Racikan" class="btn btn-primary"/>
                              <input type="button" name="btnBack" id="btnBack" value="Kembali" class="btn btn-default" onClick="document.location.href='<? echo $backPage;?>'"/>
                          </div>
                        </div>
                         <div class="clearfix"></div>
                                   <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
                                  <input type="hidden" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $kode;?>" />
                                  <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $idReg;?>" />
                                  <input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
                                  <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $pembayaranId;?>" />
                                  <input type="hidden" name="jenis_id" id="jenis_id" value="<?php echo $_POST['jenis_id'];?>" />

                         </form>
                  </div>
                </div>
              </div>
            </div>
               <!-- row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
                            <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                              <th class="column-title">Hapus</th>
                              <th class="column-title">Nama Ingredient</th>
                              <th class="column-title">Harga Jual</th>
                              <th class="column-title">Quantity</th>
                              <th class="column-title">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                          <?php for($i=0,$n=count($dataDetRacikan);$i<$n;$i++) { ?>
                          <tr>
                            <td><?php echo '<a href="'.$thisPage.'?del=1&id='.$dataDetRacikan[$i]["detail_racikan_id"].'&transaksi='.$enc->Encode($penjualanId).'&kode='.$enc->Encode($kode).'&idreg='.$enc->Encode($idReg).'&id_pembayaran='.$pembayaranId.'&item='.$racikanId.'&rinci='.$dataDetRacikan[$i]["id_item"].'&id_penjualan_detail='.$_GET['id_penjualan_detail'].'"><img hspace="2" width="20" height="20" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"/></a>'; ?></td>
                            <td><? echo $dataDetRacikan[$i]["item_nama"]?></td>
                            <td><? echo currency_format($dataDetRacikan[$i]["item_harga_jual"]);?></td>
                            <td><? echo ($dataDetRacikan[$i]["detail_racikan_jumlah"]);?></td>
                            <td><? echo $dataDetRacikan[$i]["detail_racikan_total"];?></td>
                          </tr>
                         <? }?>                      
                          <tr class="even pointer">
                            <td class=" " colspan="2"><!--a href="<?php echo $findPage1;?>?jenis_id=<? echo $_POST["jenis_id"];?>&TB_iframe=true&height=550&width=900&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat"-->
                        <?php echo $view->RenderTextBox("item_nama","item_nama","30","100",$_POST["item_nama"],"inputField", "",false);?>
                      </td>
                            <td class=" ">
                              <?php echo $view->RenderTextBox("detail_jual","detail_jual","5","10",$_POST["detail_jual"],"curedit", null,true,null);?>
                            </td> 
                            <td class=" "><?php echo $view->RenderTextBox("jumlah","jumlah","5","10","","curedit","",true,'onChange=GantiHarga(this)');?></td>  
                            <td class=" "><?php echo $view->RenderTextBox("detail_total","detail_total","5","10",$_POST["detail_total"],"curedit", null,true,null);?>
                              <input type="hidden" name="j_jumlah" id="j_jumlah" value="<?php echo $_POST["j_jumlah"];?>" /> 
                              <input type="hidden" name="id_penjualan_detail" id="id_penjualan_detail" value="<?php echo $_GET["id_penjualan_detail"];?>" /> 
                              <input type="hidden" id="itemhpp" />
                              <input type="hidden" name="hpp" id="hpp" />
                              <input type="hidden" id="ppnpokok" />
                              <input type="hidden" name="ppn" id="ppn" />
                              <input type="hidden" id="tslgpersen" />
                              <input type="hidden" name="tuslag" id="tuslag" />
                              <input type="hidden" id="hmargin" />
                              <input type="hidden" name="hargamargin" id="hargamargin" />
                            <input type="submit" name="btnSimpanDet" value="Simpan Detail" class="submit"/></td>
                          </tr>
                           <input type="hidden" name="item_detail" id="item_detail" value="<?php echo $_POST["item_detail"];?>" />                        
                          <input type="hidden" name="id_racikan" id="id_racikan" value="<?php echo $racikanId;?>" />
                           <input type="hidden" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $kode;?>" />
                                  <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $idReg;?>" />
                                  <input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
                                  <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $pembayaranId;?>" />

                      </tbody>
                    </table> 
                           <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      	<tr><th>Nama</th><th><input type="text" class="form-control" name="racikan_nama" id="racikan_nama" value="<?php echo $dataItem['item_nama']; ?>"><!-- <?php echo $namaRacikan;?> --></th></tr>
                        <tr><th>Satuan Jual</th><th><select class="form-control" name="id_satuan_jual" id="id_satuan_jual" class="inputField"  >
                              <option value="">[- Pilih Satuan -]</option>
                            <?php for($i=0,$n=count($dataSatuan);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataSatuan[$i]["satuan_id"];?>" <?php if($dataTable["id_satuan_jual"]==$dataSatuan[$i]["satuan_id"]) echo "selected";?>><?php echo $dataSatuan[$i]["satuan_nama"];?></option>
                       <?php } ?>               
                         </select>
                        </th></tr>
                        <?php 
                          if ($dataTable["penjualan_detail_jumlah"] != '') { $_POST["penjualan_detail_jumlah"] = $dataTable["penjualan_detail_jumlah"]; }
                          if ($dataTable["penjualan_detail_harga_jual"] != '') { $_POST["penjualan_detail_harga_jual"] = $dataTable["penjualan_detail_harga_jual"]; }
                          // if ($dataTable["penjualan_detail_total"] != '') { 
                          //   $total_t = $_POST['penjualan_detail_jumlah'] * $_POST["penjualan_detail_harga_jual"];
                          //   $_POST['penjualan_detail_total'] = $total_t;
                          // }
                          // if ($dataTable["penjualan_detail_total"] != '') { $_POST["penjualan_detail_total"] = $dataTable["penjualan_detail_total"]; }
                        ?>	<tr>
	                        	<th>Jenis Racikan</th>
	                        	<th>
	                        		<select name="jenisracikan" id="jenisracikan" class="form-control" onchange="Ubahjenis(this.value)">
	                        			<option value="">[-Pilih Jenis Racikan-]</option>
	                        			<?php
	                        			for($i = 0; $i < count($jenisRacikan); $i++){
	                        				?>
	                        				<option value="<?=$jenisRacikan[$i]['jenis_racikan_id']?>"><?=$jenisRacikan[$i]['jenis_racikan_nama']?></option>
	                        				<?php
	                        			}
	                        			?>
	                        		</select>
	                        </th>
                        	</tr>
                            <tr><th>Quantity</th><th><?php echo $view->RenderTextBox("penjualan_detail_jumlah","penjualan_detail_jumlah","5","10",$_POST["penjualan_detail_jumlah"],"curedit","",true,'onChange=GantiDetail(this)');?>
                              </th></tr>
                            <tr><th>Harga Satuan</th><th><?php echo $view->RenderTextBox("penjualan_detail_harga_jual","penjualan_detail_harga_jual","5","10",$_POST["penjualan_detail_harga_jual"],"curedit",true,null);?></th></tr>
                            <tr><th>Total</th><th><?php echo $view->RenderTextBox("penjualan_detail_total","penjualan_detail_total","5","10",$_POST["penjualan_detail_total"],"curedit",true,null);?></th></tr>
                            <tr><th>Dosis</th><th><select class="form-control" name="id_petunjuk" id="id_petunjuk" class="inputField"  >
                              <option value="">[- Pilih Dosis -]</option>
                            <?php for($i=0,$n=count($dataDosis);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataDosis[$i]["petunjuk_id"];?>" <?php if($dataTable["id_petunjuk"]==$dataDosis[$i]["petunjuk_id"]) echo "selected";?>><?php echo $dataDosis[$i]["petunjuk_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                            <tr><th>Aturan Pakai</th><th><select class="form-control" name="id_aturan_pakai" id="id_aturan_pakai" class="inputField"  >
                              <option value="">[- Pilih Aturan Pakai -]</option>
                            <?php for($i=0,$n=count($dataAtPakai);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataAtPakai[$i]["aturan_pakai_id"];?>" <?php if($dataTable["id_aturan_pakai"]==$dataAtPakai[$i]["aturan_pakai_id"]) echo "selected";?>><?php echo $dataAtPakai[$i]["aturan_pakai_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                            <tr><th>Aturan Minum</th><th><select class="form-control" name="id_aturan_minum" id="id_aturan_minum" class="inputField"  >
                              <option value="">[- Pilih Aturan Minum -]</option>
                            <?php for($i=0,$n=count($dataAtMinum);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataAtMinum[$i]["aturan_minum_id"];?>" <?php if($dataTable["id_aturan_minum"]==$dataAtMinum[$i]["aturan_minum_id"]) echo "selected";?>><?php echo $dataAtMinum[$i]["aturan_minum_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                            <tr><th>Jam Aturan Pakai</th><th><select class="form-control" name="id_jam_aturan_pakai" id="id_jam_aturan_pakai" class="inputField"  >
                              <option value="">[- Pilih Jam Aturan Pakai -]</option>
                            <?php for($i=0,$n=count($dataJamPakai);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataJamPakai[$i]["jam_aturan_pakai_id"];?>" <?php if($dataTable["id_jam_aturan_pakai"]==$dataJamPakai[$i]["jam_aturan_pakai_id"]) echo "selected";?>><?php echo $dataJamPakai[$i]["jam_aturan_pakai_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                         <input type="hidden" name="id_penjualan_detail" value="<?php echo $_GET['id_penjualan_detail']; ?>" placeholder="">
                     </table>
                     
                    <table><tr><td><input type="submit" name="btnSimpanRacik" value="Simpan Racikan" class="submit"/></td></tr></table>
                    </form> 

                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!--page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
