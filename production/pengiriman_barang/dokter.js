///////////////////////////////
/*
Programer : Agustinus Verry Ricki
Describe  : File Ajax untuk tampilan pilihan dokter
*/
//////////////////////////////

var xmlhttpdokter = false;

try {
	xmlhttpdokter = new ActiveXObject("Msxml2.XMLHTTP");
} catch (e) {
	try {
		xmlhttpdokter = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
		xmlhttpdokter = false;
	}
}

if (!xmlhttpdokter && typeof XMLHttpRequest != 'undefined') {
	xmlhttpdokter = new XMLHttpRequest();
}

//untuk tampilkan dokter
function dokter(id_penerima)
{
	var obj=document.getElementById("dokter-view");
	var url='dokter.php?id_penerima='+id_penerima;
	xmlhttpdokter.open("GET", url);
	
	xmlhttpdokter.onreadystatechange = function() {
		if ( xmlhttpdokter.readyState == 4 && xmlhttpdokter.status == 200 ) {
			obj.innerHTML = xmlhttpdokter.responseText; 
		} else {
			obj.innerHTML = "<div align ='center'><img src='waiting.gif' alt='Loading' /></div>";
		}
	}
	xmlhttpdokter.send(null);

}

