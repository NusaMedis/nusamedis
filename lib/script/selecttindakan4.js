var xmlHttp

function showTindakan3(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan3.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged3
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged3(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint3").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind3").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint3").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_3").value='1'
	}
}

function showTotTindakan3(jml){
  var biaya = document.getElementById('inputHint3').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind3").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_3").value='1'
  }else{
   document.getElementById("txtTotTind3").innerHTML=formatCurrency(total)
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
