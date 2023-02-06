var xmlHttp

function showTindakan4(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan4.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged4
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged4(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint4").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind4").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint4").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_4").value='1'
	}
}

function showTotTindakan4(jml){
  var biaya = document.getElementById('inputHint4').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind4").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_4").value='1'
  }else{
   document.getElementById("txtTotTind4").innerHTML=formatCurrency(total)
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

