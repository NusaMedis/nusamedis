<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "login.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();
$tglSekarang = date("Y-m-d");

//Tanggal Kemarin
$tglKemarin = date('Y-m-d', strtotime("-1 day", strtotime(date("Y-m-d"))));

$auth = new CAuth();
$userId = $auth->GetUserId();

if (isset($_GET['reg_id'])) {
  $sql = "select a.reg_diagnosa_igd,
    a.id_dep,a.reg_id,a.reg_tingkat_kegawatan,a.reg_status_kondisi_deskripsi,a.reg_tanggal,a.reg_status_kondisi,a.id_pembayaran,a.reg_status, a.reg_when_update,
    a.id_cust_usr,a.reg_rujukan_id,a.id_poli,a.id_dokter,a.reg_jenis_pasien,
    b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_id,b.cust_usr_nama,b.cust_usr_alamat,
    c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id, h.rawat_id,
    h.rawat_anamnesa,h.rawat_keluhan,h.rawat_catatan,h.rawat_pemeriksaan_fisik,h.rawat_diagnosa_utama,h.rawat_ket, i.perusahaan_nama, a.id_jenis_kb, c.form_asmed,j.pembayaran_flag, a.id_dokter_2, cust_usr_alergi
    from
    klinik.klinik_registrasi a left join 
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
    left join global.global_auth_poli c on a.id_poli = c.poli_id 
    left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
    left join global.global_shift e on e.shift_id = a.reg_shift
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_auth_user g on g.usr_id = a.id_dokter
    left join klinik.klinik_perawatan h on h.id_reg = a.reg_id
    left join global.global_perusahaan i on i.perusahaan_id = a.id_perusahaan
    left join klinik.klinik_pembayaran j on j.pembayaran_id = a.id_pembayaran

    where reg_id = '$_GET[reg_id]'";
  $sql .= " order by a.reg_when_update asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);

  $sql1 = "select id_cust_usr from klinik.klinik_registrasi where reg_id ='$_GET[reg_id]'";
  $rs1 = $dtaccess->Execute($sql1);
  $dataTable1 = $dtaccess->Fetch($rs1);

  $sql = "select * from klinik.klinik_perawatan where id_reg = " . QuoteValue(DPE_CHAR, $_GET["reg_id"]);
  $dataRawat = $dtaccess->Fetch($sql);

  if($dataRawat["waktu_simpan"] != null && $dataRawat["waktu_mulai_asmed"] == null){
    $noww = date('Y-m-d H:i:s');
    $sql = "UPDATE klinik.klinik_perawatan set waktu_mulai_asmed = '$noww' where rawat_id = ". QuoteValue(DPE_CHAR, $dataRawat["rawat_id"]);
    $s = $dtaccess->Execute($sql);
  }

  $sql = "select anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat["rawat_id"]) . " and id_anamnesa = 'TIPE LAYANAN'";
  $dataLayanan = $dtaccess->Fetch($sql);

  $waktu_tglSekarang = $dataTable[0]['reg_when_update'];
  //PEMERIKSAAN YANG LALU 
  $sql = "select a.reg_id,b.rawat_id from klinik.klinik_registrasi a left join klinik.klinik_perawatan b on b.id_reg = a.reg_id where a.id_cust_usr = " . QuoteValue(DPE_CHAR, $dataTable1["id_cust_usr"]) . " and a.reg_tipe_rawat = 'J' and a.id_poli <> '33' and a.id_poli <> '20' and reg_when_update <> '$waktu_tglSekarang' order by reg_tanggal desc limit 1 ";

  $dtRegTerakhir = $dtaccess->Fetch($sql);


  $sql = "select anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_rawat = " . QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]) . " and id_anamnesa = 'TIPE LAYANAN'";
  $dataLayananTerakhir = $dtaccess->Fetch($sql);
  //echo $sql;
  // $sql = "select item_nama,aturan_pakai_nama,aturan_minum_nama,petunjuk_nama, terapi_jumlah_item,jam_aturan_pakai_nama
  //            from klinik.klinik_perawatan_terapi a 
  //            WHERE id_rawat = " . QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]);
  // $dtTerapi = $dtaccess->FetchAll($sql);

  // ambil history obat dari klinik_history_terapi
  $sql = "select * from klinik.klinik_history_terapi  
             WHERE  nama_item not like '%Racikan%' and id_reg = " . QuoteValue(DPE_CHAR, $dtRegTerakhir["reg_id"]);
            
  $dtTerapi = $dtaccess->FetchAll($sql);

  //echo $sql;
  //HPHT TERAKHIR
  $sql_hpht = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = " . QuoteValue(DPE_CHAR, $dtRegTerakhir['rawat_id']) . " AND id_anamnesa = '5d2c31995c9ef8fbad77eaee2abc6ecd' AND id_anamnesa_detail = '50e70246c321cc4b0309bb9f5203fd42' and anamnesa_isi_detail_nilai not like '%Hari%' and anamnesa_isi_detail_nilai not like '% - %'";
  $dataHpht = $dtaccess->Fetch($sql_hpht);

  $sql_hphts = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat['rawat_id']) . " AND id_anamnesa = '5d2c31995c9ef8fbad77eaee2abc6ecd' AND id_anamnesa_detail = '50e70246c321cc4b0309bb9f5203fd42' and anamnesa_isi_detail_nilai not like '%Hari%' and anamnesa_isi_detail_nilai not like '% - %'";
  $dataHphtSekarang = $dtaccess->Fetch($sql_hphts);

  $sql_G = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $dataRawat['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '8c58848aae6b61fc5c7f70e4659ebbe5'";
  $dataG = $dtaccess->Fetch($sql_G);

  $sql_P = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $dataRawat['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '3275c22d4d0c8e008b5deb78d68ba116'";
  $dataP = $dtaccess->Fetch($sql_P);

  $sql_A = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $dataRawat['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '86b6648a00e26029a0949b88a6bebf2d'";
  $dataA = $dtaccess->Fetch($sql_A);

  if ($dataA['anamnesa_isi_detail_nilai'] == '') $dataA['anamnesa_isi_detail_nilai'] = '0';
  if ($dataP['anamnesa_isi_detail_nilai'] == '') $dataP['anamnesa_isi_detail_nilai'] = '0';
  if ($dataG['anamnesa_isi_detail_nilai'] == '') $dataG['anamnesa_isi_detail_nilai'] = '0';

  //if ($dataHPHT['anamnesa_isi_detail_nilai'] != ''){
  $HPHT = $dataHpht['anamnesa_isi_detail_nilai'];
  // }else{
  //   $HPHT = $dataHphtSekarang['anamnesa_isi_detail_nilai'];
  // }
  //echo $HPHT;

  if ($dataHpht['anamnesa_isi_detail_nilai'] <> null) {
    $HPL = date('Y-m-d', strtotime('+279 days', strtotime(date_db($dataHpht['anamnesa_isi_detail_nilai']))));
  } elseif ($dataHphtSekarang['anamnesa_isi_detail_nilai'] <> null) {
    $HPL = date('Y-m-d', strtotime('+279 days', strtotime(date_db($dataHphtSekarang['anamnesa_isi_detail_nilai']))));
  } else {
    $HPL = '';
  }

  $sql = "select item_nama,aturan_pakai_nama,aturan_minum_nama,petunjuk_nama, terapi_jumlah_item,jam_aturan_pakai_nama
             from klinik.klinik_perawatan_terapi a 
             WHERE id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat["rawat_id"]);
  $dtTerapiSekarang = $dtaccess->FetchAll($sql);
  
  $sql = "select rawat_tanggal,rawat_pemeriksaan_fisik,rawat_diagnosa_utama,rawat_anamnesa,rawat_ket,rawat_obgyn, rawat_anak from klinik.klinik_perawatan where rawat_id = " . QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]);
  //echo $sql;
  $dataPerawatanTerakhir = $dtaccess->Fetch($sql);

  $sql = "SELECT b.diagnosa_nomor, b.diagnosa_nama, b.diagnosa_short_desc from klinik.klinik_perawatan_diagnosa a 
  left join klinik.klinik_diagnosa b on a.id_diagnosa = b.diagnosa_id
  where a.id_rawat = ". QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]);
  $diagnose_lalu = $dtaccess->FetchAll($sql);

  $sql = "SELECT b.diagnosa_nomor, b.diagnosa_short_desc from klinik.klinik_perawatan_diagnosa a 
  left join klinik.klinik_diagnosa b on a.id_diagnosa = b.diagnosa_id
  where a.id_rawat = ". QuoteValue(DPE_CHAR, $dataRawat["rawat_id"]);
  $diagnose_skr = $dtaccess->FetchAll($sql);

  $sql = "SELECT rawat_terapi from klinik.klinik_perawatan where rawat_id = ". QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]);
  $TerapiAreaTerakhir = $dtaccess->Fetch($sql);

  $sql = "SELECT anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_anamnesa = 'F15' and id_rawat = ". QuoteValue(DPE_CHAR, $dataRawat["rawat_id"]);
  $diagKhusus = $dtaccess->Fetch($sql);

  if(count($diagnose_skr) > 0){
    for($i = 0; $i < count($diagnose_skr); $i++){
    $str[] = implode("-", $diagnose_skr[$i]);
  }
  $diag_skr = implode(" \n", $str);
  }

  if(count($diagnose_lalu) > 0){
    for($i = 0; $i < count($diagnose_lalu); $i++){
    $strd[] = implode("-", $diagnose_lalu[$i]);
  }
  $diag_lalu = implode(" \n", $strd);
  }

  if($TerapiAreaTerakhir['rawat_terapi']){

      $terapiArTerakhir = explode("+", $TerapiAreaTerakhir['rawat_terapi']);
      $terapiAreaTerakhir = implode(" \n", $terapiArTerakhir);

  }

  if($dataTable[0]['rawat_terapi']){

      $terapiArea = explode("+", $dataTable[0]['rawat_terapi']);

  }

  

  $sql = "SELECT b.procedure_nomor, b.procedure_nama, b.procedure_short_desc from klinik.klinik_perawatan_procedure a 
  left join klinik.klinik_procedure b on a.id_procedure = b.procedure_id
  where a.id_rawat = ". QuoteValue(DPE_CHAR, $dtRegTerakhir["rawat_id"]);
  $procedure_lalu = $dtaccess->FetchAll($sql);

  for($i = 0; $i < count($procedure_lalu); $i++){
    //$strp[] = implode("-", $procedure_lalu[$i]);
  }
  //$proc_lalu = implode(" ; ", $strp);

  if($dataTable[0]['form_asmed'] == 'obgyn'){
  $dataRawatObgyn = unserialize($dataPerawatanTerakhir['rawat_obgyn']);
  $GS0 = $dataRawatObgyn['gs0'];
  $GS1 = $dataRawatObgyn['gs1'];
  $GS2 = $dataRawatObgyn['gs2'];
  $FetalPool = $dataRawatObgyn['fetal_pool'];
  $Fetus = $dataRawatObgyn['fetus'];
  $CRL = $dataRawatObgyn['crl'];
  $DJJ = $dataRawatObgyn['djj'];
  $UsiaKehamilanMinggu = $dataRawatObgyn['usia_kehamilan_minggu'];
  $UsiaKehamilanHari = $dataRawatObgyn['usia_kehamilan_hari'];
  $JaninTunggal = $dataRawatObgyn['janin_tunggal'];
  $JaninKembar = $dataRawatObgyn['janin_kembar'];
  $JaninHidup = $dataRawatObgyn['janin_hidup'];
  $JaninIufd = $dataRawatObgyn['janin_iufd'];
  $LetakJaninKepala = $dataRawatObgyn['letak_janin_kepala'];
  $LetakJaninSungsang = $dataRawatObgyn['letak_janin_sungsang'];
  $LetakJaninMelintang = $dataRawatObgyn['letak_janin_melintang'];
  $LetakJaninOblique = $dataRawatObgyn['letak_janin_oblique'];
  $BPD = $dataRawatObgyn['bpd'];
  $FL = $dataRawatObgyn['fl'];
  $AC = $dataRawatObgyn['ac'];
  $EFW = $dataRawatObgyn['efw'];
  $UsiaKehamilanMinggu1 = $dataRawatObgyn['usia_kehamilan_minggu1'];
  $UsiaKehamilanHari1 = $dataRawatObgyn['usia_kehamilan_hari1'];

  $Fudus = $dataRawatObgyn['insersi_fudus'];
  $Corpus = $dataRawatObgyn['insersi_corpus'];
  $SBR = $dataRawatObgyn['insersi_sbr'];
  $ANT = $dataRawatObgyn['insersi_anterior'];
  $PST = $dataRawatObgyn['insersi_posterior'];

  $AFI = $dataRawatObgyn['afi'];
  $LainLain = $dataRawatObgyn['lain-lain'];
  $HPLTP = $dataRawatObgyn['hpltp'];
  $KetubanCukup = $dataRawatObgyn['ketuban_cukup'];
  $KetubanKurang = $dataRawatObgyn['ketuban_kurang'];
  $KetubanBanyak = $dataRawatObgyn['ketuban_banyak'];
  $USGGinek = $dataRawatObgyn['usg_ginekologi'];
  $USGGinek = $dataRawatObgyn['usg_ginekologi'];
  $Grade = $dataRawatObgyn['grade'];
  $g_analisa_lalu = $dataRawatObgyn['g_analisa'];
  $p_analisa_lalu = $dataRawatObgyn['p_analisa'];
  $a_analisa_lalu = $dataRawatObgyn['a_analisa'];
  $ket_diagnosa_satu_lalu = $dataRawatObgyn['ket_diagnosa_satu'];
  $ket_diagnosa_dua_lalu = $dataRawatObgyn['ket_diagnosa_dua'];
  $ket_diagnosa_tiga_lalu = $dataRawatObgyn['ket_diagnosa_tiga'];
  $ket_diagnosa_lima_lalu = $dataRawatObgyn['ket_diagnosa_lima'];
  $pemeriksaan_penunjang_lalu = $dataRawatObgyn['pemeriksaan_penunjang'];
  //if ($dataRawatObgyn['ket_diagnosa_empat'] != '') {
    $ket_diagnosa_empat_lalu = $diag_lalu;

  // } else {
  //   $ket_diagnosa_empat_lalu = $dataRawatObgyn['analisa_diagnosaa'];
  // }
  //USG SKRG
  $dataRawatObgynSekarang = unserialize($dataRawat['rawat_obgyn']);
  
  $UsiaKehamilanMingguSkg = $dataRawatObgynSekarang['usia_kehamilan_minggu'];
  $UsiaKehamilanHariSkg = $dataRawatObgynSekarang['usia_kehamilan_hari'];

  $UsiaKehamilanMinggu1Skg = $dataRawatObgynSekarang['usia_kehamilan_minggu1'];
  $UsiaKehamilanHari1Skg = $dataRawatObgynSekarang['usia_kehamilan_hari1'];

  if ($dataRawatObgynSekarang['usia_kehamilan_minggu'] != '' || $dataRawatObgynSekarang['usia_kehamilan_hari'] != '' || $dataRawatObgynSekarang['usia_kehamilan_minggu1'] != '' || $dataRawatObgynSekarang['usia_kehamilan_hari1'] != '') {
    if ($dataRawatObgynSekarang['usia_kehamilan_minggu'] != '') $ket_diagnosa_satu = $dataRawatObgynSekarang['usia_kehamilan_minggu'].' Mgg ';
    if ($dataRawatObgynSekarang['usia_kehamilan_hari'] != '') $ket_diagnosa_satu .= $dataRawatObgynSekarang['usia_kehamilan_hari'].' Hari';
    if ($dataRawatObgynSekarang['usia_kehamilan_minggu1'] != '') $ket_diagnosa_satu = $dataRawatObgynSekarang['usia_kehamilan_minggu1'].' Mgg ';
    if ($dataRawatObgynSekarang['usia_kehamilan_hari1'] != '') $ket_diagnosa_satu .= $dataRawatObgynSekarang['usia_kehamilan_hari1'].' Hari';
  }
// echo $dataRawatObgynSekarang['usia_kehamilan_hari1'];
  // echo 'adsadasda'.$dataRawatObgynSekarang['usia_kehamilan_minggu'].'x'.$dataRawatObgynSekarang['usia_kehamilan_hari'].'x'.$dataRawatObgynSekarang['usia_kehamilan_minggu1'].'x'.$dataRawatObgynSekarang['usia_kehamilan_hari1'];
  $GS0Skg = $dataRawatObgynSekarang['gs0'];
  $GS1Skg = $dataRawatObgynSekarang['gs1'];
  $GS2Skg = $dataRawatObgynSekarang['gs2'];
  $FetalPoolSkg = $dataRawatObgynSekarang['fetal_pool'];
  $FetusSkg = $dataRawatObgynSekarang['fetus'];
  $CRLSkg = $dataRawatObgynSekarang['crl'];
  $DJJSkg = $dataRawatObgynSekarang['djj'];
  $UsiaKehamilanMingguSkg = $dataRawatObgynSekarang['usia_kehamilan_minggu'];
  $UsiaKehamilanHariSkg = $dataRawatObgynSekarang['usia_kehamilan_hari'];
  $JaninTunggalSkg = $dataRawatObgynSekarang['janin_tunggal'];
  $JaninKembarSkg = $dataRawatObgynSekarang['janin_kembar'];
  $JaninHidupSkg = $dataRawatObgynSekarang['janin_hidup'];
  $JaninIufdSkg = $dataRawatObgynSekarang['janin_iufd'];
  $LetakJaninKepalaSkg = $dataRawatObgynSekarang['letak_janin_kepala'];
  $LetakJaninSungsangSkg = $dataRawatObgynSekarang['letak_janin_sungsang'];
  $LetakJaninMelintangSkg = $dataRawatObgynSekarang['letak_janin_melintang'];
  $LetakJaninObliqueSkg = $dataRawatObgynSekarang['letak_janin_oblique'];
  $BPDSkg = $dataRawatObgynSekarang['bpd'];
  $FLSkg = $dataRawatObgynSekarang['fl'];
  $ACSkg = $dataRawatObgynSekarang['ac'];
  $EFWSkg = $dataRawatObgynSekarang['efw'];
  $UsiaKehamilanMinggu1Skg = $dataRawatObgynSekarang['usia_kehamilan_minggu1'];
  $UsiaKehamilanHari1Skg = $dataRawatObgynSekarang['usia_kehamilan_hari1'];
  $FudusSkg = $dataRawatObgynSekarang['insersi_fudus'];
  $CorpusSkg = $dataRawatObgynSekarang['insersi_corpus'];
  $SBRSkg = $dataRawatObgynSekarang['insersi_sbr'];
  $ANTSkg = $dataRawatObgynSekarang['insersi_anterior'];
  $PSTSkg = $dataRawatObgynSekarang['insersi_posterior'];
  $AFISkg = $dataRawatObgynSekarang['afi'];
  $LainLainSkg = $dataRawatObgynSekarang['lain-lain'];
  $JenisKelaminSkg = $dataRawatObgynSekarang['jenis_kelamin'];
  $HPLTPSkg = $dataRawatObgynSekarang['hpltp'];
  $HPLMdSkg = $dataRawatObgynSekarang['hpl_muda'];
  $KetubanCukupSkg = $dataRawatObgynSekarang['ketubah_cukup'];
  $KetubanKurangSkg = $dataRawatObgynSekarang['ketuban_kurang'];
  $KetubanBanyakSkg = $dataRawatObgynSekarang['ketuban_banyak'];
  $USGGinekSkg = $dataRawatObgynSekarang['usg_ginekologi'];
  $GradeSkg = $dataRawatObgynSekarang['grade'];
  $status_lokalis = $dataRawatObgynSekarang['status_lokalis'];
  $g_analisa = $dataG['anamnesa_isi_detail_nilai'];
  $p_analisa = $dataP['anamnesa_isi_detail_nilai'];
  $a_analisa = $dataA['anamnesa_isi_detail_nilai'];

  $pemeriksaanPenunjang = ($dataRawatObgynSekarang['pemeriksaanPenunjang'] != '') ? $dataRawatObgynSekarang['pemeriksaanPenunjang'] : $dataRawatObgynSekarang['pemeriksaanPenunjang_g'];


  $memahamiMateri = $dataRawatObgynSekarang['memahamiMateri'];
  $butuhLeaflet = $dataRawatObgynSekarang['butuhLeaflet'];
  $membatasiMateri = $dataRawatObgynSekarang['membatasiMateri'];
  $pengulanganMateri= $dataRawatObgynSekarang['pengulanganMateri'];
  $bisaMengulang = $dataRawatObgynSekarang['bisaMengulang'];
  $lain_lainEdukasi = $dataRawatObgynSekarang['lain_lainEdukasi'];
  $lainEd_det = $dataRawatObgynSekarang['lainEd_det'];

 //Materi Edukasi 
 
  $diagnosa = $dataRawatObgynSekarang['diagnosa'];
  $penjelasan_penyakit = $dataRawatObgynSekarang['penjelasan_penyakit'];
  $pemeriksaan_penunjang = $dataRawatObgynSekarang['pemeriksaan_penunjang'];
  $terapi_edukasi = $dataRawatObgynSekarang['terapi_edukasi'];
  $terapi_alter = $dataRawatObgynSekarang['terapi_alter'];
  $tindakan_medis = $dataRawatObgynSekarang['tindakan_medis'];
  $perkiraan_hari_rawat = $dataRawatObgynSekarang['perkiraan_hari_rawat'];
  $penjelasan_komplikasi = $dataRawatObgynSekarang['penjelasan_komplikasi'];
  $informed_concent = $dataRawatObgynSekarang['informed_concent'];
  $kondisi = $dataRawatObgynSekarang['kondisi'];
  $konsul = $dataRawatObgynSekarang['konsul'];
  $konsul_det = $dataRawatObgynSekarang['konsul_det'];
  $edukasi_pulang = $dataRawatObgynSekarang['edukasi_pulang'];
  $edukasi_lain = $dataRawatObgynSekarang['edukasi_lain'];
  $lain_det = $dataRawatObgynSekarang['lain_det'];

  // $ket_diagnosa_satu = $dataRawatObgynSekarang['ket_diagnosa_satu'];
if ($JaninTunggalSkg == 'true') {
  $ket_diagnosa_dua = 'T';
}
if ($JaninKembarSkg == 'true') {
  $ket_diagnosa_dua = 'G';
}
if ($JaninHidupSkg == 'true') {
  $ket_diagnosa_tiga = 'Hidup'; 
}
if ($JaninIufdSkg == 'true') {
  $ket_diagnosa_tiga = 'IUFD';
}
if ($LetakJaninKepalaSkg == 'true') {
  $ket_diagnosa_lima = 'Kepala';
}
if ($LetakJaninSungsangSkg) {
  $ket_diagnosa_lima = 'Sungsang';
}
if ($LetakJaninObliqueSkg) {
  $ket_diagnosa_lima = 'Oblique';
}
if ($LetakJaninMelintangSkg) {
  $ket_diagnosa_lima = 'Melintang';
}
  $ket_diagnosa_empat = $dataRawatObgynSekarang['ket_diagnosa_empat'];
  $pemeriksaan_penunjang = $dataRawatObgynSekarang['pemeriksaan_penunjang'];
  $lap_tindakan = $dataRawatObgynSekarang['lap_tindakan'];
  // echo $dataRawatObgyn;
  //echo $sql;

  
  // echo $sql;

  //format baru usg terakhir
  if ($JaninTunggal == true) {
    $tunggal = 'Tunggal,';
  } else {
    $tunggal = '';
  }
  if ($JaninKembar == true) {
    $kembar = 'Kembar,';
  } else {
    $kembar = '';
  }
  if ($JaninHidup == true) {
    $hidup = 'Hidup,';
  } else {
    $hidup = '';
  }
  if ($JaninIufd == true) {
    $iufd = 'Iufd,';
  } else {
    $iufd = '';
  }
  if ($LetakJaninKepala == true) {
    $LetakKepala = 'Kepala,';
  } else {
    $LetakKepala = '';
  }
  if ($LetakJaninMelintang == true) {
    $LetakMelintang = 'Melintang,';
  } else {
    $LetakMelintang = '';
  }
  if ($LetakJaninOblique == true) {
    $LetakOblique = 'Oblique,';
  } else {
    $LetakOblique = '';
  }
  if ($LetakJaninSungsang == true) {
    $LetakSungsang = 'Sungsang,';
  } else {
    $LetakSungsang = '';
  }
  if ($UsiaKehamilanHari != '') {
    $GAHari = $UsiaKehamilanHari;
  } elseif ($UsiaKehamilanHari1 != '') {
    $GAHari = $UsiaKehamilanHari1;
  } else {
    $GAHari = '-';
  }
  if ($UsiaKehamilanMinggu != '') {
    $GAMinggu = $UsiaKehamilanMinggu;
  } elseif ($UsiaKehamilanMinggu1 != '') {
    $GAMinggu = $UsiaKehamilanMinggu1;
  } else {
    $GAMinggu = '-';
  }
  if ($dataLayananTerakhir['anamnesa_isi_nilai'] == '0bstetri') {

     if ($dataRawatObgyn['insersi_fudus']) { 
        $plasentall[] =  ($dataRawatObgyn['insersi_fudus'] == 'true') ? 'Fundus;' : '' ;
       } 
       if ($dataRawatObgyn['insersi_corpus']) { 
        $plasentall[] =  ($dataRawatObgyn['insersi_corpus'] == 'true') ? 'Corpus;' : '';
       } 
       if ($dataRawatObgyn['insersi_sbr']) { 
        $plasentall[] =  ($dataRawatObgyn['insersi_sbr'] == 'true') ? 'SBR;' : '' ;
       } 
       if ($dataRawatObgyn['insersi_anterior']) { 
        $plasentall[] =  ($dataRawatObgyn['insersi_anterior'] == 'true') ? 'Ant;' : '';
       } 
      if ($dataRawatObgyn['insersi_posterior']) { 
       $plasentall[] =  ($dataRawatObgyn['insersi_posterior'] == 'true') ? 'Post;' : '' ;
       } 

       if(count($plasentall) > 0){
        $plasental = implode(" ", $plasentall);
       }
       else{
        $plasental = '';
       }

       

    if($GS0 || $GS1 || $GS2 || $FetalPool || $Fetus || $CRL || $DJJ){
      $janin = ($GS1 == '1') ? 'Tunggal' : 'Kembar';
     $UsgTerakhir = 'GS : ' . $GS0 . ' - ' . $janin . ' - ' . $GS2 . ' ; Fetal Pool : ' . $FetalPool . ';' . ' Fetus : ' . $Fetus . ' ; CRL : ' . $CRL . " mm " . ' ; BPD : ' . $BPD . ' cm ' . ' ; DJJ : ' . $DJJ . ' GA : ' . $GAMinggu . ' Minggu' . ' ' . $GAHari . ' Hari' . '; HPL/TP : ' . $HPLTP;
    }
    else{
       $UsgTerakhir = 'Janin : ' . $tunggal . ' ' . $kembar . ' ' . $hidup . ' ' . $iufd . ';' . ' Letak Janin : ' . $LetakKepala . ' ' . $LetakSungsang . ' ' . $LetakMelintang . ' ' . $LetakOblique . ';' . ' ; BPD : ' . $BPD . ' cm ' . "; Plasenta : " . $plasental . " Grade : " . $Grade . '; GA : ' . $GAMinggu . ' Minggu' . ' ' . $GAHari . ' Hari' . '; HPL/TP : ' . $HPLTP;
    }
    
  } else {
    $UsgTerakhir = '';
  }

}
else if($dataTable[0]['form_asmed'] == 'anak'){
  $dataRawatLalu = unserialize($dataPerawatanTerakhir['rawat_anak']);
  $dataRawatSkr = unserialize($dataRawat['rawat_anak']);
}

$Obat = '';
  for ($i = 0; $i < count($dtTerapi); $i++) {
    $Obat .= $dtTerapi[$i]['nama_item'] . ", ";
    if ($dtTerapi[$i]['jumlah_item'] != '') {
      $Obat .= $dtTerapi[$i]['jumlah_item'] . ", ";
    }
    // $Obat .= $dtTerapi[$i]['item_nama'] . ", ";
    // if ($dtTerapi[$i]['terapi_jumlah_item'] != '') {
    //   $Obat .= $dtTerapi[$i]['terapi_jumlah_item'] . ", ";
    // }
    // if ($dtTerapi[$i]['petunjuk_nama'] != '') {
    //   $Obat .= $dtTerapi[$i]['petunjuk_nama'] . ", ";
    // }
    // if ($dtTerapi[$i]['aturan_minum_nama'] != '') {
    //   $Obat .= $dtTerapi[$i]['aturan_minum_nama'] . ", ";
    // }
    // if ($dtTerapi[$i]['aturan_minum_nama'] != '') {
    //   $Obat .= $dtTerapi[$i]['aturan_minum_nama'] . ", ";
    // }
    // if ($dtTerapi[$i]['aturan_pakai_nama'] != '') {
    //   $Obat .= $dtTerapi[$i]['aturan_pakai_nama'] . ", ";
    // }
    // if ($dtTerapi[$i]['jam_aturan_pakai_nama'] != '') {
    //   $Obat .= $dtTerapi[$i]['jam_aturan_pakai_nama'] . " ";
    // }
    $Obat .= "; ";
  }

  $ObatSekarang = '';
  for ($j = 0; $j < count($dtTerapiSekarang); $j++) {
    $ObatSekarang .= $dtTerapiSekarang[$j]['item_nama'] . ", ";
    if ($dtTerapiSekarang[$j]['terapi_jumlah_item'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['terapi_jumlah_item'] . ", ";
    }
    if ($dtTerapiSekarang[$j]['petunjuk_nama'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['petunjuk_nama'] . ", ";
    }
    if ($dtTerapiSekarang[$j]['aturan_minum_nama'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['aturan_minum_nama'] . ", ";
    }
    if ($dtTerapiSekarang[$j]['aturan_minum_nama'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['aturan_minum_nama'] . ", ";
    }
    if ($dtTerapiSekarang[$j]['aturan_pakai_nama'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['aturan_pakai_nama'] . ", ";
    }
    if ($dtTerapiSekarang[$j]['jam_aturan_pakai_nama'] != '') {
      $ObatSekarang .= $dtTerapiSekarang[$j]['jam_aturan_pakai_nama'] . " ";
    }
    $ObatSekarang .= "; ";
  }


  $data = [];

  for ($i = 0; $i < count($dataTable); $i++) {

    if($dataTable[0]['form_asmed'] == 'obgyn'){
    array_push($data, [
      'id_dep'   => $dataTable[$i]['id_dep'],
      'reg_id'   => $dataTable[$i]['reg_id'],
      'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
      'reg_status_kondisi'   => $dataTable[$i]['reg_status_kondisi'],
      'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
      'id_cust_usr'   => $dataTable[$i]['id_cust_usr'],
      'reg_rujukan_id'   => $dataTable[$i]['reg_rujukan_id'],
      'reg_tingkat_kegawatan'   => $dataTable[$i]['reg_tingkat_kegawatan'],
      'pembayaran_flag'   => $dataTable[$i]['pembayaran_flag'],
      'id_poli'   => $dataTable[$i]['id_poli'],
      'form_asmed'   => $dataTable[$i]['form_asmed'],
      'id_dokter'   => $dataTable[$i]['id_dokter'],
      'id_dokter2'   => $dataTable[$i]['id_dokter_2'],
      'reg_jenis_pasien'   => $dataTable[$i]['reg_jenis_pasien'],
      'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode_tampilan'],
      'reg_status'   => $dataTable[$i]['reg_status'],
      'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
      'cust_usr_alergi'   => $dataTable[$i]['cust_usr_alergi'],
      // 'cust_usr_nama'   => str_replace("*", "'", $dataTable[$i]['cust_usr_nama']),
      'cust_usr_nama'   => $dataTable[$i]['cust_usr_nama'],
      'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
      'poli_nama'   => $dataTable[$i]['poli_nama'],
      'sebab_sakit_nama'   => $dataTable[$i]['sebab_sakit_nama'],
      'shift_nama'   => $dataTable[$i]['shift_nama'],
      'jenis_nama'   => $dataTable[$i]['jenis_nama'],
      'usr_name'   => $dataTable[$i]['usr_name'],
      'usr_id'   => $dataTable[$i]['usr_id'],
      'rawat_anamnesa'   => $dataTable[$i]['rawat_anamnesa'],
      'rawat_pemeriksaan_fisik'   => $dataTable[$i]['rawat_pemeriksaan_fisik'],
      'rawat_penunjang'   => $dataTable[$i]['rawat_penunjang'],
      'rawat_kasus_keterangan'   => $dataTable[$i]['rawat_kasus_keterangan'],
      'cust_usr_foto'   => $dataTable[$i]['cust_usr_foto'],
      'rawat_ket'   => $dataRawatObgynSekarang['planning_penatalaksanaan'],
      'rawat_terapi'   => $dataTable[$i]['rawat_terapi'],
      'rawat_diagnosa_utama'   => $dataTable[$i]['rawat_diagnosa_utama'],
      'rawat_pemeriksaan_dalam'   => $dataTable[$i]['rawat_pemeriksaan_dalam'],
      'rawat_id'   => $dataTable[$i]['rawat_id'],
      'layanan'   => $dataLayanan['anamnesa_isi_nilai'],
      'rawat_tanggal_terakhir'   => date_db($dataPerawatanTerakhir['rawat_tanggal']),
      'rawat_anamnesa_terakhir'   => $dataPerawatanTerakhir['rawat_anamnesa'],
      'rawat_pemeriksaan_fisik_terakhir'   => $dataPerawatanTerakhir['rawat_pemeriksaan_fisik'],
      'rawat_diagnosa_utama_terakhir'   => $dataPerawatanTerakhir['rawat_diagnosa_utama'],
      'rawat_ket_terakhir'   => $proc_lalu,
      'obat'   => $Obat,
      'obat_sekarang'   => $ObatSekarang,
      'id_rawat_lalu' => $dtRegTerakhir['rawat_id'],
      'diagnosa_lalu' => $diag_lalu,
      'diagnosa_sekarang' => $diag_skr,
      'lap_tindakan' => $lap_tindakan,
      'jenis_kb_id' => $dataTable[$i]['id_jenis_kb'],
      'diagKhusus' => $diagKhusus['anamnesa_isi_nilai'],

      
      'rawat_usg_ginekologi'   => $dataTable[$i]['rawat_usg_ginekologi'],
      'usg_lalu' => $dataPerawatanTerakhir['rawat_obgyn'],
      'hpht_terakhir' => $HPHT,
      'hpl_terakhir' => date_db($HPL),
      'g_analisa_lalu' => $g_analisa_lalu,
      'p_analisa_lalu' => $p_analisa_lalu,
      'a_analisa_lalu' => $a_analisa_lalu,
      'ket_diagnosa_satu_lalu' => $ket_diagnosa_satu_lalu,
      'ket_diagnosa_dua_lalu' => $ket_diagnosa_dua_lalu,
      'ket_diagnosa_tiga_lalu' => $ket_diagnosa_tiga_lalu,
      'ket_diagnosa_empat_lalu' => $ket_diagnosa_empat_lalu,
      'ket_diagnosa_lima_lalu' => $ket_diagnosa_lima_lalu,
      //USG Terakhir
      'gs0' => $GS0,
      'gs1' => $GS1,
      'gs2' => $GS2,
      'fetal_pool' => $FetalPool,
      'fetus' => $Fetus,
      'crl' => $CRL,
      'djj' => $DJJ,
      'ga_hari' => $UsiaKehamilanHari,
      'ga_minggu' => $UsiaKehamilanMinggu,
      'janin_tunggal' => $JaninTunggal,
      'janin_kembar' => $JaninKembar,
      'janin_hidup' => $JaninHidup,
      'janin_iufd' => $JaninIufd,
      'letak_janin_kepala' => $LetakJaninKepala,
      'letak_janin_sungsang' => $LetakJaninSungsang,
      'letak_janin_melintang' => $LetakJaninMelintang,
      'letak_janin_oblique' => $LetakJaninOblique,
      'bpd' => $BPD,
      'fl' => $FL,
      'ac' => $AC,
      'efw' => $EFW,
      'usia_kehamilan_minggu1' => $UsiaKehamilanMinggu1,
      'usia_kehamilan_hari1' => $UsiaKehamilanHari1,
      'fudus' => $Fudus,
      'corpus' => $Corpus,
      'sbr' => $SBR,
      'ant' => $ANT,
      'pst' => $PST,
      'afi' => $AFI,
      'pemeriksaanPenunjang' => $pemeriksaanPenunjang,
      'hpltp' => $HPLTP,
      'ketuban_cukup' => $KetubanCukup,
      'ketuban_banyak' => $KetubanBanyak,
      'ketuban_kurang' => $KetubanKurang,
      'usg_ginekologi_terakhir' => $USGGinek,
      'grade' => $Grade,
      'pemeriksaan_penunjang_lalu' => $pemeriksaan_penunjang_lalu,

      //USG SKRG
      'gs0Skg' => $GS0Skg,
      'gs1Skg' => $GS1Skg,
      'gs2Skg' => $GS2Skg,
      'gs2Skgx' => $GS2Skg,
      'fetal_poolSkg' => $FetalPoolSkg,
      'fetusSkg' => $FetusSkg,
      'crlSkg' => $CRLSkg,
      'djjSkg' => $DJJSkg,
      'ga_hariSkg' => $UsiaKehamilanHariSkg,
      'ga_mingguSkg' => $UsiaKehamilanMingguSkg,
      'janin_tunggalSkg' => $JaninTunggalSkg,
      'janin_kembarSkg' => $JaninKembarSkg,
      'janin_hidupSkg' => $JaninHidupSkg,
      'janin_iufdSkg' => $JaninIufdSkg,
      'letak_janin_kepalaSkg' => $LetakJaninKepalaSkg,
      'letak_janin_sungsangSkg' => $LetakJaninSungsangSkg,
      'letak_janin_melintangSkg' => $LetakJaninMelintangSkg,
      'letak_janin_obliqueSkg' => $LetakJaninObliqueSkg,
      'bpdSkg' => $BPDSkg,
      'flSkg' => $FLSkg,
      'acSkg' => $ACSkg,
      'efwSkg' => $EFWSkg,
      'usia_kehamilan_minggu1Skg' => $UsiaKehamilanMinggu1Skg,
      'usia_kehamilan_hari1Skg' => $UsiaKehamilanHari1Skg,
      'fudusSkg' => $FudusSkg,
      'corpusSkg' => $CorpusSkg,
      'sbrSkg' => $SBRSkg,
      'antSkg' => $ANTSkg,
      'pstSkg' => $PSTSkg,
      'afiSkg' => $AFISkg,
      'pemeriksaanPenunjang' => $pemeriksaanPenunjang,
      'JenisKelaminSkg' => $JenisKelaminSkg,
      'hpltpSkg' => $HPLTPSkg,
      'hplmdSkg' => $HPLMdSkg,
      'ketuban_cukupSkg' => $KetubanCukupSkg,
      'ketuban_banyakSkg' => $KetubanBanyakSkg,
      'ketuban_kurangSkg' => $KetubanKurangSkg,
      'usg_ginekologiSkg' => $USGGinekSkg,
      'gradeSkg' => $GradeSkg,
      'status_lokalis' => $status_lokalis,
      'g_analisa' => $g_analisa,
      'p_analisa' => $p_analisa,
      'a_analisa' => $a_analisa,
      'ket_diagnosa_satu' => $ket_diagnosa_satu,
      'ket_diagnosa_dua' => $ket_diagnosa_dua,
      'ket_diagnosa_tiga' => $ket_diagnosa_tiga,
      'ket_diagnosa_empat' => $ket_diagnosa_empat,
      'ket_diagnosa_lima' => $ket_diagnosa_lima,
      'usg_terakhir' => $UsgTerakhir,
      'pemeriksaan_penunjang' => $pemeriksaan_penunjang,

      'memahamiMateri' => $memahamiMateri ,
      'butuhLeaflet' => $butuhLeaflet ,
      'membatasiMateri' => $membatasiMateri ,
      'pengulanganMateri' => $pengulanganMateri,
      'bisaMengulang' => $bisaMengulang ,
      'lain_lainEdukasi' => $lain_lainEdukasi ,
      'lainEd_det' => $lainEd_det ,

      'diagnosa' => $diagnosa ,
      'penjelasan_penyakit' => $penjelasan_penyakit ,
      'pemeriksaan_penunjang' => $pemeriksaan_penunjang ,
      'terapi_edukasi' => $terapi_edukasi,
      'terapi_alter' => $terapi_alter,
      'tindakan_medis' => $tindakan_medis ,
      'perkiraan_hari_rawat' => $perkiraan_hari_rawat ,
      'penjelasan_komplikasi' => $penjelasan_komplikasi ,
      'informed_concent' => $informed_concent ,
      'kondisi' => $kondisi ,
      'konsul' => $konsul ,
      'konsul_det' => $konsul_det ,
      'edukasi_pulang' => $edukasi_pulang ,
      'edukasi_lain' => $edukasi_lain ,
      'lain_det' => $lain_det ,
      

      // for ($z=0; $z < 81; $z++) {
      //   'isi$z' => $dataAskep[$z]['anamnesa_isi_nilai'],
      // } 
      'reg_tanggal_terakhir'   => format_date($dataHistory['reg_tanggal_terakhir']),
    ]);
    }
    else {
    array_push($data, [
      'id_dep'   => $dataTable[$i]['id_dep'],
      'reg_id'   => $dataTable[$i]['reg_id'],
      'reg_tingkat_kegawatan'   => $dataTable[$i]['reg_tingkat_kegawatan'],
      'reg_status_kondisi_deskripsi'   => $dataTable[$i]['reg_status_kondisi_deskripsi'],
      'pembayaran_flag'   => $dataTable[$i]['pembayaran_flag'],
      'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
      'reg_status_kondisi'   => $dataTable[$i]['reg_status_kondisi'],
      'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
      'id_cust_usr'   => $dataTable[$i]['id_cust_usr'],
      'reg_rujukan_id'   => $dataTable[$i]['reg_rujukan_id'],
      'reg_status'   => $dataTable[$i]['reg_status'],
      'id_poli'   => $dataTable[$i]['id_poli'],
      'id_dokter'   => $dataTable[$i]['id_dokter'],
      'id_dokter2'   => $dataTable[$i]['id_dokter_2'],
      'reg_jenis_pasien'   => $dataTable[$i]['reg_jenis_pasien'],
      'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
      'cust_usr_nama'   => $dataTable[$i]['cust_usr_nama'],
      'rawat_id'   => $dataTable[$i]['rawat_id'],
      'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
      'poli_nama'   => $dataTable[$i]['poli_nama'],
      'sebab_sakit_nama'   => $dataTable[$i]['sebab_sakit_nama'],
      'shift_nama'   => $dataTable[$i]['shift_nama'],
      'jenis_nama'   => $dataTable[$i]['jenis_nama'],
      'usr_name'   => $dataTable[$i]['usr_name'],
      'usr_id'   => $dataTable[$i]['usr_id'],
      'rawat_anamnesa'   => $dataTable[$i]['rawat_anamnesa'],
      'rawat_keluhan'   => $dataTable[$i]['rawat_keluhan'],
      'rawat_catatan'   => $dataTable[$i]['rawat_catatan'],
      'rawat_pemeriksaan_fisik'   => $dataTable[$i]['rawat_pemeriksaan_fisik'],
      'rawat_diagnosa_utama'   => $dataTable[$i]['rawat_diagnosa_utama'],
      'rawat_ket'   => $dataTable[$i]['rawat_ket'],
      'rawat_ket'   => $dataTable[$i]['perusahaan_nama'],
      'reg_diagnosa_igd'   => $dataTable[$i]['reg_diagnosa_igd'],
      'jenis_kb_id' => $dataTable[$i]['id_jenis_kb'],
      'cust_usr_alergi'   => $dataTable[$i]['cust_usr_alergi'],
    ]);
  }
  }

  echo json_encode($data);
  //echo json_encode(array('errorMsg'=>'Some errors occured.'));
} elseif (!isset($_GET['reg_id'])) {

  if (isset($_POST['tgl_awal']) || isset($_POST['tgl_akhir'])) {
    $kondisi = " and a.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']));
    $kondisi .= " and a.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']));
  } else {
    $kondisi = "and a.reg_tanggal <='$tglSekarang'";
    $kondisi .= "and a.reg_tanggal >='$tglKemarin'";
    $kondisi .= "and reg_status!='G3'";
  }

  $sql = "select a.reg_kode_trans,a.reg_id,a.reg_tanggal,a.reg_waktu,a.reg_status,
    a.reg_tipe_jkn,a.reg_status_kondisi,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,
    b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama, perusahaan_nama,h.usr_name from 
    klinik.klinik_registrasi a left join 
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
    global.global_auth_poli c on a.id_poli = c.poli_id left join
    global.global_auth_user_poli d on a.id_poli = d.id_poli
    left join global.global_jkn e on a.reg_tipe_jkn = e.jkn_id
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
    left join global.global_auth_user h on h.usr_id = a.id_dokter
    ";
  $sql .= " where (a.reg_status_kondisi = '1' or a.reg_status_kondisi = '' or a.reg_status_kondisi is null) and c.poli_tipe='G'";
  $sql .= $kondisi;
  $sql .= " group by a.reg_id, b.cust_usr_id, c.poli_id, f.jenis_id, e.jkn_id, g.perusahaan_id,h.usr_id order by a.reg_tanggal desc,a.reg_waktu desc ";
  //die($sql);
  // echo $sql;
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  $data = [];

  for ($i = 0; $i < count($dataTable); $i++) {

    array_push($data, [
      'reg_kode_trans'   => $dataTable[$i]['reg_kode_trans'],
      'reg_id'   => $dataTable[$i]['reg_id'],
      'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
      'reg_waktu'   => $dataTable[$i]['reg_waktu'],
      'reg_status'   => $dataTable[$i]['reg_status'],
      'reg_tipe_jkn'   => $dataTable[$i]['reg_tipe_jkn'],
      'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
      'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
    
      'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode'],
      'cust_usr_nama'   => str_replace("*", "'", $dataTable[$i]['cust_usr_nama']),
      'cust_usr_tanggal_lahir'   => format_date($dataTable[$i]['cust_usr_tanggal_lahir']),
      'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
      'poli_nama'   => $dataTable[$i]['poli_nama'],
      'jenis_nama'   => $dataTable[$i]['jenis_nama'],
      'jkn_nama'   => $dataTable[$i]['jkn_nama'],
      'perusahaan_nama'   => $dataTable[$i]['perusahaan_nama'],
      'dokter'   => $dataTable[$i]['usr_name'],
      'poli'   => $dataTable[$i]['poli_nama'],
       'reg_status_kondisi'   => $dataTable[$i]['reg_status_kondisi'],
       'reg_status'   => $dataTable[$i]['reg_status'],
    ]);
  }

  echo json_encode($data);
}
