<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($APLICATION_ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $skr = date('Y-m-d');
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $usrId = $auth->GetUserId(); 
	   if(!$_POST["sms_tanggal"]) $_POST["sms_tanggal"] = $skr;

     $backPage = "promo_view.php";
     $findPage = "cari_contact.php?";
     
     if ($_POST["btnSave"]) {          
         
               $dbTable = "global.global_sms_promo";
               
               $dbField[0] = "sms_promo_id";   // PK
               $dbField[1] = "sms_promo_isi"; 
               $dbField[2] = "id_dep";
               $dbField[3] = "sms_promo_pasien";
               $dbField[4] = "sms_promo_when_create";
               $dbField[5] = "sms_promo_who_create";

               $smsId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$smsId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["sms_promo_isi"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["sms_promo_pasien"]);
               $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Insert() or die("update  error");	
                    
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
                                     
                         
          $sql = "select * from global.global_customer_contact where id_dep = ".QuoteValue(DPE_CHAR,$depId)."
                  and contact_no_hp is not null and contact_no_hp <>'' and contact_no_hp <>'-'";
          $rs_edit = $dtaccess->Execute($sql);
          $DataSms = $dtaccess->FetchAll($rs_edit); 
        
               for($i=0,$n=count($_POST["cust_usr_id"]);$i<$n;$i++) {                                        
               //--ngisi data sms ke db sms
               //$online_access = new PG_DataAccess();
               $sql_sms = "insert into public.outbox(\"DestinationNumber\",\"Coding\",\"TextDecoded\",\"CreatorID\") 
                           values(".QuoteValue(DPE_CHAR,$_POST["cust_usr_no_hp"][$i]).",'Default_No_Compression',".QuoteValue(DPE_CHAR,$_POST["sms_promo_isi"]).",'1')";
               //$sms = $online_access->PG_Execute($sql_sms);
               $sms = $dtaccess->Execute($sql_sms);
               } 
               
        if($_POST["sms_promo_pasien"]!='--'){
        
          $sql = "select cust_usr_no_hp from global.global_customer_grup_contact_det a
                    join global.global_customer_grup_contact b on a.id_grup_contact = b.grup_contact_id
                    join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
                    where a.id_grup_contact = ".QuoteValue(DPE_CHAR,$_POST["sms_promo_pasien"])."
                    order by id_cust_usr";
          $rs = $dtaccess->Execute($sql);
          $DataSmsPasien = $dtaccess->FetchAll($rs);
          
               for($i=0,$n=count($DataSmsPasien);$i<$n;$i++) {                                        
               //--ngisi data sms ke db sms
               //$online_access = new PG_DataAccess();
               $sql_sms = "insert into public.outbox(\"DestinationNumber\",\"Coding\",\"TextDecoded\",\"CreatorID\") 
                           values(".QuoteValue(DPE_CHAR,$DataSmsPasien[$i]["cust_usr_no_hp"]).",'Default_No_Compression',".QuoteValue(DPE_CHAR,$_POST["sms_promo_isi"]).",'1')";    
               $sms = $dtaccess->Execute($sql_sms);
               //$sms = $online_access->PG_Execute($sql_sms);
               //$sms = $dtaccess->PG_Execute($sql_sms);
               } 
        
        }
               
               header("location:".$backPage);
               exit();        
     } 
     
          $sql = "select a.* from global.global_departemen a where dep_id = ".QuoteValue(DPE_CHAR,$depId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
        	$_POST["dep_id"] = $row_edit["dep_id"];
          $_POST["dep_sms_pengumuman"] = $row_edit["dep_sms_pengumuman"];
          
          
          
          $sql = "select * from global.global_customer_grup_contact where id_dep = ".QuoteValue(DPE_CHAR,$depId)."
                    order by grup_contact_nama";
          $rs = $dtaccess->Execute($sql);
          $dataGrup = $dtaccess->FetchAll($rs);

?>
<?php echo $view->RenderBody("ipad_depans.css",true,"SMS PROMO"); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?> 
<script language="javascript" type="text/javascript">
function PasienTambah(){
     var akhir = eval(document.getElementById('pasien_tot').value)+1;
     
      $('#tb_pasien').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_pasien_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:45, maxLength:100, name:'cust_usr_nama[]', id:'cust_usr_nama_'+akhir},[],
                         'a',{ href:'<?php echo $findPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Pasien'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>gambar/finder.png', hspace:2, align:'middle', style:'cursor:pointer; margin-bottom:15px;', border:0, class:'tombol',}
                         ],
                         'input', {type:'hidden', value:'', name:'cust_usr_id[]', id:'cust_usr_id_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:25, maxLength:100,name:'cust_usr_no_hp[]', id:'cust_usr_no_hp_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'submit', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { PasienDelete(akhir) } );
     document.getElementById('cust_usr_nama_'+akhir).readOnly = true;
          
     document.getElementById('pasien_tot').value = akhir;
     tb_init('a.thickbox');
}

function Kembali()
{
    document.location.href='<?php echo $backPage;?>';
}

function PasienDelete(akhir){
     document.getElementById('hid_pasien_del').value += document.getElementById('cust_usr_id_'+akhir).value;
     
     $('#tr_pasien_'+akhir).remove();
}

function CheckDataSave(frm)
{  
     if(!frm.sms_promo_isi.value){
		alert('Isi Pengumuman harus di isi');
		frm.sms_promo_isi.focus();
          return false;
	}	 
	
	return true;
          
}
</script>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $APLICATION_ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">SMS PROMO</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>

<div id="body">
<div id="scroller">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="0">
<tr>
     <td>
     <table width="100%" border="1">
          <tr>
               <td align="left" class="tablecontent-odd" width=20%"><strong>Pengumuman</strong>&nbsp;</td>
               <td align="center" class="tablecontent-odd" width="1%">:</td>
               <td width="80%" class="tablecontent-odd">
                    <?php echo $view->RenderTextArea("sms_promo_isi","sms_promo_isi","5","100",$_POST["sms_promo_isi"],"inputField", null,false);?>
               </td>
          </tr>
           <tr>
               <td align="left" class="tablecontent-odd" width=20%"><strong>Kirim ke pasien ?</strong>&nbsp;</td>
               <td align="center" class="tablecontent-odd" width="1%">:</td>
               <td width="80%" class="tablecontent-odd"> 
                       <select name="sms_promo_pasien" onchange="this.form.submit();">
                           <option value="--">[- Grup Pasien -]</option>
                           <?php for($i=0,$n=count($dataGrup);$i<$n;$i++) { ?>
            							 <option value="<?php echo $dataGrup[$i]["grup_contact_id"];?>" <?php if($_POST["sms_promo_pasien"]==$dataGrup[$i]["grup_contact_id"]) echo "selected";?>><?php echo $dataGrup[$i]["grup_contact_nama"];?></option>
            						   <?php } ?>               
                       </select>
                    <!--<select name="sms_promo_pasien" id="sms_promo_pasien" onKeyDown="return tabOnEnter(this, event);">								
                <option value="y" <?php if($_POST["sms_promo_pasien"]=='y') echo "selected"; ?>>Ya</option>
                <option value="n" <?php if($_POST["sms_promo_pasien"]=='n') echo "selected"; ?>>Tidak</option>
                    		</select>-->
               </td>
          </tr>
           <tr>
      
      <td width="20%"  class="tablecontent" align="left">Contact</td>
      <td align="center" class="tablecontent" width="1%">:</td>
      <td align="left" class="tablecontent" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_pasien">
        <?php if(!$_POST["cust_usr_nama"]) { ?>
					    <tr id="tr_pasien_0">
						      <td align="left" class="tablecontent-odd" width="70%">
							    <?php echo $view->RenderTextBox("cust_usr_nama[]","cust_usr_nama_0","45","100",$_POST["cust_usr_nama"][0],"inputField", "readonly",false);?>
							    <a href="<?php echo $findPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>gambar/finder.png" border="0" class="tombol" align="middle" style="cursor:pointer; margin-bottom:15px;" title="Cari Contact" alt="Cari Contact" /></a>
							    <input type="hidden" id="cust_usr_id_0" name="cust_usr_id[]" value="<?php echo $_POST["cust_usr_id"];?>"/>
			            </td>
                  <td align="left" class="tablecontent-odd">
                     <input size="25" maxlength="100" name="cust_usr_no_hp[0]" id="cust_usr_no_hp_0" value="<?php echo $_POST["cust_usr_no_hp"][0]?>" />
                  </td>
						  <td align="center" class="tablecontent-odd" width="30%">
							<input name="btnAdd" id="btnAdd" type="button" value="Tambah" class="submit" onClick="PasienTambah();">
							<input name="pasien_tot" id="pasien_tot" type="hidden" value="0">
			               </td>
					   </tr>
        <?php } else  { ?>
        <?php for($i=0,$n=count($_POST["cust_usr_id"]);$i<$n;$i++) { ?>
              <tr id="tr_pasien_<?php echo $i;?>">
              <td align="left" class="tablecontent-odd" width="70%">
                    <?php echo $view->RenderTextBox("cust_usr_nama[]","cust_usr_nama_".$i,"45","100",$_POST["cust_usr_nama"][$i],"inputField", "readonly",false);?>                         
                      <a href="<?php echo $findPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>gambar/finder.png" border="0" class="tombol" align="middle" style="cursor:pointer; margin-bottom:15px;" title="Cari Contact" alt="Cari Contact" /></a>
                        <input type="hidden" id="cust_usr_id_<?php echo $i;?>" name="cust_usr_id[]" value="<?php echo $_POST["cust_usr_id"][$i];?>"/>
                       </td>                   
                         <td align="right" class="tablecontent-odd">
                              <input size="25" maxlength="100" name="cust_usr_no_hp[]" id="cust_usr_no_hp_<?php echo $i;?>" value="<?php echo $_POST["cust_usr_no_hp"][$i]?>" />
                         </td>                    
                       <td align="left" class="tablecontent-odd" width="30%">
                    <?php if($i==0) { ?>
                        <input class="submit" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="PasienTambah();">
                    <?php } else { ?>
                       <input class="submit" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="PasienDelete(<?php echo $i;?>);">
                    <?php } ?>
                       <input name="pasien_tot" id="pasien_tot" type="hidden" value="<?php echo $n;?>">
              </td>
              </tr>
        <?php } ?>
        <?php } ?>
        <?php echo $view->RenderHidden("hid_pasien_del","hid_pasien_del",'');?>
				</table>
      </td>
  </tr> 
          <tr>
          <td colspan="2">&nbsp;</td>
               <td align="left">
                    <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Kirim","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>                    
                    &nbsp;&nbsp;<input type="button" name="btnDel" id="btnDel" value="Kembali" class="submit" onClick="javascript:return Kembali();" />  
               </td>
          </tr>
     </table>
     </td>
</tr>
</table> 

<script>document.frmEdit.sms_tanggal.focus();</script>
<input type="hidden" name="dep_id" value="<?php echo $_POST["dep_id"];?>">  
</form>
</div> 
</div>

  		<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>

<?php echo $view->RenderBodyEnd(); ?>
