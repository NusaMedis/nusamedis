<?php
     // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."tampilan.php");

     // Inisialisasi Lib
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $enc = new textEncrypt();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depId = $auth->GetDepId();
     $poliId = $auth->IdPoli();
     $tglSekarang = date("d-m-Y");
     $depLowest = $auth->GetDepLowest();

     $sql = "select anamnesa_pilihan_id from klinik.klinik_anamnesa_pilihan where id_anamnesa=".QuoteValue(DPE_CHAR,$_POST['anamnesa_id'])." order by anamnesa_pilihan_urut asc";
     $dataAnamnesaPilihan = $dtaccess->FetchAll($sql);

     foreach ($dataAnamnesaPilihan as $f) {
          if (array_key_exists($f['anamnesa_pilihan_id'], $_POST)) {
            $data[] = [ 'field' => $f['anamnesa_pilihan_id'], 'value' => $_POST[$f['anamnesa_pilihan_id']] ];
          }
     }

     $dbTable = "klinik.klinik_anamnesa_tb";
     
     $dbField[0] = 'anamnesa_tb_id';
     $dbField[1] = 'id_anamnesa';
     $dbField[2] = 'id_rawat';
     $dbField[3] = 'id_reg';
     $dbField[4] = 'anamnesa_tb_isi';
     $dbField[5] = 'id_poli';

     $id= $dtaccess->GetTransID();
     $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
     $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['anamnesa_id']);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['rawat_id']);
     $dbValue[3] = QuoteValue(DPE_CHAR, $_POST['reg_id']);
     $dbValue[4] = QuoteValue(DPE_CHAR, serialize($data));
     $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['poli_id']);

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
     $a = $dtmodel->Insert() or die("update error");

     if ($a) {
          foreach ($dataAnamnesaPilihan as $f) {
               $rs['anamnesa_tb_id'] = $id;
               $rs['reg_id'] = $_POST['reg_id'];
               $rs['rawat_id'] = $_POST['rawat_id'];
               $rs['poli_id'] = $_POST['poli_id'];
               $rs['anamnesa_id'] = $_POST['anamnesa_id'];
               if (array_key_exists($f['anamnesa_pilihan_id'], $_POST)) {
                 $rs[$f['anamnesa_pilihan_id']] = $_POST[$f['anamnesa_pilihan_id']];
               }
          }
          echo json_encode($rs);
     }

     unset($dtmodel);
     unset($dbTable);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);   
?>