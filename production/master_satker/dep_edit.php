<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");	
     require_once($LIB."tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $err_code = 0; 
     $tree = new CTree("hris.hris_struktural","struk_tree", TREE_LENGTH_CHILD);
     //$depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   
	   $backPage = "dep_view.php";
	   $thisPage = "dep_edit.php";
     
     
    /*if(!$auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
	//echo $_GET["parent"]."<br>".$enc->Decode($_GET["parent"]);
	 if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	 else $_x_mode = "New";
   
   if(isset($_GET["parent"])) $parentId = $enc->Decode($_GET["parent"]);
	 if($_POST["parent_id"])  $parentId = & $_POST["parent_id"]; 
	 if($_POST["struk_id"])  $strukId = & $_POST["struk_id"];  
   if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
             $_x_mode = "Delete";
        } else { 
             $_x_mode = "Edit";
             $strukId = $enc->Decode($_GET["id"]);
        }
       
        $sql = "select * from hris.hris_struktural where struk_id = ".QuoteValue(DPE_CHAR,$strukId);
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $view->CreatePost($row_edit);
        $kembali = "dep_view.php";
   }
     
   if ($_GET["parent"]) {
        $sql = "select struk_nama as struk_nama_parent,struk_kode as struk_kode_parent from hris.hris_struktural where struk_tree = ".QuoteValue(DPE_CHAR,$parentId);
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $view->CreatePost($row_edit);
   }
     
	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $strukId = & $_POST["struk_id"];
               $_x_mode = "Edit";
          }
		
          if ($err_code == 0) {
               $dbTable = "hris.hris_struktural";
            
                $dbField[0] = "struk_id";   // PK
                $dbField[1] = "struk_nama";
                $dbField[2] = "struk_kode";
                if($_POST["btnSave"] || $_POST["btnUpdateLagi"]){
                $dbField[3] = "struk_tree";
                }
                
               if(!$strukId) $strukId = $dtaccess->GetTransID();   
               $_POST["struk_tree"] = $tree->AddChild($parentId);
               
               $dbValue[0] = QuoteValue(DPE_CHAR,$strukId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["struk_nama"]);   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["struk_kode"]);
               if($_POST["btnSave"] || $_POST["btnUpdateLagi"]){
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["struk_tree"]);
               }
               //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
   
               if ($_POST["btnSave"] || $_POST["btnUpdateLagi"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
              if($parentId) 
              {
                $sql = "update hris.hris_struktural set struk_is_lowest = 'n' 
                        where struk_tree = ".QuoteValue(DPE_CHAR,$parentId);
                $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
              }
	

          header("location:".$backPage);
          exit();
                  
          }
     }
 
    
     if ($_GET["del"]) 
     {
         
          $strukId = $enc->Decode($_GET["id"]);
          $sql = "select struk_id,struk_tree from hris.hris_struktural where struk_id like ".QuoteValue(DPE_CHAR,$strukId);
          $rs = $dtaccess->Execute($sql);
          $parent = $dtaccess->Fetch($rs);

           $sql = "delete from hris.hris_struktural where struk_tree like ".QuoteValue(DPE_CHAR,$parent["struk_tree"]."%");
           $dtaccess->Execute($sql);

           $sql = "delete from hris.hris_struktural 
           where struk_id = ".QuoteValue(DPE_CHAR,$strukId);
            

          header("location:".$backPage);
          exit();  
          
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
                    <h2>Master Satuan Kerja</h2>
                    <?php if ($parentId) { ?>
                    <h2>&nbsp;Anak dari : <?php echo $_POST["struk_nama_parent"];?> Kode : <?php echo $_POST["struk_kode_parent"];?></h2>
                    <? } ?>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="frmEdit" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kode Satuan Kerja</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <input type="text" name="struk_kode" class="form-control col-md-7 col-xs-12" id="struk_kode" value="<?php echo $_POST["struk_kode"];?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Satuan Kerja</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <input type="text" name="struk_nama" class="form-control col-md-7 col-xs-12" id="struk_nama" maxlength="255" value="<?php echo $_POST["struk_nama"];?>">
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-danger" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("parent_id","parent_id",$parentId);?>
                      <?php echo $view->RenderHidden("struk_id","struk_id",$strukId);?>
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