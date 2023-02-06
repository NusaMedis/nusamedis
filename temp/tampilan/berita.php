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
     
     $sql = "select * from global.global_news where id_dep=".QuoteValue(DPE_CHAR,$konfig["dep_id"]);
     $rs = $dtaccess->Execute($sql);
     $dataNews = $dtaccess->FetchAll($rs);
     
?>
<script type="text/javascript" src="tampilan/js/jquery.cycle.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#news')
	.cycle({
        fx: 'fade', // choose your transition type, ex: fade, scrollUp, scrollRight, shuffle
     });
});
</script>

<div id="news">
<?php for($i=0,$n=count($dataNews);$i<$n;$i++){?>
<blockquote>
<p><cite><?php echo $dataNews[$i]["news_nama"]; ?></cite>
<img src="<?php echo $ROOT;?>gambar/news/<?php echo $dataNews[$i]["news_gambar"]; ?>" />"<?php echo $dataNews[$i]["news_keterangan"]; ?> <a href="#">Selengkapnya</a>"</p>
</blockquote>
<?php } ?>
<!--<blockquote>
<p><cite>Antisipasi Sebaran Tomcat di Jakarta </cite>
<img src="tampilan/images/berita2.jpg" />"Jakarta, Hingga saat ini, Jakarta masih terpantau aman dari sebaran serangga tomcat. Namun, Dinas Kesehatan DKI Jakarta sudah mulai melakukan antisipasi jika sewaktu-waktu tomcat menyebar di Jakarta. Salah satunya dengan menyiagakan 44 puskesmas kecamatan di DKI Jakarta untuk menangani korban serangan tomcat. <a href="#">Selengkapnya</a>"</p>
</blockquote>

<blockquote>
<p><cite>Dr. Nafsiah Mboi Meneteri Kesehatan 2012-2014</cite>
<img src="tampilan/images/berita3.jpg" />"Hari ini Rabu, 13 Juni 2012 pukul 11.15 Presiden Susilo Bambang Yudhoyono mengumumkan dr. Nafsiah Mboi, SpA, MPH sebagai Menteri Kesehatan 2012-2014 menggantikan Almarhumah dr. Endang Rahayu Sedyaningsih, MPH, Dr.PH. <a href="#">Selengkapnya</a>"</p>
</blockquote>-->

</div>