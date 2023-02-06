<?php
// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "History_view_inap.php";
     $detailPage = "detail_pemakaian_obat.php";
     $poliId = $auth->IdPoli();
     $tableHeader = "Histori Pemakaian Obat Pasien Rawat Inap";

     $sql_where[] = "(a.reg_status = 'I2' or a.reg_status = 'I3') and a.id_poli != '20' and a.id_poli != '10' and reg_tipe_rawat = 'I'";

     if($_POST['cust_usr_nama']) $sql_where[] = " b.cust_usr_nama  like '%".strtoupper($_POST['cust_usr_nama'])."%' ";

     if($_POST['cust_usr_kode']) $sql_where[] = " b.cust_usr_kode like '%".strtoupper($_POST['cust_usr_kode'])."%' ";

     if($_POST['id_dokter']) $sql_where[] = " a.id_dokter = '".$_POST['id_dokter']."' ";


     $sql = "select a.reg_id, b.cust_usr_nama, b.cust_usr_alamat, c.usr_name,id_pembayaran from klinik.klinik_registrasi a
              LEFT JOIN global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
              LEFT JOIN global.global_auth_user c on a.id_dokter = c.usr_id";
     $sql .= " where  ".implode(" and ", $sql_where);
     $sql .= " order by b.cust_usr_nama";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);


	 
	$sql = "select * from global.global_auth_user where id_rol = 2 order by usr_name asc";
  $rs = $dtaccess->Execute($sql);
  $dataDokter = $dtaccess->FetchAll($rs);
?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

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
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?= $tableHeader ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
				 <div class="col-md-3 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
					 
				    </div>
				    <div class="col-md-3 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
						
				    </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
              <div>
              <select class="select2_single form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                <option value="">[Pilih Dokter]</option> 
                <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
                <?php } ?>
              </select>
            </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary" onClick="javascript:return CheckFilter(document.frmView);">
                    <!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
                    
            </div>
          </div>
            
				    
					<div class="clearfix"></div>
				  </form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->
			 <!-- //row content -->
			<div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">

                  <div class="x_content">
                    
                  <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">

                  	<thead>
                      <th>No</th>
                      <th>Lihat</th>
                      <th>Nama</th>
                      <th>Alamat</th>
                      <th>Dokter</th>
                    </thead>
                    <tbody>
                      <?php
                      for($i = 0, $n = count($dataTable); $i < $n; $i++){
                        $reg = $dataTable[$i]['reg_id'];
                        $id_pembayaran = $dataTable[$i]['id_pembayaran'];
                        ?>
                        <tr>
                          <td><?php echo ($i+1); ?></td>
                          <td><a target="_blank" href="<?php echo "$detailPage?reg_id=$reg&id_pembayaran=$id_pembayaran"; ?>"><img hspace="2" width="32" height="32" src="<?php echo $ROOT; ?>gambar/icon/cari.png" alt="Edit" title="Edit" border="0"></a></td>
                          <td><?php echo $dataTable[$i]['cust_usr_nama']; ?></td>
                          <td><?php echo $dataTable[$i]['cust_usr_alamat']; ?></td>
                          <td><?php echo $dataTable[$i]['usr_name']; ?></td>
                        </tr>
                        <?php
                      }
                      ?>
                    </tbody>
                  </table>
                  
                  </div>
                </div>
              </div>	  	
              <!-- //row content -->
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