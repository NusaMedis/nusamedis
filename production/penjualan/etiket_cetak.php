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
/* if(!$auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)){
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */


$_x_mode = "New";
$thisPage = "kasir_view.php";

if ($_GET["id"]) {

    $sql = "select * from apotik.apotik_penjualan a 
    left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
    left join klinik.klinik_registrasi c on c.reg_id = a.id_reg 
    left join global.global_auth_poli d on d.poli_id = c.id_poli_asal 
    left join global.global_auth_user e on e.usr_id = c.id_dokter where penjualan_id = " . QuoteValue(DPE_CHAR, $_GET['id']);
    $dataPasien = $dtaccess->Fetch($sql);

    $sql = "select * from klinik.klinik_folio where fol_lunas = 'n' and fol_jenis = " . QuoteValue(DPE_CHAR, $_POST["fol_jenis"]) . " and id_reg = " . QuoteValue(DPE_CHAR, $_POST["id_reg"]);
    $dataFolio = $dtaccess->FetchAll($sql);

    $sql = "select * from apotik.apotik_penjualan_detail a 
    left join apotik.apotik_obat_petunjuk b on b.petunjuk_id = a.id_petunjuk
    left join apotik.apotik_aturan_minum d on a.id_aturan_minum = d.aturan_minum_id
    left join apotik.apotik_aturan_pakai e on a.id_aturan_pakai = e.aturan_pakai_id 
    left join apotik.apotik_jam_aturan_pakai f on a.id_jam_aturan_pakai = f.jam_aturan_pakai_id
    left join logistik.logistik_item c on c.item_id = a.id_item 
    left join logistik.logistik_item_satuan g on c.id_satuan_jual = g.satuan_id
    left join apotik.apotik_nama_racikan h on c.item_id = h.nama_racikan_id
    left join apotik.apotik_jenis_racikan i on h.nama_racikan_jenis = i.jenis_racikan_id
    where id_penjualan = " . QuoteValue(DPE_CHAR, $_GET['id']);
    $dataObat = $dtaccess->FetchAll($sql);
}

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
$dokter = $dataPasien['usr_id'];
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
                        <td><?php echo $dataPasien['cust_usr_nama'] ?></td>
                    </tr>
                    <tr>
                        <td>No RM</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['cust_usr_kode']; ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Lahir</td>
                        <td>:</td>
                        <td><?php echo date_db($dataPasien['cust_usr_tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <td>Alamat Pasien</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['cust_usr_alamat']; ?></td>
                    </tr>
                    <tr>
                        <td>Alergi</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['cust_usr_alergi']; ?></td>
                    </tr>
                </table>

            </td>
            <td>
                <table>
                    <tr>
                        <td>Nama Dokter</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['usr_name'] ?></td>
                    </tr>
                    <tr>
                        <td>No. Traksaksi</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['penjualan_nomor']; ?></td>
                    </tr>
                    <tr>
                        <td>Asal Resep</td>
                        <td>:</td>
                        <td><?php echo $dataPasien['poli_nama']; ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>:</td>
                        <?php if ($dataPasien['cust_usr_jenis_kelamin'] == 'L') { ?>
                            <td>Laki-Laki</td>
                        <?php } elseif ($dataPasien['cust_usr_jenis_kelamin'] == 'P') { ?>
                            <td>Perempuan</td>
                        <?php } ?>
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
            $isRacikan = $dataObat[$i]['item_racikan'];
            ?>
            <tr>
                <td width="80%"><?php echo "R/ "?><?=($isRacikan == 'n  ') ? $dataObat[$i]['item_nama'] : ""?> </td>
                <td width="20%"><?php echo "No ".currency_format($dataObat[$i]['penjualan_detail_jumlah']) ?></td>
            </tr>
            <!-- detail racikan -->
            <?php
            $sql = "select * from apotik.apotik_detail_racikan a
             where a.id_nama_racikan = " . QuoteValue(DPE_CHAR, $dataObat[$i]['id_item']);
            
            $dataDetRacikan = $dtaccess->FetchAll($sql); 
            $nomer = 0;
            
                for ($j = 0, $n = count($dataDetRacikan); $j < $n; $j++) { ?>
                    <tr>
                        
                        <td>  &nbsp;<? echo strtoupper($dataDetRacikan[$j]["item_nama"]) . ' &nbsp;[' . $dataDetRacikan[$j]["detail_racikan_jumlah"] . ']' ?> </td>
                        <td> </td>
                    </tr>
            <?php }
             ?>
         <tr>
                <td width="80%">&nbsp;</td>
                <td width="20%"></td>
        </tr>
        <?php
        if($isRacikan != 'n  ') {
        ?>
        <tr>
            <td>m.f <?=$dataObat[$i]['satuan_nama']?> <?=$dataObat[$i]['jenis_racikan_nama']?></td>
            <td></td>
        </tr> 
        <?php } ?>
        <tr class="bottom">
            <td><?php echo $dataObat[$i]['petunjuk_nama']." ".$dataObat[$i]['aturan_minum_nama']."<br>".$dataObat[$i]['aturan_pakai_nama']."<br>".$dataObat[$i]['jam_aturan_pakai_nama']."<br>".$dataObat[$i]['penjualan_detail_ket'] ?></td>
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
            <td align="center"><?php echo $dataPasien['usr_name'] ?></td>
        </tr>
    </table>
</body>

</html>