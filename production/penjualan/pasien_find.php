<?php
      require_once("../penghubung.inc.php");
      require_once($LIB."login.php");
      require_once($LIB."datamodel.php");
      require_once($LIB."dateLib.php");
      require_once($LIB."expAJAX.php");
      require_once($LIB."tampilan.php");
      
      $dtaccess = new DataAccess();
      $auth = new CAuth();
      $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
      $userId = $auth->GetUserId();
      $depNama = $auth->GetDepNama();
      $depId = $auth->GetDepId();
      $userName = $auth->GetUserName();
      $tgl = date("Y-m-d");
      $skr = date("d-m-Y");
      $tgl2 = date('Y-m-d', strtotime('-7 days', strtotime($tgl)));
      
      $plx = new expAJAX("GetData");
      
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     

function GetData($in_nama,$in_kode,$in_tipe){
	global $dtaccess,$depId,$enc,$table,$poliId,$ROOT, $idPrj, $idCust, $depId, $tgl, $skr, $klinik,$view,$ROOT,$splitsId,$addPage,
  $editPage,$regPage,$thisPage,$PageJenisBiaya,$PageKategoriTindakan,$totalHargaTax,$detPage;
	
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	if(!$in_tglawl) { $in_tglawl = $skr; }
  if(!$in_tglakh) { $in_tglakh = $skr; }
     
     
 	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
 	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%%".$in_kode."%"));;
  
  /*
	$sql = "select a.reg_tipe_rawat 
          from klinik.klinik_registrasi a left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
          where (a.reg_utama is null or a.reg_utama ='') 
          and UPPER(b.cust_usr_kode) like '".$in_kode."' order by a.reg_tanggal desc";   
  //return $sql;        
	$rs = $dtaccess->Execute($sql);     
	$dataPasien = $dtaccess->Fetch($rs);  */
  
  //return $dataPasien["reg_tipe_rawat"];     
	//if($in_jk) $sql_where[] = "UPPER(a.cust_usr_jenis_kelamin) = ".QuoteValue(DPE_CHAR,strtoupper($in_jk));
 
 if ($in_tipe=="J" )
  {
  $sql_where[] = "date(b.reg_tanggal) >= ".QuoteValue(DPE_DATE,date_db($in_tglawl));
  $sql_where[] = "date(b.reg_tanggal) <= ".QuoteValue(DPE_DATE,date_db($in_tglakh)); 
  $sql_where[] = "b.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$in_tipe); 
  $sql_where[] = "b.reg_status != 'E3'"; 
  $sql_where[] = "d.poli_tipe = 'J'";
 }
 elseif ($in_tipe=="G") {
    $tglSekarang = date("Y-m-d");
    $tglKemarin = date('Y-m-d', strtotime("-1 day", strtotime(date("Y-m-d"))));

    $sql_where[] = "b.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$in_tipe); 
    $sql_where[] = "b.reg_status != 'G3'"; 
    $sql_where[] = "d.poli_tipe = 'G'";
    $sql_where[] = "b.reg_tanggal <='$tglSekarang'";
    $sql_where[] = "b.reg_tanggal >='$tglKemarin'" ;
 }
  else
  if ($dataPasien["reg_tipe_rawat"]="I")
  {
  $sql_where[] = "(b.reg_status = 'I2' or reg_status = 'I3')";
  }   
  
      if($sql_where) $sql_where = implode(" and ",$sql_where);
      


	// --- cari data pasien ---
	/*$sql = "select a.cust_usr_id, b.reg_id,a.cust_usr_nama, a.cust_usr_kode , a.cust_usr_alamat, c.jenis_nama, b.id_pembayaran  
          from global.global_customer_user a
          left join klinik.klinik_registrasi b on a.cust_usr_id = b.id_cust_usr 
          left join global.global_jenis_pasien c on c.jenis_id = b.reg_jenis_pasien 
          where (reg_status='M0' or reg_status='M1' or reg_status='E0' or reg_status='E1') and (reg_utama is null or reg_utama ='') 
          and a.id_dep = ".QuoteValue(DPE_CHAR,$depId); */  
          
	$sql = "select a.cust_usr_id, b.reg_id,a.cust_usr_nama, a.cust_usr_kode , a.cust_usr_alamat, c.jenis_nama, b.id_pembayaran, d.poli_nama , b.reg_tanggal,
          f.bed_keterangan, g.kamar_nama, b.reg_tipe_rawat
          from global.global_customer_user a
          left join klinik.klinik_registrasi b on a.cust_usr_id = b.id_cust_usr 
          left join global.global_jenis_pasien c on c.jenis_id = b.reg_jenis_pasien
          left join global.global_auth_poli d on d.poli_id = b.id_poli 
          left join klinik.klinik_rawatinap e on b.reg_id = e.id_reg
          left join klinik.klinik_kamar_bed f on e.id_bed = f.bed_id
          left join klinik.klinik_kamar g on g.kamar_id = e.id_kamar 
          where (b.reg_jenis_pasien = '2') and b.id_pembayaran not in (select b.id_pembayaran from klinik.klinik_pembayaran_det x where b.id_pembayaran = x.id_pembayaran)
          and a.id_dep = ".QuoteValue(DPE_CHAR,$depId)."
          and cust_usr_kode <>'100'";            //SELAIN BPJS
        //$sql .= " b.reg_status = 'E0' ";		 
  if($sql_where) $sql .= " and ".$sql_where;			
	$sql .= " order by a.cust_usr_nama";	
  //return $sql;	
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
  // echo $sql;
  
  //jika tidak ada maka diquery yang rawat inap
	
  if ($dataTable)  //Jika ditemukan pasiennya
  {
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "3%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nomor RM";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Alamat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

  	$tbHeader[0][$counter][TABLE_ISI] = "Klinik/Kamar/Triase";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

  	$tbHeader[0][$counter][TABLE_ISI] = "Tgl Masuk";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".nl2br($dataTable[$i]["cust_usr_alamat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["poli_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++; 

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".format_date($dataTable[$i]["reg_tanggal"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;                                                                                                                                                                                                                                                                                                                                              
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["cust_usr_id"].'\',\''.$dataTable[$i]["cust_usr_kode"].'\',\''.addslashes($dataTable[$i]["cust_usr_nama"]).'\',\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["id_pembayaran"].'\',\''.$dataTable[$i]["reg_tipe_rawat"].'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	}
  else //Jika tidak ditemukan Data Pasiennya
  {
   $str = "<font color='red'>Pasien Tidak DItemukan</font>";
  }
	return $str;
}

//$optionJK[0] = $view->RenderOption("","[All]",$show);
//$optionJK[1] = $view->RenderOption("L","Laki-laki",$show);
//$optionJK[2] = $view->RenderOption("P","Perempuan",$show);

?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id,kode,nama,reg,byr,tipe) {
	self.parent.document.getElementById('id_cust_usr').value = id;
	self.parent.document.getElementById('cust_usr_kode').value = kode;
	self.parent.document.getElementById('cust_usr_nama').value = nama;
  self.parent.document.getElementById('id_reg_lama').value = reg;
  self.parent.document.getElementById('id_pembayaran').value = byr;
  if(tipe == 'G' || tipe == 'J'){
    self.parent.document.getElementById('apotek').value = '33';
  }
  else{
    self.parent.document.getElementById('apotek').value = 'b1b99707e536adf5e57daede3576bb0f';
  }
	//self.parent.document.getElementById('cust_usr_alamat').value = alamat; 
	self.parent.tb_remove();
}

function Search() {
	//var tglawal = document.getElementById('tanggal_awal').value;
	//var tglakhir = document.getElementById('tanggal_akhir').value;
	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('_kode').value; 
  var tipe = document.getElementById('_tipe').value;  
	//var jk = document.getElementById('_jk').value;

  //if(nama=='' && kode=='' )
 // {	   
  //   alert('Salah satu filter pencarian pasien harus diisi');
   // document.getElementById('_kode').focus();
  //}
  //else
  //{
  GetData(nama,kode,tipe,'target=dv_hasil'); 
     
  //}
    
}

function CekData()
{
    if(!document.getElementById('_kode').value || document.getElementById('_nama').value =='0')
    {
      alert('Salah satu filter pencarian pasien harus diisi');
      document.getElementById('_kode').focus();
      return false;                                                                                  e
    }
    
    return true;
}

</script>
<?php require_once($LAY."header.php"); ?>
  <!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<br><br>
			 <div class="col-md-12 col-sm-12 col-xs-12">
				<form name="frmSearch"  class="form-horizontal form-label-left">
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Nomor RM</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<?php echo $view->RenderTextBox("_kode","_kode",60,255,$_POST["_kode"],false,false);?>
                        </div>
                    </div>  
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Pasien</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<?php echo $view->RenderTextBox("_name","_name",60,255,$_POST["_name"],false,false);?>
                        </div>
                    </div> 
          <div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Tipe Rawat</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                         <select class="select2_single form-control" name="_tipe" id="_tipe"  onKeyDown="return tabOnEnter(this, event);" >                                    
                             <option value="J" <?php if($_POST["_tipe"]=='J')echo "selected" ;?> >Rawat Jalan</option>
                             <option value="I" <?php if($_POST["_tipe"]=='I')echo "selected" ;?> >Rawat Inap</option>                                  
                             <option value="G" <?php if($_POST["_tipe"]=='G')echo "selected" ;?> >Rawat Darurat</option>                                  
                        </select>
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
<?php require_once($LAY."js.php") ?>
<?php echo $view->SetFocus("_kode",true);?>
</div>