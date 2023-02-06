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



     $sql = 'SELECT procedure_nomor, procedure_nama, procedure_id, procedure_short_desc from klinik.klinik_procedure';
     $sql .= ' WHERE procedure_id = '.QuoteValue(DPE_CHAR, $_POST['procedure_id']);
     $icd = $dtaccess->fetch($sql);

     switch ($_GET['func']) {
          case 'store':
               $dbTable = "klinik.klinik_perawatan_procedure";
               
               $dbField[0] = 'rawat_procedure_id';
               $dbField[1] = 'id_rawat';
               $dbField[2] = 'id_procedure';
               $dbField[3] = 'rawat_procedure_kode';
               $dbField[4] = 'rawat_procedure_keterangan';

               $id= $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['procedure_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($icd['procedure_nomor']));
               $dbValue[4] = QuoteValue(DPE_CHAR, $icd['procedure_nama']);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->Insert() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_procedure_id'] = $id;
                   $rs['procedure_id'] = $_POST['procedure_id'];
                   $rs['procedure_nomor'] = $icd['procedure_nomor'];
                   $rs['procedure_nama'] = $icd['procedure_nama'];
                   $rs['procedure_short_desc'] = $icd['procedure_short_desc'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;
          case 'update':
               $dbTable = "klinik.klinik_perawatan_procedure";
               
               $dbField[0] = 'rawat_procedure_id';
               $dbField[1] = 'rawat_procedure_keterangan';
               $dbField[2] = 'id_procedure';
               $dbField[3] = 'rawat_procedure_kode';

               $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['rawat_procedure_id']);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $icd['procedure_nama']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['procedure_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($icd['procedure_nomor']));

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->update() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_procedure_id'] = $_POST['rawat_procedure_id'];
                   $rs['procedure_id'] = $_POST['procedure_id'];
                   $rs['procedure_nomor'] = $icd['procedure_nomor'];
                   $rs['procedure_nama'] = $icd['procedure_nama'];
                   $rs['procedure_short_desc'] = $icd['procedure_short_desc'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;

          case 'destroy':
               $sql = 'DELETE from klinik.klinik_perawatan_procedure';
               $sql .= ' WHERE rawat_procedure_id = '.QuoteValue(DPE_CHAR, $_POST['id']);

               $dtaccess->execute($sql);
               echo json_encode(['success' => true]);
               break;
          
          default:
               $sql = 'SELECT rawat_procedure_id, procedure_nomor, procedure_nama, procedure_id, procedure_short_desc from klinik.klinik_perawatan_procedure a';
               $sql .= ' LEFT JOIN klinik.klinik_procedure b on b.procedure_id = a.id_procedure ';
               $sql .= ' WHERE id_rawat = '.QuoteValue(DPE_CHAR, $_POST['rawat_id']);
               $sql .= ' order by procedure_nomor_tanpa_titik ';
               $q = $dtaccess->fetchAll($sql);
               echo json_encode($q);
               break;
     }




?>