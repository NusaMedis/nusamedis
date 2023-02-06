<?php
// echo "total fol".count($dataFolioPas);
// print_r($dataFolioPas);

// Konfig COA PENDAPATAN APOTEK
$sql = "select * from gl.gl_konf";
$konf = $dtaccess->Fetch($sql);

for($m=0,$n=count($dataFolioPas);$m<$n;$m++)
     {
              $sql = "select id_prk from klinik.klinik_biaya
                      where biaya_id = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_biaya"]);
   //           if ($_POST["dep_posting_poli"]=="y") $sql .= " and id_poli = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);         
               //echo $sql; 
               $dataPrkFolio = $dtaccess->Fetch($sql);
               if ($dataFolioPas[$m]['id_biaya'] == '9999999') {
                 if ($_POST['reg_tipe_rawat'] == 'J') {
                   $prkId = $konf['dep_coa_pendapatan_apotik_irj'];
                 }elseif ($_POST['reg_tipe_rawat'] == 'I') {
                   $prkId = $konf['dep_coa_pendapatan_apotik_irna'];
                 }elseif ($_POST['reg_tipe_rawat'] == 'G') {
                   $prkId = $konf['dep_coa_pendapatan_apotik_igd'];
                 }
               }else{
                $prkId = $dataPrkFolio['id_prk'];
               }
        if ($dataFolioPas[$m]['id_biaya'] != '9999999') {
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
          if($dataFolioPas[$m]["fol_nominal"]<0){
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          } else {  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'."".StripCurrency($dataFolioPas[$m]["fol_nominal"]));
          }
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
          //print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
        }
    
          //update penjualan obat
          // if($dataFolioPas[$m]["fol_jenis"]=='O' || $dataFolioPas[$m]["fol_jenis"]=='OA' ||
          // $dataFolioPas[$m]["fol_jenis"]=='OI' || $dataFolioPas[$m]["fol_jenis"]=='OG' ||
          // $dataFolioPas[$m]["fol_jenis"]=='R' || $dataFolioPas[$m]["fol_jenis"]=='RA' ||
          // $dataFolioPas[$m]["fol_jenis"]=='RI' || $dataFolioPas[$m]["fol_jenis"]=='RG' ||
          // $dataFolioPas[$m]["fol_jenis"]=='I') {
          
          // $sql = "update apotik.apotik_penjualan set penjualan_terbayar ='y' where 
          //       penjualan_nomor = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_catatan"]);
          // $rs = $dtaccess->Execute($sql);      
          // // echo $sql; die();
          // }
    } //Akhir Looping Folio Pasien
?>