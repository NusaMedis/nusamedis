<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
	   require_once($LIB."tampilan.php");		
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   $findPage = "akun_prk.php?";
     $findPage2 = "akun_prk_bpjs.php?";
     $findPage3 = "akun_prk_asuransi.php?";
	   $findPageBeban = "akun_prk_beban.php?";
 
     

     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
  if(!$_POST["id_kategori_tindakan_header_instalasi"] && $_GET["id_kategori_tindakan_header_instalasi"])  $_POST["id_kategori_tindakan_header_instalasi"] = & $_GET["id_kategori_tindakan_header_instalasi"];
  if(!$_POST["id_kategori_tindakan_header"] && $_GET["id_kategori_tindakan_header"])  $_POST["id_kategori_tindakan_header"] = & $_GET["id_kategori_tindakan_header"];
  if(!$_POST["biaya_kategori"] && $_GET["biaya_kategori"])  $_POST["biaya_kategori"] = & $_GET["biaya_kategori"];
  

  $backPage = "tindakan_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
 

  
     if($_GET["id"] || $_GET["id_dep"]) 
     {
     	
			$biayaId = $enc->Decode($_GET["id"]);
        // echo $biayaId;
		  if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
          }

          $sql = "select a.*, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, 
              d.kegiatan_kategori_nama, e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, f.no_prk as no_prk_beban, h.nama_prk as prk_nama_bpjs, h.no_prk as prk_no_bpjs, i.nama_prk as prk_nama_asuransi, i.no_prk as prk_no_asuransi, i.id_prk as id_prk_asuransi, h.id_prk as id_prk_bpjs, e.id_prk, id_kategori_tindakan_header_instalasi,
              g.kategori_tindakan_header_nama
              from klinik.klinik_biaya a
              join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
              left join gl.gl_perkiraan h on h.id_prk = a.id_prk_bpjs
              left join gl.gl_perkiraan i on i.id_prk = a.id_prk_asuransi
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
          $dtaccess->Clear($rs_edit);                 
          
          // echo $sql;
          
          $_POST['id_prk'] = $row_edit['id_prk'];
          $_POST['prk_no'] = $row_edit['no_prk'];
          $_POST['prk_nama'] = $row_edit['nama_prk'];
          $_POST['id_prk_bpjs'] = $row_edit['id_prk_bpjs'];
          $_POST['prk_no_bpjs'] = $row_edit['prk_no_bpjs'];
          $_POST['prk_nama_bpjs'] = $row_edit['prk_nama_bpjs'];
          $_POST['id_prk_asuransi'] = $row_edit['id_prk_asuransi'];
          $_POST['prk_no_asuransi'] = $row_edit['prk_no_asuransi'];
          $_POST['prk_nama_asuransi'] = $row_edit['prk_nama_asuransi'];
          $_POST['id_kategori_tindakan_header'] = $row_edit['id_kategori_tindakan_header'];
          $_POST['id_kategori_tindakan_header_instalasi'] = $row_edit['id_kategori_tindakan_header_instalasi'];
          $_POST['biaya_kategori'] = $row_edit['biaya_kategori'];
      }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnSave"] || $_POST["btnUpdate"])
     { 
     
             
          if($_POST["btnUpdate"])
          {
               // $biayaId = & $_POST["biaya_id"];
               $_x_mode = "Edit";
          } 
               $dbTable = " klinik.klinik_biaya";
               
               $dbField[0] = "biaya_id";   // PK
               $dbField[1] = "biaya_nama";  
               $dbField[2] = "biaya_kode";
               $dbField[3] = "biaya_kategori";
               $dbField[4] = "id_dep";
               $dbField[5] = "id_kegiatan_kategori";
               $dbField[6] = "biaya_urut";
               $dbField[7] = "biaya_kode_kategori";
               $dbField[8] = "id_prk_bpjs";
               $dbField[9] = "id_prk_asuransi";
               $dbField[10] = "id_prk";
               $dbField[11] = "biaya_jenis_sem";
               $dbField[12] = "biaya_jenis";

               // if($_POST["dep_posting_poli"]=="y")
               // {  
               //   $dbField[7] = "id_poli";
               //   $dbField[8] = "id_prk";
               //   $dbField[9] = "biaya_paket";
               //   $dbField[10] = "id_prk_beban";
               //   $dbField[11] = "biaya_jenis";
               //   $dbField[12] = "biaya_jenis_sem";
               //   $dbField[13] = "id_prk_bpjs";
               //   $dbField[14] = "id_prk_asuransi";
                  
               // } 
               // else 
               // {
               //   $dbField[8] = "id_prk";
               //   $dbField[9] = "biaya_paket";
               //   $dbField[10] = "id_prk_beban";
               //   $dbField[11] = "biaya_jenis";
               //   $dbField[12] = "biaya_jenis_sem";
               //   $dbField[13] = "id_prk_bpjs";
               //   $dbField[14] = "id_prk_asuransi";
               // }

			          // jika biaya nya baru , maka insert nomer urut //
                $sql = "select max(biaya_urut) as total from klinik.klinik_biaya";
                $rs = $dtaccess->Execute($sql);
                $Maxs = $dtaccess->Fetch($rs);
                $MaksUrut = ($Maxs["total"]+1);
                if (!$_POST["biaya_urut"]) $_POST["biaya_urut"] = $MaksUrut;
                //echo "urut ".$_POST["biaya_urut"];  
               if ($_POST['biaya_id'] == '') {
                 $_POST['biaya_id'] = $dtaccess->GetTransId();
               }
               // echo "<pre>";
               // print_r ($_POST);
               // echo "</pre>";  
               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST['biaya_id']);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]);   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_kode"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["biaya_kategori"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_dep"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_kegiatan_kategori"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["biaya_urut"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["biaya_kode_kategori"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_prk_bpjs"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_prk_asuransi"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_prk"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis_sem"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]);
               // if($_POST["dep_posting_poli"]=="y")
               // {
               //   $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               //   $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_prk"]);
               //   $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["biaya_paket"]);
               //   $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_prk_beban"]); 
               //   $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]); 
               //   $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis_sem"]);  
               //   $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_prk_bpjs"]);
               //   $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_prk_asuransi"]);             
               // } 
               // else 
               // {
               //   $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_prk"]);
               //   $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["biaya_paket"]);
               //   $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_prk_beban"]);
               //   $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]); 
               //   $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis_sem"]);  
               //   $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_prk_bpjs"]);
               //   $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_prk_asuransi"]);
               // }              
                // print_r($dbValue);
                // die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
    /*                            
                    // cek data local //
       $sql = "select * from klinik.klinik_biaya a 
              join klinik.klinik_kategori_tindakan b  on b.kategori_tindakan_id=a.biaya_kategori
              left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id=b.id_kategori_tindakan_header 
              left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=c.id_kategori_tindakan_header_instalasi 
              where 1=1  
              order by klinik_kategori_tindakan_header_instalasi_urut,kategori_tindakan_header_urut,b.kategori_urut, biaya_urut, biaya_nama";
       $rs = $dtaccess->Execute($sql);
       $dataBiaya= $dtaccess->FetchAll($rs); 
       //echo $sql;       

       for($i=0,$n=count($dataBiaya);$i<$n;$i++) {       

         $sql = "update klinik.klinik_biaya set biaya_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                where biaya_id=".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
         //echo $sql; die();
         $dtaccess->Execute($sql);

       } //end loopinh   

	*/
      echo "<script>document.location.href='".$backPage."';</script>";
      exit(); 
             

     }

  //Fungsi untuk Menghapus Tindakan  
  if ($_GET["del"]) 
  {
          $biayaId = $_GET["id_del"];      
          
          $sql = "select * from klinik.klinik_biaya_tarif where id_biaya=".QuoteValue(DPE_CHAR,$biayaId);
          $rs = $dtaccess->Execute($sql);
          $dataBiayaTarif = $dtaccess->Fetch($rs);
          
          if(!$dataBiayaTarif)
          {          
              $sql = "select * from klinik.klinik_folio where id_biaya=".QuoteValue(DPE_CHAR,$biayaId);
              $rs = $dtaccess->Execute($sql);
              $dataFolio = $dtaccess->Fetch($rs);
          } 
          else
          {
            echo "<script>alert('Tindakan ini tidak bisa dihapus karena sudah masih terdapat biaya tarif !!!');</script>";
            echo "<script>document.location.href='tindakan_view.php?klinik=".$_GET["id_dep"]."&id_kategori_tindakan_header=".$_GET["id_kategori_tindakan_header"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."&id_tahun_tarif=".$_GET["id_tahun_tarif"]."';</script>";
          }
          
          if(!$dataFolio && !$dataBiayaTarif){
          
           $sql = "delete from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
           //echo $sql; die();
           $dtaccess->Execute($sql);

          header("location:".$backPage);
          exit();
          
          } else {
            echo "<script>alert('Tindakan ini tidak bisa dihapus karena sudah dipakai pelayanan!!!');</script>";
            echo "<script>document.location.href='tindakan_view.php?klinik=".$_GET["id_dep"]."&id_kategori_tindakan_header=".$_GET["id_kategori_tindakan_header"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."&id_tahun_tarif=".$_GET["id_tahun_tarif"]."';</script>";
          //exit();
          }
     } //AKHIR HAPUS TINDAKAN
     //-- bikin combo box untuk kategori kegiatan --//
     $sql = "select * from klinik.klinik_kegiatan_kategori_tindakan order by kegiatan_kategori_urut";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataKegiatanKategoriTindakan = $dtaccess->FetchAll($rs);


     //-- bikin combo box untuk jenis tindakan --//
     $sql = "select * from klinik.klinik_jenis_tindakan order by jenis_tindakan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataJenisTindakan = $dtaccess->FetchAll($rs);

     //-- bikin combo box untuk jenis INACBG --//
     $sql = "select * from klinik.klinik_jenis_inacbg order by jenis_inacbg_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataJenisInacbg = $dtaccess->FetchAll($rs);
     
     // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

     if($_POST['id_kategori_tindakan_header']) $sql_where_tindakan[] = "id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_tindakan = "select * from  klinik.klinik_kategori_tindakan where 1=1";
     if($sql_where_tindakan)  $sql_tindakan .= " and ".implode(" and ",$sql_where_tindakan);
     $sql_tindakan .= " order by kategori_urut asc";
    //echo  $sql_where_tindakan;
     $rs_tindakan = $dtaccess->Execute($sql_tindakan);
     $dataKategoriTindakan = $dtaccess->FetchAll($rs_tindakan);

		for ($i = 0, $n = count($dataKategoriTindakanHeaderInstalasi); $i < $n; $i++) {
		  unset($show);
		  if ($_POST["id_kategori_tindakan_header"] == $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"]) $show = "selected";
		  $opt_header[$i] = $view->RenderOption($dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"], $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_nama"], $show);
		  $opt_kategori[0] = $view->RenderOption("--", "[Pilih Header]", $show);
		}

		for ($i = 0, $n = count($dataKategoriTindakan); $i < $n; $i++) {
		  unset($show);
		  if ($_POST["biaya_kategori"] == $dataKategoriTindakan[$i]["kategori_tindakan_id"]) $show = "selected";
		  $opt_kategori[$i] = $view->RenderOption($dataKategoriTindakan[$i]["kategori_tindakan_id"], $dataKategoriTindakan[$i]["kategori_tindakan_nama"], $show);
		}

      // Data Kategori Tindakan Header //

     // Data Kategori Tindakan Header //
     

     
?>

<!DOCTYPE html>
<html lang="en">
	<?php require_once($LAY."header.php"); ?>
	<script type="text/javascript">
		function tindakanHeader(){
			var instalasi = $('#id_kategori_tindakan_header_instalasi').val();
			// var header = $('#kategori_tindakan_header').val();
      if (instalasi) {
        $.ajax({
          type: 'POST',
          url: 'data.php',
          data: 'instalasi=' + instalasi,
          success: function(html) {
            $('#id_kategori_tindakan_header').html(html);
          }
        });
      } else {
        $('#id_kategori_tindakan_header').html('<option value="">Pilih Kategori Tindakan Header Instalasi Terlebih Dahulu</option>');
      }
		}
    function kategoriTindakan(){
			// var instalasi = $('#id_kategori_tindakan_header_instalasi').val();
			var header = $('#id_kategori_tindakan_header').val();
			console.log(header);
      if (header) {
        $.ajax({
          type: 'POST',
          url: 'data2.php',
          data: 'header=' + header,
          success: function(html) {
            $('#biaya_kategori').html(html);
          }
        });
      } else {
        $('#biaya_kategori').html('<option value="">Pilih Kategori Tindakan Header Instalasi Terlebih Dahulu</option>');
      }
		}
	</script>
	<body class="nav-md">
		<div class="container body">
			<div class="main_container">
				
				<?php require_once($LAY."sidebar.php"); ?>
				<!-- top navigation -->
				<?php require_once($LAY."topnav.php"); ?>
				<!-- /top navigation -->
				<!-- page content -->
				<div class="right_col" role="main">
					<div class="">
						<div class="page-title">
							<div class="title_left">
								<h3>Manajemen</h3>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_panel">
									<div class="x_title">
										<h2>Setup Tindakan</h2>
										<span class="pull-right"><?php echo $tombolAdd; ?></span>
										<div class="clearfix"></div>
									</div>
									<div class="x_content">
										<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Kategori Tindakan Header Instalasi</label>
											<div class="col-md-6 col-sm-6 col-xs-12">
												<select name="id_kategori_tindakan_header_instalasi" id="id_kategori_tindakan_header_instalasi" class="select2_single form-control"  onchange="tindakanHeader()" required>
													<option class="inputField" value="" >- Pilih Kategori Tindakan Header Instalasi-</option>
													<?php for($i=0,$n=count($dataKategoriTindakanHeaderInstalasi);$i<$n;$i++){ ?>
													<option class="inputField" value="<?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"];?>"<?php if ($_POST["id_kategori_tindakan_header_instalasi"]==$dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"];?>&nbsp;</option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori Tindakan Header <span class="required">*</span>
										</label>
										<div class="col-md-6 col-sm-6 col-xs-12">
											<?php echo $view->RenderComboBox("id_kategori_tindakan_header", "id_kategori_tindakan_header", $opt_header, "inputfield", null, "OnChange=kategoriTindakan()"); ?>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori Tindakan <span class="required">*</span>
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
										<?php echo $view->RenderComboBox("biaya_kategori", "biaya_kategori", $opt_kategori, "inputfield", null); ?>
									</div>
								</div>
								
								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jenis Tindakan<span class="required">*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<select class="select2_single form-control" name="biaya_jenis_sem" id="biaya_jenis_sem"  required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')">
										<option value="" >[Pilih Jenis Tindakan]</option>
										<?php for($jj=0,$kk=count($dataJenisTindakan);$jj<$kk;$jj++){ ?>
										<option value="<?php echo $dataJenisTindakan[$jj]["jenis_tindakan_kode"] ;?>" <?php if($_POST["biaya_jenis_sem"]==$dataJenisTindakan[$jj]["jenis_tindakan_id"])echo "selected" ;?> ><?php echo $dataJenisTindakan[$jj]["jenis_tindakan_nama"] ;?></option>
										<?php }?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Variable INACBG<span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-12">
								<select class="select2_single form-control" name="biaya_jenis" id="biaya_jenis"  required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')">
									<option value="" >[Pilih Variable INACBG]</option>
									<?php for($jj=0,$kk=count($dataJenisInacbg);$jj<$kk;$jj++){ ?>
									<option value="<?php echo $dataJenisInacbg[$jj]["jenis_inacbg_id"] ;?>" <?php if($_POST["biaya_jenis"]==$dataJenisInacbg[$jj]["jenis_inacbg_id"])echo "selected" ;?> ><?php echo $dataJenisInacbg[$jj]["jenis_inacbg_nama"] ;?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tindakan Urut</label>
							<div class="col-md-6 col-sm-6 col-xs-12">
								<?php echo $view->RenderTextBox("biaya_urut","biaya_urut","85","100",$_POST["biaya_urut"],"inputField", null,false);?>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Tindakan  <!-- <span class="required">*</span> -->
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input id="biaya_kode_kategori" name="biaya_kode_kategori" value="<?php echo $_POST["biaya_kode_kategori"];?>" class="form-control col-md-7 col-xs-12" data-validate-length-range="6" type="text">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Tindakan  <span class="required">*</span>
					</label>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<input id="biaya_nama" name="biaya_nama" value="<?php echo $_POST["biaya_nama"];?>" class="form-control col-md-7 col-xs-12" data-validate-length-range="6" required="required" type="text">
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Prk Pendapatan  <span class="required">*</span>
				</label>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<?php echo $view->RenderTextBox("prk_no","prk_no","25","100",$_POST["prk_no"],"inputField",false,false);?>
					<?php echo $view->RenderTextBox("prk_nama","prk_nama","50","100",$_POST["prk_nama"],"inputField",false,false);?>
					<input type="hidden" name="id_prk" id="id_prk" value="<?php echo $_POST["id_prk"];?>" />
					<a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">
					<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a></td>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Prk Pendapatan  BPJS<span class="required">*</span>
			</label>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<?php echo $view->RenderTextBox("prk_no_bpjs","prk_no_bpjs","25","100",$_POST["prk_no_bpjs"],"inputField",false,false);?>
				<?php echo $view->RenderTextBox("prk_nama_bpjs","prk_nama_bpjs","50","100",$_POST["prk_nama_bpjs"],"inputField",false,false);?>
				<input type="hidden" name="id_prk_bpjs" id="id_prk_bpjs" value="<?php echo $_POST["id_prk_bpjs"];?>" />
				<a href="<?php echo $findPage2;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">
				<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a></td>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Prk Pendapatan  Asuransi<span class="required">*</span>
		</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<?php echo $view->RenderTextBox("prk_no_asuransi","prk_no_asuransi","25","100",$_POST["prk_no_asuransi"],"inputField",false,false);?>
			<?php echo $view->RenderTextBox("prk_nama_asuransi","prk_nama_asuransi","50","100",$_POST["prk_nama_asuransi"],"inputField",false,false);?>
			<input type="hidden" name="id_prk_asuransi" id="id_prk_asuransi" value="<?php echo $_POST["id_prk_asuransi"];?>" />
			<a href="<?php echo $findPage3;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">
			<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a></td>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Prk Biaya  <span class="required">*</span>
	</label>
	<div class="col-md-6 col-sm-6 col-xs-12">
		<?php echo $view->RenderTextBox("prk_no_beban","prk_no_beban","25","100",$_POST["prk_no_beban"],"inputField",false,false);?>
		<?php echo $view->RenderTextBox("prk_nama_beban","prk_nama_beban","50","100",$_POST["prk_nama_beban"],"inputField",false,false);?>
		<input type="hidden" name="id_prk_beban" id="id_prk_beban" value="<?php echo $_POST["id_prk_beban"];?>" />
		<a href="<?php echo $findPageBeban;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Prk">
		<img src="<?php echo $ROOT;?>gambar/icon/cari.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Prk" alt="Pilih Prk" /></a></td>
	</div>
</div>
<div class="ln_solid"></div>
<div class="form-group">
<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
	<button class="btn btn-Primary" type="button" onClick="document.location.href='<?php echo $backPage;?>'">Kembali</button>
	<button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
</div>
</div>

<input type="hidden" name="split_1" id="split_1" value="<?php echo $_POST["split_1"];?>" />
<input type="hidden" name="split_2" id="split_2" value="<?php echo $_POST["split_2"];?>" />
<input type="hidden" name="split_3" id="split_3" value="<?php echo $_POST["split_3"];?>" />
<input type="hidden" name="split_4" id="split_4" value="<?php echo $_POST["split_4"];?>" />
<input type="hidden" name="split_5" id="split_5" value="<?php echo $_POST["split_5"];?>" />
<input type="hidden" name="split_6" id="split_6" value="<?php echo $_POST["split_6"];?>" />
<input type="hidden" name="split_7" id="split_7" value="<?php echo $_POST["split_7"];?>" />
<input type="hidden" name="split_8" id="split_8" value="<?php echo $_POST["split_8"];?>" />
<input type="hidden" name="split_9" id="split_9" value="<?php echo $_POST["split_9"];?>" />
<input type="hidden" name="split_10" id="split_10" value="<?php echo $_POST["split_10"];?>" />
<input type="hidden" name="id_dep" id="id_dep" value="<?php echo $_POST["klinik"];?>" />
<input type="hidden" name="dep_lowest" id="dep_lowest" value="<?php echo $_POST["dep_lowest"];?>" />
<input type="hidden" name="id_jenis_pasien" id="id_jenis_pasien" value="<?php echo $_GET["id_jenis_pasien"];?>" />
<input type="hidden" name="id_shift" id="id_shift" value="<?php echo $_GET["id_shift"];?>" />
<input type="hidden" name="id_tipe_layanan" id="id_tipe_layanan" value="<?php echo $_GET["id_tipe_layanan"];?>" />
<input type="hidden" name="dep_posting_poli" id="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"];?>" />
<input type="hidden" name="biaya_kategori_lama" id="biaya_kategori_lama" value="<?php echo $_POST["biaya_kategori_lama"];?>" />
<input type="hidden" name="id_tahun_tarif" id="id_tahun_tarif" value="<?php echo $_POST["id_tahun_tarif"];?>" />
<input type="hidden" name="biaya_id" id="biaya_id" value="<?php echo $_POST["biaya_id"];?>" />
<input type="hidden" name="kategori_tindakan_header_instalasi" id="kategori_tindakan_header_instalasi" value="<?php echo $_POST["id_kategori_tindakan_header_instalasi"];?>" />
<input type="hidden" name="kategori_tindakan_header" id="kategori_tindakan_header" value="<?php echo $_POST["id_kategori_tindakan_header"];?>" />
<input type="hidden" name="kategori_tindakan" id="kategori_tindakan" value="<?php echo $_POST["biaya_kategori"];?>" />

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("biaya_id","biaya_id",$biayaId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>
</div>
</div>
</div>
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