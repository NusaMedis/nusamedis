var xmlHttp

function showTindakan1(str){
	xmlHttp=GetXmlHttpObject()
	if(xmlHttp==null){
		alert("Browser anda tidak support")
		return
}
var url="get_tindakan1.php"

url=url+"?q="+str
xmlHttp.onreadystatechange=stateChanged1
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function stateChanged1(){
	if(xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){
		document.getElementById("txtHint1").innerHTML=xmlHttp.responseText
		document.getElementById("txtTotTind1").innerHTML=xmlHttp.responseText
			document.getElementById("inputHint1").value=xmlHttp.responseText
    document.getElementById("txtTotalTind_1").value='1'
	}
}

function showTotTindakan1(jml){
  var biaya = document.getElementById('inputHint1').value.toString().replace(/\,/g,"");
   var total = biaya*jml*1;
   //alert(jml);
  if(jml=='0'){
   document.getElementById("txtTotTind1").innerHTML=formatCurrency(biaya)
   document.getElementById("txtTotalTind_1").value='1'
  }else{
   document.getElementById("txtTotTind1").innerHTML=formatCurrency(total)
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
