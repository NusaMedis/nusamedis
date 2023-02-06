<?php
    require_once("penghubung.inc.php");
    require_once($ROOT."lib/login.php");
    require_once($ROOT."lib/encrypt.php");
    require_once($ROOT."lib/datamodel.php");
   require_once($ROOT."lib/dateLib.php");
   require_once($ROOT."lib/tampilan.php");
    require_once($ROOT."lib/tree.php");
    
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    $tree = new CTree("hris_struktural","struk_tree", TREE_LENGTH);
    $depId = $auth->GetDepId();
    $depNama = $auth->GetDepNama();
    $userName = $auth->GetUserName();

     if(!$auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/ogin.php?msg=Session Expired'</script>";
          exit(1);
     }

	$editPage = "dep_edit.php";
	$thisPage = "dep_view.php";
	
	//*-- config table ---*//
	$PageHeader = "Setup UnitKerja";
	
	$tableSubHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
	$tableSubHeader[0]["width"] = "1%";
	$tableSubHeader[0]["align"] = "center";
	

	
	$tableSubHeader[1]["name"]  = "Nama";
	$tableSubHeader[1]["width"] = "20%";
	$tableSubHeader[1]["align"] = "center";
  
  $tableSubHeader[2]["name"]  = "Edit";
	$tableSubHeader[2]["width"] = "2%";
	$tableSubHeader[2]["align"] = "center";
	
	$tableSubHeader[3]["name"]  = "Anak";
	$tableSubHeader[3]["width"] = "2%";
	$tableSubHeader[3]["align"] = "center";    
	
	$tableContent[0]["name"]  = "struk_tree";
	$tableContent[0]["wrap"] = "nowrap";
	$tableContent[0]["align"] = "left";
	
	$tableContent[1]["name"]  = "struk_id";
	$tableContent[1]["wrap"] = "nowrap";
	$tableContent[1]["align"] = "left";
	
	 $tableContent[2]["name"]  = "struk_tree";
	$tableContent[2]["wrap"] = "nowrap";
	$tableContent[2]["align"] = "left";
	
	$tableContent[3]["name"]  = "struk_nama";
	$tableContent[3]["wrap"] = "nowrap";
	$tableContent[3]["align"] = "left";  
	
	
	
	$jumContent = count($tableSubHeader);
	$startContent = 3;
	
	$sql = "select * from hris.hris_struktural order by struk_tree";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);
	
/*	$sql = "select mhs_prodi, struk_tree, length(struk_tree) as len 
		   from hris.hris_mahasiswa a
		   join hris.hris_struktural b on b.struk_id = a.mhs_prodi 
		   group by mhs_prodi,struk_tree
		   order by struk_tree "; 
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataMhs = $dtaccess->FetchAll($rs);
	
	for($i=0,$n=count($dataMhs);$i<$n;$i++) {
		$ada[$dataMhs[$i]["struk_tree"]] = true;
		$len = ($dataMhs[$i]["len"]/TREE_LENGTH);
		for($a=0,$m=$len;$a<=$m;$a++) { 
			$hasilTree = substr($dataMhs[$i]["struk_tree"],0,($a*TREE_LENGTH)); 
			$ada[$hasilTree] = true; 
		}  
	}   */
	
	$sql = "select id_dep, struk_tree, length(struk_tree) as len 
		   from hris.hris_pegawai a
		   join hris.hris_struktural b on b.struk_id = a.id_dep
		   where struk_id = '36' 
		   group by id_dep,struk_tree
		   order by struk_tree "; 
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataPgw = $dtaccess->FetchAll($rs);
	
	for($i=0,$n=count($dataPgw);$i<$n;$i++) {
		$ada[$dataPgw[$i]["struk_tree"]] = true;
		$len = ($dataPgw[$i]["len"]/TREE_LENGTH);
		for($a=0,$m=$len;$a<=$m;$a++) { 
			$hasilTree = substr($dataPgw[$i]["struk_tree"],0,($a*TREE_LENGTH)); 
			$ada[$hasilTree] = true;
		} 
	}
	
	/*$sql = "select id_struk, struk_tree, length(struk_tree) as len 
		   from akademik.akad_matakuliah a
		   join hris.hris_struktural b on b.struk_id = a.id_struk
		   group by id_struk,struk_tree
		   order by struk_tree "; 
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataMk = $dtaccess->FetchAll($rs);
	
	for($i=0,$n=count($dataMk);$i<$n;$i++) {
		$ada[$dataMk[$i]["struk_tree"]] = true;
		$len = ($dataMk[$i]["len"]/TREE_LENGTH);
		for($a=0,$m=$len;$a<=$m;$a++) { 
			$hasilTree = substr($dataMk[$i]["struk_tree"],0,($a*TREE_LENGTH)); 
			$ada[$hasilTree] = true;
		} 
	}           */

    // -- tree config ----
    $idField = "struk_id";
    $treeField = "struk_tree";
    $showField = "struk_nama";
    $treeLength = TREE_LENGTH;
    $topLayer = 85;

    $sql = "select struk_id,struk_nama,struk_tree from hris.hris_struktural where length(struk_tree) = ".($treeLength)." order by struk_tree";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $headerTable = $dtaccess->FetchAll($rs);
    $jumlahHeader = count($headerTable);
    // -- end ---

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/ew.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/mm_layer.js"></script>
<script language="Javascript">
function GantiLayer(layer_skr,show_layer)
{
    MM_showHideLayers(layer_skr.value,'','hide',show_layer,'','show');
    layer_skr.value = show_layer;
}
</script>
</head>

<?php echo $view->RenderBody("module.css",true,true,"UNIT KERJA"); ?>
<body> 
    
<div id="body">

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr>
        <td width="10%" class="tablecontent"><div align="center">Pilih Kategori</div></td>
        <td class="tablecontent">
            <select class="inputField" name="header_jabatan" onChange="GantiLayer(this.form.lyr_skr,this.options[this.selectedIndex].value)">
            <option value="lyr_all">[Lihat Semua]</option>
            <?php for($i=0;$i<$jumlahHeader;$i++){ ?>
                <option value="lyr_<?php echo $headerTable[$i][$idField];?>"><?php echo $headerTable[$i][$showField];?></option> 
            <?php } ?>
            </select>
            <input type="hidden" name="lyr_skr" value="lyr_all">
        </td>
    </tr>
</table> 

<div id="lyr_all" style="position:absolute; left:0px; top:<?php echo $topLayer;?>px; width:100%; z-index:0; visibility: visible;"> 
    <table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tablecontent"> 
        <td colspan="<?php echo ($jumContent);?>"><div align="right">
            <input type="submit" name="btnDelete" value="Hapus" class="inputField">
            <input type="button" name="btnAdd" value="Tambah" class="inputField" onClick="document.location.href='<?php echo $editPage;?>?parent='">        
        </div></td> 
    </tr>
    <tr class="subheader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tableSubHeader[$i]["width"];?>" nowrap align="<?php echo $tableSubHeader[$i]["align"];?>"><?php echo $tableSubHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
	   
    <?php $counter = -1;
        for($i=0,$n=count($dataTable);$i<$n;$i++){
            // -- buat ngambil awal counter ---
            if($dataTable[$i][$treeField]==$headerTable[$counter+1][$treeField]){
                $counter++;
                $layerAwal[$counter] = $i;
            }
            if(substr($dataTable[$i][$treeField],0,$treeLength) == $headerTable[$counter][$treeField]) $layerAkhir[$counter] = $i;
            // -- end buat ngambil awal counter ---
		    
            unset($spacer); 
            $length = (strlen($dataTable[$i][$treeField])/TREE_LENGTH)-1; 
            for($j=0;$j<$length;$j++) $spacer .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		
		?>
       <tr class="<?php if($i%2==0) echo "tablecontent";else echo "tablecontent-odd";?>">
            <td align="center"><div align="center"><?php if(!$ada[$dataTable[$i]["struk_tree"]]) { ?><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"><?php  } else echo " ";?></div></td>     
			     <td <?php echo $tableContent[3]["wrap"];?> align="<?php echo $tableContent[3]["align"];?>">&nbsp;<?php echo $spacer." ".$dataTable[$i][$tableContent[3]["name"]];?>&nbsp;</td>
           <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $ROOT;?>gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <td align="center"><a href='<?php echo $editPage?>?parent=<?php echo $enc->Encode($dataTable[$i][$tableContent[2]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $ROOT;?>gambar/anak.png" alt="Tambah Anak" title="Tambah Anak" border="0"></a></td>
        </tr>
    <?php } ?>
    </table>
</div>

<?php for($p=0;$p<$jumlahHeader;$p++){ ?>
    <div id="lyr_<?php echo $headerTable[$p][$idField];?>" style="position:absolute; left:0px; top:<?php echo $topLayer;?>px; width:100%; z-index:0; visibility: hidden;"> 
        <table width="100%" border="1" cellpadding="1" cellspacing="1">
        <tr class="tablecontent"> 
            <td colspan="<?php echo ($jumContent);?>"><div align="right">
                <input type="submit" name="btnDelete" value="Hapus" class="submit">
                <input type="button" name="btnAdd" value="Tambah" class="submit" onClick="document.location.href='<?php echo $editPage;?>?parent='"> 
            </div></td> 
        </tr>
        <tr class="subheader">
            <?php for($i=0;$i<$jumContent;$i++){ ?>
                <td width="<?php echo $tableSubHeader[$i]["width"];?>" nowrap align="<?php echo $tableSubHeader[$i]["align"];?>"><?php echo $tableSubHeader[$i]["name"];?></td>
            <?php } ?>
        </tr> 
           
        <?php for($i=$layerAwal[$p];$i<=$layerAkhir[$p];$i++){ 
                unset($spacer); 
                $length = (strlen($dataTable[$i]["struk_tree"])/TREE_LENGTH)-1; 
                for($j=0;$j<$length;$j++) $spacer .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            
            ?>
            <tr class="tablecontent">
                <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
                <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $ROOT;?>gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a></td>
                <td align="center"><a href='<?php echo $editPage?>?parent=<?php echo $enc->Encode($dataTable[$i][$tableContent[2]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $ROOT;?>gambar/anak.png" alt="Tambah Anak" title="Tambah Anak" border="0"></a></td>
				<td <?php echo $tableContent[3]["wrap"];?> align="<?php echo $tableContent[3]["align"];?>">&nbsp;<?php echo $spacer." ".$dataTable[$i][$tableContent[3]["name"]];?>&nbsp;</td>
				<td <?php echo $tableContent[4]["wrap"];?> align="<?php echo $tableContent[4]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[4]["name"]];?>&nbsp;</td>
            </tr>
        <?php } ?>
           
        </table>
    </div>
<?php } ?>

<br /><br /><br />
</form>

</div>
<br /><br /><br />
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
