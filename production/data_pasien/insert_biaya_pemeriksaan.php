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

      //checking tipe layanan
      if($_POST["layanan"]=='1') 
          {
           $regTipeAntrian = 'R';
          } 
          else if ($_POST["layanan"]=='2')
          {
           $regTipeAntrian = 'E';
          }
          else     //Rehab Medik
          {
            $regTipeAntrian = 'H';
          }

              //insert biaya pemeriksaan 

              $sql = "select a.*,b.*,c.*  from  klinik.klinik_biaya_pemeriksaan a                       
                      left join klinik.klinik_biaya_tarif c on c.biaya_tarif_id = a.id_biaya_tarif
                      left join klinik.klinik_biaya b on c.id_biaya = b.biaya_id 
                      where 1=1";

              if($konfigurasi["dep_konf_reg_poli"]=='y') $sql .= " and a.id_poli=".QuoteValue(DPE_CHAR,$_POST["klinik"]);
              // if($konfigurasi["dep_konf_header_klinik"]=='n') $sql .= " and a.id_jenis_pasien=".QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
              $periksa = $dtaccess->Fetch($sql);
              //die($sql);

              //jika ada biaya pemeriksaan
              if($periksa) {             
                
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

               $folId2 = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId2);
               $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
               $dbValue[2] = QuoteValue(DPE_CHAR,$periksa["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($periksa["biaya_total"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$periksa["biaya_jenis"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,$periksa["biaya_id"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_CHAR,$userId);
               $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($periksa["biaya_total"]));
               $dbValue[15] = QuoteValue(DPE_NUMERIC,'1');
               $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($periksa["biaya_total"]));
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($periksa["biaya_total"]));
               $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency(0));
               $dbValue[19] = QuoteValue(DPE_CHAR,$byrId);
               $dbValue[20] = QuoteValue(DPE_DATE,date('Y-m-d'));
               $dbValue[21] = QuoteValue(DPE_DATE,date('H:i:s'));
               $dbValue[22] = QuoteValue(DPE_CHAR,$periksa["biaya_tarif_id"]);

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
                   $dbValue[2] = QuoteValue(DPE_CHAR,$periksa["biaya_id"]);
                   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($periksa["biaya_total"]));
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
               // echo "<pre>";
               // print_r ($_POST);
               // echo "</pre>";

                $idSplit = "1"; //DIPATEN 1 untuk JASA MEDIK  HARUSNYA KONFIGURASI
                $sql = "select * from  klinik.klinik_biaya_remunerasi where id_split = ".QuoteValue(DPE_CHAR,$idSplit)." and id_biaya_tarif = ".QuoteValue(DPE_CHAR,$periksa["biaya_tarif_id"])." 
                        and biaya_remunerasi_nominal > 0";
                $dataBiayaSplit = $dtaccess->FetchAll($sql);
                
                $sql = "SELECT usr_id from global.global_auth_user where usr_name = 'perawat'";
                $perawatDef = $dtaccess->Fetch($sql);
                
                
              for ($i = 0; $i < count($dataBiayaSplit); $i++)
              {              
                $sql = "select id_perawat from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST['dokter']);
                $UserPerawat = $dtaccess->Fetch($sql);

                if ($dataBiayaSplit[$i]['id_folio_posisi'] == '10') {
                  $UsrId = $_POST['dokter'];
                }elseif ($dataBiayaSplit[$i]['id_folio_posisi'] == '2') {

                  $UsrId = ($UserPerawat['id_perawat']) ? $UserPerawat['id_perawat'] : $perawatDef['usr_id'];
                }

                $dbTable = "klinik.klinik_folio_pelaksana";
                $dbField[0] = "fol_pelaksana_id";   // PK
                $dbField[1] = "id_fol";
                $dbField[2] = "id_usr";
                $dbField[3] = "id_fol_posisi";
                $dbField[4] = "fol_pelaksana_nominal";
                $dbField[5] = "id_fol_split";
                $dbField[6] = "fol_pelaksana_tipe";

                $folPelId = $dtaccess->GetTransID(); 
                $folSplitId = $dtaccess->GetTransID(); 
                $dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
                $dbValue[1] = QuoteValue(DPE_CHAR,$folId2);
                $dbValue[2] = QuoteValue(DPE_CHAR,$UsrId);
                $dbValue[3] = QuoteValue(DPE_CHAR,$dataBiayaSplit[$i]['id_folio_posisi']);
                $dbValue[4] = QuoteValue(DPE_NUMERIC,$dataBiayaSplit[$i]['biaya_remunerasi_nominal']);
                $dbValue[5] = QuoteValue(DPE_CHAR,$folSplitId);
                $dbValue[6] = QuoteValue(DPE_CHAR,$dataBiayaSplit[$i]['id_folio_posisi']);

                $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
// print_r($dbValue);die();
                if ($UsrId != '') {
                  $dtmodel->Insert() or die("insert  error"); 
                }

                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);


                $dbTable = "klinik.klinik_folio_split";
              
                $dbField[0] = "folsplit_id";   // PK
                $dbField[1] = "id_fol";
                $dbField[2] = "id_split";
                $dbField[3] = "folsplit_nominal";
                $dbField[4] = "id_fol_pelaksana";
                    
                $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
                $dbValue[1] = QuoteValue(DPE_CHAR,$folId2);
                $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiayaSplit[$i]["id_split"]);
                $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiayaSplit[$i]["biaya_remunerasi_nominal"]);
                $dbValue[4] = QuoteValue(DPE_CHAR,$folPelId);
                      
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                
                $dtmodel->Insert() or die("insert error"); 
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);                              
              }               
               


              }   // Akhir Periksa 
?>