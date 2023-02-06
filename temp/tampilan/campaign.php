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
     
     $sql = "select * from global.global_gambar_campaign where id_dep=".QuoteValue(DPE_CHAR,$konfig["dep_id"]);
     $rs = $dtaccess->Execute($sql);
     $dataCampaign = $dtaccess->FetchAll($rs);
     
?>
<script type="text/javascript" src="tampilan/js/jquery.cycle.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#campaign')
	.cycle({
        fx: 'scrollUp', // choose your transition type, ex: fade, scrollUp, scrollRight, shuffle
     });
});
</script>

<div id="campaign">
<?php for($i=0,$n=count($dataCampaign);$i<$n;$i++){?>
<blockquote>
<p><img src="<?php echo $ROOT; ?>gambar/campaign/<?php echo $dataCampaign[$i]["gambar_campaign_nama"];?>"/></p>
</blockquote>
<?php } ?>
<!--<blockquote>
<p><img src="tampilan/images/kampanye-anti-narkoba1.jpg"/></p>
</blockquote>

<blockquote>
<p><img src="tampilan/images/kampanye-anti-narkoba2.jpg"/></p>
</blockquote>-->

</div>
<div style="clear:both"></div>