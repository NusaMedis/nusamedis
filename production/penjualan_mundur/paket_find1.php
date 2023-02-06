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
    $plx = new expAJAX("GetData");
//      $tuslag = 1000;

    if ($_GET["transaksi"]) $penjualanId=$_GET["transaksi"];
    if ($_POST["penjualan_id"]) $penjualanId=$_POST["penjualan_id"];

//    echo "masuk 1".$penjualanId;

function GetData($in_nama=null,$jenis){
	global $dtaccess,$ROOT,$depId,$theDep;

	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");

	// --- cari data menunya ---
//	   if($in_nama) $sql_where[] = " UPPER(b.item_nama) like '".strtoupper($in_nama)."%'";
     if($jenis && $jenis!="--") $sql_where[] = "a.id_paket = ".QuoteValue(DPE_CHAR,$jenis)."";
     if($sql_where) $sql_where = implode(" and ",$sql_where);

	$sql = "select b.item_nama, a.jumlah_item, b.item_harga_jual, b.item_harga_beli
          from apotik.apotik_paket_item a
          left join logistik.logistik_item b on a.id_item = b.item_id
          left join apotik.apotik_paket c on a.id_paket = c.paket_id";
//  $sql .= " where id_paket";
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by b.item_nama asc";
//  return $sql;
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;

	$counter = 0;

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Nama Obat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Jumlah";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pakai";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Harga Jual";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

/*	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	*/

	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {

    /*$sql = "select a.stok_dep_saldo from logistik.logistik_stok_dep a
    where a.id_item =".QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"])." and id_gudang = '2'
    and id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by a.stok_dep_create desc";
  	$rs = $dtaccess->Execute($sql);
  	$log = $dtaccess->Fetch($rs);*/
$hargabeli = $dataTable[$i]['item_harga_beli'];
          $sql = "select margin_nilai from apotik.apotik_margin
               where is_aktif ='Y' and ".$hargabeli." >= harga_min and ".$hargabeli.
               " <= harga_max ";
          $rs = $dtaccess->Execute($sql);
          $margin = $dtaccess->Fetch($rs);
          $hargajual = ((100+$margin["margin_nilai"])/100)*$dataTable[$i]["item_harga_beli"];

		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jumlah_item"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

     $isian[$i]='<input type="text" size="5" name="penjualan_detail_jumlah" id="penjualan_detail_jumlah" value="">';
	 // $isian[$i]='$view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);';
		$tbContent[$i][$counter][TABLE_ISI] = $isian[$i];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = currency_format($hargajual);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jenis_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

    }

	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);

	return $str;
}
 /*    // --- master jenis pasien ---
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql);
     $dataJenis = $dtaccess->FetchAll($rs);
   // print_r ($dataJenis);
    echo $sql;

		$jenis = $view->RenderOption('--','[ Pilih Semua ]',$show);
  for($i=0,$n=count($dataJenis);$i<$n;$i++){
		unset($show);
		if($_POST["jenis_id"]==$dataJenis[$i]["jenis_id"]) $show = "selected";
		$jenis = $view->RenderOption($dataJenis[$i]["jenis_id"],$dataJenis[$i]["jenis_nama"],$show);
	}  */
   //-- bikin combo box untuk Jenis --//





   	$sql = "select paket_id, paket_nama from apotik.apotik_paket
      order by paket_nama asc";
		$dataJenis = $dtaccess->FetchAll($sql);
     //ambil get
    if ($_GET["transaksi"]) $penjualanId=$_GET["transaksi"];
    if ($_POST["penjualan_id"]) $penjualanId=$_POST["penjualan_id"];
//    echo "masuk 3".$penjualanId;

//Tampilkan data Paket
      if($_POST["id_paket"]) $sql_where[] = "a.id_paket = ".QuoteValue(DPE_CHAR,$_POST["id_paket"]);
      if($sql_where) $sql_where = implode(" and ",$sql_where);
    	$sql = "select b.item_id,c.paket_nama, b.item_nama, a.jumlah_item, b.item_harga_jual, b.item_harga_beli
             from apotik.apotik_paket_item a
             left join logistik.logistik_item b on a.id_item = b.item_id
             left join apotik.apotik_paket c on a.id_paket = c.paket_id";
      $sql .= " where 1=1";
      if($sql_where) $sql .= " and ".$sql_where;
    	$sql .= " order by b.item_nama asc";
    	$rs = $dtaccess->Execute($sql);
    	$dataTable = $dtaccess->FetchAll($rs);


  // echo "masuk 4".$_POST["penjualan_pakai"];
 //   echo "masuk 5".$penjualanId;

   // echo "penjualan detail0".$_POST["penjualan_detail_jumlah"];

     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
          $dateSekarang = date('Y-m-d H:i:s');
        for($i=0,$j=count($dataTable);$i<$j;$i++){
          
          $hargabeli = $dataTable[$i]['item_harga_beli'];
          $sql = "select margin_nilai from apotik.apotik_margin
               where is_aktif ='Y' and ".$hargabeli." >= harga_min and ".$hargabeli.
               " <= harga_max ";
          $rs = $dtaccess->Execute($sql);
          $margin = $dtaccess->Fetch($rs);
          $hargajual = ((100+$margin["margin_nilai"])/100)*$dataTable[$i]["item_harga_beli"];
/*        $sql =" select id_batch from logistik.logistik_stok_batch_dep where
                id_item =".QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"])." and
                stok_batch_dep_saldo >=".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_pakai".$i]))." and
                id_gudang ="QuoteValue(DPE_CHAR,$theDep);
*/
        $sql =" select id_batch from logistik.logistik_stok_batch_dep where
                id_item =".QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"])." and
                id_gudang =".QuoteValue(DPE_CHAR,$theDep);
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
          //$dbField[13]  = "id_fol";

          //$folId = $dtaccess->GetTransID();

          if (!$_POST["btn_edit"])         //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($hargajual));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_pakai".$i]));
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($hargajual*($_POST["penjualan_pakai".$i]))+($_POST["tuslag".$i]*$_POST["penjualan_pakai".$i]));
          $dbValue[6] = QuoteValue(DPE_CHAR,'y');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah"]));
          $dbValue[11] = QuoteValue(DPE_CHAR,$databatch["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["tuslag".$i]));
          //$dbValue[13] = QuoteValue(DPE_CHARKEY,$folId);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");
//print_r($dbValue);
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
     }



     //Tampilkan data Paket
     /*
      if($_POST["id_paket"]) $sql_where[] = "a.id_paket = ".QuoteValue(DPE_CHAR,$_POST["id_paket"]);
      if($sql_where) $sql_where = implode(" and ",$sql_where);

    	$sql = "select b.item_nama, a.jumlah_item, b.item_harga_jual
              from apotik.apotik_paket_item a
              left join logistik.logistik_item b on a.id_item = b.item_id
              left join apotik.apotik_paket c on a.id_paket = c.paket_id";
      $sql .= " where 1=1";
      if($sql_where) $sql .= " and ".$sql_where;
    	$sql .= " order by b.item_nama asc";
    	$rs = $dtaccess->Execute($sql);
    	$dataTable = $dtaccess->FetchAll($rs);      */

?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id,harga,kode,stok,batch,batch_no,batch_exp) {
if(stok > "0") {
  self.parent.document.getElementById('obat_kode').value = kode;
	self.parent.document.getElementById('obat_nama').value = nama;
	self.parent.document.getElementById('obat_id').value = id;
	self.parent.document.getElementById('txtHargaSatuan').value = harga;
	self.parent.document.getElementById('txtHargaTotal').value = harga;
	self.parent.document.getElementById('txtJumlah').value = '1';
	self.parent.document.getElementById('id_batch').value = batch;
	self.parent.document.getElementById('batch_no').value = batch_no;
	self.parent.document.getElementById('batch_tgl_jatuh_tempo').value = batch_exp;
	self.parent.document.getElementById('txtSatuanNom').innerHTML = harga;
	self.parent.document.getElementById('txtIsiTotale').innerHTML = harga;
	self.parent.document.getElementById('txtJumlah').focus();
	self.parent.tb_remove();
}else{
alert('Maaf, item stok kosong ('+stok+')');
}
}

function Search(nama,jenis) {
	GetData(nama,jenis,'target=dv_hasil');

}

</script>

  <!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<br><br>
			 <div class="col-md-12 col-sm-12 col-xs-12">
				<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>"  class="form-horizontal form-label-left">
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">PAKET</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
						<select class="form-control" name="id_paket" id="id_paket" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
               <option value="">[- Pilih Paket -]</option>
            <?php for($i=0,$n=count($dataJenis);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataJenis[$i]["paket_id"];?>" <?php if($_POST["id_paket"]==$dataJenis[$i]["paket_id"]) echo "selected";?>><?php echo $dataJenis[$i]["paket_nama"];?></option>
						 <?php } ?>
               </select>
			   </div>
                    </div>  
					  
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<input type="button" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()" />
						</div>
                    </div> 
						<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
						<?php if ($tutup=='1') { ?> <font color="red">PAKET TELAH TERSIMPAN SILAHKAN CLICK TOMBOL TUTUP</font>   <? } ?>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">

		<tr>
				<td colspan="5">
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="submit" />
          <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form); self.parent.tb_remove();\"");?>
				</td>
			</tr>

    <tr>
       <td width='1%' align="left" class="tableheader">No</td>
       <td width='20%' align="left" class="tablecontent">Nama</td>
       <td width='5%' align="left" class="tablecontent">Jumlah</td>
       <td width='5%' align="left" class="tablecontent">Beli</td>
       <td width='5%' align="left" class="tablecontent">Tuslag</td>
<!--       <td width='5%' align="left" class="tablecontent-odd">Harga</td>
       <td width='5%' align="left" class="tablecontent-odd">Total</td>   -->
    </tr>
    <?php for($i=0,$j=count($dataTable);$i<$j;$i++){ ?>
     <tr>
       <td width='1%' align="right" class="tablecontent-odd"><?php echo $i+1?></td>
       <td width='20%' align="left" class="tablecontent-odd"><?php echo $dataTable[$i]["item_nama"]?></td>
       <td width='5%' align="right" class="tablecontent-odd"><?php echo $dataTable[$i]["jumlah_item"]?></td>
        <td width='3%' align="left" class="tablecontent-odd">
        <input type="text" size="3" name="penjualan_pakai<?php echo $i;?>" id="penjualan_pakai<?php echo $i;?>" value="" />
        </td>
        <td width='3%' align="left" class="tablecontent-odd">
        <input type="text"  size="10" name="tuslag<?php echo $i;?>" id="tuslag<?php echo $i;?>" value="" />
        </td>
<!--              <td width='5%' align="right" class="tablecontent"><?php echo $dataTable[$i]["item_harga_jual"]?></td>
              <td></td> -->
    </tr>
    <? } ?>
		</table>
		<input type="hidden" name="penjualan_id" id="penjualan_id" value="<?php echo $penjualanId;?>" />

<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<?php echo $view->SetFocus("_name",true);?>
						
						</div>
                    </div>  					
				</form>
			<div>
<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
</div>







