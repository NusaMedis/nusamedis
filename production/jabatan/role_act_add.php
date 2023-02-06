<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."bit.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();

  $enc = new textEncrypt();
  $auth = new CAuth();
  $err_code = 0;
  $userName = $auth->GetUserName();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);     

  if($_GET["id_rol"]) { 
    $id_rol = $_GET["id_rol"];
    $idRolEnc = $_GET["id_rol"];
  }

  $addPage = "role_act_add.php?id_rol=".$id_rol;
  $backPage = "role_act_view.php?id=".$id_rol;

  if($_POST['modul'] != '') $Code = substr($_POST['modul'], 0, 2);

  $sqlx = "SELECT MENU_ID FROM GLOBAL.GLOBAL_AUTH_MENU A
          WHERE A.MENU_ID IN (
            SELECT C.ID_MENU FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE C
            LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = C.ID_MENU
            WHERE C.ID_ROL = ".QuoteValue(DPE_CHAR, $id_rol)."
              AND MENU_LEVEL = '3') ";
  if($_POST['modul'] != '') $sqlx .= " AND MENU_ID LIKE '".$Code."%'";
  $sqlx .= " ORDER BY MENU_URUT ASC";
  $dataTable = $dtaccess->FetchAll($sqlx);

  if ($_POST['btnSave']) {
    foreach ($_POST['add'] as $key => $value) {
      $dbTable = "global.global_auth_menu_role";

      $dbField[0] = "auth_menu_role_id";   // PK
      $dbField[1] = "id_rol";
      $dbField[2] = "id_menu";

      $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['id_rol']);
      $dbValue[2] = QuoteValue(DPE_CHAR,$value);

      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
      $dtmodel->Insert() or die("insert  error");   
      
      $sql = "SELECT MENU_PARENT FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_ID = ".QuoteValue(DPE_CHAR,$value);
      $dataParent2 = $dtaccess->FetchAll($sql);
      $dataParent2 = $dataParent2[0]['menu_parent'];
      
      $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
              LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
              WHERE B.MENU_LEVEL = '2' AND A.ID_ROL = ".QuoteValue(DPE_CHAR,$_POST['id_rol'])." AND B.MENU_ID = ".QuoteValue(DPE_CHAR,$dataParent2);
      $cekDataParent2 = $dtaccess->FetchAll($sql);

      
      if (!count($cekDataParent2) > 0) {
        $dbTable = "global.global_auth_menu_role";

        $dbField[0] = "auth_menu_role_id";   // PK
        $dbField[1] = "id_rol";
        $dbField[2] = "id_menu";

        $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['id_rol']);
        $dbValue[2] = QuoteValue(DPE_CHAR,$dataParent2);

        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
        $dtmodel->Insert() or die("insert  error");   
  
      }

      $sql = "SELECT MENU_PARENT FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_ID = ".QuoteValue(DPE_CHAR,$dataParent2);
      $dataParent1 = $dtaccess->FetchAll($sql);
      $dataParent1 = $dataParent1[0]['menu_parent'];

      $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU_ROLE A 
              LEFT JOIN GLOBAL.GLOBAL_AUTH_MENU B ON B.MENU_ID = A.ID_MENU
              WHERE B.MENU_LEVEL = '1' AND A.ID_ROL = ".QuoteValue(DPE_CHAR,$_POST['id_rol'])." AND B.MENU_ID = ".QuoteValue(DPE_CHAR,$dataParent1);
      $cekDataParent1 = $dtaccess->FetchAll($sql);

      if (!count($cekDataParent1) > 0) {
        $dbTable = "global.global_auth_menu_role";

        $dbField[0] = "auth_menu_role_id";   // PK
        $dbField[1] = "id_rol";
        $dbField[2] = "id_menu";

        $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['id_rol']);
        $dbValue[2] = QuoteValue(DPE_CHAR,$dataParent1);

        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
        $dtmodel->Insert() or die("insert  error");   
       
      }      

      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);
    }

    header("location:".$backPage);
    exit(); 
  }

  $sql = "select rol_name from global.global_auth_role where rol_id = ".$id_rol;
  $rs = $dtaccess->Execute($sql);
  $dataJabatan = $dtaccess->Fetch($rs);

  $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU 
          WHERE MENU_LEVEL = '1' AND MENU_ID != '000001'";   
  $sql .= " ORDER BY MENU_URUT ASC";
  $dataMenu = $dtaccess->FetchAll($sql);

  $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU 
          WHERE MENU_LEVEL = '1' AND MENU_ID != '000001'";   
  if ($_POST['modul'] != '') $sql .= " AND MENU_ID LIKE '".$Code."%'";
  $sql .= " ORDER BY MENU_URUT ASC";
  $dataLevel1 = $dtaccess->FetchAll($sql);

  $tableHeader = "&nbsp;Jabatan : ".$dataJabatan["rol_name"];
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
        <?php require_once($LAY."topnav.php") ?>    
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
                  <form name="frmEdit" method="POST" action="<?php echo $addPage;?>">
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
                  <form name="frmEdit" method="POST" action="<?php echo $addPage;?>">
                    <input type="hidden" name="id_rol" value="<?php echo $id_rol ?>">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <!-- <th style="width: 10%">&nbsp;</th> -->
                          <th style="width:10%; text-align: center;"><input type="checkbox" id="checkall" value="y" onclick="FunChecklistAll()"></th>
                          <th style="width: 90%">Menu</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                          foreach ($dataLevel1 as $key => $Level1): 
                            $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_PARENT = ".QuoteValue(DPE_CHAR, $Level1['menu_id'])." ORDER BY MENU_URUT ASC";
                            $dataLevel2 = $dtaccess->FetchAll($sql);
                        ?>
                          <tr>
                            <td colspan="2"><?php echo $Level1['menu_nama'] ?></td>
                          </tr>
                          <?php 
                            foreach ($dataLevel2 as $key => $Level2): 
                              $sql = "SELECT * FROM GLOBAL.GLOBAL_AUTH_MENU WHERE MENU_PARENT = ".QuoteValue(DPE_CHAR, $Level2['menu_id'])." AND MENU_ID NOT IN(".$sqlx.") ORDER BY MENU_URUT ASC";
                              $dataLevel3 = $dtaccess->FetchAll($sql);
                          ?>
                            <tr>
                              <td colspan="2"> . . <?php echo $Level2['menu_nama'] ?></td>
                            </tr>
                            <?php foreach ($dataLevel3 as $key => $Level3): ?>
                              <tr>
                                <td align="center"><input type="checkbox" name="add[]" value="<?php echo $Level3['menu_id'] ?>" class="checklist"></td>
                                <td><?php echo $Level3['menu_nama'] ?></td>
                              </tr>
                            <?php endforeach ?>
                          <?php endforeach ?>
                        <?php endforeach ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="2">
                            <input type="submit" name="btnSave" class="btn btn-primary col-md-2" value="Simpan">
                            <a href="<?php echo $backPage ?>"><input type="button" name="btnBack" class="btn btn-success col-md-2" value="Kembali"></a>
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
      <?php require_once($LAY."footer.php") ?>
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