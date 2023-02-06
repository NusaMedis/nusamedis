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
     
     //$depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();

              
              $sql_rawat = "select * from klinik.klinik_perawatan 
                     where id_reg = ".QuoteValue(DPE_CHAR,$regId)." 
                     and id_dep =".QuoteValue(DPE_CHAR,$depId);
            $dataPerawat= $dtaccess->Fetch($sql_rawat);
            
            if(!$dataPerawat){
 
              $dbTable = " klinik.klinik_perawatan";
              $dbField[0] = "rawat_id";   // PK
              $dbField[1] = "id_reg";
              $dbField[2] = "id_cust_usr";
              $dbField[3] = "rawat_waktu_kontrol";
              $dbField[4] = "rawat_tanggal";
              $dbField[5] = "rawat_flag"; 
              $dbField[6] = "rawat_flag_komen"; 
              $dbField[7] = "id_poli"; 
              $dbField[8] = "id_dep";
              $dbField[9] = "rawat_who_update";
              $dbField[10] = "rawat_waktu";         
              
              $_POST["rawat_id"] = $dtaccess->GetTransID();          
              $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
              $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
              $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
              $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
              $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
              $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 
              $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
              $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
              $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
              $dtmodel->Insert() or die("insert  error");	
          
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          
            }

            $skr = date('Y-m-d');
              //insert biaya pemeriksaan 

              $sql = "select a.*,b.*,c.* from global.global_detail_paket a 
                      left join klinik.klinik_biaya_tarif b on a.id_biaya_tarif = b.biaya_tarif_id
                      left join klinik.klinik_biaya c on b.id_biaya = c.biaya_id 
                      where b.biaya_tarif_tgl_awal <= '$skr'
                      and b.biaya_tarif_tgl_akhir >= '$skr'
                      and a.id_paket = ".QuoteValue(DPE_CHAR,$_POST["paket"]);
              $rs = $dtaccess->Execute($sql);
              $dataTarifPaket = $dtaccess->FetchAll($sql);
              die($sql);

              //jika ada biaya pemeriksaan
        for($a=0,$b=count($dataTarifPaket);$a<$b;$a++) {             
                
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
               
			   $folId2 = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId2);
               $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
               $dbValue[2] = QuoteValue(DPE_CHAR,$dataTarifPaket[$a]["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$dataTarifPaket[$a]["biaya_jenis"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,$dataTarifPaket[$a]["biaya_id"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_CHAR,$userId);
               $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
               $dbValue[15] = QuoteValue(DPE_NUMERIC,'1');
               $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
               if($_POST["reg_jenis_pasien"]=="5" || $_POST["reg_jenis_pasien"]=="7" || $_POST["reg_jenis_pasien"]=="18" || $_POST["reg_jenis_pasien"]=='26'){
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency(0));
               } else {
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
               } 
               if($_POST["reg_jenis_pasien"]=="5" || $_POST["reg_jenis_pasien"]=="7" || $_POST["reg_jenis_pasien"]=="18" || $_POST["reg_jenis_pasien"]=='26'){
               $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
               } else {
               $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency(0));
               }
               $dbValue[19] = QuoteValue(DPE_CHAR,$byrId);
                $dbValue[20] = QuoteValue(DPE_DATE,date('Y-m-d'));
               $dbValue[21] = QuoteValue(DPE_DATE,date('H:i:s'));
                            
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               
               $dtmodel->Insert() or die("insert error"); 

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);                      
               unset($dbKey);

               $dbTable = "klinik.klinik_perawatan_tindakan";
             
              $dbField[0] = "rawat_tindakan_id";   // PK
              $dbField[1] = "id_rawat";
              $dbField[2] = "id_tindakan";
              $dbField[3] = "rawat_tindakan_total";
              $dbField[4] = "id_dep";                
              $dbField[5] = "rawat_tindakan_jumlah";
              
              $rawatTindId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHARKEY,$rawatTindId);
                   $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
                   $dbValue[2] = QuoteValue(DPE_CHAR,$dataTarifPaket[$a]["biaya_id"]);
                   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTarifPaket[$a]["biaya_total"]));
                   $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
                   $dbValue[5] = QuoteValue(DPE_NUMERIC,'1');
                  // print_r ($dbValue);
                  // die();
                   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    
                   $dtmodel->Insert() or die("insert  error");
                   
                   unset($dtmodel);
                   unset($dbValue);
                   unset($dbKey);

               //masukkan pelaksana
                 //masukkan dokter dahulu
                $dbTable = "klinik.klinik_folio_pelaksana";
    					
    						$dbField[0] = "fol_pelaksana_id";   // PK
    						$dbField[1] = "id_fol";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "fol_pelaksana_tipe";            
    						  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId2);
    						if($_POST["dokter"]){
			                $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
			                } else {
    						$dbValue[2] = QuoteValue(DPE_CHAR,$userId);
                			}
    						$dbValue[3] = QuoteValue(DPE_CHAR,'1');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey);
                
                			$dbTable = "klinik.klinik_perawatan_tindakan_pelaksana";
    					
    						$dbField[0] = "rawat_tindakan_pelaksana_id";   // PK
    						$dbField[1] = "id_rawat_tindakan";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "rawat_tindakan_pelaksana_tipe";            
    						  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$rawatTindId);
    						if($_POST["dokter"]){
			                $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
			                } else {
    						$dbValue[2] = QuoteValue(DPE_CHAR,$userId);
                			}
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
    						$dbValue[1] = QuoteValue(DPE_CHAR,$folId2);
    						if($_POST["dokter"]){
                			$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
                			} else {
    						$dbValue[2] = QuoteValue(DPE_CHAR,$userId);
                			}
    						$dbValue[3] = QuoteValue(DPE_CHAR,'2');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey);
                
                			$dbTable = "klinik.klinik_perawatan_tindakan_pelaksana";
    					
    						$dbField[0] = "rawat_tindakan_pelaksana_id";   // PK
    						$dbField[1] = "id_rawat_tindakan";
    						$dbField[2] = "id_usr";
    						$dbField[3] = "rawat_tindakan_pelaksana_tipe";            
    						  							  
    						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
    						$dbValue[1] = QuoteValue(DPE_CHAR,$rawatTindId);
    						if($_POST["dokter"]){
                			$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
                			} else {
    						$dbValue[2] = QuoteValue(DPE_CHAR,$userId);
                			}
    						$dbValue[3] = QuoteValue(DPE_CHAR,'2');
    						 
    						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    						
    						$dtmodel->Insert() or die("insert error"); 
    						
    						unset($dtmodel);
    						unset($dbField);
    						unset($dbValue);
    						unset($dbKey);

          			$sql = "select * from  klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataTarifPaket[$a]["biaya_id"])." 
                        and bea_split_nominal > 0";
                $dataSplitKarcis2 = $dtaccess->FetchAll($sql);
          			
          			for($i=0,$n=count($dataSplitKarcis2);$i<$n;$i++) {
          				$dbTable = "klinik.klinik_folio_split";
          			
          				$dbField[0] = "folsplit_id";   // PK
          				$dbField[1] = "id_fol";
          				$dbField[2] = "id_split";
          				// JIKA pasien gratis dan SKTM //
		                  if($_POST["reg_jenis_pasien"]=='6') {
		                  $dbField[3] = "folsplit_nominal";
		          				} else {
		                  $dbField[3] = "folsplit_nominal";
		                  }
                  	  
          				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
          				$dbValue[1] = QuoteValue(DPE_CHAR,$folId2);
          				$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplitKarcis2[$i]["id_split"]);
          				// JIKA pasien gratis dan SKTM //
		                  if($_POST["reg_jenis_pasien"]=='6') {
		                  $dbValue[3] = QuoteValue(DPE_NUMERIC,'0.00');
		          				} else {
		                  $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplitKarcis2[$i]["bea_split_nominal"]);
		                  }
          				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          				
          				$dtmodel->Insert() or die("insert error"); 
          				unset($dtmodel);
          				unset($dbField);
          				unset($dbValue);
          				unset($dbKey);
          			}
          		}
          	 
          	//cari data folio lab
          	$sql = "select * from klinik.klinik_folio a left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
          			where biaya_jenis_sem ='LA'";
          	$rs = $dtaccess->Execute($sql);
          	$dataFolLab = $dtaccess->Fetch($rs);

			//bikin registrasi lab
          	if($dataFolLab["fol_id"]){

          	}                                                      
?>