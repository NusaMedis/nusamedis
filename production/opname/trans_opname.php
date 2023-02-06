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
  $depLowest = $auth->GetDepLowest();
  $skr = date("d-m-Y");


  $halaman_tambah = "trans_opname_edit.php";

  /* SQL GUDANG */
  $sql_gudang = "SELECT * FROM logistik.logistik_gudang WHERE gudang_flag ='M' 
  AND id_dep = ".QuoteValue(DPE_CHAR, $depId)." ORDER BY gudang_nama ASC";
  $dataGudang = $dtaccess->FetchAll($sql_gudang);
  /* SQL GUDANG */

  if ($_GET['lanjut']) {
    /* SQL TABLE */
    if ($_GET['periode_bulan']) $sql_where[] = "a.id_periode = ".QuoteValue(DPE_CHAR,$_GET['periode_bulan']);
    if ($_GET['tanggal_awal']) $sql_where[] = "a.opname_tanggal >= ".QuoteValue(DPE_CHAR,date_db($_GET['tanggal_awal']));
    if ($_GET['tanggal_akhir']) $sql_where[] = "a.opname_tanggal <= ".QuoteValue(DPE_CHAR,date_db($_GET['tanggal_akhir']));
    if ($_GET['gudang']) $sql_where[] = "a.id_gudang = ".QuoteValue(DPE_CHAR,$_GET['gudang']);

    if($sql_where) $sql_where = implode(" AND ",$sql_where);

    $sql_table = "SELECT a.*, b.penerimaan_periode_nama, c.gudang_nama FROM logistik.logistik_opname a LEFT JOIN logistik.logistik_penerimaan_periode b ON a.id_periode = b.penerimaan_periode_id LEFT JOIN logistik.logistik_gudang c ON a.id_gudang = c.gudang_id WHERE (a.tipe_opname != 'K' or a.tipe_opname is null) and a.opname_flag = 'M' AND a.id_dep = ".QuoteValue(DPE_CHAR, $depId);
    if($sql_where) $sql_table .= " AND ".$sql_where;
    $sql_table .= " ORDER BY a.opname_tanggal ASC, a.opname_waktu ASC";
    $dataTable = $dtaccess->FetchAll($sql_table);
    /* SQL TABLE */

    $stat = "";
    if(count($dataTable) > 0){
    if( $skr >= date_format(date_create($dataTable[0]['penerimaan_periode_tanggal_awal']), 'd-m-Y') && $skr <= date_format(date_create($dataTable[0]['penerimaan_periode_tanggal_akhir']), 'd-m-Y') ){
      $stat = "allow";
    }
    else{
      $stat = "ignore";
    }
  }
  }

  

  $tableHeader = "Stok Opname";
?>
<!DOCTYPE html>
<html>
  <?php require_once($LAY."header.php") ?>

  <script type="text/javascript">
    $( document ).ready(function() {
      tahun($("#periode_tahun").val());
    });

    function createNew(){
      var gudang = $("#gudang").val();
      var bulan = $("#periode_bulan").val();
      var tahun = $("#periode_tahun").val();
      <?php if($_GET['periode_tahun'] || $_GET['periode_bulan'] || $_GET['gudang']) { ?>
        var url = "trans_opname_edit.php?tahun=<?=$_GET['periode_tahun']?>&id_periode=<?=$_GET['periode_bulan']?>&id_gudang=<?=$_GET['gudang']?>";
      <?php } 
      else { ?>
        var url = "trans_opname_edit.php?tahun="+tahun+"&id_periode="+bulan+"&id_gudang="+gudang;
      <?php } ?>

      window.location.href = url;

    }

  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <?php require_once($LAY."topnav.php") ?>
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <!-- FILTER -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?=$tableHeader?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form method="GET" action="">
                      <!-- DIV KIRI -->
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tahun</label>
                          <select class="form-control" name="periode_tahun" id="periode_tahun" onchange="tahun(this.value)" required="">
                            <option value="">--- Pilih Periode Tahun ---</option>
                            <?php
                              $periode_tahun = ($_GET['periode_tahun']) ? $_GET['periode_tahun'] : date('Y');
                              for ($i = 2018; $i <= date('Y')+5; $i++) {
                              ?>
                                <option value="<?=$i?>" <?= ($periode_tahun == $i) ? "selected" : ''; ?>><?=$i?></option>
                              <?php
                              }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Bulan</label>
                          <select class="form-control" name="periode_bulan" id="periode_bulan" onchange="bulan(this.value)" required="">
                            <option value="">--- Pilih Periode Bulan ---</option>
                          </select>
                        </div>
                      </div>
                      <!-- DIV KIRI -->
                      <!-- DIV TENGAH -->
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Awal</label>
                          <div class='input-group date' id='datepicker'>
                            <input name="tanggal_awal" id="tanggal_awal" type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" value="<?= ($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : $skr; ?>" required="">
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Akhir</label>
                          <div class='input-group date' id='datepicker2'>
                            <input name="tanggal_akhir" id="tanggal_akhir" type='text' class="form-control" data-inputmask="'alias': 'dd-mm-yyyy'" value="<?= ($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : $skr; ?>" required="">
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                          </div>
                        </div>
                      </div>
                      <!-- DIV TENGAH -->
                      <!-- DIV KANAN -->
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <div class="col-md-12 col-sm-12 col-xs-12 input-group">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Gudang</label>
                          <select class="form-control" name="gudang" id="gudang" required="">
                            <option value="">--- Pilih Gudang ---</option>
                            <?php foreach ($dataGudang as $gudang): ?>
                              <option value="<?=$gudang['gudang_id']?>" <?= ($_GET['gudang'] == $gudang['gudang_id']) ? "selected" : ''; ?>><?=$gudang['gudang_nama']?></option>
                            <?php endforeach ?>
                          </select>
                        </div>            
                      </div>
                      <!-- DIV KANAN -->
                      <!-- BUTTON -->
                      <div class="col-md-12 col-sm-12 col-xs-12 ">
                        <a href="#" title="Tambah" onclick="createNew()" class="btn btn-success pull-right">Tambah</a>
                        <input type="submit" name="lanjut" value="Lanjut" class="btn btn-primary pull-right">
                      </div>
                      <!-- BUTTON -->
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- FILTER -->
            <div class="clearfix"></div>
            <!-- TABLE -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_content">
                    <table id="datatable-fixed-header" class="table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th style="text-align: center;">No</th>
                          <th style="text-align: center;">Edit</th>
                          <th style="text-align: center;">Print</th>
                          <th style="text-align: center;">Tanggal</th>
                          <th style="text-align: center;">Periode</th>
                          <th style="text-align: center;">Gudang</th>
                          <th style="text-align: center;">Keterangan</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if ($dataTable): ?>
                          <?php foreach ($dataTable as $key => $value): ?>
                            <tr>
                              <td align="center"><?=$key+1?></td>
                              <td>
                              <?php if($stat == 'allow') { ?>
                                <a href="trans_opname_edit.php?id=<?=$value['opname_id']?>&tahun=<?=$_GET['periode_tahun']?>&id_periode=<?=$_GET['periode_bulan']?>&id_gudang=<?=$_GET['gudang']?>&tanggal=<?=$value['opname_tanggal']?>" title="Edit"><center><i class="fa fa-pencil" style="font-size: 25px;"></i></center></a>
                              <?php }?>
                              </td>
                              <td><a href="trans_opname_cetak.php?id=<?=$value['opname_id']?>" title="Print" target="_BLANK"><center><i class="fa fa-print" style="font-size: 25px;"></i></center></a></td>
                              <td align="center"><?=date_db($value['opname_tanggal'])?></td>
                              <td align="center"><?=$value['penerimaan_periode_nama']?></td>
                              <td align="center"><?=$value['gudang_nama']?></td>
                              <td><?=$value['opname_keterangan']?></td>
                            </tr>
                          <?php endforeach ?>
                        <?php endif ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- TABLE -->
          </div>
        </div>
        <?php require_once($LAY."footer.php") ?>
      </div>
    </div>
    <?php require_once($LAY."js.php") ?>
  </body>
</html>
<script type="text/javascript">
  function tahun(isi_tahun) {
    jQuery.getJSON('get_periode.php', {tahun: isi_tahun}, function(data) {
      $("#periode_bulan").html(`<option value="">--- Pilih Periode Bulan ---</option>`);
      $.each(data, function(index, val) {
        $("#periode_bulan").append(
          "<option id = '"+val.penerimaan_periode_id+"' value = '"+val.penerimaan_periode_id+"'>"+val.penerimaan_periode_nama+"</option>");
      });
      $('#<?=$_GET["periode_bulan"]?>').attr('selected',true);
    });

  }

  function bulan(isi_bulan) {
    jQuery.getJSON('get_periode.php', {id: isi_bulan}, function(data) {
      $("#tanggal_awal").val(data.tgl_awal);
      $("#tanggal_akhir").val(data.tgl_akhir);
    });
  }
</script>