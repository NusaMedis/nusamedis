<?php
// echo "total fol".count($dataFolioPas);
// print_r($dataFolioPas);

// Konfig COA PENDAPATAN APOTEK
$sql = "select * from gl.gl_konf";
$konf = $dtaccess->Fetch($sql);

//Data Apotik
$sql = "select a.id_reg,* from apotik.apotik_penjualan a 
        left join klinik.klinik_folio b on b.fol_id = a.id_fol
        left join klinik.klinik_registrasi c on c.reg_id = a.id_reg
        left join global.global_auth_poli d on d.poli_id = c.id_poli
        where b.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'])." and fol_lunas = 'n'";
$dataApotik = $dtaccess->FetchAll($sql);
   // echo $sql;die();
for ($i = 0; $i < count($dataApotik); $i++) {
 if ($dataApotik[$i]['penjualan_total']!='') {
  if ($dataFolioPas[$m]['id_biaya']='9999999') {
   if ($dataApotik[$i]['poli_nama'] == 'Apotek RSIA') {
    $prkId = $konf['dep_coa_pendapatan_apotik_irna'];
    $prkIdPPN = $konf['dep_coa_ppn_keluar_apotik_irna'];
    $prkIdTuslag = $konf['dep_coa_tuslag_apotik_irna'];
    $prkIdRacikan = $konf['dep_coa_biaya_racikan_irna'];
   }elseif ($dataApotik[$i]['poli_nama'] == 'Apotek GRAHA') {
    $prkId = $konf['dep_coa_pendapatan_apotik_irj'];
    $prkIdPPN = $konf['dep_coa_ppn_keluar_apotik_irj'];
    $prkIdTuslag = $konf['dep_coa_tuslag_apotik_irj'];
    $prkIdRacikan = $konf['dep_coa_biaya_racikan'];
   }
    
    //Pendapatan
    $dbTable = "gl.gl_buffer_transaksidetil";

    $dbField[0]  = "id_trad";   // PK
    $dbField[1]  = "tra_id";
    $dbField[2]  = "prk_id";
    $dbField[3]  = "ket_trad";
    $dbField[4]  = "job_id";
    $dbField[5]  = "dept_id";
    $dbField[6]  = "jumlah_trad";
    $dbField[7]  = "id_poli";
    $dbField[8]  = "id_instalasi";
    $dbField[9]  = "id_fol";

    $transaksiDetailId = $dtaccess->GetTransId();

    $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
    $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$prkId);
    $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
    if($dataApotik[$i]["penjualan_biaya_pokok"]<0){
    $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik[$i]["penjualan_biaya_pokok"])));
    } else {  
    $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik[$i]["penjualan_biaya_pokok"]));
    }
    $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
    $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

    $dtmodel->Insert() or die("insert  error"); 
      
    unset($dbField);
    unset($dbValue);
    unset($dataPrkFolio);

    //PPN
    $dbTable = "gl.gl_buffer_transaksidetil";

    $dbField[0]  = "id_trad";   // PK
    $dbField[1]  = "tra_id";
    $dbField[2]  = "prk_id";
    $dbField[3]  = "ket_trad";
    $dbField[4]  = "job_id";
    $dbField[5]  = "dept_id";
    $dbField[6]  = "jumlah_trad";
    $dbField[7]  = "id_poli";
    $dbField[8]  = "id_instalasi";
    $dbField[9]  = "id_fol";

    $transaksiDetailId = $dtaccess->GetTransId();

    $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
    $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdPPN);
    $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
    if($dataApotik[$i]["penjualan_pajak"]<0){
    $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik[$i]["penjualan_pajak"])));
    } else {  
    $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik[$i]["penjualan_pajak"]));
    }
    $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
    $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

    $dtmodel->Insert() or die("insert  error"); 
      
    unset($dbField);
    unset($dbValue);
    unset($dataPrkFolio);

    //Tuslag
    $dbTable = "gl.gl_buffer_transaksidetil";

    $dbField[0]  = "id_trad";   // PK
    $dbField[1]  = "tra_id";
    $dbField[2]  = "prk_id";
    $dbField[3]  = "ket_trad";
    $dbField[4]  = "job_id";
    $dbField[5]  = "dept_id";
    $dbField[6]  = "jumlah_trad";
    $dbField[7]  = "id_poli";
    $dbField[8]  = "id_instalasi";
    $dbField[9]  = "id_fol";

    $transaksiDetailId = $dtaccess->GetTransId();

    $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
    $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdTuslag);
    $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
    if($dataApotik[$i]["penjualan_tuslag"]<0){
    $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataApotik[$i]["penjualan_tuslag"])));
    } else {  
    $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataApotik[$i]["penjualan_tuslag"]));
    }
    $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
    $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

    $dtmodel->Insert() or die("insert  error"); 
      
    unset($dbField);
    unset($dbValue);
    unset($dataPrkFolio);

    //Racikan
    if ($dataApotik[$i]['penjualan_biaya_racikan'] > 0) {
      $dbTable = "gl.gl_buffer_transaksidetil";

      $dbField[0]  = "id_trad";   // PK
      $dbField[1]  = "tra_id";
      $dbField[2]  = "prk_id";
      $dbField[3]  = "ket_trad";
      $dbField[4]  = "job_id";
      $dbField[5]  = "dept_id";
      $dbField[6]  = "jumlah_trad";
      $dbField[7]  = "id_poli";
      $dbField[8]  = "id_instalasi";
      $dbField[9]  = "id_fol";

      $transaksiDetailId = $dtaccess->GetTransId();

      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdRacikan);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.$dataApotik[$i]["penjualan_biaya_racikan"]);
      $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
      $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
      $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

      $dtmodel->Insert() or die("insert  error"); 
        
      unset($dbField);
      unset($dbValue);
    }

    $sql = "update apotik.apotik_penjualan set penjualan_terbayar ='y' where 
            penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataApotik[$i]["penjualan_id"]);
    $rs = $dtaccess->Execute($sql); 
  }
 }
}
?>