<?php
      
      $sql = "SELECT penjualan_create, penjualan_grandtotal from apotik.apotik_penjualan where penjualan_id = ".QuoteValue(DPE_CHAR, $penjualanId);
      $tanggal = $dtaccess->Fetch($sql); 

      $tanggal['penjualan_create'] = ($tanggal['penjualan_grandtotal'] == 0 || $tanggal['penjualan_grandtotal'] == null) ? date("Y-m-d H:i:s") : $tanggal['penjualan_create'] ;

      $sql = "UPDATE apotik.apotik_penjualan_detail set penjualan_detail_create = '".$tanggal['penjualan_create']."' where id_penjualan = '$penjualanId'";
      $dtaccess->Execute($sql);

// $_POST["penjualan_total_obat"]=$_POST["txtBalik"];
      $sql = "select sum(penjualan_detail_total) as penjualan_total_detail, sum(penjualan_detail_harga_beli) as hargabeli, sum(penjualan_detail_ppn) as ppn, sum(penjualan_detail_tuslag) as tuslag, sum(penjualan_detail_harga_pokok) as harga_pokok from apotik.apotik_penjualan_detail  where 
      id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
      $rs = $dtaccess->Execute($sql);
      $total = $dtaccess->Fetch($rs);
         
      $_POST["hargabeli"] = $total["hargabeli"];
      $_POST["ppn"] = $total["ppn"];
      $_POST["harga_pokok"] = $total["harga_pokok"];
      $_POST["tuslag"] = $total["tuslag"];

  //Tuslag
      $sql = "select sum(penjualan_detail_harga_pokok) as harga_pokok from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
      $dataHargaPokok = $dtaccess->Fetch($sql);
  //PPN
      $sql = "select sum(penjualan_detail_ppn) as ppn from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
      $dataHargaPPN = $dtaccess->Fetch($sql);
  //Tuslag
      $sql = "select sum(penjualan_detail_tuslag) as tuslag from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
      $dataHargaTuslag = $dtaccess->Fetch($sql);

      $_POST["penjualan_total_detail"] = $total['penjualan_total_detail'];

      // $grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"])-StripCurrency($_POST["txtDiskon"]); 
      $grandTotals =  StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"])-StripCurrency($_POST["txtDiskon"]); 
     //   $newGrand = $_POST["txtBalik"] ;
   //  echo "grandtotal".$grandTotals."<br> newgrand".$newGrand;  
      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "apotik.apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_create";
      $dbField[2]  = "penjualan_nomor";
      $dbField[3]  = "penjualan_total";     
      $dbField[4]  = "penjualan_terbayar";
      $dbField[5]  = "who_update";
      $dbField[6]  = "id_gudang";
      $dbField[7]  = "penjualan_flag";
      $dbField[8]  = "penjualan_catatan";
      $dbField[9]  = "penjualan_pajak";
      $dbField[10]  = "penjualan_diskon";
      $dbField[11]  = "penjualan_diskon_persen";
      $dbField[12]  = "penjualan_biaya_resep";
      $dbField[13]  = "penjualan_biaya_racikan";
      $dbField[14]  = "penjualan_biaya_bhps";
      $dbField[15]  = "penjualan_biaya_pembulatan";
      $dbField[16]  = "id_dep";
      $dbField[17]  = "penjualan_grandtotal";
      $dbField[18]  = "penjualan_bayar";
      $dbField[19]  = "penjualan_keterangan";
      $dbField[20]  = "penjualan_tuslag";
      $dbField[21]  = "id_fol";
      $dbField[22]  = "id_dokter";
      $dbField[23]  = "dokter_nama";
      $dbField[24]  = "id_poli";
      $dbField[25]  = "penjualan_biaya_pokok";
      $dbField[26]  = "id_pembayaran";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_DATE, $tanggal['penjualan_create']);
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_detail"]));  
      $dbValue[4] = QuoteValue(DPE_CHAR,'n');
      $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[7] = QuoteValue(DPE_CHAR,'D');
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["penjualan_catatan"]);
      $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($dataHargaPPN['ppn'])); 
      $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
      $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])); 
      $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtResep"]));
      $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaRacikan"]));
      $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaBhps"])); 
      $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"]));
      $dbValue[16] = QuoteValue(DPE_CHAR,$depId); 
      $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
      $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"]));
      $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["fol_keterangan"]); 
      $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($dataHargaTuslag['tuslag']));
      $dbValue[21] = QuoteValue(DPE_CHARKEY,$folId);            
      $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["id_usr"]);
      $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["poli"]);
      $dbValue[25] = QuoteValue(DPE_NUMERIC,$dataHargaPokok["harga_pokok"]);
      $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["id_pembayaran"]);
      
    //  print_r ($dbValue);
   //   die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Update() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
        
          $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
                   where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          		$idPemb = $dtaccess->Fetch($sqlpemb);
//          $sql  ="update klinik.kliniK_registrasi set reg_obat='y' , reg_status = 'E0'
 //                 where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql  ="update klinik.klinik_registrasi set reg_obat='y'
                  where id_pembayaran=".QuoteValue(DPE_CHARKEY,$_POST["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);

          $rs = $dtaccess->Execute($sql); 
//echo $sqlpemb;
//die();   
 
 // Masuk Kassa Rawat Jalan
 
         /*$sql = "select sum(penjualan_detail_total) as penjualan_total_obat from apotik.apotik_penjualan_detail  where penjualan_detail_flag = 'n' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detailflag = $dtaccess->Fetch($rs);
         
         $_POST["penjualan_total_obat"] = $detailflag["penjualan_total_obat"];*/
         
         $sql = "select sum(penjualan_detail_total) as penjualan_total_obat from apotik.apotik_penjualan_detail y
         left join logistik.logistik_item x on x.item_id = y.id_item where y.penjualan_detail_flag = 'n' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detJual = $dtaccess->Fetch($rs);
          //echo $sql;die();
         $sql = "select sum(penjualan_detail_total) as penjualan_total_obat_fornas from apotik.apotik_penjualan_detail y
         left join logistik.logistik_item x on x.item_id = y.id_item where y.penjualan_detail_flag = 'n' and x.item_fornas = 'y' and
         id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
         $rs = $dtaccess->Execute($sql);
         $detJualFornas = $dtaccess->Fetch($rs);
         
         $sql = "select * from logistik.logistik_item x left join apotik.apotik_penjualan_detail y on y.id_item = x.item_id 
         where y.id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ." and y.penjualan_detail_id = ".QuoteValue(DPE_CHAR,$penjualanDetailId);
         $rs = $dtaccess->Execute($sql);
         $detFlag = $dtaccess->Fetch($rs);
         
         //$_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] ;
         $_POST["penjualan_total_obat_fornas"] = $detJualFornas["penjualan_total_obat_fornas"];
         $_POST["penjualan_total_obat_fornas_flag"] = $detFlag["item_fornas"]; 
         
         if ($_POST["penjualan_total_obat_fornas"] <> '0' && ($_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='26')) {
          $_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] -  $detJualFornas["penjualan_total_obat_fornas"];
          //echo  $_POST["penjualan_total_obat"] ; die();
         }
         else
         {
         $_POST["penjualan_total_obat"] = $detJual["penjualan_total_obat"] ;
          //echo  "2".$_POST["penjualan_total_obat"] ; die();
         }    

         //echo  $_POST["penjualan_total_obat"]." dan " .$sql;
         //die();
        // echo "ini ".$_POST["penjualan_detail_flag"];
         $sql = "select count(id_item) as total_item from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $jumlahTotalObat = $dtaccess->Fetch($rs);

         $sql = "select e_medrec from apotik.apotik_penjualan where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $medrec = $dtaccess->Fetch($rs);
         //if ($detailflag["penjualan_detail_flag"] == 'n') {
         
     //    if($medrec["e_medrec"]=='n'){
          
          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_dibayar_when";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";                   
          $dbField[9] = "id_poli";
          $dbField[10] = "fol_jenis_pasien";
          $dbField[11] = "id_dep";
          $dbField[12] = "id_pembayaran";                          
          $dbField[13] = "fol_nominal";
          $dbField[14] = "fol_dibayar";
          $dbField[15] = "fol_total_harga";
          $dbField[16] = "fol_jumlah";
          $dbField[17] = "fol_catatan";
          $dbField[18] = "id_dokter";
          $dbField[19] = "fol_nominal_satuan";
          $dbField[20] = "who_when_update";        
          $dbField[21] = "fol_hrs_bayar";
          $dbField[22] = "tipe_rawat";

	   $sqlJamkesda = "	select a.id_jamkesda_kota, b.jamkesda_kota_nama, b.jamkesda_kota_persentase_kota, b.jamkesda_kota_persentase_prov 
						from klinik.klinik_registrasi a 
						left join global.global_jamkesda_kota b 
						on a.id_jamkesda_kota=b.jamkesda_kota_id 
						where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
					$dataJamkesda = $dtaccess->Fetch($sqlJamkesda);
					$jamkesdaNama=$dataJamkesda["jamkesda_kota_nama"];
					$jamkesdaPesentaseKota=$dataJamkesda["jamkesda_kota_persentase_kota"];
					$jamkesdaPesentaseProv=$dataJamkesda["jamkesda_kota_persentase_prov"];	
          
//         $sqltdk = "select biaya_jenis, biaya_nama, biaya_id from klinik.klinik_biaya where biaya_jenis = 'O' and id_dep =".QuoteValue(DPE_CHAR,$depId);
//         $dataObat = $dtaccess->Fetch($sqltdk);
         $date = $tanggal['penjualan_create'];                

				 if ($_POST["reg_jenis_pasien"]=='18') 
				 {
					$totalTindNom = StripCurrency($grandTotals);
					$jaminDinkesProv=(StripCurrency($totalTindNom)*StripCurrency($jamkesdaPesentaseProv)/100);
					$jaminDinkesKota=(StripCurrency($totalTindNom)*StripCurrency($jamkesdaPesentaseKota)/100);
					$totalJaminan=StripCurrency($jaminDinkesKota)+StripCurrency($jaminDinkesProv);
          $hrsBayar = StripCurrency($totalTindNom)-StripCurrency($totalJaminan);
				  
				 }elseif( $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='26'){
          //$_POST["penjualan_total_obat"]  = 0;
					$totalTindNom = StripCurrency($_POST["penjualan_total_obat"]);
					$dijamin=StripCurrency($totalTindNom);
					$hrsBayar=0;
         } 
				 elseif ($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='7') 
				 {
					$totalTindNom = StripCurrency($grandTotals);
					$dijamin=StripCurrency($totalTindNom);
					$hrsBayar=0;
				 }

         //checking ulang penjulannya
         $sql = "select penjualan_id from apotik.apotik_penjualan where penjualan_nomor =".QuoteValue(DPE_CHAR,$_POST["penjualan_no"])." and penjualan_id <> ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $nomorjual = $dtaccess->Fetch($rs);
         if(!$nomorjual){
          $_POST["penjualan_no"]= $_POST["penjualan_no"];
         }else{
          $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId)." and penjualan_flag = 'D'";
            $lastKode = $dtaccess->Fetch($sql);
            $tgl = explode("-",date('Y-m-d'));
            $_POST["penjualan_no"] = "APRJ".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
            $_POST["hidUrut"] = $lastKode["urut"]+1;
            $sql = "update apotik.apotik_penjualan set penjualan_nomor =".QuoteValue(DPE_CHAR,$_POST["penjualan_no"]).",penjualan_urut = '".$_POST["hidUrut"]."' where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
         }
          //cari folio penjualan tersebut
         $sql = "select * from  klinik.klinik_folio where fol_catatan = ".QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
         $rs = $dtaccess->Execute($sql);
         $foliojualan = $dtaccess->Fetch($rs);
         
               if(!$foliojualan["fol_id"]){  $folId = $dtaccess->GetTransID();
                }else{
                $folId = $foliojualan["fol_id"];
                }
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_DATE,$date);
               $dbValue[4] = QuoteValue(DPE_CHAR,'13');
               $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["cust_usr_id"]);
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHARKEY,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
               $dbValue[9] = QuoteValue(DPE_CHARKEY,$_POST["id_poli"]);
               $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_CHAR,$idPemb["id_pembayaran"]);
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
					     $dbValue[16] = QuoteValue(DPE_NUMERIC,$jumlahTotalObat["total_item"]);
               $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["id_usr"]); //apoteker
               $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               if(!$foliojualan["fol_id"]){
               $dbValue[20] = QuoteValue(DPE_CHAR,$usrId); //apoteker
               }else{
               $dbValue[20] = QuoteValue(DPE_CHAR,$foliojualan["who_when_update"]); //apoteker
               }   
               $dbValue[21] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));   
               $dbValue[22] = QuoteValue(DPE_CHAR,'A');   
                        
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               if(!$foliojualan["fol_id"]){
               $dtmodel->Insert() or die("insert  error");
               }else{
               $dtmodel->Update() or die("update  error");               
               }
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
            
            //masukkan pelaksana
                 //masukkan dokter dahulu
               if(!$foliojualan["fol_id"]){ 
                $dbTable = "klinik.klinik_folio_pelaksana";
    					
    						$dbField[0] = "fol_pelaksana_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "fol_pelaksana_tipe";            
    						
                $folPelId = $dtaccess->GetTransID();
                  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
    						$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
    						$dbValue[3] = QuoteValue(DPE_CHAR,'1');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey); 

                 //masukkan pelaksana dahulu
                $dbTable = "klinik.klinik_folio_pelaksana";
    					
    						$dbField[0] = "fol_pelaksana_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "fol_pelaksana_tipe";
    						  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
    						$dbValue[2] = QuoteValue(DPE_CHAR,$usrId);
    						$dbValue[3] = QuoteValue(DPE_CHAR,'2');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey);                                          
				        
				    $sql = "select * from  klinik.klinik_split where split_flag = ".QuoteValue(DPE_CHAR,SPLIT_OBAT)." order by split_id";
            $rs = $dtaccess->Execute($sql,DB_SCHEMA);
            $dataSplit = $dtaccess->Fetch($rs);
            
						$dbTable = "klinik.klinik_folio_split";
					
						$dbField[0] = "folsplit_id";   // PK
						$dbField[1] = "id_fol";
						$dbField[2] = "id_split";
						$dbField[3] = "folsplit_nominal";
							  
						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
						$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit["split_id"]);
						$dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_obat"]));
						 
						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
						
						$dtmodel->Insert() or die("insert error"); 
						
						unset($dtmodel);
						unset($dbField);
						unset($dbValue);
						unset($dbKey); 
                }
            $sql = "update apotik.apotik_penjualan set id_fol=".QuoteValue(DPE_CHAR,$folId)." 
                    where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
                    
?>