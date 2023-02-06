var xmlHttp

function showTindakan2(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan2.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged2
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged2(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint2").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind2").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint2").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_2").value='1'
	}
}

function showTotTindakan2(jml){
  var biaya = document.getElementById('inputHint2').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind2").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_2").value='1'
  }else{
   document.getElementById("txtTotTind2").innerHTML=formatCurrency(total)
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
