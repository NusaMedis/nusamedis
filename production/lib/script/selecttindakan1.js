var xmlHttp

function showTindakan(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}


function stateChanged(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind").innerHTML=xmlHttp.responseText
		document.getElementById("inputHint").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_0").value='1'
	}
}

function showTotTindakan(jml){
  // var biaya = xmlHttp.responseText.toString().replace(/\,/g,"");
  var biaya = document.getElementById('inputHint').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_0").value='1'
  }else{
   document.getElementById("txtTotTind").innerHTML=formatCurrency(total)
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

