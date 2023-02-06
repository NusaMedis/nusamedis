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



     $sql = 'SELECT diagnosa_nomor, diagnosa_nama, diagnosa_id, diagnosa_short_desc as diagnosa_deskripsi from klinik.klinik_diagnosa';
     $sql .= ' WHERE diagnosa_id = '.QuoteValue(DPE_CHAR, $_POST['diagnosa_id']);
     $diagnosa = $dtaccess->fetch($sql);

     switch ($_GET['func']) {
          case 'store':

               $sql = "SELECT count(rawat_diagnosa_id) as nomor from klinik.klinik_perawatan_diagnosa where id_rawat = ".QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $nomorTerakhir = $dtaccess->Fetch($sql);

               $nomor = $nomorTerakhir['nomor'] + 1;

               $dbTable = "klinik.klinik_perawatan_diagnosa";
               
               $dbField[0] = 'rawat_diagnosa_id';
               $dbField[1] = 'id_rawat';
               $dbField[2] = 'id_diagnosa';
               $dbField[3] = 'rawat_diagnosa_kode';
               $dbField[4] = 'rawat_diagnosa_tindakan_nama';
               $dbField[5] = 'rawat_diagnosa_urut';
               $dbField[6] = 'rawat_diagnosa_status';

               $id= $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['diagnosa_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($diagnosa['diagnosa_nomor']));
               $dbValue[4] = QuoteValue(DPE_CHAR, $diagnosa['diagnosa_nama']);
               $dbValue[5] = QuoteValue(DPE_CHAR, $nomor);
               $dbValue[6] = QuoteValue(DPE_CHAR, $_POST['rawat_icd_status_id']);


               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->Insert() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_diagnosa_id'] = $id;
                   $rs['diagnosa_id'] = $_POST['diagnosa_id'];
                   $rs['diagnosa_nomor'] = $diagnosa['diagnosa_nomor'];
                   $rs['diagnosa_nama'] = $diagnosa['diagnosa_nama'];
                   $rs['diagnosa_deskripsi'] = $diagnosa['diagnosa_deskripsi'];
                   $rs['rawat_icd_status'] = $_POST['rawat_icd_status_id'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;
          case 'update':
               $dbTable = "klinik.klinik_perawatan_diagnosa";
               
               $dbField[0] = 'rawat_diagnosa_id';
               $dbField[1] = 'id_rawat';
               $dbField[2] = 'id_diagnosa';
               $dbField[3] = 'rawat_diagnosa_kode';
               $dbField[4] = 'rawat_diagnosa_tindakan_nama';
               $dbField[5] = 'rawat_diagnosa_status';

               $id= $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
               $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['id_rawat']);
               $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['diagnosa_id']);
               $dbValue[3] = QuoteValue(DPE_CHAR, trim($diagnosa['diagnosa_nomor']));
               $dbValue[4] = QuoteValue(DPE_CHAR, $diagnosa['diagnosa_nama']);
               $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['rawat_icd_status_id']);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $a = $dtmodel->update() or die("update error");
               $rs = [];
               if ($a) {
                   $rs['rawat_diagnosa_id'] = $id;
                   $rs['diagnosa_id'] = $_POST['diagnosa_id'];
                   $rs['diagnosa_nomor'] = $diagnosa['diagnosa_nomor'];
                   $rs['diagnosa_nama'] = $diagnosa['diagnosa_nama'];
                   $rs['diagnosa_deskripsi'] = $diagnosa['diagnosa_deskripsi'];
                   $rs['rawat_icd_status'] = $_POST['rawat_icd_status_id'];
               }

               echo json_encode($rs);
               
               unset($dtmodel);
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               break;

          case 'destroy':
               $sql = 'DELETE from klinik.klinik_perawatan_diagnosa';
               $sql .= ' WHERE rawat_diagnosa_id = '.QuoteValue(DPE_CHAR, $_POST['id']);

               $dtaccess->execute($sql);
               echo json_encode(['success' => true]);
               break;
          
          default:
               $sql = 'SELECT rawat_diagnosa_id, diagnosa_nomor, diagnosa_nama, diagnosa_id, diagnosa_short_desc as diagnosa_deskripsi, rawat_diagnosa_status as rawat_icd_status from klinik.klinik_perawatan_diagnosa a';
               $sql .= ' LEFT JOIN klinik.klinik_diagnosa b on b.diagnosa_id = a.id_diagnosa ';
               $sql .= ' WHERE id_rawat = '.QuoteValue(DPE_CHAR, $_POST['rawat_id']);
               $sql .= ' order by diagnosa_nomor_tanpa_titik ';
               $q = $dtaccess->fetchAll($sql);
               echo json_encode($q);
               break;
     }




?>