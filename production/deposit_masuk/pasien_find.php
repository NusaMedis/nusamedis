<?php
require_once("../penghubung.inc.php");
require_once($ROOT . "lib/login.php");
require_once($ROOT . "lib/encrypt.php");
require_once($ROOT . "lib/datamodel.php");
require_once($ROOT . "lib/dateLib.php");
require_once($ROOT . "lib/expAJAX.php");
require_once($ROOT . "lib/tampilan.php");

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userId = $auth->GetUserId();
$userData = $auth->GetUserData();
$userName = $auth->GetUserName();

if (!$_POST["klinik"]) $klinik = $depId;
else $klinik = $_POST["klinik"];

//  if(!$auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_READ)){
//      die("access_denied");
//      exit(1);

// } elseif($auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_READ)===1){
//      echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
//      exit(1);
// }

$plx = new expAJAX("GetData");

function GetData($in_nama, $in_kode)
{
	global $dtaccess, $ROOT, $idPrj, $idCust, $depId, $userData, $userId;

	$table = new InoTable("table1", "100%", "center", null, 0, 5, 1, null, "tblForm");

	$sql_where[] = "1=1";
	if ($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like " . QuoteValue(DPE_CHAR, strtoupper("%" . $in_nama . "%"));
	if ($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like " . QuoteValue(DPE_CHAR, strtoupper("%" . $in_kode . "%"));
	//if($in_alamat) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_alamat."%"));
	//if($in_umur) $sql_where[] = "((current_date - cust_usr_tanggal_lahir)/365)=".$in_umur;
	//if($in_jk) $sql_where[] = "UPPER(cust_usr_jenis_kelamin) = ".QuoteValue(DPE_CHAR,strtoupper($in_jk));
	//  if($userData["rol"]=='2') { 
	//       $sql_where[] = " id_dokter =".QuoteValue(DPE_CHAR,$userId);
	//   } else {
	//       if($dokter) $sql_where[] = "id_dokter = ".QuoteValue(DPE_CHAR,$dokter);
	//}
	//	if($klinik) $sql_where[] = "id_dep = ".QuoteValue(DPE_CHAR,"%".$klinik."%");  

	$sql_where = implode(" and ", $sql_where);

	// --- cari data krsnya ---
	$sql = "select cust_usr_kode, cust_usr_nama, cust_usr_id, reg_status, reg_id
			from klinik.klinik_registrasi a 
			left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr ";
	$sql .= " where " . $sql_where;
	$sql .= " and reg_tipe_rawat = 'I' and (reg_status = 'I2' or reg_status = 'I3') and id_pembayaran in(select pembayaran_id from klinik.klinik_pembayaran where pembayaran_flag = 'n')
		group by cust_usr_id, reg_status, reg_id order by cust_usr_nama asc";
	//return $sql;		
	// echo $sql;
	$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;
	$counter = 0;

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "No. RM";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	/*$tbHeader[0][$counter][TABLE_ISI] = "Klinik";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;*/

	$tbHeader[0][$counter][TABLE_ISI] = "Alamat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;


	for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {

		($i % 2 == 0) ? $class = "tablecontent" : $class = "tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i + 1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . $dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		/*$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["dep_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;*/

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;" . nl2br($dataTable[$i]["cust_usr_alamat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = '<img src="' . $ROOT . 'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\'' . $dataTable[$i]["cust_usr_id"] . '\',\'' . $dataTable[$i]["cust_usr_kode"] . '\',\'' . $dataTable[$i]["reg_id"] . '\',\'' . $dataTable[$i]["id_dep"] . '\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
	}

	$str = $table->RenderView($tbHeader, $tbContent, $tbBottom);

	return $str;
}

$optionJK[0] = $view->RenderOption("", "[All]", $show);
$optionJK[1] = $view->RenderOption("L", "Laki-laki", $show);
$optionJK[2] = $view->RenderOption("P", "Perempuan", $show);

//ambil nama dokter e
$sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like " . QuoteValue(DPE_CHAR, "%" . $klinik . "%") . " order by usr_id asc ";
$rs = $dtaccess->Execute($sql);
$dataDokter = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_departemen where dep_id like " . QuoteValue(DPE_CHAR, "%" . $depId . "%") . " order by dep_id";
//echo $sql;
$rs = $dtaccess->Execute($sql);
$dataKlinik = $dtaccess->FetchAll($rs);
?>

<br />

<script language="JavaScript">
	<?php $plx->Run(); ?>

	function sendValue(id, kode, reg, dep) {

		self.parent.document.getElementById('id_cust_usr').value = id;
		self.parent.document.getElementById('cust_usr_kode').value = kode;
		self.parent.document.getElementById('klinik').value = dep;
		self.parent.document.getElementById('id_reg').value = reg;
		self.parent.tb_remove();
	}

	function Search() {
		var nama = document.getElementById('_name').value;
		var kode = document.getElementById('_kode').value;
		<?php //if($userData["rol"]!='2') { 
		?>
		//var dokter = document.getElementById('id_dokter').value;
		<?php //} 
		?>
		//var klinik = document.getElementById('klinik').value;
		//var alamat= document.getElementById('_alamat').value;
		//var umur = document.getElementById('_umur').value;
		//var jk = document.getElementById('_jk').value;
		//GetData(nama,kode,alamat,umur,jk,'target=dv_hasil');  yang lama
		<?php //if($userData["rol"]!='2') { 
		?>
		//GetData(nama,kode,dokter,klinik,'target=dv_hasil');
		<?php //} else { 
		?>
		GetData(nama, kode, 'target=dv_hasil');
		<?php //} 
		?>
	}
</script>

<div id="body">
	<br />
	<form name="frmSearch">
		<table border="1" width="100%" cellpadding="1" cellspacing="1">
			<tr>
				<td>
					<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
						<tr class="tablecontent-odd">
							<td colspan="2">
								<center>PENCARIAN&nbsp;</center>
							</td>
						</tr>
						<tr>
							<td align="right" class="tablecontent" width="30%">Nama Pasien</td>
							<td class="tablecontent">
								<?php echo $view->RenderTextBox("_name", "_name", 30, 200, $_POST["_name"], false, false); ?>
							</td>
						</tr>
						<tr>
							<td align="right" class="tablecontent" width="30%">No. RM</td>
							<td class="tablecontent">
								<?php echo $view->RenderTextBox("_kode", "_kode", 30, 200, $_POST["_kode"], false, false); ?>
							</td>
						</tr>
						<!--<tr>
				<td align="right" class="tablecontent" width="30%">Alamat</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_alamat", "_alamat", 30, 200, $_POST["_alamat"], false, false); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Umur</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_umur", "_umur", 30, 200, $_POST["_umur"], false, false); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Jenis Kelamin</td>
				<td class="tablecontent">
					<?php echo $view->RenderComboBox("_jk", "_jk", $optionJK); ?>
				</td>
			</tr>  -->
						<tr>
							<td colspan="2">
								<center>
									<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search()" />
									<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="submit" /></center>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php echo $view->SetFocus("_name", true); ?>
	</form>

	<div id="dv_hasil"></div>

</div>