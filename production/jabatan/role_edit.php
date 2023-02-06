<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();    
   
     $editPage = "role_edit.php";
      // $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA_GLOBAL);

      // echo $rolId;
    
	
	/* if(!$auth->IsAllowed("man_user_jabatan",PRIV_READ) && !$auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("man_user_jabatan",PRIV_READ)===1 || $auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
	   if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	   else $_x_mode = "New";
	
	  $plx = new expAJAX("CheckRole");
    
	function CheckRole($rolName,$idDep,$rolId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.rol_id FROM global.global_auth_role a 
                  WHERE a.id_dep =  ".QuoteValue(DPE_CHAR,strtoupper($idDep))."
                  and upper(a.rol_name) = ".QuoteValue(DPE_CHAR,strtoupper($rolName));  
          if($rolId) $sql .= " and a.rol_id <> ".QuoteValue(DPE_NUMERIC,$rolId);      
          $rs = $dtaccess->Execute($sql);
          $dataRole = $dtaccess->Fetch($rs);
		return $dataRole["rol_id"];
  }

   if($_POST["rol_id"])  $rolId = & $_POST["rol_id"];
 
     if ($_GET["id"]) {

          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $rolId = $enc->Decode($_GET["id"]);
          }
          
          $sql = "select * from global.global_auth_role where rol_id = ".$rolId;
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          //echo $sql;
          
          $dtaccess->Clear($rs_edit);
          $_POST["rol_id"] = $row_edit["rol_id"];
          $_POST["rol_name"] = $row_edit["rol_name"];
          $_POST["dep_id"] = $row_edit["id_dep"];
          $_POST["rol_jabatan"] = $row_edit["rol_jabatan"];
          $_POST["klinik"] = $row_edit["id_dep"];
          $back = "role_view.php";
          
     }
     
     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;   

	   
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     /*  if ($_GET["tambah"]) {
      $_POST["klinik"]=$_GET["tambah"];
      $back = "role_view.php?kembali=".$_POST["klinik"];
     //echo $_GET["tambah"];
     } */
     
    if ($_POST["btnSave"]) {
    
          // $err_code = 3;
          //--- Checking Data ---//
          if ($_POST["rol_name"]) $err_code = clearbit($err_code,1); 
          else $err_code = setbit($err_code,1);
          
          if ($_POST["btnSave"]) 
               $sql = "SELECT rol_id FROM global.global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"])." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          else
               $sql = "SELECT rol_id FROM global.global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"])." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." and rol_id <> ".QuoteValue(DPE_NUMERIC,$_POST["rol_id"]);
              
          $rs_check = $dtaccess->Execute($sql);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
          else $err_code = clearbit($err_code,2); 
          
          $dtaccess->Clear($rs_check);

          if ($err_code == 0) {
               $dbTable = "global.global_auth_role";
               
               $dbField[0] = "rol_id";   // PK
               $dbField[1] = "rol_name";
               $dbField[2] = "id_app";
               $dbField[3] = "id_dep";
               $dbField[4] = "rol_jabatan";
   
               $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$rolId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rol_name"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,'10');
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["rol_jabatan"]);
               
               // print_r($dbValue);
               // die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Insert() or die("insert  error");	

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();        
            } 
     }
     
      if ($_POST["btnUpdate"]) {

          		if($_POST["btnUpdate"]){
                         $rolId = & $_POST["rol_id"];
                         $_x_mode = "Edit";
          		}

               $dbTable = "global.global_auth_role";
               
               $dbField[0] = "rol_id";   // PK
               $dbField[1] = "rol_name";
               $dbField[2] = "id_app";
			   $dbField[3] = "rol_jabatan";
   
               if(!$rolId) $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$rolId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rol_name"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,'10');
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["rol_jabatan"]);
               
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Update() or die("update  error");
			   print_r($dbValue);	
				//die();//menghentikan proses
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               

               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();        

     }

    if ($_GET["del"]) {
        $rolId = $enc->Decode($_GET["id"]);

            $sql = "delete from global.global_auth_role where rol_id = ".$rolId;
            $dtaccess->Execute($sql);
            
               //$back = "role_view.php?kembali=".$_POST["klinik"]."";
               $back = "role_view.php";
               header("location:".$back);
               exit();    
    }

      //Query Nama Klinik
      $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_POST["klinik"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinik = $dtaccess->Fetch($rs);
      
      $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_POST["dep_id"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinikEdit = $dtaccess->Fetch($rs);
      
      //Nama Sekolah
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      $klinikHeaderEdit = "Klinik : ".$namaKlinikEdit["dep_nama"];
      // echo $klinikHeaderEdit;
      //select
      $sql = "select * from global.global_departemen";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
      $jabatan[0] = $view->RenderOption("","Pilih Jabatan",$show);
     for($i=0,$n=count($dataPoli);$i<$n;$i++) {
          unset($show);
          if($_POST["id_rol"]==$dataPoli[$i]["rol_id"]) $show = "selected";
          $jabatan[$i+1] = $view->RenderOption($dataPoli[$i]["rol_id"],$dataPoli[$i]["rol_nama"],$show);
     }
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
                    <h2>Setup Jabatan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Jabatan<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						  <input onKeyDown="return tabOnEnter(this, event);" type="text" name="rol_name" id="rol_name" size="50" maxlength="100" value="<?php echo $_POST["rol_name"];?>"class="form-control col-md-7 col-xs-12" required="required"/>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jabatan Fungsional</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="rol_jabatan" class="select2_single form-control" id="rol_jabatan" onKeyDown="return tabOnEnter(this, event);">
							<option class="form_control" value="">[ Pilih Jabatan ]</option>								
							<option class="form_control" value="<?php echo STS_JAB_DOKTER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_DOKTER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_DOKTER];?></option>
							<option class="form_control" value="<?php echo STS_JAB_PPDS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PPDS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PPDS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_PERAWAT;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PERAWAT) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PERAWAT];?></option>
							<option class="form_control" value="<?php echo STS_JAB_STAFF;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_STAFF) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_STAFF];?></option>
							<option class="form_control" value="<?php echo STS_JAB_ANALIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANALIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANALIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_RADIOGRAFER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_RADIOGRAFER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_RADIOGRAFER];?></option>
							<option class="form_control" value="<?php echo STS_JAB_FISIOTERAPIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_FISIOTERAPIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_FISIOTERAPIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_ANESTESIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANESTESIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANESTESIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_IPJ;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_IPJ) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_IPJ];?></option>
              <option class="form_control" value="<?php echo STS_JAB_BIDAN;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_BIDAN) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_BIDAN];?></option>
						</select>
                        </div>
                      </div>
                           
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("rol_id","rol_id",$rolId);?>
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