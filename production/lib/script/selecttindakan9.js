var xmlHttp

function showTindakan8(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan8.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged8
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged8(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint8").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind8").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint8").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_8").value='1'
	}
}

function showTotTindakan8(jml){
  var biaya = document.getElementById('inputHint8').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind8").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_8").value='1'
  }else{
   document.getElementById("txtTotTind8").innerHTML=formatCurrency(total)
  }
   
}

function GetXmlHttpObject(){
	var xmlHttp=null;
	
	try{
		xmlHttp=new XMLHttpRequest();
	}catch(e){
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xmlHttp;
}

