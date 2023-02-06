<?php
  require_once("../penghubung.inc.php");
  require_once($ROOT."lib/login.php");
  require_once($ROOT."lib/encrypt.php");
  require_once($ROOT."lib/datamodel.php");
  require_once($ROOT."lib/currency.php");
  require_once($ROOT."lib/dateLib.php");
  require_once($ROOT."lib/expAJAX.php");
  require_once($ROOT."lib/tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new TextEncrypt();     
  $auth = new CAuth();
  $table = new InoTable("table","100%","left");
  $usrId = $auth->GetUserId();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();

  $tgl = date("d-m-Y");
  $wkt = date("H:i:s");


  $opname_id = ($_POST['opname_id']) ? $_POST['opname_id'] : $_GET['id'];
  $periode_tahun_awal = ($_POST['periode_tahun']) ? $_POST['periode_tahun'] : $_GET['tahun'];
  $periode_bulan_awal = ($_POST['periode_bulan']) ? $_POST['periode_bulan'] : $_GET['id_periode'];
  $gudang_awal = ($_POST['gudang']) ? $_POST['gudang'] : $_GET['id_gudang'];
  
  if ($dataOpname) {
    $tgl_opname = $dataOpname['opname_tanggal'];
  }else{
    $tgl_opname = ($_POST['tanggal']) ? $_POST['tanggal'] : $_GET['tanggal'];
  }

  if ($_POST['simpan']) {
    $sql = "select penerimaan_periode_tanggal_awal, penerimaan_periode_tanggal_akhir from logistik.logistik_penerimaan_periode where
            penerimaan_periode_id = ".QuoteValue(DPE_CHAR,$periode_bulan_awal);
    $dataPeriodeOpname = $dtaccess->Fetch($sql);

    /* SIMPAN OPNAME */
    $dbTable = "logistik.logistik_opname";
     
    $dbField[0] = "opname_id";   // PK
    $dbField[1] = "opname_tanggal";
    $dbField[2] = "id_dep";
    $dbField[3] = "id_gudang";
    $dbField[4] = "id_periode";
    $dbField[5] = "opname_flag";
    $dbField[6] = "opname_waktu";
    $dbField[7] = "opname_keterangan";

    $opname_id = ($opname_id) ? $opname_id : $dtaccess->GetTransID();
    
    $dbValue[0] = QuoteValue(DPE_CHAR,$opname_id);
    $dbValue[1] = QuoteValue(DPE_DATE,date_db($_POST["tanggal"]));
    $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[3] = QuoteValue(DPE_CHAR,$gudang_awal);
    $dbValue[4] = QuoteValue(DPE_CHAR,$periode_bulan_awal);
    $dbValue[5] = QuoteValue(DPE_CHAR,'M');
    $dbValue[6] = QuoteValue(DPE_DATE,$_POST['waktu']);
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST['opname_keterangan']);

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

    if ($_POST['opname_id'] == '') $dtmodel->Insert() or die("insert  error");

    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey); 
    /* SIMPAN OPNAME */

    /* SQL STOK ITEM OPNAME */
    $sql = "select stok_item_id from logistik.logistik_stok_item where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_opname = ".QuoteValue(DPE_CHAR,$opname_id)." and id_gudang = ".QuoteValue(DPE_CHAR,$gudang_awal);
    $dataStokItem =$dtaccess->Fetch($sql);
    /* SQL STOK ITEM OPNAME */

    /* INSERT / UPDATE STOK ITEM */
    $dbTable = "logistik.logistik_stok_item";

    $dbField[0]  = "stok_item_id";   // PK
    $dbField[1]  = "stok_item_jumlah";
    $dbField[2]  = "id_item";    
    $dbField[3]  = "id_gudang";
    $dbField[4]  = "stok_item_flag";
    $dbField[5]  = "stok_item_create";
    $dbField[6]  = "stok_item_saldo";
    $dbField[7]  = "stok_item_keterangan";
    $dbField[8]  = "id_dep";
    $dbField[9]  = "id_opname";      
    $dbField[10]  = "stok_item_sebelumnya"; 
    if(!$_POST['stok_item_id']){
          $stokItemId =  $dtaccess->GetTransID();
      }else{
          $stokItemId =  $_POST['stok_item_id']  ; 
      };


    $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
    $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST['selisih']);
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,$gudang_awal);
    $dbValue[4] = QuoteValue(DPE_CHAR,'O');
    $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["tanggal"]).' '.$_POST['waktu']);
    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["stok_sebenarnya"]);      
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["keterangan"]);     
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$opname_id);
    $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST['stok_tercatatnyaa']);
    // echo "<pre>";
    // var_dump($_POST);
    // echo "</pre>";
    // print_r($dbValue);die();
    $dbKey[0]   = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel    = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

    if(!$_POST['stok_item_id']){
      $dtmodel->Insert() or die("insert  error");
        }else{
      $dtmodel->Update() or die("update  error");
        }
   

    unset($dbField);
    unset($dbValue);
    /* INSERT / UPDATE STOK ITEM */

    /* UPDATE STOK DEP */
    $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_sebenarnya"])).", stok_dep_create = current_timestamp, stok_dep_tgl = current_date  where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang =".QuoteValue(DPE_CHAR,$gudang_awal);
    $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    /* UPDATE STOK DEP */

    /* SQL STOK DEP PERIODE */
    $sql = "select * from logistik.logistik_stok_dep_periode where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and stok_dep_periode_tgl >= '".$dataPeriodeOpname['penerimaan_periode_tanggal_awal']."' and stok_dep_periode_tgl <= '".$dataPeriodeOpname['penerimaan_periode_tanggal_akhir']."' and id_gudang =".QuoteValue(DPE_CHAR,$gudang_awal);              
    $dataStokDepPeriode = $dtaccess->Fetch($sql);
    /* SQL STOK DEP PERIODE */

    /* INSERT / UPDATE STOK DEP PERIODE */
    $dbTable = "logistik.logistik_stok_dep_periode";
           
    $dbField[0]  = "stok_dep_periode_id";   // PK
    $dbField[1]  = "id_item";
    $dbField[2]  = "stok_dep_periode_saldo";
    $dbField[3]  = "stok_dep_periode_create";
    $dbField[4]  = "stok_dep_periode_tgl";    
    $dbField[5]  = "id_gudang";
    $dbField[6]  = "id_dep";
    $dbField[7]  = "id_periode";

    $stokdepPerId = ($dataStokDepPeriode) ? $dataStokDepPeriode["stok_dep_periode_id"] : $dtaccess->GetTransID();

    $dbValue[0] = QuoteValue(DPE_CHAR,$stokdepPerId);
    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
    $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_sebenarnya"]));    
    $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["tanggal"]));
    $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["tanggal"]));
    $dbValue[5] = QuoteValue(DPE_CHAR,$gudang_awal);
    $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[7] = QuoteValue(DPE_CHAR,$periode_bulan_awal);

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

    ($dataStokDepPeriode) ? $dtmodel->Update() or die("update  error") : $dtmodel->Insert() or die("insert  error");

    unset($dbTable);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);
    /* INSERT / UPDATE STOK DEP PERIODE */

    /* SQL PENGURUTAN */
    $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$gudang_awal)." and id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." order by id_gudang asc, stok_item_create asc";
    $dataAdjustment = $dtaccess->FetchAll($sql);
    /* SQL PENGURUTAN */

    for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
      if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
      if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
      if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Opname
      if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
      if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
      
      $sql  ="update logistik.logistik_stok_item set stok_item_saldo=".$saldo." where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
      $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    }

    $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo))." where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang =".QuoteValue(DPE_CHAR,$gudang_awal);
    $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
  }

  if ($_GET['hapus']) {
    $sql = "delete from logistik.logistik_stok_item where stok_item_id = ".QuoteValue(DPE_CHAR,$_GET['stok_opname_id']);
    $result = $dtaccess->Execute($sql);

    /* SQL PENGURUTAN */
    $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$_GET['id_gudang'])." and id_item = ".QuoteValue(DPE_CHAR,$_GET["id_item"])." order by id_gudang asc, stok_item_create asc";
    $dataAdjustment = $dtaccess->FetchAll($sql);
    /* SQL PENGURUTAN */

    for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
      if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
      if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
      if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Opname
      if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
      if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
      if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
      if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
      
      $sql  ="update logistik.logistik_stok_item set stok_item_saldo=".$saldo." where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
      $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    }

    $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo))." where id_item = ".QuoteValue(DPE_CHAR,$_GET["id_item"])." and id_gudang =".QuoteValue(DPE_CHAR,$_GET['id_gudang']);
    $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

    // $link = "trans_opname_edit.php?id=$_GET[id]&tahun=$_GET[tahun]&periode=$_GET[periode]&id_gudang=";
    $link = "trans_opname_edit.php?id=$_GET[id]&tahun=&id_periode=$_GET[periode]&id_gudang=$_GET[id_gudang]&id_periode=$_GET[id_periode]&tanggal=$tgl_opname";
    header("location: $link");
  }

  /* SQL GUDANG */
  $sql_gudang = "SELECT * FROM logistik.logistik_gudang WHERE gudang_flag = 'M' AND id_dep = ".QuoteValue(DPE_CHAR, $depId)." ORDER BY gudang_nama ASC";
  $dataGudang = $dtaccess->FetchAll($sql_gudang);
  /* SQL GUDANG */  

  $sql_table = "SELECT a.*, b.*,a.id_gudang as gudang FROM logistik.logistik_stok_item a 
  LEFT JOIN logistik.logistik_item b ON a.id_item = b.item_id 
  WHERE id_opname = ".QuoteValue(DPE_CHAR, $opname_id)." ORDER BY stok_item_create ASC";
  $dataTable = $dtaccess->FetchAll($sql_table);

  /* SQL OPNAME */
  $sql_opname = "SELECT * FROM logistik.logistik_opname WHERE opname_id = ".QuoteValue(DPE_CHAR, $opname_id);
  $dataOpname = $dtaccess->Fetch($sql_opname);
  /* SQL OPNAME */

  $waktu = ($dataOpname) ? $dataOpname['opname_waktu'] : $wkt;
  $tanggal = ($dataOpname) ? date_db($dataOpname['opname_tanggal']) : $tgl;

  $tableHeader = "Opname Barang";
?>

<!DOCTYPE html>
<html>
  <?php require_once($LAY."header.php") ?>

  <script type="text/javascript">
    function obat(isi) {
      mamas = $('#tanggal').val();
      auto_obat(isi, mamas);
    }

    function tanggal_isi(isi) {
      var gudang = $('#gudang').val();
      mamas = isi;
      auto_obat(gudang, mamas);
    }

    function auto_obat(isi_gudang, tanggal_op) {
      var waktu = $('#waktu').val();
      $('#item_nama').autocomplete({
        serviceUrl: 'get_obat.php?id_gudang='+isi_gudang+'&tanggal='+tanggal_op+'&waktu='+waktu,
        paramName: 'item_nama',
        transformResult: function(response) {
          var data = jQuery.parseJSON(response);
          return {
            suggestions: $.map(data, function(item) {
              return {
                value: item.item_nama,
                data: { 
                  item_id: item.item_id,
                  item_nama: item.item_nama,
                  item_kode: item.item_kode,
                  stok_dep_saldo: item.stok_dep_saldo,
                  stok_tercatat: item.stok_tercatat,
                } 
              };
            })
          };
        },

        onSelect: function (suggestion) {
          $('#item_nama').val(suggestion.data.item_nama);
          $('#item_kode').val(suggestion.data.item_kode);
          $('#item_id').val(suggestion.data.item_id);
          $('#stok_tercatat').val(suggestion.data.stok_dep_saldo);
          $('#stok_tercatatnyaa').val(suggestion.data.stok_tercatat);
        }
      });
    }

    $( document ).ready(function() {
      var mamas = $('#tanggal').val();
      var gudang = $('#gudang').val();
      auto_obat(gudang,mamas);

      tahun($("#periode_tahun").val());
    });
  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <?php require_once($LAY."topnav.php") ?>
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <!-- DATA OPNAME -->
            <form method="POST" action="">
              <input type="hidden" name="opname_id" value="<?=$opname_id?>">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2><?=$tableHeader?></h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Periode Tahun</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="periode_tahun" id="periode_tahun" onchange="tahun(this.value)" required="" <?php if ($dataOpname) echo 'readonly' ?>>
                              <option value="">--- Pilih Periode Tahun ---</option>
                              <?php
                                $periode_tahun = ($periode_tahun_awal) ? $periode_tahun_awal : date('Y');
                                for ($i = 2018; $i <= date('Y')+5; $i++) {
                                ?>
                                  <option value="<?=$i?>" <?= ($periode_tahun == $i) ? "selected" : ''; ?>><?=$i?></option>
                                <?php
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Periode Bulan</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="periode_bulan" id="periode_bulan" onchange="bulan(this.value)" required="" <?php if ($dataOpname) echo 'readonly' ?>>
                              <option value="">--- Pilih Periode Bulan ---</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Tanggal Opname</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <!-- <div class='input-group date' id='datepicker'> -->
                              <input name="tanggal" id="tanggal" type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" value="<?php if(!$tgl_opname) { 
                                              echo date('d-m-Y');
                                            }else{
                                             echo date("d-m-Y", strtotime($tgl_opname));
                                             }?>" 
                                     <?php if($tgl_opname){echo "readonly";}?> onchange="tanggal_isi(this.value)">
                              <!-- <span class="input-group-addon"><span class="fa fa-calendar"></span></span> -->
                            <!-- </div> -->
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Waktu Opname</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <input name="waktu" id="waktu" type='text' class="form-control" data-inputmask="'mask': '99:99:99'" value="<?= ($_POST['waktu']) ? $_POST['waktu'] : $waktu; ?>" required="" <?php if ($dataOpname) echo 'readonly' ?>>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Gudang</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <select class="form-control" name="gudang" id="gudang" required="" onchange="obat(this.value)" <?php if ($dataOpname) echo 'readonly' ?>>
                              <option value="">--- Pilih Gudang ---</option>
                              <?php foreach ($dataGudang as $gudang): ?>
                                <option value="<?=$gudang['gudang_id']?>" <?= ($gudang_awal == $gudang['gudang_id']) ? "selected" : ''; ?>><?=$gudang['gudang_nama']?></option>
                              <?php endforeach ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4 text-right">Keterangan</label>
                          <div class="col-md-8 col-sm-8 col-xs-8">
                            <textarea name="opname_keterangan" class="form-control" style="resize: none;" <?php if ($dataOpname) echo "disabled" ?>><?= $dataOpname['opname_keterangan'] ?></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- DATA OPNAME -->
              <div class="clearfix"></div>
              <!-- TABLE -->
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_content">
                      <table class="table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th width="4%" style="text-align: center;"> Hapus </th>
                            <th width="4%" style="text-align: center;"> Edit </th>
                            <th width="4%" style="text-align: center;"> No </th>
                            <th width="7%" style="text-align: center;"> Kode Barang </th>
                            <th width="35%" style="text-align: center;">Nama Obat</th>
                            <th width="10%" style="text-align: center;">Stok Tercatat</th>
                            <th width="10%" style="text-align: center;">Stok Sebenarnya</th>
                            <th width="10%" style="text-align: center;">Selisih</th>
                            <th width="10%" style="text-align: center;">Keterangan</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($dataTable): ?>
                            <?php foreach ($dataTable as $key => $value): ?>
                              <tr>
                                <td>
                                  <a href="trans_opname_edit.php?id=<?=$opname_id?>&stok_opname_id=<?php echo $value['stok_item_id'] ?>&id_item=<?php echo $value['item_id'] ?>&tahun=&id_periode=<?=$periode_bulan_awal?>&id_gudang=<?php echo $value['gudang'] ?>&hapus=1&tanggal=<?=$tgl_opname ?>" type="button"><center><i class="fa fa-close" style="font-size: 25px;"></i></center></a>
                                </td>
                                <td>
                                  <a href="#" type="button" onclick="SendEdit(<?= $key?>)" ><center><i class="fa fa-pencil" style="font-size: 25px;"></i></center></a>
                                  <input type="hidden" id="id_edit_item_<?= $key ?>" value="<?= $value['stok_item_id'] ?>">
                                  <input type="hidden" id="stok_sebelum_edit_item_<?= $key ?>" value="<?= $value["stok_item_sebelumnya"] ?>">
                                  <input type="hidden" id="stok_asli_edit_item_<?= $key ?>" value="<?= $value["stok_item_saldo"] ?>">
                                  <input type="hidden" id="stok_selisih_edit_item_<?= $key ?>" value="<?= $value["stok_item_jumlah"] ?>">
                                  <input type="hidden" id="ket_edit_item_<?= $key ?>" value="<?= $value["stok_item_keterangan"] ?>">
                                  <input type="hidden" id="nama_edit_item_<?= $key ?>" value="<?= $value["item_nama"] ?>">
                                  <input type="hidden" id="idItem_edit_item_<?= $key ?>" value="<?= $value["id_item"] ?>">
                                </td>
                                
                                <td align="center"><?=$key+1?></td>
                                <td align="center"><?=$value['item_kode']?></td>
                                <td><?=$value['item_nama']?></td>
                                <td align="center"><?=number_format($value['stok_item_sebelumnya'],2,",",".")?></td>
                                <td align="center"><?=number_format($value['stok_item_saldo'],2,",",".")?></td>
                                <td align="center"><?=number_format($value['stok_item_jumlah'],2,",",".")?></td>
                                <td><?=$value['stok_item_keterangan']?></td>
                              </tr>
                            <?php endforeach ?>
                          <?php endif ?>
                          <tr>
                           <td>
                           </td>
                           <td>
                           </td>
                           <td>
                           <input type="hidden" name="stok_item_id" id="stok_item_id" value="">
                           
                           </td>
                           <td>
                           <input type="text" name="item_kode" id="item_kode" class="form-control" readonly="true">
                           
                           </td>
                            <td>
                              <input type="text" name="item_nama" id="item_nama" value="" placeholder="Nama Obat" class="form-control">
                              <input type="hidden" name="item_id" id="item_id" value="">
                              <input type="hidden" name="stok_tercatatnyaa" id="stok_tercatatnyaa" value="" placeholder="Stok Tercatat" class="form-control" readonly="">
                            </td>
                            <td>
                              <input type="text" name="stok_tercatat" id="stok_tercatat" value="" placeholder="Stok Tercatat" class="form-control" readonly="">
                            </td>
                            <td><input type="text" name="stok_sebenarnya" id="stok_sebenarnya" value="" placeholder="Stok Sebenarnya" class="form-control" onchange="fun_selisih(this.value)"></td>
                            <td><input type="text" name="selisih" id="selisih" value="" placeholder="Selisih" class="form-control" readonly=""></td>
                            <td><input type="text" name="keterangan" id="keterangan" value="" placeholder="Keterangan" class="form-control"></td>
                          </tr>
                        </tbody>
                      </table>
                      <a href="trans_opname.php" title="Kembali" class="btn btn-danger">Kembali</a>
                      <button type="button" class="btn btn-warning" onclick="cetak()">Cetak</button>
                      <input type="submit" name="simpan" value="Simpan" class="btn btn-success pull-right">
                    </div>
                  </div>
                </div>
              </div>
              <!-- TABLE -->
            </form>
          </div>
        </div>
        <?php require_once($LAY."footer.php") ?>
      </div>
    </div>
    <?php require_once($LAY."js.php") ?>
  </body>
</html>

<script type="text/javascript">
  
  function SendEdit(key){

    var stok_item_id = $(`#id_edit_item_${key}`).val();
    var stok_sebelumnya = $(`#stok_sebelum_edit_item_${key}`).val();
    var stok_asli = $(`#stok_asli_edit_item_${key}`).val();
    var stok_selisih = $(`#stok_selisih_edit_item_${key}`).val();
    var ket = $(`#ket_edit_item_${key}`).val();
    var nama_item = $(`#nama_edit_item_${key}`).val();
    var id_item = $(`#idItem_edit_item_${key}`).val();
    
    $('#item_nama').val(nama_item);
    $('#item_id').val(id_item);
    $('#stok_item_id').val(stok_item_id);
    $('#stok_tercatat').val(parseFloat(stok_sebelumnya));
    $('#stok_tercatatnyaa').val(parseFloat(stok_sebelumnya));
    $('#stok_sebenarnya').val(parseFloat(stok_asli));
    $('#selisih').val(parseFloat(stok_selisih));
    $('#keterangan').val(ket);


  }



  function tahun(isi_tahun) {
    jQuery.getJSON('get_periode.php', {tahun: isi_tahun}, function(data) {
      $("#periode_bulan").html(`<option value="">--- Pilih Periode Bulan ---</option>`);
      $.each(data, function(index, val) {
        $("#periode_bulan").append(
          "<option id = '"+val.penerimaan_periode_id+"' value = '"+val.penerimaan_periode_id+"'>"+val.penerimaan_periode_nama+"</option>");
      });
      $('#<?=$periode_bulan_awal?>').attr('selected',true);
    });

  }

  function bulan(isi_bulan) {
    jQuery.getJSON('get_periode.php', {id: isi_bulan}, function(data) {
      $("#tanggal_awal").val(data.tgl_awal);
      $("#tanggal_akhir").val(data.tgl_akhir);
    });
  }

  function fun_selisih(isi) {
    $('#selisih').val(isi-$('#stok_tercatatnyaa').val());
  }

  function cetak() {
    window.open('trans_opname_cetak.php?id=<?=$opname_id?>', '_blank');
    window.location.href = "trans_opname.php";
  }
</script>