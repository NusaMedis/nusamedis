var xmlHttp

function showTindakan9(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan9.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged9
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged9(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint9").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind9").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint9").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_9").value='1'
	}
}

function showTotTindakan9(jml){
  var biaya = document.getElementById('inputHint9').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind9").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_9").value='1'
  }else{
   document.getElementById("txtTotTind9").innerHTML=formatCurrency(total)
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

