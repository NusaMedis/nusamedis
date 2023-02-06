<!--
var xmlhttp;

// Kategori Tindakan Pertama //
function tindakan(id_kategori)
{
    var url="barang.php?rand="+Math.random();
    var post="id_kategori="+id_kategori;    
    ajax(url,post,'barang');

}


// Kategori Tindakan Kedua //
function kategories(vv)
{
 
 document.getElementById("example").innerHTML="";
 if(vv=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub").focus();
    document.getElementById("hasil").innerHTML="";
    document.getElementById("id_tindakan_1").value="";
    document.getElementById("example").innerHTML="";
    document.getElementById("nom_1").value="";
    document.getElementById("totalTind_1").value="";
    document.getElementById("tind_diskon_1").value="";
    return false;
 }
 else
 {
    var url="barang_1.php?rand="+Math.random();
    var post="id_kategori="+vv;
    document.getElementById("hasil").innerHTML="";
    document.getElementById("id_tindakan_1").value="";
    document.getElementById("example").innerHTML="";
    document.getElementById("nom_1").value="";
    document.getElementById("totalTind_1").value="";
    document.getElementById("tind_diskon_1").value="";
    ajax(url,post,'hasil');
 }
}


// Kategori Tindakan Ketiga //
function gosries(gg)
{
 
 document.getElementById("example1").innerHTML="";
 if(gg=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("subs").focus();
    document.getElementById("hasil1").innerHTML="";
    document.getElementById("id_tindakan_2").value="";
    document.getElementById("example1").innerHTML="";
    document.getElementById("nom_2").value="";
    document.getElementById("totalTind_2").value="";
    document.getElementById("tind_diskon_2").value="";
    return false;
 }
 else
 {
    var url="barang_2.php?rand="+Math.random();
    var post="id_kategori="+gg;
    document.getElementById("hasil1").innerHTML="";
    document.getElementById("id_tindakan_2").value="";
    document.getElementById("example1").innerHTML="";
    document.getElementById("nom_2").value="";
    document.getElementById("totalTind_2").value="";
    document.getElementById("tind_diskon_2").value="";
    ajax(url,post,'hasil1');
 }
}


// Kategori Tindakan Keempaat //
function groove(gv)
{
 
 document.getElementById("example2").innerHTML="";
 if(gv=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub2").focus();
    document.getElementById("hasil2").innerHTML="";
    document.getElementById("id_tindakan_3").value="";
    document.getElementById("example2").innerHTML="";
    document.getElementById("nom_3").value="";
    document.getElementById("totalTind_3").value="";
    document.getElementById("tind_diskon_3").value="";
    return false;
 }
 else
 {
    var url="barang_3.php?rand="+Math.random();
    var post="id_kategori="+gv;
    document.getElementById("hasil2").innerHTML="";
    document.getElementById("id_tindakan_3").value="";
    document.getElementById("example2").innerHTML="";
    document.getElementById("nom_3").value="";
    document.getElementById("totalTind_3").value="";
    document.getElementById("tind_diskon_3").value="";
    ajax(url,post,'hasil2');
 }
}


// Kategori Tindakan Kelima //
function grovie(gie)
{
 
 document.getElementById("example3").innerHTML="";
 if(gie=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub3").focus();
    document.getElementById("hasil3").innerHTML="";
    document.getElementById("id_tindakan_4").value="";
    document.getElementById("example3").innerHTML="";
    document.getElementById("nom_4").value="";
    document.getElementById("totalTind_4").value="";
    document.getElementById("tind_diskon_4").value="";
    return false;
 }
 else
 {
    var url="barang_4.php?rand="+Math.random();
    var post="id_kategori="+gie;
    document.getElementById("hasil3").innerHTML="";
    document.getElementById("id_tindakan_4").value="";
    document.getElementById("example3").innerHTML="";
    document.getElementById("nom_4").value="";
    document.getElementById("totalTind_4").value="";
    document.getElementById("tind_diskon_4").value="";
    ajax(url,post,'hasil3');
 }
}


// Kategori Tindakan Keenam //
function thegrove(tg)
{
 
 document.getElementById("example4").innerHTML="";
 if(tg=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub4").focus();
    document.getElementById("hasil4").innerHTML="";
    document.getElementById("id_tindakan_5").value="";
    document.getElementById("example4").innerHTML="";
    document.getElementById("nom_5").value="";
    document.getElementById("totalTind_5").value="";
    document.getElementById("tind_diskon_5").value="";
    return false;
 }
 else
 {
    var url="barang_5.php?rand="+Math.random();
    var post="id_kategori="+tg;
    ajax(url,post,'hasil4');
 }
}



// Kategori Tindakan Ketujuh //
function gvies(tv)
{
 
 document.getElementById("example5").innerHTML="";
 if(tv=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub5").focus();
    document.getElementById("hasil5").innerHTML="";
    document.getElementById("id_tindakan_6").value="";
    document.getElementById("example5").innerHTML="";
    document.getElementById("nom_6").value="";
    document.getElementById("totalTind_6").value="";
    document.getElementById("tind_diskon_6").value="";
    return false;
 }
 else
 {
    var url="barang_6.php?rand="+Math.random();
    var post="id_kategori="+tv;
    ajax(url,post,'hasil5');
 }
}


// Kategori Tindakan Kedelapan //
function cgivinings(trd)
{
 
 document.getElementById("example6").innerHTML="";
 if(trd=="")
 {
    alert("Anda Belum Memilih Kategori");
    document.getElementById("sub6").focus();
    document.getElementById("hasil6").innerHTML="";
    document.getElementById("id_tindakan_7").value="";
    document.getElementById("example6").innerHTML="";
    document.getElementById("nom_7").value="";
    document.getElementById("totalTind_7").value="";
    document.getElementById("tind_diskon_7").value="";
    return false;
 }
 else
 {
    var url="barang_7.php?rand="+Math.random();
    var post="id_kategori="+trd;
    ajax(url,post,'hasil6');
 }
}

// Data Tindakan Obat //
function obt1(id_satuan_1)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_1=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat.php?rand="+Math.random();
    var post="id_satuan_1="+id_satuan_1;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat1');
 }
}

// Data Tindakan Obat //
function obt2(id_satuan_2)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_2=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat1.php?rand="+Math.random();
    var post="id_satuan_2="+id_satuan_2;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat2');
 }
}

// Data Tindakan Obat //
function obt3(id_satuan_3)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_3=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat2.php?rand="+Math.random();
    var post="id_satuan_3="+id_satuan_3;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat3');
 }
}

// Data Tindakan Obat //
function obt4(id_satuan_4)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_4=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat3.php?rand="+Math.random();
    var post="id_satuan_4="+id_satuan_4;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat4');
 }
}

// Data Tindakan Obat //
function obt5(id_satuan_5)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_5=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat4.php?rand="+Math.random();
    var post="id_satuan_5="+id_satuan_5;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat5');
 }
}

// Data Tindakan Obat //
function obt6(id_satuan_6)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_6=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat5.php?rand="+Math.random();
    var post="id_satuan_6="+id_satuan_6;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat6');
 }
}

// Data Tindakan Obat //
function obt7(id_satuan_7)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_7=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat6.php?rand="+Math.random();
    var post="id_satuan_7="+id_satuan_7;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat7');
 }
}

// Data Tindakan Obat //
function obt8(id_satuan_8)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_8=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat7.php?rand="+Math.random();
    var post="id_satuan_8="+id_satuan_8;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat8');
 }
}

// Data Tindakan Obat //
function obt9(id_satuan_9)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_9=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat8.php?rand="+Math.random();
    var post="id_satuan_9="+id_satuan_9;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat9');
 }
}

// Data Tindakan Obat //
function obt10(id_satuan_10)
{
  //alert(id_satuan_1);
 //document.getElementById("detail").innerHTML="";
 if(id_satuan_10=="")
 {
    alert("Anda Belum Memilih Obat");
    /*document.getElementById("kategori").focus();
    document.getElementById("barang").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    return false;
 }
 else
 {
    var url="obat9.php?rand="+Math.random();
    var post="id_satuan_10="+id_satuan_10;
    /*document.getElementById("obt").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
    document.getElementById("totalTind_0").value="";
    document.getElementById("tind_diskon_0").value="";
    */
    ajax(url,post,'pasien_obat10');
 }
}

//------------------RACIKAN

// Data Tindakan Obat //
function racikan1(id_racikan_1)
{
 if(id_racikan_1=="")
 {
    alert("Anda Belum Memilih Obat");
    return false;
 }
 else
 {
    var url="racikan1.php?rand="+Math.random();
    var post="id_racikan_1="+id_racikan_1;
    ajax(url,post,'pasien_racikan1');
 }
}


//------------------END RACIKAN

// Obat Pertama //
/*function obtdetail(id_barang)
{

 var hs = id_barang.split('-');
 if(id_barang=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("barang").focus();
  document.getElementById("detail").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
  document.getElementById("totalTind_0").value="";
  document.getElementById("tind_diskon_0").value="";
  return false;
 }
 else
 {
  var url="get_tindakan.php?rand="+Math.random();
  var post="id_barang="+hs[0];            
  document.getElementById("id_tindakan_0").value=hs[0];
  document.getElementById("detail").innerHTML=formatCurrency(hs[1]);
  document.getElementById("nom_0").value=hs[1];                         
  document.getElementById("totalTind_0").value=formatCurrency(hs[1]);
  //var pp = hs[1].split('.');
  //document.getElementById("totalTind_0").value=pp[0];
  document.getElementById("tind_diskon_0").value=hs[2];
  ajax(url,post,'');
 }
}         
*/

// Tindakan Pertama //
function detail(id_barang)
{

 var hs = id_barang.split('-');
 if(id_barang=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("barang").focus();
  document.getElementById("detail").innerHTML="";
    document.getElementById("id_tindakan_0").value="";
    document.getElementById("detail").innerHTML="";
    document.getElementById("nom_0").value="";
  document.getElementById("totalTind_0").value="";
  document.getElementById("tind_diskon_0").value="";
  return false;
 }
 else
 {
  var url="get_tindakan.php?rand="+Math.random();
  var post="id_barang="+hs[0];            
  document.getElementById("id_tindakan_0").value=hs[0];
  document.getElementById("detail").innerHTML=formatCurrency(hs[1]);
  document.getElementById("nom_0").value=hs[1];                         
  document.getElementById("totalTind_0").value=formatCurrency(hs[1]);
  //var pp = hs[1].split('.');
  //document.getElementById("totalTind_0").value=pp[0];
  document.getElementById("tind_diskon_0").value=0;
  ajax(url,post,'');
 }
}                   


// Tindakan Kedua //
function example(kk)
{
 //alert(kk);
 var ht = kk.split('-');
 if(kk=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil").focus();
  document.getElementById("example").innerHTML="";
    document.getElementById("id_tindakan_1").value="";
    document.getElementById("example").innerHTML="";
    document.getElementById("nom_1").value="";
  document.getElementById("totalTind_1").value="";
  document.getElementById("tind_diskon_1").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_1.php?rand="+Math.random();
  var post="id_barang="+ht[0];
  document.getElementById("id_tindakan_1").value=ht[0];
  document.getElementById("example").innerHTML=formatCurrency(ht[1]);
  document.getElementById("nom_1").value=ht[1];
  document.getElementById("totalTind_1").value=formatCurrency(ht[1]);
  //var py = ht[1].split('.');
  //document.getElementById("totalTind_1").value=py[0];
  document.getElementById("tind_diskon_1").value=0;
  ajax(url,post,'');
 }
} 


// Tindakan Ketiga //
function example1(ee)
{
 //alert(kk);
 var ha = ee.split('-');
 if(ee=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil1").focus();
  document.getElementById("example1").innerHTML="";
    document.getElementById("id_tindakan_2").value="";
    document.getElementById("example1").innerHTML="";
    document.getElementById("nom_2").value="";
  document.getElementById("totalTind_2").value="";
  document.getElementById("tind_diskon_2").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_2.php?rand="+Math.random();
  var post="id_barang="+ha[0];
  document.getElementById("id_tindakan_2").value=ha[0];
  document.getElementById("example1").innerHTML=formatCurrency(ha[1]);
  document.getElementById("nom_2").value=ha[1];
  //var pa = ha[1].split('.');
  //document.getElementById("totalTind_2").value=pa[0];
  document.getElementById("totalTind_2").value=formatCurrency(ha[1]);
  document.getElementById("tind_diskon_2").value=0;
  ajax(url,post,'');
 }
} 


// Tindakan Keempat //
function example2(yy)
{
 //alert(kk);
 var hq = yy.split('-');
 if(yy=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil2").focus();
  document.getElementById("example2").innerHTML="";
    document.getElementById("id_tindakan_3").value="";
    document.getElementById("example2").innerHTML="";
    document.getElementById("nom_3").value="";
  document.getElementById("totalTind_3").value="";
  document.getElementById("tind_diskon_3").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_3.php?rand="+Math.random();
  var post="id_barang="+hq[0];
  document.getElementById("id_tindakan_3").value=hq[0];
  document.getElementById("example2").innerHTML=formatCurrency(hq[1]);
  document.getElementById("nom_3").value=hq[1];
  document.getElementById("totalTind_3").value=formatCurrency(hq[1]);
  //var po = hq[1].split('.');
  //document.getElementById("totalTind_3").value=po[0];
  document.getElementById("tind_diskon_3").value=0;
  ajax(url,post,'');
 }
}     


// Tindakan Kelima //
function example3(oo)
{
 //alert(kk);
 var hy = oo.split('-');
 if(oo=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil3").focus();
  document.getElementById("example3").innerHTML="";
    document.getElementById("id_tindakan_4").value="";
    document.getElementById("example3").innerHTML="";
    document.getElementById("nom_4").value="";
  document.getElementById("totalTind_4").value="";
  document.getElementById("tind_diskon_4").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_4.php?rand="+Math.random();
  var post="id_barang="+hy[0];
  document.getElementById("id_tindakan_4").value=hy[0];
  document.getElementById("example3").innerHTML=formatCurrency(hy[1]);
  document.getElementById("nom_4").value=hy[1];
  document.getElementById("totalTind_4").value=formatCurrency(hy[1]);
  //var pi = hy[1].split('.');
  //document.getElementById("totalTind_4").value=pi[0];
  document.getElementById("tind_diskon_4").value=0;
  ajax(url,post,'');
 }
}


// Tindakan Keenam //
function example4(oe)
{
 //alert(kk);
 var hio = oe.split('-');
 if(oe=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil4").focus();
  document.getElementById("example4").innerHTML="";
    document.getElementById("id_tindakan_5").value="";
    document.getElementById("example4").innerHTML="";
    document.getElementById("nom_5").value="";
  document.getElementById("totalTind_5").value="";
  document.getElementById("tind_diskon_5").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_5.php?rand="+Math.random();
  var post="id_barang="+hio[0];
  document.getElementById("id_tindakan_5").value=hio[0];
  document.getElementById("example4").innerHTML=formatCurrency(hio[1]);
  document.getElementById("nom_5").value=hio[1];
  document.getElementById("totalTind_5").value=formatCurrency(hio[1]);
  //var pj = hio[1].split('.');
  //document.getElementById("totalTind_5").value=pj[0];
  document.getElementById("tind_diskon_5").value=formatCurrency('0');
  ajax(url,post,'');
 }
} 


// Tindakan Ketujuh //
function example5(voc)
{
 //alert(kk);
 var hius = voc.split('-');
 if(voc=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil5").focus();
  document.getElementById("example5").innerHTML="";
    document.getElementById("id_tindakan_6").value="";
    document.getElementById("example5").innerHTML="";
    document.getElementById("nom_6").value="";
    document.getElementById("totalTind_6").value="";
    document.getElementById("tind_diskon_6").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_6.php?rand="+Math.random();
  var post="id_barang="+hius[0];
  document.getElementById("id_tindakan_6").value=hius[0];
  document.getElementById("example5").innerHTML=formatCurrency(hius[1]);
  document.getElementById("nom_6").value=hius[1];
  document.getElementById("totalTind_6").value=formatCurrency(hius[1]);
  //var pio = hius[1].split('.');
  //document.getElementById("totalTind_6").value=pio[0];
  document.getElementById("tind_diskon_6").value=formatCurrency('0');
  ajax(url,post,'');
 }
}


// Tindakan Kedelapan //
function example6(doc)
{
 //alert(kk);                                           
 var haus = doc.split('-');
 if(doc=="")
 {
  alert("Anda Belum Memilih Tindakan");
  document.getElementById("hasil6").focus();
  document.getElementById("example6").innerHTML="";
    document.getElementById("id_tindakan_7").value="";
    document.getElementById("example6").innerHTML="";
    document.getElementById("nom_7").value="";
    document.getElementById("totalTind_7").value="";
    document.getElementById("tind_diskon_7").value="";
  return false;
 }
 else
 {
  var url="get_tindakan_7.php?rand="+Math.random();
  var post="id_barang="+haus[0];
  document.getElementById("id_tindakan_7").value=haus[0];
  document.getElementById("example6").innerHTML=formatCurrency(haus[1]);
  document.getElementById("nom_7").value=haus[1];
  document.getElementById("totalTind_7").value=formatCurrency(haus[1]);
  //var paulo = haus[1].split('.');
  //document.getElementById("totalTind_7").value=paulo[0];
  document.getElementById("tind_diskon_7").value=formatCurrency('0');
  ajax(url,post,'');
 }
}               


/**Ajax**/
/*out_response*/
function out_response(response)
{
   if(response=="barang")
   {document.getElementById("barang").innerHTML=xmlhttp.responseText;}
   else if(response=="detail")
   {
   document.getElementById("detail").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_0").value=xmlhttp.responseText;
   } 
   else if(response=="hasil") 
   {document.getElementById("hasil").innerHTML=xmlhttp.responseText;}
   else if(response=="example")
   {
   document.getElementById("example").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_1").value=xmlhttp.responseText; 
   }
   else if(response=="hasil1") 
   {document.getElementById("hasil1").innerHTML=xmlhttp.responseText;}
   else if(response=="example1")
   {
   document.getElementById("example1").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_2").value=xmlhttp.responseText; 
   }
   else if(response=="hasil2") 
   {document.getElementById("hasil2").innerHTML=xmlhttp.responseText;}
   else if(response=="example2")
   {
   document.getElementById("example2").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_3").value=xmlhttp.responseText; 
   }
   else if(response=="hasil3") 
   {document.getElementById("hasil3").innerHTML=xmlhttp.responseText;}
   else if(response=="example3")
   {
   document.getElementById("example3").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_4").value=xmlhttp.responseText; 
   }
   else if(response=="hasil4") 
   {document.getElementById("hasil4").innerHTML=xmlhttp.responseText;}
   else if(response=="example4")
   {
   document.getElementById("example4").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_5").value=xmlhttp.responseText; 
   }
   else if(response=="hasil5") 
   {document.getElementById("hasil5").innerHTML=xmlhttp.responseText;}
   else if(response=="example5")
   {
   document.getElementById("example5").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_6").value=xmlhttp.responseText; 
   }
   else if(response=="hasil6") 
   {document.getElementById("hasil6").innerHTML=xmlhttp.responseText;}
   else if(response=="example6")
   {
   document.getElementById("example6").innerHTML=xmlhttp.responseText;
   document.getElementById("nom_7").value=xmlhttp.responseText; 
   }
   else if(response=="pasien_obat1")
   {document.getElementById("pasien_obat1").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat2")
   {document.getElementById("pasien_obat2").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat3")
   {document.getElementById("pasien_obat3").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat4")
   {document.getElementById("pasien_obat4").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat5")
   {document.getElementById("pasien_obat5").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat6")
   {document.getElementById("pasien_obat6").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat7")
   {document.getElementById("pasien_obat7").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat8")
   {document.getElementById("pasien_obat8").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat9")
   {document.getElementById("pasien_obat9").innerHTML=xmlhttp.responseText;}
   else if(response=="pasien_obat10")
   {document.getElementById("pasien_obat10").innerHTML=xmlhttp.responseText;}
}
/*--------*/

/*ajax*/
function ajax(url,post,response)
{
 xmlhttp=GetXmlHttpObject();
 xmlhttp.onreadystatechange=function()
 {
  if(xmlhttp.readyState==4)
  {
   if(xmlhttp.status==200)
   {
    out_response(response);
   }
   else{ajax_fail();}
  }
 }
 xmlhttp.open("POST",url,true);
 xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 xmlhttp.send(post);
}
/*--------*/

/*ajax_fail*/
/*function ajax_fail()
{
 alert("Maaf ada gangguan penerimaan data, silahkan coba lagi atau refresh browser anda.");
 return false;
}*/
/*--------*/

/*pilih xmlhttp berdasarkan browser*/
function GetXmlHttpObject()
{
 if(window.XMLHttpRequest)
 {
  return new XMLHttpRequest();
 }
 if(window.ActiveXObject)
 {
  return new ActiveXObject("Microsoft.XMLHTTP");
 }
 else
 {alert("Maaf browser anda tidak mendukung ajax.");}
 return false;
}
/*--------*/
/**--------**/
//-->