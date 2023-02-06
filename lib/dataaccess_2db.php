<?php
require_once("penghubung.inc.php");
require_once($ROOT."lib/encrypt.php");
require_once($ROOT."lib/conf/database.php");
require_once($ROOT."lib/adodb/adodb.inc.php");
require_once($ROOT."lib/adodb/adodb-errorhandler.inc.php");

$G_Connection = & ADONewConnection(DB_DRIVER);
$G_Connection->debug = DB_DEBUGGING;

$Wifi_G_Connection = & ADONewConnection(Wifi_DB_DRIVER);
$Wifi_G_Connection->debug = DB_DEBUGGING;

$PG_G_Connection = & ADONewConnection(PG_DB_DRIVER);
$PG_G_Connection->debug = DB_DEBUGGING;

function QuoteTable($in_tableName)
{
    return "`".$in_tableName."`";
}

function QuoteField($in_fieldName)
{
    return "`".$in_fieldName."`";
}

function _QuoteChar($in_value)
{
    global $G_Connection;
    return $G_Connection->qstr($in_value);
}

function _QuoteExecDate($in_date)
{
    global $G_Connection;
    return $G_Connection->DBDate($in_date);
}

function _QuoteSelectDate($in_format, $in_field)
{
    global $G_Connection;
    return $G_Connection->SqlDate($in_format, $in_field);
}

function _QuoteExecDateTime($in_dateTime)
{
    global $G_Connection;
    return $G_Connection->DBTimeStamp($in_dateTime);
}

function QuoteValue($in_type,$val,$in_format=null)
{
     switch ($in_type) {
          case  DPE_CHAR: return _QuoteChar($val);
          break;
          case  DPE_DATE: return _QuoteExecDate($val);
          break; 
          case  DPE_DATETIME: return _QuoteExecDateTime($val);
          break;
          case  DPE_TIMESTAMP: return _QuoteExecDateTime($val);
          break;
          case  DPE_NUMERIC: return ($val && is_numeric($val)) ? $val : "0";
          break;
          case  DPE_NUMERICKEY: return ($val) ? $val : "null";
          break;
          case  DPE_CHARKEY: return ($val) ? _QuoteChar(trim($val)) : "null";
          break;
          default: return $val;
     }

}

class DataAccess
{
    var $db;

     /**
    * Constucts a new DataAccess object
    * @param $host string hostname for dbserver
    * @param $user string dbserver user
    * @param $pass string dbserver user password
    * @param $db string database name
    * usage $somevar = new DataAccess()
    * setup files can be configured in config/config.cfg.php
    */

    function DataAccess($in_post=null)
    {
        global $G_Connection;
        $encrypt = new Encrypt();

        if ($in_post) 
             $G_Connection->PConnect(DB_SERVER,$encrypt->Decode(DB_USER),$encrypt->Decode(DB_PASSWORD),DB_NAME);
        else 
             $G_Connection->PConnect(DB_SERVER,$encrypt->Decode(DB_USER),$encrypt->Decode(DB_PASSWORD),DB_NAME);
         
        $this->db = & $G_Connection;
    }
    
   
	function Reconnect($in_dbName)
    {
        $this->db->PConnect(DB_SERVER,$encrypt->Decode(DB_USER),$encrypt->Decode(DB_PASSWORD),$in_dbName);
    }
    
    function CloseDb()
    {
        $this->db->close;
    }

   function Execute($in_sql,$in_schema=DB_SCHEMA)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->db->Execute($in_sql);
    }
    
    function Query($in_sql,$numrows=-1,$offset=-1,$in_schema=DB_SCHEMA)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->db->SelectLimit($in_sql, $numrows, $offset);
    }
    
    function & Fetch(& $in_rs)
    {
        return $in_rs->FetchRow();
    }

    function & FetchAll(& $in_rs)
    {
        $res=array();
        $this->MoveFirst($in_rs);
        while  ($col = $this->Fetch($in_rs))
        {
            $res[]=$col;
        }
        return $res;
    }


    function MoveFirst(& $in_rs)
    {
        $in_rs->MoveFirst();
    }

    function MoveNext(& $in_rs)
    {
        $in_rs->MoveNext();
    }

    function MovePrev(& $in_rs)
    {
        $in_rs->Move($in_rs->CurrentRow()-1);
    }

    function MoveLast(& $in_rs)
    {
        $in_rs->MoveLast();
    }

    function RowCount(& $in_rs)
    {
        return $in_rs->RecordCount();
    }

    function GetLastID($in_tabel, $in_field,$in_schema=DB_SCHEMA)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $query_rsLastID = sprintf("SELECT MAX($in_field) as last_id FROM $in_tabel");
        $rsLastID = $this->db->Execute($query_rsLastID);
        $row_rsLastID = $this->Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }
     
    function & GetNewID($in_tabel, $in_field,$in_schema=DB_SCHEMA)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->GetLastID($in_tabel, $in_field,$in_schema);
        return $row_rsMaxID+1;
    }
    
    function GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

    //--- G Add Code Here ---//
    //---- Clear Method ----//
    function Clear(& $in_rs) {
       if ($in_rs) {
            $in_rs->Close();
       } 
    }
    
    //---- Close Method ----//
    function Close() {
        $this->db->Close();       
    }

    //---- Count Method ----//
    function Count(& $in_rs) {
        if($in_rs) {
            return $in_rs->RecordCount();
        } else {
            return (-1);
        }
    }

    function GetLastID_W($in_tabel, $in_field, $in_where = "", $in_schema=DB_SCHEMA)
    {   
        
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $_whereSQL = "";
        if ($in_where != "")
            $_whereSQL = "WHERE ".$in_where;
        $query_rsLastID = "SELECT MAX(".$in_field.") as last_id FROM ".$in_tabel." ".$_whereSQL;
        $rsLastID = $this->db->Execute($query_rsLastID);
        $row_rsLastID = $this->Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }

    function & GetNewID_W($in_tabel, $in_field, $in_where = "", $in_schema=DB_SCHEMA)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->GetLastID_W($in_tabel, $in_field, $in_where, $in_schema);
        return $row_rsMaxID+1;
    }
    
   
}


class PG_DataAccess
{
    var $postgres_db;

     /**
    * Constucts a new DataAccess object
    * @param $host string hostname for dbserver
    * @param $user string dbserver user
    * @param $pass string dbserver user password
    * @param $db string database name
    * usage $somevar = new DataAccess()
    * setup files can be configured in config/config.cfg.php
    */
    
    function PG_DataAccess($in_post=null)
    {
        global $PG_G_Connection;
        $encrypt = new Encrypt();

        /*if ($in_post) 
             $PG_G_Connection->PConnect(PG_DB_SERVER,$encrypt->Decode(PG_DB_USER),$encrypt->Decode(PG_DB_PASSWORD),$encrypt->Decode(PG_DB_GL_NAME));
        else 
             $PG_G_Connection->PConnect(PG_DB_SERVER,$encrypt->Decode(PG_DB_USER),$encrypt->Decode(PG_DB_PASSWORD),(PG_DB_NAME));*/
        
        if ($in_post) 
             $PG_G_Connection->PConnect(PG_DB_SERVER,PG_DB_USER,PG_DB_PASSWORD,PG_DB_GL_NAME);
        else 
             $PG_G_Connection->PConnect(PG_DB_SERVER,PG_DB_USER,PG_DB_PASSWORD,(PG_DB_NAME));
         
        $this->postgres_db = & $PG_G_Connection;
    }
    
    
    function PG_Reconnect($in_dbName)
    {
        //$this->postgres_db->PConnect(PG_B_SERVER,$encrypt->Decode(PG_DB_USER),$encrypt->Decode(PG_DB_PASSWORD),$encrypt->Decode($in_dbName));
        $this->postgres_db->PConnect(PG_B_SERVER,PG_DB_USER,PG_DB_PASSWORD,$in_dbName);
    }
    
    function PG_CloseDb()
    {
        $this->postgres_db->close;
    }
    
    function PG_Execute($in_sql,$in_schema=PG_DB_SCHEMA)
    {
        if($in_schema) $this->postgres_db->Execute("set search_path to ".$in_schema);
		    $this->postgres_db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->postgres_db->Execute($in_sql);
    }

    function PG_Query($in_sql,$numrows=-1,$offset=-1,$in_schema=PG_DB_SCHEMA)
    {
        if($in_schema) $this->postgres_db->Execute("set search_path to ".$in_schema);
        $this->postgres_db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->postgres_db->SelectLimit($in_sql, $numrows, $offset);
    }
    
    function & PG_Fetch(& $in_rs)
    {
        return $in_rs->FetchRow();
    }

    function & PG_FetchAll(& $in_rs)
    {
        $res=array();
        $this->PG_MoveFirst($in_rs);
        while  ($col = $this->PG_Fetch($in_rs))
        {
            $res[]=$col;
        }
        return $res;
    }
    
    function PG_MoveFirst(& $in_rs)
    {
        $in_rs->MoveFirst();
    }

    function PG_MoveNext(& $in_rs)
    {
        $in_rs->MoveNext();
    }

    function PG_MovePrev(& $in_rs)
    {
        $in_rs->Move($in_rs->CurrentRow()-1);
    }

    function PG_MoveLast(& $in_rs)
    {
        $in_rs->MoveLast();
    }

    function PG_RowCount(& $in_rs)
    {
        return $in_rs->RecordCount();
    }
    
     function PG_GetLastID($in_tabel, $in_field,$in_schema=PG_DB_SCHEMA)
    {
        if($in_schema) $this->postgres_db->Execute("set search_path to ".$in_schema);
        $query_rsLastID = sprintf("SELECT MAX($in_field) as last_id FROM $in_tabel");
        $rsLastID = $this->postgres_db->Execute($query_rsLastID);
        $row_rsLastID = $this->PG_Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }

    function & PG_GetNewID($in_tabel, $in_field,$in_schema=PG_DB_SCHEMA)
    {
        if($in_schema) $this->postgres_db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->PG_GetLastID($in_tabel, $in_field,$in_schema);
        return $row_rsMaxID+1;
    }
    
    
    
    
    
     function PG_GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

    //--- G Add Code Here ---//
    //---- Clear Method ----//
    function PG_Clear(& $in_rs) {
       if ($in_rs) {
            $in_rs->Close();
       } 
    }
    
    //---- Close Method ----//
    function PG_Close() {
        $this->postgres_db->Close();       
    }

    //---- Count Method ----//
    function PG_Count(& $in_rs) {
        if($in_rs) {
            return $in_rs->RecordCount();
        } else {
            return (-1);
        }
    }


    function PG_GetLastID_W($in_tabel, $in_field, $in_where = "", $in_schema=PG_DB_SCHEMA)
    {   
        
        if($in_schema) $this->db->PG_Execute("set search_path to ".$in_schema);
        $_whereSQL = "";
        if ($in_where != "")
            $_whereSQL = "WHERE ".$in_where;
        $query_rsLastID = "SELECT MAX(".$in_field.") as last_id FROM ".$in_tabel." ".$_whereSQL;
        $rsLastID = $this->db->PG_Execute($query_rsLastID);
        $row_rsLastID = $this->PG_Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }
    
    

    function & PG_GetNewID_W($in_tabel, $in_field, $in_where = "", $in_schema=PG_DB_SCHEMA)
    {
        if($in_schema) $this->postgres_db->PG_Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->PG_GetLastID_W($in_tabel, $in_field, $in_where, $in_schema);
        return $row_rsMaxID+1;
    }
    //--- End of G Code --//  
}


class Wifi_DataAccess
{
    var $wifi_db;

     /**
    * Constucts a new DataAccess object
    * @param $host string hostname for dbserver
    * @param $user string dbserver user
    * @param $pass string dbserver user password
    * @param $db string database name
    * usage $somevar = new DataAccess()
    * setup files can be configured in config/config.cfg.php
    */
    
    function Wifi_DataAccess($in_post=null)
    {
        global $Wifi_G_Connection;
        $encrypt = new Encrypt();

        if ($in_post) 
             $Wifi_G_Connection->PConnect(Wifi_DB_SERVER,$encrypt->Decode(Wifi_DB_USER),$encrypt->Decode(Wifi_DB_PASSWORD),$encrypt->Decode(Wifi_DB_GL_NAME));
        else 
             $Wifi_G_Connection->PConnect(Wifi_DB_SERVER,$encrypt->Decode(Wifi_DB_USER),$encrypt->Decode(Wifi_DB_PASSWORD),(Wifi_DB_NAME));
         
        $this->wifi_db = & $Wifi_G_Connection;
    }
    
    
    function Wifi_Reconnect($in_dbName)
    {
        $this->wifi_db->PConnect(Wifi_B_SERVER,$encrypt->Decode(Wifi_DB_USER),$encrypt->Decode(Wifi_DB_PASSWORD),$encrypt->Decode($in_dbName));
    }
    
    function Wifi_CloseDb()
    {
        $this->wifi_db->close;
    }
    
    function Wifi_Execute($in_sql,$in_schema=Wifi_DB_SCHEMA)
    {
        if($in_schema) $this->wifi_db->Execute("set search_path to ".$in_schema);
		    $this->wifi_db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->wifi_db->Execute($in_sql);
    }

    function Wifi_Query($in_sql,$numrows=-1,$offset=-1,$in_schema=Wifi_DB_SCHEMA)
    {
        if($in_schema) $this->wifi_db->Execute("set search_path to ".$in_schema);
        $this->wifi_db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->wifi_db->SelectLimit($in_sql, $numrows, $offset);
    }
    
    function & Wifi_Fetch(& $in_rs)
    {
        return $in_rs->FetchRow();
    }

    function & Wifi_FetchAll(& $in_rs)
    {
        $res=array();
        $this->Wifi_MoveFirst($in_rs);
        while  ($col = $this->Wifi_Fetch($in_rs))
        {
            $res[]=$col;
        }
        return $res;
    }
    
    function Wifi_MoveFirst(& $in_rs)
    {
        $in_rs->MoveFirst();
    }

    function Wifi_MoveNext(& $in_rs)
    {
        $in_rs->MoveNext();
    }

    function Wifi_MovePrev(& $in_rs)
    {
        $in_rs->Move($in_rs->CurrentRow()-1);
    }

    function Wifi_MoveLast(& $in_rs)
    {
        $in_rs->MoveLast();
    }

    function Wifi_RowCount(& $in_rs)
    {
        return $in_rs->RecordCount();
    }
    
     function Wifi_GetLastID($in_tabel, $in_field,$in_schema=Wifi_DB_SCHEMA)
    {
        if($in_schema) $this->wifi_db->Execute("set search_path to ".$in_schema);
        $query_rsLastID = sprintf("SELECT MAX($in_field) as last_id FROM $in_tabel");
        $rsLastID = $this->wifi_db->Execute($query_rsLastID);
        $row_rsLastID = $this->Wifi_Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }

    function & Wifi_GetNewID($in_tabel, $in_field,$in_schema=Wifi_DB_SCHEMA)
    {
        if($in_schema) $this->wifi_db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->Wifi_GetLastID($in_tabel, $in_field,$in_schema);
        return $row_rsMaxID+1;
    }
    
    
    
    
    
     function Wifi_GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

    //--- G Add Code Here ---//
    //---- Clear Method ----//
    function Wifi_Clear(& $in_rs) {
       if ($in_rs) {
            $in_rs->Close();
       } 
    }
    
    //---- Close Method ----//
    function Wifi_Close() {
        $this->wifi_db->Close();       
    }

    //---- Count Method ----//
    function Wifi_Count(& $in_rs) {
        if($in_rs) {
            return $in_rs->RecordCount();
        } else {
            return (-1);
        }
    }


    function Wifi_GetLastID_W($in_tabel, $in_field, $in_where = "", $in_schema=Wifi_DB_SCHEMA)
    {   
        
        if($in_schema) $this->db->Wifi_Execute("set search_path to ".$in_schema);
        $_whereSQL = "";
        if ($in_where != "")
            $_whereSQL = "WHERE ".$in_where;
        $query_rsLastID = "SELECT MAX(".$in_field.") as last_id FROM ".$in_tabel." ".$_whereSQL;
        $rsLastID = $this->db->Wifi_Execute($query_rsLastID);
        $row_rsLastID = $this->Wifi_Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }
    
    

    function & Wifi_GetNewID_W($in_tabel, $in_field, $in_where = "", $in_schema=Wifi_DB_SCHEMA)
    {
        if($in_schema) $this->wifi_db->Wifi_Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->Wifi_GetLastID_W($in_tabel, $in_field, $in_where, $in_schema);
        return $row_rsMaxID+1;
    }
    //--- End of G Code --//  
}

?>
