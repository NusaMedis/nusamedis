<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $cetakPage = "jenis_bayar_cetak.php";

     if(!$auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }

    if($_GET["id"]){
    
    $id = $enc->Decode($_GET["id"]);
    }
     $sql = "select * from global.global_jenis_bayar";
     $sql .=" order by jbayar_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
     //*-- config table ---*//
     $tableHeader = "&nbsp; CETAK JENIS BAYAR";
     
     /*$isAllowedDel = $auth->IsAllowed("proses_apbn",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("proses_apbn",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("proses_apbn",PRIV_CREATE);*/
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;    

     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
	  $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
	
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;        
}
?>


<?php echo $view->RenderBody("module.css",false,false,"CETAK JENIS BAYAR"); ?>
<script language="JavaScript">
<?php if($_x_mode=="cetak"){ ?>	
  document.location.href='jenis_bayar_cetak.php?jbiaya_id=<?php echo $_POST["jbayar_id"];?>';
<?php } ?>

</script>
<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>
<br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
     <tr >
          <td align="center"><font size="5px" ><?php echo $tableHeader;?></font></td>
     </tr>
</table>
<br><br><br />
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->SetFocus("btnAdd"); ?>
<?php echo $view->RenderBodyEnd(); ?>
