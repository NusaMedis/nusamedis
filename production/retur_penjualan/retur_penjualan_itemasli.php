<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");    
     require_once($LIB."tampilan.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();
  	 $userData = $auth->GetUserData();
  	 $thisPage = "retur_penjualan_item.php";
  	 $findPage = "retur_penjualan_find.php?";
  	 $backPage = "retur_penjualan_view.php"; 
	   $findPasien = "nota_find.php";
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $addObat = "item_edit_lama.php?";
     $PageSup = "page_sup.php";
	   $userName = $auth->GetUserName();	   
	   $table = new InoTable("table","100%","left");  
 
$userName = $auth->GetUserName();
	     
    	if($auth->IsAllowed()===1){
    	    header("Location:".$ROOT."login.php");
    	    exit();
    	} 
       if(!$auth->IsAllowed("apo_ret_retjual",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_ret_retjual",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 
     
	
    	//$shutdownMode=0;
    	if($_x_mode=="New") $privMode = PRIV_CREATE;
    	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    	else $privMode = PRIV_DELETE;    
      
      if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
      	else $_x_mode = "New";
	
	  	//ambil data penjualan baru
      if($_GET["id_beli"] || $_GET["id"])  
      {
        $_x_mode = "Edit";
        //echo $_x_mode;
      } 
   
   if($_POST["beli_nota"] || $_POST["urut"]) { 
   
    $nota = $_POST["beli_nota"];
    $urut = $_POST["urut"]; 
    //echo "kepet".$nota;
   } else {
   
    $sql = "select max(retur_penjualan_urut) as urut from logistik.logistik_retur_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId);
    $lastKode = $dtaccess->Fetch($sql);
    $tgl = explode("-",$skr);
    $nota = "RJL.".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
    $urut = $lastKode["urut"]+1;
    //echo $urut; 
    //echo "semmepet".$nota; 
    $_POST["beli_nota"] = $nota;          
   }

   if($_POST["retur_penjualan_id"]) $returId = $_POST["retur_penjualan_id"];
   if($_POST["penjualan_id"]) $_POST["penjualan_id"] = $_POST["penjualan_id"];
	   
     $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     
     if($gudang["conf_gudang_obat"]=='L'){
          $theDep = "1";
     }else{
          $theDep = $auth->GetNamaLogistik();
     }
     
  if ($_GET["transaksi"] || $_GET["id_penjualan_trans"]) 
  { 
   $_x_mode = "Edit";
   $returId = $enc->Decode($_GET["transaksi"]);
   $dataPenjualanId = $enc->Decode($_GET["id_penjualan_trans"]);
   
   $sql = "select * from logistik.logistik_retur_penjualan a 
           left join apotik.apotik_penjualan b on b.penjualan_id = a.id_penjualan
           left join global.global_customer_user c on c.cust_usr_id = b.id_cust_usr
           where a.retur_penjualan_id = ".QuoteValue(DPE_CHAR,$returId);       
   $rs = $dtaccess->Execute($sql,DB_SCHEMA);
   $dataRetur = $dtaccess->Fetch($rs);
   
   $_POST["beli_nota"] = $dataRetur["retur_penjualan_nomor"];
   $_POST["retur_penjualan_tgl"] = format_date($dataRetur["retur_penjualan_tgl"]);
   $_POST["klinik"] = $dataRetur["id_dep"];
   $_POST["retur_penjualan_keterangan"] = $dataRetur["retur_penjualan_keterangan"];
   $_POST["cust_usr_alamat"] = $dataRetur["cust_usr_alamat"];
   $_POST["cust_usr_nama"] = $dataRetur["cust_usr_nama"];
   $_POST["no_nota"] = $dataRetur["penjualan_nomor"];
   $_POST["id_reg"] = $dataRetur["id_reg"];
         
   $sql = "select *,b.item_nama,b.item_kode,c.jenis_nama,d.petunjuk_nama
           from apotik.apotik_penjualan_detail a
           left join logistik.logistik_item b on a.id_item=b.item_id 
           left join global.global_jenis_pasien c on b.item_tipe_jenis=c.jenis_id
           left join apotik.apotik_obat_petunjuk d on a.id_petunjuk=d.petunjuk_id
           where a.id_penjualan = ".QuoteValue(DPE_CHAR,$dataPenjualanId)."
           order by a.penjualan_detail_create desc";       
   $rs_edit = $dtaccess->Execute($sql);
   $dataTable = $dtaccess->FetchAll($rs_edit);
    
   for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
         $totalHarga +=$dataTable[$i]["item_harga_jual"]*$dataTable[$i]["retur_penjualan_detail_jumlah"];
   }
//   echo $totalHarga;
   $kembali = "retur_penjualan_view.php?kembali=".$_POST["klinik"];
  
  }
 
   if ($_x_mode == "New" && !$returId)  //Jika menyimpan penjualan
    {
              $dbTable = "logistik.logistik_retur_penjualan";
               
               $dbField[0] = "retur_penjualan_id";   // PK
               $dbField[1] = "retur_penjualan_nomor";
               $dbField[2] = "retur_penjualan_supplier";
               $dbField[3] = "retur_penjualan_tgl";
               $dbField[4] = "retur_penjualan_when_update";
               $dbField[5] = "retur_penjualan_who_create";
               $dbField[6] = "retur_penjualan_lunas";
               $dbField[7] = "id_gudang";
               $dbField[8] = "retur_penjualan_urut";
               $dbField[9] = "retur_penjualan_keterangan";
               $dbField[10] = "id_dep";
               $dbField[11] = "retur_penjualan_total"; 
               $dbField[12] = "id_reg";              
               
               if(!$_POST["retur_penjualan_tgl"]) $_POST["retur_penjualan_tgl"] =  date("d-m-Y");
               if(!$returId) $returId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR,$returId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$nota);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$sup);
               $dbValue[3] = QuoteValue(DPE_DATE,format_date($_POST["retur_penjualan_tgl"]));
               $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[5] = QuoteValue(DPE_CHAR,$userData['name']);
               $dbValue[6] = QuoteValue(DPE_CHAR,"n");
               $dbValue[7] = QuoteValue(DPE_CHAR,$theDep);
               $dbValue[8] = QuoteValue(DPE_NUMERIC,$urut);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["retur_penjualan_keterangan"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[11] = QuoteValue(DPE_NUMERIC,$totalHarga);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);	
               		                         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
                    $dtmodel->Insert() or die("insert  error");	

                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey); 
               
  } 
  
     if ($_POST["btnUpdate"] || $_POST["btnSave"]) {

     for($i=0,$n=count($_POST["item_id"]);$i<$n;$i++){      
                                  
      //cek di stok_dep ada item nya apa ga , jika ga ada maka di input jika ada update
     $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_dep =".QuoteValue(DPE_CHAR,$theDep);
     $sql .="and id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
     $sql .="order by stok_dep_create desc"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataDep = $dtaccess->Fetch($rs);

     $newStok[$i] = $dataDep["stok_dep_saldo"] + StripCurrency($_POST["sendItem".$i]);
  
      $sql = "select a.penjualan_detail_sisa, b.retur_penjualan_id from apotik.apotik_penjualan_detail a
              left join logistik.logistik_retur_penjualan b on b.id_penjualan = a.id_penjualan 
              where a.id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"][$i])."
              and b.retur_penjualan_id =".QuoteValue(DPE_CHAR,$_POST["retur_penjualan_id"]);
      $rs = $dtaccess->Execute($sql);
      $dataFkt = $dtaccess->Fetch($rs);
 
      $newFkt[$i] = $dataFkt["penjualan_detail_sisa"] - StripCurrency($_POST["sendItem".$i]);
      
      $sql = "update apotik.apotik_penjualan_detail set penjualan_detail_sisa = ".QuoteValue(DPE_NUMERIC,$newFkt[$i]);
      $sql .= " where id_penjualan  =".QuoteValue(DPE_CHAR,$_POST["penjualan_id"]);
      $sql .= " and id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
      $rs = $dtaccess->Execute($sql);  
      //echo $sql;
      //die();
      $isOke += $newFkt[$i];

      //masukkan ke penjualan detail
      $dbTable = "logistik.logistik_retur_penjualan_detail";
      $dbField[0]  = "retur_penjualan_detail_id";   // PK
      $dbField[1]  = "retur_penjualan_detail_create";    
      $dbField[2]  = "retur_penjualan_who_create";
      $dbField[3]  = "id_penjualan_retur";
      $dbField[4]  = "retur_penjualan_detail_jumlah";
      $dbField[5]  = "retur_penjualan_detail_total";
      $dbField[6]  = "retur_penjualan_detail_grandtotal";
      $dbField[7]  = "id_item";
      $dbField[8] = "id_dep";

      $fakturId = $dtaccess->GetTransID();
      $dbValue[0] = QuoteValue(DPE_CHAR,$fakturId);
      $dbValue[1] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[2] = QuoteValue(DPE_CHAR,$userData['name']);
      $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["retur_penjualan_id"]);  
			$dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["sendItem".$i]));     
			$dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["itemHarga".$i]));
			$dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["totalHargaTerima".$i]));
			$dbValue[7] = QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
			$dbValue[8] = QuoteValue(DPE_CHAR,$depId);
       
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

      $dtmodel->Insert() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
      
      //masukkan ke stok item :: Keterangan Opname Selisih
      $dbTable = "logistik.logistik_stok_item";
      $dbField[0]  = "stok_item_id";   // PK
      $dbField[1]  = "stok_item_jumlah";
      $dbField[2]  = "id_item";    
      $dbField[3]  = "id_gudang";
      $dbField[4]  = "stok_item_flag";
      $dbField[5]  = "stok_item_create";
      $dbField[6]  = "stok_item_saldo";
      $dbField[7] = "id_dep";
      
      $stokItemId = $dtaccess->GetTransID();
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["sendItem".$i]));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[4] = QuoteValue(DPE_CHAR,'M');
      $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
			$dbValue[6] = QuoteValue(DPE_NUMERIC,$newStok[$i]);       
      $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      $dtmodel->Insert() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$newStok[$i]);
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$theDep);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
      
      unset($_POST["sendItem".$i]);
      unset($_POST["itemHarga".$i]);
      unset($_POST["totalHargaTerima".$i]);
      
    }

    $sql = "select f.id_reg, g.reg_jenis_pasien,g.id_cust_usr, f.id_dokter,f.cust_usr_nama, g.id_pembayaran, a.retur_penjualan_nomor, 
            sum(retur_penjualan_detail_grandtotal) as grandtotal_retur, sum(retur_penjualan_detail_jumlah) as jumlah 
            from logistik.logistik_retur_penjualan a 
            left join logistik.logistik_retur_penjualan_detail b on b.id_penjualan_retur = a.retur_penjualan_id 
            left join logistik.logistik_item c on b.id_item = c.item_id 
            left join apotik.apotik_penjualan f on f.penjualan_id = a.id_penjualan 
            left join global.global_customer_user d on f.id_cust_usr = d.cust_usr_id 
            left join global.global_jenis_pasien e on f.id_jenis_pasien = e.jenis_id
            left join klinik.klinik_registrasi g on g.reg_id = f.id_reg
            where a.retur_penjualan_id = ".QuoteValue(DPE_CHAR,$returId);
      $sql .= " group by g.id_pembayaran,f.id_reg,g.id_cust_usr, a.retur_penjualan_nomor,g.reg_jenis_pasien,f.cust_usr_nama,f.id_dokter";
      
      $rs = $dtaccess->Execute($sql);
  $dataTotal = $dtaccess->Fetch($rs);
  
    $date = date('Y-m-d H:i:s');       
 //      echo $sql;                 
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
          $dbField[16] = "fol_hrs_bayar";
          $dbField[17] = "id_dokter";          
          $dbField[18] = "who_when_update";
          $dbField[19] = "fol_catatan";
          $dbField[20] = "fol_keterangan";
          $dbField[21] = "fol_jumlah";
          $dbField[22] = "fol_nominal_satuan";
                         
               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$dataTotal["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Retur Obat');
               $dbValue[3] = QuoteValue(DPE_NUMERIC,-(StripCurrency($dataTotal["grandtotal_retur"])));
               $dbValue[4] = QuoteValue(DPE_CHAR,'R');
               $dbValue[5] = QuoteValue(DPE_CHAR,$dataTotal["id_cust_usr"]);
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999998');
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$dataTotal["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,-(StripCurrency($dataTotal["grandtotal_retur"])));
               $dbValue[13] = QuoteValue(DPE_DATE,$date);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,-(StripCurrency($dataTotal["grandtotal_retur"])));
               $dbValue[15] = QuoteValue(DPE_CHAR,$dataTotal["id_pembayaran"]);   
               $dbValue[16] = QuoteValue(DPE_NUMERIC,-(StripCurrency($dataTotal["grandtotal_retur"])));   
               $dbValue[17] = QuoteValue(DPE_CHAR,$dataTotal["id_dokter"]);
               $dbValue[18] = QuoteValue(DPE_CHAR,$usrId);               
               $dbValue[19] = QuoteValue(DPE_CHAR,$dataTotal["retur_penjualan_nomor"]);
               $dbValue[20] = QuoteValue(DPE_CHAR,$dataTotal["cust_usr_nama"]);
               $dbValue[21] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTotal["jumlah"]));
               $dbValue[22] = QuoteValue(DPE_NUMERIC,-(StripCurrency($dataTotal["grandtotal_retur"])));
                         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
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
						$dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["retur_penjualan_detail_grandtotal"]));
						 
						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
						
						$dtmodel->Insert() or die("insert error"); 
						
						unset($dtmodel);
						unset($dbField);
						unset($dbValue);
						unset($dbKey); 
    
        $sql = " update logistik.logistik_retur_penjualan set retur_penjualan_lunas = 'y' ";
        $sql .= " where retur_penjualan_id  =".QuoteValue(DPE_CHAR,$_POST["retur_penjualan_id"]);
        $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
        
        $sql = " update logistik.logistik_retur_penjualan set id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." , id_dep = ".QuoteValue(DPE_CHAR,$depId)." , retur_penjualan_total = ".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtgrandTotale"]))." , retur_penjualan_keterangan = ".QuoteValue(DPE_CHAR,$_POST["retur_penjualan_keterangan"])." where retur_penjualan_id = ".QuoteValue(DPE_CHAR,$returId)." and id_dep = ".QuoteValue(DPE_CHAR,$theDep);
        $rs = $dtaccess->Execute($sql);      
         
        $_x_mode = "cetak" ;

   $kembali = "retur_penjualan_view.php?kembali=".$_POST["klinik"];   
   
    //   $kembali = "retur_penjualan_view.php";
   //     echo "<script>document.location.href='".$kembali."';</script>";
        
     }
   
     if($_POST["btnTampil"]) {
     
         if($_POST["id_penjualan"]) $penjualanId = $_POST["id_penjualan"];
         
          $sql = " update logistik.logistik_retur_penjualan set id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." , retur_penjualan_keterangan = ".QuoteValue(DPE_CHAR,$_POST["retur_penjualan_keterangan"])." where retur_penjualan_id = ".QuoteValue(DPE_CHAR,$returId)." and id_dep = ".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
         // echo $sql;
         // die(); 
          $sql = "select *,b.item_nama,b.item_kode,c.jenis_nama,d.petunjuk_nama
                 from apotik.apotik_penjualan_detail a
                 left join logistik.logistik_item b on a.id_item=b.item_id 
                 left join global.global_jenis_pasien c on b.item_tipe_jenis=c.jenis_id
                 left join apotik.apotik_obat_petunjuk d on a.id_petunjuk=d.petunjuk_id
                 where a.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId)."
                 order by a.penjualan_detail_create desc";       
         $rs_edit = $dtaccess->Execute($sql);
         $dataTable = $dtaccess->FetchAll($rs_edit);
    
         for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
         $totalHarga +=$dataTable[$i]["item_harga_jual"]*$dataTable[$i]["retur_penjualan_detail_jumlah"];
         }
     
     }

?>


<?php //echo $view->RenderBody("module.css",true,true,"RETUR PENJUALAN"); ?>
<br />
<?php //echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">
<script language="Javascript">

var TotalRetur = new Array(); //total semua uang retur  

function hapus() {
  if(confirm('apakah anda yakin akan menghapus barang ini???'));
  else return false;
}

function Editobat(id,id_obat,nama,jumlah) { 
	document.getElementById('retur_penjualan_detail_id').value = id;
	document.getElementById('item_id').value = id_obat;
	document.getElementById('item_nama').value = nama; 
	//document.getElementById('txtHargaSatuan').value = harga_jual; 
	document.getElementById('txtJumlah').value = jumlah; 
	//document.getElementById('txtHargaTotal').value = total;
	//document.getElementById('id_petunjuk').value = dosis; 
	document.getElementById('btn_edit').value = id_detail;   //Penjualan detail
}
                                            
function GantiJumlah(jumlah) {
     
  /*    if(document.getElementById('txtJumlah').value>document.getElementById('txtStok').value)
    {
      alert('Maaf, Barang yang diretur melebihi stok');
      document.getElementById('txtJumlah').value = 0;
      document.getElementById('txtJumlah').focus();
      return false;
    
    } else {    */
      var stok = document.getElementById('txtStok').value.toString().replace(/\,/g,"")*1;
      var jumlah = document.getElementById('txtJumlah').value.toString().replace(/\,/g,"")*1;
      var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"")*1;
     
     if(jumlah > stok) {
      alert('Maaf, Barang yang diretur melebihi stok');
      document.getElementById('txtJumlah').value = 0;
      document.getElementById('txtHargaTotal').value = 0;
      document.getElementById('txtJumlah').focus();
      return false;
    
     } else {
     
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*jumlah);
    
    } 
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
     document.getElementById('txtKembalian').value = formatCurrency(dibayar_format-(totalnya+(pajak-diskon)));
     document.getElementById('txtKembalian').value = formatCurrency(dibayar_format_int-totalnyaInt);
     document.getElementById('btnBayar').focus();
}

function GantiPengurangan(terima,urut) {
    
     var bayaren = document.getElementById('total_harga').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
     dibayar_int=bayaren*1;
     totalnyaInt=totalnya*1;        
     
     //document.getElementById('txtIsi').innerHTML = grandTotal; 
     document.getElementById('txtIsi').innerHTML = formatCurrency(totalnyaInt-dibayar_int);
     document.getElementById('txtBack').value = formatCurrency(totalnyaInt-dibayar_int);
     //document.getElementById('txtIsi').focus();

}

function SendHrg(terima,urut) {    
     var Sisa = document.getElementById('penjualan_detail_sisa'+urut).value.toString().replace(/\,/g,"");              
     var SendItem = document.getElementById('sendItem'+urut).value.toString().replace(/\,/g,"");
     var Harga = document.getElementById('itemHarga'+urut).value.toString().replace(/\,/g,"");
     var TotalFunc;
     var curSis = formatCurrency(Sisa);
     var curSend= formatCurrency(SendItem);
     var curHarga= formatCurrency(Harga);  
     var totalHarga=0; 
    
     if(curSend>curSis) {
     alert('Maaf jumlah barang yg retur maksimal hanya '+ curSis);
     document.getElementById('sendItem'+urut).value = 0;
     return false;
     } else {
     
       document.getElementById('totalHargaTerima'+urut).value = formatCurrency(SendItem*Harga);
       TotalFunc=SendItem*Harga;
       if(urut==0) TotalRetur[urut]=TotalFunc;
       if(urut>=1) TotalRetur[urut]=totalHarga+TotalFunc;
 
           for(var i in TotalRetur) 
           { 
           totalHarga += TotalRetur[i];
           } 

       document.getElementById('txtIsi').innerHTML = formatCurrency(totalHarga);
       document.getElementById('txtgrandTotale').value = formatCurrency(totalHarga);
     }
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
function Masukkanitem(frm,kode) 
{        
     hasilKode=CariItem(kode,'type=r');
     hasilAkhir=hasilKode.split('~~');
     
  //   if(!hasilAkhir[0]) {
  //        document.getElementById('item_kode').focus();
  //        alert('Item dengan kode \''+kode+'\' tidak ditemukan');
  //        return false;
  //   }
     
     document.getElementById('item_id').value=hasilAkhir[0];
     document.getElementById('item_nama').value=hasilAkhir[1];     
     //document.getElementById('txtHargaSatuan').value=formatCurrency(hasilAkhir[2]);   
     document.getElementById('txtJumlah').value = 1;
     document.getElementById('txtHargaTotal').value =formatCurrency(hasilAkhir[2])
     document.getElementById('txtJumlah').focus();
}

function CekItem()
{

    if(document.getElementById('txtHargaTotal').value == '0') {
		     alert('Maaf, data kosong, sebaiknya dipilh dulu barangnya');
         document.getElementById('txtHargaTotal').focus();
         return false;
        }

    return true;
}

function CekRetur()
{

    if(!document.getElementById('sup_id').value || document.getElementById('sup_id').value == '--') {
		     alert('Supplier harus diisi ');
         document.getElementById('sup_id').focus();
         return false;
      }
      
    if(!document.getElementById('brg_msk').value || document.getElementById('brg_msk').value == '0') {
		     alert('Masukkan dulu barang yang akan di retur');
         document.getElementById('item_kode').focus();
         return false;
      }

    return true;
}

function CekDataThis()
{         
    if(!document.getElementById('txtgrandTotale').value || document.getElementById('txtgrandTotale').value == '0' )
    {
      alert('Tidak Ada Barang yg akan di Retur');
      document.getElementById('retur_penjualan_keterangan').focus();
      return false;
    }
    
    return true;
}

function CekObat()
{

    if(!document.getElementById('no_nota').value) {
		     alert('Nomor Nota Disi Dahulu');
         document.getElementById('no_nota').focus();
         return false;
      }

    return true;
}


function CekData()
{
    if(!document.getElementById('txtDibayar').value || document.getElementById('txtDibayar').value =='0')
    {
      alert('Belum dibayar');
      document.getElementById('txtDibayar').focus();
      return false;
    }
    
    return true;
}
function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     
     if(keyCode==113) {  // -- f2 buat fokus ke tipe transaksi ---
          document.getElementById('txtDiskon').focus();
     }
     return false;
}
function daftar_meja() {     
     var new_win;
     new_win=new_win=window.open('meja_find.php','Meja','status=no,toolbar=no,scrollbars=yes,resizable=no,width=680,height=480');
     new_win.focus();
}
function daftar_pembayaran() {     
     var new_win;
     new_win=new_win=window.open('bayar_find.php','Meja','status=no,toolbar=no,scrollbars=yes,resizable=no,width=680,height=480');
     new_win.focus();
}

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=300,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=300,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
<?php if($_x_mode=="cetak"){ ?>
         BukaWindow('retur_cetak.php?id=<?php echo $returId;?>','Nota');
       //	       document.location.href='<?php echo $sellPage;?>';
    <?php } ?>
</script>
<div onKeyDown="CaptureEvent(event);">
<body>

<div id="body">
<div id="scroller">
<br />   
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" >         
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
              <tr>
                  <td align="left" width="15%" class="tablecontent">&nbsp;Nomor Retur&nbsp;</td>
                  <td align="left" width="40%" class="tablecontent-odd" >
                  <input type="text" name="beli_nota" id="beli_nota" value="<?php echo $_POST["beli_nota"];?>" size="60" readonly="readonly" />
                  </td>                                      
                  <td align="center" width="47%" class="tablecontent" rowspan="6"><font color='red' size='10'><span id=txtIsi ><?php echo currency_format($totalHarga);?></span></font></td>      
              </tr>
              <tr>
               <td align="left" width="15%" class="tablecontent">&nbsp;Tanggal</td>
               <td align="left" width="40%" class="tablecontent-odd" >
                <?php echo $view->RenderTextBox("retur_penjualan_tgl","retur_penjualan_tgl","15","30",$_POST["retur_penjualan_tgl"],"inputField", "readonly",null,false);?>
               </td> 
               </tr>
                <tr>
                <td align="left" width="15%" class="tablecontent">&nbsp;No Nota</td>
                <td align="left" width="3%" class="tablecontent-odd">
                <a href="<?php echo $findPasien;?>?TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Supplier">
                <?php echo $view->RenderTextBox("no_nota","no_nota","30","30",$_POST["no_nota"],"inputField", null,false);?>
                <input type="hidden" name="id_penjualan" id="id_penjualan" value="<?php echo $_POST["id_penjualan"];?>"></a>
                <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"></a>
                </td>
                </tr>
                <tr>
                <td align="left" width="15%" class="tablecontent">&nbsp;Nama Pasien</td>
                <td align="left" width="40%" class="tablecontent-odd" >
                <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","30","30",$_POST["cust_usr_nama"],"inputField", null,false);?>
                </td> 
                </tr>
                <tr>
                <td align="left" width="15%" class="tablecontent">&nbsp;Alamat</td>
                <td align="left" width="40%" class="tablecontent-odd" >
                <?php echo $view->RenderTextBox("cust_usr_alamat","cust_usr_alamat","40","40",$_POST["cust_usr_alamat"],"inputField", null,false);?>
                </td> 
                </tr>
                <tr>
                <td align="left" width="15%" class="tablecontent">&nbsp;Keterangan</td>
                <td align="left" width="40%" class="tablecontent-odd" >
                <?php echo $view->RenderTextBox("retur_penjualan_keterangan","retur_penjualan_keterangan","60","60",$_POST["retur_penjualan_keterangan"],"inputField", null,false);?>
                </td> 
                </tr>
       </table>
       
       <?php if(!$dataTable) { ?>
         
       <table width="100%" border="0" cellpadding="1" cellspacing="1">
       <tr>
       <td align="center">
       <input type="submit" name="btnTampil" id="btnTampil" value="Tampilkan Obat" class="submit" onClick="javascript:return CekObat();">
				       <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='<?php echo $ROOT;?>apotik/module/retur_barang/retur_penjualan/retur_penjualan_view.php'";/>
       </td>
       </tr>
       </table>
         
       <?php } ?>
       
       <br />
  <?php if($dataTable) { ?>
       <table width="100%" border="1" cellpadding="1" cellspacing="1">       
               <tr>
                    <td align="left" width="7%" class="subheader">&nbsp;Kode Item&nbsp;</td>
                    <td align="left" width="15%" class="subheader">&nbsp;Item&nbsp;</td>
                    <td align="left" width="10%" class="subheader">&nbsp;Jumlah</td>  
                    <td align="left" width="10%" class="subheader">&nbsp;Harga Beli</td>
                    <td align="left" width="10%" class="subheader">&nbsp;Terima</td>
                    <td align="left" width="15%" class="subheader">&nbsp;Harga</td>
              </tr>
              
             	<?php for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                      <tr class="tablecontent-odd">
                      <td align="left" width="5%" ><?php echo $dataTable[$i]["item_kode"];?></td>  
                      <input type="hidden" name="item_id[]" value="<?php echo $dataTable[$i]["item_id"] ;?>" /></td>
                      <td align="left" width="15%" ><?php echo $dataTable[$i]["item_nama"];?></td> 
                      <td align="left" width="10%" >
                      <?php echo $view->RenderTextBox("penjualan_detail_sisa$i","penjualan_detail_sisa$i","4","4",currency_format($dataTable[$i]["penjualan_detail_sisa"]),"curedit", "readonly",true);?>
                      </td>
                      <td align="left" width="10%" >
                      <?php echo $view->RenderTextBox("itemHarga$i","itemHarga$i","4","4",currency_format($dataTable[$i]["penjualan_detail_harga_jual"]),"curedit", "readonly",true,'onChange=SendHrg(this.value,'.$i.');onKeyDown=SendHrg(this.value,'.$i.');');?>
                      </td>
                      <td align="left" width="10%" >
                      <?php echo $view->RenderTextBox("sendItem$i","sendItem$i","4","4",currency_format($_POST["sendItem$i"]),"curedit", "",true,'onChange=SendHrg(this.value,'.$i.');onKeyDown=SendHrg(this.value,'.$i.');');?>
                      </td>
                      <td align="left" width="15%" >
                      <?php echo $view->RenderTextBox("totalHargaTerima$i","totalHargaTerima$i","10","10",$_POST["totalHargaTerima$i"],"curedit", "readonly",true,'onChange=SendHrgTot(this.value,'.$i.');onKeyDown=SendHrgTot(this.value,'.$i.');');?>
                      </td>                      
                      </tr>
                      <?php //echo $view->RenderHidden("tipehid[]","tipehid$i",$_POST["tipehid$i"]);?>
						  <?php } ?>
						          <tr class="tablesmallheader">
                      <td align="center" width="2%" colspan="6">
                      <input type="submit" name="btnSave" id="btnSave" value="Retur" class="submit" onClick="javascript:return CekDataThis();">
                      <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='<?php echo $ROOT;?>apotik/module/retur_barang/retur_penjualan/retur_penjualan_view.php'";/>
                      </td>
                      </tr>
            </table>
        <?php } ?>              
          </td>
     </tr>
<input type="hidden" name="klinik" value="<?php echo $_POST["klinik"];?>" />
<input type="hidden" id="btn_edit" name="btn_edit" value="<?php echo $btn_edit;?>" />      
<input type="hidden" name="id_meja" value="<?php echo $_POST["id_meja"]; ?>" />
<input type="hidden" name="pgw_cuti_id" value="<?php echo $pgwCutiId?>" />
<input type="hidden" name="retur_penjualan_id" value="<?php echo $returId;?>" />
<?php if($dataPenjualanId) { ?>
<input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $dataPenjualanId;?>" />
<?php } else { ?>
<input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
<?php } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
<input type="hidden" name="tambah" value="<?php echo $_GET['tambah'];?>" />
<input type="hidden" name="penjualan_edit" value="<?php echo $penjualan_edit;?>"/>
<input type="hidden" name="penjualan_po_det_id" id=="penjualan_po_det_id"/>
<input type="hidden" name="retur_penjualan_id" value="<?php echo $returId;?>" />
<input type="hidden" name="meja_nama" value="<?php echo $dataMeja["meja_nama"];?>" />
<input type="hidden" name="menu_harga_jual" value="<?php echo $datamenu["menu_harga_jual"];?>" />
<input type="hidden" name="member_id" value="<?php echo $_POST["member_id"];?>" />
<input type="hidden" name="jbayar_nama" value="<?php echo $dataMeja["jbayar_nama"];?>" />
<input type="hidden" name="awal" value="1" />
<input type="hidden" name="brg_msk" id="brg_msk" value="<?php echo $brg_msk;?>" />
<input type="hidden" id="txtgrandTotale" name="txtgrandTotale" value="<?php echo $_POST["txtgrandTotale"];?>" />
</form>

<script>document.frmEdit.beli_nota.focus();</script>

</div>
</div>
<!--  		<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>     -->
<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>