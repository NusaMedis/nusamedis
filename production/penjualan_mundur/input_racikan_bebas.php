<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
     $thisPage = "input_racikan_bebas.php";
     
    if($_GET['kode']){$kode = $enc->Decode($_GET['kode']);}
     else{$kode = $_POST['cust_usr_kode'];}
     
     if($_GET['transaksi']){$penjualanId = $enc->Decode($_GET['transaksi']);}
     else{$penjualanId = $_POST['penjualan_id'];}
     if($_GET['id_pembayaran']){ $pembayaranId = $_GET['id_pembayaran'];}
     else{$pembayaranId = $_POST['id_pembayaran'];}
     if($_GET['jenis_id'])$_POST['jenis_id']=$_GET['jenis_id'];
     if($_GET['item'])$racikanId = $_GET['item'];
    if($_GET['id_reg']){$idReg = $enc->Decode($_GET['id_reg']);}
    elseif ($_GET['idreg']) {
      $idReg = $enc->Decode($_GET['idreg']);
    }
     else{$idReg = $_POST['id_reg'];}
     
     
   // PRIVILLAGE
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     
     $backPage = "penjualan_bebas.php?kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId;
    // echo $backPage;
     //gudangnya
     $poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  
         
//cari master racikan dan stoknya
     $sql = "select b.batch_id, b.batch_no, b.batch_tgl_jatuh_tempo, a.stok_batch_dep_saldo ,a.id_batch,
          c.item_kode, c.item_id , c.item_nama , c.item_harga_jual, c.item_program, d.jenis_nama 
          from logistik.logistik_stok_batch_dep a 
          left join logistik.logistik_item_batch b on a.id_batch = b.batch_id
          left join logistik.logistik_item c  on a.id_item=c.item_id
          left join global.global_jenis_pasien d on d.jenis_id = c.item_tipe_jenis
          left join logistik.logistik_grup_item e on e.grup_item_id = c.id_kategori  
           where item_racikan='y' and c.item_aktif='y' and c.item_flag = 'M' and a.id_gudang =".QuoteValue(DPE_CHAR,$theDep)." and b.id_dep = ".QuoteValue(DPE_CHAR,$depId);   
     $sql .= " order by c.item_nama asc, b.batch_tgl_jatuh_tempo asc";
    // echo $sql;
     $rs = $dtaccess->Execute($sql);     
     $dataRacikan = $dtaccess->FetchAll($rs);   

if($_POST["btnPilih"]){
$addDetailPage = "racikan_pilih_bebas.php?q=".$batchId."&item=".$_POST['id_racikan']."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&id_reg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&jenis_id=".$_POST['jenis_id'];
header('location:'.$addDetailPage);
}

if($_POST["btnNew"]){
	$addDetailPage = "racikan_new_bebas.php?new=1&q=".$batchId."&item=".$itemId."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&id_reg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&jenis_id=".$_POST['jenis_id'];
header('location:'.$addDetailPage);
}
//combo satuan jual
$sql = "select * from logistik.logistik_item_satuan where satuan_tipe ='J'
        order by satuan_nama asc";
$rs = $dtaccess->Execute($sql);
$dataSatuan = $dtaccess->FetchAll($rs);

//combo dosis
$sql = "select * from apotik.apotik_obat_petunjuk
        order by petunjuk_nama asc";
$rs = $dtaccess->Execute($sql);
$dataDosis = $dtaccess->FetchAll($rs);

//combo aturan minum
$sql = "select * from apotik.apotik_aturan_minum
        order by aturan_minum_nama asc";
$rs = $dtaccess->Execute($sql);
$dataAtMinum = $dtaccess->FetchAll($rs);

//combo aturan pakai
$sql = "select * from apotik.apotik_aturan_pakai
        order by aturan_pakai_nama asc";
$rs = $dtaccess->Execute($sql);
$dataAtPakai = $dtaccess->FetchAll($rs);

 ?>
 <!DOCTYPE html>
<html lang="en">
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.11.3.min.js"></script>
<script language="JavaScript">

</script>
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
            <div class="page-title">
              <div class="title_left">
                <h3>Racikan</h3>
              </div>
            </div>
               <div class="clearfix"></div>
               <!-- row filter -->
               <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Input Racikan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
                        <div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Racikan</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                        <select class="form-control" name="id_racikan" id="id_racikan" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                              <option value="">[- Pilih Racikan -]</option>
                            <?php for($i=0,$n=count($dataRacikan);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataRacikan[$i]["item_id"];?>" <?php if($_POST["id_racikan"]==$dataRacikan[$i]["item_id"]) echo "selected";?>><?php echo $dataRacikan[$i]["item_nama"];?></option>
                       <?php } ?>               
                         </select>
                        </div>
                    </div>  
                        
                        <div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">&nbsp;</label>
                              <input type="submit" name="btnPilih" value="Pilih Racikan" class="btn btn-success" />
                              <input type="submit" name="btnNew" value="Tambah Master Racikan" class="btn btn-primary"/>
                              <input type="button" name="btnBack" id="btnBack" value="Kembali" class="btn btn-default" onClick="document.location.href='<? echo $backPage;?>'"/>
                          </div>
                        </div>
                         <div class="clearfix"></div>
                                   <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
                                  <input type="hidden" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $kode;?>" />
                                  <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $idReg;?>" />
                                  <input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
                                  <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $pembayaranId;?>" />
                                  <input type="hidden" name="jenis_id" id="jenis_id" value="<?php echo $_POST['jenis_id'];?>" />

                         </form>
                  </div>
                </div>
              </div>
            </div>
               <!-- row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
                           
                    </form> 

                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!--page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
