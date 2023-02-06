<?php
	// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
   	 $dtaccess = new DataAccess();
    

	 //delete di klinik folio
	 $sql = "delete from klinik.klinik_preop
				where preop_id = '$_POST[id]'";
	 $result = $dtaccess->Execute($sql);
	 
	  if ($result){
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('errorMsg'=>'Some errors occured.'));
		} 
	 
	 exit();      

?>