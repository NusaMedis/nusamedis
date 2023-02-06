///////////////////////////////
/*
Programer : Agustinus Verry Ricki
Describe  : File Ajax untuk tampilan pilihan dokter
*/
//////////////////////////////

var xmlhttppengirimdata = false;

try {
	xmlhttppengirimdata = new ActiveXObject("Msxml2.XMLHTTP");
} catch (e) {
	try {
		xmlhttppengirimdata = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
		xmlhttppengirimdata = false;
	}
}

if (!xmlhttppengirimdata && typeof XMLHttpRequest != 'undefined') {
	xmlhttppengirimdata = new XMLHttpRequest();
}

//untuk tampilkan pengirimdata
function pengirimdata(id_pengirim)
{
	var obj=document.getElementById("pengirim-view");
	var url='pengirim.php?id_pengirim='+id_pengirim;
	xmlhttppengirimdata.open("GET", url);
	
	xmlhttppengirimdata.onreadystatechange = function() {
		if ( xmlhttppengirimdata.readyState == 4 && xmlhttppengirimdata.status == 200 ) {
			obj.innerHTML = xmlhttppengirimdata.responseText; 
		} else {
			obj.innerHTML = "<div align ='center'><img src='waiting.gif' alt='Loading' /></div>";
		}
	}
	xmlhttppengirimdata.send(null);

}

