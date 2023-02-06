<?php    
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."tree.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php"); 
     
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $enc = new textEncrypt();     
     $depId = $auth->GetDepId();
     $lokasi = $ROOT."gambar/foto_pasien";     

     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }

    #konfigurasi 
    $sql = "select dep_konf_reg_no_rm_depan from global.global_departemen";
    $konf = $dtaccess->Fetch($sql);
      
     //INISIALISASI AWAL
    $backPage = "registrasi_pasien_awal.php?usr_id="; 
    if(!$_POST["cust_usr_asal_negara"])$_POST["cust_usr_asal_negara"] ='1';
    $norm_depan = $konf['dep_konf_reg_no_rm_depan'];
    if(!$_POST["reg_tgl_rujukan"]) $_POST["reg_tgl_rujukan"]=date('Y-m-d');
    if(!$_POST["reg_tgl_sep"]) $_POST["reg_tgl_sep"]=date('Y-m-d');
    if(!$_POST["reg_no_rujukan"]) $_POST["reg_no_rujukan"]="0";


      # cek pasien masih irj
     $sql = "select count(reg_id) as jkn_irj, string_agg(poli_nama, ', ')
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id ";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET['usr_id'])." and reg_tanggal > '2018-05-31' and reg_tanggal='".date('Y-m-d')."' and (reg_tipe_rawat = 'J' or reg_tipe_rawat = 'G') and (reg_status = 'E1' or reg_status = 'G1') and (b.poli_tipe !='L' and b.poli_tipe !='R' and b.poli_tipe !='A') and reg_jenis_pasien = '5'";  
     $pas_irj = $dtaccess->Fetch($sql);  
     //echo $sql;
     
      # cek pasien masih irna
     $sql = "select count(reg_id) as jkn_irna
            from klinik.klinik_registrasi a ";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET['usr_id'])." and reg_status = 'I2' and reg_tanggal > '2018-05-31'";
     $sql .= " and id_poli IN (select poli_id from global.global_auth_poli where poli_tipe= 'I')  ";  
     //echo $sql;
     $pas_irna = $dtaccess->Fetch($sql);

    // FUNGSI ADD dan DELETE
    if ($_POST["btnUpdate"]) 
    {   
    # cek lagi
     # cek pasien masih irj
     $sql = "select count(reg_id) as jkn_irj, string_agg(poli_nama, ', ')
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id'])." and reg_tanggal > '2018-05-31' and reg_tanggal='".date('Y-m-d')."' and (reg_tipe_rawat = 'J' or reg_tipe_rawat = 'G') and (reg_status = 'E1' or reg_status = 'G1') and (b.poli_tipe !='L' and b.poli_tipe !='R' and b.poli_tipe !='A') and reg_jenis_pasien = '5'";  
     $jkn_irj = $dtaccess->Fetch($sql);  
    # cek pasien masih irna
     $sql = "select count(reg_id) as jkn_irna
            from klinik.klinik_registrasi a ";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id'])." and reg_status = 'I2' and reg_tanggal > '2018-05-31'";  
     $sql .= " and id_poli IN (select poli_id from global.global_auth_poli where poli_tipe= 'I')  ";  
     
     //echo $sql;
     $jkn_irna = $dtaccess->Fetch($sql);
     //print_r($jkn_irna);
     //die();
    # pasien irj di poli yang sama
     $sql = "select count(reg_id) as sameday
            from klinik.klinik_registrasi a ";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id'])." and id_poli = ".QuoteValue(DPE_CHAR,$_POST['klinik'])." and reg_tanggal='".date('Y-m-d')."' and reg_tipe_rawat = 'J'";  
     $sameday_irj = $dtaccess->Fetch($sql);      
     # pasien nama & tgl lahir sama
     $sql = "select count(*) as exist
            from global.global_customer_user ";
     $sql .= " WHERE UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,'%%'.strtoupper($_POST["cust_usr_nama"]).'%%')." and cust_usr_tanggal_lahir = ".QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));  
     $pasien_exist = $dtaccess->Fetch($sql); 
    // echo $sql;     
    /* $sameday_irj['sameday'] = 0;
    $jkn_irj['jkn_irj'] = 0;
    $jkn_irna['jkn_irna'] = 0; */
    

    if ( $sameday_irj['sameday'] > 0  ){  echo "<script>alert('Pasien sudah terdaftar di klinik'); window.history.back();</script>"; exit;} 
    elseif ( $jkn_irj['jkn_irj'] > 0   ){  echo "<script>alert('Pelayanan di poli ".$jkn_irj['string_agg']." belum di selesaikan.'); window.history.back();</script>"; exit;} 
    elseif ( $jkn_irna['jkn_irna'] > 0  ){  echo "<script>alert('Pasien rawat inap tidak bisa diregistrasi ke rawat jalan. Registrasi via rujukan di rawat inap.'); window.history.back();return true;</script>"; exit;} 
    elseif ( $pasien_exist['exist'] > 0 && $_POST['reg_status_pasien'] == "B" ){  echo "<script>alert('Pasien memiliki nama dan tanggal lahir yang sama dengan pasien No RM ".$pasien_exist['cust_usr_kode']." '); window.history.back();</script>"; exit;}  
    //echo  $sameday_irj['sameday'].$jkn_irj['jkn_irj'].$jkn_irna['jkn_irna'].$pasien_exist['exist'];
    //die();

    if ( $sameday_irj['sameday'] < 1 && $jkn_irj['jkn_irj'] < 1 && $jkn_irna['jkn_irna'] < 1 ) {     
   // die();
    $custUsrId = $_POST['cust_usr_id'];
    if(!$custUsrId) $custUsrId = $dtaccess->GetTransID();
    
    if($_POST['reg_status_pasien'] == "B" || $_POST['reg_status_pasien'] == "L" ) { 
        $sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["kel"]."'";
        $lokasidaerah = $dtaccess->Fetch($sql);
                   
         $dbTable = "global.global_customer_user";         
         $dbField[0] = "cust_usr_id";   // PK         
         $dbField[1] = "cust_usr_nama";
         $dbField[2] = "cust_usr_tempat_lahir";
         $dbField[3] = "cust_usr_tanggal_lahir";
         $dbField[4] = "cust_usr_umur";
         $dbField[5] = "cust_usr_alamat";
         $dbField[6] = "cust_usr_dusun";
         $dbField[7] = "cust_usr_no_hp";
         $dbField[8] = "id_dep";
         $dbField[9] = "cust_usr_jenis_kelamin"; 
         $dbField[10] = "cust_usr_agama"; 
         $dbField[11] = "cust_usr_no_identitas"; 
         $dbField[12] = "id_card"; 
         $dbField[13] = "id_pendidikan"; 
         $dbField[14] = "id_pekerjaan"; 
         $dbField[15] = "cust_usr_asal_negara"; 
         $dbField[16] = "id_status_perkawinan"; 
         $dbField[17] = "id_kecamatan";
         $dbField[18] = "id_kelurahan";
         $dbField[19] = "id_prop";
         $dbField[20] = "id_kota";
         $dbField[21] = "id_lokasi";
         $dbField[22] = "cust_usr_foto";
         $dbField[23] = "cust_usr_penanggung_jawab";
         $dbField[24] = "cust_usr_penanggung_jawab_status";
         $dbField[25] = "cust_usr_gol_darah";
         $dbField[26] = "cust_usr_gol_darah_resus";
         $dbField[27] = "cust_usr_no_jaminan";
         $dbField[28] = "cust_usr_jkn_asal";
         if ($norm_depan == 'n') {
          $dbField[29] = "cust_usr_kode";
          $dbField[30] = "cust_usr_kode_tampilan";
          if($_POST["id_card"] == "KTP") $dbField[31] = "cust_usr_nik";
         }else{ if($_POST["id_card"] == "KTP") { $dbField[29] = "cust_usr_nik";} }
        
         $dbValue[0] = QuoteValue(DPE_CHAR,$custUsrId);         
         $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($_POST["cust_usr_nama"]));
         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
         $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["tahun"]."~".$_POST["bulan"]."~".$_POST["hari"]);
         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["cust_usr_dusun"]);
         $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_hp"]);
         $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
         $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
         $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["cust_usr_agama"]);
         $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_identitas"]);
         $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_card"]);
         $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_pendidikan"]);
         $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_pekerjaan"]);
         $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["cust_usr_asal_negara"]);
         $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["id_status_perkawinan"]);
         $dbValue[17] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kecamatan"]);
         $dbValue[18] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kelurahan"]);
         $dbValue[19] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_propinsi"]);
         $dbValue[20] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kabupatenkota"]);          
         $dbValue[21] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_id"]);
         $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
         $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab"]);
         $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_status"]);
         $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_gol_darah"]);
         $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cust_usr_gol_darah_resus"]);
         $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_jaminan"]);
         $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn_asal"]);
         if ($norm_depan == 'n') {
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode_tampilan"]);
          $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_identitas"]);
        }else{ if($_POST["id_card"] == "KTP") { $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_identitas"]); } }
         
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        //if(($_POST['reg_status_pasien'] == "B" && $pasien_exist['exist'] < 1) || ($_POST['reg_status_pasien'] == "L" && $pasien_exist['exist'] > 1)) { 
            $dtmodel->Update() or die("update  error");    
        //} 
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
         //die();
    }
     #cari poli tipe 
     $sql = "select poli_tipe, poli_nama from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$_POST['klinik']);    
     $rs = $dtaccess->Execute($sql);
     $poliTipe = $dtaccess->Fetch($rs);
     //echo $poliTipe['poli_tipe']." - ".$poliTipe['poli_nama'];
     //die();
    //if(($_POST['reg_status_pasien'] == "B" && $pasien_exist['exist'] < 1) || ($_POST['reg_status_pasien'] == "L" && $pasien_exist['exist'] > 1)) { 
         include("reg_pas_lama.php"); // insert ke klinik.klinikregistrasi
         include("insert_inacbg.php"); // insert ke klinik.klinik_inacbg
         if ($_POST["instalasi"]=="I") include("reg_pas_irna.php");
         if ($poliTipe['poli_tipe']=="O") include("reg_pas_preop.php");
         if (!empty($_POST['reg_no_sep'])) include("update_jkn.php");
    //}
         if($_POST['cetak_sep'] == 'yes'){
            echo "
            <script type='text/javascript'>
             var url = '../cetak_ulang_sep/cetak_sep.php?id_reg=".$regId."';
             window.open( url );
            </script>
            ";
        }
         echo "
            <script type='text/javascript'>
             window.location.replace('registrasi_pasien_awal.php?usr_id=".$_POST["cust_usr_kode"]."&reg_sukses=true');
            </script>
            ";
 
         //header("location:".$backPage.$_POST["cust_usr_kode"]."&reg_sukses=true");
         exit();    

    } 
    //untuk cetak sep
    if(isset($_POST['cetak_sep']) && 
   $_POST['cetak_sep'] == 'yes'){ 
        
    $cetaksep ="yes";
        
    }
    
    }
     
     
   $tombolback = "<button class=\"btn btn-primary\" type=\"button\" onClick=\"window.location.replace
   ('".$backPage."')\">Kembali</button>";
    
    //cari data agama
     $sql = "select * from global.global_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataAgama = $dtaccess->FetchAll($rs); 
     
     //cari data pendidikan
     $sql = "select * from global.global_pendidikan order by pendidikan_urut";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPendidikan = $dtaccess->FetchAll($rs);
         
//cari data pekerjaan
     $sql = "select * from global.global_pekerjaan order by pekerjaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPekerjaan = $dtaccess->FetchAll($rs);

 //combo negara kebangsaan
     $sql = "select * from global.global_negara order by negara_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataNegara = $dtaccess->FetchAll($rs);    
     
      //cari status perkawinan
     $sql = "select * from global.global_status_perkawinan order by status_perkawinan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatus = $dtaccess->FetchAll($rs);
     
     // data mbuh
     $sql = "select * from global.global_status_pj order by status_pj_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatusPJ = $dtaccess->FetchAll($rs);
     
     // data instalasi
     $sql = "select instalasi_id, instalasi_nama from global.global_auth_instalasi";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataInstalasi = $dtaccess->FetchAll($rs);
     
     //cari data Sebab Sakit
     $sql = "select * from global.global_sebab_sakit";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataSebabSakit = $dtaccess->FetchAll($rs);
     
     // Data Layanan / tipe biaya //
     $sql = "select * from  global.global_tipe_biaya where tipe_biaya_aktif ='y' ";
     $rs = $dtaccess->Execute($sql);
     $dataLayanan = $dtaccess->FetchAll($rs);
     
     // Data Shift //
     $sql = "select * from  global.global_shift a where a.shift_aktif='y' order by shift_id limit 1";
     $rs = $dtaccess->Execute($sql);
     $dataShift = $dtaccess->FetchAll($rs);
        
     # data provinsi
     $sql = "select * from  global.global_lokasi where lokasi_kabupatenkota='00' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_id";
     $dataProvinsi = $dtaccess->FetchAll($sql); 

     $sql = "select * FROM global.global_lokasi where lokasi_propinsi='31' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota!='00' order by lokasi_nama";
     $dataKota = $dtaccess->FetchAll($sql);
    // print_r($dataKota);
     
     /*//cari data cara kunjungan
     $sql = "select * from global.global_rujukan";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataCaraKunjungan = $dtaccess->FetchAll($rs);*/
     
     // Data jenis pasien yang ditampilkan umum saja//
     $sql = "select * from  global.global_jenis_pasien a";
    // $sql .= " where jenis_id<>".PASIEN_BAYAR_BPJS." and jenis_flag='y'";
    //echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataJPasien = $dtaccess->FetchAll($rs);
     
     // Data jenis jkn
      $sql = "select * from  global.global_jkn order by jkn_id desc";
     $rs = $dtaccess->Execute($sql);
     $dataJKN = $dtaccess->FetchAll($rs);

      // Data jenis iks
      $sql = "select * from  global.global_perusahaan order by perusahaan_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataIKS = $dtaccess->FetchAll($rs);
     
     //data gedung
     $sql = "select * from global.global_gedung_rawat 
             order by gedung_rawat_nama, gedung_lantai_ke asc ";     
     $rs = $dtaccess->Execute($sql);
     $dataGedungRawat = $dtaccess->FetchAll($rs);
     
     //data kelas
     $sql = "select * from klinik.klinik_kelas order by kelas_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKelas = $dtaccess->FetchAll($rs);
     
     for($i=0,$n=count($dataKelas);$i<$n;$i++){
        unset($show);
        if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
        $opt_kategori[$i] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
     $opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
     
     $opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
     if($_POST["id_kamar"] && $_POST["id_kamar"]!="--"){
     $opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
     }
    }
     
     // Data dokter dan pelaksana
     $sql = "select * from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id
             where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='A') and a.id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);
     $dataPelaksana = $dtaccess->FetchAll($rs);
     
     // Data prosedur masuk
     $sql = "select * from global.global_prosedur_masuk";    
     $rs = $dtaccess->Execute($sql);
     $dataProsedurMasuk = $dtaccess->FetchAll($rs);
     
     // Data tipe poli
     $sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='M' or poli_tipe_id='R' or poli_tipe_id='L' or poli_tipe_id='G' or poli_tipe_id='J' or poli_tipe_id='I') order by poli_tipe_nama asc";    
     $rs = $dtaccess->Execute($sql);
     $dataTipe = $dtaccess->FetchAll($rs);
     
     // Data asal poli
     $sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J') order by poli_tipe_nama asc"; 
     $rs = $dtaccess->Execute($sql);
     $dataAsal = $dtaccess->FetchAll($rs);
     
     $lokasi = $ROOT."gambar/foto_pasien";
    $lokTakeFoto = $ROOT."gambar/foto_pasien"; 
    
    $tableHeader = "Registrasi Pasien";
    
    if ($_GET['usr_id']) { 
     # data pasien 
     $sql = "select * from global.global_customer_user where cust_usr_id = '$_GET[usr_id]'";    
     $rs = $dtaccess->Execute($sql);
     $row = $dtaccess->Fetch($rs);
     //print_r($row);
     #NO RM
     require_once("data_pasien_kode.php");
     if ($_GET['status_pasien'] == 'L') { $usr_kode = $row['cust_usr_kode']; }else { $usr_kode = $_POST["kode_pasien"]; };
     $arr = str_split($usr_kode,"2");
     $usr_kode_tampilan = implode(".",$arr);

     # poli ke 2
     $sql = "select reg_id, reg_tipe_rawat, id_poli, b.poli_nama
            from klinik.klinik_registrasi a
            left join global.global_auth_poli b on a.id_poli = b.poli_id";
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$row[cust_usr_id])." and reg_tanggal = ".QuoteValue(DPE_DATE, date('Y-m-d'));  
     $sql .= " order by reg_waktu desc";  
     $rs = $dtaccess->Execute($sql);
     $reg = $dtaccess->Fetch($rs);
     $polike2 = $dtaccess->FetchAll($rs);
    // echo $reg['reg_id'];
    //die($sql);
        # Tanggal Lahir
        $birthday = $row['cust_usr_tanggal_lahir']; 
        # Convert Ke Date Time
        $today = new DateTime();
        $biday = new DateTime( $birthday );
        $diff = $today->diff($biday);   
        $tahun = $diff->y;
        $bulan = $diff->m;
        $hari = $diff->d;

    if ($_GET['status_pasien'] == 'L') { 
     # cek bpjs trakhir pasien irna
     $sql = "select reg_id, reg_tipe_rawat, id_poli, reg_tanggal, b.rawatinap_tanggal_keluar, reg_cara_keluar_inap
            from klinik.klinik_registrasi a ";
     $sql .= " left join klinik.klinik_rawatinap b on a.reg_id = b.id_reg ";  
     $sql .= " WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$row[cust_usr_id])." and reg_jenis_pasien = '5' and reg_status ='I5' and reg_tipe_rawat = 'I'";  
     $sql .= " order by reg_waktu desc";  
     $jkn_last = $dtaccess->Fetch($sql);
     //die($sql);

        if($jkn_last){
        #hitung pasien jkn pulang kurang dari 7 hari
        $last = new DateTime( $jkn_last['rawatinap_tanggal_keluar'] );
        $last_jkn = $today->diff($last);
        $last_jkn_hari = $last_jkn->d;
      // echo $last_jkn_hari;
        //die();
        }
    }
    #fix kebangsaan 
    if($row["cust_usr_asal_negara"]=="")$row["cust_usr_asal_negara"] ='1';
    }
?>
 

<!DOCTYPE html>
<html lang="en">
  
    <?php require_once($LAY."header.php") ?>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
    <script src="assets/fancybox/jquery.easing-1.3.pack.js"></script>
    <script src="assets/webcam/webcam.js"></script>
    <!-- sweet alert -->
  <script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.min.js"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.css">
    <script type="text/javascript">
    
    function cek_kepesertaan(param){

        $.ajax({
            type: 'POST',
            url:'cek_kepesertaan.php',
            data: { param: param },
            dataType: 'json',
            beforeSend: function() {
              swal({
                title: 'Mohon Tunggu!',
                html: 'Sedang Menghubungi Server BPJS. <br> <i>Jika Tidak selesai dalam 30 detik, silahkan klik dimana saja diluar box dan ulangi proses ini.</i>',
                onOpen: () => {
                  swal.showLoading()
                }
              })
            },
            success:function(data){
              swal.close();
        
              var status = data.metaData.code;
              var messagesresponse = data.metaData.message;
            
              if(status != '200'){
                alert(messagesresponse);
              }else{
                alert('Status pasien BPJS / ASKES '+data.response.peserta.statusPeserta.keterangan+' Atas Nama : '+data.response.peserta.nama);  
                tgllahir = data.response.peserta.tglLahir;
                tanggal = tgllahir.split("-");
                d = tanggal[2];
                m = tanggal[1];
                y = tanggal[0];
           
                Umur(d+'-'+m+'-'+y);
                //alert(data.response.peserta.jenisPeserta.keterangan);
                //$('#cust_usr_nama').val(data.response.peserta.nama);
                //$('#cust_usr_tanggal_lahir').val(d+'-'+m+'-'+y); 
                //$('#cust_usr_jenis_kelamin').val(data.response.peserta.sex);
                $('#cust_usr_jkn_asal').val(data.response.peserta.jenisPeserta.keterangan);
                $('#hak_kelas_inap').val(data.response.peserta.hakKelas.kode);
                $('#reg_ppk_rujukan').val(data.response.peserta.provUmum.kdProvider);
                $('#reg_dokter_sender').val(data.response.peserta.provUmum.nmProvider);

                //cek nama di server bpjs = server rs
                var namaBPJS = data.response.peserta.nama;
                var namaRS = $('#cust_usr_nama').val();
                var match = namaBPJS.match(namaRS);

                if(match){
                    console.log(match);
                    $('#cust_usr_no_identitas').val(data.response.peserta.nik);
                    $('#cust_usr_no_jaminan').val(data.response.peserta.noKartu);
                } else {
                    alert("Nama pasien tidak sesuai dengan nama peserta bpjs");
                }

                var jns = data.response.peserta.jenisPeserta.keterangan;
                var pbi = jns.match(/PBI/);

                if (pbi) {
                    $('#tipe_jkn').val('1');
                } else {
                    $('#tipe_jkn').val('2');
                }

                $('#reg_jenis_pasien').val('5');
                $('#bpjs').css('display','block');
                $("#div_jkn").css('display','block');
                $("#tipe_jkn").removeAttr("disabled") ; 

                //$('#reg_no_rujukan').val(data.response.peserta.jenisPeserta.keterangan);
                //$('#reg_tgl_rujukan').val(data.response.peserta.jenisPeserta.keterangan);
                //$('#catatan_bpjs').val(data.response.peserta.statusPeserta.keterangan);
                
                //show form bpjs
                $('#bpjs').css('display','block');
              
              } 
            },
        });         
    }
    
    function create_sep(){
        $.ajax({
            type: 'POST',
            url:'create_sep.php',
            data: $("form#form_utama").serialize(),
            dataType: 'json',
            beforeSend: function() {
              swal({
                title: 'Mohon Tunggu!',
                html: 'Sedang Menghubungi Server BPJS. <br> <i>Jika Tidak selesai dalam 30 detik, silahkan klik dimana saja diluar box dan ulangi proses ini.</i>',
                onOpen: () => {
                  swal.showLoading()
                }
              })
            },
            success:function(data){
              swal.close();
              var status = data.metaData.code;
              var messagesresponse = data.metaData.message;
            
              //alert(messagesresponse);
              if(status != '200'){
                alert(messagesresponse);
              }else{
                $('#reg_no_sep').val(data.response.sep.noSep);
                //show form bpjs
                $('#bpjs').css('display','block');
              } 
            },
        });         
    }
    
    //fix load cmb propinsi, kota dst
    $(document).ready(function(){
        //select 2
        $('#dokter').select2();
        //auto complete
        $('#diagnosa').autocomplete({
          serviceUrl: 'get_icd.php',
          paramName: 'q',
          transformResult: function(response) {
            var data = jQuery.parseJSON(response);
            return {
                suggestions: $.map(data, function(item) {
                    return { value: item.icd_nomor+" - "+item.icd_nama, data: item.icd_nomor };
                })
            };
          },
          onSelect: function (suggestion) {
            $('#reg_diagnosa_awal').val(suggestion.data);
          }
        });
    
        var a = $('#cust_usr_id').val();
        setTimeout(function() {
            $.post( "get_pasien.php", { usr_id: a },
                    function( data ) {
                        //alert(data.id_prop);
                        $('#cust_usr_gol_darah').val(data.cust_usr_gol_darah);
                        $('#cust_usr_gol_darah_resus').val(data.cust_usr_gol_darah_resus);
                        if (data.id_prop != null) { ajaxkota(data.id_prop); };
                        var delay = 500;
                        var delay2 = 700;
                        var delay3 = 1000;
                        setTimeout(function() {
                            $('#kota').val(data.id_kota+"&prop="+data.id_prop);
                            ajaxkec(data.id_kota+"&prop="+data.id_prop);
                    
                        }, delay);
                        setTimeout(function() {
                            $('#kec').val(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
                            ajaxkel(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
                            //console.log("anjay");
                        }, delay2);
                        setTimeout(function() {
                            $('#kel').val(data.id_prop+"."+data.id_kota+"."+data.id_kecamatan+"."+data.id_kelurahan);
                        }, delay3);
                        
                      },"json");            
        }, 300);
         //jika ada poli ke 2           
        <?php if (count($row) > 0 ) { ?>
    
            $('#klinik_asal').val('<?php echo $reg['id_poli']; ?>');            

        <?php } ?>
    });
    </script>

    
<script type="text/javascript">
function pad (str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}
$(document).ready(function(){
    //auto get klinik irj when load
    getKlinik('J');
    $('#reg_prosedur_masuk').val('2');
    getProsedurMasuk('2');
    setTimeout(function () {
        $('#reg_rujukan_id').val('1');
        getRujukanDet('1')
    },1000);

    $('#id_kamar').on('change',function(){ //membaca id poli berdasarkan id kamar -> Rawat inap
        var id = $(this).val();
        $.get('read_back_poli.php',{kamar_id:id},function(result){
            //alert(result.id_poli);
            $('#klinik').val(result.id_poli);
        },'json');
    })
        
    $('#instalasi').on('change',function(){
        if ($(this).val() == 'I') {
            $("#div_klinik").css('display','none');
            $("#div_klinik_asal").css('display','none');
            $("#klinik_asal").attr('disabled','disabled');
            $("#id_bed").attr('required','required');
            $("#div_gedung").css('display','block');
            $("#div_kelas").css('display','block');
            $("#div_kamar").css('display','block');
            $("#div_bed").css('display','block');
            $("#div_asal").css('display','block');
            $("#div_reg_tanggal").css('display','block');
            
        } else { 
            $("#id_bed").removeAttr('required');
            $("#div_klinik").css('display','block');
            $("#div_klinik_asal").css('display','block');
            $("#klinik_asal").removeAttr('disabled');
            $("#div_gedung").css('display','none');
            $("#div_kelas").css('display','none');
            $("#div_kamar").css('display','none');
            $("#div_bed").css('display','none');
            $("#div_asal").css('display','none');
            $("#div_reg_tanggal").css('display','block');
            
        }
        
        getKlinik($(this).val());
        jnsLayananBPJS($(this).val());
    });

    function jnsLayananBPJS(val) {
        if (val == 'I') {
            $('#reg_jenis_layanan').val('ri');
        } else {
            $('#reg_jenis_layanan').val('rj');
        }
    }

    function getKlinik(instalasi_id) {
        if(instalasi_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'instalasi_id='+instalasi_id,
                success:function(html){
                    $('#klinik').html(html);
                    $('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
                }
            }); 
        }else{
            $('#klinik').html('<option value="">Pilih Instalasi Dahulu</option>');
            $('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
        }
    }
    
    $('#klinik').on('change',function(){
        var klinik_id = $(this).val();
        if(klinik_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'poli_id='+klinik_id,
                success:function(html){
                    $('#dokter').html(html);
                }
            });
        $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'id_poli='+klinik_id,
                success:function(html){
                    $('#paket').html(html);
                }
            });             
        }else{
             $('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
             $('#paket').html('<option value="">Pilih Klinik Dahulu</option>');
        }
    });
    
    $('#reg_jenis_pasien').on('change',function(){
        var jenis_pasien = $(this).val();
        if(jenis_pasien=='5'){ //pasien jkn
            $('#bpjs').css('display','block');
            $("#div_jkn").css('display','block');
            $("#div_iks").css('display','none');
            $("#tipe_jkn").removeAttr("disabled") ; 
            $("#tipe_iks").attr("disabled","disabled");
        } else if(jenis_pasien=='7'){ //cara bayar iks
            $('#bpjs').css('display','none');
            $("#div_jkn").css('display','none');
            $("#div_iks").css('display','block');
            $("#tipe_jkn").attr("disabled","disabled");
            $("#tipe_iks").removeAttr("disabled") ; 
        }else{
            $('#bpjs').css('display','none');   
            $("#div_jkn").css('display','none');
            $("#div_iks").css('display','none');
            $("#tipe_jkn").attr("disabled","disabled");
            $("#tipe_iks").attr("disabled","disabled");
        }
    });
    
    $('#id_kelas').on('change',function(){
        var kelas_id = $(this).val();
        var gedung_id = $('#id_gedung_rawat').val();
        if(kelas_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'kelas_id='+kelas_id+'&gedung_id='+gedung_id,
                success:function(html){
                    $('#id_kamar').html(html);
                    $('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>'); 
                }
            }); 
        }else{
            $('#id_kamar').html('<option value="">Pilih Gedung dan Kelas Dahulu</option>'); 
            $('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
        }
    });

    $('#id_gedung_rawat').on('change',function(){
        var kelas_id = $('#id_kelas').val();
        var gedung_id = $(this).val();
        if(kelas_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'kelas_id='+kelas_id+'&gedung_id='+gedung_id,
                success:function(html){
                    $('#id_kamar').html(html);
                    $('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>'); 
                }
            }); 
        }else{
            $('#id_kamar').html('<option value="">Pilih Gedung dan Kelas Dahulu</option>'); 
            $('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
        }
    });
    
    $('#id_kamar').on('change',function(){
        var kamar_id = $(this).val();
        if(kamar_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'kamar_id='+kamar_id,
                success:function(html){
                    $('#id_bed').html(html);
                }
            }); 
        }else{
            $('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
        }
    });
    
    $('#id_bed').on('change',function(){ //get dokter irna
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'irna=irna',
                success:function(html){
                    $('#dokter').html(html);
                }
            }); 
    });

    $('#reg_prosedur_masuk').on('change',function(){ 
        var prosedur_id = $(this).val();
        getProsedurMasuk(prosedur_id);
    });

    function getProsedurMasuk(val){ 
        var prosedur_id = val;
        
        if(prosedur_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'prosedur_id='+prosedur_id,
                success:function(html){
                    $('#reg_rujukan_id').html(html); 
                    $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
                }
            }); 
        }else{
            $('#reg_rujukan_id').html('<option value="">Pilih Prosedur Dahulu</option>');
            $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
        }
    };

    $('#reg_rujukan_id').on('change',function(){ 
        var rujukan_id = $(this).val();
        getRujukanDet(rujukan_id);
    });

    function getRujukanDet(val){ 
        var rujukan_id = val;
        if(rujukan_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'rujukan_id='+rujukan_id,
                success:function(html){
                    $('#reg_rujukan_det').html(html); 
               }
            }); 
        }else{
            $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
        }
    };
    
});
</script>

<script>
   //Perhitungan Umur//
   function Umur(umur) {
      tgllahir = umur;
      tanggal = tgllahir.split("-");
      t = tanggal[0];
      bln = (tanggal[1] - 1);
      thn = tanggal[2];
   
      var d = new Date();
      d.setDate(t);
      d.setMonth(bln);
      d.setFullYear(thn);
      x1 = d.getTime();
      var d2 = new Date();
      x2 = d2.getTime();
      beda = x2-x1;
      var umurtahun = beda/(1000*60*60*24*365);
      var umurbulan = (umurtahun - Math.floor(umurtahun)) * 12;
      var umurhari = (umurbulan - Math.floor(umurbulan)) * 31;
      
      document.getElementById("tahun").value = Math.floor(umurtahun);
      document.getElementById("bulan").value = Math.floor(umurbulan);
      document.getElementById("hari").value = Math.floor(umurhari);
            
    }

    function TanggalLahir(tanggal) {
      umur = document.getElementById("tahun").value;

      var e = new Date();
      
      skr = e.getFullYear();

      thn = skr-umur;
      var tahunlahir = thn;
      document.getElementById("cust_usr_tanggal_lahir").value = "01-01-" + Math.floor(tahunlahir);
            
    }
</script>

<script type="text/javascript">
$(document).ready(function(){
    
    var camera = $('#camera'),
        photos = $('#photos'),
        screen =  $('#screen');

    var template = '<a href="<?php echo $ROOT;?>gambar/foto_pasien/{src}" rel="cam" '
        +'style="background-image:url(<?php echo $ROOT;?>gambar/thumbs/{src})"></a>';

    /*----------------------------------
        Setting up the web camera
    ----------------------------------*/
  webcam.set_swf_url('assets/webcam/webcam.swf');
    webcam.set_api_url('upload_pasien.php');    // The upload script
    webcam.set_quality(80);             // JPEG Photo Quality
    webcam.set_shutter_sound(true, 'assets/webcam/shutter.mp3');

    // Generating the embed code and adding it to the page: 
    screen.html(
    webcam.get_html(screen.width(), screen.height())
    );

    /*----------------------------------
        Binding event listeners
    ----------------------------------*/
    var shootEnabled = false;       
    $('#shootButton').click(function(){
        
        if(!shootEnabled){
            return false;
        }
        webcam.freeze();
        togglePane();
        return false;
    });
    
    $('#cancelButton').click(function(){
        webcam.reset();
        togglePane();
        return false;
    });
   
    $('#uploadButton').click(function(){
 
        webcam.upload();
        webcam.reset();
        togglePane();  
        return false;
    });

    camera.find('.settings').click(function(){
        if(!shootEnabled){
            return false;
        }
        
        webcam.configure('camera');
    });

    // Showing and hiding the camera panel: 
    $('.camTop').click(function(){

            camera.animate({
                bottom:-350
            });

    });
  
    var showns = false;
    $('.camTops').click(function(){
        
        if(showns){
            camera.animate({
                bottom:-350
            });
        }
        else {
             camera.animate({
                bottom:20
            },{easing:'easeOutExpo',duration:'slow'});
        }
        
        showns = !showns;
    });

    /*---------------------- 
        Callbacks
    ----------------------*/

    webcam.set_hook('onLoad',function(){
        // When the flash loads, enable
        // the Shoot and settings buttons:
        shootEnabled = true;
    });
    
    webcam.set_hook('onComplete', function(msg){
        
        // This response is returned by upload.php
        // and it holds the name of the image in a
        // JSON object format:
        msg1 = $.parseJSON(msg);
    
        if(msg.error){
   // alert('masuk foto');
            alert(msg1.message);
        }
        else {  
     //     alert(msg1.filename);
             //Adding it to the page;    
      document.getElementById('cust_usr_foto').value=msg1.filename; 
      
      document.original.src='<?php echo $lokTakeFoto."/";?>'+msg1.filename;  
      //alert(kepet);
      alert('Foto Pasien telah tersimpan');
            photos.prepend(templateReplace(template,{src:msg1.filename}));
            initFancyBox();
        }
    });
    
      webcam.set_hook('onError',function(e){
        screen.html(e);
    });
    
  
    // This function toggles the two
    // .buttonPane divs into visibility:
    function togglePane(){
        var visible = $('#camera .buttonPane:visible:first');
        var hidden = $('#camera .buttonPane:hidden:first');
        
        visible.fadeOut('fast',function(){
            hidden.show();
        });
    }
    
    // Helper function for replacing "{KEYWORD}" with
    // the respectful values of an object:
    function templateReplace(template,data){
        return template.replace(/{([^}]+)}/g,function(match,group){
            return data[group.toLowerCase()];
        });
    }
});


</script> 


    <!--function UMUR -->
   <script>
  
var ajaxku;
function ajaxkota(id){
    ajaxku = buatajax();
    var url="select_kota.php";
    url=url+"?q="+id;
    url=url+"&sid="+Math.random();
    ajaxku.onreadystatechange=stateChanged;
    ajaxku.open("GET",url,true);
    ajaxku.send(null);
}

function ajaxkec(id){
    ajaxku = buatajax();
    var url="select_kota.php";
    //alert(id);
    url=url+"?kec="+id;
    url=url+"&sid="+Math.random();
    ajaxku.onreadystatechange=stateChangedKec;
    ajaxku.open("GET",url,true);
    ajaxku.send(null);
}

function ajaxkel(id){
    ajaxku = buatajax();
    var url="select_kota.php";
    url=url+"?kel="+id;
    url=url+"&sid="+Math.random();
    ajaxku.onreadystatechange=stateChangedKel;
    ajaxku.open("GET",url,true);
    ajaxku.send(null);
}

function buatajax(){
    if (window.XMLHttpRequest){
    return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
    return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}
function stateChanged(){
    var data;
    
    if (ajaxku.readyState==4){
    data=ajaxku.responseText;
    //alert(data);
    if(data.length>=0){
    document.getElementById("kota").innerHTML = data
    }else{
    document.getElementById("kota").value = "<option selected>Pilih Kota/Kab</option>";
    }
    }
}

function stateChangedKec(){
    var data;
    if (ajaxku.readyState==4){
    data=ajaxku.responseText;
    //alert(data);
    if(data.length>=0){
    document.getElementById("kec").innerHTML = data
    }else{
    document.getElementById("kec").value = "<option selected>Pilih Kecamatan</option>";
    }
    }
}

function stateChangedKel(){
    var data;
    if (ajaxku.readyState==4){
    data=ajaxku.responseText;
    //alert(data);
    if(data.length>=0){
    document.getElementById("kel").innerHTML = data
    }else{
    document.getElementById("kel").value = "<option selected>Pilih Kelurahan/Desa</option>";
    }
    }
}
// end ajax lokasi
</script> 
<!-- ajax untuk cetak sep-->
<script>
<?php if($cetaksep=='yes'){?>
 url = 'cetak_sep.php?id_reg=<? echo $regId;?>';
 window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
<?php } ?>  
</script>

<?php if ($_GET["status_pasien"]== "B" && $_GET["bpjs"]== "true") {
        echo "<script>
                var pk = ".$_GET['noKartu'].";
                var param = pad(pk, 13);
                cek_kepesertaan(param)
              </script>";
    } 
?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        
        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
        <form id="form_utama" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
        <input type="hidden" value="<?php echo $_GET['status_pasien']; ?>" name="reg_status_pasien"> 
        <input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $row['cust_usr_id']; ?>">
        <input type="hidden" name="cust_usr_kode_tampilan" id="cust_usr_kode_tampilan" value="<?php echo $usr_kode_tampilan; ?>">
         
       <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Registrasi Pasien <? if($_GET['status_pasien'] == 'B') echo "Baru"; else echo "Lama"; ?></h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <!-- Row 1 Input Data Pasien -->
            <div class="row form">
            
            <!-- Kolom 1 Input Data Pasien -->
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <br />
                    
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">No RM <span class="required">*</span>
                        </label>
                        <div class="col-md-3 col-sm-3 col-xs-3">
                          <input id="cust_usr_kode" name="cust_usr_kode" readonly="readonly" value="<?php echo $usr_kode;?>" class="form-control col-md-5 col-xs-5" type="text">
                          </div>
                          <div class="col-md-6 col-sm-6 col-xs-6">
                         <a href="pasien_find.php?TB_iframe=true&height=550&width=800&modal=true" class="thickbox " title="Cari Pasien">
                            <i id="pasien_find" class="fa fa-search" style="display:none;"  > Cari No RM</i>  
                         </a>
                         
                           <span id="RMMt" style="display:none;" ><input id="RMM" type="checkbox"> Input RM Manual</span>
                        </div>
                      </div>                            
                      <div class="field form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nama <span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input id="cust_usr_nama" name="cust_usr_nama" value="<?php echo $row["cust_usr_nama"];?>" class="form-control col-md-7 col-xs-12" data-validate-length-range="2" data-validate-words="2"  placeholder="dua kata contoh:Moch Mansyur" required="required" type="text"> 
                          <!-- data-validate-length="min1" data-validate-words="2" -->
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tempat Lahir<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="cust_usr_tempat_lahir" name="cust_usr_tempat_lahir" value="<?php echo $row["cust_usr_tempat_lahir"];?>" required="required" data-validate-length-range="5,90" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>

                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir<span class="required">*</span></label>
                        <div class="col-md-4 col-sm-4  col-xs-12">
                          <input type="text" class="form-control" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" value="<?php echo format_date($row["cust_usr_tanggal_lahir"]);?>" data-inputmask="'mask': '99-99-9999'" onChange="Umur(this.value);" required="required" />
                         <!-- <input type="text" id="tgl" name="tgl" size="2" maxlength="2" value="<?php echo $_POST["tgl"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/> -
                          <input type="text" id="bln" name="bln" size="2" maxlength="2" value="<?php echo $_POST["bln"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/> -
                          <input type="text" id="thn" name="thn" size="4" maxlength="4" value="<?php echo $_POST["thn"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/><font color="red">*</font>-->
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="umur">Umur<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="tahun" id="tahun" size="3" maxlength="3" value="<?php echo $tahun;?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> tahun
                        <input type="text" name="bulan" id="bulan" size="3" maxlength="3" value="<?php echo $bulan;?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> bulan  
                        <input type="text" name="hari" id="hari" size="3" maxlength="3" value="<?php echo $hari;?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> hari
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Jenis Kelamin<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="cust_usr_jenis_kelamin" class="form-control" name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
                            <option value="L" <?php if($row["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
                            <option value="P" <?php if($row["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
                        </select>
                        </div>
                      </div>
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Agama<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">   
                        <option value="" >[ Pilih Agama ]</option>  
                          <?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
                            <option value="<?php echo $dataAgama[$i]["agm_id"];?>"
                            <?php if ($row["cust_usr_agama"] == $dataAgama[$i]["agm_id"]) echo "selected"?> >
                            <?php echo $dataAgama[$i]["agm_nama"];?>
                            </option>
                          <?php } ?>
                    </select>
                    </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gol. Darah
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <div class="col-md-5 col-sm-5 col-xs-5">
                                <select class="form-control" name="cust_usr_gol_darah" id="cust_usr_gol_darah">   
                                    <option <?php if ($row['cust_usr_gol_darah'] = '-') { echo "selected"; } ?> value="-">Tidak Tahu</option>  
                                    <option <?php if ($row['cust_usr_gol_darah'] = 'A') { echo "selected"; } ?> value="A">A</option>  
                                    <option <?php if ($row['cust_usr_gol_darah'] = 'AB') { echo "selected"; } ?> value="AB">AB</option>  
                                    <option <?php if ($row['cust_usr_gol_darah'] = 'B') { echo "selected"; } ?> value="B">B</option>  
                                    <option <?php if ($row['cust_usr_gol_darah'] = 'O') { echo "selected"; } ?> value="O">O</option>  
                                </select>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <span class="control-label col-md-3 col-sm-3 col-xs-3" >Rhesus</span>
                                <div class="col-md-8 col-sm-8 col-xs-8">
                                  <select class="form-control" name="cust_usr_gol_darah_resus" id="cust_usr_gol_darah_resus">   
                                    <option <?php if ($row['cust_usr_gol_darah_resus'] = 'Positif') { echo "selected"; } ?> value="Positif">Positif</option>  
                                    <option <?php if ($row['cust_usr_gol_darah_resus'] = 'Negatif') { echo "selected"; } ?> value="Negatif">Negatif</option>  
                                  </select>
                                </div>
                            </div>
                        </div>
                      </div>
                    
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="alamat">Alamat<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <textarea class="form-control" id="cust_usr_alamat" name="cust_usr_alamat"> <?php echo htmlspecialchars($row["cust_usr_alamat"]);?></textarea>
                          <!-- <input type="text" id="cust_usr_alamat" name="cust_usr_alamat" value="<?php echo $row["cust_usr_alamat"];?>" required="required" data-validate-length-range="5" class="form-control col-md-7 col-xs-12"> -->
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Nama Dusun/RT/RW <span class="required">&nbsp;</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="cust_usr_dusun" name="cust_usr_dusun" value="<?php echo $row["cust_usr_dusun"];?>" data-validate-length-range="5,20" class="optional form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Propinsi <span class="required">&nbsp;</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_prop" id="id_prop" onchange="ajaxkota(this.value)">
                            <!-- <option value="">Pilih Provinsi</option> -->
                            <?php                                                      
                            for($i=0,$n=count($dataProvinsi);$i<$n;$i++) { ?>  
                              <option value="<?php echo $dataProvinsi[$i]['lokasi_propinsi'];?>" 
                              <?php if($dataProvinsi[$i]["lokasi_propinsi"]==$row["id_prop"]) { echo "selected"; } elseif($dataProvinsi[$i]["lokasi_propinsi"]=='31') echo "selected";?>

                                ><?php echo $dataProvinsi[$i]['lokasi_nama'];?></option>';
                            <? } ?>                                                                   
                            <option value="0">Tidak Tahu</option>
                          </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kota
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="kota" id="kota" onchange="ajaxkec(this.value)">
                            <option value="">Pilih Kota</option>
                            <?php                                                      
                            for($i=0,$n=count($dataKota);$i<$n;$i++) { ?>  
                              <option value="<?php echo $dataKota[$i]['lokasi_kabupatenkota'].'&prop=31';?>" <?php if($dataKota[$i]["lokasi_kabupatenkota"]==$row["id_prop"]) echo "selected";?>><?php echo $dataKota[$i]['lokasi_nama'];?></option>';
                            <? } ?>                                                                   
                            <option value="0">Tidak Tahu</option>
                          </select> 
                        </div>
                      </div>

                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kecamatan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="kec" id="kec" onchange="ajaxkel(this.value)">
                            <option value="">Pilih Kecamatan</option>
                          </select> 
                        </div>
                      </div>

                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kelurahan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="kel" id="kel">
                            <option value="">Pilih Kelurahan/Desa</option>
                          </select> 
                          <input type="hidden" id="id_kel" name="id_kel" value="<?php echo $_POST["id_prop"].".".$_POST["id_kota"].".".$_POST["id_kecamatan"].".".$_POST["id_kelurahan"];?>"/>
                        </div>
                      </div>                                           
                      
                  </div>
                </div>
                
                <!-- begin kolom kanan row 2 // panel instalasi -->
                <div class="x_panel">
                  <div class="x_content">
                    <br />
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No. HP <span class="required">*</span>
                        </label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <input type="text" id="cust_usr_no_hp" name="cust_usr_no_hp" value="<?php echo $row["cust_usr_no_hp"];?>" maxlength="13" required="required" data-validate-length-range="10,13" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nik">No. KTP / Identitas
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                         <input type="text" class="form-control" name="cust_usr_no_identitas" id="cust_usr_no_identitas" size="30" maxlength="65" value="<?php echo $row["cust_usr_no_identitas"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                         &nbsp;Jenis :
                     <select name="id_card" class="form-control" onKeyDown="return tabOnEnter(this, event);">
                            <option value="KTP" <?php if($row["id_card"]=="KTP")echo "selected";?>>KTP</option>
                            <option value="SIM" <?php if($row["id_card"]=="SIM")echo "selected";?>>SIM</option>
                            <option value="PASPOR" <?php if($row["id_card"]=="PASPOR")echo "selected";?>>PASPOR</option>
                        </select>
 
                        </div>
                      </div>
                       
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Pendidikan 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_pendidikan" id="id_pendidikan" onKeyDown="return tabOnEnter(this, event);"> 
                    <option value="--" >[ Pilih sekolah ]</option>  
                    <?php for($i=0,$n=count($dataPendidikan);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataPendidikan[$i]["pendidikan_id"];?>" <?php if($dataPendidikan[$i]["pendidikan_id"]==$row["id_pendidikan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPendidikan[$i]["pendidikan_nama"];?></option>
                      <?php } ?>
                </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pekerjaan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_pekerjaan" id="id_pekerjaan" onKeyDown="return tabOnEnter(this, event);">   
                     <option value="" >Pilih Pekerjaan</option>
                     <?php for($i=0,$n=count($dataPekerjaan);$i<$n;$i++){ ?>
                     <option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"];?>" <?php if($dataPekerjaan[$i]["pekerjaan_id"]==$row["id_pekerjaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPekerjaan[$i]["pekerjaan_nama"];?></option>
                           <?php } ?>   
                 </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kebangsaan</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="cust_usr_asal_negara" id="cust_usr_asal_negara" onKeyDown="return tabOnEnter(this, event);">   
                     <option value="" >Pilih Kebangsaan</option>
                     <?php for($i=0,$n=count($dataNegara);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataNegara[$i]["negara_id"];?>" <?php if($dataNegara[$i]["negara_id"]==$row["cust_usr_asal_negara"]) echo "selected"; ?>><?php echo $dataNegara[$i]["negara_nama"]." ( ".$dataNegara[$i]["negara_kode"]." ) ";?></option>
                        <?php } ?>  
                 </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Status Pernikahan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_status_perkawinan" id="id_status_perkawinan" onKeyDown="return tabOnEnter(this, event);">   
                     <option value="" >Pilih Status Perkawinan</option>
                     <?php for($i=0,$n=count($dataStatus);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataStatus[$i]["status_perkawinan_id"];?>" <?php if($dataStatus[$i]["status_perkawinan_id"]==$row["id_status_perkawinan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatus[$i]["status_perkawinan_nama"];?></option>
                       <?php } ?>   
                 </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Nama Penanggung Jawab
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" class="form-control" name="cust_usr_penanggung_jawab" id="cust_usr_penanggung_jawab" size="30" maxlength="65" value="<?php echo $row["cust_usr_penanggung_jawab"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                     &nbsp;Status :
                     <select class="form-control" name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">    
                        <option value="" >- Pilih Hubungan -</option>
                        <?php for($i=0,$n=count($dataStatusPJ);$i<$n;$i++){ ?>
                        <option value="<?php echo $dataStatusPJ[$i]["status_pj_id"];?>" <?php if($dataStatusPJ[$i]["status_pj_id"]==$row["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatusPJ[$i]["status_pj_nama"];?></option>
                              <?php } ?>    
                     </select>
                        </div>
                      </div>
                      <div hidden class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="telephone">Berat Lahir <span class="required">*</span>
                        </label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <input type="text" id="cust_berat_lahir" name="cust_berat_lahir" value="<?php echo $_POST["cust_berat_lahir"];?>" maxlength="13" data-validate-length-range="1,13" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                  </div>
                  
                </div>
                <!-- end kolom kanan row 2 -->
                
              </div>
              <!-- END KOLOM 1 DATA PASIEN -->

            <!-- Kolom 2 Input Data Pasien -->
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Foto Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                        <!--td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td-->
                        <img hspace="2" height="100" name="original" id="original" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_foto"]) echo $lokTakeFoto."/".$_POST["cust_usr_foto"]; else echo $lokTakeFoto."/default.jpg";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_pic.php?orifoto='+ document.frmFind.cust_usr_foto.value + '&nama=<?php echo $_POST["vcust_usr_kode"];?>','UploadFoto')">
                        <input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>">
                        <br/>
                        <div class="camTops"  alt="foto pasien" title="foto pasien">
                            <input type="button" id="Ambil Foto" size="35" name="Ambil Foto" value="Ambil Foto" class="btn btn-default">
                        </div>  
                    </div>                    
                  </div>
                </div>
                
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Registrasi</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content" >

                    <div id="div_reg_tanggal" class="col-md-6 col-sm-12 col-xs-12" style="display:block;">
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tanggal Registrasi</label>
                        <input type="text" name="reg_tanggal" class="form-control" value="<?php echo date('d-m-Y'); ?>" data-inputmask="'mask': '99-99-9999'">
                    </div>
                  
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Bayar</label>
                        <select id="reg_jenis_pasien" class="select2_single form-control" name="reg_jenis_pasien">
                        <?php 
                            for($i=0,$n=count($dataJPasien);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataJPasien[$i]["jenis_id"];?>"
                            <?php if ($row["cust_usr_no_jaminan"] != "" && $dataJPasien[$i]["jenis_id"]=='5') { echo "selected"; } elseif ($row["cust_usr_no_jaminan"] == "" && $dataJPasien[$i]["jenis_id"]=='2') { echo "selected"; } ?>>
                                <?php echo $dataJPasien[$i]["jenis_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_jkn" class="col-md-6 col-sm-6 col-xs-12" <?php if(empty($row["cust_usr_no_jaminan"]) || $row["cust_usr_no_jaminan"] == "" ){  echo 'style="display:none;"'; }?>>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Tipe JKN</label>
                        <select id="tipe_jkn" class="select2_single form-control" name="tipe_jkn" <?php if(empty($row["cust_usr_no_jaminan"]) || $row["cust_usr_no_jaminan"] == "" ){  echo 'disabled'; }?>>
                        <?php 
                            for($i=0,$n=count($dataJKN);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataJKN[$i]["jkn_id"];?>">
                                <?php echo $dataJKN[$i]["jkn_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>

                    <div id="div_iks" class="col-md-6 col-sm-6 col-xs-12" style="display: none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Perusahaan</label>
                        <select id="tipe_iks" class="select2_single form-control" name="perusahaan" disabled="">
                        <?php 
                            for($i=0,$n=count($dataIKS);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataIKS[$i]["perusahaan_id"];?>">
                                <?php echo $dataIKS[$i]["perusahaan_nama"];?>
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Diagnosa Awal</label>
                        <input name ="diagnosa" type="text" class="form-control" id="diagnosa" placeholder="" value="<?php echo $row["reg_diagnosa_awal"]; ?>">
                        <input name ="reg_diagnosa_awal" type="hidden" class="form-control" id="reg_diagnosa_awal" value="<?php echo $row["reg_diagnosa_awal"]; ?>">
                    </div>
                    <div class="clearfix"><br></div>
  
                    <div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Instalasi</label>
                        <select id="instalasi" class="select2_single form-control" name="instalasi">
                           <!--  <option value="">- Pilih instalasi -</option> -->
                          <?php for ($i=0; $i < count($dataTipe); $i++) { ?>
                            <option value="<?php echo $dataTipe[$i]['poli_tipe_id'] ?>" 
                                <?php if ($reg['reg_tipe_rawat'] == $dataTipe[$i]['poli_tipe_id']) { echo "selected"; } elseif ($dataTipe[$i]['poli_tipe_id'] == 'J') { echo "selected"; } ?>
                            ><?php echo $dataTipe[$i]['poli_tipe_nama'] ?></option>
                          <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_klinik" class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Poli Klinik</label>
                        <select id="klinik" class="select2_single form-control" name="klinik" required>
                            <option value="">- Pilih Klinik -</option>
                        </select>
                    </div>

                    <div id="div_klinik_asal" class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Poli Klinik Asal </label>
                        <select id="klinik_asal" class="select2_single form-control" name="klinik_asal">
                            <option value="">- Poli Pertama -</option>
                            <?php for($i=0,$n=count($polike2);$i<$n;$i++) {?>
                          <option value="<?php echo $polike2[$i]["id_poli"];?>"><?php echo $polike2[$i]["poli_nama"];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_gedung" class="col-md-6 col-sm-6 col-xs-12" style="display:none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Gedung / Ruang Rawat</label>
                        <select name="id_gedung_rawat" id="id_gedung_rawat" class="form-control">
                          <option value="" >[ Pilih Gedung / Ruang Rawat ]</option>
                            <?php for($i=0,$n=count($dataGedungRawat);$i<$n;$i++) {?>
                          <option value="<?php echo $dataGedungRawat[$i]["gedung_rawat_id"];?>" <?php if($dataGedungRawat[$i]["gedung_rawat_id"]==$_POST["id_gedung_rawat"]) echo "selected"; ?>><?php echo $dataGedungRawat[$i]["gedung_rawat_nama"];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_kelas" class="col-md-6 col-sm-12 col-xs-12" style="display:none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Kelas</label>
                        <select class="form-control" name="id_kelas" id="id_kelas" onKeyDown="return tabOnEnter(this, event);">
                        <option value="--" >[ Pilih Kelas ]</option>
                        <?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
                        <option value="<?php echo $dataKelas[$i]["kelas_id"];?>" <?php if($dataKelas[$i]["kelas_id"]==$_POST["id_kelas"]) echo "selected"; ?>><?php echo $dataKelas[$i]["kelas_nama"];?></option>
                             <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_kamar" class="col-md-6 col-sm-12 col-xs-12" style="display:none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Kamar</label>
                        <?php echo $view->RenderComboBox("id_kamar","id_kamar",$opt_kamar,"inputfield",null); ?>
                    </div>
                    
                    <div id="div_bed" class="col-md-6 col-sm-12 col-xs-12" style="display:none;">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Bed *</label>
                        <?php echo $view->RenderComboBox("id_bed","id_bed",$opt_bed,"inputfield",null,null); ?>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Nama Paket</label>
                        <select id="paket" class="select2_single form-control" name="paket" >
                        <option value="">- Pilih Paket -</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Nama Dokter</label>
                        <select id="dokter" class="select2_single form-control" name="dokter" >
                        <option value="">- Pilih Dokter -</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Sebab Sakit</label>
                        <select id="reg_sebab_sakit" class="select2_single form-control" name="reg_sebab_sakit" >
                       <!--  <option value="">- Pilih Sebab Sakit -</option> -->
                            <?php for($i=0,$n=count($dataSebabSakit);$i<$n;$i++){ ?>
                            <option value="<?php echo $dataSebabSakit[$i]["sebab_sakit_id"];?>">
                                <?php echo $dataSebabSakit[$i]["sebab_sakit_nama"];?> 
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div hidden class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tipe Pelayanan</label>
                        <select class="select2_single form-control" name="layanan" >
                        <option value="">- Pilih Tipe Layanan -</option>
                            <?php 
                            for($i=0,$n=count($dataLayanan);$i<$n;$i++){
                                ?>
                            <option selected value="<?php echo $dataLayanan[$i]["tipe_biaya_id"];?>">
                                <?php echo $dataLayanan[$i]["tipe_biaya_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div id="div_asal" class="col-md-6 col-sm-12 col-xs-12" style="display:none;">
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Instalasi Asal Pasien</label>
                        <select id="asal_pasien" class="select2_single form-control" name="asal_pasien" >
                            <option value="">- Pilih instalasi -</option>
                          <?php for ($i=0; $i < count($dataAsal); $i++) { ?>
                            <option value="<?php echo $dataAsal[$i]['poli_tipe_id'] ?>"><?php echo $dataAsal[$i]['poli_tipe_nama'] ?></option>
                          <?php } ?>
                        </select>
                    </div>
                    
                    <div hidden class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Shift Pelayanan</label>
                        <select id="reg_shift" class="select2_single form-control" name="reg_shift" >
                            <?php 
                            for($i=0,$n=count($dataShift);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataShift[$i]["shift_id"];?>">
                                <?php echo $dataShift[$i]["shift_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Prosedur Masuk</label>
                        <select id="reg_prosedur_masuk" class="select2_single form-control" name="reg_prosedur_masuk" required>
                        <option value="">- Pilih Prosedur Masuk -</option>
                            <?php 
                            for($i=0,$n=count($dataProsedurMasuk);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_id"];?>">
                                <?php echo $dataProsedurMasuk[$i]["prosedur_masuk_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Kunjungan</label>
                        <select id="reg_rujukan_id" class="select2_single form-control" name="reg_rujukan_id" >
                        <option value="">- Pilih Cara Kunjungan -</option>
                            <?php 
                            for($i=0,$n=count($dataCaraKunjungan);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataCaraKunjungan[$i]["rujukan_id"];?>">
                                <?php echo $dataCaraKunjungan[$i]["rujukan_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div>

                    <div id="rujukan_det" class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Detail Kunjungan</label>
                        <select id="reg_rujukan_det" class="select2_single form-control" name="reg_rujukan_det" >
                            <option value="">- Pilih Detail Kunjungan -</option>
                        </select>
                    </div>

                     <!-- == BEGIN data bpjs== -->
                <div class="clearfix"><br></div>
                <div id="bpjs" <?php if(empty($row["cust_usr_no_jaminan"]) || $row["cust_usr_no_jaminan"] == "" ){  echo 'style="display:none;"'; }?> >
                    <hr>
                <div class="col-sm-6"><strong>BPJS - Create SEP</strong></div>
                <div class="x_panel">
                  <div class="x_content">
                    <div class="x_title">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                          <div class="col-md-8 col-sm-8 col-xs-8">
                          <label class="control-label" style="text-align:left;">No NIK / ASKES / BPJS</label>
                            <input id="cust_usr_no_jaminan" value="<?php echo $row["cust_usr_no_jaminan"]; ?>" type="text" name="cust_usr_no_jaminan" class="form-control">
                          </div>
                          <div class="col-md-4 col-sm-4 col-xs-4">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                            <button type="button" class="btn btn-default" href="#" style="font-size:14px" onclick="cek_kepesertaan($('#cust_usr_no_jaminan').val());"> <span  class="fa fa-search"> Cek Kepesertaan</span> </button>
                          </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Jenis Layanan BPJS</label>
                        <select id="reg_jenis_layanan" class="select2_single form-control" name="reg_jenis_layanan" >
                        <option value="rj">Rawat Jalan</option>
                        <option value="ri">Rawat Inap</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Jenis Peserta BPJS</label>
                        <input type="text" name="cust_usr_jkn_asal" id="cust_usr_jkn_asal" class="form-control">
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Hak Kelas Inap</label>
                        <input type="text" name="hak_kelas_inap" id="hak_kelas_inap" class="form-control">
                    </div>
                    
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">No Rujukan</label>
                        <input type="text" name="reg_no_rujukan" id="reg_no_rujukan" class="form-control">
                    </div>
                    
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Tanggal Rujukan</label>
                        <input type="text" name="reg_tgl_rujukan" id="reg_tgl_rujukan" class="form-control" data-inputmask="'mask': '99-99-9999'" value="<?php echo format_date($_POST["reg_tgl_rujukan"]);?>">
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Kode Asal Rujukan</label>
                        <input type="text" name="reg_ppk_rujukan" id="reg_ppk_rujukan" class="form-control">
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Asal Rujukan</label>
                        <input type="text" name="reg_dokter_sender" id="reg_dokter_sender" class="form-control">
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Catatan BPJS</label>
                        <textarea id="catatan_bpjs" name="catatan_bpjs" id="catatan_bpjs" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">No SEP</label>
                        <input type="text" name="reg_no_sep" id="reg_no_sep" class="form-control">
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">&nbsp;</label>
                        <button type="button" class="btn btn-default" href="#" style="font-size:14px" onclick="create_sep();"> <span  class="fa fa-search"> Create SEP</span> </button>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;"></label>
                        
                    </div>
                    
                  </div>
                </div>
                </div>
                <!-- == END data bpjs== -->
                   
                    
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <br />
                        <table width="100%">
                          <tr>
                            <td><input type="checkbox" name="cetak_tracer" value="n"> Cetak tracer</td>
                            <td><input type="checkbox" name="cetak_reg" value="n"> Cetak registrasi</td>
                            <td><input type="checkbox" name="cetak_ringkasan" value="n"
                            <?php //if ($_GET['status_pasien'] == 'B') echo "checked"; ?>> Cetak ringkasan &nbsp;</td>
                          </tr>
                           <tr>
                            <td><input type="checkbox" name="cetak_barcode_k" value="n"> Cetak barcode kecil</td>
                            <td><input type="checkbox" name="cetak_barcode_b" value="n" 
                            <?php //if ($_GET['status_pasien'] == 'B') echo "checked"; ?>> Cetak barcode besar</td>
                            <td><input type="checkbox" name="cetak_sep" value="yes"> Cetak SEP</td>
                          </tr>
                        </table>
                        <!--input type="checkbox" name="" value="n" 
                            <?php //if ($_GET['status_pasien'] == 'B') echo "checked"; ?>> Cetak kartu<br-->
                    </div>
                    
                  </div>
                </div>
                
                    
               <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
                    <?php echo $tombolback ?>
                    <input type="hidden" name="btnUpdate" value="update"> <!-- value btn, krn js validity -->
                    <button id="btnUpdate" type="submit" value="Update" class="btn col-md-5 btn-success">Simpan</button>
                </div>      
              </div>
              <!-- END KOLOM 2 DATA PASIEN -->
            </div>
            <!-- END ROW INPUT DATA 1 -->

            <!-- BEGIN CAM -->
                <div id="camera">
                    <span class="camTop"></span>
                  
                  <div id="screen"></div>
                    <div id="buttons" style="margin-top:90px;">
                        <div class="buttonPane">
                            <a id="shootButton" href="" class="blueButton">Shoot!</a>
                        </div>
                        <div class="buttonPane" style="display:none;">
                            <a id="cancelButton" href="" class="blueButton">Cancel</a> <a id="uploadButton" href="" class="greenButton">Upload!</a>
                        </div>
                    </div>
                        <span class="settings"></span>
                </div>      
            <!-- END CAM -->
            <?php 
            if($jkn_last){
                if ($last_jkn_hari <= 7 && $_GET['status_pasien'] == 'L' ){
                    echo "<script>alert('Pasien pulang rawat inap kurang dari 7 hari')</script>";
                    //echo "asasa".$jkn_last['reg_cara_keluar_inap'];
                };
                if ($jkn_last['reg_cara_keluar_inap'] == '2'){
                    echo "<script>alert('Pasien pulang rawat inap pulang paksa')</script>";
                }
            } 
            ?>
            
          </div>
        </div>
        <!-- /page content -->
        </form>
        <!-- footer content -->
        <?php require_once($LAY."footer.php"); ?>
      </div>
    </div>
<!-- validator -->
<script src="<?php echo $ROOT; ?>assets/vendors/validator/validator.js"></script>
<?php if ( $pas_irj['jkn_irj'] > 0   ){  echo "<script>alert('Pasien masih di poli ".$pas_irj['string_agg'].". Minta petugas terkait menyelesaikan pelayanan.'); </script>"; } 
    elseif ( $pas_irna['jkn_irna'] > 0  ){  echo "<script>alert('Pasien rawat inap silahkan registrasi via rujukan di rawat inap');</script>"; } 
    ?>
<script>
    var validator = new FormValidator({"events" : ['blur', 'input', 'change']}, document.forms[0]);
</script>
<?php require_once($LAY."js.php"); ?>
  </body>
</html>
