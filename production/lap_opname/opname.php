<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");    
     require_once($ROOT."lib/tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table","70%","left");
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
          $skr = date("d-m-Y");
          
     if($_GET["klinik"]) { 
              $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { 
              $_POST["klinik"] = $_POST["klinik"]; }
      else { 
              $_POST["klinik"] = $depId; 
      }
     
     if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
       } else if($_POST["klinik"]) { 
        $_POST["klinik"] = $_POST["klinik"]; 
         } else { 
          $_POST["klinik"] = $depId; 
          }          
     
     if($_GET["id_gudang"]) $_POST["id_gudang"]=$_GET["id_gudang"];
     if($_GET["id_periode"]) $_POST["id_periode"]=$_GET["id_periode"];
     if($_GET["tahun"]) $_POST["tahun"]=$_GET["tahun"];
     
     if(!$_POST["tahun"]) $_POST["tahun"]=date('Y');
     
     $plx = new expAJAX("GetPeriode");
     
     function GetPeriode($thn){
        global $dtaccess,$view,$depId,$ROOT; 
         $sql = "select * from logistik.logistik_penerimaan_periode where extract(year from penerimaan_periode_tanggal_awal)=".QuoteValue(DPE_CHAR,$thn)." 
                order by penerimaan_periode_tanggal_awal asc";
         $rs = $dtaccess->Execute($sql); 
         $dataPeriode = $dtaccess->FetchAll($rs);
          unset($periode);
          $periode[0] = $view->RenderOption("","[Pilih Periode]",$show);
          $i = 1;
          
         for($i=0,$n=count($dataPeriode);$i<$n;$i++){   
             if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) $show = "selected";
             $periode[$i+1] = $view->RenderOption($dataPeriode[$i]["penerimaan_periode_id"],$dataPeriode[$i]["penerimaan_periode_nama"],$show);
             unset($show);
         }
          $str = $view->RenderComboBox("id_periode","id_periode",$periode,null,null,null);
       return $str;
     }

//     if(!$_POST["id_gudang"]) $_POST["id_gudang"]=$theDep;     
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;       
     
     $addPage = "trans_opname_edit.php?tambah=1&klinik=".$_POST["klinik"]."&id_periode=".$_POST["id_periode"]."&id_gudang=".$_POST["id_gudang"]."&tahun=".$_POST["tahun"];
     
     if($_POST["id_periode"]){ $sql_where[] = "h.id_periode = ".QuoteValue(DPE_CHAR,$_POST["id_periode"]);
     } else {
     if($_POST["tanggal_awal"]) $sql_where[] = "h.opname_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if($_POST["tanggal_akhir"]) $sql_where[] = "h.opname_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     }
     if($_POST["id_gudang"] && $_POST["id_gudang"]<>'--') $sql_where[] = "h.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);

     //$sql_where[] = "1=1";
     
     if($sql_where) $sql_where = implode(" and ",$sql_where);

      if($_POST["btnLanjut"] || $_POST["btnExcel"] )   {
           
     $sql = "select a.*,b.*,a.id_gudang as dep_dep, grup_item_nama, c.gudang_nama, a.stok_item_create as tanggal, 
     d.gudang_nama as nama_asal, e.gudang_nama as nama_tujuan, f.dep_nama as departemen, g.gudang_nama as gudang 

      from logistik.logistik_stok_item a 
      left join logistik.logistik_item b on a.id_item = b.item_id 
      left join logistik.logistik_grup_item i on b.id_kategori = i.grup_item_id
      left join logistik.logistik_gudang c on c.gudang_id = a.id_gudang 
      left join logistik.logistik_gudang d on d.gudang_id = a.id_dep_asal 
      left join logistik.logistik_gudang e on e.gudang_id = a.id_dep_tujuan 
      left join global.global_departemen f on f.dep_id = a.id_dep 
      left join logistik.logistik_gudang g on g.gudang_id = a.id_gudang 
      left join logistik.logistik_opname h on a.id_opname = h.opname_id ";
     $sql .= " where ".$sql_where;
     $sql .= " and h.opname_flag='M' and b.item_aktif='y' and stok_item_flag = 'O' and ".$sql_where;
     $sql .= " order by b.item_nama";  
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataTable = $dtaccess->FetchAll($rs);

     $periode = $_POST["id_periode"];
     $sql = "SELECT * from logistik.logistik_penerimaan_periode WHERE penerimaan_periode_id = '$periode' order by penerimaan_periode_tanggal_awal desc limit 1";
     $periodeSeb = $dtaccess->Fetch($sql);
      }
    // echo $sql;
    $sql ="select a.stok_dep_saldo, b.gudang_nama from logistik.logistik_stok_dep a
           left join logistik.logistik_gudang b on b.gudang_id = a.id_gudang";
    $sql .= " where a.id_item =".QuoteValue(DPE_CHAR,$_POST["item_id"]);
    if($_POST["id_dep"] && $_POST["id_dep"]!="--"){
    $sql .= " and a.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_dep"]);
    }
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    $dataStok = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  
  $PageHeader = "LAPORAN STOK OPNAME";

  $tableHeader = 'Laporan Stok Opname';
  
  $sql = "select * from global.global_auth_user where id_rol <> '2'"; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 

    $sql = "select * from logistik.logistik_gudang where id_dep =".QuoteValue(DPE_CHAR,$depId)." and gudang_id = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])."
    and  (gudang_flag = 'M' or gudang_flag is null or gudang_flag='') order by gudang_nama asc"; 
  $rs = $dtaccess->Execute($sql);            
  $pilihGudang = $dtaccess->FetchAll($rs);

   //-- bikin combo box untuk Tujuan --//
    $sql = "select * from logistik.logistik_gudang where id_dep =".QuoteValue(DPE_CHAR,$depId)."
            and  (gudang_flag = 'M' or gudang_flag is null or gudang_flag='') order by gudang_nama asc"; 
    $rs = $dtaccess->Execute($sql);            
    $dataGudang = $dtaccess->FetchAll($rs);

    $id_petugas=$_POST["id_petugas"];
    $tglAwal=format_date($_POST["tanggal_awal"]);
    $tglAkhir=$_POST["tanggal_akhir"];
    $penjualanTipe=$_POST["penjualan_tipe"];
  
          //Data Klinik
          /*$sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);*/
            if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_opname.xls');
     }
          
      if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
   
   if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
     
    $year = date('Y')+5;
    //echo $year;
    $a=0;
    $tahun[0] = $view->RenderOption("","[Pilih Tahun]",$show);
      for($i=2010;$i<=$year;$i++){
             if($_POST["tahun"]==$i) $show = "selected";
             $tahun[$a+1] = $view->RenderOption($i,$i,$show);
             $a++;   
             unset($show);            
        }
     
?>
<?php if(!$_POST["btnExcel"]) {?>


<?php }?>

<script language="Javascript">
<?php $plx->Run(); ?>

function CariPeriode(id){ 
  document.getElementById('div_periode').innerHTML = GetPeriode(id,'type=r');
}

 var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
}
  
<?php if($_x_mode=="cetak"){ ?> 
  BukaWindow('opname_cetak.php?tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&klinik=<?php echo $_POST["klinik"];?>&id_gudang=<?php echo $_POST["id_gudang"];?>&id_periode=<?php echo $_POST["id_periode"];?>','Opname');
  //document.location.href='<?php echo $thisPage;?>';
<?php } ?>
function CheckDataSave(frm)
{  
  
if(!document.getElementById('id_periode').value || document.getElementById('id_periode').value=='--'){
    alert('Periode harus dipilih!');
    document.getElementById('id_periode').focus();
    return false;
  }
if(!document.getElementById('id_gudang').value || document.getElementById('id_gudang').value=='--'){
    alert('Gudang harus dipilih!');
    document.getElementById('id_gudang').focus();
    return false;
  }
    
  return true;
          
}

</script>
  <body class="nav-md">
<?php if(!$_POST["btnExcel"]) { ?>

  <?php require_once($LAY."header.php") ?>


    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Laporan Stok Opname <?php echo $pilihGudang['gudang_nama']; ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
              <input name="tanggal_awal" type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
            <div class='input-group date' id='datepicker2'>
              <input  name="tanggal_akhir"  type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>             
            </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Tahun </label>
          <div>
            <?php echo $view->RenderComboBox("tahun","tahun",$tahun,null,null,"onchange=\"javascript:return CariPeriode(document.getElementById('tahun').value);\"");?>
      </div>
    </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;Periode </label>
          <div>
            <div id="div_periode"><?php echo GetPeriode($_POST["tahun"]);?></div>
            <!--<select name="id_periode" id="id_periode" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
              <option value="--">[- Pilih Periode -]</option>
                <?php for($i=0,$n=count($dataPeriode);$i<$n;$i++) { ?>
                 <option value="<?php echo $dataPeriode[$i]["penerimaan_periode_id"];?>" <?php if($_POST["id_periode"]==$dataPeriode[$i]["penerimaan_periode_id"]) echo "selected";?>><?php echo $dataPeriode[$i]["penerimaan_periode_nama"];?></option>
                <?php } ?>               
            </select>-->
      </div>
    </div>
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;&nbsp;Gudang&nbsp;</label>
      <div>
        <select name="id_gudang" id="id_gudang" class="form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
          <option value="--">[- Pilih Gudang -]</option>
            <?php for($i=0,$n=count($dataGudang);$i<$n;$i++) { ?>
             <option value="<?php echo $dataGudang[$i]["gudang_id"];?>" <?php if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) echo "selected";?>><?php echo $dataGudang[$i]["gudang_nama"];?></option>
            <?php } ?>               
        </select>
      </div>
      </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
          <div>
      <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary" onClick="javascript:return CheckDataSave(this.form);">
      <input type="submit" class="pull-right btn btn-success"  name="btnExcel" value="Export Excel" class="submit">
      <input type="submit" name="btnCetak" id="btnCetak" class="pull-right btn btn-primary"  value="Cetak" class="pull-right btn btn-default">
          </div>
     </div>
</form>

<script type="text/javascript">
   Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    }); 
</script>
<?php } ?>
<?php if($_POST["btnExcel"]) {?>

     <table class="table table-striped table-bordered dt-responsive nowrap" width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr width="100%" class="tableheader">
               <td width="100%" align="center" colspan="<?php echo (count($dataSplit)+6)?>">
               <strong>LAPORAN STOK OPNAME <?php echo $pilihGudang['gudang_nama']; ?><br />
               <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
               
               </strong>
               </td>          
          </tr>
          <tr class="tableheader">
          <td align="left" colspan="<?php echo (count($dataSplit)+6)?>">
          <?php echo $poliNama; ?><br />
          <?php if($_POST["tanggal_awal"]==$_POST["tanggal_akhir"]) { echo "Tanggal : ".$_POST["tanggal_awal"]; } elseif($_POST["tanggal_awal"]!=$_POST["tanggal_akhir"]) { echo "Periode : ".$_POST["tanggal_awal"]." - ".$_POST["tanggal_akhir"]; }  ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               <br />
          </td>
          </tr>
     </table>
<?php }?> 

<table id="opname" class="table table-striped table-bordered" border="0" cellpadding="0" cellspacing="0">
  <thead>
  <tr>
    <th rowspan="2" style="text-align: center;">NO</th>
    <th rowspan="2" style="text-align: center;">Kode</th>
    <th rowspan="2" style="text-align: center;">Nama Obat</th>
    <th rowspan="2" style="text-align: center;">Kel.</th>
    <th colspan="2" style="text-align: center;">Stok Tercatat</th>
    <th colspan="2" style="text-align: center;">Stok Fisik</th>
    <th colspan="2" style="text-align: center;">Selisih</th>
  </tr>
  
  <tr>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
    <th style="text-align: center;">QTy</th>
    <th style="text-align: center;">HPP</th>
  </tr>
</thead>

  <?php
  for($i = 0, $n = count($dataTable); $i < $n; $i++){
    $item_id = $dataTable[$i]['item_id'];
    $gudang= $dataTable[$i]['gudang_nama'];
    $depo = $dataTable[$i]['dep_dep'];
    $tglOpname = $dataTable[$i]['stok_item_create'];

    $sql = "select count(stok_item_id) as history from logistik.logistik_stok_item where id_gudang = '$depo' and stok_item_flag != 'A' and date(stok_item_create) < '$tglOpname' and id_item = '$item_id'";
    $hstr = $dtaccess->Fetch($sql);
    $history = $hstr['history'];

    $sql = "SELECT * from logistik.logistik_hpp_periode where id_item = '$item_id' and id_periode = '$periode'";
    $hppPeriode = $dtaccess->Fetch($sql);

    $hppPeriode["hpp"] = ($dataTable[$i]['stok_item_hpp']) ? $dataTable[$i]['stok_item_hpp'] : $hppPeriode["hpp"];

    $awal = ($history == 0) ? $dataTable[$i]["stok_item_saldo"] : $dataTable[$i]["stok_item_saldo"] - $dataTable[$i]["stok_item_jumlah"];
    $selisih = ($history == 0) ? 0 :$dataTable[$i]["stok_item_jumlah"];
    ?>
  <tr>
    <td><?=$i+1?></td>
    <td><?=$dataTable[$i]['item_kode']?></td>
    <td><?=$dataTable[$i]['item_nama']?></td>
    <td><?=$dataTable[$i]['grup_item_nama']?></td>
    <td><?=number_format($awal, 2, ',', '.')?></td>
    <td><?=number_format($awal*$hppPeriode["hpp"], 0, ',', '.')?></td>

    <?php
    $hpp = $awal*$hppPeriode["hpp"];
    $opname += $dataTable[$i]["stok_item_saldo"];
    $hppOpname += $hpp;
    ?>

    <td><?=number_format($dataTable[$i]["stok_item_saldo"], 2, ',', '.')?></td>
    <td><?=number_format($dataTable[$i]["stok_item_saldo"]*$hppPeriode["hpp"], 0, ',', '.')?></td>

    <td><?=number_format($selisih, 2, ',', '.')?></td>
    <td><?=number_format($hppPeriode["hpp"]*$selisih, 0, ',', '.')?></td>
  </tr>
    <?php
    $hpp_akhir = $dataTable[$i]["stok_item_saldo"]*$hppPeriode["hpp"];
    $hpp_selisih = $hppPeriode["hpp"]*$selisih;
    $akhir += $dataTable[$i]["stok_item_saldo"];
    $hppAkhir += $hpp_akhir;
    $hppSelisih += $hpp_selisih;
    $jumlahSelisih += $selisih;
  }
  ?>
  <tr>
    <td></td>
    <td></td>
    <td>Jumlah</td>
    <td></td>
    <td><!-- <?=number_format($opname, 2, ',', '.')?> --></td>
    <td><?=number_format($hppOpname, 0, ',', '.')?></td>
    <td><?=number_format($akhir, 2, ',', '.')?></td>
    <td><?=number_format($hppAkhir, 0, ',', '.')?></td>
    <td><?=number_format($jumlahSelisih, 2, ',', '.')?></td>
    <td><?=number_format($hppSelisih, 0, ',', '.')?></td>
  </tr>
  
</table>

<?php if(!$_POST["btnExcel"]) {?>
</div>
</div>
      <!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
      <tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
      <td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
      </table>-->
<?php }?>
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <script>
      $(document).ready(function() {
      $('#opname').DataTable({
        "paging": false,
        "searching": false,
        "info": false,
        "order": false,
        "fixedHeader": true
      });
    });
    </script>
<?php require_once($LAY."js.php") ?>
</div>
</div>
</div>
</div>
</body>
</html>                                   
 
