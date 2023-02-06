<?php
require_once("penghubung.inc.php");      
require_once($ROOT."lib/dataaccess.php");  
require_once($ROOT."lib/regLib.php"); 

class CAuth
{
     var $_usrId;     
     var $_dataAccess;
     var $globalData;
     var $_usrData;
     var $_usrConfig;

     function CAuth()
     {
          global $globalData;
          $this->_dataAccess=new DataAccess();

          $this->globalData=&$globalData;
          $loginData=$this->globalData->GetEntry("Silahkan Menunggu");
          $loginConfig=$this->globalData->GetEntry("Config");

          $this->_usrId = $loginData["id"];
          $this->_usr_app_def = $loginData["usr_app_def"];
          $this->_usrName = $loginData["name"];
          $this->_idDep = $loginData["id_dep"];
          $this->_depNama = $loginData["dep_nama"];
          $this->_depLowest = $loginData["dep_lowest"];
          $this->_poliId = $loginData["poli_id"];
          $this->_usrData = $loginData;
          $this->_usrConfig = $loginConfig;
          $this->_ref = $loginData["ref"];
          $this->_anggarNama = $loginConfig["anggar_nama"];
          $this->_anggarId = $loginConfig["cfg_tahun_anggaran"];
     }

     function IsLoginOk($in_username,$in_password,$ref) 
     {
            
        if($ref=='antrian') { 
                $password_in = $in_password;
          }else{
                $password_in = md5($in_password);
          }
    

           $sql = "select a.*,b.dep_id,b.dep_nama,b.dep_lowest,a.id_dep from global.global_auth_user a
                  left join global.global_departemen b on a.id_dep = b.dep_id
                  left join hris.hris_pegawai c on c.pgw_id = a.id_pgw               
                  where c.pgw_nip = ".QuoteValue(DPE_CHAR,$in_username)."  
                  and usr_password = ".QuoteValue(DPE_CHAR,md5($in_password));
          
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);
          //print_r($sql);
          //die();
          
          if($dataUser){

              $sql = "select usr_app_def from global.global_auth_user            
                  where usr_id = ".QuoteValue(DPE_CHAR,$dataUser["usr_id"]);
              $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
              $dataUsrDef = $this->_dataAccess->Fetch($rs);
              //echo $sql;
              //die();
              
               $data["loginname"] =  $dataUser["usr_loginname"];
               $data["name"] =  $dataUser["usr_name"];
               $data["id"] = $dataUser["usr_id"];            
               $data["tipe"] = $dataUser["usr_tipe"];
               $data["rol"] = $dataUser["id_rol"];
               $data["id_dep"] = $dataUser["id_dep"];
               $data["dep_id"] = $dataUser["dep_id"];
               $data["dep_nama"] = $dataUser["dep_nama"];
               $data["dep_lowest"] = $dataUser["dep_lowest"];
               $data["poli_id"] = $dataUser["usr_poli"];
               $data["usr_app_def"] = $dataUsrDef["usr_app_def"];        
               $data["ref"] = $ref;                              
                                      
               $this->globalData->SetEntry("Silahkan Menunggu",$data);
               $this->globalData->Save();
               
               $this->SetUserLog($dataUser["usr_id"]);
               
               return $data;
                    
          } else return false;
     }


     function IsAutoLoginOk($in_username,$in_project) 
     {
          $sql = "select a.* from vglobal_user_customer a 
                    where a.usr_loginname = ".QuoteValue(DPE_CHAR,$in_username);
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);
          
          if($dataUser["usr_loginname"]){
               $data["loginname"] =  $dataUser["usr_loginname"];
               $data["name"] =  $dataUser["usr_name"];
               $data["id"] = $dataUser["usr_id"];            
               $data["tipe"] = $dataUser["usr_tipe"];
               $data["project"] = $in_project;            
                 
               $this->globalData->SetEntry("Silahkan Menunggu",$data);
               $this->globalData->Save();
               
               $this->SetUserLog($dataUser["usr_id"]);
               return $data;
          } else return false;
     }


     function IsAllowed($in_modul=null,$in_akses=null)
     {
          if(!isset($this->_usrId)) return 1;
          else { 
               $this->SetUserLog($this->_usrId);
               if($in_modul){
                    $sql = "select b.* 
                            from global.global_auth_user a 
                            join global.global_auth_role_priv b on a.id_rol = b.id_rol 
                            join global.global_auth_privilege c on b.id_priv = c.priv_id
                            where a.usr_id = ".QuoteValue(DPE_CHAR,$this->_usrId)." 
                            and c.id_app = ".$this->_usr_app_def." 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$in_modul);
                    $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
                    $dataPriv = $this->_dataAccess->Fetch($rs);
               
                    if($dataPriv["rol_priv_access"]{$in_akses}=="1") return true; //true
                    else return false; //false
                }                
                return false; //false
          }
          return true; //true
     }
     
     function IsMenuAllowed($menu)
     {
          if(!isset($this->_usrId)) return 1;          
          for($i=0,$n=count($menu);$i<$n;$i++){
               for($j=0,$k=count($menu[$i]["sub"]);$j<$k;$j++){
                    $sql[] = "select c.priv_code 
                            from global.global_auth_user a 
                            join global.global_auth_role_priv b on a.id_rol = b.id_rol 
                            join global.global_auth_privilege c on b.id_priv = c.priv_id 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$menu[$i]["sub"][$j]["priv"])."   
                            and substring(rol_priv_access from 2 for 1) = '1' 
                            where a.usr_id = ".$this->_usrId;                    
               }
               
               if(count($menu[$i]["sub"])==0){
                    $sql[] = "select c.priv_code 
                            from global.global_auth_user a 
                            join global.global_auth_role_priv b on a.id_rol = b.id_rol 
                            join global.global_auth_privilege c on b.id_priv = c.priv_id 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$menu[$i]["priv"])."   
                            and substring(rol_priv_access from 2 for 1) = '1' 
                            where a.usr_id = ".$this->_usrId;
               }
          }
          
          $sql = implode(" union all ", $sql);
          
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          while($dataPriv = $this->_dataAccess->Fetch($rs)) {
               $status[$dataPriv["priv_code"]] = true;
          }
          
          return $status;
     }



     function Logout()
     {
          if($this->_usrId){
               $sql = "update global.global_user_log set usr_log_aktif = 'n',usr_log_cout = ".QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s"))." where id_usr = ".QuoteValue(DPE_CHAR,$this->_usrId)." and usr_log_aktif = 'y'";
               $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          }
          
          $this->globalData->DelEntry("Login");
          $this->globalData->Free();
     }

     function CleanIdle()
     {
          $sql = "update global.global_user_log set usr_log_aktif = 'n' where id_usr in (select id_usr from vglobal_user_idle where online_status = ".QuoteValue(DPE_CHAR,USER_IDLE).") and usr_log_aktif = 'y'";
          $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          return;
     }
     
     
     function GetUserData()
     {
          return $this->_usrData;
     }
     
     function GetUserId()
     {
          return $this->_usrId;
     }
     
     function GetUserName()
     {
          return $this->_usrName;
     }
     
     function GetDepId()
     {
          return $this->_idDep;
     }
     
     function GetDepNama()
     {
          return $this->_depNama;
     }
     
     function GetDepLowest()
     {
          return $this->_depLowest;
     }
     
     function IdPoli()
     {
          return $this->_poliId;
     }
     
     function GetUserConfig()
     {
          return $this->_usrConfig;
     }
     
    function GetNamaLogistik()
     {
          return $this->_ref;
     }
     
     function GetAnggarId()
    {
        return $this->_anggarId;
    }

    function GetAnggarName()
    {
        return $this->_anggarNama;
    }


 
     function SetUserLog($in_id)
     {
          $sql = "update global.global_user_log set usr_log_aktif = 'n' where id_usr = ".QuoteValue(DPE_CHAR,$in_id)." and usr_log_session <> ".QuoteValue(DPE_CHAR,session_id());
          $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          
          $sql = "select usr_log_id from global.global_user_log where id_usr = ".QuoteValue(DPE_CHAR,$in_id)." and usr_log_session = ".QuoteValue(DPE_CHAR,session_id());
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);
          
          if($dataUser["usr_log_id"]) $sql = "update global.global_user_log set usr_log_lifetime = ".QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s")).", usr_log_aktif = 'y' where usr_log_id = ".$dataUser["usr_log_id"];
          else $sql = "insert into global.global_user_log(usr_log_id,id_usr,usr_log_session,usr_log_cin,usr_log_lifetime) values (".$this->_dataAccess->GetNewID("global.global_user_log","usr_log_id",DB_SCHEMA_GLOBAL).",".QuoteValue(DPE_CHAR,$in_id).",".QuoteValue(DPE_CHAR,session_id()).",".QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s")).",".QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s")).")";
          $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
     }
     
     function SetConfig($inConfig)
     {    
          $this->globalData->DelEntry("Config");
          $this->globalData->SetEntry("Config",$inConfig);
          $this->globalData->Save();
     }
}

?>
