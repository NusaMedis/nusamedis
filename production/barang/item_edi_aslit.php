<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $skr = date("d-m-Y");
     $userData = $auth->GetUserData();
     $monthName = array("--","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");     
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   
	   $viewPage = "item_view.php";
     $editPage = "item_edit.php";
	   $findPage = "akun_find.php?";
	   
	  /* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
         echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     }  */
     
     
       $sql = "select * from global.global_departemen";
$dataDep = $dtaccess->Fetch($sql);
     
     if($_GET["klinik"]) { 
          $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
          $_POST["klinik"] = $_POST["klinik"]; 
      }else if($_GET["tambah"]) { 
          $_POST["klinik"] = $_GET["tambah"]; 
      }else if(!$_POST["klinik"]) { 
          $_POST["klinik"] = $depId; 
      }
      $klinik = $_POST["klinik"]; 

	   // cek konfigurasi --
	   $sql = "select * from logistik.logistik_konfigurasi where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     $_POST["id_gudang"] = $gudang["konf_gudang"];
	   
	   $plx = new expAJAX("CheckDataCustomerTipe,GetCombo,GetComboSatuanBeli,GetComboSatuanJual,GetComboSup");
	
    /* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  */        
     
     $PageKat= "page_kat.php";
     $PageSatuanBeli = "page_satuan_beli.php";
     $PageSatuanJual = "page_satuan_jual.php";
     $PageSup = "page_sup.php";
	  
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
	   if(!$_POST["item_tipe_jenis"])  $_POST["item_tipe_jenis"] = "2";
	   
	   if($_GET["id_kategori"]) $_POST["id_kategori"] = $_GET["id_kategori"];
	   elseif(!$_POST["id_kategori"]) $_POST["id_kategori"] = "1"; 
	   
    $lokasi = $ROOT."gambar/item";

    function GetCombo() 
    {
          global $dtaccess, $userData,$lokasi,$view,$klinik;
          $sql = "select * from logistik.logistik_grup_item where item_flag = 'M' and id_dep=".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataKatItem = $dtaccess->FetchAll($rs);
          $opt_kat[0] = $view->RenderOption("--","[Pilih Kategori]",$show);
          for($i=1,$n=count($dataKatItem);$i<=$n;$i++)
          { 
            unset($show);
            if($dataKatItem[$i-1]["grup_item_id"] == $_POST["id_kategori"]) $show="selected";
            $opt_kat[$i] = $view->RenderOption($dataKatItem[$i-1]["grup_item_id"],$dataKatItem[$i-1]["grup_item_nama"],$show);
          }
          
          return $view->RenderComboBox("id_kategori","id_kategori",$opt_kat,"inputField");
       }

     function GetComboSatuanBeli() 
     {
          global $dtaccess, $userData,$lokasi,$view,$klinik;
          $sql = "select * from logistik.logistik_item_satuan where satuan_tipe='B' and id_dep=".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataSatuanBeli = $dtaccess->FetchAll($rs);
          $opt_satuan[0] = $view->RenderOption("--","[Pilih Satuan Beli]",$show);
          for($i=1,$n=count($dataSatuanBeli);$i<=$n;$i++)
          { 
            unset($show);
            if($dataSatuanBeli[$i-1]["satuan_id"] == $_POST["id_satuan_beli"]) $show="selected";
            $opt_satuan[$i] = $view->RenderOption($dataSatuanBeli[$i-1]["satuan_id"],$dataSatuanBeli[$i-1]["satuan_nama"]."(".$dataSatuanBeli[$i-1]["satuan_jumlah"].")",$show);
          }
          
          return $view->RenderComboBox("id_satuan_beli","id_satuan_beli",$opt_satuan,"inputField");
     }
     
          function GetComboSatuanJual() {
          global $dtaccess, $userData,$lokasi,$view,$klinik;
          $sql = "select * from logistik.logistik_item_satuan where satuan_tipe='J' and id_dep=".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataSatuanJual = $dtaccess->FetchAll($rs);
          $opt_satuan_jual[0] = $view->RenderOption("--","[Pilih Satuan Jual]",$show);
          for($i=1,$n=count($dataSatuanJual);$i<=$n;$i++)
          { 
            unset($show);
            if($dataSatuanJual[$i-1]["satuan_id"] == $_POST["id_satuan_jual"]) $show="selected";
            $opt_satuan_jual[$i] = $view->RenderOption($dataSatuanJual[$i-1]["satuan_id"],$dataSatuanJual[$i-1]["satuan_nama"]."(".$dataSatuanJual[$i-1]["satuan_jumlah"].")",$show);
          }
          
          return $view->RenderComboBox("id_satuan_jual","id_satuan_jual",$opt_satuan_jual,"inputField");
     }
     
        function GetComboSup() 
        {
         global $dtaccess, $userData,$lokasi,$view,$klinik;
         $sql = "select * from global.global_supplier where id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by sup_nama";
         $rs = $dtaccess->Execute($sql);
         $dataSup = $dtaccess->FetchAll($rs);
         $opt_sup[0] = $view->RenderOption("--","[Pilih Supplier]",$show);
          for($i=1,$n=count($dataSup);$i<=$n;$i++)
          { 
            unset($show);
            if($dataSup[$i-1]["sup_id"] == $_POST["id_sup"]) $show="selected";
            $opt_sup[$i] = $view->RenderOption($dataSup[$i-1]["sup_id"],$dataSup[$i-1]["sup_nama"],$show);
         }
          
          
          return $view->RenderComboBox("id_sup","id_sup",$opt_sup,"inputField");
        } 
                                          
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.item_id FROM logistik.logistik_item a 
                    WHERE upper(a.item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataitem = $dtaccess->Fetch($rs);
          
		return $dataitem["item_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["item_id"])  $itemId = & $_POST["item_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $itemId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from logistik.logistik_item a 
				          where item_id = ".QuoteValue(DPE_CHAR,$itemId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          //echo $sql; 
          $_POST["item_nama"] = $row_edit["item_nama"];
          $_POST["item_satuan"] = $row_edit["item_satuan"];
          $_POST["item_harga_beli"] = $row_edit["item_harga_beli"];
          $_POST["item_harga_jual"] = $row_edit["item_harga_jual"];
          $_POST["item_berlaku"] = $row_edit["item_berlaku"];
          $_POST["item_keterangan"] = $row_edit["item_keterangan"];
          $_POST["id_kategori"] = $row_edit["id_kategori"];
          $_POST["id_kategori_tindakan"] = $row_edit["id_kategori_tindakan"];
          $_POST["id_petunjuk"] = $row_edit["id_petunjuk"];
          $_POST["item_stok_alert"] = $row_edit["item_stok_alert"];
          $_POST["item_stok"] = $row_edit["item_stok"];
          $_POST["id_satuan_jual"] = $row_edit["id_satuan_jual"];
          $_POST["id_satuan_beli"] = $row_edit["id_satuan_beli"];
          $_POST["item_kode"] = $row_edit["item_kode"];
          $_POST["item_tipe_jenis"] = $row_edit["item_tipe_jenis"];
          $_POST["item_spesifikasi"] = $row_edit["item_spesifikasi"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $_POST["item_pic"] = $row_edit["item_pic"];
          $_POST["id_sup"] = $row_edit["id_sup"];
          $_POST["item_aktif"] = $row_edit["item_aktif"];          
          // data perkiraan //
          $_POST["id_prk"] = $row_edit["id_prk"];
        	$sql = "select * from  gl.gl_perkiraan where id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_prk =".QuoteValue(DPE_CHAR,$_POST["id_prk"]);  
        	$rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);     
	        $dataPerkiran = $dtaccess->Fetch($rs);
	        $_POST["prk_id"] = $dataPerkiran["id_prk"];
	        $_POST["prk_nama"] = $dataPerkiran["nama_prk"];
	        $_POST["prk_no"] = $dataPerkiran["no_prk"];
	        
	        // data batch //
        	$sql = "select * from  logistik.logistik_item_batch where id_dep = ".QuoteValue(DPE_CHAR,$depId)." and batch_flag = 'A' and id_item =".QuoteValue(DPE_CHAR,$itemId)." order by batch_tgl_jatuh_tempo asc";  
        	$rs = $dtaccess->Execute($sql);     
	        $dataBatz = $dtaccess->Fetch($rs);
	        $_POST["batch_no"] = $dataBatz["batch_no"];
	        $_POST["batch_tgl_jatuh_tempo"] = format_date($dataBatz["batch_tgl_jatuh_tempo"]);

          $kembali = "item_view.php?kembali=".$_POST["klinik"];
          
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
      if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"]; 
        $kembali = "item_view.php?kembali=".$_POST["klinik"];
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $itemId = & $_POST["item_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
                    if (isset($_POST['item_aktif'])) {    
          $_POST['item_aktif'] ='y';    
           }else{
          $_POST['item_aktif'] ='n'; 
           } 
               $dbTable = "logistik.logistik_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_nama";
			         $dbField[2] = "item_satuan";
			         $dbField[3] = "item_harga_beli";
			         $dbField[4] = "item_harga_jual";
			         $dbField[5] = "item_keterangan";
			         $dbField[6] = "item_berlaku";
			         $dbField[7] = "id_kategori";
			         $dbField[8] = "id_petunjuk";
			         $dbField[9] = "item_stok_alert";
			         $dbField[10] = "id_satuan_beli";
			         $dbField[11] = "item_kode";
			         $dbField[12] = "item_tipe_jenis";
			         $dbField[13] = "item_spesifikasi";
			         $dbField[14] = "id_dep";
			         $dbField[15] = "id_satuan_jual";
               $dbField[16] = "item_stok";
               $dbField[17] = "id_sup";
               $dbField[18] = "id_kategori_tindakan";
               $dbField[19] = "id_prk";
               $dbField[20] = "item_flag";
               $dbField[21] = "item_aktif";
               if ($_POST["item_pic"]) $dbField[22] = "item_pic";
               
               // buat mempermudah waktu cek masa berlaku --
               if($_POST["item_berlaku_bulan"]=='1') {
               $bln = "01";
               }elseif($_POST["item_berlaku_bulan"]=='2') {
               $bln = "02";
               }elseif($_POST["item_berlaku_bulan"]=='3') {
               $bln = "03";
               }elseif($_POST["item_berlaku_bulan"]=='4') {
               $bln = "04";
               }elseif($_POST["item_berlaku_bulan"]=='5') {
               $bln = "05";
               }elseif($_POST["item_berlaku_bulan"]=='6') {
               $bln = "06";
               }elseif($_POST["item_berlaku_bulan"]=='7') {
               $bln = "07";
               }elseif($_POST["item_berlaku_bulan"]=='8') {
               $bln = "08";
               }elseif($_POST["item_berlaku_bulan"]=='9') {
               $bln = "09";
               }elseif($_POST["item_berlaku_bulan"]=='10') {
               $bln = "10";
               }elseif($_POST["item_berlaku_bulan"]=='11') {
               $bln = "11";
               }elseif($_POST["item_berlaku_bulan"]=='12') {
               $bln = "12";
               }
               
			         $berlakunya = $bln."-".$_POST["item_berlaku_tahun"];
               if(!$itemId) $itemId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_nama"]); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_satuan"]); 
			         $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"])); 
			         $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_jual"])); 
			         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["item_keterangan"]); 
			         $dbValue[6] = QuoteValue(DPE_CHAR,$berlakunya); 
			         $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
			         $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
			         $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok_alert"]));
			         $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_satuan_beli"]);
			         $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["item_kode"]);
			         $dbValue[12] = QuoteValue(DPE_NUMERIC,'2');
			         $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["item_spesifikasi"]);
			         $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
			         $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_satuan_jual"]);
			         $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"]));
			         $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["id_sup"]);
			         $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan"]);
               $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["prk_id"]);
               $dbValue[20] = QuoteValue(DPE_CHAR,'M');               
               $dbValue[21] = QuoteValue(DPE_CHAR,$_POST['item_aktif']);
               if ($_POST["item_pic"]) $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["item_pic"]);
               
               
			         //print_r($dbValue);
			         //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
              
              // jika input data barang baru // 
              if ($_x_mode == "New") {
            
        /*      // insert data stok item batch //
              $dbTable = "logistik.logistik_item_batch";
              $dbField[0]  = "batch_id";   // PK
              $dbField[1]  = "batch_no";
              $dbField[2]  = "batch_create";    
              $dbField[3]  = "batch_tgl_jatuh_tempo";
              $dbField[4]  = "batch_stok_saldo";
              $dbField[5]  = "batch_flag";
              $dbField[6]  = "id_item";
              $dbField[7]  = "id_dep";
              $dbField[8]  = "id_gudang";
              
              $batchId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["batch_no"]);
              $dbValue[2] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
              $dbValue[3] = QuoteValue(DPE_CHAR,date_db($_POST["batch_tgl_jatuh_tempo"]));   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"])); 
              $dbValue[5] = QuoteValue(DPE_CHAR,'A');
              $dbValue[6] = QuoteValue(DPE_CHAR,$itemId); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("insert  error");
              	
              unset($dtmodel);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);
            
        */    
              // insert data stok item dahulu //
              $dbTable = "logistik.logistik_stok_item";
              $dbField[0]  = "stok_item_id";   // PK
              $dbField[1]  = "stok_item_jumlah";
              $dbField[2]  = "id_item";    
              $dbField[3]  = "id_gudang";
              $dbField[4]  = "stok_item_flag";
              $dbField[5]  = "stok_item_create";
              $dbField[6]  = "stok_item_saldo";
              $dbField[7]  = "id_dep";
              $dbField[8]  = "stok_item_hpp";

              
              $stokItemId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
              $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"]));
              $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
              $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);   //sesuai konfigurasi apotik 
              $dbValue[4] = QuoteValue(DPE_CHAR,'A');               // A adalah saldo
              $dbValue[5] = QuoteValue(DPE_DATE,format_date($_POST["tgl_awal"])." ".date('H:i:s'));
              $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"])); 
			        $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"])); 

              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("insert  error");
              	
              unset($dtmodel);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);             
        
          $dbTable = "logistik.logistik_stok_dep";
          $dbField[0]  = "stok_dep_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "stok_dep_saldo";
          $dbField[3]  = "stok_dep_create";
          $dbField[4]  = "stok_dep_tgl";    
          $dbField[5]  = "id_gudang";
          $dbField[6]  = "id_dep";
          
          $stokDepId = $dtaccess->GetTransID();

          $dbValue[0] = QuoteValue(DPE_CHAR,$stokDepId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);//QuoteValue(DPE_NUMERIC,StripCurrency($_POST['txtJumlah']));
          $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_stok"]));    
          $dbValue[3] = QuoteValue(DPE_DATE,format_date($_POST["tgl_awal"])." ".date('H:i:s'));
          $dbValue[4] = QuoteValue(DPE_DATE,format_date($_POST["tgl_awal"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
			    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);  
        
             } 
               $kembali = "item_view.php?kembali=".$_POST["klinik"];
               
               header("location:".$kembali);
               exit();
          } 
     }
     
     //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$klinik."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
 
  if ($_GET["del"]) {
           $itemId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$itemId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

           $kembali = "item_view.php?kembali=".$_POST["klinik"];
               
           header("location:".$kembali);
           exit(); 
     }
       
      //-- bikin combo box untuk satuan Beli item --//
     $sql = "select * from logistik.logistik_item_satuan where id_dep like '".$_POST["klinik"]."%' and satuan_tipe ='B' order by satuan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);  
     
     unset($opt_satuan_beli);$i=1;
     $opt_satuan_beli[0] = $view->RenderOption("--","[Pilih Satuan Beli]",$show);
     while($data_satuan_beli = $dtaccess->Fetch($rs)){
     unset($show);
        if($data_satuan_beli["satuan_id"] == $_POST["id_satuan_beli"]) $show="selected";
        $opt_satuan_beli[$i] = $view->RenderOption($data_satuan_beli["satuan_id"],$data_satuan_beli["satuan_nama"],$show);
        $i++;
     }
     
           //-- bikin combo box untuk satuan Jual item --//
     $sql = "select * from logistik.logistik_item_satuan where id_dep like '".$_POST["klinik"]."%' and satuan_tipe ='J' order by satuan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     
     unset($opt_satuan_jual);$i=1;
     $opt_satuan_jual[0] = $view->RenderOption("--","[Pilih Satuan Jual]",$show);
     while($data_satuan_jual = $dtaccess->Fetch($rs)){
     unset($show);
        if($data_satuan_jual["satuan_id"] == $_POST["id_satuan_jual"]) $show="selected";
        $opt_satuan_jual[$i] = $view->RenderOption($data_satuan_jual["satuan_id"],$data_satuan_jual["satuan_nama"],$show);
        $i++;
     }
     
           //-- bikin combo box untuk jenis item --//
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'  order by jenis_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     
     unset($opt_jenis);$i=1;
     $opt_jenis[0] = $view->RenderOption("--","[Pilih Jenis]",$show);
     while($data_jenis = $dtaccess->Fetch($rs)){
     unset($show);
        if($data_jenis["jenis_id"] == $_POST["item_tipe_jenis"]) $show="selected";
        $opt_jenis[$i] = $view->RenderOption($data_jenis["jenis_id"],$data_jenis["jenis_nama"],$show);
        $i++;
     }
     
     //Kategori Tindakannya
     $sql = "select * from klinik.klinik_kategori_tindakan where id_dep = '".$_POST["klinik"]."' order by kategori_tindakan_nama ";
     $rs = $dtaccess->Execute($sql);
     $dataKatTind = $dtaccess->FetchAll($rs);
     
     //-- bikin combo box untuk kategori --//
     $sql = "select * from logistik.logistik_grup_item where item_flag='M' order by grup_item_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
// echo $sql; die();    
     unset($opt_kat);$i=1;
     $opt_kat[0] = $view->RenderOption("--","[Pilih Kategori]",$show);
     while($data_kat = $dtaccess->Fetch($rs)){
      unset($show);
        if($data_kat["grup_item_id"] == $_POST["id_kategori"]) $show="selected";
        $opt_kat[$i] = $view->RenderOption($data_kat["grup_item_id"],$data_kat["grup_item_nama"],$show);
        $i++;
 //         echo $_POST["id_kategori"];
     }
     
          //-- bikin combo box untuk Supplier --//
     $sql = "select * from global.global_supplier where id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by sup_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     
     unset($opt_sup);$i=1;
     $opt_sup[0] = $view->RenderOption("--","[Pilih Supplier]",$show);
     while($data_sup = $dtaccess->Fetch($rs)){
      unset($show);
        if($data_sup["sup_id"] == $_POST["id_sup"]) $show="selected";
        $opt_sup[$i] = $view->RenderOption($data_sup["sup_id"],$data_sup["sup_nama"],$show);
        $i++;
     }
   
     $berlaku = explode("-",$_POST["item_berlaku"]);
     //echo $berlaku[0]."-".$berlaku[1];
     unset($opt_berlaku_tahun); unset($opt_berlaku_bulan);
     unset($show);
     for($r=0;$r<10;$r++){
       unset($show);
       if($berlaku[1]=="201".$r) $show = "selected";
       $opt_berlaku_tahun[$r] = $view->RenderOption("201".$r,"201".$r,$show);
     }
     
     for($m=1;$m<=13;$m++){
       unset($show);
       if($berlaku[0]==$m) $show = "selected";
       $opt_berlaku_bulan[$m] = $view->RenderOption($m,$monthName[$m],$show);
     }
     
      if($_POST["item_pic"]) $fotoName = $lokasi."/".$row_edit["item_pic"];
      else $fotoName = $lokasi."/default_barang.jpg";
?> 


<script language="javascript" type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'item_pic.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
						
                                   document.getElementById('item_pic').value= data.file;
                                   document.img_item_item.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}

<? $plx->Run(); ?>    

function CheckDataSave(frm)
{ 
     
     if(!frm.item_nama.value){
		alert('Nama item Harus Diisi');
		frm.item_nama.focus();
          return false;
	}
  
  

     /*if(!frm.batch_tgl_jatuh_tempo.value){
		alert('Batch Tanggal Jatuh Tempo Harus Diisi');
		frm.batch_tgl_jatuh_tempo.focus();
          return false;
	}  */

	
	if(frm.x_mode.value=="New") {
  
  if(!frm.batch_tgl_jatuh_tempo.value){
		alert('Batch Tanggal Jatuh Tempo Harus Diisi');
		frm.batch_tgl_jatuh_tempo.focus();
          return false;
	} 
		/*if(CheckDataCustomerTipe(frm.item_nama.value,'type=r')){
			alert('Nama item Sudah Ada');
			frm.item_nama.focus();
			frm.item_nama.select();
			return false;
		}  */
	} 
     document.frmEdit.submit();     
}   

function getCombo() {
     GetCombo('target=dv_combo');
}

function getComboSatuanBeli() {
     GetComboSatuanBeli('target=dv_combo_satuan_beli');
}

function getComboSatuanJual() {
     GetComboSatuanJual('target=dv_combo_satuan_jual');
}

function getComboSup() {
     GetComboSup('target=dv_combo_sup');
}

</script>
<style type="text/css">
#top{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#000000), to(#000000));
background: -moz-linear-gradient(top, #000000, #000000); 
}
#footer{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#000000), to(#000000));
background: -moz-linear-gradient(top, #000000, #000000);
}
</style>
<body>
<div id="top">
<table border="0" width="100%" valign="top" >
<tr>                                                
<td width="10%" align="left" valign="top">
<a href="#" target="_blank"><img height="44px" src="<?php echo $ROOT;?>gambar/img_cfg/<?php echo $dataDep["dep_logo_aplikasi_kiri"];?>"/></a>
</td>                                                                                                             
<td width="90%" valign="top" align="right">                                                               
<span style="font-size: 27pt; color: #fff;">SETUP BARANG</span>&nbsp;&nbsp;
</td>
</tr>
</table>   
</div>
<body>
<div id="body">
<div id="scroller">

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="0" cellpadding="1" cellspacing="1">

     <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
          <td align="left" width="80%">
          <fieldset>
          <legend><strong>Identitas Barang</legend>
     <table width="100%" border="1" cellpadding="2" cellspacing="2">
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Tanggal&nbsp;</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />

               </td>
          </tr>

		 
          <tr>
               <td align="right" class="tablecontent" width="10%"><strong>Kode</strong>&nbsp;</td>
               <td width="50%" colspan="3" class="tablecontent-odd">
                 <?php echo $view->RenderTextBox("item_kode","item_kode","50","100",$_POST["item_kode"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="10%"><strong>Nama</strong>&nbsp;</td>
               <td width="50%" colspan="3" class="tablecontent-odd">
                 <?php echo $view->RenderTextBox("item_nama","item_nama","50","100",$_POST["item_nama"],"inputField", null,false);?>
               </td>
          </tr>
           <tr>
               <td align="right" class="tablecontent"><strong>Kategori Barang</strong>&nbsp;</td>
               <td colspan="2" class="tablecontent-odd" width="3%">
                  <div id="dv_combo"><?php echo GetCombo(); ?>
                  </div>
                  </td>
                  <td align="left" class="tablecontent-odd">
                  <a href="<?php echo $PageKat;?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=300&width=600&modal=true" class="thickbox" title="Tambah Satuan Jual"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Jual" alt="Tambah Satuan Jual" /></a>
               </td>
          </tr>
        <!--   <tr>
               <td align="right" class="tablecontent"><strong>Kategori Tindakan</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd" width="3%">
               <select name="id_kategori_tindakan" id="id_kategori_tindakan" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">
				       <option class="inputField" value="<?php if ($_POST["id_kategori"]=="--") echo"selected"?>">- Pilih Kategori -</option>
			      	 <?php for($i=0,$n=count($dataKatTind);$i<$n;$i++){ ?>
				    	 <option class="inputField" value="<?php echo $dataKatTind[$i]["kategori_tindakan_id"];?>" <?php if($dataKatTind[$i]["kategori_tindakan_id"]==$_POST["id_kategori_tindakan"]) echo "selected"; ?>><?php echo $dataKatTind[$i]["kategori_tindakan_nama"];?></option>
			       	 <?php } ?>
			         </select>
                  </td>
          </tr>
         <tr>
               <td align="right" class="tablecontent">Kode Perkiraan Item</td>
               <td colspan="3" class="tablecontent-odd">
               <?php echo $view->RenderTextBox("prk_no","prk_no","25","100",$_POST["prk_no"],"inputField",false,false);?>                                                    
               <?php echo $view->RenderTextBox("prk_nama","prk_nama","40","100",$_POST["prk_nama"],"inputField",false,false);?>                                        
               <input type="hidden" name="prk_id" id="prk_id" value="<?php echo $_POST["prk_id"];?>" />                                                   
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">                           
               <img src="<?php echo $ROOT;?>gambar/search.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a>
              </td>
          </tr> -->
        <!--  <tr>
               <td align="right" class="tablecontent"><strong>Jenis Tipe Pasien</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                 <?php echo $view->RenderComboBox("item_tipe_jenis","item_tipe_jenis",$opt_jenis,"inputField");?>
               </td>
          </tr> -->
          <tr >
               <td align="right" class="tablecontent">Status Item</td>
               <td colspan="3" class="tablecontent-odd">
            <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="item_aktif" id="item_aktif" value="<?php echo "y";?>" <?php if ($_POST["item_aktif"]=="y") echo "checked"; ?>/>
            <label for="item_aktif">Aktif</label>
               </td>
          </tr>
                    <tr >
               <td align="right" class="tablecontent">&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">&nbsp;</td>
          </tr>
          </table>
          </fieldset>
          </td>
          
          <td align="left" width="20%" rowspan="3">
          <fieldset>
          <legend><strong>Gambar</legend>
     <table width="100%" border="0" cellpadding="2" cellspacing="2" rowspan="3">
      <tr>
               <td>
                    <img hspace="2" width="100" height="100" name="img_item_item" id="img_item_item" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="item_pic" id="item_pic" value="<?php echo $_POST["item_pic"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="submit">
                    </td>
                    </tr>
                    <tr>
                    <td>
                    <button class="submit" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar</button>
                    <span id="loading" style="display:none;"><img width="25" height="25"  id="imgloading" src="<?php echo $ROOT;?>gambar/loading.gif"></span>
               </td>
          </tr> 
     
     </table>
     </td>
          </tr>
          </table>
          </table>
          
          
          <table width="80%" border="0" cellpadding="1" cellspacing="1">
          <tr>
          <td align="left"  width="40%">
          <!-- kanan -->
          <fieldset>
          <legend><strong>Satuan</legend>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
          <td width="30%" align="right" class="tablecontent"><strong>Satuan Beli</strong>&nbsp;</td>
               <td colspan="2" class="tablecontent-odd" width="3%">
                    <div id="dv_combo_satuan_beli"><?php echo GetComboSatuanBeli(); ?>                    
                    </div>
                    </td>
                    <td align="left" class="tablecontent-odd">
              <a href="<?php echo $PageSatuanBeli;?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Satuan Juak"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Beli" alt="Tambah Satuan Beli" /></a>
                    </td>
               </tr>
               
               <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Satuan Jual</strong>&nbsp;</td>
               <td  colspan="2" class="tablecontent-odd" width="3%">
               <div id="dv_combo_satuan_jual"><?php echo GetComboSatuanJual(); ?>
                 <div>
              </td>
                    <td align="left" class="tablecontent-odd">      
              <a href="<?php echo $PageSatuanJual;?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=200&width=450&modal=true" class="thickbox" title="Tambah Satuan Juak"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Jual" alt="Tambah Satuan Jual" /></a>                
               </td>
               </tr>
           
          </table>
          </fieldset>
          </td>
          
          
          <td align="left" width="40%">
          <!-- kiri -->
          <fieldset>
          <legend><strong>Harga</legend>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Harga Beli</strong>&nbsp;</td>
               <td width="70%" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("item_harga_beli","item_harga_beli","20","100",currency_format($_POST["item_harga_beli"]),"inputField", null,true);?>
               </td>
               </tr>
               <tr>                                                                                                                                              
              <td width="30%" align="right" class="tablecontent"><strong>Harga Jual</strong>&nbsp;</td>
               <td width="70%" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("item_harga_jual","item_harga_jual","20","100",currency_format($_POST["item_harga_jual"]),"inputField", null,true);?>
               </td>
          </tr>
          
          
          </table>
          </table>
          </fieldset>
           </td>
           </tr>
           </table>
           
           <table width="80%" border="0" cellpadding="1" cellspacing="1">
          <tr>
          <td align="left"  width="40%">
          <!-- kanan -->
          <fieldset>
          <legend><strong>Stok</legend>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
              <!-- <tr>
                   <td width="3%" align="right" class="tablecontent"><strong>Stok Awal</strong>&nbsp;</td>
                   <td width="3%" colspan="3" class="tablecontent-odd">  
                   <?php if($_x_mode=="New"){?>
                      <?php echo $view->RenderTextBox("item_stok","item_stok","20","100",currency_format($_POST["item_stok"]),"inputField", null,true);?>
                   <?php } else {  ?>
    
                      <?php echo $view->RenderTextBox("item_stok","item_stok","20","100",currency_format($_POST["item_stok"]),"inputField", "readonly", null,true);?>
                      
                   <?php } ?>
                  </td>
               </tr>  -->
          
          <tr>
               <td width="3%" align="right" class="tablecontent"><strong>Stok Alert</strong>&nbsp;</td>
               <td width="3%" rowspan="1" colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("item_stok_alert","item_stok_alert","20","100",currency_format($_POST["item_stok_alert"]),"inputField", null,true);?>
               </td>
          </tr> 
 <!--         <tr>
          <td width="3%" align="right" class="tablecontent"><strong>Supplier</strong>&nbsp;</td>
               <td class="tablecontent-odd" colspan="2" rowspan="1" width="1%">
                    <div id="dv_combo_sup"><?php echo GetComboSup(); ?>
                </div>
                </td>              
                <td class="tablecontent-odd" align="left">
                <a href="<?php echo $PageSup;?>?klinik=<?php echo $_POST["klinik"]; ?>&TB_iframe=true&height=300&width=550&modal=true" class="thickbox" title="Tambah Satuan Juak"><img src="<?php echo $ROOT;?>gambar/icon/add.png" border="0" align="middle" width="14" height="16" style="cursor:pointer" title="Tambah Satuan Juak" alt="Tambah Satuan Juak" /></a>
                 </td>
               </tr>-->
          <tr>
           <td class="tablecontent">
           <td class="tablecontent-odd" width="3%" colspan="3">
               &nbsp;
               </td>
          </tr> 
           
          </table>
          </fieldset>
          </td>
          
          
<!--          <td align="left" width="40%">
           kiri
          <fieldset>
          <legend><strong>BATCH & ED</legend>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Masa Berlaku</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderComboBox("item_berlaku_bulan","item_berlaku_bulan",$opt_berlaku_bulan,"inputField");?>&nbsp;
                  <?php echo $view->RenderComboBox("item_berlaku_tahun","item_berlaku_tahun",$opt_berlaku_tahun,"inputField");?>
               </td>
          </tr> 
          
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>No. BATCH</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
                  <?php if($_x_mode=="New"){?>
                  <?php echo $view->RenderTextBox("batch_no","batch_no","20","100",$_POST["batch_no"],"inputField", null,false);?>
                  <?php } else { ?>
                  <?php echo $view->RenderTextBox("batch_no","batch_no","20","100",$_POST["batch_no"],"inputField","readonly", null,false);?>
                  <?php } ?>
               </td>
          </tr>
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Expire Date</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
                  <?php if($_x_mode=="New"){?>
                  <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo","batch_tgl_jatuh_tempo","10","75",$_POST["batch_tgl_jatuh_tempo"],"inputField", null,false);?> (dd-mm-yyyy)
                  <?php } else { ?>
                  <?php echo $view->RenderTextBox("batch_tgl_jatuh_tempo","batch_tgl_jatuh_tempo","10","75",$_POST["batch_tgl_jatuh_tempo"],"inputField","readonly", null,false);?> (dd-mm-yyyy)
                  <?php } ?>
               </td>
          </tr>
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Keterangan</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextArea("item_keterangan","item_keterangan","1","20",$_POST["item_keterangan"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td width="30%" align="right" class="tablecontent"><strong>Keterangan</strong>&nbsp;</td>
               <td width="70%" colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextArea("item_keterangan","item_keterangan","1","20",$_POST["item_keterangan"],"inputField", null,false);?>
               </td>
          </tr>     
          </table>
          </table>
          </fieldset>
           </td>
           </tr>
           </table> -->
                    
          <tr>
              
           <table width="80%" border="0" cellpadding="1" cellspacing="1">     
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$kembali."';\"");?>                    
               </td>
          </tr>
     </table>
     </td>
</tr>
</table> </div></div>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.item_kode.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("item_id","item_id",$itemId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>

		 </div>
		 </div>
			

