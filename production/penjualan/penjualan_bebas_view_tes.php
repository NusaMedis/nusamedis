<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new TextEncrypt();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
//     $poli = $auth->GetPoli();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();

//DIPATEN SEMENTARA
$poli = "33"; //POLI APOTIK IRJ

$sql = "select id_gudang from global.global_auth_poli where poli_id=" . QuoteValue(DPE_CHAR, $poli);
$rs = $dtaccess->Execute($sql);
$gudang = $dtaccess->Fetch($rs);
$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif 

$sql = " select * from apotik.apotik_conf where conf_id = 'asa' ";
$rs_edit = $dtaccess->Execute($sql);
$dataOnOff = $dtaccess->Fetch($rs_edit);

// PRIVILLAGE
if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}

$skr = date("d-m-Y");
if (!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
if (!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;

$sql_where[] = "date(a.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_POST["tanggal_awal"]));
$sql_where[] = "date(a.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_POST["tanggal_akhir"]));

if ($sql_where[0])
  $sql_where = implode(" and ", $sql_where);

$sql = "select a.*, b.cust_usr_kode, c.jenis_nama, d.id_pembayaran,e.poli_nama from apotik.apotik_penjualan a
             left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
             left join global.global_jenis_pasien c on a.id_jenis_pasien = c.jenis_id 
             left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
             left join global.global_auth_poli e on e.poli_id=d.id_poli
             where penjualan_id in(select id_penjualan from apotik.apotik_penjualan_detail) and  a.is_terima<>'n' and a.id_cust_usr = '100' and penjualan_flag<>'R' and a.id_dep =" . QuoteValue(DPE_CHAR, $depId);
$sql .= " and " . $sql_where;
$sql .= "order by penjualan_create desc";
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);
//echo $sql;
//die();
$isAllowedDel = $auth->IsAllowed("pros_penjualan_dlm", PRIV_DELETE);
$isAllowedUpdate = $auth->IsAllowed("pros_penjualan_dlm", PRIV_UPDATE);
$isAllowedCreate = $auth->IsAllowed("pros_penjualan_dlm", PRIV_CREATE);

//echo $sql;
//die();

// --- construct new table ---- //
// VIEW PENJUALAN OBAT
$tableHeader = "Penjualan Bebas";
$counterHeader = 0;
/*if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }*/

// if($isAllowedUpdate){
$tbHeader[0][$counterHeader][TABLE_ISI] = "Item";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
$counterHeader++;
// }

/*
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Order";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }  */
$tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Serahkan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Nota";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;


$tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik/Kamar/Triase Asal";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;
//TOTAL HEADER TABLE
$jumHeader = $counterHeader;
for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {

  /*  if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["po_id"].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
 
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'id='.$enc->Encode($dataTable[$i]["penjualan_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }*/

  //if ( $dataOnOff["conf_apotik_central"] == 'y') {


  // if($dataTable[$i]["penjualan_terbayar"]=='n' && $dataTable[$i]["penjualan_flag"]=='L'){
  //   $sellPage = "penjualan_bebas.php?";
  //     $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$sellPage.'kode='.$enc->Encode($dataTable[$i]["cust_usr_kode"]).'&transaksi='.$enc->Encode($dataTable[$i]["penjualan_id"]).'&idreg='.$enc->Encode($dataTable[$i]["id_reg"]).'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'"><img hspace="2" src="'.$ROOT.'gambar/finder.png" align="top" alt="Edit" title="Edit" border="0"></a>';               
  //     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  //     $counter++;
  // }   else {

  //    $tbContent[$i][$counter][TABLE_ISI] = '&nbsp';               
  //     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  //     $counter++;
  //  }

  if ($dataTable[$i]["penjualan_terbayar"] == 'n') {
    // if ($dataTable[$i]["penjualan_flag"] == 'L') {
    //   $sellPage = "penjualan_bebas.php?";

    //   $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $sellPage . 'transaksi=' . $dataTable[$i]["penjualan_id"] . '"><img hspace="2" src="' . $ROOT . 'gambar/finder.png" align="top" alt="Edit" title="Edit" border="0" ></a>';
    //   $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    //   $counter++;
    // } else {

      $sellPage = "penjualan_bebas.php?";
      $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $sellPage . 'kode=' . $enc->Encode($dataTable[$i]["cust_usr_kode"]) . '&transaksi=' . $enc->Encode($dataTable[$i]["penjualan_id"]) . '&idreg=' . $enc->Encode($dataTable[$i]["id_reg"]) . '&id_pembayaran=' . $dataTable[$i]["id_pembayaran"] . '"><img hspace="2" src="' . $ROOT . 'gambar/finder.png" align="top" alt="Edit" title="Edit" border="0"></a>';
      $tbContent[$i][$counter][TABLE_ALIGN] = "center";
      $counter++;
    // }
  } else {

    $tbContent[$i][$counter][TABLE_ISI] = '';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;
  }


  // if($isAllowedUpdate) {

  // } 
  /*if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'transaksi='.$enc->Encode($dataTable[$i]["po_id"]).'"><img hspace="2" width="16" height="16" src="'.$ROOT.'gambar/b_prop.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }*/

  // if(!empty($dataTable[$i]["id_fol"])){
  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="' . $ROOT . 'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetak(\'' . $dataTable[$i]["penjualan_id"] . '\');"/>';
  // }else{
  //  $tbContent[$i][$counter][TABLE_ISI] = '&nbsp'; 
  // }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  if ($dataTable[$i]["penjualan_terbayar"] == 'y') {
    $tbContent[$i][$counter][TABLE_ISI] = '<a href="serah_obat.php?kode=' . $enc->Encode($dataTable[$i]["cust_usr_kode"]) . '&id=' . $enc->Encode($dataTable[$i]["penjualan_id"]) . '&idreg=' . $enc->Encode($dataTable[$i]["id_reg"]) . '&id_pembayaran=' . $dataTable[$i]["id_pembayaran"] . '"><img hspace="2" width="30" height="30" src="' . $ROOT . 'gambar/give.png" style="cursor:pointer" alt="Serahkan" title="Serahkan" border="0"/>';
  } else {
    $tbContent[$i][$counter][TABLE_ISI] = '';
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $date = explode(" ", $dataTable[$i]["penjualan_create"]);

  $tbContent[$i][$counter][TABLE_ISI] = format_date($date[0]) . "&nbsp;" . $date[1];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["cust_usr_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["penjualan_alamat"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["poli_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["jenis_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;
}
$colspan = count($tbHeader[0]);

/*if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'tambah=1\'">&nbsp;';
     }*/

$tbBottom[0][0][TABLE_WIDTH] = "100%";
$tbBottom[0][0][TABLE_COLSPAN] = $colspan;
?>


<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>
<script language="JavaScript">
  var _wnd_new;

  function BukaWindow(url, judul) {
    if (!_wnd_new) {
      _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
    } else {
      if (_wnd_new.closed) {
        _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
      } else {
        _wnd_new.focus();
      }
    }
    return false;
  }

  function ProsesCetak(id) {

    BukaWindow('penjualan_bebas_cetak.php?id=' + id + '', 'Nota');
    //document.location.href='<?php echo $thisPage; ?>';
  }
</script>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require_once($LAY . "sidebar.php") ?>

      <!-- top navigation -->
      <?php require_once($LAY . "topnav.php") ?>
      <!-- /top navigation -->

      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          <div class="page-title">
            <div class="title_left">
              <h3>Apotik</h3>
            </div>
          </div>
          <div class="clearfix"></div>
          <!-- row filter -->
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2><?= $tableHeader; ?></h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form name="frmFind" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                      <div class='input-group date' id='datepicker'>
                        <input id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo $_POST["tanggal_awal"] ?>" />
                        <span class="input-group-addon">
                          <span class="fa fa-calendar">
                          </span>
                        </span>
                      </div>

                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                      <div class='input-group date' id='datepicker2'>
                        <input id="tanggal_akhir" name="tanggal_akhir" type='text' class="form-control" value="<?php echo $_POST["tanggal_akhir"] ?>" />
                        <span class="input-group-addon">
                          <span class="fa fa-calendar">
                          </span>
                        </span>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <input type="button" name="btnTambah" value="Tambah Penjualan Bebas" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href='penjualan_bebas.php'" />
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <input type="submit" name="btnLanjut" id="btnLanjut" value="Lanjut" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">&nbsp;
                    </div>

                    <div class="clearfix"></div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- //row filter -->

          <div class="row">

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                          <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI]; ?> </th>
                        <? } ?>
                      </tr>
                    </thead>
                    <tbody>
                      <? for ($i = 0, $n = count($dataTable); $i < $n; $i++) {   ?>

                        <tr class="even pointer">
                          <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI] ?></td>
                          <? } ?>

                        </tr>

                      <? } ?>
                    </tbody>
                  </table>
                  <script type="text/javascript">
                    Calendar.setup({
                      inputField: "tanggal_awal", // id of the input field
                      ifFormat: "<?= $formatCal; ?>", // format of the input field
                      showsTime: false, // will display a time selector
                      button: "img_tgl_awal", // trigger for the calendar (button ID)
                      singleClick: true, // double-click mode
                      step: 1 // show all years in drop-down boxes (instead of every other year as default)
                    });

                    Calendar.setup({
                      inputField: "tanggal_akhir", // id of the input field
                      ifFormat: "<?= $formatCal; ?>", // format of the input field
                      showsTime: false, // will display a time selector
                      button: "img_tgl_akhir", // trigger for the calendar (button ID)
                      singleClick: true, // double-click mode
                      step: 1 // show all years in drop-down boxes (instead of every other year as default)
                    });
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /page content -->

      <!-- footer content -->
      <?php require_once($LAY . "footer.php") ?>
      <!-- /footer content -->
    </div>
  </div>

  <?php require_once($LAY . "js.php") ?>

</body>

</html>