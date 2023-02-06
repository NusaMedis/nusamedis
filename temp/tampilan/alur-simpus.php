<?php 
    // LIBRARY
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
	   require_once($ROOT."lib/tampilan.php");	
     
     // INISIALISASY LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();   
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
     
     $sql = "select * from global.global_departemen";
     $rs = $dtaccess->Execute($sql);
     $konfig = $dtaccess->Fetch($rs);
     
?>
<div class="box" align="center" style="height:350px;">
<img src="<?php echo $ROOT;?>gambar/img_cfg/<?php echo $konfig["dep_gambar_bagan_depan"]; ?>" title="Alur Simpus"/>
</div>