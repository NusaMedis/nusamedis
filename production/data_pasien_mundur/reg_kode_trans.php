<?php    
          //kode registrasi
          //ambil kode app
          $sql = "select app_no_reg from global.global_app where app_id='1'";  //kode rawat jalan
          $appNoReg = $dtaccess->Fetch($sql);
    	  $kodeApp =  $appNoReg["app_no_reg"];
          
          //ambil kode poli
          $sql = "select poli_kode, id_instalasi, poli_tipe, id_sub_instalasi from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $poliKodeFetch = $dtaccess->Fetch($sql);
    	  $kodePoli =  $poliKodeFetch["poli_kode"];
          $instalasiId =  $poliKodeFetch["id_instalasi"];
          $tipePoli = $poliKodeFetch["poli_tipe"];
          $subInsId = $poliKodeFetch["id_sub_instalasi"];
          
          $sql = "select * from global.global_auth_instalasi where instalasi_id=".QuoteValue(DPE_CHAR,$instalasiId);
          $rs = $dtaccess->Execute($sql);
          $dataIns = $dtaccess->Fetch($rs);
          $kodeIns = $dataIns["instalasi_kode"];
          
          $sql = "select * from global.global_auth_sub_instalasi where sub_instalasi_id=".QuoteValue(DPE_CHAR,$subInsId);
          $rs = $dtaccess->Execute($sql);
          $dataSubIns = $dtaccess->Fetch($rs);
          $kodeSubIns = $dataSubIns["sub_instalasi_kode"];

          //ambil kode registrasi
          $sql = "select count(reg_id) as nomorurut from klinik.klinik_registrasi";
          $noUrut = $dtaccess->Fetch($sql);
          $kodeUrutReg =  $noUrut["nomorurut"]+1;
          $kodeUrutReg = str_pad($kodeUrutReg,4,"0",STR_PAD_LEFT);
          $kodeUrutReg = getdateTodayReg().$kodeUrutReg;   

          //tipe layanan besok direntet
          //$sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi where 
          //reg_tipe_layanan='".$_POST["reg_tipe_layanan"]."' and reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." 
          //and id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId);   

          $sql = "select max(reg_no_antrian) as nomore from klinik.klinik_registrasi where reg_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"))." 
          and id_poli = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId);   
          $noAntrian = $dtaccess->Fetch($sql);
    	    $noantri =  ($noAntrian["nomore"]+1);

 
          $noantriReg = str_pad($noantri,3,"0",STR_PAD_LEFT);
          
          if($konfigurasi["dep_konf_kode_sub_instalasi"]=="y"){
            if($kodeSubIns){
              if($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeSubIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeSubIns.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeSubIns.".".$kodePoli.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeSubIns.".".$kodePoli.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeSubIns.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeSubIns.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".".$kodeSubIns.".".$kodePoli;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".".$kodeSubIns;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeSubIns.".".$kodePoli;
              }
            } else {
              if($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".01.".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = "01.".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".01.".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".01.".$kodePoli.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".01.".$kodePoli.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = "01.".$kodeUrutReg.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = "01.".$kodePoli.".".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = "01.".$kodePoli.".".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = "01.".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = "01.".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
                $kodeTrans = $kodeIns.".01.".$noantriReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".01.".$kodeUrutReg;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".01.".$kodePoli;
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = $kodeIns.".01";
              } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
                $kodeTrans = "01.".$kodePoli;
              }
            }
          } else {
          if($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodePoli.".".$kodeUrutReg.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodeUrutReg.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$kodePoli.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodePoli.".".$kodeUrutReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeUrutReg.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodePoli.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodePoli.".".$kodeUrutReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeUrutReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="y"){
            $kodeTrans = $kodeIns.".".$noantriReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="y" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodeUrutReg;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns.".".$kodePoli;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="y" && $konfigurasi["dep_konf_kode_poli"]=="n" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodeIns;
          } elseif($konfigurasi["dep_konf_kode_instalasi"]=="n" && $konfigurasi["dep_konf_kode_poli"]=="y" && $konfigurasi["dep_konf_urut_registrasi"]=="n" && $konfigurasi["dep_konf_urut_pasien"]=="n"){
            $kodeTrans = $kodePoli;
          }
          } 
         
         // echo $kodeTrans;die();         
?>