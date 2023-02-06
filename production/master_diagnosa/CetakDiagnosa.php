<?php
 	 require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");     
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt(); 
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
         
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     if($_GET["id_poli"]) $_POST["id_poli"] = $_GET["id_poli"];
     
     $editPage = "diagnosa_edit.php?nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $hapusPage = "diagnosa_edit.php?del=1&nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $thisPage = "diagnosa_view.php?";
     
     
     if($_POST["id_poli"]) $sql_where[] = " id_poli = ".QuoteValue(DPE_CHAR, $_POST["id_poli"]);

     $sql = "select a.*, poli_nama from klinik.klinik_diagnosa a 
     left join global.global_auth_poli b on a.id_poli = b.poli_id";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where); 
     $sql .= " order by diagnosa_nomor_tanpa_titik";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);

     
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Master Diagnosa";
     
     $isAllowedDel = $auth->IsAllowed("man_medis_diagnosa",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_medis_diagnosa",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_medis_diagnosa",PRIV_CREATE);
     
     // --- construct new table ---- //
   
     	$counter=0;
     $counterHeader = 0;
   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomor Urut";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Code";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Deskripsi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;
     
        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_short_desc"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

         
     }
     
     $colspan = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;


	
?>
<!DOCTYPE html>
<html lang="en">

  <script type="text/javascript">
    window.print();
  </script>
  <body class="nav-md">

    <table width="100%">
      <tr>
        <td>
          <center><h2>Daftar Diagnosa ICD 10 Poli</h2></center>
        </td>
      </tr>
    </table>
    
    <table style=" border-collapse: collapse;" border="1" cellspacing="0" width="100%">
      <thead>
        <tr>
          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
            <? } ?>
        </tr>
      </thead>
      <tbody>
          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
          
          <tr class="even pointer">
            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
            <? } ?>
            
          </tr>
           
         <? } ?>
      </tbody>
    </table>

  </body>
</html>