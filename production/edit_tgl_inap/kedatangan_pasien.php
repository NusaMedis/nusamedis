<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();
     
   /* if(!$auth->IsAllowed("sirs_rawat_inap_edit_tanggal_inap",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("sirs_rawat_inap_edit_tanggal_inap",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."auth.cls.php?msg=Login First'</script>";
          exit(1);
     }*/

    $_x_mode = "New";
     $thisPage = "perawatan_view_simpel.php";
     $editPage = "perawatan_edit_simpel.php?";
     $findPage = "pasien_find.php?";
     
  if($_GET["id_cust_usr"]) $_POST["cust_usr_id"] = $enc->Decode($_GET["id_cust_usr"]);
  
     	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama from global.global_customer_user a where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
    $_POST["cust_usr_id"] = $dataPasien["cust_usr_id"];
    
    }

	if($_POST["cust_usr_id"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
    $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
    
    
     
    }

	
     if($dataPasien) {
          $table = new InoTable("table","100%","left");

          $sql = "select * from global.global_customer_user a 
                  left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id
                  left join klinik.klinik_perawatan c on c.id_reg = b.reg_id
                  left join klinik.klinik_jadwal d on d.id_reg = b.reg_id
				          left join klinik.klinik_rawatinap g on b.reg_id = g.id_reg
                  left join global.global_auth_poli e on b.id_poli = e.poli_id
                  left join global.global_departemen f on b.id_dep = f.dep_id
                  left join klinik.klinik_kamar h on h.kamar_id = g.id_kamar
				          left join klinik.klinik_kamar_bed i on i.bed_id = g.id_bed
				          left join klinik.klinik_kelas j on b.reg_kelas = j.kelas_id
                  left join global.global_shift k on b.reg_shift = k.shift_id
					where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])." 
                  order by b.reg_tanggal desc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataTable = $dtaccess->FetchAll($rs);
          
          $addPage = "perawatan_tambah.php?tambah=1&id=".$enc->Encode($dataTable[0]["cust_usr_id"]);
          
          //*-- config table ---*//
          $tableHeader = "&nbsp;Nama : ".$dataPasien["cust_usr_nama"];
          
         // $isAllowedUpdate = $auth->IsAllowed("dok_edit_pemeriksaan",PRIV_UPDATE);
          
          // --- construct new table ---- //
         // $colspan = ($isAllowedUpdate) ? 2:1;
          $colspan =2;
          $tbHeader[0][0][TABLE_ISI] = $tableHeader;
          $tbHeader[0][0][TABLE_WIDTH] = "80%";
          $tbHeader[0][0][TABLE_COLSPAN] = "12";
          
          //$tbHeader[0][1][TABLE_ISI] = '<a href="'.$addPage.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
          //$tbHeader[0][1][TABLE_WIDTH] = "30%";
          //$tbHeader[0][1][TABLE_CLASS] = "tablecontent-odd";
          //$tbHeader[0][1][TABLE_COLSPAN] = "2";


          $counterHeader = 0;
         
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;    

     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Bayar";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;    
*/
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;    
		  
     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Anamnesa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Diagnosa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "16%"; 
         $counterHeader++;

         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Pemeriksaan Fisik";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Penunjang";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
        
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Tindakan";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;   
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Terapi(Resep)";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++; */
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Poli";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;

		$tbHeader[1][$counterHeader][TABLE_ISI] = "Shift";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;
         
    /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Klinik";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "12%"; 
         $counterHeader++;
         */
		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Kelas";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "4%"; 
         $counterHeader++;
          
		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Bangsal";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;

		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Bed";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%"; 
         $counterHeader++;		 
		 // if($isAllowedUpdate){
               $tbHeader[1][$counterHeader][TABLE_ISI] = "Edit";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++;
              //}
              
           /*    $tbHeader[1][$counterHeader][TABLE_ISI] = "Hapus";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++; */
              
              
          for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
               
              
               if($dataTable[$i]["rawat_tanggal"]) {
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["rawat_tanggal"]);
               } else {
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["reg_tanggal"])." (Belum Ada Data)";
               }$tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
                
      /*         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_bayar"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
			*/   
			    $tbContent[$i][$counter][TABLE_ISI] = $regPasienStatus[$dataTable[$i]["reg_status"]];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
                 
               // nyari data anamnesaa 
    /*      $DataAnamnesa = explode("\n", $dataTable[$i]["rawat_anamnesa"]);
          $Anamnesa1 = $DataAnamnesa[0];
          $Anamnesa2 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1];
          $Anamnesa3 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2];
          $Anamnesa4 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3];
          $Anamnesa5 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4];
          $Anamnesa6 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4]."<br />&nbsp;".$DataAnamnesa[5];
          $Anamnesa7 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4]."<br />&nbsp;".$DataAnamnesa[5]."<br />&nbsp;".$DataAnamnesa[6];
          $Anamnesa8 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4]."<br />&nbsp;".$DataAnamnesa[5]."<br />&nbsp;".$DataAnamnesa[6]."<br />&nbsp;".$DataAnamnesa[7];
          $Anamnesa9 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4]."<br />&nbsp;".$DataAnamnesa[5]."<br />&nbsp;".$DataAnamnesa[6]."<br />&nbsp;".$DataAnamnesa[7]."<br />&nbsp;".$DataAnamnesa[8];
          $Anamnesa10 = $DataAnamnesa[0]."<br />&nbsp;".$DataAnamnesa[1]."<br />&nbsp;".$DataAnamnesa[2]."<br />&nbsp;".$DataAnamnesa[3]."<br />&nbsp;".$DataAnamnesa[4]."<br />&nbsp;".$DataAnamnesa[5]."<br />&nbsp;".$DataAnamnesa[6]."<br />&nbsp;".$DataAnamnesa[7]."<br />&nbsp;".$DataAnamnesa[8]."<br />&nbsp;".$DataAnamnesa[9];
          
          // nyari data keluhan 
          $DataKeluhan = explode("\n", $dataTable[$i]["rawat_diagnosa_utama"]);
          $Keluhan1 = $DataKeluhan[0];
          $Keluhan2 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1];
          $Keluhan3 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2];
          $Keluhan4 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3];
          $Keluhan5 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4];
          $Keluhan6 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4]."<br />&nbsp;".$DataKeluhan[5];
          $Keluhan7 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4]."<br />&nbsp;".$DataKeluhan[5]."<br />&nbsp;".$DataKeluhan[6];
          $Keluhan8 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4]."<br />&nbsp;".$DataKeluhan[5]."<br />&nbsp;".$DataKeluhan[6]."<br />&nbsp;".$DataKeluhan[7];
          $Keluhan9 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4]."<br />&nbsp;".$DataKeluhan[5]."<br />&nbsp;".$DataKeluhan[6]."<br />&nbsp;".$DataKeluhan[7]."<br />&nbsp;".$DataKeluhan[8];
          $Keluhan10 = $DataKeluhan[0]."<br />&nbsp;".$DataKeluhan[1]."<br />&nbsp;".$DataKeluhan[2]."<br />&nbsp;".$DataKeluhan[3]."<br />&nbsp;".$DataKeluhan[4]."<br />&nbsp;".$DataKeluhan[5]."<br />&nbsp;".$DataKeluhan[6]."<br />&nbsp;".$DataKeluhan[7]."<br />&nbsp;".$DataKeluhan[8]."<br />&nbsp;".$DataKeluhan[9];
          
          // nyari data terapi 
          $DataTerapi = explode("\n", $dataTable[$i]["rawat_pemeriksaan_fisik"]);
          $Terapi1 = $DataTerapi[0];
          $Terapi2 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1];
          $Terapi3 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2];
          $Terapi4 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3];
          $Terapi5 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4];
          $Terapi6 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4]."<br />&nbsp;".$DataTerapi[5];
          $Terapi7 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4]."<br />&nbsp;".$DataTerapi[5]."<br />&nbsp;".$DataTerapi[6];
          $Terapi8 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4]."<br />&nbsp;".$DataTerapi[5]."<br />&nbsp;".$DataTerapi[6]."<br />&nbsp;".$DataTerapi[7];
          $Terapi9 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4]."<br />&nbsp;".$DataTerapi[5]."<br />&nbsp;".$DataTerapi[6]."<br />&nbsp;".$DataTerapi[7]."<br />&nbsp;".$DataTerapi[8];
          $Terapi10 = $DataTerapi[0]."<br />&nbsp;".$DataTerapi[1]."<br />&nbsp;".$DataTerapi[2]."<br />&nbsp;".$DataTerapi[3]."<br />&nbsp;".$DataTerapi[4]."<br />&nbsp;".$DataTerapi[5]."<br />&nbsp;".$DataTerapi[6]."<br />&nbsp;".$DataTerapi[7]."<br />&nbsp;".$DataTerapi[8]."<br />&nbsp;".$DataTerapi[9];
          
          // nyari data catatan 
          $DataCatatan = explode("\n", $dataTable[$i]["rawat_penunjang"]);
          $Catatan1 = $DataCatatan[0];
          $Catatan2 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1];
          $Catatan3 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2];
          $Catatan4 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3];
          $Catatan5 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4];
          $Catatan6 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4]."<br />&nbsp;".$DataCatatan[5];
          $Catatan7 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4]."<br />&nbsp;".$DataCatatan[5]."<br />&nbsp;".$DataCatatan[6];
          $Catatan8 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4]."<br />&nbsp;".$DataCatatan[5]."<br />&nbsp;".$DataCatatan[6]."<br />&nbsp;".$DataCatatan[7];
          $Catatan9 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4]."<br />&nbsp;".$DataCatatan[5]."<br />&nbsp;".$DataCatatan[6]."<br />&nbsp;".$DataCatatan[7]."<br />&nbsp;".$DataCatatan[8];
          $Catatan10 = $DataCatatan[0]."<br />&nbsp;".$DataCatatan[1]."<br />&nbsp;".$DataCatatan[2]."<br />&nbsp;".$DataCatatan[3]."<br />&nbsp;".$DataCatatan[4]."<br />&nbsp;".$DataCatatan[5]."<br />&nbsp;".$DataCatatan[6]."<br />&nbsp;".$DataCatatan[7]."<br />&nbsp;".$DataCatatan[8]."<br />&nbsp;".$DataCatatan[9];
              

          if($rawatPenting[0]=='1'){
         
          if($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7] && $DataAnamnesa[8] && $DataAnamnesa[9]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa10.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7] && $DataAnamnesa[8]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa9.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa8.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa7.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa6.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa5.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa4.'</font>';
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa3.'</font>'; 
          elseif($DataAnamnesa[0] && $DataAnamnesa[1]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa2.'</font>';
          elseif($DataAnamnesa[0]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Anamnesa1.'</font>';
       
          }else{  
          
          if($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7] && $DataAnamnesa[8] && $DataAnamnesa[9]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa10;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7] && $DataAnamnesa[8]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa9;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6] && $DataAnamnesa[7]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa8;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5] && $DataAnamnesa[6]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa7;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4] && $DataAnamnesa[5]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa6;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3] && $DataAnamnesa[4]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa5;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2] && $DataAnamnesa[3]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa4;
          elseif($DataAnamnesa[0] && $DataAnamnesa[1] && $DataAnamnesa[2]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa3; 
          elseif($DataAnamnesa[0] && $DataAnamnesa[1]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa2;
          elseif($DataAnamnesa[0]) $tbContent[$i][$counter][TABLE_ISI] = $Anamnesa1;                      
         
          } 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
          
          /*if($rawatPenting[1]=='1'){
          
          if($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7] && $DataKeluhan[8] && $DataKeluhan[9]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan10.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7] && $DataKeluhan[8]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan9.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan8.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan7.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan6.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan5.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan4.'</font>';
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan3.'</font>'; 
          elseif($DataKeluhan[0] && $DataKeluhan[1]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan2.'</font>';
          elseif($DataKeluhan[0]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Keluhan1.'</font>';  
  
          }else{
         
          if($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7] && $DataKeluhan[8] && $DataKeluhan[9]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan10;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7] && $DataKeluhan[8]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan9;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6] && $DataKeluhan[7]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan8;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5] && $DataKeluhan[6]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan7;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4] && $DataKeluhan[5]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan6;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3] && $DataKeluhan[4]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan5;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2] && $DataKeluhan[3]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan4;
          elseif($DataKeluhan[0] && $DataKeluhan[1] && $DataKeluhan[2]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan3; 
          elseif($DataKeluhan[0] && $DataKeluhan[1]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan2;
          elseif($DataKeluhan[0]) $tbContent[$i][$counter][TABLE_ISI] = $Keluhan1;  
   
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";  
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";       
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          
                    $sqlIcd = "select icd_nama,icd_nomor from klinik.klinik_perawatan_icd a
                  left join klinik.klinik_icd b on b.icd_id = a.id_icd 
                   where a.id_rawat=".QuoteValue(DPE_CHAR,$dataTable[$i]["rawat_id"])."
                   order by rawat_icd_urut asc ";                             
          $dataIcd = $dtaccess->FetchAll($sqlIcd);
           //echo $sqlIcd;
          for($id=0,$nd=count($dataIcd);$id<=$nd;$id++){
           $dataIcdNya[$i][] = "<b>".$dataIcd[$id]["icd_nomor"]."</b>&nbsp;".$dataIcd[$id]["icd_nama"];
          }
          
          $terapiIcd = implode("&nbsp;,&nbsp;&nbsp;<br />", $dataIcdNya[$i]);
  
          if($terapiIcd){
          $tbContent[$i][$counter][TABLE_ISI] = $terapiIcd; 
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "-";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++; 
          
          
          if($rawatPenting[2]=='1'){
          
          if($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7] && $DataTerapi[8] && $DataTerapi[9]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi10.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7] && $DataTerapi[8]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi9.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi8.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi7.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi6.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi5.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi4.'</font>';
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi3.'</font>'; 
          elseif($DataTerapi[0] && $DataTerapi[1]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi2.'</font>';
          elseif($DataTerapi[0]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Terapi1.'</font>';  

          }else{
          
          if($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7] && $DataTerapi[8] && $DataTerapi[9]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi10;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7] && $DataTerapi[8]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi9;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6] && $DataTerapi[7]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi8;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5] && $DataTerapi[6]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi7;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4] && $DataTerapi[5]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi6;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3] && $DataTerapi[4]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi5;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2] && $DataTerapi[3]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi4;
          elseif($DataTerapi[0] && $DataTerapi[1] && $DataTerapi[2]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi3; 
          elseif($DataTerapi[0] && $DataTerapi[1]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi2;
          elseif($DataTerapi[0]) $tbContent[$i][$counter][TABLE_ISI] = $Terapi1;  
          
          }
           
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $tbContent[$i][$counter][TABLE_VALIGN] = "top"; 
          $counter++;
          
          if($rawatPenting[3]=='1'){
          
          if($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7] && $DataCatatan[8] && $DataCatatan[9]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan10.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7] && $DataCatatan[8]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan9.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan8.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan7.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan6.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan5.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan4.'</font>';
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan3.'</font>'; 
          elseif($DataCatatan[0] && $DataCatatan[1]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan2.'</font>';
          elseif($DataCatatan[0]) $tbContent[$i][$counter][TABLE_ISI] = '<font color="red">'.$Catatan1.'</font>';  
        
          }else{
          
          if($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7] && $DataCatatan[8] && $DataCatatan[9]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan10;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7] && $DataCatatan[8]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan9;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6] && $DataCatatan[7]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan8;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5] && $DataCatatan[6]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan7;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4] && $DataCatatan[5]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan6;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3] && $DataCatatan[4]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan5;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2] && $DataCatatan[3]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan4;
          elseif($DataCatatan[0] && $DataCatatan[1] && $DataCatatan[2]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan3; 
          elseif($DataCatatan[0] && $DataCatatan[1]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan2;
          elseif($DataCatatan[0]) $tbContent[$i][$counter][TABLE_ISI] = $Catatan1;  

          } 
          
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
          
          $span = $dataSpan["jml_span"]; 
         
     
          if($asistenName){
          $tbContent[$i][$counter][TABLE_ISI] = $asistenName; 
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "-";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];        
          $counter++;   
          
          if($terapiResep){
          $tbContent[$i][$counter][TABLE_ISI] = $terapiResep; 
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "-";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent-odd";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++; */
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
		  
          //if ($dataTable[$i]["reg_shift"]==1) {}
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["shift_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
     /*     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
               */
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kelas_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          
		  //if($isAllowedUpdate) {
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["rawat_id"]).'&id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               //}
                
           /*         $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'deleted=1&id_reg='.$dataTable[$i]["reg_id"].'&id_rawat='.$dataTable[$i]["rawat_id"].'&id_jadwal='.$dataTable[$i]["jadwal_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"  onclick="javascript: return hapus();"></a>';               
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++; */
          }
          
          $colspan = $colspan;
          
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = "12";
     }
     
       //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
	     

?>

<?php //echo $view->RenderBody("module.css",true,false,"EDIT TGL INAP"); ?>

<?php //echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
    'width' : '50%',
    'height' : '100%',
    'autoScale' : false,
    'transitionIn' : 'none',
    'transitionOut' : 'none',
    'type' : 'iframe'      
    });
}); 

function Kembali() {

    document.location.href='pasien_view.php';
}

</script>


<?php //echo $view->InitThickBox(); ?>
<script language="JavaScript">

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan menghapus data ini???'));
  else return false;
}

</script>

<body>
<div id="body">
<br />	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>No. RM Tidak Ditemukan</strong></font>
<?php } ?>

<script>document.frmFind.cust_usr_kode.focus();</script>

</form>

<?php if($dataPasien["cust_usr_id"] || $_POST["btnAdd"]) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)">
<table width="100%" align="center">
<!--<tr><td align="right">
     <a href="<?php echo $addPage; ?>" style="text-decoration:none"><input type="button" value="Tambah" class="submit" alt="Tambah" title="Tambah" border="0"></a>
</td></tr>-->
<tr><td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td></tr>
<tr>
    <td>
    <input type="button" name="btnBack" id="btnBack" value="Kembali" class="submit" onClick="javascript: Kembali();" />
    </td>
</tr>
</table>
</form>
<?php } ?>

</div>

<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/edit_pemeriksaan.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>

