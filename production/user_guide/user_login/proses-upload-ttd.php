<?php
	
	$nm_file = $_FILES["file"]["name"];
	$tp_file = $_FILES["file"]["tmp_name"];
	$sz_file = $_FILES["file"]["size"];
	$ty_file = $_FILES["file"]["type"];
	$id=$_POST['id'];

	$dir = "../gambar/asset_ttd/$nm_file";

	move_uploaded_file($tp_file, $dir);

	// cek status file
	if($sz_file > 100000){
		// tampilkan status gagal karena melebihi batas
		echo $nm_file . " <b>Gagal! file melebihi ukuran. Ukuran maksimal 100kb</b><br>"."<input type='button' value='Kembali' onclick='history.back(-1)' />";
	} else if (file_exists($dir)) {
		// tampilkan status gagal karena file sudah ada
		echo $nm_file . " <b>Gagal! File sudah ada</b><br>" ."<input type='button' value='Kembali' onclick='history.back(-1)' />";
	} else {
		// //lokasi file
		// $imgFullpath = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/'. "hasil-upload/" . $nm_file;
		
		// //deskripsi file
		// echo "<b>Stored in:</b><a href = '$imgFullpath' target='_blank'> " .$imgFullpath.'<a>';

		echo "<br/><b>File Name:</b> " . $nm_file . "<br>";
		// echo "<b>Type:</b> " . $ty_file . "<br>";
		// echo "<b>Size:</b> " . $sz_file . " kB<br>";
		// echo "<b>Temp file:</b> " . $tp_file . "<br>";

	}

?>