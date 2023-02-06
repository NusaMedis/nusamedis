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
     
     $sql = "select * from global.global_gambar_slide where id_dep=".QuoteValue(DPE_CHAR,$konfig["dep_id"]);
     $rs = $dtaccess->Execute($sql);
     $dataSlide = $dtaccess->FetchAll($rs);
     
?>
<div id="nivo">
<div id="slide">
	<div id="slider">
    <?php for($i=0,$n=count($dataSlide);$i<$n;$i++){ ?>
		<a href="#" target="_blank"><img src="<?php echo $ROOT;?>gambar/slide/<?php echo $dataSlide[$i]["gambar_slide_nama"];?>" alt="" /></a>
    <?php } ?>
		<!--<a href="#" target="_blank"><img src="tampilan/images/slide/r_2.jpg" alt="" /></a>
		<a href="#" target="_blank"><img src="tampilan/images/slide/r_3.jpg" alt="" /></a>
		<a href="#" target="_blank"><img src="tampilan/images/slide/r_4.jpg" alt="" /></a>-->
	</div>
</div>
</div>