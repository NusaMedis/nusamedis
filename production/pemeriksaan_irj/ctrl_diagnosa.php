<?php
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



     $sql = 'SELECT icd_nomor, icd_nama, icd_id from klinik.klinik_icd';
     $sql .= ' WHERE icd_id = '.QuoteValue(DPE_CHAR, $_POST['icd_id']);
     $icd = $dtaccess->fetch($sql);

     switch ($_GET['func']) {
          case 'store':
               $dbTable = "klinik.klinik_perawatan_icd";
               
               $dbField[0] = 'rawat_icd_id';
               $dbField[1] = 'id_rawat';
               $dbField[2] = 'id_icd';
               $dbField[3] = 'rawat_icd_kode';
               $dbField[4] = 'rawat_icd9_tindakan_nama';

               $id= $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['icd_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($icd['icd_nomor']));
               $dbValue[4] = QuoteValue(DPE_CHAR, $icd['icd_nama']);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->Insert() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_icd_id'] = $id;
                   $rs['icd_id'] = $_POST['icd_id'];
                   $rs['icd_nomor'] = $icd['icd_nomor'];
                   $rs['icd_nama'] = $icd['icd_nama'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;
          case 'update':
               $dbTable = "klinik.klinik_perawatan_icd";
               
               $dbField[0] = 'rawat_icd_id';
               $dbField[1] = 'rawat_icd9_tindakan_nama';
               $dbField[2] = 'id_icd';
               $dbField[3] = 'rawat_icd_kode';

               $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['rawat_icd_id']);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $icd['icd_nama']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['icd_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($icd['icd_nomor']));

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->update() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_icd_id'] = $_POST['rawat_icd_id'];
                   $rs['icd_id'] = $_POST['icd_id'];
                   $rs['icd_nomor'] = $icd['icd_nomor'];
                   $rs['icd_nama'] = $icd['icd_nama'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;

          case 'destroy':
               $sql = 'DELETE from klinik.klinik_perawatan_icd';
               $sql .= ' WHERE rawat_icd_id = '.QuoteValue(DPE_CHAR, $_POST['id']);

               $dtaccess->execute($sql);
               echo json_encode(['success' => true]);
               break;
          
          default:
               $sql = 'SELECT rawat_icd_id, icd_nomor, icd_nama, icd_id from klinik.klinik_perawatan_icd a';
               $sql .= ' LEFT JOIN klinik.klinik_icd b on b.icd_id = a.id_icd ';
               $sql .= ' WHERE id_rawat = '.QuoteValue(DPE_CHAR, $_POST['rawat_id']);
               $q = $dtaccess->fetchAll($sql);
               echo json_encode($q);
               break;
     }




?>