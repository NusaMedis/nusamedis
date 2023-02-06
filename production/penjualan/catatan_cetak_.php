<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "tampilan.php");


$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$err_code = 0;
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();

//AUTHENTIFIKASI

$_x_mode = "New";
$thisPage = "kasir_view.php";
$atMinum = [];
$atPakai = [];
$dosis = [];
$jamPakai = [];

if ($_GET["id"]) {
  $sql = "SELECT rawat_cppt_data, id_rawat from klinik.klinik_perawatan_cppt where rawat_cppt_id = ".QuoteValue(DPE_CHAR, $_GET["id"]);
  $CPPT = $dtaccess->Fetch($sql);

  $_GET["id"] = ($CPPT['id_rawat']) ? $CPPT['id_rawat'] : $_GET["id"] ;

  $sql = " SELECT a.rawat_id, a.rawat_terapi, b.reg_tanggal, c.usr_name as dokter,  d.cust_usr_kode, d.cust_usr_alamat, 
  d.cust_usr_tanggal_lahir, d.cust_usr_jenis_kelamin, d.cust_usr_nama, f.poli_nama, c.usr_id, f.form_asmed, g.jenis_nama
            from klinik.klinik_perawatan a
            left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
            left join global.global_auth_user c on b.id_dokter = c.usr_id 
            left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
            left join global.global_auth_poli f on f.poli_id = b.id_poli
            left join global.global_jenis_pasien g on b.reg_jenis_pasien = g.jenis_id
            where a.rawat_id = " . QuoteValue(DPE_CHAR, $_GET["id"]);
  //echo $sql;
  $dataTable = $dtaccess->Fetch($sql);

  $rawat_id = $dataTable['rawat_id'];

$sql = "SELECT terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
$sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
$sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
$sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
$sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
$sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
$sql .= " WHERE id_rawat = '$rawat_id'";
$sql .= " AND id_rawat_terapi_racikan is null ";
$terapi = $dtaccess->fetchAll($sql);

$sql = "SELECT * from klinik.klinik_perawatan_terapi_racikan a
left join apotik.apotik_jenis_racikan f on f.jenis_racikan_id = a.id_jenis_racikan
LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai
LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai
LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum
LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.id_petunjuk
 where id_rawat = '$rawat_id'";
$dataRacikan = $dtaccess->FetchAll($sql);

}

$sql = "SELECT petunjuk_id, petunjuk_nama from apotik.apotik_obat_petunjuk";
$dataAtMinum = $dtaccess->FetchAll($sql);

$sql = "SELECT aturan_pakai_id, aturan_pakai_nama from apotik.apotik_aturan_pakai";
$dataAtPakai = $dtaccess->FetchAll($sql);

$sql = "SELECT aturan_minum_id, aturan_minum_nama from apotik.apotik_aturan_minum";
$dataDosis = $dtaccess->FetchAll($sql);

$sql = "SELECT jam_aturan_pakai_id, jam_aturan_pakai_nama from apotik.apotik_jam_aturan_pakai";
$dataJamPakai = $dtaccess->FetchAll($sql);

for($i=0; $i < count($dataAtMinum); $i++){
  $atMinum[$dataAtMinum[$i]['petunjuk_id']] = $dataAtMinum[$i]['petunjuk_nama'];
}

for($i=0; $i < count($dataAtPakai); $i++){
  $atPakai[$dataAtPakai[$i]['aturan_pakai_id']] = $dataAtPakai[$i]['aturan_pakai_nama'];
}

for($i=0; $i < count($dataDosis); $i++){
  $dosis[$dataDosis[$i]['aturan_minum_id']] = $dataDosis[$i]['aturan_minum_nama'];
}

for($i=0; $i < count($dataJamPakai); $i++){
  $jamPakai[$dataJamPakai[$i]['jam_aturan_pakai_id']] = $dataJamPakai[$i]['jam_aturan_pakai_nama'];
}

$dataCPPT = ($CPPT) ? unserialize($CPPT['rawat_cppt_data']) : [] ;

$terapiCPPTEx = $dataCPPT['terapi'];
$terapiCPPT = [];

if($dataTable['form_asmed'] != 'anak'){
    for($i=0; $i < count($terapiCPPTEx); $i++){
      $temp['item_nama'] = $terapiCPPTEx[$i]['nama_obat'];
      $temp['penjualan_detail_jumlah'] = $terapiCPPTEx[$i]['jumlah'];
      $temp['petunjuk_nama'] = $atMinum[$terapiCPPTEx[$i]['atMinum']];
      $temp['aturan_minum_nama'] = $dosis[$terapiCPPTEx[$i]['dosis']];
      $temp['aturan_pakai_nama'] = $atPakai[$terapiCPPTEx[$i]['atPakai']];
      $temp['jam_aturan_pakai_nama'] = $jamPakai[$terapiCPPTEx[$i]['jamPakai']];

      $terapiCPPT[] = $temp;
    }
}

$terapiCPPT = ($dataTable['form_asmed'] == 'anak') ? [] : $terapiCPPT ;

$terapi = ($terapi) ? $terapi : $terapiCPPT;

$dataTable['rawat_terapi'] = ($dataTable['rawat_terapi']) ? $dataTable['rawat_terapi'] : $dataCPPT['terapiApotik'] ;

$dataObat = explode("+", $dataTable['rawat_terapi']);

$lokasi = $ROOT . "/gambar/img_cfg";

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

//TTD Dokter
$dokter = $dataTable['usr_id'];
$ttd = '../gambar/asset_ttd/' . $dokter . '.jpg';

//cari detail racikan

// var_dump($dataDetRacikan);
?>

<?php // echo $view->RenderBody("inventori_prn.css",true);  
?>
<html>

<head>
    <title>Cetak Pengeluaran Obat Apotik</title>
    <script language="javascript" type="text/javascript">
        window.print();
        // window.close();
    </script>
    <style>
        @media print {
            #tableprint {
                display: none;
            }
        }
    </style>
    <style type="text/css">
        body {
            font-family: Arial, Verdana, Helvetica, sans-serif;
            margin: 0px;
            font-size: 10px;
        }

        .tableisi {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 10px;
            border: none #000000 0px;
            padding: 4px;
            border-collapse: collapse;
        }


        .tableisi td {
            border: solid #000000 1px;
            padding: 4px;
        }

        .tablenota {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 10px;
            border: solid #000000 1px;
            padding: 4px;
            border-collapse: collapse;
        }

        .tablenota .judul {
            border: solid #000000 1px;
            padding: 4px;
        }

        .tablenota .isi {
            border-right: solid black 1px;
            padding: 4px;
        }

        .ttd {
            height: 50px;
        }

        .judul {
            font-size: 14px;
            font-weight: bolder;
            border-collapse: collapse;
        }


        .judul1 {
            font-size: 12px;
            font-weight: bolder;
        }

        .judul2 {
            font-size: 14px;
            font-weight: bolder;
        }

        .judul3 {
            font-size: 12px;
            font-weight: normal;
        }

        .judul4 {
            font-size: 11px;
            font-weight: bold;
            background-color: #CCCCCC;
            text-align: center;
        }

        .judul5 {
            font-size: 11px;
            font-weight: bold;
            background-color: #040404;
            text-align: center;
            color: #FFFFFF;
        }

        table{
            font-size: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        tr.bottom td{
            border-bottom: 1px solid black;
        }
        tr.top{
            border-bottom: 1px solid black;
            border-top: 1px solid black;
        }
    </style>
    </style>
</head>

<body style="width: 10cm;">
    <table align="center" width="100%" border="0" style="font-size:11px;">
        <tr class="bottom">
            <td align="left" width="5%"><img src="<?php echo $fotoName; ?>" height="75"> </td>
            <td align="left" id="judul" rowspan="1">
                <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"] ?></strong><br></span>
                <span class="judul3">
                    <?php echo $konfigurasi["dep_kop_surat_1"] ?></span><br>
                <span class="judul4">
                    <?php echo $konfigurasi["dep_kop_surat_2"] ?></span></td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>Nama Pasien</td>
                        <td>:</td>
                        <td><?php echo $dataTable['cust_usr_nama'] ?></td>
                    </tr>
                    <tr>
                        <td>No RM</td>
                        <td>:</td>
                        <td><?php echo $dataTable['cust_usr_kode']; ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Lahir</td>
                        <td>:</td>
                        <td><?php echo date_db($dataTable['cust_usr_tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <td>Alamat Pasien</td>
                        <td>:</td>
                        <td><?php echo $dataTable['cust_usr_alamat']; ?></td>
                    </tr>
                    <tr>
                        <td>Alergi</td>
                        <td>:</td>
                        <td><?php echo $dataTable['cust_usr_alergi']; ?></td>
                    </tr>
                </table>

            </td>
            <td>
                <table>
                    <tr>
                        <td>Nama Dokter</td>
                        <td>:</td>
                        <td><?php echo $dataTable['dokter'] ?></td>
                    </tr>
                    
                    <tr>
                        <td>Asal Resep</td>
                        <td>:</td>
                        <td><?php echo $dataTable['poli_nama']; ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>:</td>
                        <?php if ($dataTable['cust_usr_jenis_kelamin'] == 'L') { ?>
                            <td>Laki-Laki</td>
                        <?php } elseif ($dataTable['cust_usr_jenis_kelamin'] == 'P') { ?>
                            <td>Perempuan</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td>Cara Bayar</td>
                        <td>:</td>
                        <td><?php echo $dataTable['jenis_nama']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table>
        <tr class="top">
            <td width="100%"><?php echo date('d-m-Y / H:i:s'); ?></td>
        </tr>
    </table>
    <table>
        
        <?php for ($i = 0; $i < count($dataObat); $i++) { 
            if($dataObat[$i] != ""){

                preg_match('#\((.*?)\)#', $dataObat[$i], $match);
                
                $nomor = $match[1];
                $ingg = str_replace($match[0], "", $dataObat[$i]);

                $ingridients = explode(",", $ingg);
                $nomor = explode(",", $nomor);
            ?>
            <tr>
                <td width="80%"><?php echo "R/ "?> </td>
                <td></td>
            </tr>
            <?php for($a = 0; $a < count($ingridients);$a++) {?>
            <tr>
                <td width="80%" style="padding: 0 0 0 8px;"><?=strtoupper($ingridients[$a])?> </td>
                <td></td>
            </tr>
            <?php }?>
            <tr>
                <td width="80%" style="padding: 0 0 0 8px;"><?=$nomor[0]?> </td>
                <td></td>
            </tr>
            <tr class="bottom">
                <td width="80%" style="padding: 10px 0 20px 8px;"><?=$nomor[1]?> </td>
                <td></td>
            </tr>
        <?php }
    } ?>
    <?php for ($i = 0; $i < count($terapi); $i++) { ?>
        <tr>
                <td width="80%"><?php echo "R/ "?><?=$terapi[$i]['item_nama']?> </td>
                <td width="20%"><?php echo "No ".currency_format($terapi[$i]['penjualan_detail_jumlah']) ?></td>
            </tr>
        <tr>
                <td width="80%">&nbsp;</td>
                <td width="20%"></td>
        </tr>
        <tr class="bottom">
            <td><?php echo $terapi[$i]['petunjuk_nama']." ".$terapi[$i]['aturan_minum_nama']."<br>".$terapi[$i]['aturan_pakai_nama']."<br>".$terapi[$i]['jam_aturan_pakai_nama'] ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php for ($i = 0; $i < count($dataRacikan); $i++) { ?>
         <tr>
                <td width="80%"><?php echo "R/ "?> </td>
                <td width="20%"><?php echo "No ".currency_format($dataRacikan[$i]['rawat_terapi_racikan_jumlah']) ?></td>
        </tr>
        <?php
        $rawat_racikan = $dataRacikan[$i]['rawat_terapi_racikan_id'];
        $sql = "SELECT terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
        $sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
        $sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
        $sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
        $sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
        $sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
        $sql .= " WHERE id_rawat_terapi_racikan = '$rawat_racikan'";
        $terapiRacikitem = $dtaccess->fetchAll($sql);

        for($a = 0; $a < count($terapiRacikitem); $a++){
        ?>
        <tr>
                <td width="80%"><?=$terapiRacikitem[$a]['item_nama']?> </td>
                <td width="20%"></td>
        </tr>
        <?php }?>
        <tr>
                <td width="80%">&nbsp;</td>
                <td width="20%"></td>
        </tr>
        <tr>
                <td width="80%">m.f <?=$dataRacikan[$i]['satuan_nama']?> <?=$dataRacikan[$i]['jenis_racikan_nama']?></td>
                <td width="20%"></td>
        </tr>
        <tr>
                <td width="80%">&nbsp;</td>
                <td width="20%"></td>
        </tr>
        <tr class="bottom">
            <td><?php echo $dataRacikan[$i]['petunjuk_nama']." ".$dataRacikan[$i]['aturan_minum_nama']."<br>".$dataRacikan[$i]['aturan_pakai_nama']."<br>".$dataRacikan[$i]['jam_aturan_pakai_nama'] ?></td>
            <td></td>
        </tr>

    <?php } ?>

    </table>
    <table>
         <tr>
            <td width="70%">&nbsp;</td>
            <td><img src="<?php echo $ttd; ?>" height="100" valign="middle"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="center"><?php echo $dataTable['dokter'] ?></td>
        </tr>
    </table>
</body>

</html>