<?php
$string = "A1sdbsde8A";

echo " Pertama ".$string."<br>";
       //hapuskarakter paling belakang
       $stringz = substr($string, 0, -1);
echo " Kedua ".$stringz."<br>";

//setelah itu hapus karakter pertama
       $stringx = substr($stringz, 1);
       echo " Hasilnya ".$stringx;

$stringy = str_replace('A', '', $string);
echo "<br> str_replace ".$stringy;
?>       