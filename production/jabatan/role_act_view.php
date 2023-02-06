<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."tampilan.php");

  // INISIALISASI LIBRARY
  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $enc = new textEncrypt();
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $table = new InoTable("table1","100%","center");
  $userName = $auth->GetUserName();
  $depNama = $auth->GetDepNama();

  // AMBIL ID
  if($_GET["id"]){
    $rolEnc = $_GET["id"];
    $rolId = $_GET["id"];
  }

  $LinkAdd = "role_act_add.php?id_rol=".$rolId;

  if($_POST['modul'] != '') $Code = substr($_POST['modul'], 0, 2);
  $sql = "SELECT *
          FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A
          LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
          LEFT JOIN GLOBAL.GLOBAL_AUTH_ROLE C ON C.ROL_ID = A.ID_ROL
          WHERE A.ID_ROL = ".QuoteValue(DPE_CHAR, $_GET['id'])." 
          AND B.MENU_LEVEL = '1'
          ";
  if($_POST['modul'] != '') $sql .= " AND MENU_ID LIKE '".$Code."%'";
  $sql .= " ORDER BY MENU_URUT ASC";
  $dataPriv = $dtaccess->FetchAll($sql);

  
  if ($_POST['btnDelete']) {
    foreach ($_POST['delete'] as $key => $value) {
      $sqlDel = "DELETE FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE WHERE AUTH_MENU_ROLE_ID = ".QuoteValue(DPE_CHAR, $value);
      
      $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
              LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
              WHERE B.MENU_LEVEL = '3' AND A.ID_ROL = ".QuoteValue(DPE_CHAR, $_GET['id'])." AND A.AUTH_MENU_ROLE_ID = ".QuoteValue(DPE_CHAR, $value);
      $dataParent2 = $dtaccess->FetchAll($sql);
      $dataParent2 = $dataParent2[0]['menu_parent'];

      $dtaccess->Execute($sqlDel);

      $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
              LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
              WHERE B.MENU_LEVEL = '3' AND A.ID_ROL = ".QuoteValue(DPE_CHAR,$_GET['id'])." AND B.MENU_PARENT = ".QuoteValue(DPE_CHAR,$dataParent2);
      $cekDataParent2 = $dtaccess->FetchAll($sql);

      if (!count($cekDataParent2) > 0) {
        $sqlDel = "DELETE FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE WHERE ID_ROL = ".QuoteValue(DPE_CHAR, $_GET['id'])." AND ID_MENU = ".QuoteValue(DPE_CHAR, $dataParent2);
        $dtaccess->Execute($sqlDel);
      }

      $sql = "SELECT MENU_PARENT FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_ID = ".QuoteValue(DPE_CHAR,$dataParent2);
      $dataParent1 = $dtaccess->FetchAll($sql);
      $dataParent1 = $dataParent1[0]['menu_parent'];

      $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
              LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
              WHERE B.MENU_LEVEL = '2' AND A.ID_ROL = ".QuoteValue(DPE_CHAR,$_GET['id'])." AND B.MENU_PARENT = ".QuoteValue(DPE_CHAR,$dataParent1);
      $cekDataParent1 = $dtaccess->FetchAll($sql);

      if (!count($cekDataParent1) > 0) {
        $sqlDel = "DELETE FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE WHERE ID_ROL = ".QuoteValue(DPE_CHAR, $_GET['id'])." AND ID_MENU = ".QuoteValue(DPE_CHAR, $dataParent1);
        $dtaccess->Execute($sqlDel);
      }
    }
    header('Location: role_act_view.php?id='.$rolId);
  }

  $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_LEVEL = '1' AND MENU_ID != '000001' ORDER BY MENU_URUT ASC";
  $rs = $dtaccess->Execute($sql);
  $dataApp = $dtaccess->FetchAll($rs);

  $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_ROLE WHERE ROL_ID = ".QuoteValue(DPE_CHAR, $_GET['id']);
  $dataJabatan = $dtaccess->Fetch($sql);

  $tableHeader = 'Hak Akses Jabatan : '.$dataJabatan['rol_name'];

  $sql = "SELECT *
          FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A
          LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
          LEFT JOIN GLOBAL.GLOBAL_AUTH_ROLE C ON C.ROL_ID = A.ID_ROL
          WHERE A.ID_ROL = ".QuoteValue(DPE_CHAR, $_GET['id'])." 
          AND B.MENU_LEVEL = '1'
          ";
  $sql .= " ORDER BY MENU_URUT ASC";
  $dataMenu = $dtaccess->FetchAll($sql);
?>


<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/ew.js"></script>
  </head>
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
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Manajemen - <?php echo $tableHeader;?></h2>                
                    <div class="clearfix"></div>
                  </div>
                  <form name="frmView" method="POST" action="<?php echo $addPage;?>">
                    <table width="100%">
                      <tr>
                        <td width="5%"><b>Modul</b></td>
                        <td width="30%">
                          <select class="form-control" name="modul">
                            <option></option>
                            <?php foreach ($dataMenu as $key => $value): ?>
                              <option value="<?php echo $value['menu_id'] ?>" <?php if($_POST['modul'] == $value['menu_id']) echo "selected"; ?>><?php echo $value['menu_nama'] ?></option>
                            <?php endforeach ?>
                          </select>
                        </td>
                        <td width="3%">&nbsp;</td>
                        <td width="5%"><input type="submit" class="btn btn-primary" name="btnLanjut" value="Lanjut"></td>
                        <td width="57%">&nbsp;</td>
                      </tr>
                    </table>
                  </form>
                </div>
                <div class="x_panel">
                  <form name="frmView" method="POST" action="<?php echo $editPage; ?>">
                     <table class="table table-bordered" width="100%">
                       <thead>
                         <tr>
                           <th style="width:10%; text-align: center;"><input type="checkbox" id="checkall" value="y" onclick="FunChecklistAll()"></th>
                           <th style="width:90%">Menu</th>
                         </tr>
                       </thead>
                       <tbody>
                          <?php 
                            foreach ($dataPriv as $key => $Level1): 
                              $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
                                      LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
                                      WHERE B.MENU_LEVEL = '2' AND A.ID_ROL = ".QuoteValue(DPE_NUMERIC, $Level1['id_rol'])." AND B.MENU_PARENT = ".QuoteValue(DPE_CHAR, $Level1['menu_id'])." ORDER BY MENU_URUT ASC";
                              $dataLevel2 = $dtaccess->FetchAll($sql);
                          ?>
                          <tr>
                            <td colspan="2"><?php echo $Level1['menu_nama'] ?></td>
                          </tr>
                            <?php 
                              foreach ($dataLevel2 as $key => $Level2): 
                                $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
                                        LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
                                        WHERE B.MENU_LEVEL = '3' AND A.ID_ROL = ".QuoteValue(DPE_NUMERIC, $Level2['id_rol'])." AND B.MENU_PARENT = ".QuoteValue(DPE_CHAR, $Level2['menu_id'])." ORDER BY MENU_URUT ASC";
                                $dataLevel3 = $dtaccess->FetchAll($sql);
                            ?>
                            <tr>
                              <td colspan="2"> . . <?php echo $Level2['menu_nama'] ?></td>
                            </tr>
                              <?php foreach ($dataLevel3 as $key => $Level3): ?>
                                <tr>
                                  <td align="center"><input type="checkbox" name="delete[]" value="<?php echo $Level3['auth_menu_role_id'] ?>" class="checklist"></td>
                                  <td><?php echo $Level3['menu_nama'] ?></td>
                                </tr>
                              <?php endforeach ?>
                            <?php endforeach ?>
                          <?php endforeach ?>
                       </tbody>
                       <tfoot>
                          <tr>
                            <td colspan="2">
                              <input type="submit" name="btnDelete" class="btn btn-danger col-md-2" value="Hapus">&nbsp;
                              <a href="<?php echo $LinkAdd ?>"><input type="button" name="btnAdd" class="btn btn-primary col-md-2" value="Tambah Baru"></a>
                              <a href="role_view.php"><input type="button" name="btnBack" class="btn btn-success col-md-2" value="Kembali"></a>
                            </td>
                          </tr>
                       </tfoot>
                     </table>
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
    <?php require_once($LAY."js.php") ?>
  </body>
</html>


<script type="text/javascript">
  function FunChecklistAll() {
    if ($('#checkall').prop('checked') == true) {
      $('.checklist').prop('checked', true);
    } else {
      $('.checklist').prop('checked', false);
    }
  }
</script>