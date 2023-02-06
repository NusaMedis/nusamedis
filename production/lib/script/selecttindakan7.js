var xmlHttp

function showTindakan6(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan6.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged6
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged6(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint6").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind6").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint6").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_6").value='1'
	}
}

function showTotTindakan6(jml){
  var biaya = document.getElementById('inputHint6').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind6").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_6").value='1'
  }else{
   document.getElementById("txtTotTind6").innerHTML=formatCurrency(total)
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

