// JavaScript Document

function Logout()
{
    if(confirm('Apakah anda yakin ingin keluar?')) window.parent.document.location.href='logout.php';
    else return false;
}

$(function() {
	$('#loading').ajaxStart(function(){
		$(this).fadeIn();
	}).ajaxStop(function(){
		$(this).fadeOut();
	});

	$('#left a').click(function() {
		var url = $(this).attr('href');
		$('#right').load(url);
		return false;
	}); 
});

