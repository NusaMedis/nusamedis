<?php
  // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
   
  //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
   $depId = $auth->GetDepId();
   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
     
 $idSplit = "1"; //DIPATEN 1 untuk JASA MEDIK    
  
  //cari folio
  // if ($_POST['isNewRecord']=='true') {
  //     $folId = $_POST["fol_id"];
  // }else{
  //     $folId = $_POST["id_fol"];
  // }
    if($_GET['save'] || $_GET['update']){
          $sql = "SELECT id_pembayaran from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST['id_reg']);
          $dtPembayaran = $dtaccess->Fetch($sql);

          $sql = "select * from logistik.logistik_alkes where alkes_id = ".QuoteValue(DPE_CHAR,$_POST['alkes_id']);
          $dataItem = $dtaccess->Fetch($sql);

        if($_POST['isNewRecord']=='true') {
          $folPelId = $dtaccess->GetTransID();   
        } else {
          $folPelId = $_POST['fol_pemakaian_id'];
        }
        
          # simpan di pelaksana
          $dbTable = "klinik.klinik_pemakaian_alkes";
          $dbField[0] = "pemakaian_alkes_id";   // PK
          $dbField[1] = "id_alkes";
          $dbField[2] = "pemakaian_alkes_waktu_awal";
          $dbField[3] = "pemakaian_alkes_waktu_akhir";
          $dbField[4] = "id_reg";
          $dbField[5] = "id_pembayaran";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["alkes_id"]);
          $dbValue[2] = QuoteValue(DPE_DATE, date_format(date_create($_POST["pemakaian_alkes_awal"]), 'Y-m-d H:i:s'));
          $dbValue[3] = QuoteValue(DPE_DATE, date_format(date_create($_POST["pemakaian_alkes_akhir"]), 'Y-m-d H:i:s'));
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$dtPembayaran['id_pembayaran']);
           
          $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
          //print_r($dbField);
          //print_r($dbValue);
          //print_r($dbKey);
          //die();
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if($_POST['isNewRecord']=='true') {
            $dtmodel->Insert() or die("insert  error"); 
            echo "sukses insert fol pemakaian => " ;
          } else {
            $dtmodel->Update() or die("insert  error"); 
            echo "sukses update fol pemakaian => " ;
            //delete split dulu
            //$sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$_POST["id_fol"]);
            //$dtaccess->Execute($sql);
            //echo "sukses hapus fol split lama => " ;
          }
            
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);    
        }
        else if($_GET['del']){
          $id_pemakaian = $_POST['id'];

          $sql = "DELETE from klinik.klinik_pemakaian_alkes where pemakaian_alkes_id = '$id_pemakaian'";
          $dtaccess->Execute($sql);

          echo json_encode(array('success'=>true));

        }
         

    
   exit();      
  
?>