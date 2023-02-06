<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."currency.php");
  require_once($LIB."expAJAX.php");
  require_once($LIB."tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $err_code = 0;
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $theDep = $auth->GetNamaLogistik();  //Ambil Gudang yang aktif

  $kode = $_GET['kode'];
  $transaksi = $_GET['transaksi'];
  $idreg = $_GET['idreg'];
  $id_pembayaran = $_GET['id_pembayaran'];

  if ($_GET["transaksi"]) $penjualanId=$enc->Decode($_GET["transaksi"]);
  if ($_POST["penjualan_id"]) $penjualanId=$_POST["penjualan_id"];
  
  $sql = "select paket_id, paket_nama from apotik.apotik_paket order by paket_nama asc";
  $dataJenis = $dtaccess->FetchAll($sql);

  $sql = "SELECT * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR, $depId);
  $conf = $dtaccess->Fetch($sql);

  //Tampilkan data Paket
  if($_POST["id_paket"]) $sql_where[] = "a.id_paket = ".QuoteValue(DPE_CHAR,$_POST["id_paket"]);
  if($sql_where) $sql_where = implode(" and ",$sql_where);

  if ($_POST['btnSearch'] || $_POST['simpan']) {
    $sql = "select b.item_id,c.paket_nama, b.item_nama, a.jumlah_item, b.item_harga_jual, b.item_harga_beli, b.id_kategori, b.item_hpp
            from apotik.apotik_paket_item a
            left join logistik.logistik_item b on a.id_item = b.item_id
            left join apotik.apotik_paket c on a.id_paket = c.paket_id";
    $sql .= " where 1=1";
    if($sql_where) $sql .= " and ".$sql_where;
    $sql .= " order by b.item_nama asc";
    $rs = $dtaccess->Execute($sql);
    $dataTable = $dtaccess->FetchAll($rs);
  }

  if ($_POST["simpan"]) {
    $dateSekarang = date('Y-m-d H:i:s');


          
    for($i=0,$j=count($dataTable);$i<$j;$i++){
      $hargabeli = intval($dataTable[$i]['item_hpp']);
      $sql = "select margin_nilai from apotik.apotik_margin
               where id_grup_item = ".QuoteValue(DPE_CHAR, $dataTable[$i]['id_kategori'])."
               and is_aktif ='Y' and " . $hargabeli . " >= harga_min and " . $hargabeli .
          " <= harga_max  ";
      $rs = $dtaccess->Execute($sql);
      $margin = $dtaccess->Fetch($rs);
      $hargajual = intval(((100+$margin["margin_nilai"])/100)*$dataTable[$i]["item_hpp"]);

      $hmargin = $hargajual;

      $hppn = intval($hmargin * 1.1);

      $ppn = intval((intval($hargajual)*$_POST["penjualan_pakai".$i])*0.1);

      $hargapokok = intval($hargajual*$_POST["penjualan_pakai".$i]);

      $hargajual = intval($hargapokok + $ppn);

      $tuslag = 0;

      $tuslag = intval(intval($conf['conf_biaya_tuslag'] / 100 * $hppn) * $_POST["penjualan_pakai".$i]);

      $_POST["tuslag".$i] = ($_POST["tuslag".$i]) ? intval($_POST["tuslag".$i]) : intval($tuslag);
      
      $sql =" select id_batch from logistik.logistik_stok_batch_dep where id_item =".QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"])." and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
      $rs = $dtaccess->Execute($sql);
      $databatch = $dtaccess->Fetch($rs);

      if ($_POST["penjualan_pakai".$i]>0) {
        $dbTable = "apotik.apotik_penjualan_detail";
        $dbField[0]  = "penjualan_detail_id";   // PK
        $dbField[1]  = "id_penjualan";
        $dbField[2]  = "id_item";
        $dbField[3]  = "penjualan_detail_harga_jual";
        $dbField[4]  = "penjualan_detail_jumlah";
        $dbField[5]  = "penjualan_detail_total";
        $dbField[6]  = "penjualan_detail_flag";
        $dbField[7]  = "penjualan_detail_create";
        $dbField[8]  = "id_petunjuk";
        $dbField[9]  = "id_dep";
        $dbField[10]  = "penjualan_detail_sisa";
        $dbField[11]  = "id_batch";
        $dbField[12]  = "penjualan_detail_tuslag";
        $dbField[13]  = "item_nama";
        $dbField[14]  = "penjualan_detail_ppn";
        $dbField[15]  = "penjualan_detail_harga_pokok";
        $dbField[16]  = "penjualan_detail_harga_beli";
        
        $penjualanDetailId = $dtaccess->GetTransID();

        $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
        $dbValue[2] = QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"]);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($hppn));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_pakai".$i]));
        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency( $hargajual + $_POST["tuslag".$i] ) );
        $dbValue[6] = QuoteValue(DPE_CHAR,'y');
        $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
        $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
        $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
        $dbValue[11] = QuoteValue(DPE_CHAR,$databatch["id_batch"]);
        $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["tuslag".$i]));
        $dbValue[13] = QuoteValue(DPE_CHAR, strtolower($dataTable[$i]["item_nama"]));
        $dbValue[14] = QuoteValue(DPE_NUMERIC, StripCurrency($ppn));
        $dbValue[15] = QuoteValue(DPE_NUMERIC, StripCurrency($hargapokok));
        $dbValue[16] = QuoteValue(DPE_NUMERIC, StripCurrency($dataTable[$i]['item_hpp']*$_POST["penjualan_pakai".$i]));
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        if ($_POST["btn_edit"]) $dtmodel->Update() or die("insert  error");
        else $dtmodel->Insert() or die("insert  error");

        unset($dbField);
        unset($dbValue);

        unset($_POST["btnSave"]);
        unset($_POST["obat_id"]);
        unset($_POST["obat_kode"]);
        unset($_POST["obat_nama"]);
        unset($_POST["txtTuslag"]);
        unset($_POST["txtHargaSatuan"]);
        unset($_POST["txtJumlah"]);
        unset($_POST["txtHargaTotal"]);
        unset($_POST["txtDibayar"]);
        unset($_POST["txtBalik"]);
        unset($_POST["txtBack"]);
        unset($_POST["txtDiskon"]);

        $tutup=1;
      }
    }
        $sql = "SELECT sum(penjualan_detail_total) as total from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR, $enc->Decode($transaksi));
        $total = $dtaccess->Fetch($sql);

        $sql = "SELECT penjualan_nomor from apotik.apotik_penjualan where penjualan_id = ".QuoteValue(DPE_CHAR, $enc->Decode($transaksi));
        $nojual = $dtaccess->Fetch($sql);

        $sql = "update apotik.apotik_penjualan set
                penjualan_total = '".$total["total"]."',
                penjualan_grandtotal= '".$total["total"]."',
                penjualan_bayar ='".$total["total"]."' where
                penjualan_id =".QuoteValue(DPE_CHAR, $enc->Decode($transaksi));
        $rs = $dtaccess->Execute($sql);

        $sql = "update klinik.klinik_folio set fol_nominal = '".$total["total"]."',
                fol_hrs_bayar='".$total["total"]."',  fol_dibayar ='".$total["total"]."' where
                fol_catatan ='".$nojual["penjualan_nomor"]."'";
                $rs = $dtaccess->Execute($sql);

        $sql = "SELECT sum(fol_nominal) AS total FROM klinik.klinik_folio WHERE id_pembayaran = ".QuoteValue(DPE_CHAR, $enc->Decode($id_pembayaran));
        $dataTotal = $dtaccess->Fetch($sql);

        $sql = "UPDATE klinik.klinik_pembayaran SET pembayaran_total = ".QuoteValue(DPE_NUMERIC, $dataTotal['total'])." WHERE pembayaran_id = ".QuoteValue(DPE_CHAR, $enc->Decode($id_pembayaran));
        $dtaccess->Execute($sql);

        header("location: penjualan.php?kode=$kode&transaksi=$transaksi&idreg=$idreg&id_pembayaran=$id_pembayaran");
        exit();
  }
?>

<!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<div>
  <br><br>
  <div class="col-md-12 col-sm-12 col-xs-12">
    <form name="frmEdit" method="POST" action="" class="form-horizontal form-label-left">
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-3">PAKET</label>
        <div class="col-md-5 col-sm-5 col-xs-12">
          <select class="form-control" name="id_paket" id="id_paket" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
            <option value="">[- Pilih Paket -]</option>
            <?php for($i=0,$n=count($dataJenis);$i<$n;$i++) { ?>
              <option value="<?php echo $dataJenis[$i]["paket_id"];?>" <?php if($_POST["id_paket"]==$dataJenis[$i]["paket_id"]) echo "selected";?>><?php echo $dataJenis[$i]["paket_nama"];?></option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-2 col-sm-2 col-xs-2">
          <input type="submit" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()" />
        </div>
      </div>  
            
      <div class="form-group">
        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
      </div> 
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-3"></label>
        <div class="col-md-offset-1 col-md-10 col-sm-10 col-xs-10">
          <?php if ($tutup=='1') { ?> <font color="red">PAKET TELAH TERSIMPAN SILAHKAN CLICK TOMBOL TUTUP</font>   <? } ?>
          <table cellpadding="1" cellspacing="1" border="0" align="center" width="100%" class="table table-striped table-bordered">
            <tr>
              <td colspan="5" align="center">
                <a href="penjualan.php?kode=<?=$kode?>&transaksi=<?=$transaksi?>&idreg=<?=$idreg?>&id_pembayaran=<?=$id_pembayaran?>" title="Tutup" class="btn btn-danger">Tutup</a>
                <input type="submit" name="simpan" value="Simpan" class="btn btn-primary">
              </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
              <td width='4%' align="center" class="tableheader"><b>No</b></td>
              <td width='30%' align="center" class="tablecontent"><b>Nama</b></td>
              <td width='10%' align="center" class="tablecontent"><b>Jumlah</b></td>
              <td width='23%' align="center" class="tablecontent"><b>Beli</b></td>
              <?php
              if($conf['conf_biaya_tuslag'] == 0 || $conf['conf_biaya_tuslag'] == null){
              ?>
              <td width='23%' align="center" class="tablecontent"><b>Tuslag</b></td>
              <?php
              }
              ?>
            </tr>

            <?php for($i=0,$j=count($dataTable);$i<$j;$i++){ ?>
              <tr>
                <td align="center" class="tablecontent-odd"><?php echo $i+1?></td>
                <td align="center" class="tablecontent-odd"><?php echo $dataTable[$i]["item_nama"]?></td>
                <td align="center" class="tablecontent-odd"><?php echo $dataTable[$i]["jumlah_item"]?></td>
                <td align="center" class="tablecontent-odd">
                  <input type="text" width="100%" name="penjualan_pakai<?php echo $i;?>" id="penjualan_pakai<?php echo $i;?>" value="" />
                </td>
                <?php
                if($conf['conf_biaya_tuslag'] == 0 || $conf['conf_biaya_tuslag'] == null){
                ?>
                <td align="center" class="tablecontent-odd">
                  <input type="text" width="100%" name="tuslag<?php echo $i;?>" id="tuslag<?php echo $i;?>" value="" />
                </td>
                <?php
                }
                ?>
              </tr>
            <? } ?>
          </table>
          <input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
          <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
          <?php echo $view->SetFocus("_name",true);?>
        </div>
      </div>            
    </form>
  </div>
</div>