<?php
    require_once("penghubung.inc.php");
    require_once($ROOT."lib/bit.php");
    require_once($ROOT."lib/login.php");
    require_once($ROOT."lib/encrypt.php");
    require_once($ROOT."lib/datamodel.php");
    require_once($ROOT."lib/tree.php");
    require_once($ROOT."lib/tampilan.php");
    
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']); 
    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    $err_code = 0;
    $tree = new CTree("hris.hris_struktural","struk_tree", TREE_LENGTH);
    $depId = $auth->GetDepId();
    $depNama = $auth->GetDepNama();
    $userName = $auth->GetUserName();
    
    if(!$auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
    if(isset($_GET["parent"])) {
        $parentEnc = & $_GET["parent"];
        if($parentEnc) $parentId = $enc->Decode($parentEnc);
    } elseif(isset($_POST["parent"])) {
        $parentEnc = & $_POST["parent"];
        if($parentEnc) $parentId = $enc->Decode($parentEnc);
    } 
    //echo  $parentId;
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["struk_id"])  $depId = & $_POST["struk_id"];
 
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $depId = $enc->Decode($_GET["id"]);
        }
	   
        $sql = "select struk_nama, struk_tree,struk_kode, struk_id, length(struk_tree) as len 
			 from hris.hris_struktural where struk_id = '".$depId."'";
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["struk_nama"] = $row_edit["struk_nama"];
        $_POST["struk_tree"] = $row_edit["struk_tree"];  
	   
	   $_POST["struk"] = substr($_POST["struk_tree"],0,($row_edit["len"]-TREE_LENGTH));
	     
	   $_POST["id_struk"] = $_POST["struk"]; 
    } 
    if($_x_mode=="New") $privMode = PRIV_CREATE;
    elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    /*if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }*/
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$depId = & $_POST["struk_id"];
			$_x_mode = "Edit";
		}

		$_POST["struk_nama"] = trim($_POST["struk_nama"]);

        $err_code = 3;
        //--- Checking Data ---//
        
        if ($_POST["struk_nama"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);
        
         if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT struk_id FROM hris.hris_struktural WHERE UPPER(struk_nama) = '%s'",strtoupper($_POST["struk_nama"]));
        else
            $sql = sprintf("SELECT struk_id FROM hris.hris_struktural WHERE UPPER(struk_nama) = '%s' AND struk_id <> '%s'",strtoupper($_POST["struk_nama"]),$_POST["struk_id"]);

        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        $dtaccess->Clear($rs_check);

        //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "hris.hris_struktural";
            
            $dbField[0] = "struk_id";   // PK
            $dbField[1] = "struk_nama";
            $dbField[2] = "struk_tree";  

            if(!$depId) $depId = $dtaccess->GetTransId("hris.hris_struktural","struk_id",DB_SCHEMA);
		   
		  if($_POST["struk"] && $_POST["struk"]!=$_POST["id_struk"]) { 
			
			 $sql = "select struk_id, struk_tree, length(struk_tree) as len 
				    from hris.hris_struktural
				    where struk_tree like '".$_POST["struk_tree"]."%'
				    and struk_id <> ".QuoteValue(DPE_CHAR,$depId)."
				    order by struk_tree "; 
			 $rs = $dtaccess->Execute($sql,DB_SCHEMA);
			 $dataTree = $dtaccess->FetchAll($rs);
			 
			 $_POST["struk_tree"] = $tree->AddChild($_POST["id_struk"]); 
		  } else if(!$_POST["struk"] && !$parentId){ 
			 $_POST["struk_tree"] = $tree->AddChild($_POST["id_struk"]); 
		  } else if($parentId) {
			 $_POST["struk_tree"] = $tree->AddChild($parentId);  
		  }
		  
		  $strLen = (strlen($_POST["struk_tree"])-TREE_LENGTH)/TREE_LENGTH;
		  $treeNya = $_POST["struk_tree"];

            $dbValue[0] = QuoteValue(DPE_CHAR,$depId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["struk_nama"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["struk_tree"]);

            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);

            if ($_POST["btnSave"]) {
                $dtmodel->Insert() or die("insert item error");	
            } else if ($_POST["btnUpdate"]) {
                $dtmodel->Update() or die("update item error");	
            }
		  
            unset($dtmodel);
            unset($dbField);
            unset($dbValue);
            unset($dbKey);
		  
		  if($dataTree) {
			 for($i=0,$n=count($dataTree);$i<$n;$i++) {
				
				if($dataTree[$i-1]["len"] && $dataTree[$i]["len"]>$dataTree[$i-1]["len"]) {
				    $_POST["struk_tree"] = $_POST["tree"]; 
				} elseif($dataTree[$i]["len"]<$dataTree[$i-1]["len"]){
				    $_POST["struk_tree"] = $treeNya;
				}
				
				$_POST["tree"] = $tree->AddChild($_POST["struk_tree"]); 
				
				$sql = "update hris.hris_struktural set struk_tree = ".QuoteValue(DPE_CHAR,$_POST["tree"])." 
					   where struk_id = ".QuoteValue(DPE_CHAR,$dataTree[$i]["struk_id"]);
				$dtaccess->Execute($sql,DB_SCHEMA);
			 }
			 
		  }
            

		  header("location:dep_view.php");
            exit();        
        }
    }

    if ($_POST["btnDelete"]) {
        $depId = & $_POST["cbDelete"];
        for($i=0,$n=count($depId);$i<$n;$i++) {
			$tree->DelNode($depId[$i]);
			$parentId = $tree->GetParentId($depId[$i]);
			$sql = "select struk_id from hris.hris_struktural where struk_tree like '".$parentId."%' and length(struk_tree) = ".(strlen($parentId)+TREE_LENGTH);
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
        }
        header("location:dep_view.php");
        exit();    
    }
    
    $sql = "select struk_id, struk_nama,struk_tree
		  from hris.hris_struktural
		  where struk_id <> ".QuoteValue(DPE_CHAR,$depId)." 
		  order by struk_tree ";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataStruk = $dtaccess->FetchAll($rs);
  //  echo $sql;
?>
<?php echo $view->RenderBody("module.css",true,true,"EDIT SATKER"); ?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
 
<div id="body">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="0" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>Setup&nbsp;UnitKerja&nbsp;</strong></legend>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
	   <?php if(($_x_mode=="Edit" && $parentId) || !$parentId) { ?>
		  <tr>
			 <td align="right" class="tablecontent-odd"><strong>Struktur Unit Kerja</strong></td>
			 <td class="tablecontent">
				<select name="id_struk" class="inputField">
				    <option value="">[ Pilih Tree Unit Kerja ]</option>
				    <?php for($i=0,$n=count($dataStruk);$i<$n;$i++) { 
				    
				    unset($spacer); 
				    $length = (strlen($dataStruk[$i]["struk_tree"])/TREE_LENGTH)-1; 
				    for($j=0;$j<$length;$j++) $spacer .= ".."; ?>
				    
					   <option value="<?php echo $dataStruk[$i]["struk_tree"];?>" <?php if($_POST["id_struk"]==$dataStruk[$i]["struk_tree"]) echo "selected";?>><?php echo $spacer.$dataStruk[$i]["struk_nama"];?></option>
				    <?php } ?>
				</select>
			 </td>
		  </tr>
	   <?php } ?>
        <tr>
            <td align="right" class="tablecontent-odd"><strong>Nama <?if(readbit($err_code,1)||readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
            <td class="tablecontent"><input type="text" name="struk_nama" class="inputField" size="48" maxlength="100" value="<?php echo $_POST["struk_nama"]?>"/></td>
        </tr>

        <tr>
            <td colspan="2" align="right" class="tablecontent">
                <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Save" class="submit"/>
				<input type="button" name="btnNew" value="kembali" class="submit" onClick="document.location.href='dep_view.php?id=<?php echo $enc->Encode($depId);?>'">
            </td>
        </tr>
    </table>
    </fieldset>
    </td>
</tr>
</table>
<script type="text/javascript">
	document.frmEdit.struk_nama.focus();
</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="struk_id" value="<?php echo $depId?>" /> 
<input type="hidden" name="struk_tree" value="<?php echo $_POST["struk_tree"];?>" />
<input type="hidden" name="struk" value="<?php echo $_POST["struk"];?>" />
<? } ?>
<input type="hidden" name="parent" value="<?php echo $parentEnc?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;&nbsp;Nama&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>
</div>
</body>
</html>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
<?
    $dtaccess->Close();
?>
