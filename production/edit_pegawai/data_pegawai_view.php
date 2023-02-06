<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."bit.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
    
	   $nPages = 48;
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $tahunTarif = $auth->GetTahunTarif();
     $lokasi = $ROOT."/gambar/img_cfg";
     $depLowest = $auth->GetDepLowest();
     $depNama = $auth->GetDepNama(); 
    
		     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	
	
     $thisPage = "data_pegawai_view.php";
     $detPage = "data_pegawai_detail.php";
     $editPage = "data_pegawai_edit.php";
     $cetPage = "cetak_data_pegawai.php";
     
   
	
	//pencarian
	if($_POST["btnSearch"] || $_GET["kembali"]=='yes' || $_GET["pgw_nama"])
    {
               // -- search nip ---
			//if($nip) $sql_where[] = "a.pgw_nip like '%".$nip."%'";
      //if($_POST["cust_usr_kode"]) $sql_where[] = "c.cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");
      //if($_POST["pgw_nama"]) $sql_where[] = " a.pgw_nama like ".QuoteValue(DPE_CHAR,"%".$_POST["pgw_nama"]."%");
      //if($_POST["pgw_nip"]) $sql_where[] = " a.pgw_nip like ".QuoteValue(DPE_CHAR,"%".$_POST["pgw_nip"]."%");
               // -- search nama pelamar ---
			//if($nama) $sql_where[] = "UPPER(a.pgw_nama) like '%".strtoupper($nama)."%'";

			// -- search alamat pegawai---
			//if($alamat) $sql_where[] = "UPPER(a.pgw_alamat_surabaya) like '%".strtoupper($alamat)."%'";
			
			// -- search unit kerja ---
			if($_POST["id_struk"] && $_POST["id_struk"]!="--") $sql_where[] = "a.id_struk = '".$_POST["id_struk"]."'";
    //$sql_where = implode(" and ".$sql_where);
			//queri cari 
			$sql = " select a.pgw_id, a.pgw_kode, a.pgw_nip, a.pgw_nama, a.pgw_alamat, 
                      a.id_struk,a.pgw_telp_hp,  b.struk_nama 
					     from hris.hris_pegawai a left join hris.hris_struktural b on a.id_struk = b.struk_id";						
			
			$sql .= " where 1=1 and a.pgw_nama like  ".QuoteValue(DPE_CHAR,"%".$_POST["pgw_nama"]."%");
      $sql .= " and a.pgw_nip like ".QuoteValue(DPE_CHAR,"%".$_POST["pgw_nip"]."%");
      $sql .= " and upper(a.pgw_alamat) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["pgw_alamat_surabaya"])."%");
      $sql .= " and a.id_struk like ".QuoteValue(DPE_CHAR,"%".$_POST['id_struk']."%");
               
			//$sql .= " order by pgw_nama";
			//$rs = $dtaccess->Execute($sql,DB_SCHEMA);
			$dataTable = $dtaccess->FetchAll($sql);
      //echo $sql;
		} 

  if($_POST["btnTambah"]){
    header("location:entri_pegawai.php");
  	exit();
  }
  
  if($_GET["del"]){
        $pgwId = $enc->Decode($_GET["id"]);
     
     $sql = "delete from hris.hris_pegawai where pgw_id=".QuoteValue(DPE_CHAR,$pgwId);
     $dtaccess->Execute($sql);
     
     header("location:".$thisPage."?kembali=yes");
     exit(); 
  }
     
	
     //*-- config table ---*//
     $tableHeader = "&nbsp;Manajemen&nbsp;Pegawai";
     
     // --- construct new table ---- //
     $counterHeader=0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "NIP";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pegawai";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
	
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. HP";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan Kerja";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
	
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
	
	

     
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
      
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["pgw_nip"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["pgw_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
		  
		  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["pgw_alamat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
           $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["pgw_telp_hp"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["struk_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
		  
		  
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["pgw_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;               
          
          $tbContent[$i][$counter][TABLE_ISI] ='<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["pgw_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';           
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kamar_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kamar_id"]).'&del=1&id_poli='.$dataTable[$i]["id_poli"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
     
     }
     
	// -- cari unit_kerja
	$sql = "select * from hris.hris_struktural order by struk_tree";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataDep = $dtaccess->FetchAll($rs);     
     
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
                <h3>Manajemen</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">NIP</label>
						<input type=text size=30 maxlength="200" class="form-control" name="pgw_nip"  value="<?php echo $nip;?>">
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pegawai</label>
						<input type=text size=30 maxlength="200" class="form-control" name="pgw_nama"  value="<?php echo $nama;?>">
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat</label>
						<input type=text size=40 maxlength="200" class="form-control" name="pgw_alamat_surabaya"  value="<?php echo $alamat;?>">
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Unit Kerja</label>
						<select name="id_struk" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
							<option class="form_control" value="0"<?php if ($_POST["id_struk"]=="0") echo"selected"?>>- Pilih Departemen</option>
							<?php $counter = -1;
								for($i=0,$n=count($dataDep);$i<$n;$i++){
								unset($spacer); 
								$length = (strlen($dataDep[$i]["struk_tree"])/TREE_LENGTH)-1; 
								for($j=0;$j<$length;$j++) $spacer .= "....";
							?> 
								<option class="form_control" value="<?php echo $dataDep[$i]["struk_id"];?>"<?php if ($_POST["id_struk"]==$dataDep[$i]["struk_id"]) echo"selected"?>><?php echo $spacer." ".$dataDep[$i]["struk_nama"];?>&nbsp;</option>
							<?php } ?>
						</select> 
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnSearch" value="   Cari   " class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-success">
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnTambah" value="   Tambah   " class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
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
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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