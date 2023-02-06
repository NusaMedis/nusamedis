<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);   

     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
     
	    $backPage = "jadwal_dokter_view.php"; 
     
    	$isAllowedCreate=1;
    	$isAllowedUpdate=1;
    	$isAllowedDel=1;

     
     if($_POST["id_instalasi"]) { 
       $idInstalasi = $_POST["id_instalasi"];
       $_POST["id_instalasi"] = $_POST["id_instalasi"];
       }
     
     if($_GET["id_instalasi"]) { 
       $idInstalasi = $_GET["id_instalasi"];
       $_POST["id_instalasi"] = $_GET["id_instalasi"];
     }

     if($_POST["id_sub_instalasi"]) { 
       $idSubInstalasi = $_POST["id_sub_instalasi"];
       $_POST["id_sub_instalasi"] = $_POST["id_sub_instalasi"];
       }
     
     if($_GET["id_sub_instalasi"]) { 
       $idSubInstalasi = $_GET["id_sub_instalasi"];
       $_POST["id_sub_instalasi"] = $_GET["id_sub_instalasi"];
     }

     if($_POST["id_poli"]) { 
       $idPoli = $_POST["id_poli"];
       $_POST["id_poli"] = $_POST["id_poli"];
       }
     
     if($_GET["id_poli"]) { 
       $idPoli = $_GET["id_poli"];
       $_POST["id_poli"] = $_GET["id_poli"];
     }
     
                                                               
     
     $excel = $_POST["btnExcel"];
     $cetak = $_POST["btnCetak"];
     
     $addPage = "jadwal_dokter_edit.php?id_instalasi=".$_POST["id_instalasi"]."&id_sub_instalasi=".$_POST["id_sub_instalasi"]."&id_poli=".$_POST["id_poli"];
     $editPage = "jadwal_dokter_edit.php?id_instalasi=".$_POST["id_instalasi"]."&id_sub_instalasi=".$_POST["id_sub_instalasi"]."&id_poli=".$_POST["id_poli"];
     $thisPage = "jadwal_dokter_view.php";

     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$addPage.'\'"></button>';
		
 		 $sql_where[] = "1=1"; 
     
     if($_POST["id_poli"]  && $_POST["id_poli"] !="--") $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"] );    
     $sql_where = implode(" and ",$sql_where);

// QUERY PERKIRAAN NANTI DULU
//              f.no_prk as no_prk_beban, 
//              e.nama_prk, e.no_prk, f.nama_prk as nama_prk_beban, 
//            left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
//              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
    
     if ($_POST["btnLanjut"]) 
     {
   	  $sql = "select a.*, b.poli_id, e.usr_name as dokter_nama
              from klinik.klinik_jadwal_dokter a
              join global.global_auth_poli b on b.poli_id = a.id_poli
              left join global.global_auth_sub_instalasi c on c.sub_instalasi_id = a.id_sub_instalasi
              left join global.global_auth_instalasi d on a.id_instalasi = d.instalasi_id
              left join global.global_auth_user e on a.id_dokter = e.usr_id
              left join global.global_auth_role f on e.id_rol = f.rol_id
              where ".$sql_where;
      $sql .= " order by jadwal_dokter_hari,jadwal_dokter_jam_mulai,e.usr_name asc ";
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTable = $dtaccess->FetchAll($rs);
  	  
      }
		
	 	$counterHeader = 0;
     $tableHeader = "Manajemen - Jadwal Dokter";
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;  
	 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;  
	 
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Hari Praktek";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  
     
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Mulai";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++; 
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Selesai";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  
	 
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
    // $counterHeader++; 
	 	 
//  	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Pendapatan";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	 
//	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Beban";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++; 
	  
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
     $counterHeader++; 
          
     //TOTAL HEADER TABLE
     $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	
	          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
	          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	          $counter++;
			  
			      $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter_nama"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;

			      $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dayName[$dataTable[$i]["jadwal_dokter_hari"]];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;

            $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["jadwal_dokter_jam_mulai"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
	          $counter++;
            
			      $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["jadwal_dokter_jam_selesai"];
	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	          $counter++;
			  
		//	  if($dataTable[$i]["biaya_jenis"]=='TA'){
	//		  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";	
//			  }elseif($dataTable[$i]["biaya_jenis"]=='TG'){
//			  $tbContent[$i][$counter][TABLE_ISI] = "I G D";	
//			  }elseif($dataTable[$i]["biaya_jenis"]=='TI'){
//	          $tbContent[$i][$counter][TABLE_ISI] = "Rawat Inap";
//			  }
//	          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//	          $counter++;   
	          
		//	  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
	  //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	  //        $counter++;
		//	  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
	  //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	  //        $counter++;
			if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["jadwal_dokter_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_del='.$dataTable[$i]["jadwal_dokter_id"].'&del=1"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }			    


				                            
                    
     }
		
		
		
		
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=tarif_all.xls');
     }
     
     if($_POST["btnCetak"]){
      $_x_mode = "cetak" ;      
     }

     // Data Instalasi//
     $sql = "select * from  global.global_auth_instalasi a where id_dep = '$depId'";
     $sql .= " order by instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);

      // Data Sub Instalasi //
     if($_POST['id_instalasi']) $sql_where_instalasi[] = "a.id_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_instalasi']);
     $sql_instalasi = "select * from  global.global_auth_sub_instalasi a where 1=1 and id_dep = '$depId'";
     if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by sub_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataSubInstalasi = $dtaccess->FetchAll($rs_instalasi);

     // Data Klinik //     
     if($_POST['id_sub_instalasi']) $sql_where_poli[] = "id_sub_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_sub_instalasi']);
     $sql_poli = "select * from  global.global_auth_poli where 1=1 and id_dep = '$depId'";
     if ($sql_where_poli) $sql_poli .= " and ".implode(" and ",$sql_where_poli);
     $sql_poli .= " order by poli_urut asc";
     $rs_poli = $dtaccess->Execute($sql_poli);
     $dataPoli = $dtaccess->FetchAll($rs_poli);

     
?>



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
            <div class="page-title">
              <div class="title_left">
                <h3><?php echo $tableHeader;?></h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <a href="import_jadwal.php" class="btn btn-danger pull-right">Import</a>
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					 <!--
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<select name="biaya_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    		<option class="inputField" value="" >- Pilih Tipe Rawat -</option>
				    				<option class="inputField" value="TA" <?php if ($_POST["biaya_jenis"]=="TA") echo"selected"?>>Rawat Jalan&nbsp;</option> 
				   					 <option class="inputField" value="TI" <?php if ($_POST["biaya_jenis"]=="TI") echo"selected"?>>Rawat Inap&nbsp;</option>
           							 <option class="inputField" value="TG" <?php if ($_POST["biaya_jenis"]=="TG") echo"selected"?>>IGD&nbsp;</option>
				  		</select> 				  		
				    </div> -->
            <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Instalasi</label>
						<select name="id_instalasi" class="select2_single form-control"  onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Instalasi-</option>
				     		<?php for($i=0,$n=count($dataInstalasi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataInstalasi[$i]["instalasi_id"];?>"<?php if ($_POST["id_instalasi"]==$dataInstalasi[$i]["instalasi_id"]) echo"selected"?>><?php echo $dataInstalasi[$i]["instalasi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
				    </div>
            
				    <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Sub Instalasi</label>
						<select name="id_sub_instalasi" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Sub Instalasi -</option>
				    		 <?php for($i=0,$n=count($dataSubInstalasi);$i<$n;$i++){ ?>
				   			 <option class="inputField" value="<?php echo $dataSubInstalasi[$i]["sub_instalasi_id"];?>"<?php if ($_POST["id_sub_instalasi"]==$dataSubInstalasi[$i]["sub_instalasi_id"]) echo"selected"?>><?php echo $dataSubInstalasi[$i]["sub_instalasi_nama"];?>&nbsp;</option>
				  			 <?php } ?>
				  		</select> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik</label>
						  <select name="id_poli" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Klinik -</option>
				     		<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>"<?php if ($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo"selected"?>><?php echo $dataPoli[$i]["poli_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
				    </div>	
            
            	    
				  <?if ($_POST["id_poli"]) { ?>  			    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnLanjut" id="btnLanjut" value="   Lanjut   " class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
				    </div>
					<? } ?>
          <!-- DITUTUP DULU SEMENTARA -->
          
          <?if ($_POST["btnLanjut"]) { ?>  
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<?php echo "$tombolAdd"; ?>
				    </div>	
          <? }?>
            			    
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
                  <div class="x_content">
					   <table class="table" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
					
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

