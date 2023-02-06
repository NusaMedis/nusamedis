<?php
		 SESSION_START();
      require_once("penghubung.inc.php");
      require_once($ROOT."lib/login.php");
      require_once($ROOT."lib/encrypt.php");
  
    	$enc = new textEncrypt();
    	$auth = new CAuth();     
    	$dtaccess = new DataAccess();
    
      $login = $auth->IsLoginOk($_POST["txtUser"],$_POST["txtPass"]);
      
      $sql = "select b.id_app from global.global_auth_user_app b
             where id_usr=".QuoteValue(DPE_CHAR,$login["id"])."
             and id_app=".QuoteValue(DPE_NUMERIC,$_POST["cmbSystem"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      //echo $login;
      //die();
      $dataTable = $dtaccess->FetchAll($rs); 
      
      $sql = "select * from global.global_auth_poli";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs); 
     
     $sql = "select * from klinik.klinik_kamar order by kamar_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataInap = $dtaccess->FetchAll($rs); 
	 
	 
	 
    if($login && !$dataTable)
     {
      //echo header("Location:../index.php?msg=kode_eror02&user=".$_POST["txtUser"]);
      //echo 'Login True';
     
	//	echo header("Location:../poli_orto_reg/menu_orto_reg_coba.php");
     //  exit();
     }
     
     if($login && $dataTable) {
     
     // for($i=0,$n=count($dataPoli);$i<$n;$i++){
   		  //      $_POST["txtPoli"][$dataPoli[$i]["id_poli"]] = $dataPaket[$i]["bea_split_nominal"];
	    //} 
     
      if($_POST["txtPoli"]=="Ortopedi"){
        echo "ortopedi";  
      } 
      elseif ($_POST["txtPoli"]=="Bedah Umum"){
        echo "bedah umum";  
      }

        if($_POST["cmbSystem"]==34){
         //echo 'login true';//"<script>top.location.href='menu_orto_reg.php'</script>"; 
            
       // echo header("Location:../poli_orto_reg/menu_orto_reg_coba.php");   
		//	exit();         
         //header("location:./logistik/index.php");
        } 
		
     } 
     elseif(!$login)
       {
         header("Location:../rawat_jalan.php?ref=100&msg=kode_eror01&user=".$_POST["txtUser"]);
       } 
     elseif(!$dataTable)
       {
        // header("Location:login.php?msg=kode_eror02&user=".$_POST["txtUser"]);
         //echo "<script>top.location.href='../index.php?msg=kode_eror02&user='".$_POST["txtUser"]."</script>";
         //echo 'Login True';
	$_SESSION['txtUser']=$_POST['txtUser'];
		$poli=$_POST["txtPoli"];
		
		//echo "---$poli---<br>";
		
	
		if($poli=='Ortopedi'){
        echo header("Location:../poli_orto_reg/menu_orto_reg.php");   
		    exit();   
      } 
      elseif ($poli=='Bedah Umum'){
        echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			  exit(); 
      } elseif ($poli=='Poli Umum'){
        echo header("Location:../poli_umum/menu_umum.php");   
			  exit(); 
	    }elseif ($poli=='Poli Fisioterapi'){
        echo header("Location:../poli_fisioterapi/menu_fisio_ekse.php");   
			  exit();
      }elseif ($poli=='Poli THT'){
        echo header("Location:../poli_tht/menu_tht.php");   
			  exit();
      }elseif ($poli=='Poli Mata'){
        echo header("Location:../poli_mata/menu_poli_mata.php");   
			  exit();
      }elseif ($poli=='Poli Jiwa'){
        echo header("Location:../poli_jiwa/menu_poli_jiwa.php");   
			  exit();
      }elseif ($poli=='Poli Obsgyn'){
        echo header("Location:../poli_obsgyn/menu_obsgyn.php");   
			  exit();
      }elseif ($poli=='Gigi dan Mulut'){
        echo header("Location:../poli_gigi_reg/menu_gigi_reg.php");   
			  exit();
      }elseif ($poli=='Poli Kulit dan Kelamin'){
        echo header("Location:../poli_kulit_kelamin/menu_kulit_kelamin.php");   
			  exit();
      }elseif ($poli=='Poli Penyakit Dalam'){
        echo header("Location:../poli_dalam/menu_poli_dalam.php");   
			  exit();
      }elseif ($poli=='Poli Anak'){
        echo header("Location:../poli_anak/menu_poli_anak.php");   
			  exit();
      }elseif ($poli=='Poli Bedah Anak'){
        echo header("Location:../poli_bedah_anak/menu_bedah_anak.php");   
			  exit();
      }elseif ($poli=='Poli Saraf dan Bedah Saraf'){
        echo header("Location:../poli_syaraf/menu_poli_syaraf.php");   
			  exit();
      }elseif ($poli=='Poli Jantung'){
        echo header("Location:../poli_jantung/menu_poli_jantung.php");   
			  exit();
      }elseif ($poli=='Bedah Urologi'){
		
        echo header("Location:../poli_bedah_urologi/menu_bedah_urologi.php");   
			  exit();
      }elseif ($poli=='Disgesif'){
	 
         echo header("Location:../poli_disgesif/menu_disgesif.php");   
		 	  exit();
      }elseif ($poli=='Bedah Tumor'){
        echo header("Location:../poli_bedah_tumor/menu_bedah_tumor.php");   
			  exit();
      }elseif ($poli=='TB'){
        echo header("Location:../poli_tb/menu_tb.php");   
			  exit();
      }
      
    /*   $inap=$_POST["txtInap"];
       if($poli=='Anggrek'){
        echo header("Location:../poli_orto_reg/menu_orto_reg.php");   
		    exit();   
       } elseif ($poli=='Mawar Pi'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Mawar Pa'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='ICU'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Jasmine'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Lotus'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Lily'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Alamanda'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Aster'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Chrysan'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit(); 
       } elseif ($poli=='Tulip'){
          echo header("Location:../poli_bedah_reg/menu_bedah_reg.php");   
			    exit();
       }    */
       
        // echo header("Location:../poli_orto_reg/menu_orto_reg_coba.php");
      //  exit();
	  
	 
       }
	   
     
     unset($_POST["txtUser"]);
     unset($_POST["txtPass"]);
     unset($login);
     /*
     if($login==1){
         header("Location:login.php?msg=User Online");         
     } elseif($login==2) {
         header("Location:login.php?msg=Login Failed");
     } else {
          header("location:./klinik/index.php");
     }*/
	 
	 
?>
