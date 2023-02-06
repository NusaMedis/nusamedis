<?php
require_once("penghubung.inc.php");
require_once($ROOT."lib/dataaccess.php");
require_once($ROOT."lib/login.php");

$dtaccess = new DataAccess();

$sql = "select * from global.global_app where app_id='14'" ;
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataTable = $dtaccess->FetchAll($rs);

if($_GET["user"]) $_POST["txtUser"] = $_GET["user"];
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>.:: SIKTA ONLINE - Login ::.</title>
<link href="lib/css/expMobile.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>lib/script/elements.js"></script>
</head>
<body onLoad="document.frmLogin.txtUser.focus();">
<table bgcolor="#ffffff" border=1 cellpadding=0 cellspacing=0 width="100%" valign="top" height="50%">
<tr><td>
	<div align="center"><table  align="center" border=1 valign="top"  cellpadding=1 cellspacing=0 width="10%">
		<tr><td background="gambar/mlogo_sikita.png" height=50 valign=top>
			<table align="center" border=1 valign="top"  cellpadding=0 cellspacing=2 width=80>
				<tr><td height=45 colspan=2>&nbsp;</td></tr>
				<tr><form name="frmLogin" action="check.php" method="post">
				      <tr><td >&nbsp;</td></tr>
				<td align="left" >&nbsp;&nbsp;&nbsp;&nbsp;<input type=text size=3 class="inputField" name="txtUser" onKeyDown="return tabOnEnter_select_with_button(this, event);"></td></tr>
				<tr>
        <td align="left" valign="bottom">&nbsp;&nbsp;&nbsp;&nbsp;<input type="password" size=3 class="inputField" name="txtPass" onKeyDown="return tabOnEnter_select_with_button(this, event);"></td></tr>

				<tr><td align="center" colspan=0>
        <input type="submit" value="Login" class="button"></td></tr>
        				<input type="hidden" name="cmbSystem" value="14">
       
                        
				</form>
			</table>
		  </td></tr>
      <tr><td align="center" class="logonLabel"><font color="red">
      <?php if($_GET["msg"]=="kode_eror01") echo "Login Gagal.<br />Username atau Password salah."; 
      elseif($_GET["msg"]=="kode_eror02") echo "Akses Ditolak.<br />User tidak berhak masuk aplikasi.";
      ?></font></td></tr>
	</table>
</div>
</td></tr>

</table>
</body>
</html>

