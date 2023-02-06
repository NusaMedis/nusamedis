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
     
     $thisPage = "input_racikan.php";
     
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
     
     $backPage = "penjualan.php?kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId;
     //echo $backPage;
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

  $sql = "select * from logistik.logistik_item a left join logistik.logistik_item_satuan b on b.satuan_id = a.id_satuan_jual
          where item_id = ".QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
  $rs = $dtaccess->Execute($sql);
  $dataMasterRacik = $dtaccess->Fetch($rs);
  //cari masternya
  $sql = "select a.*, c.satuan_nama from apotik.apotik_detail_racikan a
          left join logistik.logistik_item b on a.id_item =b.item_id
          left join logistik.logistik_item_satuan c on b.id_satuan_jual=c.satuan_id where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
  $rs = $dtaccess->Execute($sql);
  $dataDetailRacik = $dtaccess->FetchAll($rs);


     
if($_POST["btnSave"]){
   
   $_POST["id_racikan"] = & $_POST["id_racikan"];

     for($i=0,$n=count($_POST["id_detail"]);$i<$n;$i++) {
          
            //update detail racikan
      $sql = "update apotik.apotik_detail_racikan set detail_racikan_total =".QuoteValue(DPE_CHAR,StripCurrency($_POST["detail_total"][$i])).",detail_racikan_jumlah =".QuoteValue(DPE_CHAR,StripCurrency($_POST["jumlah"][$i])).", item_harga_jual = ".QuoteValue(DPE_CHAR,StripCurrency($_POST["detail_jual"][$i]))." where
            id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST["id_racikan"])." and id_item = ".QuoteValue(DPE_CHAR,$_POST["id_detail"][$i]);

      $rs = $dtaccess->Execute($sql);
 // echo $sql; die();
            $dbTable = "logistik.logistik_stok_item";
                        $dbField[0]  = "stok_item_id";   // PK
                        $dbField[1]  = "stok_item_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_flag";
                        $dbField[5]  = "stok_item_create";         
                        $dbField[6]  = "stok_item_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_racikan";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jumlah"][$i]));  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_detail"][$i]);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'P');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$_POST['id_racikan']);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

  //update stok_dep
   $sql = "update logistik.logistik_stok_dep set stok_dep_saldo ='0', stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))."
          where id_item = ".QuoteValue(DPE_CHAR,$_POST["id_detail"][$i])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
   $rs = $dtaccess->Execute($sql); 

   $sql = "select batch_id from logistik.logistik_item_batch where id_item = ".QuoteValue(DPE_CHAR,$_POST["id_detail"][$i]);
   $rs = $dtaccess->Execute($sql);
   $dataBatch = $dtaccess->Fetch($rs);                    
  //simpan stok_batch_item
  $dbTable = "logistik.logistik_stok_item_batch";
                        $dbField[0]  = "stok_item_batch_id";   // PK
                        $dbField[1]  = "stok_item_batch_jumlah";
                        $dbField[2]  = "id_item";    
                        $dbField[3]  = "id_gudang";
                        $dbField[4]  = "stok_item_batch_flag";
                        $dbField[5]  = "stok_item_batch_create";         
                        $dbField[6]  = "stok_item_batch_saldo";
                        $dbField[7]  = "id_dep";
                        $dbField[8]  = "id_batch";
                        $dbField[9]  = "id_racikan";
                        
                        $date = date("Y-m-d H:i:s");
                        $stokbatchid = $dtaccess->GetTransID();
                        $dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
                        $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jumlah"][$i]));  
                        $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_detail"][$i]);
                        $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
                        $dbValue[4] = QuoteValue(DPE_CHAR,'P');
                        $dbValue[5] = QuoteValue(DPE_DATE,$date);
                        $dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
                        $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
                        $dbValue[8] = QuoteValue(DPE_CHAR,$dataBatch["batch_id"]);
                        $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
                        
                        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              
                        $dtmodel->Insert() or die("insert  error"); 
                        
                        unset($dbTable);
                        unset($dbField);
                        unset($dbValue);
                        unset($dbKey); 

  //simpan stok_batch_dep
         $sql = "update logistik.logistik_stok_batch_dep set stok_batch_dep_saldo ='0', stok_batch_dep_tgl=".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_batch =".QuoteValue(DPE_CHAR,$dataBatch['batch_id'])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
        $rs = $dtaccess->Execute($sql);

        }

        //update harga racikan
        $sql = "select sum(detail_racikan_total) as total from apotik.apotik_detail_racikan
                where id_nama_racikan = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
        $rs = $dtaccess->Execute($sql);
        $racik = $dtaccess->Fetch($rs);
        //update itemnya
        //hitung harga satuan berdasarkan jumlah yang disiapkan
        if(!$_POST["penjualan_detail_jumlah"]) $_POST["penjualan_detail_jumlah"]="1";
        $hargasatuan = $racik["total"]/StripCurrency($_POST["penjualan_detail_jumlah"]);

        $sql= "update logistik.logistik_item set item_harga_beli = ".QuoteValue(DPE_NUMERIC,$hargasatuan).",
              item_harga_jual = ".QuoteValue(DPE_NUMERIC,$hargasatuan).", id_satuan_jual =".QuoteValue(DPE_CHAR,$_POST["id_satuan_jual"])."
              where item_id = ".QuoteValue(DPE_CHAR,$_POST['id_racikan']);
        $rs = $dtaccess->Execute($sql);
          $dateSekarang = date('Y-m-d H:i:s');

          
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
          $dbField[13]  = "penjualan_detail_dosis_obat";
          $dbField[14]  = "id_aturan_pakai";
          $dbField[15]  = "id_aturan_minum";

               $penjualanId = $dtaccess->GetTransID();
         
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_id"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_racikan"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($hargasatuan));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["penjualan_detail_jumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$hargasatuan);  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["penjualan_detail_jumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          //print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");     
          
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["cbDelete"]);
          unset($_POST["jumlah"]);
          unset($_POST["dosis"]);
          unset($_POST["total"]);
          unset($_POST["id_batch"]);                
          
     
     header("location:".$backPage);
}

if($_POST["btnSimpanRacik"]){
$addDetailPage = "racikan_pilih.php?q=".$batchId."&item=".$itemId."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&idreg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId;
header('location:'.$addDetailPage);
}

if($_POST["btnNew"]){
  $addDetailPage = "racikan_new.php?new=1&q=".$batchId."&item=".$itemId."&kode=".$enc->Encode($kode)."&transaksi=".$enc->Encode($penjualanId)."&id_reg=".$enc->Encode($idReg)."&id_pembayaran=".$pembayaranId."&jenis_id=".$_POST['jenis_id'];
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

<script type="text/javascript">
function GantiHarga(terima,urut) {
    
    var hargajual = document.getElementById('detail_jual_'+urut).value.toString().replace(/\,/g,"");
    var jumlah = document.getElementById('jumlah_'+urut).value.toString().replace(/\,/g,"");

   // alert(jumlah); 
         
    TotalJual = eval(hargajual)*eval(jumlah);
    //alert(TotSisa);
    
    document.getElementById('detail_total_'+urut).value = TotalJual;
    
    
}
</script>
  <?php require_once($LAY."header.php") ?>
  <script type="text/javascript">
    $(function() {
      $('#id_racikan').select2();
    })
  </script>
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
                        <br><br><br>
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
                          <?php if($dataMasterRacik){ ?>
                           <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                              <th class="column-title">No</th>                       
                              <th class="column-title">Nama Ingredient</th>
                              <th class="column-title">Satuan</th>
                              <th class="column-title">Harga Jual</th>
                              <th class="column-title">Quantity</th>
                              <th class="column-title">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataDetailRacik);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <td class=" "><? echo $i+1;?></td> 
                            <td class=" "><? echo $dataDetailRacik[$i]["item_nama"];?></td>
                            <td class=" "><? echo $dataDetailRacik[$i]["satuan_nama"];?></td>
                            <td class=" ">
                              <?php echo $view->RenderTextBox("detail_jual[$i]","detail_jual_$i","5","30",currency_format($dataDetailRacik[$i]["item_harga_jual"]),"curedit", "readonly",true);?>
                               <input type="hidden" name="id_detail[<?php echo $i;?>]" id="id_detail_<?php echo $i;?>" value="<?php echo $dataDetailRacik[$i]["id_item"];?>" /> 
                              </td> 
                            <td class=" "><?php echo $view->RenderTextBox("jumlah[$i]","jumlah_$i","8","30",currency_format($_POST["jumlah"][$i]),"curedit", "", null,'onChange=GantiHarga(this.value,'.$i.')'); ?></td>  
                            <td class=" "><?php echo $view->RenderTextBox("detail_total[$i]","detail_total_$i","5","10",currency_format($_POST["detail_total"][$i]),"curedit", null,true,null);?></td> 
                          </tr>
                           <input type="hidden" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $kode;?>" />
                                  <input type="hidden" name="id_reg" id="id_reg" value="<?php echo $idReg;?>" />
                                  <input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />
                                  <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $pembayaranId;?>" />
                                  <input type="hidden" name="jenis_id" id="jenis_id" value="<?php echo $_POST['jenis_id'];?>" />

                         <? } ?>
                         
                      </tbody>
                    </table> <?php }else{ ?>
          <table><th>Silahkan klik tombol Tambah Master Racikan</th></table>
                    <? } ?>
                           <?php if($dataMasterRacik){ ?>
                           <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                            <tr><th>Nama</th><th><?php echo $dataMasterRacik["item_nama"];?>
                              <input type="hidden" name="id_racikan" id="id_racikan" value="<?php echo $dataMasterRacik["item_id"];?>" />
                            </th></tr>
                            <tr><th>Satuan Jual</th><th><select class="form-control" name="id_satuan_jual" id="id_satuan_jual" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                              <option value="">[- Pilih Satuan -]</option>
                            <?php for($i=0,$n=count($dataSatuan);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataSatuan[$i]["satuan_id"];?>" <?php if($_POST["id_satuan_jual"]==$dataSatuan[$i]["satuan_id"]) echo "selected";?>><?php echo $dataSatuan[$i]["satuan_nama"];?></option>
                       <?php } ?>               
                         </select>
                        </th></tr>
                            <tr><th>Quantity</th><th><?php echo $view->RenderTextBox("penjualan_detail_jumlah","penjualan_detail_jumlah",40,200,$_POST["penjualan_detail_jumlah"],false,null);?></th></tr>
                            <tr><th>Dosis</th><th><select class="form-control" name="id_petunjuk" id="id_petunjuk" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                              <option value="">[- Pilih Dosis -]</option>
                            <?php for($i=0,$n=count($dataDosis);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataDosis[$i]["petunjuk_id"];?>" <?php if($_POST["id_petunjuk"]==$dataDosis[$i]["petunjuk_id"]) echo "selected";?>><?php echo $dataDosis[$i]["petunjuk_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                            <tr><th>Aturan Pakai</th><th><select class="form-control" name="id_aturan_pakai" id="id_aturan_pakai" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                              <option value="">[- Pilih Aturan Pakai -]</option>
                            <?php for($i=0,$n=count($dataAtPakai);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataAtPakai[$i]["aturan_pakai_id"];?>" <?php if($_POST["id_aturan_pakai"]==$dataAtPakai[$i]["aturan_pakai_id"]) echo "selected";?>><?php echo $dataAtPakai[$i]["aturan_pakai_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                            <tr><th>Aturan Minum</th><th><select class="form-control" name="id_aturan_minum" id="id_aturan_minum" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                              <option value="">[- Pilih Aturan Minum -]</option>
                            <?php for($i=0,$n=count($dataAtMinum);$i<$n;$i++) { ?>
                              <option value="<?php echo $dataAtMinum[$i]["aturan_minum_id"];?>" <?php if($_POST["id_aturan_minum"]==$dataAtMinum[$i]["aturan_minum_id"]) echo "selected";?>><?php echo $dataAtMinum[$i]["aturan_minum_nama"];?></option>
                       <?php } ?>               
                         </select></th></tr>
                           </table> 
                           
                    
                    <table><tr><td><input type="submit" name="btnSave" value="Simpan Racikan" class="submit"/></td></tr></table>
                  <? } ?>
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
