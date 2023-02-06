<?php
//error_reporting(0);
// Library
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "encrypt.php");
//  require_once($LIB."expAJAX.php");
require_once($LIB . "tampilan.php");

// Inisialisasi Lib
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$enc = new textEncrypt();
$userData = $auth->GetUserData();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();
$tglSekarang = date("d-m-Y");
$now = date("Y-m-d H:i:s");
$depLowest = $auth->GetDepLowest();

$id_rawat = $_GET['id_rawat'];

$sql = "SELECT rawat_id, permintaan_konsultasi from klinik.klinik_perawatan 
        where id_reg = ( 
            select reg_utama from klinik.klinik_registrasi a 
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg 
            where b.rawat_id = '$id_rawat'
            )";
$rawat_utama = $dtaccess->Fetch($sql);

$send = [];

$sql = "SELECT permintaan_konsultasi, jawaban_konsultasi from klinik.klinik_perawatan where rawat_id = '$id_rawat'";
$dataKonsul = $dtaccess->Fetch($sql);

$dataKonsul['permintaan_konsultasi'] = ($rawat_utama['rawat_id']) ? $rawat_utama['permintaan_konsultasi'] : $dataKonsul['permintaan_konsultasi'];

$permintaan = ($dataKonsul['permintaan_konsultasi']) ? unserialize($dataKonsul['permintaan_konsultasi']) : array() ;
$jawaban = ($dataKonsul['jawaban_konsultasi']) ? unserialize($dataKonsul['jawaban_konsultasi']) : array() ;

function getDokterNama($usr_id){
    global $dtaccess;
    $sql = "SELECT usr_name from global.global_auth_user where usr_id = '$usr_id'";
    $dataDokter = $dtaccess->Fetch($sql);

    return $dataDokter['usr_name'];

}

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$lokasi = $ROOT . "/gambar/img_cfg";

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

if ($konfigurasi["dep_logo"] != "n") {
    $fotoName = $lokasi . "/" . $konfigurasi["dep_logo"];
} elseif ($konfigurasi["dep_logo"] == "n") {
    $fotoName = $lokasi . "/default.jpg";
} else {
    $fotoName = $lokasi . "/default.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">
<style>
    .regards {
        position: relative;
        float: right;
        padding-right: 20px;
        text-align: center;
    }

    body {
        margin: 0;
        /* overflow: ; */
        font-family: Arial, Helvetica, sans-serif;
        height: 800px;
        /* width: 800px; 
    white-space: nowrap;*/
        font-size: 12px;
    }

    @page {
        size: auto;
        /* auto is the initial value */
        margin: 0cm;

    }

    .block {
        display: inline-block;
        padding: 1px;
    }

    img.logo {
        width: 2cm;
    }

    table {
        font-size: 12px;
        width: 100% !important;
        margin-top: 0px;
    }

    div.x_content {
        width: 100%;
        display: block;
    }

    div.pilihan {
        display: inline-block;


        height: 20px;

    }

    div.pilihan label {
        word-wrap: break-word;
        max-width: 150px;
    }

    div.head {
        display: block;
        width: 100%;
        font-weight: bold;
        margin-bottom: 3px;
        margin-top: 3px;
    }

    input.pilihan {
        width: 100%;
    }

    div.small,
    div.medium,
    div.mediumm {
        position: relative;
        min-height: 1px;
        margin-right: -2px;
        margin-left: -2px;
        display: inline-block;
        height: 14px;
    }

    div.small {
        width: 9%;

    }

    div.medium {
        width: 27%;

    }

    div.mediumm {
        width: 36%;
    }

    div.bottom {
        border-bottom: 1px solid black;
    }

    div.mask,
    div.maskr {
        width: 50%;
        position: absolute;
        bottom: -1px;
        background: white;
    }

    div.mask {
        left: -1px;
        border-right: 1px solid black;
    }

    div.maskr {
        right: -1px;
        border-left: 1px solid black;
    }

    div.selection {
        border: 1px solid black;
        width: 20px;
        height: 20px;
        position: relative;
        border-radius: 100%;
    }

    div.selection p {
        position: absolute;
        top: -8px;
        left: 7px;
    }

    div.col-md-3 {
        display: inline-block;
        margin-right: 10px;
        min-width: 120px;
    }

    @media print {
        hr.break {
            page-break-before: always;
        }
    }


    /* Custom CSS */
    table.list,
    table.list tr td {
        border-collapse: collapse;
        font-size: 8px;
    }

    table.list tr td {
        padding: 10px;
        vertical-align: top;
    }

    hr {
        border: none;
        border-bottom: .5px solid black;
    }

    textarea.content,
    textarea {
        border: none;
        outline: none;
        font-family: 'Times New Roman', Times, serif;
        font-size: 10px;
        width: 90%;
        overflow: hidden;
        resize: none;
    }

    table td {
        vertical-align: top;
    }

    div.header {
        position: absolute;
        right: 20px;
    }

    /* HIDE RADIO */
    [name=nyeri_emot] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* IMAGE STYLES */
    [name=nyeri_emot]+img {
        cursor: pointer;
    }

    /* CHECKED STYLES */
    [name=nyeri_emot]:checked+img {
        outline: 4px solid #4B0082;
    }

    .mt2 {
        margin-top: 20px;
    }

    /* Bootstrap 3 CSS */
    .container {
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    .form-control,
    input {
        border: none;
        border-bottom: 1px dotted black;
    }

    .row {
        margin-right: -15px;
        margin-left: -15px;
        vertical-align: top;
    }

    .clearfix:before,
    .clearfix:after,
    .dl-horizontal dd:before,
    .dl-horizontal dd:after,
    .container:before,
    .container:after,
    .container-fluid:before,
    .container-fluid:after,
    .row:before,
    .row:after,
    .form-horizontal .form-group:before,
    .form-horizontal .form-group:after,
    .btn-toolbar:before,
    .btn-toolbar:after,
    .btn-group-vertical>.btn-group:before,
    .btn-group-vertical>.btn-group:after,
    .nav:before,
    .nav:after,
    .navbar:before,
    .navbar:after,
    .navbar-header:before,
    .navbar-header:after,
    .navbar-collapse:before,
    .navbar-collapse:after,
    .pager:before,
    .pager:after,
    .panel-body:before,
    .panel-body:after,
    .modal-header:before,
    .modal-header:after,
    .modal-footer:before,
    .modal-footer:after {
        display: table;
        content: " ";
    }

    .clearfix:after,
    .dl-horizontal dd:after,
    .container:after,
    .container-fluid:after,
    .row:after,
    .form-horizontal .form-group:after,
    .btn-toolbar:after,
    .btn-group-vertical>.btn-group:after,
    .nav:after,
    .navbar:after,
    .navbar-header:after,
    .navbar-collapse:after,
    .pager:after,
    .panel-body:after,
    .modal-header:after,
    .modal-footer:after {
        clear: both;
    }

    .col-md-1 {
        display: inline-block;
        width: 7.333%;
    }

    .col-md-2 {
        display: inline-block;
        width: 15.666%;
    }

    .col-md-3 {
        display: inline-block;
        width: 24%;
    }

    .col-md-4 {
        display: inline-block;
        vertical-align: top;
        width: 32.333%;
    }

    .col-md-5 {
        display: inline-block;
        width: 40.666%;
    }

    .col-md-6 {
        display: inline-block;
        width: 49%;
    }

    .col-md-7 {
        display: inline-block;
        width: 57.333%;
    }

    .col-md-8 {
        display: inline-block;
        width: 65.666%;
    }

    .col-md-9 {
        display: inline-block;
        width: 74%;
    }

    .col-md-10 {
        display: inline-block;
        width: 82.333%;
    }

    .col-md-11 {
        display: inline-block;
        width: 90.666%;
    }

    .col-md-12 {
        display: inline-block;
        width: 99%;
    }

    hr {
        border: none;
        border-bottom: 5px solid black;
    }

    .tagline {
        padding: 3px;
        background-color: black;
        color: white;
        font-weight: bold;
        margin-top: 10px;
        text-align: center;
    }

    .table.table-bordered {
        border-collapse: collapse;

    }

    .table.table-bordered tr td {
        border-collapse: collapse;

    }

    hr {
        border: none;
        border-bottom: 5px solid black;
    }

    .tagline {
        padding: 3px;
        background-color: orange !important;
        color: white;
        font-weight: bold;
        margin-top: 10px;
        text-align: center;
        -webkit-print-color-adjust: exact !important;
    }

    @page {
        margin-top: 20px;
    }

    @media print {
        .break {
            padding-top: 50px;
            page-break-after: always;
        }

        .nomor {
            position: fixed;
            top: 20px;
            right: 40px;
        }
    }

    table tr td {
        vertical-align: top;
    }

    table.border {
        border-collapse: collapse;
    }

    table.border tr td {
        padding: 5px;
    }

    .inlined {
        display: inline-block;
    }

    .long80 {
        width: 90%;
    }

    .bordered {
        border: 1px solid black;
    }

    div.tittle {
        padding: 10px;
        border-bottom: 1px solid black;
        margin: 0 0 20px 0;
    }

    .left-tittle {
        padding: 10px;
    }

    form {
        padding-left: 10px;
        padding-right: 10px;
    }
</style>

<body>

    <div class="wrapper" style="width: 19cm;margin : auto">
        <div style="margin-top: 20px;">
            <table style="margin-bottom: 10px;">
                <tr>
                    <td width="10%">
                        <center>
                            <img class="logo"> src="<?php echo $fotoName; ?>">
                        </center>
                    </td>
                    <td style="padding-left: 15px;">
                        <p style="margin-top: 0px;margin-bottom: 0px;letter-spacing: 3px">RUMAH SAKIT IBU & ANAK</p>
                        <h1 style="margin-bottom: 0px;margin-top: 0px;font-size: 35px;color:green"><?php echo $konfigurasi['dep_nama']; ?></h1>
                        <p style="margin-top: 0px;margin-bottom: 0px;"><?php echo $konfigurasi['dep_kop_surat_1'] . " Telp. " . $konfigurasi['dep_kop_surat_2']; ?></p>
                        <p style="margin-top: 3px;margin-bottom: 0px;"><?php echo $konfigurasi['dep_kop_surat_3']; ?> <?php echo strtoupper($konfigurasi['dep_kota']); ?></p>
                    </td>
                    <td width="1%">
                        <div class="nomor"></div>
                    </td>
                </tr>

            </table>

            <div class="tagline">KEPERCAYAAN ANDA AMANAH KAMI. IBU SEHAT. ANAK SEHAT</div>
        </div>
        <div>
            <center>
                <h2>KONSULTASI OPERASI</h2>
            </center>
        </div>
        <div style="margin-top: 20px;">
            <div class="row">
                <div class="col-md-12">
                    <div title="Konsultasi Operasi" style="padding:5px">
                        <div class="form-horizontal form-label-left">
                            <input type="hidden" class="id_rawat" name="id_rawat" value="<?php echo $_GET['id_rawat']; ?>">
                            <form id="perimtaanOP" class="form-horizontal form-label-left bordered">

                                <div class="col-md-12 tittle">
                                    <center>
                                        <h2><b>PERMINTAAN KONSULTASI</b></h2>
                                    </center>
                                </div>

                                <div class="col-md-12 ">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-1 left-tittle">Kepada </label>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <input type="text" class="form-control" name="kepada" value="<?=$permintaan['kepada']?>">
                                        </div>
                                        <div class="form-group">&nbsp;</div>
                                        <div class="form-group">
                                            <label class="col-md-2 left-tittle" style="text-align: right">
                                                Yth / TS
                                            </label>
                                            <div class="col-md-8">
                                                <label><?=getDokterNama($permintaan['kepada_dokter'])?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-1 left-tittle" style="text-align: right">
                                                di
                                            </label>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="tempat" value="<?=$permintaan['tempat']?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="col-md-5 left-tittle" style="text-align: right">Tgl / Jam</label>
                                        <div class="col-md-3 input-group date" id="tgl">
                                            <input type="text" class="form-control" name="waktu_tanggal" value="<?=$permintaan['waktu_tanggal']?>">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                        <script type="text/javascript">
                                            $(function() {
                                                $('#tgl').datetimepicker({
                                                    format: 'YYYY-MM-DD HH:mm:ss'
                                                });
                                            });
                                        </script>
                                    </div>

                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Ringkasan singkat pemeriksaan</label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="ringkasan"><?=$permintaan['ringkasan']?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Konsultasi</label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="konsultasi"><?=$permintaan['konsultasi']?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="col-md-6">
                                        <center>
                                            <b>Mengetahui Keluarga</b><br>
                                            <?php 
                                            $ttdkeluarga = "../gambar/asset_ttd/".$permintaan['ttd_keluarga'].".jpg";
                                            if(file_exists($ttdkeluarga)) { ?>
                                                <img src="<?=$ttdkeluarga?>" style="height: 100px"><br>
                                            <?php } else { ?>
                                                <br><br><br><br><br>
                                            <?php } ?>
                                            <label>(<?=$permintaan['nama_ttd_keluarga']?>)</label>
                                        </center>
                                    </div>
                                    <div class="col-md-6">
                                        <center>
                                            <b>Paraf Dokter</b><br>
                                            <?php 
                                            $ttd_dokter = "../gambar/asset_ttd/".$permintaan['ttd_dokter'].".jpg";
                                            if(file_exists($ttd_dokter)) { ?>
                                                <img src="<?=$ttd_dokter?>" style="height: 100px"><br>
                                            <?php } else { ?>
                                                <br><br><br><br><br>
                                            <?php } ?>
                                            <label>(<?=$permintaan['nama_ttd_dokter']?>)</label>
                                        </center>
                                    </div>
                                </div>

                            </form>
                            <form id="jawabanOP" class="form-horizontal form-label-left bordered">

                                <div class="col-md-12 tittle">
                                    <center>
                                        <h2><b>JAWABAN KONSULTASI</b></h2>
                                    </center>
                                </div>

                                <div class="col-md-12 ">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-1 left-tittle">Kepada </label>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <input type="text" class="form-control" name="kepada" value="<?=$jawaban['tempat']?>">
                                        </div>
                                        <div class="form-group">&nbsp;</div>
                                        <div class="form-group">
                                            <label class="col-md-1 left-tittle" style="text-align: right">
                                                Yth / TS
                                            </label>
                                            <div class="col-md-5">
                                               <label><?=getDokterNama($jawaban['kepada_dokter'])?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-1 left-tittle" style="text-align: right">
                                                di
                                            </label>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="tempat" value="<?=$jawaban['tempat']?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="col-md-5 left-tittle" style="text-align: right">Tgl / Jam</label>
                                        <div class="col-md-3 input-group date" id="tgl2">
                                            <input type="text" class="form-control" name="waktu_tanggal" value="<?=$jawaban['waktu_tanggal']?>">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                        <script type="text/javascript">
                                            $(function() {
                                                $('#tgl2').datetimepicker({
                                                    format: 'YYYY-MM-DD HH:mm:ss'
                                                });
                                            });
                                        </script>
                                    </div>

                                </div>

                                <div class="col-md-12">&nbsp;</div>

                                <div class="col-md-12">
                                    <label class="col-md-2 left-tittle">Pasien telah kami periksa pada </label>
                                    <div class="col-md-2 input-group date" id="tgl3">
                                        <input type="text" class="form-control" name="waktu_tanggal_pemeriksaan" value="<?=$jawaban['waktu_tanggal_pemeriksaan']?>">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    <script type="text/javascript">
                                        $(function() {
                                            $('#tgl3').datetimepicker({
                                                format: 'YYYY-MM-DD HH:mm:ss'
                                            });
                                        });
                                    </script>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Hasil Pemeriksaan</label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="hasil_pemeriksaan"><?=$jawaban['hasil_pemeriksaan']?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Kesumpulan</label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="kesimpulan"><?=$jawaban['kesimpulan']?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Advis/Tindakan</label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="advis"><?=$jawaban['advis']?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="col-md-6">

                                    </div>
                                    <div class="col-md-6">
                                        <center>
                                            <b>Paraf Dokter</b><br>
                                            <?php 
                                            $ttd_dokter = "../gambar/asset_ttd/".$jawaban['ttd_dokter'].".jpg";
                                            if(file_exists($ttd_dokter)) { ?>
                                                <img src="<?=$ttd_dokter?>" style="height: 100px"><br>
                                            <?php } else { ?>
                                                <br><br><br><br><br>
                                            <?php } ?>
                                            <label>(<?=$jawaban['nama_ttd_dokter']?>)</label>
                                        </center>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</body>

</html>