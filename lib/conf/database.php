<?
DEFINE('APP_ID', 'SIKITA');
DEFINE('APP_TITLE', 'SIKITA');

// --- connection data ---
DEFINE('DB_DRIVER', 'postgres');
DEFINE('DB_SERVER', 'localhost');
DEFINE('DB_USER', 'RVhQaXRzRVhQQkVSU0FUVQ==');
DEFINE('DB_PASSWORD', 'RVhQaXRzdGhva0VYUEJFUlNBVFU= ');
DEFINE('DB_NAME', 'nusamedis');
DEFINE('DB_NAME_SMS', 'nusamedis');
DEFINE('DB_SCHEMA', 'global');
DEFINE('DB_SCHEMA_LAB', 'laboratorium');
DEFINE('DB_SCHEMA_GLOBAL', 'global');
DEFINE('DB_SCHEMA_KLINIK', 'klinik');
DEFINE('DB_SCHEMA_HRIS', 'hris');
DEFINE('DB_SCHEMA_LOGISTIK', 'logistik');
DEFINE('DB_SCHEMA_CS', 'cs');
DEFINE('DB_SCHEMA_APOTIK', 'apotik');
DEFINE('DB_SCHEMA_GL', 'gl');
DEFINE('DB_SCHEMA_ACC', 'gl');
DEFINE('DB_SCHEMA_ULP', 'ulp');


//---connection data patch dan plugin
DEFINE('PG_DB_DRIVER', 'postgres');
DEFINE('PG_DB_SERVER', 'www.sikita.net'); //www.sikita.net
DEFINE('PG_DB_USER', 'its');  
DEFINE('PG_DB_PASSWORD', 'itsthok');
DEFINE('PG_DB_NAME', 'hrdsis');
DEFINE('PG_DB_NAME_SMS', 'hrdsis');
DEFINE('PG_DB_SCHEMA','global');
DEFINE('PG_DB_SCHEMA_GLOBAL','');

//---connection data postgreSQL
DEFINE('SY_DB_DRIVER', 'postgres');
DEFINE('SY_DB_SERVER', 'localhost');    // 180.243.65.166
DEFINE('SY_DB_USER', 'its');          
DEFINE('SY_DB_PASSWORD', 'itsthok');
DEFINE('SY_DB_NAME', 'rspi');       // sikita_dental_emerald
DEFINE('SY_DB_SCHEMA', 'global');
DEFINE('SY_DB_SCHEMA_GLOBAL', 'global');
DEFINE('SY_DB_SCHEMA_KLINIK', 'klinik');
DEFINE('SY_DB_SCHEMA_GL', 'gl');
DEFINE('SY_DB_SCHEMA_HRIS', 'hris');
DEFINE('SY_DB_SCHEMA_APOTIK', 'apotik');
DEFINE('SY_DB_SCHEMA_LOGISTIK', 'logistik');

DEFINE("SY_DEBUGGING",false);
DEFINE("SY_ORDER_ASC", 0);
DEFINE("SY_ORDER_DESC", 1);

DEFINE("TREE_LENGTH",3);
DEFINE('DB_DEBUGGING',false);
DEFINE("DP_ORDER_ASC", 0);
DEFINE("DP_ORDER_DESC", 1);

DEFINE('PG_UPDATE_FILE_SERVER', 'logbook.sikita.net/gambar/file_update_zend');
DEFINE('PG_UPDATE_FILE_SOURCE_SERVER', 'logbook.sikita.net/gambar/file_source');
DEFINE('PG_UPDATE_PLUGIN_FILE_SERVER', 'logbook.sikita.net/gambar/file_update_zend_plugin');

DEFINE('SY_UPDATE_FILE_SERVER', 'logbook.sikita.net/gambar/file_source');
DEFINE('SY_UPDATE_PLUGIN_FILE_SERVER', 'logbook.sikita.net/gambar/file_source_plugin');

DEFINE("DPE_CHAR", 1); /*qstr ''*/
DEFINE("DPE_CLOB", 2); /*qstr ''*/
DEFINE("DPE_DATE", 3); /*DBDate ''*/
DEFINE("DPE_DATETIME", 4);  /*DBDate ''*/
DEFINE("DPE_TIMESTAMP", 5); /*DBDate ''*/
DEFINE("DPE_BOOL", 6);
DEFINE("DPE_NUMERIC", 7);
DEFINE("DPE_BLOB", 8);
DEFINE("DPE_NUMERICKEY", 9);
DEFINE("DPE_CHARKEY", 10);

DEFINE("PRIV_CREATE",0);
DEFINE("PRIV_READ",1);
DEFINE("PRIV_UPDATE",2);
DEFINE("PRIV_DELETE",3);

// --- modul code ---
DEFINE("APP_CAREMAX","10");
DEFINE("APP_OPTIK","11");
DEFINE("APP_LOGISTIK","12");


DEFINE("TIPE_DISTRIBUTOR","00");

DEFINE("ROLE_TIPE_CUSTOMER",1);
DEFINE("ROLE_TIPE_DISTRIBUTOR",2);

DEFINE("USR_TIPE_CUSTOMER","C");
DEFINE("USR_TIPE_DISTRIBUTOR","D");
DEFINE("USR_TIPE_EXPRESSA","I");

DEFINE('USER_IDLE','1');	
DEFINE('USER_AKTIF','2');	

DEFINE("TREE_LENGTH",10);
DEFINE("TREE_LENGTH_CHILD",2);
DEFINE("TREE_LENGTH_TIC",10);
DEFINE("TREE_DELIMITER","... ");

DEFINE("STATUS_REGISTRASI","A");
DEFINE("STATUS_PEMERIKSAAN","M");
DEFINE("STATUS_PREOP","P");
DEFINE("STATUS_OPERASI","O");
DEFINE("STATUS_OPERASI_JADWAL","J");
DEFINE("STATUS_BEDAH","B");
DEFINE("STATUS_DIAGNOSTIK","D");
DEFINE("STATUS_SELESAI","E");
DEFINE("STATUS_PREMEDIKASI","K");
DEFINE("STATUS_RAWATINAP","I");
DEFINE("STATUS_CEKOUT","C");
DEFINE("STATUS_MENINGGAL","N");
DEFINE("STATUS_ICU","U");
DEFINE("STATUS_PICU","L");
DEFINE("STATUS_APOTEK","T");
DEFINE("STATUS_UGD","G");
DEFINE("STATUS_UGD_SPESIALIS","G1");
DEFINE("STATUS_TINDAKAN_RAWATJALAN","TJ");
DEFINE("STATUS_TINDAKAN_RAWATINAP","TI");
DEFINE("STATUS_TINDAKAN_UGD","TG");
DEFINE("STATUS_RAWATJALAN_SPESIALIS","A1");
DEFINE("STATUS_RAWATJALAN_AHLIGIZI","A2");
DEFINE("STATUS_REGISTRASI_KOSONG","KS");

$rawatStatus[STATUS_REGISTRASI] = "Registrasi";
$rawatStatus[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$rawatStatus[STATUS_PREOP] = "PreOP";
$rawatStatus[STATUS_OPERASI] = "Operasi";
$rawatStatus[STATUS_OPERASI_JADWAL] = "Jadwal";
$rawatStatus[STATUS_BEDAH] = "Bedah Minor";
$rawatStatus[STATUS_DIAGNOSTIK] = "Diagnostik";
$rawatStatus[STATUS_SELESAI] = "Selesai";
$rawatStatus[STATUS_PREMEDIKASI] = "Premedikasi";
$rawatStatus[STATUS_RAWATINAP] = "Rawat Inap";
$rawatStatus[STATUS_CEKOUT] = "Check Out";
$rawatStatus[STATUS_MENINGGAL] = "Meninggal";
$rawatStatus[STATUS_ICU] = "ICU";
$rawatStatus[STATUS_PICU] = "PICU";
$rawatStatus[STATUS_APOTEK] = "Apotek";
$rawatStatus[STATUS_UGD] = "UGD";

//$biayaStatus[STATUS_REGISTRASI] = "Registrasi";
$biayaStatus[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$biayaTetap[STATUS_REGISTRASI] = "Rawat Jalan ( Umum )";
$biayaTetap[STATUS_UGD] = "UGD ( Umum )";
$biayaTetap[STATUS_RAWATJALAN_SPESIALIS] = "Rawat Jalan ( Spesialis )";
$biayaTetap[STATUS_RAWATJALAN_AHLIGIZI] = "Rawat Jalan ( Ahli Gizi )";
$biayaTetap[STATUS_RAWATINAP] = "VK ( Bersalin )";
$biayaTetap[STATUS_UGD_SPESIALIS] = "UGD";


//SETUP SPLIT
DEFINE("SPLIT_REGISTRASI","SB");
DEFINE("SPLIT_PERAWATAN","SU");
DEFINE("SPLIT_TINDAKAN","SA");
DEFINE("SPLIT_OBAT","SO");
DEFINE("SPLIT_INAP","SI");
DEFINE("SPLIT_VISITE","SC");


$namaSPLIT[SPLIT_REGISTRASI] = "SPLIT REGISTRASI";
$namaSPLIT[SPLIT_PERAWATAN] = "SPLIT PERAWATAN";
$namaSPLIT[SPLIT_TINDAKAN] = "SPLIT TINDAKAN";
$namaSPLIT[SPLIT_OBAT] = "SPLIT OBAT";
$namaSPLIT[SPLIT_VISITE] = "SPLIT VISITE";
$namaSPLIT[SPLIT_INAP] = "SPLIT KELAS KAMAR";

//SETUP BIAYA REGISTRASI
DEFINE("KARCIS_LOKET","WA");

$namaReg[KARCIS_LOKET] = "Biaya Karcis Loket";
$namaFolio[KARCIS_LOKET] = "Biaya Karcis Loket";

//SETUP BIAYA PEMERIKSAAN
//DEFINE("REGISTRASI_UMUM","RJ");
DEFINE("REGISTRASI_SPESIALIS"," ");
//DEFINE("REGISTRASI_UGD","RU");
//DEFINE("REGISTRASI_VK","RV");

//$namaRegistrasi[REGISTRASI_UMUM] = "Pemeriksaan Poli Umun";
$namaRegistrasi[REGISTRASI_SPESIALIS] = "Pemeriksaan Poli Spesialis";
//$namaRegistrasi[REGISTRASI_UGD] = "Pemeriksaan UGD";
//$namaRegistrasi[REGISTRASI_VK] = "Pemeriksaan VK"; 

$namaFolio[REGISTRASI_UMUM] = "Pemeriksaan Poli Klinik";
$namaFolio[REGISTRASI_SPESIALIS] = "Pemeriksaan Poli Spesialis";
$namaFolio[REGISTRASI_UGD] = "Pemeriksaan UGD";
$namaFolio[REGISTRASI_VK] = "Pemeriksaan VK"; 
 
//SETUP TINDAKAN
DEFINE("TINDAKAN_KLINIK","TA");
//DEFINE("TINDAKAN_UGD","TB");
DEFINE("TINDAKAN_1","TC");
DEFINE("TINDAKAN_2","TD");
DEFINE("TINDAKAN_3","TE");
DEFINE("TINDAKAN_VIP","TF");
DEFINE("TINDAKAN_OPERASI_1","OA");
DEFINE("TINDAKAN_OPERASI_2","OB");
DEFINE("TINDAKAN_OPERASI_3","OC");
DEFINE("TINDAKAN_OPERASI_4","OD");
DEFINE("TINDAKAN_LABORATORIUM_1","LA");
DEFINE("TINDAKAN_LABORATORIUM_2","LB");
DEFINE("TINDAKAN_LABORATORIUM_3","LC");
DEFINE("TINDAKAN_LABORATORIUM_4","LD");


$namaTindakan[TINDAKAN_KLINIK] = "Tindakan Klinik";
//$namaTindakan[TINDAKAN_UGD] = "Tindakan UGD";
$namaTindakanInap[TINDAKAN_1] = "Tindakan Kelas 1";
$namaTindakanInap[TINDAKAN_2] = "Tindakan Kelas 2";
$namaTindakanInap[TINDAKAN_3] = "Tindakan Kelas 3";
$namaTindakanInap[TINDAKAN_VIP] = "Tindakan VIP";

$namaTindakanOperasi[TINDAKAN_OPERASI_1] = "Tindakan Operasi Kelas 1";
$namaTindakanOperasi[TINDAKAN_OPERASI_2] = "Tindakan Operasi Kelas 2";
$namaTindakanOperasi[TINDAKAN_OPERASI_3] = "Tindakan Operasi Kelas 3";
$namaTindakanOperasi[TINDAKAN_OPERASI_4] = "Tindakan Operasi Kelas Paviliun";

$tindakanLab[TINDAKAN_LABORATORIUM_1] = "Tindakan Laboratorium Kelas 1";
$tindakanLab[TINDAKAN_LABORATORIUM_2] = "Tindakan Laboratorium Kelas 2";
$tindakanLab[TINDAKAN_LABORATORIUM_3] = "Tindakan Laboratorium Kelas 3";
$tindakanLab[TINDAKAN_LABORATORIUM_4] = "Tindakan Laboratorium Kelas Paviliun";

$namaFolio[TINDAKAN_KLINIK] = "Tindakan Klinik";
//$namaFolio[TINDAKAN_UGD] = "Tindakan UGD";
$namaFolio[TINDAKAN_1] = "Tindakan Kelas 1";
$namaFolio[TINDAKAN_2] = "Tindakan Kelas 2";
$namaFolio[TINDAKAN_3] = "Tindakan Kelas 3";
$namaFolio[TINDAKAN_VIP] = "Tindakan VIP";
$namaFolio[TINDAKAN_OPERASI_1] = "Tindakan Operasi Kelas 1";
$namaFolio[TINDAKAN_OPERASI_2] = "Tindakan Operasi Kelas 2";
$namaFolio[TINDAKAN_OPERASI_3] = "Tindakan Operasi Kelas 3";
$namaFolio[TINDAKAN_OPERASI_4] = "Tindakan Operasi Kelas Paviliun";
$namaFolio[TINDAKAN_LABORATORIUM_1] = "Tindakan Laboratorium Kelas 1";
$namaFolio[TINDAKAN_LABORATORIUM_2] = "Tindakan Laboratorium Kelas 2";
$namaFolio[TINDAKAN_LABORATORIUM_3] = "Tindakan Laboratorium Kelas 3";
$namaFolio[TINDAKAN_LABORATORIUM_4] = "Tindakan Laboratorium Kelas Paviliun";

//SETUP KELAS
DEFINE("KELAS_1","KA");
DEFINE("KELAS_2","KB");
DEFINE("KELAS_3","KC");
DEFINE("KELAS_VIP","KD");
DEFINE("KELAS_IRD","KE");
DEFINE("KELAS_HCU","KF");
DEFINE("KELAS_ICU","KG");


$namaKelas[KELAS_1] = "Biaya Kelas 1 per Hari";
$namaKelas[KELAS_2] = "Biaya Kelas 2 per Hari";
$namaKelas[KELAS_3] = "Biaya Kelas 3 per Hari";
$namaKelas[KELAS_VIP] = "Biaya Kelas Paviliun per Hari";
$namaKelas[KELAS_IRD] = "Biaya Kelas IRD / Perinatlogi per Hari";
$namaKelas[KELAS_HCU] = "Biaya Kelas HCU per Hari";
$namaKelas[KELAS_ICU] = "Biaya Kelas ICU per Hari";

$judulKelas[KELAS_1] = "Kelas 1";
$judulKelas[KELAS_2] = "Kelas 2";
$judulKelas[KELAS_3] = "Kelas 3";
$judulKelas[KELAS_VIP] = "Kelas Paviliun";
$judulKelas[KELAS_IRD] = "Kelas IRD";
$judulKelas[KELAS_HCU] = "Kelas HCU";
$judulKelas[KELAS_ICU] = "Kelas ICU";


$namaFolio[KELAS_1] = "Kelas 1";
$namaFolio[KELAS_2] = "Kelas 2";
$namaFolio[KELAS_3] = "Kelas 3";
$namaFolio[KELAS_VIP] = "Kelas Paviliun";
$namaFolio[KELAS_IRD] = "Kelas IRD";
$namaFolio[KELAS_HCU] = "Kelas HCU";
$namaFolio[KELAS_ICU] = "Kelas ICU";


//Setup Visite
DEFINE("VISITE_1","VA");
DEFINE("VISITE_2","VB");
DEFINE("VISITE_3","VC");
DEFINE("VISITE_VIP","VD");
DEFINE("VISITE_IRD","VE");
DEFINE("VISITE_HCU","VF");
DEFINE("VISITE_ICU","VG");

$namaVisite[VISITE_1] = "Biaya Visite Kelas 1";
$namaVisite[VISITE_2] = "Biaya Visite Kelas 2";
$namaVisite[VISITE_3] = "Biaya Visite Kelas 3";
$namaVisite[VISITE_VIP] = "Biaya Visite Kelas Paviliun";
$namaVisite[VISITE_IRD] = "Biaya Visite Kelas IRD";
$namaVisite[VISITE_HCU] = "Biaya Visite Kelas HCU";
$namaVisite[VISITE_ICU] = "Biaya Visite Kelas ICU";

$namaFolio[VISITE_1] = "Biaya Visite Kelas 1";
$namaFolio[VISITE_2] = "Biaya Visite Kelas 2";
$namaFolio[VISITE_3] = "Biaya Visite Kelas 3";
$namaFolio[VISITE_VIP] = "Biaya Visite Kelas Paviliun";
$namaFolio[VISITE_IRD] = "Biaya Visite Kelas IRD";
$namaFolio[VISITE_HCU] = "Biaya Visite Kelas HCU";
$namaFolio[VISITE_ICU] = "Biaya Visite Kelas ICU";
 
//$ruangProses[STATUS_REFRAKSI] = "Refraksi";
$ruangProses[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$ruangProses[STATUS_PREOP] = "PreOP";
$ruangProses[STATUS_OPERASI] = "Operasi"; 
$ruangProses[STATUS_BEDAH] = "Bedah";
$ruangProses[STATUS_DIAGNOSTIK] = "Diagnostik"; 
$ruangProses[STATUS_PREMEDIKASI] = "Premedikasi";
$ruangProses[STATUS_RAWATINAP] = "Rawat Inap";
$ruangProses[STATUS_ICU] = "ICU";
$ruangProses[STATUS_PICU] = "PICU";
$ruangProses[STATUS_APOTEK] = "Apotek";

DEFINE("STATUS_ANTRI","0");
DEFINE("STATUS_PROSES","1");
DEFINE("STATUS_MENGINAP","2");

$rawatStatus[STATUS_ANTRI] = "Antri";
$rawatStatus[STATUS_PROSES] = "Proses";
$rawatStatus[STATUS_MENGINAP] = "Menginap";

DEFINE("PGW_JENIS_DOKTER",1);
DEFINE("PGW_JENIS_SUSTER",2);
DEFINE("PGW_JENIS_ADMINISTRASI",3);
DEFINE("PGW_JENIS_BIDAN",4);
DEFINE("PGW_JENIS_RADIOGRAFER",5);
DEFINE("PGW_JENIS_AHLIGIZI",6);
DEFINE("PGW_JENIS_REKAMMEDIK",7);
DEFINE("PGW_JENIS_SANITARIAN",8);
DEFINE("PGW_JENIS_FISIOTERAPI",9);
DEFINE("PGW_JENIS_TEKNIKELEKTROMEDIK",10);
DEFINE("PGW_JENIS_ANALISKESEHATAN",11);
DEFINE("PGW_JENIS_ASISTENAPOTEKER",12);
DEFINE("PGW_JENIS_PENGEMUDI",13);
DEFINE("PGW_JENIS_PENJAGA",14);
DEFINE("PGW_JENIS_STAFTATAUSAHA",15);
DEFINE("PGW_JENIS_PEMULASARAN",16);
DEFINE("PGW_JENIS_KABAGTATAUSAHA",17);
DEFINE("PGW_JENIS_KABIDKEUANGAN",18);
DEFINE("PGW_JENIS_KABIDPENUNJANG",19);
DEFINE("PGW_JENIS_KABIDPELAYANAN",20);
DEFINE("PGW_JENIS_PELAKSANAGIZI",21);
DEFINE("PGW_JENIS_PERAWATGIGI",22);
DEFINE("PGW_JENIS_PERAWATANASTESI",23);
DEFINE("PGW_JENIS_DOKTERAHLIBEDAH",24);
DEFINE("PGW_JENIS_DOKTERSPESIALIS",25);
DEFINE("PGW_JENIS_DOKTERGIGI",26);
DEFINE("PGW_JENIS_DOKTERSPESIALISSYARAF",27);
DEFINE("PGW_JENIS_DOKTERSPESIALISDALAM",28);
DEFINE("PGW_JENIS_STAFKEUANGAN",29);
DEFINE("PGW_JENIS_KASIMOBILISASIDANA",30);
DEFINE("PGW_JENIS_KASIPELKEPERAWATAN",31);
DEFINE("PGW_JENIS_KASIPENUNJANGNONMEDIS",32);
DEFINE("PGW_JENIS_KASUBAGPERCRMEDIS",33);
DEFINE("PGW_JENIS_KASUBAGKEPEGAWAIAN",34);
DEFINE("PGW_JENIS_KASIPENUNJANGMEDIS",35);
DEFINE("PGW_JENIS_KASUBAGUMUM",36);
DEFINE("PGW_JENIS_KASIPERBENDARAAN",37);
DEFINE("PGW_JENIS_KASIPELAYANANMEDIS",38);
DEFINE("PGW_JENIS_DIREKTUR",39);

$jenisPegawai[PGW_JENIS_DOKTER] = "Dokter";
$jenisPegawai[PGW_JENIS_SUSTER] = "Perawat";
$jenisPegawai[PGW_JENIS_ADMINISTRASI] = "Administrasi";
$jenisPegawai[PGW_JENIS_BIDAN] = "Bidan";
$jenisPegawai[PGW_JENIS_RADIOGRAFER] = "Radiografer";
$jenisPegawai[PGW_JENIS_AHLIGIZI] = "Ahli Gizi";
$jenisPegawai[PGW_JENIS_REKAMMEDIK] = "Rekam Medik";
$jenisPegawai[PGW_JENIS_SANITARIAN] = "Sanitarian";
$jenisPegawai[PGW_JENIS_FISIOTERAPI] = "Fisioterapi";
$jenisPegawai[PGW_JENIS_TEKNIKELEKTROMEDIK] = "Teknik Elektro Medik";
$jenisPegawai[PGW_JENIS_ANALISKESEHATAN] = "Analisis Kesehatan";
$jenisPegawai[PGW_JENIS_ASISTENAPOTEKER] = "Asisten Apoteker";
$jenisPegawai[PGW_JENIS_PENGEMUDI] = "Pengemudi";
$jenisPegawai[PGW_JENIS_PENJAGA] = "Penjaga";
$jenisPegawai[PGW_JENIS_STAFTATAUSAHA] = "Staf Tata Usaha";
$jenisPegawai[PGW_JENIS_PEMULASARAN] = "Pemulasan";
$jenisPegawai[PGW_JENIS_KABAGTATAUSAHA] = "Kabag Tata Usaha";
$jenisPegawai[PGW_JENIS_KABIDKEUANGAN] = "Kabid Keuangan";
$jenisPegawai[PGW_JENIS_KABIDPENUNJANG] = "Kabid Penunjang";
$jenisPegawai[PGW_JENIS_KABIDPELAYANAN] = "Kabid Pelayanan";
$jenisPegawai[PGW_JENIS_PELAKSANAGIZI] = "Pelaksana Gizi";
$jenisPegawai[PGW_JENIS_PERAWATGIGI] = "Perawat Gigi";
$jenisPegawai[PGW_JENIS_PERAWATANASTESI] = "Perwatan Anastesi";
$jenisPegawai[PGW_JENIS_DOKTERAHLIBEDAH] = "Dokter Ahli Bedah";
$jenisPegawai[PGW_JENIS_DOKTERSPESIALIS] = "Dokter Spesialis";
$jenisPegawai[PGW_JENIS_DOKTERGIGI] = "Dokter Gigi";
$jenisPegawai[PGW_JENIS_DOKTERSPESIALISSYARAF] = "Dokter Spesialis Syaraf";
$jenisPegawai[PGW_JENIS_DOKTERSPESIALISDALAM] = "Dokter Spesialis Dalam";
$jenisPegawai[PGW_JENIS_STAFKEUANGAN] = "Staf Keuangan";
$jenisPegawai[PGW_JENIS_KASIMOBILISASIDANA] = "Kasi Mobilisasi Dana";
$jenisPegawai[PGW_JENIS_KASIPENUNJANGNONMEDIS] = "Kasi Penunjang Non Medis";
$jenisPegawai[PGW_JENIS_KASIPELKEPERAWATAN] = "Kasi Pel. Keperawatan";
$jenisPegawai[PGW_JENIS_KASUBAGPERCRMEDIS] = "Ka.Subag.Perc.&.R.Medis";
$jenisPegawai[PGW_JENIS_KASUBAGKEPEGAWAIAN] = "Ka.Subag.Kepegawaian";
$jenisPegawai[PGW_JENIS_KASIPENUNJANGMEDIS] = "Kasi Penunjang Medis";
$jenisPegawai[PGW_JENIS_KASUBAGUMUM] = "Ka.Subag.Umum";
$jenisPegawai[PGW_JENIS_KASIPERBENDARAAN] = "Kasi Perbendaraan";
$jenisPegawai[PGW_JENIS_KASIPELAYANANMEDIS] = "Kasi Pelayanan Medis";
$jenisPegawai[PGW_JENIS_DIREKTUR] = "Direktur";


DEFINE("RAWAT_KEADAAN_BAIK","B");
DEFINE("RAWAT_KEADAAN_LEMAH","L");
DEFINE("RAWAT_KEADAAN_JELEK","J");
DEFINE("RAWAT_KEADAAN_COMA","C");

$rawatKeadaan[RAWAT_KEADAAN_BAIK] = "Baik";
$rawatKeadaan[RAWAT_KEADAAN_LEMAH] = "Lemah";
$rawatKeadaan[RAWAT_KEADAAN_JELEK] = "Jelek";
$rawatKeadaan[RAWAT_KEADAAN_COMA] = "Coma";

DEFINE("RAWAT_JENIS_PENYAKIT_BARU","B");
DEFINE("RAWAT_JENIS_PENYAKIT_LAMA","L");

$rawatJenisPenyakit[RAWAT_JENIS_PENYAKIT_BARU] = "Baru";
$rawatJenisPenyakit[RAWAT_JENIS_PENYAKIT_LAMA] = "Lama";


DEFINE("JK_LAKILAKI","L");
DEFINE("JK_PEREMPUAN","P");

$jenisKelamin[JK_LAKILAKI] = "Laki-laki";
$jenisKelamin[JK_PEREMPUAN] = "Perempuan";


DEFINE("KAT_OBAT_ANESTESIS","01");
DEFINE("KAT_OBAT_INJEKSI","02");

DEFINE("ICD_DIAGNOSIS","1");
DEFINE("ICD_OPERASI","2");

DEFINE("INA_DIAGNOSIS","1");
DEFINE("INA_OPERASI","2");

DEFINE("BIAYA_LOKET","1");
DEFINE("BIAYA_GULA","2");
DEFINE("BIAYA_ARK","6");
DEFINE("BIAYA_USG","3");
DEFINE("BIAYA_GULA_PREOP","4");
DEFINE("BIAYA_INJEKSI","7");
DEFINE("BIAYA_KERATOMETRI","8");
DEFINE("BIAYA_BIOMETRI","9");
DEFINE("BIAYA_KARTU","10");
DEFINE("BIAYA_EKG","11");
DEFINE("BIAYA_FUNDUS","12");
DEFINE("BIAYA_OPTHALMOSCOPY","13");
DEFINE("BIAYA_OCT","14");
DEFINE("BIAYA_YAG","15");
DEFINE("BIAYA_ARGON","16");
DEFINE("BIAYA_GLAUKOMA","17");
DEFINE("BIAYA_HUMPREY","18");

// -- biaya pemeriksaan --
DEFINE("BIAYA_UJIMATA","19");

// --- biaya preop ---
DEFINE("BIAYA_GULA_PREOP","4");
DEFINE("BIAYA_GULAREGULASI_PREOP","20");

$bayarNama[BIAYA_KARTU] = "Cetak Kartu Identitas Pasien";

DEFINE("PASIEN_BARU","B");
DEFINE("PASIEN_LAMA","L");

$statusPasien[PASIEN_BARU] = "Baru";
$statusPasien[PASIEN_LAMA] = "Lama";


DEFINE("PASIEN_BAYAR_ASKES","1");
DEFINE("PASIEN_BAYAR_SWADAYA","2");
DEFINE("PASIEN_BAYAR_JAMKESNAS_PUSAT","3");
DEFINE("PASIEN_BAYAR_JAMKESNAS_DAERAH","4");
DEFINE("PASIEN_GR","5");
DEFINE("PASIEN_SKTM","6");
DEFINE("PASIEN_CORP","7");
DEFINE("PASIEN_PROG","8");
DEFINE("PASIEN_GRAT","9");

$bayarPasien[PASIEN_BAYAR_ASKES] = "Askes";
$bayarPasien[PASIEN_BAYAR_SWADAYA] = "Umum";
$bayarPasien[PASIEN_BAYAR_JAMKESNAS_PUSAT] = "Jamkesmas";
$bayarPasien[PASIEN_BAYAR_JAMKESNAS_DAERAH] = "Jamkesmas Daerah";
$bayarPasien[PASIEN_GR] = "JKN";
$bayarPasien[PASIEN_SKTM] = "SKTM";
$bayarPasien[PASIEN_CORP] = "Corporate";
$bayarPasien[PASIEN_PROG] = "Program";
$bayarPasien[PASIEN_GRAT] = "Gratis";

$bayarPasien2[PASIEN_BAYAR_ASKES] = "ASK";
$bayarPasien2[PASIEN_BAYAR_SWADAYA] = "SWD";
$bayarPasien2[PASIEN_BAYAR_JAMKESNAS_PUSAT] = "JMP";
$bayarPasien2[PASIEN_BAYAR_JAMKESNAS_DAERAH] = "JMD";
$bayarPasien2[PASIEN_GR] = "Gratis";
$bayarPasien2[PASIEN_SKTM] = "SKTM";
$bayarPasien2[PASIEN_CORP] = "CORP";


//SETUP Laboratorium
DEFINE("BAYAR_LABORATORIUM_KLINIK","ZA");
DEFINE("BAYAR_LABORATORIUM_INAP","ZB");

$namaLab[BAYAR_LABORATORIUM_KLINIK] = "Biaya Laboratorium";
$namaLab[BAYAR_LABORATORIUM_INAP] = "Biaya Laboratorium Rawat Inap";

$namaFolio[BAYAR_LABORATORIUM_KLINIK] = "Biaya Laboratorium";
$namaFolio[BAYAR_LABORATORIUM_INAP] = "Biaya Laboratorium Rawat Inap";

//SETUP Poli
DEFINE("POLI_ORTO","1");
DEFINE("POLI_GIGI","2");
DEFINE("POLI_REHAB","3");
DEFINE("POLI_PENY_DLM","4");
DEFINE("POLI_IGD","5");
DEFINE("POLI_ORTO_PEDIATRI","6");
DEFINE("POLI_RAD","7");
DEFINE("POLI_ANESTESI","8");
DEFINE("POLI_PSIKOLOGI","9");
DEFINE("POLI_FISIO","10");
DEFINE("POLI_DIKLIT","11");
DEFINE("POLI_LAB","12");
DEFINE("POLI_ORTO_ONKOLOGI","13");
DEFINE("POLI_OSTEO","14");
DEFINE("POLI_AKUPUNTUR","15");
DEFINE("POLI_ANAK","16");
DEFINE("POLI_SYARAF","17");
DEFINE("POLI_BEDAH","18");
DEFINE("POLI_TLG_BLK","19");
DEFINE("POLI_OKUPASI","20");
DEFINE("POLI_PSM","21");
DEFINE("POLI_WICARA","22");
DEFINE("POLI_OP","23");
DEFINE("POLI_SUB_SP","24");
DEFINE("POLI_INTERNIS","40");

$namaPoli[POLI_ORTO] = "ORTOPEDI";
$namaPoli[POLI_GIGI] = "GIGI & MULUT";
$namaPoli[POLI_REHAB] = "REHABILITAS MEDIK";
$namaPoli[POLI_PENY_DLM] = "PENYAKIT DALAM";
$namaPoli[POLI_IGD] = "IGD";
$namaPoli[POLI_ORTO_PEDIATRI] = "ORTOPEDI PEDIATRI";
$namaPoli[POLI_RAD] = "RADIOLOGI";
$namaPoli[POLI_ANESTESI] = "ANESTESI";
$namaPoli[POLI_PSIKOLOGI] = "PSIKOLOGI";
$namaPoli[POLI_FISIO] = "FISIOTERAPI";
$namaPoli[POLI_DIKLIT] = "DIKLIT";
$namaPoli[POLI_LAB] = "LABORATORIUM";
$namaPoli[POLI_ORTO_ONKOLOGI] = "ORTOPEDI ONKOLOGI";
$namaPoli[POLI_OSTEO] = "OSTEOPOROSIS";
$namaPoli[POLI_AKUPUNTUR] = "AKUPUNTUR";
$namaPoli[POLI_ANAK] = "PERKEMBANGAN ANAK";
$namaPoli[POLI_SYARAF] = "NEUROLOGI/SYARAF";
$namaPoli[POLI_BEDAH] = "BEDAH UMUM";
$namaPoli[POLI_TLG_BLK] = "TULANG BELAKANG";
$namaPoli[POLI_OKUPASI] = "OKUPASI TERAPI";
$namaPoli[POLI_PSM] = "PEKERJA SOSIAL MEDIK";
$namaPoli[POLI_WICARA] = "TERAPI WICARA";
$namaPoli[POLI_OP] = "ORTOTIK PROSTETIK";
$namaPoli[POLI_GIGI] = "POLI GIGI";
$namaPoli[POLI_INTERNIS] = "POLI INTERNIS";

// SETUP STATUS REGISTRASI
DEFINE("REG_RWT_JLN1","E0");
DEFINE("REG_RWT_JLN","E1");
DEFINE("REG_RWT_JLN2","E2");
DEFINE("REG_DFTR_LOKET","M0");
DEFINE("REG_DFTR_POLI2","M1");
DEFINE("REG_DFTR_RANAP","I0");
DEFINE("REG_RANAP_KMR","I1");
DEFINE("REG_RANAP","I2");
DEFINE("REG_PLG_BLM","I4");
DEFINE("REG_PLG_SDH","I5");
DEFINE("REG_DFTR_IGD","G0");
DEFINE("REG_PROSES_IGD","G1");
DEFINE("REG_IGD_PLG","G2");
DEFINE("REG_RANAP_RENCANAPULANG","I3");
DEFINE("REG_PAS_PULANG","F0");

$regPasienStatus[REG_RWT_JLN1] = "Rawat Jalan Awal";
$regPasienStatus[REG_RWT_JLN] = "Rawat Jalan";
$regPasienStatus[REG_RWT_JLN2] = "Rawat Jalan Pulang";
$regPasienStatus[REG_DFTR_LOKET] = "Pendaftaran Loket";
$regPasienStatus[REG_DFTR_POLI2] = "Pendaftaran Poli Kedua";
$regPasienStatus[REG_DFTR_RANAP] = "Pendaftaran Rawat Inap";
$regPasienStatus[REG_RANAP_KMR] = "Rawat Inap Pilih Kamar";
$regPasienStatus[REG_RANAP] = "Rawat Inap";
$regPasienStatus[REG_PLG_BLM] = "Pasien Pulang Belum Bayar";
$regPasienStatus[REG_PLG_SDH] = "Pasien Pulang Sudah Bayar";
$regPasienStatus[REG_DFTR_IGD] = "Pendaftaran IGD";
$regPasienStatus[REG_PROSES_IGD] = "Tindakan IGD";
$regPasienStatus[REG_IGD_PLG] = "IGD Pulang";
$regPasienStatus[REG_RANAP_RENCANAPULANG] = "Pasien Inap Rencana Pulang";
$regPasienStatus[REG_PAS_PULANG] = "Pasien Sudah Pulang";

// SETUP JABATAN
DEFINE("STS_JAB_DOKTER","D");
DEFINE("STS_JAB_PERAWAT","P");
DEFINE("STS_JAB_STAFF","S");
DEFINE("STS_JAB_ANALIS","A");
DEFINE("STS_JAB_RADIOGRAFER","R");
DEFINE("STS_JAB_FISIOTERAPIS","F");
DEFINE("STS_JAB_ANESTESIS","AN");
DEFINE("STS_JAB_PPDS","PD");

$roleJabatan[STS_JAB_DOKTER] = "Dokter";
$roleJabatan[STS_JAB_PERAWAT] = "Perawat";
$roleJabatan[STS_JAB_STAFF] = "Staff";
$roleJabatan[STS_JAB_ANALIS] = "Analis Lab";
$roleJabatan[STS_JAB_RADIOGRAFER] = "Radiografer";
$roleJabatan[STS_JAB_FISIOTERAPIS] = "Fisioterapis";
$roleJabatan[STS_JAB_ANESTESIS] = "Anestesis";
$roleJabatan[STS_JAB_PPDS] = "PPDS";

DEFINE("KMR_PK","f09d60256219726d8789d0b1f1c432df");
DEFINE("KMR_PS","6d23bd012ca71bf754b2832fe455acdd");
DEFINE("KMR_CK","6d9bf24eb455a7ea707635e9d6edfac5");
DEFINE("KMR_CS","d0b403af88a5f8826da2d1a290fb7c88");
DEFINE("KMR_SJ","79036ece2a771c7254cf6ceb4ff3e20c");
DEFINE("KMR_WK","30ba78528f612bbd4147e717ae00ab68");
DEFINE("KMR_ICU","1540ffb2329c744846a9ab5509c051ab");
DEFINE("KMR_HCU","d5a0494485455f8b6563ab1521b3cf74");

$namaKamar[KMR_PK] = "Parang Kusumo";
$namaKamar[KMR_PK] = "Parang Seling";
$namaKamar[KMR_CK] = "Ceplok Kembang";
$namaKamar[KMR_CS] = "Ceplok Sriwedari";
$namaKamar[KMR_SJ] = "Sekar Jagad";
$namaKamar[KMR_WK] = "Wijaya Kusuma";
$namaKamar[KMR_ICU] = "ICU";
$namaKamar[KMR_HCU] = "HCU";

// SETUP JABATAN
DEFINE("POLI_TIPE_LAB","L");
DEFINE("POLI_TIPE_RAD","R");
DEFINE("POLI_TIPE_IRJ","J");
DEFINE("POLI_TIPE_IRNA","I");
DEFINE("POLI_TIPE_REHAB","M");
DEFINE("POLI_TIPE_IGD","G");

$poliTipe[POLI_TIPE_LAB] = "Laboratorium";
$poliTipe[POLI_TIPE_RAD] = "Radiologi";
$poliTipe[POLI_TIPE_IRJ] = "Rawat Jalan";
$poliTipe[POLI_TIPE_IRNA] = "Rawat Inap";
$poliTipe[POLI_TIPE_REHAB] = "Rehabilitasi Medik";
$poliTipe[POLI_TIPE_IGD] = "Gawat Darurat";

//jenis pasien
DEFINE("TIPE_PASIEN_ASKES","1");
DEFINE("TIPE_PASIEN_UMUM","2");
DEFINE("TIPE_PASIEN_JKN","5");
DEFINE("TIPE_PASIEN_IKS","7");
DEFINE("TIPE_PASIEN_PROGRAM","8");
DEFINE("TIPE_PASIEN_ASURANSI","10");
DEFINE("TIPE_PASIEN_TIDAK_MEMBAYAR","15");
DEFINE("TIPE_PASIEN_JAMKESMAS","16");
DEFINE("TIPE_PASIEN_JAMKESDA","18");
DEFINE("TIPE_PASIEN_SKTM","19");                           
DEFINE("TIPE_PASIEN_FASILITAS","20");
DEFINE("TIPE_PASIEN_JKN_FASILITAS","21");
DEFINE("TIPE_PASIEN_PKMS_SILVER","22");
DEFINE("TIPE_PASIEN_PKMS_GOLD","23");
DEFINE("TIPE_PASIEN_JASA_RAHARJA","24");
DEFINE("TIPE_PASIEN_PAKET","25");
DEFINE("TIPE_PASIEN_JKN_JASA_RAHARJA","26");

?>
