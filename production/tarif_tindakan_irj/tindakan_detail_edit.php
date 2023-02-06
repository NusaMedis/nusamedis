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
 
     /*
     if(!$auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
  if($_GET["id_kategori_tindakan_header_instalasi"])  $_POST["id_kategori_tindakan_header_instalasi"] = & $_GET["id_kategori_tindakan_header_instalasi"];
  if($_GET["id_kategori_tindakan_header"])  $_POST["id_kategori_tindakan_header"] = & $_GET["id_kategori_tindakan_header"];
  if($_GET["biaya_kategori"])  $_POST["biaya_kategori"] = & $_GET["biaya_kategori"];
  if($_GET["biaya_id"])  $_POST["biaya_id"] = & $_GET["biaya_id"];
  if(!$_POST["is_cito"]) $_POST["is_cito"] = "E"; //dibuat default elektif
  

  $backPage = "tindakan_detail_view.php?biaya_id=".$_POST["biaya_id"]."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
 

  
     if($_GET["id"] || $_GET["id_dep"]) 
     {
     	
			$biayaTarifId = $enc->Decode($_GET["id"]);
        
		  if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
          }

          $sql = "select a.*
              from klinik.klinik_biaya_tarif a 
              where a.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
          $dtaccess->Clear($rs_edit); 
          
          //-- Ambil data Jaspel --//
          $sql = "select bea_split_id,bea_split_nominal from klinik.klinik_biaya_split where id_split='1' and id_biaya_tarif=".QuoteValue(DPE_CHAR,$biayaTarifId);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
          $dataBiayaJaspel = $dtaccess->Fetch($rs);
          
          //-- Ambil data Sarana --//
          $sql = "select bea_split_id,bea_split_nominal from klinik.klinik_biaya_split where id_split='2' and id_biaya_tarif=".QuoteValue(DPE_CHAR,$biayaTarifId);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
          $dataBiayaSarana = $dtaccess->Fetch($rs);
           
           $_POST["biaya_split_jaspel"] = $dataBiayaJaspel["bea_split_nominal"]; 
           $biayaJaspelId = $dataBiayaJaspel["bea_split_id"]; 
           $_POST["biaya_split_sarana"] = $dataBiayaSarana["bea_split_nominal"];                
           $biayaSaranaId = $dataBiayaSarana["bea_split_id"];                
 		
      }         

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnSave"] || $_POST["btnUpdate"])
     { 
     
               $dbTable = " klinik.klinik_biaya_tarif";
               
               $dbField[0] = "biaya_tarif_id";   // PK
               $dbField[1] = "biaya_total";  
               $dbField[2] = "id_kelas";
               $dbField[3] = "biaya_tarif_tgl_awal";
               $dbField[4] = "biaya_tarif_tgl_akhir";
               $dbField[5] = "id_biaya";
               $dbField[6] = "is_cito";
               $dbField[7]= "id_jenis_pasien";
               $dbField[8] = "id_jenis_kelas";
 
               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["biaya_tarif_id"]);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_total"]));   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
               $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["biaya_tarif_tgl_awal"]));
               $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["biaya_tarif_tgl_akhir"]));
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["is_cito"]);
               $dbValue[7]= QuoteValue(DPE_NUMERIC,$_POST["id_jenis"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_jenis_kelas"]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
   
               $dtmodel->Update() or die("insert  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

            //ambil prosentase biaya split urut 1
             //-- bikin keterangan untuk Master Split Urut 1 --//
             $sql = "select split_persen from klinik.klinik_split where split_urut=1";
             $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
             $dataSplit_1 = $dtaccess->Fetch($rs);
             // $_POST["biaya_split_jaspel"] = ($dataSplit_1["split_persen"]/100)*StripCurrency($_POST["biaya_total"]);

            if($_POST["biaya_split_jaspel"])
            {
               
               $dbTable = " klinik.klinik_biaya_split";
               
               $dbField[0] = "bea_split_id";   // PK
               $dbField[1] = "id_split";
               $dbField[2] = "bea_split_nominal";
               $dbField[3] = "id_biaya_tarif";
                
               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["biaya_jaspel_id"]);    
               $dbValue[1] = QuoteValue(DPE_CHAR,"1");
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_split_jaspel"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["biaya_tarif_id"]);
                //print_r($dbValue);
                //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               $dtmodel->Update() or die("update  error");
                  
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
            }
            
            //ambil prosentase biaya split urut 2
             //-- bikin keterangan untuk Master Split Urut 2 --//
             $sql = "select split_persen from klinik.klinik_split where split_urut=2";
             $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
             $dataSplit_2 = $dtaccess->Fetch($rs);
             // $_POST["biaya_split_sarana"] = ($dataSplit_2["split_persen"]/100)*StripCurrency($_POST["biaya_total"]);
            if($_POST["biaya_split_sarana"])
            {
               
               $dbTable = " klinik.klinik_biaya_split";
               
               $dbField[0] = "bea_split_id";   // PK
               $dbField[1] = "id_split";
               $dbField[2] = "bea_split_nominal";
               $dbField[3] = "id_biaya_tarif";
                                                                 
                
               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["biaya_sarana_id"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,"2");
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_split_sarana"]);   
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["biaya_tarif_id"]);
                //print_r($dbValue);
                //die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

               $dtmodel->Update() or die("update  error");
                  
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
            }

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
//       var_dump($dataDbLog);


       for($i=0,$n=count($dataBiaya);$i<$n;$i++) {       

         $sql = "update klinik.klinik_biaya set biaya_urut=".QuoteValue(DPE_NUMERIC,($i+1))." 
                where biaya_id=".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
         //echo $sql; die();
         $dtaccess->Execute($sql);

       } //end loopinh   

			//$sql = "update klinik.klinik_biaya set biaya_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
			//$dtaccess->Execute($sql);
			
*/

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
          $biayaTarifId = $_GET["id_del"];
          
          $sql = "select * from klinik.klinik_folio where id_biaya_tarif=".QuoteValue(DPE_CHAR,$biayaTarifId);
          $rs = $dtaccess->Execute($sql);
          $dataFolio = $dtaccess->Fetch($rs);
          
          if(!$dataFolio)
          {
          
           $sql = "delete from klinik.klinik_biaya_split where id_biaya_tarif = ".QuoteValue(DPE_CHAR,$biayaTarifId);
           //echo $sql; die();
           $dtaccess->Execute($sql);
           
           $sql = "delete from klinik.klinik_biaya_tarif where biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId);
           //echo $sql; die();
           $dtaccess->Execute($sql);

          
          header("location:".$backPage);
          exit();
          
          } 
          else {
            echo "<script>alert('Tindakan ini tidak bisa dihapus karena sudah dipakai pelayanan!!!');</script>";
          header("location:".$backPage);
          exit();
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
     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_instalasi[] = "a.klinik_kategori_tindakan_header_instalasi_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_instalasi = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a where 1=1";
     if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->Fetch($rs_instalasi);

      // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header']) $sql_where_header[] = "a.kategori_tindakan_header_id = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->Fetch($rs_header);

     // Data Kategori Tindakan Header //    
    //  if($_POST['biaya_kategori']) $sql_where_tindakan[] = "kategori_tindakan_id = ".QuoteValue(DPE_CHAR,$_POST['biaya_kategori']);
    //  $sql_tindakan = "select * from  klinik.klinik_kategori_tindakan where 1=1";
    //  if ($sql_where_tindakan) $sql_tindakan .= " and ".implode(" and ",$sql_where_tindakan);
    //  $sql_tindakan .= " order by kategori_urut asc";
    //  $rs_tindakan = $dtaccess->Execute($sql_tindakan);
    //  $dataKategoriTindakan = $dtaccess->Fetch($rs_tindakan);
     $dataKategoriTindakan = $_GET["check"]; 
     


     //-- Ambil data Biaya --//
     $sql = "select biaya_nama from klinik.klinik_biaya where biaya_id=".QuoteValue(DPE_CHAR,$_POST['biaya_id']);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataBiayaTindakan = $dtaccess->Fetch($rs);
     
     //-- bikin keterangan untuk Master Kelas --//
     $sql = "select * from klinik.klinik_kelas order by kelas_tingkat asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
     $dataKelas = $dtaccess->FetchAll($rs);

     //Jenis pasien
     $sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataJenisPasien = $dtaccess->FetchAll($rs);
     
     //Jenis kelas
     $sql = "select * from klinik.klinik_jenis_kelas where tipe = 'RGT' order by jenis_kelas_nama";
     $dataJenisKelas = $dtaccess->FetchAll($sql);
     
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
            <!-- row filter -->
			      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Nama Tindakan : <?php echo $dataBiayaTindakan["biaya_nama"];?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
          				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Tindakan Header Instalasi : </label>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo $dataKategoriTindakanHeaderInstalasi["klinik_kategori_tindakan_header_instalasi_nama"];?></label>
          				    </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Tindakan Header : </label>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo $dataKategoriTindakanHeader["kategori_tindakan_header_nama"];?></label>
          				    </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Tindakan : </label>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo $dataKategoriTindakan;?></label>
          				    </div>
             					<div class="col-md-4 col-sm-6 col-xs-12">
                                  <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
          						<?php echo "$tombolAdd"; ?>
          				    </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                                  <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
          						<?php echo "$tombolKembali"; ?>
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
                    <h2></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>"> 
           <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jenis Pasien  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <select required name="id_jenis" id="id_jenis" class="select2_single form-control">
                <option class="inputField" value="" >- Pilih Jenis Pasien -</option>
                <?php for($i=0,$n=count($dataJenisPasien);$i<$n;$i++){ ?>
                <option class="inputField" value="<?php echo $dataJenisPasien[$i]["jenis_id"];?>"<?php if ($_POST["id_jenis_pasien"]==$dataJenisPasien[$i]["jenis_id"]) echo"selected"?>><?php echo $dataJenisPasien[$i]["jenis_nama"];?>&nbsp;</option>
                <?php } ?>
              </select>
            </div>
            </div>        
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelas</label>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <select required name="id_kelas" id="id_kelas" class="select2_single form-control" >
				    		<option class="inputField" value="" >- Pilih Kelas -</option>
				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select>
             </div>
            </div>

            <div class="form-group" id="id_jenis_kelas_container" style="<?=($_POST['id_kelas'] == '4e03cd241aa16ec69656a04da87690f7') ? '' : 'display: none'?>">
              <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelas</label>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <select name="id_jenis_kelas" id="id_jenis_kelas" class="select2_single form-control">
                <option class="inputField" value="" >- Pilih Jenis Kelas -</option>
                <?php for($i=0,$n=count($dataJenisKelas);$i<$n;$i++){ ?>
                <option class="inputField" value="<?=$dataJenisKelas[$i]["jenis_kelas_id"];?>"<?php if ($_POST["id_jenis_kelas"]==$dataJenisKelas[$i]["jenis_kelas_id"]) echo"selected"?>><?=$dataJenisKelas[$i]["jenis_kelas_nama"];?>&nbsp;</option>
                <?php } ?>
              </select>
             </div>
            </div>

				   <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tgl Awal Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
              <div class='input-group date' id='datepicker'>
                  <input required  name="biaya_tarif_tgl_awal"  id="biaya_tarif_tgl_awal" data-inputmask="'mask': '99-99-9999'" type='text' class="form-control" value="<?=$_GET['biaya_tarif_tgl_awal']?>">
                  <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
						</div>
					  </div>
            <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tgl Akhir Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
              <div class='input-group date' id='datepicker2'>
                  <input required  name="biaya_tarif_tgl_akhir"id="biaya_tarif_tgl_akhir" data-inputmask="'mask': '99-99-9999'" type='text' class="form-control" value="<?=$_GET['biaya_tarif_tgl_akhir']?>">
                  <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
						</div>
					  </div>
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">CITO <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
               <select name="is_cito" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				    		<option class="inputField" value="" >- Pilih CITO -</option>
		    				<option class="inputField" value="C" <?php if ($_POST["is_cito"]=="C") echo"selected"?>>CITO</option> 
	   					  <option class="inputField" value="E" <?php if ($_POST["is_cito"]=="E") echo"selected"?>>Non CITO</option>
							 </select> 
						  </div>
					  </div>
				   <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tarif Tindakan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_total","biaya_total","85","100",currency_format($_POST["biaya_total"]),"curedit", "",true);?>
						</div>
					  </div>
					  <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jasa Pelayanan  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" value="<?=currency_format($_POST["biaya_split_jaspel"])?>" class="form-control" readonly>
                  <?php echo $view->RenderHidden("biaya_split_jaspel","biaya_split_jaspel","85","100",$_POST["biaya_split_jaspel"],"inputField",false);?>
                  <font color="red">Otomatis berubah jika biaya total berubah setelah klik simpan</font>
						</div>
					  </div>                                                                                      
            <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jasa Sarana  <span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" value="<?=currency_format($_POST["biaya_split_sarana"])?>" class="form-control" readonly>
                  <?php echo $view->RenderHidden("biaya_split_sarana","biaya_split_sarana","85","100",$_POST["biaya_split_sarana"],"inputField",false);?>
					        <font color="red">Otomatis berubah jika biaya total berubah setelah klik simpan</font
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
                      
                      
            <input type="hidden" name="biaya_jaspel_id" id="biaya_jaspel_id" value="<?php echo $biayaJaspelId;?>" /> 
            <input type="hidden" name="biaya_sarana_id" id="biaya_sarana_id" value="<?php echo $biayaSaranaId;?>" />            
            <input type="hidden" name="biaya_tarif_id" id="biaya_tarif_id" value="<?php echo $biayaTarifId;?>" />    
            <input type="hidden" name="biaya_id" id="biaya_id" value="<?php echo $_POST["biaya_id"];?>" />       
					  <input type="hidden" name="id_biaya" id="id_biaya" value="<?php echo $_POST["biaya_id"];?>" />
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
      <script type="text/javascript">
        $("select#id_kelas").change(function(){
          var id_kelas = $(this).val();

          if(id_kelas == '4e03cd241aa16ec69656a04da87690f7'){
            $("div#id_jenis_kelas_container").css("display", "block");
            $("select#id_jenis_kelas").attr("required", true);
          }
          else{
            $("div#id_jenis_kelas_container").css("display", "none");
            $("select#id_jenis_kelas").attr("required", false);
          }
        });
      </script>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
