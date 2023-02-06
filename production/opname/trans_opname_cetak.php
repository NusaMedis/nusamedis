<?php
  require_once("../penghubung.inc.php");
  require_once($ROOT."lib/login.php");
  require_once($ROOT."lib/datamodel.php");
  require_once($ROOT."lib/dateLib.php");
  require_once($ROOT."lib/currency.php");
  require_once($ROOT."lib/tampilan.php");
  require_once($ROOT."lib/encrypt.php");

  $dtaccess = new DataAccess();
  $enc = new TextEncrypt();     
  $auth = new CAuth();
  $userData = $auth->GetUserData();     
  $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $depLowest = $auth->GetDepLowest();

  $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
  $konfigurasi = $dtaccess->Fetch($sql);

  $lokasi = $ROOT."/gambar/img_cfg";
  $fotoName = ($konfigurasi["dep_logo"]) ? $lokasi."/".$konfigurasi["dep_logo"] : $lokasi."/default.jpg";

  $sql = "SELECT a.opname_id, a.opname_tanggal, c.gudang_nama, d.penerimaan_periode_nama, e.item_nama, b.stok_item_keterangan, b.stok_item_sebelumnya, b.stok_item_jumlah, b.stok_item_saldo FROM logistik.logistik_opname a LEFT JOIN logistik.logistik_stok_item b ON a.opname_id = b.id_opname LEFT JOIN logistik.logistik_gudang c ON c.gudang_id = a.id_gudang LEFT JOIN logistik.logistik_penerimaan_periode d ON d.penerimaan_periode_id = a.id_periode LEFT JOIN logistik.logistik_item e ON e.item_id = b.id_item WHERE a.opname_id = ".QuoteValue(DPE_CHAR, $_GET['id'])." ORDER BY b.stok_item_create ASC";
  $data = $dtaccess->FetchAll($sql);
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Cetak Stok Opname</title>
  </head>
  <body onload="window.print(); ">
    <table style="width: 18cm; border-collapse: collapse;">
      <tr>
        <td align="center" style="width:10%;"><img src="<?php echo $fotoName ;?>" height="60"></td>
        <td align="center" bgcolor="#CCCCCC" id="judul" style="width:90%; font-size:larger;"> 
          <span><strong><?php echo $konfigurasi["dep_nama"]?></strong></span><br>
          <span style="font-size: 15px"><?php echo $konfigurasi["dep_kop_surat_1"]?></span>
          <span style="font-size: 15px"><?php echo $konfigurasi["dep_kop_surat_2"]?></span>
        </td>
      </tr> 
      <tr>
        <td style="font-size: 15px"><?php echo $konfigurasi["dep_kop_surat_2"]?></td>
      </tr>
    </table>
    <table style="width: 18cm;">
      <tr>
        <td width="8%"><font size="2">Periode</font></td>
        <td width="5%" align="center"><font size="2">:</font></td>
        <td width="17%"><font size="2"><?=$data[0]['penerimaan_periode_nama']?></font></td>
        <td width="70%">&nbsp;</td>
      </tr>
      <tr>
        <td><font size="2">Tanggal</font></td>
        <td align="center"><font size="2">:</font></td>
        <td><font size="2"><?=date_db($data[0]['opname_tanggal'])?></font></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><font size="2">Waktu Cetak</font></td>
        <td align="center"><font size="2">:</font></td>
        <td><font size="2"><?=date('d-m-Y H:i:s')?></font></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><font size="2">Gudang</font></td>
        <td align="center"><font size="2">:</font></td>
        <td><font size="2"><?=$data[0]['gudang_nama']?></font></td>
        <td>&nbsp;</td>
      </tr>
    </table>
    <table style="width: 18cm;">
      <tr>
        <td><hr></td>
      </tr>
      <tr>
        <td align="center"><font size="2"><b>CETAK STOK OPNAME</b></font></td>
      </tr>
      <tr>
        <td><hr></td>
      </tr>
    </table>
    <table style="width: 18cm; border-collapse: collapse;" border="1">
      <thead>
        <tr>
          <th>No.</th>
          <th>Tanggal</th>
          <th>Nama Item</th>
          <th>Keterangan</th>
          <th>Saldo Awal</th>
          <th>Masuk</th>
          <th>Keluar</th>
          <th>Saldo Akhir</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $key => $value): ?>
          <tr>
            <td><?=$key+1?></td>
            <td><?=date_db($value['opname_tanggal'])?></td>
            <td><?=$value['item_nama']?></td>
            <td><?=$value['stok_item_keterangan']?></td>
            <td><?=currency_format($value['stok_item_sebelumnya'])?></td>
            <td><?=($value['stok_item_jumlah']) >= 0 ? currency_format($value['stok_item_jumlah']) : '';?></td>
            <td><?=($value['stok_item_jumlah']) < 0 ? currency_format($value['stok_item_jumlah']) : '';?></td>
            <td><?=currency_format($value['stok_item_saldo'])?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </body>
</html>