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
 //    $poli = $auth->GetPoli();
     $userName = $auth->GetUserName();
     
     //DIPATEN SEMENTARA
     $poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  
     
     $jenis = $_GET["jenis_id"];

    $plx = new expAJAX("GetData");
    

function GetData($in_nama=null,$kategori){
	global $dtaccess,$ROOT,$depId,$theDep, $jenis; 

	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");
	//echo "pasien ".$jenis;
//	    $skr=date["Y-m-d"];
      	
	// --- cari data menunya ---
    // $jenis = "2";
	 if($in_nama) $sql_where[] = " UPPER(c.item_nama) like '".strtoupper($in_nama)."%'";
     /*if($jenis && $jenis!="--") $sql_where[] = "d.jenis_id = ".QuoteValue(DPE_NUMERIC,$jenis)."";  */
     if($kategori && $kategori!="--") $sql_where[] = "c.id_kategori = ".QuoteValue(DPE_CHAR,$kategori)."";
     if($sql_where) $sql_where = implode(" and ",$sql_where);       
$dateskrg=date("Y-m-d");
	$sql = "select b.batch_id, b.batch_no, b.batch_tgl_jatuh_tempo, a.stok_batch_dep_saldo ,
          c.item_kode, c.item_id , c.item_nama , c.item_harga_beli, d.jenis_nama 
          from logistik.logistik_stok_batch_dep a 
          left join logistik.logistik_item_batch b on a.id_batch = b.batch_id
          left join logistik.logistik_item c  on a.id_item=c.item_id
          left join global.global_jenis_pasien d on d.jenis_id = c.item_tipe_jenis
          left join logistik.logistik_grup_item e on e.grup_item_id = c.id_kategori";  
  $sql .= " where item_katalog='n' and c.item_aktif='y' and c.item_flag = 'M' and b.id_dep = ".QuoteValue(DPE_CHAR,$depId)." and a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
//  " and batch_tgl_jatuh_tempo >= ".QuoteValue(DPE_date,$skr);
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by c.item_nama asc, b.batch_tgl_jatuh_tempo asc";
    // return $sql;
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;
	
	
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
	
	$tbHeader[0][$counter][TABLE_ISI] = "Harga Beli";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	$tbHeader[0][$counter][TABLE_ISI] = "Harga Jual";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Margin";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {   
	$hargabeli = $dataTable[$i]["item_harga_beli"];
    //cari margin obat
	$sql = "select margin_nilai from apotik.apotik_margin
			where id_jenis_pasien = ".QuoteValue(DPE_NUMERIC,$jenis)."
			and is_aktif ='Y' and ".$hargabeli." >= harga_min and ".$hargabeli.
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
		

		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_harga_beli"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;                        
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($hargajual);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;                        
		
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$margin["margin_nilai"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++; 
		
    		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\',\''.currency_format($hargajual).'\')"/>';            
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    }
	
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}
   //-- bikin combo box untuk Jenis --//
   	$sql = "select jenis_id,jenis_nama from global.global_jenis_pasien where jenis_flag='y' order by jenis_id asc"; 
		$dataJenis = $dtaccess->FetchAll($sql);

   //-- bikin combo box untuk Kategori --//
   	$sql = "select * from logistik.logistik_grup_item where item_flag='M' order by grup_item_nama asc"; 
		$dataGrup = $dtaccess->FetchAll($sql);

?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function Search() {
	//var tglawal = document.getElementById('tanggal_awal').value;
	//var tglakhir = document.getElementById('tanggal_akhir').value;
	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('id_kategori').value;
	//var jk = document.getElementById('_jk').value;

	GetData(nama,kode,'target=dv_hasil');
}

function sendValue(nama,id,harga) {
 
	self.parent.document.getElementById('item_nama').value = nama;
	self.parent.document.getElementById('item_detail').value = id;
	self.parent.document.getElementById('detail_jual').value = harga;
	self.parent.tb_remove();
}



</script>

  <!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<br><br>
			 <div class="col-md-12 col-sm-12 col-xs-12">
				<form name="frmSearch"  class="form-horizontal form-label-left">
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Kategori Barang</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							 <select class="form-control" name="id_kategori" id="id_kategori" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
               <option value="">[- Pilih Kategori -]</option>
            <?php for($i=0,$n=count($dataGrup);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataGrup[$i]["grup_item_id"];?>" <?php if($_POST["id_kategori"]==$dataGrup[$i]["grup_item_id"]) echo "selected";?>><?php echo $dataGrup[$i]["grup_item_nama"];?></option>
						 <?php } ?>               
               </select>
			   </div>
                    </div>  
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Obat</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
						</div>
                    </div>  
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<input type="button" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()" />
							<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="btn btn-default" />
                        </div>
                    </div>  
				</form>
			<div>
<div id="dv_hasil"></div>
<?php echo $view->RenderHidden("jenis_id","jenis_id",$jenis);?>
<?php echo $view->SetFocus("_name",true);?>
</div>