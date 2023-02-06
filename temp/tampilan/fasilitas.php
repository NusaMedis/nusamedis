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
     
     $sql = "select * from global.global_gambar_fasilitas where id_dep=".QuoteValue(DPE_CHAR,$konfig["dep_id"]);
     $rs = $dtaccess->Execute($sql);
     $dataFasilitas = $dtaccess->FetchAll($rs);
     
?>
<script type="text/javascript">
function slideSwitch() {
    var $active = $('#slideshow IMG.active');
    if ( $active.length == 0 ) $active = $('#slideshow IMG:last');
    var $next =  $active.next().length ? $active.next()
        : $('#slideshow IMG:first');
    $active.addClass('last-active');
    $next.css({opacity: 0.0})
        .addClass('active')
        .animate({opacity: 1.0}, 1000, function() {
            $active.removeClass('active last-active');
        });
}
$(function() {
    setInterval( "slideSwitch()", 3000 );
});
</script>
<div class="box">
	<h1>Fasilitas</h1>
	<div id="slideshow">
    <?php for($i=0,$n=count($dataFasilitas);$i<$n;$i++){ ?>
		<img src="<?php echo $ROOT;?>gambar/fasilitas/<?php echo $dataFasilitas[$i]["gambar_fasilitas_nama"]; ?>" alt="Fasilitas1" class="active" />
    <?php } ?>
		<!--<img src="tampilan/images/fasilitas/fas2.jpg" alt="Fasilitas2" />
		<img src="tampilan/images/fasilitas/fas3.jpg" alt="Fasilitas3" />
		<img src="tampilan/images/fasilitas/fas4.jpg" alt="Fasilitas4" />-->
	</div>
</div>