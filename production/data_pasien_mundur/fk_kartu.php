<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
	 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
   	 $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
     $userName = $auth->GetUserName();
	 
	 
	if(isset($_POST['reg_id'])) { 
		# data registrasi
		$sql = "select a.*, b.cust_usr_nama, c.poli_nama from klinik.klinik_registrasi a
				left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join global.global_auth_poli c on a.id_poli = c.poli_nama
              where reg_id = ".QuoteValue(DPE_CHAR,$_POST["reg_id"]);
		$reg = $dtaccess->Fetch($sql);

		$sql = "select a.*,b.*, c.*  from klinik.klinik_biaya_kartu_berobat a 
               left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
               left join klinik.klinik_biaya_tarif c on b.biaya_id = c.id_biaya";
              //where a.id_poli = ".QuoteValue(DPE_CHAR,$reg["id_poli"]);
		$daftar = $dtaccess->Fetch($sql);
    
      if($daftar) {
               $dbTable = "klinik.klinik_folio";
              $dbField[0] = "fol_id";   // PK
              $dbField[1] = "id_reg";
              $dbField[2] = "fol_nama";
              $dbField[3] = "fol_nominal";
              $dbField[4] = "fol_jenis";
              $dbField[5] = "id_cust_usr";
              $dbField[6] = "fol_waktu";
              $dbField[7] = "fol_lunas";
              $dbField[8] = "id_biaya";
              $dbField[9] = "id_poli";
              $dbField[10] = "fol_jenis_pasien";
              $dbField[11] = "id_dep";
              $dbField[12] = "who_when_update";
              $dbField[13] = "id_dokter";
              $dbField[14] = "fol_total_harga";
              $dbField[15] = "fol_jumlah";
              $dbField[16] = "fol_nominal_satuan"; 
              $dbField[17] = "fol_hrs_bayar";
              $dbField[18] = "fol_dijamin";
              $dbField[19] = "id_pembayaran";
              $dbField[20] = "tindakan_tanggal";
              $dbField[21] = "tindakan_waktu";
              $dbField[22] = "id_biaya_tarif";
                
			         $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$reg['reg_id']);
               $dbValue[2] = QuoteValue(DPE_CHAR,$daftar["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($daftar["biaya_total"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$daftar["biaya_tarif_jenis"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$reg["id_cust_usr"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,$daftar["biaya_id"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$reg["id_poli"]);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$reg["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_CHAR,$userId);
               $dbValue[13] = QuoteValue(DPE_CHAR,$reg["id_dokter"]);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($daftar["biaya_total"]));
               $dbValue[15] = QuoteValue(DPE_NUMERIC,'1');
               $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($daftar["biaya_total"]));
               if($reg["reg_jenis_pasien"]=="5" || $reg["reg_jenis_pasien"]=="7" || $reg["reg_jenis_pasien"]=="18" || $reg["reg_jenis_pasien"]=='26'){
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency(0));
               } else {
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($daftar["biaya_total"]));
               } 
               if($reg["reg_jenis_pasien"]=="5" || $reg["reg_jenis_pasien"]=="7" || $reg["reg_jenis_pasien"]=="18" || $reg["reg_jenis_pasien"]=='26'){
               $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($daftar["biaya_total"]));
               } else {
               $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency(0));
               }
               $dbValue[19] = QuoteValue(DPE_CHAR,$reg['id_pembayaran']);
               $dbValue[20] = QuoteValue(DPE_DATE,date('Y-m-d'));
               $dbValue[21] = QuoteValue(DPE_DATE,date('H:i:s'));
               $dbValue[22] = QuoteValue(DPE_CHAR,$daftar["biaya_tarif_id"]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               $dtmodel->Insert() or die("insert error"); 

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);                      
               unset($dbKey);
			   
			   
			   echo $reg['cust_usr_nama']." - ".$reg['poli_nama']." - SUKSES";
   
              }
       
	}
?>

<html>
<body>
	<form method="POST">
		<input type="text" name="reg_id">
		<input type="submit" name="submit" value="submit">
	</form>
</body>
</html>