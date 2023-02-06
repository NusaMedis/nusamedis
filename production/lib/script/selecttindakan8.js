var xmlHttp

function showTindakan7(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan7.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged7
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged7(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint7").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind7").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint7").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_7").value='1'
	}
}

function showTotTindakan7(jml){
  var biaya = document.getElementById('inputHint7').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind7").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_7").value='1'
  }else{
   document.getElementById("txtTotTind7").innerHTML=formatCurrency(total)
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

