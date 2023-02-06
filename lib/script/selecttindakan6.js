var xmlHttp

function showTindakan5(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan5.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged5
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged5(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint5").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind5").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint5").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_5").value='1'
	}
}

function showTotTindakan5(jml){
  var biaya = document.getElementById('inputHint5').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind5").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_5").value='1'
  }else{
   document.getElementById("txtTotTind5").innerHTML=formatCurrency(total)
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

