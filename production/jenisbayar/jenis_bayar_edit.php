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
     $tree = new CTree("global.global_jenis_bayar","jbayar_id", TREE_LENGTH_CHILD);
     //$depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   
	   $backPage = "jenis_bayar_view.php";
	   $thisPage = "jenis_bayar_edit.php";
     
     
   /* if(!$auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

	   
    // $plx = new expAJAX("CheckOutletId");
	
  
	
   
/*	function CheckOutletId($outlet,$depId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.jbayar_id FROM global.global_jenis_bayar a 
                    WHERE upper(a.jbayar_nama) = ".QuoteValue(DPE_CHAR,strtoupper($outlet)); 
          if($depId) $sql .= " and a.jbayar_id <> ".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
          $dataOutlet = $dtaccess->Fetch($rs);
          
		return $dataOutlet["jbayar_id"];
     } */
     
   
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
  	if(isset($_GET["parent"])) $parentId = $enc->Decode($_GET["parent"]);
	if($_POST["parent_id"])  $parentId = & $_POST["parent_id"]; 
	if($_POST["jbayar_id"])  $jbayarId = & $_POST["jbayar_id"];  
  
     	$lokasi = $ROOT."/gambar/img_cfg";
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $jbayarId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from global.global_jenis_bayar  
		  where jbayar_id = ".QuoteValue(DPE_CHAR,$jbayarId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          	//echo $sql;
            $_POST["departemen_id"] = $row_edit["jbayar_id"];
            $_POST["jbayar_nama"] = $row_edit["jbayar_nama"]; 
            $kembali = "jenis_bayar_edit.php?kembali=".$_POST["departemen_id"];
          
          $_POST["jbayar_lowest"] = $row_edit["jbayar_lowest"];
          
          $view->CreatePost($row_edit);
          $_POST["dep_logo"] = $row_edit["dep_logo"];
          $fotoName = $lokasi."/".$row_edit["dep_logo"];


     }
     
     if (!$_POST["dep_logo"])
     {
        $_POST["dep_logo"] = "default.jpg";
        $fotoName = $lokasi."/".$_POST["dep_logo"];
     }else{
        $_POST["dep_logo"] = $_POST["dep_logo"];
        $fotoName = $lokasi."/".$_POST["dep_logo"];
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
               $jbayarId = & $_POST["jbayar_id"];
               $_x_mode = "Edit";
          }
	    if(!$_POST["dep_aktif"]) $_POST["dep_aktif"] = 'y';
      if(!$_POST["jbayar_lowest"]) $_POST["jbayar_lowest"] = 'y';
		
          if ($err_code == 0) {
               $dbTable = "global.global_jenis_bayar";
               
               $dbField[0] = "jbayar_id";   // PK
               $dbField[1] = "jbayar_nama"; 
               $dbField[2] = "jbayar_lowest";   
               $dbField[3] = "id_dep"; 
               $dbField[4] = "jbayar_status";             
			       
		 if(!$jbayarId) $jbayarId = $tree->AddChild($parentId);
               $dbValue[0] = QuoteValue(DPE_CHAR,$jbayarId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jbayar_nama"]);        
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["jbayar_lowest"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[4] = QuoteValue(DPE_CHAR,'y');

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          		
               if ($_POST["btnSave"]) {
                 $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                 $dtmodel->Update() or die("update  error");	
               }
          		
                 $simpan=1;
          		
          	  unset($dtmodel);
          	  unset($dbField);
          	  unset($dbValue);
          	  unset($dbKey);
               
		  if($parentId) {
			
			  //echo $parentId;
			  //die();
		  $sql = "update global.global_jenis_bayar set jbayar_lowest = 'n' where jbayar_id = ".QuoteValue(DPE_CHAR,$parentId);
		  $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
				
	    }

          header("location:".$backPage);
          exit();
                  
          }
     }
 
    
     if ($_GET["del"]) {
         
          $jbayarId = $enc->Decode($_GET["id"]);
          $tree->DelNode($jbayarId);
          $parentId = $tree->GetParentId($jbayarId);
          $sql = "select jbayar_id from global.global_jenis_bayar
		  where jbayar_id like '".$jbayarId."%' and length(jbayar_id) = ".(strlen($parentId)+TREE_LENGTH_CHILD); 
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    	//	echo $jbayarId;
       // die();	
          if($dtaccess->Count($rs)==0) {
    	  $sql = "update global.global_jenis_bayar set jbayar_lowest = 'y' where jbayar_id = ".QuoteValue(DPE_CHAR,$parentId);
    	  $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    }
    			
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
                    <h2>Jenis Bayar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Jenis Bayar <span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<input type="text" id="jbayar_nama" name="jbayar_nama" value="<?php echo $_POST["jbayar_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
					  </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("jbayar_id","jbayar_id",$jbayarId);?>
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
