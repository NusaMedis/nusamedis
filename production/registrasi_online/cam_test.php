<?php
     //LIBRARY 
     require_once("penghubung.inc.php");
    $lokasi = $ROOT."gambar/foto_pasien";
    $lokTakeFoto = $ROOT."gambar/foto_pasien"; 
    
?>
<html>
<head>
<meta charset="utf-8" />
<link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jquery-1.2.6.min.js"></script> 
<script src="assets/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="assets/webcam/webcam.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	
	var camera = $('#camera'),
		photos = $('#photos'),
		screen =  $('#screen');

	var template = '<a href="<?php echo $ROOT;?>gambar/foto_pasien/{src}" rel="cam" '
		+'style="background-image:url(<?php echo $ROOT;?>gambar/thumbs/{src})"></a>';

	/*----------------------------------
		Setting up the web camera
	----------------------------------*/
  webcam.set_swf_url('assets/webcam/webcam.swf');
	webcam.set_api_url('upload_pasien.php');	// The upload script
	webcam.set_quality(80);				// JPEG Photo Quality
	webcam.set_shutter_sound(true, 'assets/webcam/shutter.mp3');

	// Generating the embed code and adding it to the page:	
	screen.html(
	webcam.get_html(screen.width(), screen.height())
	);

	/*----------------------------------
		Binding event listeners
	----------------------------------*/
	var shootEnabled = false;		
	$('#shootButton').click(function(){
		
		if(!shootEnabled){
			return false;
		}
		webcam.freeze();
		togglePane();
		return false;
	});
	
	$('#cancelButton').click(function(){
		webcam.reset();
		togglePane();
		return false;
	});
   
	$('#uploadButton').click(function(){
 
		webcam.upload();
		webcam.reset();
		togglePane();  
		return false;
	});

	camera.find('.settings').click(function(){
		if(!shootEnabled){
			return false;
		}
		
		webcam.configure('camera');
	});

	// Showing and hiding the camera panel:	
	$('.camTop').click(function(){

			camera.animate({
				bottom:-350
			});

	});
  
  	var showns = false;
	$('.camTops').click(function(){
		
		if(showns){
			camera.animate({
				bottom:-350
			});
		}
		else {
			 camera.animate({
				bottom:20
			},{easing:'easeOutExpo',duration:'slow'});
		}
		
		showns = !showns;
	});

	/*---------------------- 
		Callbacks
	----------------------*/

	webcam.set_hook('onLoad',function(){
		// When the flash loads, enable
		// the Shoot and settings buttons:
		shootEnabled = true;
	});
	
	webcam.set_hook('onComplete', function(msg){
		
		// This response is returned by upload.php
		// and it holds the name of the image in a
		// JSON object format:
		msg1 = $.parseJSON(msg);
    
		if(msg.error){
   // alert('masuk foto');
			alert(msg1.message);
		}
		else {  
     //     alert(msg1.filename);
			 //Adding it to the page;    
      document.getElementById('cust_usr_foto').value=msg1.filename; 
      
      document.original.src='<?php echo $lokTakeFoto."/";?>'+msg1.filename;  
      //alert(kepet);
      alert('Foto Pasien telah tersimpan');
			photos.prepend(templateReplace(template,{src:msg1.filename}));
			initFancyBox();
		}
	});
	
	  webcam.set_hook('onError',function(e){
		screen.html(e);
	});
	
  
	// This function toggles the two
	// .buttonPane divs into visibility:
	function togglePane(){
		var visible = $('#camera .buttonPane:visible:first');
		var hidden = $('#camera .buttonPane:hidden:first');
		
		visible.fadeOut('fast',function(){
			hidden.show();
		});
	}
	
	// Helper function for replacing "{KEYWORD}" with
	// the respectful values of an object:
	function templateReplace(template,data){
		return template.replace(/{([^}]+)}/g,function(match,group){
			return data[group.toLowerCase()];
		});
	}
});


</script> 

<div id="camera">
	<span class="camTop"></span>
  
  <div id="screen"></div>
    <div id="buttons">
    	<div class="buttonPane">
        	<a id="shootButton" href="" class="blueButton">Shoot!</a>
        </div>
        <div class="buttonPane hidden">
        	<a id="cancelButton" href="" class="blueButton">Cancel</a> <a id="uploadButton" href="" class="greenButton">Upload!</a>
        </div>
    </div>
        <span class="settings"></span>
</div> 

<div class="camTops"  alt="foto pasien" title="foto pasien">
	<input type="button" id="Ambil Foto" size="35" name="Ambil Foto" value="Ambil Foto" class="btn btn-default">
</div>
