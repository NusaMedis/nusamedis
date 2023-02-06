<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tree.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     require_once($LIB."upload.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
    // $findPage = "pasien_find_jkn.php?";

     $sql = "select * from klinik.klinik_rawatinap where id_reg = ".QuoteValue(DPE_CHAR, $regId);
     $rs = $dtaccess->Fetch($sql);
     //die($sql);

          $dbTable = "klinik_rawatinap";
          $dbField[0] = "rawatinap_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_kategori_kamar";
          $dbField[3] = "id_kamar";
          $dbField[4] = "id_bed";
          $dbField[5] = "rawatinap_tanggal_masuk";
          $dbField[6] = "rawatinap_waktu_masuk";
          $dbField[7] = "rawatinap_jenis_pasien";
          $dbField[8] = "rawatinap_asal_instalasi";
          $dbField[9] = "id_gedung_rawat";

          ////$rawatinap_id = $dtaccess->GetTransID("klinik_rawatinap","rawatinap_id",DB_SCHEMA_KLINIK);
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$rs["rawatinap_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$rs["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["reg_tanggal"]));
          $dbValue[6] = QuoteValue(DPE_DATE,date("H:i:s"));
          $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
           $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["asal_pasien"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_gedung_rawat"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          //print_r($dbValue); die();
          $dtmodel->update() or die("insert  error");
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          // history
          $dbTable = "klinik_rawat_inap_history";
          $dbField[0] = "rawat_inap_history_id";   // PK
          $dbField[1] = "rawat_inap_history_who_update";
          $dbField[2] = "rawat_inap_history_when_update";
          $dbField[3] = "rawat_inap_history_tanggal";
          $dbField[4] = "id_reg";
          $dbField[5] = "rawat_inap_history_kelas_tujuan";
          $dbField[6] = "rawat_inap_history_status";
          $dbField[7] = "rawat_inap_history_jenis_pasien";
          $dbField[8] = "rawat_inap_history_kamar";
          $dbField[9] = "rawat_inap_history_bed";
          //$dbField[7] = "rawat_inap_history_rawat_jalan";
          //$dbField[8] = "rawat_inap_history_poli_tujuan";
          /*$dbField[11] = "rawat_inap_history_kelas_tujuan";
          $dbField[12] = "rawat_inap_history_kamar_tujuan";
          $dbField[13] = "rawat_inap_history_bed_tujuan";*/
          //$dbField[9] = "rawat_inap_history_cara_keluar";

          $rawat_inap_history_id = $dtaccess->GetTransID("klinik_rawat_inap_history","rawat_inap_history_id",DB_SCHEMA_KLINIK);
          $dbValue[0] = QuoteValue(DPE_CHAR,$rawat_inap_history_id);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$userName);
          $dbValue[2] = QuoteValue(DPE_CHAR,date("Y-m-d H:i:s"));
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,A);    // a = awal, p = pulang, t = transfer
          $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          //$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
          /*if($_POST["id_kategori_kamar"]=="KA") $_POST["id_kelas"]=2;
          elseif($_POST["id_kategori_kamar"]=="KB") $_POST["id_kelas"]=3;
          elseif($_POST["id_kategori_kamar"]=="KC") $_POST["id_kelas"]=4;
          elseif($_POST["id_kategori_kamar"]=="KD") $_POST["id_kelas"]=1;
          elseif($_POST["id_kategori_kamar"]=="KF") $_POST["id_kelas"]=5;
          elseif($_POST["id_kategori_kamar"]=="KG") $_POST["id_kelas"]=5;*/
          /*$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["kelas_tujuan"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);*/
          //$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_keadaan_keluar_inap"]);

          //print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

         // $dtmodel->Insert() or die("insert  error");

          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
            //die();
        // end history
          
          //if($_POST["reg_jenis_pasien"]=="5"){
          //$cetak = "y";
          //}
          //if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          //exit();

          
          $sqlUpdate = "update klinik_kamar_bed set bed_reserved='y' where bed_id = ".QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          $dtaccess->Execute($sqlUpdate,DB_SCHEMA_KLINIK);

          $sql = "select id_pembayaran from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR, $regId);
          $reg = $dtaccess->Fetch($sql);

          $sql = "update klinik.klinik_registrasi set reg_jenis_pasien = ".QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
          $sql .= " WHERE id_pembayaran = ".QuoteValue(DPE_CHAR,$reg["id_pembayaran"]);
          $dtaccess->Execute($sql);
          
         /* $sql = "select * from klinik.klinik_kamar where kamar_id=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $rs = $dtaccess->Execute($sql);
          $poli = $dtaccess->Fetch($rs);

           $sqlKelas = "update klinik_registrasi set reg_kelas=".QuoteValue(DPE_CHAR,$_POST["id_kelas"]).",reg_no_sep=".QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]).",hak_kelas_inap=".QuoteValue(DPE_CHAR,$_POST["id_kelas"]).", id_poli=".QuoteValue(DPE_CHAR,$poli["id_poli"])." 
                 where reg_id = ".QuoteValue(DPE_CHAR,$regId);
          $dtaccess->Execute($sqlKelas,DB_SCHEMA_KLINIK); 
    //      echo $sqlKelas; die();
          //hitung kamar yang terpakai
          $sql = "select count(bed_kode) as bed from klinik.klinik_kamar_bed where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"])." and bed_reserved='y' and bed_keterangan='n'";
          $bed = $dtaccess->Fetch($sql); 
          //hitung presentase kamar terpakai dan bukan virtual
          $sql = "select count(bed_kode) as total from klinik.klinik_kamar_bed where bed_reserved='y' and bed_keterangan='n'";
          $total = $dtaccess->Fetch($sql);
          $persen = ($total["total"]/150)*100;
          //hitung sisa bed 
          $sql = "select * from klinik.klinik_rawat_inap_bor_kamar where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $rs = $dtaccess->Execute($sql);
          $kamar = $dtaccess->Fetch($rs);
          $sisaBed = $kamar["jml_bed"]-$bed["bed"];
          //update bor
          $sql = "update klinik.klinik_rawat_inap_bor_kamar set bed_terpakai=".QuoteValue(DPE_NUMERIC,StripCurrency($bed["bed"])).", 
                  bed_tersisa=".QuoteValue(DPE_NUMERIC,Stripcurrency($sisaBed))." where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $dtaccess->Execute($sql);
                */
         /* ###### Insert folio yang belum dibayar #########
          # cari registrasi sebelumnya
          $sql = "select reg_id from klinik.klinik_registrasi where id_cust_usr = '$custUsrId' and reg_tipe_rawat = '$_POST[asal_pasien]'";
          $reg_id_lama = $dtaccess->Fetch($sql);
          # update folio yang belum dibayar
          $sql = " update klinik.klinik_folio 
                    set id_reg = '$regId', id_pembayaran ='$byrId'
                     where fol_lunas = 'n' and id_reg = '$reg_id_lama[reg_id]' ";
          $dtaccess->Execute($sql);*/
               

?>