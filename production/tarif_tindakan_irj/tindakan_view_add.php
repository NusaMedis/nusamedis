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
	   $findPageBeban = "akun_prk_beban.php?";
 
     if(!$auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } 

     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
  if($_GET["id_kategori_tindakan_header_instalasi"])  $_POST["id_kategori_tindakan_header_instalasi"] = & $_GET["id_kategori_tindakan_header_instalasi"];
  if($_GET["id_kategori_tindakan_header"])  $_POST["id_kategori_tindakan_header"] = & $_GET["id_kategori_tindakan_header"];
  if($_GET["biaya_kategori"])  $_POST["biaya_kategori"] = & $_GET["biaya_kategori"];
  

  $backPage = "tindakan_view.php?id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
 

  
     if($_GET["id"] || $_GET["id_dep"]) 
     {
     	
			$biayaId = $enc->Decode($_GET["id"]);
        
		  if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
          }

          $sql = "select a.biaya_nama, a.biaya_urut, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, 
              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, f.no_prk as no_prk_beban, 
              g.kategori_tindakan_header_nama
              from klinik.klinik_biaya a 
              join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              where a.biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
          $dtaccess->Clear($rs_edit);                 
 		
      }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnSave"] || $_POST["btnUpdate"])
     { 
     
             
          if($_POST["btnUpdate"])
          {
               $biayaId = & $_POST["biaya_id"];
               $_x_mode = "Edit";
          } 
               $dbTable = " klinik.klinik_biaya_tarif";
               
               $dbField[0] = "biaya_tarif_id";   // PK
               $dbField[1] = "biaya_total";  
               $dbField[2] = "biaya_tarif_jenis";
               $dbField[3] = "id_tahun_tarif";
               $dbField[4] = "id_shift";
               $dbField[5] = "id_tipe_biaya";
               $dbField[6] = "id_kelas";
               $dbField[7] = "biaya_tarif_tgl_awal";
               $dbField[8] = "biaya_tarif_tgl_akhir";
               $dbField[9] = "id_biaya";
               $dbField[10] = "is_cito";

               $biayaTarifId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaTarifId);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["biaya_total"]);   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_tarif_jenis"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_shift"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_tipe_biaya"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
               $dbValue[7] = QuoteValue(DPE_DATE,$_POST["biaya_tarif_tgl_awal"]);
               $dbValue[8] = QuoteValue(DPE_DATE,$_POST["biaya_tarif_tgl_akhir"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["is_cito"]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
   
               $dtmodel->Insert() or die("insert  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);



            if($_POST["biaya_split_jaspel"])
            {
               
               $dbTable = " klinik.klinik_biaya_split";
               
               $dbField[0] = "bea_split_id";   // PK
               $dbField[1] = "id_split";
               $dbField[2] = "bea_split_nominal";
               $dbField[3] = "id_biaya_tarif";
               
               $biayaJaspelId = $dtaccess->GetTransId();  
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaJaspelId);
               $dbValue[1] = QuoteValue(DPE_CHAR,"1");
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_split_jaspel"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$biayaTarifId);
                //print_r($dbValue);
                //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $dtmodel->Insert() or die("update  error");
                  
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
            }
            
        
            if($_POST["biaya_split_sarana"])
            {
               
               $dbTable = " klinik.klinik_biaya_split";
               
               $dbField[0] = "bea_split_id";   // PK
               $dbField[1] = "id_split";
               $dbField[2] = "bea_split_nominal";
               $dbField[3] = "id_biaya_tarif";
               
               $biayaSaranaId = $dtaccess->GetTransId();  
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaSaranaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,"2");
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_split_sarana"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$biayaTarifId);
                //print_r($dbValue);
                //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

               $dtmodel->Insert() or die("update  error");
                  
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
            }

                  
               
 //         header("location:".$backPage);
 //         exit();	
//			} 
			
//      if(($_POST["biaya_kategori"] <> $_POST["biaya_kategori_lama"]) || $_POST["btnSave"]){
        
  //      $sql = "update klinik.klinik_biaya set biaya_urut = null where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
  //      $dtaccess->Execute($sql);
                  
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
//       var_dump($dataDbLog);


       for($i=0,$n=count($dataBiaya);$i<$n;$i++) {       

         $sql = "update klinik.klinik_biaya set biaya_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                where biaya_id=".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
         //echo $sql; die();
         $dtaccess->Execute($sql);

       } //end loopinh   

			//$sql = "update klinik.klinik_biaya set biaya_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
			//$dtaccess->Execute($sql);
			

      echo "<script>document.location.href='".$backPage."';</script>";
      exit(); 
             

     }

     $sql = "select * from klinik.klinik_split a join klinik.klinik_kategori_split_header b
             on a.split_id = b.id_split where 
             b.id_kategori_header = ".QuoteValue(DPE_CHAR,$_GET["id_kategori_tindakan_header"])."
             and  a.split_flag = ".QuoteValue(DPE_CHAR,SPLIT_PERAWATAN)."
             order by b.klinik_kategori_split_header_urut asc";
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);  
  
  //Fungsi untuk Menghapus Tindakan  
  if ($_GET["del"]) 
  {
          $biayaId = $_GET["id_del"];
          
          $sql = "select * from klinik.klinik_folio where id_biaya=".QuoteValue(DPE_CHAR,$biayaId);
          $rs = $dtaccess->Execute($sql);
          $dataFolio = $dtaccess->Fetch($rs);
          
          if(!$dataFolio){
          
           $sql = "delete from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
           //echo $sql; die();
           $dtaccess->Execute($sql);

                    // cek data local //
       $sql = "select * from klinik.klinik_biaya a 
       		  join klinik.klinik_kategori_tindakan b  on b.kategori_tindakan_id=a.biaya_kategori
              left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id=b.id_kategori_tindakan_header 
              left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=c.id_kategori_tindakan_header_instalasi 
              where 1=1  order by d.klinik_kategori_tindakan_header_instalasi_urut,c.kategori_tindakan_header_urut,b.kategori_urut, a.biaya_urut";
       $rs = $dtaccess->Execute($sql);
       $dataBiaya= $dtaccess->FetchAll($rs); 
       //echo $sql;       
//       var_dump($dataDbLog);


       for($i=0,$n=count($dataBiaya);$i<$n;$i++) {       

         $sql = "update klinik.klinik_biaya set biaya_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                where biaya_id=".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
         //echo $sql; die();
         $dtaccess->Execute($sql);

       } //end loopinh   
          
          header("location:".$backPage);
          exit();
          
          } else {
            echo "<script>alert('Tindakan ini tidak bisa dihapus karena sudah dipakai pelayanan!!!');</script>";
            echo "<script>document.location.href='tindakan_view.php?klinik=".$_GET["id_dep"]."&id_kategori_tindakan_header=".$_GET["id_kategori_tindakan_header"]."&id_poli=".$_POST["id_poli"]."&dep_lowest=".$_POST["dep_lowest"]."&id_tahun_tarif=".$_GET["id_tahun_tarif"]."';</script>";
          //exit();
          }
     } //AKHIR HAPUS TINDAKAN
     
    if($_POST["btnUrut"])
     {
       $sql = "select a.biaya_id, a.biaya_nama, a.biaya_urut, a.biaya_kategori, e.poli_urut, b.kategori_urut, c.kategori_tindakan_header_urut, 
              d.klinik_kategori_tindakan_header_instalasi_urut from klinik.klinik_biaya a join klinik.klinik_kategori_tindakan b  on b.kategori_tindakan_id=a.biaya_kategori
              left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id=b.id_kategori_tindakan_header 
              left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=c.id_kategori_tindakan_header_instalasi 
              left join global.global_auth_poli e on e.poli_id=a.id_poli
              where 1=1 and b.id_tahun_tarif='".$_POST["id_tahun_tarif"]."' 
              order by d.klinik_kategori_tindakan_header_instalasi_urut,c.kategori_tindakan_header_urut, b.kategori_urut, e.poli_urut, a.biaya_nama, a.id_kelas";
       $rs = $dtaccess->Execute($sql);
       $dataBiayaUrut = $dtaccess->FetchAll($rs); 
       //echo $sql; die();      

       for($i=0,$n=count($dataBiayaUrut);$i<$n;$i++) {       

         $sql = "update klinik.klinik_biaya set biaya_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                where biaya_id=".QuoteValue(DPE_CHAR,$dataBiayaUrut[$i]["biaya_id"]);
         //echo $sql; die();
         $dtaccess->Execute($sql);

       }
       
       header("location:".$backPage);
       exit();
     }
     
     
     // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

      // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

     // Data Kategori Tindakan Header //
     
     if($_POST['id_kategori_tindakan_header']) $sql_where_tindakan[] = "a.id_kategori_tindakan_header = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_where_tindakan = "select * from  klinik.klinik_kategori_tindakan";
     if($sql_where_tindakan)  $sql_where_tindakan.= " where 1 = 1";
     $sql_where_tindakan .= " order by kategori_urut asc";
     $rs_tindakan = $dtaccess->Execute($sql_where_tindakan);
     $dataKategoriTindakan = $dtaccess->FetchAll($rs_tindakan);
     
     //-- bikin keterangan untuk Tahun Tarif --//
     $sql = "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataTahunTarif = $dtaccess->FetchAll($rs);

     //-- bikin keterangan untuk Master Kelas --//
     $sql = "select * from klinik.klinik_kelas order by kelas_tingkat asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataKelas = $dtaccess->FetchAll($rs);
     
     $tableHeader = "Manajemen - Tambah Tarif Tindakan";

     
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
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
                    <h2>Setup Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">         
            <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Kategori Tindakan Header Instalasi</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
            <select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control"  onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
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
               <select class="select2_single form-control" name="id_kategori_tindakan_header_id" id="id_kategori_tindakan_header"  onKeyDown="return tabOnEnter(this, event);" >
              <option value="--" >[Pilih Kategori Tindakan Header]</option>
              <?php for($a=0,$b=count($dataKategoriTindakanHeader);$a<$b;$a++){ ?>               
              <option value="<?php echo $dataKategoriTindakanHeader[$a]["kategori_tindakan_header_id"] ;?>" <?php if($_POST["id_kategori_tindakan_header"]==$dataKategoriTindakanHeader[$a]["kategori_tindakan_header_id"])echo "selected" ;?> ><?php echo $dataKategoriTindakanHeader[$a]["kategori_tindakan_header_nama"] ;?></option>
              <?php }?>          
              </select>
              </div>
						</div>
						<div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori Tindakan  <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">      
                <select class="select2_single form-control" name="biaya_kategori" id="biaya_kategori"  onKeyDown="return tabOnEnter(this, event);" >
                <option value="--" >[Pilih Kategori Tindakan]</option>
                <?php for($jj=0,$kk=count($dataKategoriTindakan);$jj<$kk;$jj++){ ?>               
                <option value="<?php echo $dataKategoriTindakan[$jj]["kategori_tindakan_id"] ;?>" <?php if($_POST["biaya_kategori"]==$dataKategoriTindakan[$jj]["kategori_tindakan_id"])echo "selected" ;?> ><?php echo $dataKategoriTindakan[$jj]["kategori_tindakan_nama"] ;?></option>
                <?php }?>          
                </select>
                </div>
						</div>
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tindakan Urut  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo $view->RenderTextBox("biaya_urut","biaya_urut","85","100",$_POST["biaya_urut"],"inputField", null,false);?>
						</div>
					  </div>
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Tindakan  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","85","100",$_POST["biaya_nama"],"inputField", null,false);?>
						</div>
					  </div>
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12">Tahun Tarif</label>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <select name="id_tahun_tarif" id="id_tahun_tarif" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
				    		<option class="inputField" value="--" >- Pilih Tahun Tarif -</option>
				     		<?php for($i=0,$n=count($dataTahunTarif);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataTahunTarif[$i]["tahun_tarif_id"];?>"<?php if ($_POST["id_tahun_tarif"]==$dataTahunTarif[$i]["tahun_tarif_id"]) echo"selected"?>><?php echo $dataTahunTarif[$i]["tahun_tarif_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select>
             </div>
            </div>
            <!--
					  <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Rawat <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
               <select name="biaya_tarif_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				    		<option class="inputField" value="" >- Pilih Tipe Rawat -</option>
		    				<option class="inputField" value="TA" <?php if ($_POST["biaya_tarif_jenis"]=="TA") echo"selected"?>>Rawat Jalan&nbsp;</option> 
	   					  <option class="inputField" value="TI" <?php if ($_POST["biaya_tarif_jenis"]=="TI") echo"selected"?>>Rawat Inap&nbsp;</option>
  							<option class="inputField" value="TG" <?php if ($_POST["biaya_tarif_jenis"]=="TG") echo"selected"?>>IGD&nbsp;</option>
							 </select> 
						  </div>
					  </div> -->
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelas</label>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <select name="id_kelas" id="id_kelas" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
				    		<option class="inputField" value="--" >- Pilih Kelas -</option>
				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select>
             </div>
            </div>
				   <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tgl Awal Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_tarif_tgl_awal","biaya_tarif_tgl_awal","85","100",$_POST["biaya_tarif_tgl_awal"],"inputField", null,false);?>
						</div>
					  </div>
            <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tgl Akhir Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_tarif_tgl_akhir","biaya_tarif_tgl_akhir","85","100",$_POST["biaya_tarif_tgl_akhir"],"inputField", null,false);?>
						</div>
					  </div>
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">CITO <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
               <select name="is_cito" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				    		<option class="inputField" value="" >- Pilih CITO -</option>
		    				<option class="inputField" value="C" <?php if ($_POST["is_cito"]=="C") echo"selected"?>>CITO</option> 
	   					  <option class="inputField" value="E" <?php if ($_POST["is_cito"]=="E") echo"selected"?>>Elektif</option>
							 </select> 
						  </div>
					  </div>
				   <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_total","biaya_total","85","100",$_POST["biaya_total"],"inputField", null,false);?>
						</div>
					  </div>
					  <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jasa Pelayanan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_split_jaspel","biaya_split_jaspel","85","100",$_POST["biaya_split_jaspel"],"inputField", null,false);?>
						</div>
					  </div>                                            
            <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jasa Sarana  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_split_sarana","biaya_split_sarana","85","100",$_POST["biaya_split_sarana"],"inputField", null,false);?>
						</div>
					  </div>
            <!--
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Biaya Paket  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                         <select class="select2_single form-control" name="biaya_paket" id="biaya_paket" onKeyDown="return tabOnEnter(this, event);"> 
							<option value="n" <?php if($_POST["biaya_paket"]=="n") echo "selected"; ?>>Tidak</option>
						<option value="y" <?php if($_POST["biaya_paket"]=="y") echo "selected"; ?>>Ya</option>
						</select>
						</div>
					  </div>
					  -->
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button id="btnSave" name="btnSave" type="submit" value="Simpan" class="btn btn-success">Simpan</button>
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
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
						<input type="hidden" name="id_kategori_tindakan_header" id="id_kategori_tindakan_header" value="<?php echo $_POST["id_kategori_tindakan_header"];?>" />
						<input type="hidden" name="dep_posting_poli" id="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"];?>" />
						<input type="hidden" name="biaya_kategori_lama" id="biaya_kategori_lama" value="<?php echo $_POST["biaya_kategori_lama"];?>" />
						<?php echo $view->RenderHidden("id_biaya","id_biaya",$biayaId);?> 
                      <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
						
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
