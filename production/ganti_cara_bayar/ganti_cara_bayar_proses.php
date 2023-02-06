<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
	 
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     
     if($_GET["id_pembayaran_det"]){
     
    $pembdetId = $_GET["id_pembayaran_det"];
    $sql = "select a.reg_id, b.pembayaran_tanggal, b.pembayaran_create, b.pembayaran_jenis, b.pembayaran_id,
                  c.cust_usr_nama, c.cust_usr_kode,e.usr_name, i.*,j.*,
                  d.poli_nama,f.jenis_nama,g.tipe_biaya_nama from klinik.klinik_pembayaran_det i
                  join klinik.klinik_pembayaran b on i.id_pembayaran = b.pembayaran_id left join
                  klinik.klinik_registrasi a on a.reg_id=b.id_reg left join 
                  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id left join 
                  global.global_auth_poli d on d.poli_id = a.id_poli
                  left join global.global_auth_user e on e.usr_id = a.id_dokter
                  left join global.global_jenis_pasien f on a.reg_jenis_pasien = f.jenis_id
                  left join global.global_tipe_biaya g on a.reg_tipe_layanan = g.tipe_biaya_id
                  left join global.global_jenis_bayar j on i.id_jbayar = j.jbayar_id 
                  where a.id_dep =".QuoteValue(DPE_CHAR,$depId)."
                  and i.pembayaran_det_id=".QuoteValue(DPE_CHAR,$pembdetId);              
          //echo $sql; die();
    $dataTable = $dtaccess->Fetch($sql); 
     $_POST["id_jbayar"]=$dataTable["id_jbayar"];
     $_POST["pembayaran_det_tgl"] = $dataTable["pembayaran_det_tgl"];
     $_POST["who_when_update"] = $dataTable["who_when_update"];
     $_POST["pembayaran_det_create"] = $dataTable["pembayaran_det_create"];
       // coa yang lama
      $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
      $rs  = $dtaccess->Execute($sql);
      $coaJenisBayarAwal = $dtaccess->Fetch($rs); 
      
      //cari data gl yang lama
      $sql = "select a.* from gl.gl_buffer_transaksidetil a
              left join gl.gl_buffer_transaksi b on a.tra_id = b.id_tra
              where b.id_pembayaran_det =".QuoteValue(DPE_CHAR,$pembdetId)."
              and a.prk_id =".QuoteValue(DPE_CHAR,$coaJenisBayarAwal["id_prk"]);
      $rs  = $dtaccess->Execute($sql);
      $glBufferAwal = $dtaccess->Fetch($rs);
 //  echo $sql;
   $id_trad= $glBufferAwal["id_trad"];
           
          $sql = "select reg_keterangan,fol_keterangan from klinik.klinik_registrasi a
                  left join klinik.klinik_pembayaran_det b on a.id_pembayaran = b.id_pembayaran
                  left join klinik.klinik_folio c on a.reg_id = c.id_reg and c.id_pembayaran_det = b.pembayaran_det_id              
                  where b.pembayaran_det_id =".QuoteValue(DPE_CHAR,$pembdetId);
          $rs = $dtaccess->Execute($sql);
          $keterangan = $dtaccess->Fetch($rs);      
    }
    
if($_POST["btnSave"]){

      $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
      $rs  = $dtaccess->Execute($sql);
      $coaJenisBayar = $dtaccess->Fetch($rs); 
      
      //update perkiraan gl_bufferdetail
      $sql = "update gl.gl_buffer_transaksidetil set
              prk_id = ".QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"])."
              where id_trad = ".QuoteValue(DPE_CHAR,$_POST["id_trad"]);
      $rs = $dtaccess->Execute($sql);
//echo $sql; die();
$sql = "update klinik.klinik_pembayaran_det set 
        id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar"]).",
        pembayaran_det_tgl = ".QuoteValue(DPE_DATE,date_db($_POST["pembayaran_det_tgl"])).",
        pembayaran_det_create = ".QuoteValue(DPE_DATE,$_POST["pembayaran_det_create"]).",
        who_when_update = ".QuoteValue(DPE_DATE,$_POST["who_when_update"])."                
        where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$_POST["id_pembayaran_det"]);
$rs = $dtaccess->Execute($sql);
//echo $sql; die();
$kembali ="ganti_cara_bayar.php?id_reg=".$_POST["id_reg"]."&pembayaran_id=".$_POST["id_pembayaran"]."&id_pembayaran_det=".$_POST["id_pembayaran_det"];
header('location:'.$kembali);
exit();
}     
    $sql = "select * from global.global_jenis_bayar where id_dep =".QuoteValue(DPE_CHAR,$depId)." and jbayar_status='y' order by jbayar_id asc";
    $rs = $dtaccess->Execute($sql);
    $dataJenis = $dtaccess->FetchAll($rs);
    
    $sql = "select * from global.global_auth_user where id_dep = ".QuoteValue(DPE_CHAR,$depId)."
            order by usr_name asc";
    $rs = $dtaccess->Execute($sql);
    $dataKasir = $dtaccess->FetchAll($rs);
         
?>
<?php  //echo $view->RenderBody("ipad_depans.css",true,"BATAL BAYAR KWITANSI"); ?>
<?php // echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '60%',
'height' : '110%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 


function hapusReg() {
  if(confirm('apakah anda yakin akan menghapus data Transaksi ini???'));
  else return false;
}
</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-md">
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
                    <h2>Ganti Cara Bayar</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
    	<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Nama Pasien</label>
				<div class="col-md-9 col-sm-12 col-xs-12">
					<?php if($dataTable["cust_usr_kode"]=='100' || $dataTable["cust_usr_kode"]=='500'){
          if($keterangan["fol_keterangan"]=='' || $keterangan["fol_keterangan"]==null) {
          echo "<strong>".$keterangan["reg_keterangan"]."</strong>"; }else{
          echo "<strong>".$keterangan["fol_keterangan"]."</strong>";
          }}else{
          echo "<strong>".$dataTable["cust_usr_nama"]."</strong>";}?>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">No RM</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
					<?php echo "<strong>".$dataTable["cust_usr_kode"]."</strong>";?></strong>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">No Kwitansi</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
					<?php echo $dataTable["pembayaran_det_kwitansi"];?>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Tgl Kwitansi</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
          <div class='input-group date' id='datepicker'>
              <input name="pembayaran_det_tgl" type='text' class="form-control" 
              value="<?php if ($_POST['pembayaran_det_tgl']) { echo $_POST['pembayaran_det_tgl']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
			</div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Tgl Transaksi</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
          <div class='input-group date' id='datepicker'>
              <input name="pembayaran_det_create" type='text' class="form-control" 
              value="<?php if ($_POST['pembayaran_det_create']) { echo $_POST['pembayaran_det_create']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
      </div>
    </div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Total Pembayaran</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
					<?php echo currency_format($dataTable["pembayaran_det_total"]);?>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Kasir</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
          <select name="who_when_update" class="form-control" id="who_when_update" onKeyDown="return tabOnEnter(this, event);">
         <?php  for($i=0,$n=count($dataKasir);$i<$n;$i++){ ?>
          <option value="<?php echo $dataKasir[$i]["usr_name"];?>" <?php if($_POST["who_when_update"]==$dataKasir[$i]["usr_name"]) echo "selected"; ?>><?php echo $dataKasir[$i]["usr_name"];?></option>
          <?php } ?>
          </select>					
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">Jenis Bayar</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
        <select name="id_jbayar" class="form-control" id="id_jbayar" onKeyDown="return tabOnEnter(this, event);">		
       				<?php if($depLowest=='n'){ ?><option class="inputField" value="--" >- Pilih Jenis Bayar  -</option><?php } ?>
           				<?php $counter = -1;
           for($i=0,$n=count($dataJenis);$i<$n;$i++){
               unset($spacer); 
					$length = (strlen($dataJenis[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";  
        				?>                                                                      
         	  <option value="<?php echo $dataJenis[$i]["jbayar_id"];?>" <?php if($_POST["id_jbayar"]==$dataJenis[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer." ".$dataJenis[$i]["jbayar_nama"];?></option>
				    <?php } ?>
			    </select>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-3 col-sm-12 col-xs-12">&nbsp;</label>
        <div class="col-md-9 col-sm-12 col-xs-12">
        <input type="submit" name="btnSave" id="btnSave" value="SIMPAN" class="btn btn-primary" onClick="javascript:return CekData();"/>
				<input type="button" name="kembali" id="kembali" value="Kembali" class="btn btn-default" onClick="document.location.href='ganti_cara_bayar.php'";/>
        </div>
			</div>          
<input type="hidden" name="id_pembayaran_det" id="id_pembayaran_det" value="<?php echo $pembdetId;?>" />
<input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $_GET["pembayaran_id"];?>" />
<input type="hidden" name="id_reg" id="id_reg" value="<?php echo $_GET["id_reg"];?>" />
<input type="hidden" name="id_trad" id="id_trad" value="<?php echo $id_trad;?>" />
</form>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>