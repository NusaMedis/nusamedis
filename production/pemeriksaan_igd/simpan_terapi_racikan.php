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
     $now = date("Y-m-d H:i:s");

     $sql = "select * from apotik.apotik_jam_aturan_pakai where jam_aturan_pakai_id = ".QuoteValue(DPE_CHAR,$_POST['jam_aturan_pakai_id']);
     $JamAturanPakai = $dtaccess->Fetch($sql);

     $sql = "select * from apotik.apotik_aturan_pakai where aturan_pakai_id = ".QuoteValue(DPE_CHAR,$_POST['aturan_pakai_id']);
     $AturanPakai = $dtaccess->Fetch($sql);

     $sql = "select * from apotik.apotik_aturan_minum where aturan_minum_id = ".QuoteValue(DPE_CHAR,$_POST['aturan_minum_id']);
     $AturanMinum = $dtaccess->Fetch($sql);

     $sql = "select * from apotik.apotik_obat_petunjuk where petunjuk_id = ".QuoteValue(DPE_CHAR,$_POST['petunjuk_id']);
     $Dosis = $dtaccess->Fetch($sql);

     $sql = "select * from logistik.logistik_item_satuan where satuan_tipe = 'J' and satuan_id = ".QuoteValue(DPE_CHAR,$_POST['satuan_id']);
     $Satuan = $dtaccess->Fetch($sql);

     $sql = "select * from apotik.apotik_jenis_racikan where jenis_racikan_id = ".QuoteValue(DPE_CHAR,$_POST['jenis_racikan_id']);
     $JenisRacikan = $dtaccess->Fetch($sql);

                 $dbTable = "klinik.klinik_perawatan_terapi_racikan";
                 $dbField[0] = "rawat_terapi_racikan_id";   // PK
                 $dbField[1] = "id_rawat";
                 $dbField[2] = "id_jenis_racikan";
                 $dbField[3] = "rawat_terapi_racikan_jumlah";
                 $dbField[4] = "id_satuan";
                 $dbField[5] = "id_petunjuk";
                 $dbField[6] = "id_aturan_minum";
                 $dbField[7] = "id_aturan_pakai";
                 $dbField[8] = "id_jam_aturan_pakai";
                 $dbField[9] = "jenis_racikan_nama";
                 $dbField[10] = "satuan_nama";
                 $dbField[11] = "petunjuk_nama";
                 $dbField[12] = "aturan_minum_nama";
                 $dbField[13] = "aturan_pakai_nama";
                 $dbField[14] = "jam_aturan_pakai_nama";
                 //$dbField[9] = "rawat_terapi_racikan_urut";

                 $rawatTerapiRacikanId = $dtaccess->GetTransId();
                 
                 $dbValue[0] = QuoteValue(DPE_CHAR,$rawatTerapiRacikanId);
                 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_rawat"]);            
                 $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["jenis_racikan_id"]);            
                 $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["rawat_terapi_racikan_jumlah"]);            
                 $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["satuan_id"]);            
                 $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["petunjuk_id"]);            
                 $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["aturan_minum_id"]);            
                 $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["aturan_pakai_id"]);            
                 $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["jam_aturan_pakai_id"]);            
                 $dbValue[9] = QuoteValue(DPE_CHAR,$JenisRacikan["jenis_racikan_nama"]);            
                 $dbValue[10] = QuoteValue(DPE_CHAR,$Satuan["satuan_nama"]);            
                 $dbValue[11] = QuoteValue(DPE_CHAR,$Dosis["petunjuk_nama"]);            
                 $dbValue[12] = QuoteValue(DPE_CHAR,$AturanMinum["aturan_minum_nama"]);            
                 $dbValue[13] = QuoteValue(DPE_CHAR,$AturanPakai["aturan_pakai_nama"]);            
                 $dbValue[14] = QuoteValue(DPE_CHAR,$JamAturanPakai["jam_aturan_pakai_nama"]);            
                 //$dbValue[9] = QuoteValue(DPE_CHAR,$Item["item_nama"]);           
                 
                 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
                 $a = $dtmodel->insert() or die("insert  error"); 
               $rs = [];
               if ($a) {
                   $rs['rawat_terapi_racikan_id'] = $rawatTerapiRacikanId;
                   $rs['petunjuk_nama'] = $Dosis['petunjuk_nama'];
                   $rs['jenis_racikan_nama'] = $JenisRacikan['jenis_racikan_nama'];
                   $rs['satuan_nama'] = $Satuan['satuan_nama'];
                   $rs['aturan_minum_nama'] = $AturanMinum['aturan_minum_nama'];
                   $rs['aturan_pakai_nama'] = $AturanPakai['aturan_pakai_nama'];
                   $rs['jam_aturan_pakai_nama'] = $JamAturanPakai['jam_aturan_pakai_nama'];
                   $rs['rawat_terapi_racikan_jumlah'] = $_POST["rawat_terapi_racikan_jumlah"];
               }

               echo json_encode($rs);

                 unset($dtmodel);
                 unset($dbField);
                 unset($dbValue);
                 unset($dbKey);     
?>