<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
     
     $editPage = "pekerjaan_edit.php";
     $thisPage = "pekerjaan_view.php";
     $addPage = "pekerjaan_edit.php?tambah=".$depId;
     //echo $addPage;
    
	 if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }

     $sql = "select a.* from global.global_pekerjaan a order by a.pekerjaan_nama asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Master Pekerjaan";

          $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     
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
                <h3>Pekerjaan</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Master Pekerjaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					 <!-- TABLE VIEW -->
                      <table width="100%" id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" border="1">
                        <thead>
                          <tr>
                              <th>No</th>
                              <th>Nama Pekerjaan</th>
                              <th>Edit</th>
                              <th>Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                          
                          <?php if ($dataTable): ?>
                            <?php $numb=1; foreach ($dataTable as  $value):  ?>
                              <tr>
                                  <td><?='&nbsp;&nbsp;'.$numb++?></td>
                                  <td><?='&nbsp;&nbsp;' .$value["pekerjaan_nama"];?></td>
                                  <td><a href="<?=$editPage.'?id='.$enc->Encode($value["pekerjaan_id"])?>"><img hspace="2" width="32" height="32" src="<?=$ROOT.'gambar/icon/edit.png'?>" alt="Edit" title="Edit" border="0"></a></td>
                                  <td><a href="<?=$editPage.'?del=1&id='.$enc->Encode($value['pekerjaan_id'])?>"><img hspace="2" width="32" height="32" src="<?=$ROOT.'gambar/icon/hapus.png'?>" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a></td>
                              </tr>
                            <?php endforeach ?>
                          <?php endif ?>
                        </tbody>
                      </table>
                     
					<!-- <?php echo $view->SetFocus("btnAdd"); ?> -->
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




