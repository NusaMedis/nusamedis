<?php    

     if ($_POST["obat_id"]) {
     
     $dateSekarang = date('Y-m-d H:i:s');
     $date = $_POST["reg_tanggal"]." ".date("H:i:s");
      //  echo $date;
        // die();
          
          $dbTable = "apotik.apotik_penjualan_detail";
          $dbField[0]  = "penjualan_detail_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "penjualan_detail_harga_jual";
          $dbField[4]  = "penjualan_detail_jumlah";
          $dbField[5]  = "penjualan_detail_total";
          $dbField[6]  = "penjualan_detail_flag";
          $dbField[7]  = "penjualan_detail_create";
          $dbField[8]  = "id_petunjuk";
          $dbField[9]  = "id_dep";
          $dbField[10]  = "penjualan_detail_sisa";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "penjualan_detail_tuslag";
          $dbField[13]  = "penjualan_detail_dosis_obat";
          $dbField[14]  = "id_aturan_minum";
          $dbField[15]  = "id_aturan_pakai";
          $dbField[16]  = "item_nama";

          if (!$_POST["btn_edit"]) //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["penjualan_detail_dosis_obat"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["obat_nama"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          unset($_POST["btnSave"]);
          unset($_POST["obat_id"]);
          unset($_POST["obat_kode"]);
          unset($_POST["obat_nama"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["txtTuslag"]);   
          unset($_POST["penjualan_detail_dosis_obat"]);                 
     }
      $isprint = "n";  

// $_POST["penjualan_total_obat"]=$_POST["txtBalik"];
      $sql = "select sum(penjualan_detail_total) as penjualan_total_detail from apotik.apotik_penjualan_detail  where 
      id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
      $rs = $dtaccess->Execute($sql);
      $total = $dtaccess->Fetch($rs);
         
      $_POST["penjualan_total_detail"] = $total["penjualan_total_detail"];

      $grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"])-StripCurrency($_POST["txtDiskon"]); 
     //   $newGrand = $_POST["txtBalik"] ;
   //  echo "grandtotal".$grandTotals."<br> newgrand".$newGrand;  
      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "apotik.apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      // $dbField[1]  = "penjualan_create";
      $dbField[1]  = "penjualan_nomor";
      $dbField[2]  = "penjualan_total";     
      $dbField[3]  = "penjualan_terbayar";
      $dbField[4]  = "who_update";
      $dbField[5]  = "id_gudang";
      $dbField[6]  = "penjualan_flag";
      $dbField[7]  = "penjualan_catatan";
      $dbField[8]  = "penjualan_pajak";
      $dbField[9]  = "penjualan_diskon";
      $dbField[10]  = "penjualan_diskon_persen";
      $dbField[11]  = "penjualan_biaya_resep";
      $dbField[12]  = "penjualan_biaya_racikan";
      $dbField[13]  = "penjualan_biaya_bhps";
      $dbField[14]  = "penjualan_biaya_pembulatan";
      $dbField[15]  = "id_dep";
      $dbField[16]  = "penjualan_grandtotal";
      $dbField[17]  = "penjualan_bayar";
      $dbField[18]  = "penjualan_keterangan";
      $dbField[19]  = "penjualan_tuslag";
      $dbField[20]  = "id_fol";
      $dbField[21]  = "id_dokter";
      $dbField[22]  = "dokter_nama";
      $dbField[23]  = "id_poli";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      // $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_detail"]));  
      $dbValue[3] = QuoteValue(DPE_CHAR,'n');
      $dbValue[4] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[5] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[6] = QuoteValue(DPE_CHAR,'D');
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["penjualan_catatan"]);
      $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtPPN"])); 
      $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
      $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])); 
      $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtResep"]));
      $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaRacikan"]));
      $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaBhps"])); 
      $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"]));
      $dbValue[15] = QuoteValue(DPE_CHAR,$depId); 
      $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
      $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"]));
      $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["fol_keterangan"]); 
      $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
      $dbValue[20] = QuoteValue(DPE_CHARKEY,$folId);            
      $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["id_usr"]);
      $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["poli"]);
      
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
                                                            
       if($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26')
              { 
          $dbField[21] = "fol_hrs_bayar";
          $dbField[22] = "fol_dijamin";
          $dbField[23] = "fol_subsidi";
          $dbField[24] = "fol_iur_biaya";
         } elseif ($_POST["reg_jenis_pasien"]=='18') {
          $dbField[21] = "fol_hrs_bayar";
          $dbField[22] = "fol_dijamin";
          $dbField[23] = "fol_dijamin1";
          $dbField[24] = "fol_dijamin2";
         }
      else{           
          $dbField[21] = "fol_hrs_bayar";
        }

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
         $date = date('Y-m-d H:i:s');                

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
               $dbValue[4] = QuoteValue(DPE_CHAR,'OA');
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
					if ($_POST["reg_jenis_pasien"]=='1' || $_POST["reg_jenis_pasien"]=='5' || $_POST["reg_jenis_pasien"]=='7' || $_POST["reg_jenis_pasien"]=='26') {
               $dbValue[21] = QuoteValue(DPE_NUMERIC,0);
      				 $dbValue[22] = QuoteValue(DPE_NUMERIC,$dijamin);	
					     $dbValue[23] = QuoteValue(DPE_NUMERIC,0);
					     $dbValue[24] = QuoteValue(DPE_NUMERIC,0);
				        }
				   elseif($_POST["reg_jenis_pasien"]=='18') {
               $dbValue[21] = QuoteValue(DPE_NUMERIC,0);
    					 $dbValue[22] = QuoteValue(DPE_NUMERIC,StripCurrency($totalJaminan));	
		    			 $dbValue[23] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesProv));
				    	 $dbValue[24] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminDinkesKota));											
				      }
					else{
               $dbValue[21] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));   
              }
                        
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
         //      }
               
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
      $rs = $dtaccess->Execute($sql);
      $dataJual = $dtaccess->FetchAll($rs); 
      // echo $sql; die();
      for($i=0,$n=count($dataJual);$i<$n;$i++){
        //hapus penjualan yang sebelumnya
          $sql = "delete from logistik.logistik_stok_item where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);

          $sql = "delete from logistik.logistik_stok_item_batch where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);
     /*     
           $sql = "select a.*, c.gudang_nama as nama_asal, d.gudang_nama as nama_tujuan
                         from logistik.logistik_stok_item a
                         left join logistik.logistik_gudang b on a.id_gudang = b.gudang_id
                         left join logistik.logistik_gudang c on a.id_dep_asal = c.gudang_id
                         left join logistik.logistik_gudang d on a.id_dep_tujuan = d.gudang_id";
                 $sql .= " where a.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
                 $sql .= " a.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
                 $sql .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
                 $sql .= " order by a.id_gudang asc, a.stok_item_create asc";
                 $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            	   $dataTable1 = $dtaccess->FetchAll($rs);
      // echo $sql; 
                 for($ia=0,$na=count($dataTable1);$ia<$na;$ia++)
                 {
                   if ($dataTable1[$ia]["stok_item_flag"]=='A') //Saldo Awal
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='PP') //Pemakaian
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='B') //Pembelian
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='P') //Penjualan
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='O') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='M') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
            
                      //update saldo stok
//                      if ($saldo>0)
//                      {
                       $sql  ="update logistik.logistik_stok_item 
                               set stok_item_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                              where stok_item_id =".QuoteValue(DPE_CHAR,$dataTable1[$ia]["stok_item_id"]);
                        $df = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
                 //       echo $sql;              
//                       }
                } //akhir looping for stok item
//                      if ($saldo>0)
 //                     {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_dep 
                                set stok_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                                where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and 
                               id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                         $fg = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
//                     }               
                   
           //      echo $sql;

               //Adjustment Item Batch
               $sqlBatch = "select a.item_nama,b.batch_id,b.batch_no,c.* from 
                            logistik.logistik_item a left join 
                            logistik.logistik_item_batch b on b.id_item = a.item_id left join
                            logistik.logistik_stok_item_batch c on b.batch_id = c.id_batch";
               $sqlBatch .= " where c.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
               $sqlBatch .= " c.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
               $sqlBatch .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
               $sqlBatch .= " order by a.item_nama,b.batch_id,a.id_gudang asc, c.stok_item_batch_create asc";
            //  echo $sqlBatch;

               $rsBatch = $dtaccess->Execute($sqlBatch,DB_SCHEMA_LOGISTIK);
          	   $dataBatch = $dtaccess->FetchAll($rsBatch);
                 for($k=0,$l=count($dataBatch);$k<$l;$k++)
                 {
                //   echo "ke".$k;
           
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='A') //Saldo Awal
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='PP') //Pemakaian
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='B') //Pembelian
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='P') //Penjualan
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='O') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='M') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
            
                      //update saldo stok
                    //  if ($saldoBatch>0)
                    //  {
                       $sql  ="update logistik.logistik_stok_item_batch 
                               set stok_item_batch_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                              where stok_item_batch_id =".QuoteValue(DPE_CHAR,$dataBatch[$k]["stok_item_batch_id"]);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
                     //  }
            
                   //   if ($saldoBatch>0)
                   //   {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_batch_dep 
                                set stok_batch_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                                where id_batch =".QuoteValue(DPE_CHAR,$dataBatch[$k]["batch_id"])." and 
                                id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
                    // }              
                    //echo "Adjust Batch : ".$dataBatch[$k]["batch_no"]." Berhasil <br>";

                    if($dataBatch[$k]["batch_id"]!=$dataBatch[$k+1]["batch_id"]) unset($saldoBatch);

                   } //end for batch
         
       */   
      // cek apakah ada dua batch atau lebih yg di input //
        if($dataJual[$i]["id_batch"]!=$dataJual[$i-1]["id_batch"]) {        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
 //echo $sql; //die();         
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
//echo $sql; die();           
           //stok batch yg lama - stok baru (dikurangi)
           $stokBatchNow[$i] = $dataDepBatch["total"] - $dataPenjualanStok["total"];
          
          
          // Langsung Update Stok Batch di Gudangnya //
          $sql  ="update logistik.logistik_stok_batch_dep set 
                  stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokBatchNow[$i]);
          $sql .=" , stok_batch_dep_create = current_timestamp";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
          $rs = $dtaccess->Execute($sql);
         
         
         //END UPDATE POSISI STOK BATCH TERAKHIR 
         
         
         //UPDATE POSISI STOK TERAKHIR
         
         //cek di stok_dep untuk melihat stokterakhir
         $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
         $sql .="and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDep = $dtaccess->Fetch($rs);         
         
         //stok lama - stok baru (dikurangi)
          $stokNow[$i] = $dataDep["stok_dep_saldo"] - $dataJual[$i]["penjualan_detail_jumlah"];

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokNow[$i]);
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
          
          //---------------- END UPDATE POSISI STOK TERAKHIR
          //cari harga beli terakhir item
          $sql = " select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $rs = $dtaccess->Execute($sql);
           $dataHargabeli = $dtaccess->Fetch($rs);
          
          //insert kartu stok untuk histry batch untuk penjualan
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_batch_hpp";
          $dbField[11]  = "stok_item_batch_hna";
          $dbField[12]  = "stok_item_batch_hna_ppn_minus_diskon";
          $dbField[13]  = "id_batch";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //insert kartu stok untuk penjualan
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";         
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_hpp";
          $dbField[11]  = "stok_item_hna";
          $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);      
    } 
    
}        
?>