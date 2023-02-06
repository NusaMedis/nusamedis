<?php
function power($base, $pangkat)
{
    $jum = 1;
    for($i = 1;$i <= $pangkat; $i++)
        $jum = $jum * $base;

    return $jum;
}


function GetSQLValueString( $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
{
    $theValue = ( !get_magic_quotes_gpc( )) ? addslashes( $theValue) : $theValue;

    switch ( $theType) {
        case "text":
            $theValue = ( $theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "long":
        case "int":
            $theValue = ( $theValue != "") ? intval( $theValue) : "NULL";
            break;
        case "double": {
                $theValue = str_replace( '.', '', $theValue);
                $theValue = str_replace( ',', '.', $theValue);
                $theValue = ( $theValue != "") ? "'" . doubleval( $theValue) . "'" : "NULL";
            } 
            break;
        case "date":
            $theValue = ( $theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "defined":
            $theValue = ( $theValue != "") ? $theDefinedValue : $theNotDefinedValue;
            break;
    } 
    return $theValue;
} 

function errorPage() {
    include("errpage.php");
    exit();
}

function getFromConfig( $key)
{
   GLOBAL $dtaccess;
   $conn = $dtaccess;
    $_ssql = "SELECT ".$key." FROM gl.gl_konfigurasi";
    $qryTemp = $conn->Execute($_ssql) or die(errorPage());
    $data = $qryTemp->FetchRow();
    return $data[$key];
}

function getConfig()
{
   GLOBAL $dtaccess;
   $conn = $dtaccess;

    $_ssql = "SELECT * FROM gl.gl_konfigurasi";
    $qryTemp = $conn->Execute($_ssql) or die(errorPage());
    $data = $qryTemp->FetchRow();
    return $data;
}

function CheckBtnHapus($var) {
    return (substr( $var, 0, 9 ) == "btnHapus_");
}

function updateReturnEarning($nama_periode,$id_dept) {

	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//CARI KONFIGURASI
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	
   GLOBAL $dtaccess;
   $conn = $dtaccess;
   
   $query = sprintf("SELECT * FROM gl.gl_konfigurasi");
   $result = $conn->Execute($query) or die (errorPage());
   $datalabel = $result->FetchRow();

	//CARI SETUP RETURN EARNING
       $sql = 'SELECT id_prk,nama_prk,isakt_prk,isleft_prk 
               FROM gl.gl_perkiraan WHERE no_prk = \''.$datalabel["prk_netincome"].'\'';
       //echo $sql."<br>";
       $qry_prk = $conn->Execute($sql) or die(errorPage());
       $prk = $qry_prk->FetchRow();

    if($prk["id_prk"]){

    	//CARI PERIODE
        $query = sprintf("SELECT id_prd, awal_prd, akhir_prd, nama_prd FROM gl.gl_periode
                        WHERE nama_prd = '%s'", $nama_periode);
        $result = $conn->Execute($query) or die(errorPage());
        if($result->RecordCount() < 1 ) return false;
        $data = $result->FetchRow();
        $tgl_akhir_prd = $data["akhir_prd"];
        $tgl_awal_prd = $data["awal_prd"];
        $prd_id = $data["id_prd"];
        $prd = $data["nama_prd"];
        
    	
    	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    	//cari retained earning
    	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    	$sql = "select sum(m.aktiva) - sum(abs(m.hutmod)) as retained  
                from ( 
                select sum(b.jumlah_trad) as aktiva,0 as hutmod 
                from gl.gl_transaksi a 
                join gl.gl_transaksidetil b 
                on a.id_tra = b.tra_id 
                where a.tanggal_tra <= ".QuoteValue(DPE_DATE,$tgl_akhir_prd)." 
                and b.prk_id like '01%' 
                and a.dept_id = ".QuoteValue(DPE_CHAR,$id_dept)." 
                and a.ref_tra <> '-1' AND a.ref_tra <> '-2' 
                
                union all 
                 
                select 0 as aktiva,sum(b.jumlah_trad) as hutmod 
                from gl.gl_transaksi a 
                join gl.gl_transaksidetil b 
                on a.id_tra = b.tra_id 
                where a.tanggal_tra <= ".QuoteValue(DPE_DATE,$tgl_akhir_prd)."  
                and (b.prk_id like '02%' or b.prk_id like '03%')  
                and a.dept_id = ".QuoteValue(DPE_CHAR,$id_dept)." 
                and a.ref_tra <> '-1' AND a.ref_tra <> '-2' 
                ) as m ";
    	$rs = $conn->Execute($sql);
    	$dataRetained = $conn->Fetch($rs);

    
    	$sql = "select sum(b.jumlah_trad) as re 
                from gl.gl_transaksi a 
                join gl.gl_transaksidetil b 
                on a.id_tra = b.tra_id 
                where a.tanggal_tra < ".QuoteValue(DPE_DATE,$tgl_awal_prd)." 
                and a.dept_id = ".QuoteValue(DPE_CHAR,$id_dept)."  
                and a.ref_tra = '-2'";
    	$rs = $conn->Execute($sql);
    	$dataReturnLalu = $conn->Fetch($rs);
    
    
    	
    	$sql = "select sum(b.jumlah_trad) as labarugi 
                from gl.gl_transaksi a 
                join gl.gl_transaksidetil b 
                on a.id_tra = b.tra_id 
                where a.tanggal_tra >= ".QuoteValue(DPE_DATE,$tgl_awal_prd)."  
                and a.tanggal_tra <= ".QuoteValue(DPE_DATE,$tgl_akhir_prd)." 
                and a.dept_id = ".QuoteValue(DPE_CHAR,$id_dept)."  
                and b.prk_id like '5%'";
    	$rs = $conn->Execute($sql);
    	$dataLaba = $conn->Fetch($rs);
    
    	// --- karena di software jika laba hasilnya minus maka hasil query dikali -1 dulu
    	if($prk["isleft_prk"]=="N") $dataReturnLalu["re"] = ($dataReturnLalu["re"] * -1);
    	$returnEarning = $dataRetained["retained"] - ($dataLaba["labarugi"]*-1) - $dataReturnLalu["re"];
        if($prk["isleft_prk"]=="N") $returnEarning = ($returnEarning * -1);
        if(!$returnEarning) $returnEarning = "0";
        
        
        // -- karena re uda didapat skr tinggal insert aja ke dbnya ----
        $sql = "select id_tra from gl.gl_buffer_transaksi where dept_id = ".QuoteValue(DPE_CHAR,$id_dept)." 
                and ref_tra = '-2' and tanggal_tra = ".QuoteValue(DPE_DATE,$tgl_akhir_prd);
    	$rs = $conn->Execute($sql);
    	$dataRE= $conn->Fetch($rs);
    
    	$ketRE = "Return Earning Periode ".$prd;
        $dbTable = "gl.gl_buffer_transaksi";
        
        $dbField[0] = "id_tra";   // PK
        $dbField[1] = "ref_tra";
        $dbField[2] = "tanggal_tra";
        $dbField[3] = "ket_tra";
        $dbField[4] = "namauser";
        $dbField[5] = "real_time";
        $dbField[6] = "dept_id";
    
        if($dataRE["id_tra"]) $idTra = $dataRE["id_tra"];
        else {
            
            $idTra = $conn->GetNewID("gl.gl_buffer_transaksi","id_tra",DB_SCHEMA_GL);
        }
        
        $dbValue[0] = QuoteValue(DPE_NUMERIC,$idTra);
        $dbValue[1] = QuoteValue(DPE_CHAR,"-2");
    	  $dbValue[2] = QuoteValue(DPE_DATE,$tgl_akhir_prd);            
    	  $dbValue[3] = QuoteValue(DPE_CHAR,$ketRE);
    	  $dbValue[4] = QuoteValue(DPE_CHAR,"SYSTEM");
    	  $dbValue[5] = QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s"));
    	  $dbValue[6] = QuoteValue(DPE_CHAR,$id_dept);
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    
        if (!$dataRE["id_tra"]) {
            $dtmodel->Insert() or die("insert  error");	
        } else {
            $dtmodel->Update() or die("update  error");	
        }
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        
        
        // -- insert ke buffer detail
        if($dataRE["id_tra"]){
            $sql = "select id_trad from gl.gl_buffer_transaksidetil where tra_id = ".QuoteValue(DPE_CHAR,$dataRE["id_tra"]); 
        	$rs = $conn->Execute($sql);
        	$dataTransDet= $conn->Fetch($rs);
        }
    
        $dbTable = "gl.gl_buffer_transaksidetil";
        
        $dbField[0] = "id_trad";   // PK
        $dbField[1] = "tra_id";
        $dbField[2] = "prk_id";
        $dbField[3] = "ket_trad";
        $dbField[4] = "jumlah_trad";
        $dbField[5] = "dept_id";
    
        if($dataTransDet["id_trad"]) $idTrad = $dataTransDet["id_trad"];
        else {
            $idTrad = $conn->GetNewID("gl.gl_buffer_transaksidetil","id_trad",DB_SCHEMA_GL);
        }
        
        $dbValue[0] = QuoteValue(DPE_NUMERIC,$idTrad);
        $dbValue[1] = QuoteValue(DPE_NUMERIC,$idTra);
    	  $dbValue[2] = QuoteValue(DPE_CHAR,$prk["id_prk"]);            
    	  $dbValue[3] = QuoteValue(DPE_CHAR,$ketRE);
    	  $dbValue[4] = QuoteValue(DPE_NUMERIC,$returnEarning);
    	  $dbValue[5] = QuoteValue(DPE_CHAR,$id_dept);
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    
        if (!$dataTransDet["id_trad"]) {
            $dtmodel->Insert() or die("insert  error");	
        } else {
            $dtmodel->Update() or die("update  error");	
        }
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        unset($dataTransDet);
        unset($dataRE);
        
        // -- insert ke gl_transaksi    
        $sql = "select id_tra from gl.gl_transaksi where dept_id = ".QuoteValue(DPE_CHAR,$id_dept)." 
                and ref_tra = '-2' and tanggal_tra= ".QuoteValue(DPE_DATE,$tgl_akhir_prd);
    	$rs = $conn->Execute($sql);
    	$dataRE= $conn->Fetch($rs);
    	
        $dbTable = "gl.gl_transaksi";
        
        $dbField[0] = "id_tra";   // PK
        $dbField[1] = "ref_tra";
        $dbField[2] = "tanggal_tra";
        $dbField[3] = "ket_tra";
        $dbField[4] = "namauser";
        $dbField[5] = "real_time";
        $dbField[6] = "dept_id";
    
        if($dataRE["id_tra"]) $idTra = $dataRE["id_tra"];
        else {
            $idTra = $conn->GetNewID("gl.gl_transaksi","id_tra",DB_SCHEMA_GL);
        }
        
        $dbValue[0] = QuoteValue(DPE_NUMERIC,$idTra);
        $dbValue[1] = QuoteValue(DPE_CHAR,"-2");
    	$dbValue[2] = QuoteValue(DPE_DATE,$tgl_akhir_prd);            
    	$dbValue[3] = QuoteValue(DPE_CHAR,$ketRE);
    	$dbValue[4] = QuoteValue(DPE_CHAR,"SYSTEM");
    	$dbValue[5] = QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s"));
    	$dbValue[6] = QuoteValue(DPE_CHAR,$id_dept);
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    
        if (!$dataRE["id_tra"]) {
            $dtmodel->Insert() or die("insert  error");	
        } else {
            $dtmodel->Update() or die("update  error");	
        }
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        
        
        // -- insert ke gl detail
        if($dataRE["id_tra"]){
            $sql = "select id_trad from gl.gl_transaksidetil where tra_id = ".QuoteValue(DPE_CHAR,$dataRE["id_tra"]); 
        	$rs = $conn->Execute($sql);
        	$dataTransDet= $conn->Fetch($rs);
        }
        $dbTable = "gl.gl_transaksidetil";
        
        $dbField[0] = "id_trad";   // PK
        $dbField[1] = "tra_id";
        $dbField[2] = "prk_id";
        $dbField[3] = "ket_trad";
        $dbField[4] = "jumlah_trad";
        $dbField[5] = "dept_id";
    
        if($dataTransDet["id_trad"]) $idTrad = $dataTransDet["id_trad"];
        else {
            $idTrad = $conn->GetNewID("gl.gl_transaksidetil","id_trad",DB_SCHEMA_GL);
        }
        
        $dbValue[0] = QuoteValue(DPE_NUMERIC,$idTrad);
        $dbValue[1] = QuoteValue(DPE_NUMERIC,$idTra);
    	$dbValue[2] = QuoteValue(DPE_CHAR,$prk["id_prk"]);            
    	$dbValue[3] = QuoteValue(DPE_CHAR,$ketRE);
    	$dbValue[4] = QuoteValue(DPE_NUMERIC,$returnEarning);
    	$dbValue[5] = QuoteValue(DPE_CHAR,$id_dept);
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    
        if (!$dataTransDet["id_trad"]) {
            $dtmodel->Insert() or die("insert  error");	
        } else {
            $dtmodel->Update() or die("update  error");	
        }
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        unset($dataTransDet);
        unset($dataRE);
    }
}



function filter_mysqldate($day, $month, $year, $field ) {
   GLOBAL $dtaccess;
   $conn = $dtaccess;

    //if ($year>1900 && $year < 2500) {
    if (isset($year) && ($year > 1900) && ($year < 2500)) 
        $filtertgl_y = $conn->SQLDate('Y', $field)."='".$year."' ";
    if(isset($month) && ($month >= 1) && ($month <= 12)) 
        $filtertgl_m = $conn->SQLDate('m', $field)."='".$month."' ";
    if (isset($day) && ($day >= 1) && ($day <= 31))
        $filtertgl_d = $conn->SQLDate('d', $field)."='".$day."' ";
    $filtertgl = "";
    if($filtertgl_y||$filtertgl_m||$filtertgl_d){
        echo $filtertgl_y."<br>";
        echo $filtertgl_m."<br>";
        echo $filtertgl_d."<br>";
    }
    
    if (isset($filtertgl_y)) 
        $filtertgl .= $filtertgl_y;

    if (isset($filtertgl_m) && ($filtertgl != ""))
        $filtertgl .= " AND ".$filtertgl_m;
    elseif (isset($filtertgl_m) && ($filtertgl == ""))
        $filtertgl .= $filtertgl_m;

    if (isset($filtertgl_d) && ($filtertgl != ""))
        $filtertgl .= " AND ".$filtertgl_d;
    elseif (isset($filtertgl_d) && ($filtertgl == ""))
        $filtertgl .= $filtertgl_d;
    echo $filtertgl ;
    //}
    return $filtertgl;
}


function InsertGL($ketTra,$tglTra,$idDept,$idPrkD,$numD,$idPrkK,$numK,$namaPrd,$idJob=null)
{
	GLOBAL $dtaccess;

	// trus langsung insert d...
	$traId = $dtaccess->GetNewID("gl.gl_buffer_transaksi","id_tra",DB_SCHEMA_GL);

	$sql = sprintf("select max(ref_tra) as maks from gl.gl_buffer_transaksi where ref_tra <> -1 and ref_tra <> -2");
	$rs = $dtaccess->Execute($sql);
	$row = $dtaccess->Fetch($rs);
	$refTra = $row["maks"]+1;

	$dbTable = "gl.gl_buffer_transaksi";
	
	$dbField[0] = "id_tra";   // PK
	$dbField[1] = "ref_tra";
	$dbField[2] = "tanggal_tra";
	$dbField[3] = "ket_tra";
	$dbField[4] = "namauser";
	$dbField[5] = "real_time";
	$dbField[6] = "dept_id";

	$dbValue[0] = QuoteValue(DPE_NUMERIC,$traId);
	$dbValue[1] = QuoteValue(DPE_NUMERIC,$refTra);
	$dbValue[2] = QuoteValue(DPE_DATE,$tglTra);            
	$dbValue[3] = QuoteValue(DPE_CHAR,$ketTra);
	$dbValue[4] = QuoteValue(DPE_CHAR,"SYSTEM");
	$dbValue[5] = QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s"));
	$dbValue[6] = QuoteValue(DPE_CHAR,$idDept);

	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

	$dtmodel->Insert() or die("insert  error");	
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);
	unset($dbTable);
	
	// --- insert ke detilnya -0---
	$tradId = $dtaccess->GetNewID("gl.gl_buffer_transaksidetil","id_trad",DB_SCHEMA_GL);
	$dbTable = "gl.gl_buffer_transaksidetil";

	$dbField[0] = "id_trad";   // PK
	$dbField[1] = "tra_id";
	$dbField[2] = "prk_id";
	$dbField[3] = "ket_trad";
	$dbField[4] = "jumlah_trad";
	$dbField[5] = "dept_id";
	$dbField[6] = "job_id";

	$dbValue[0] = QuoteValue(DPE_NUMERIC,$tradId);
	$dbValue[1] = QuoteValue(DPE_NUMERIC,$traId);
	$dbValue[2] = QuoteValue(DPE_CHAR,$idPrkD);            
	$dbValue[3] = QuoteValue(DPE_CHAR,$ketTra);
	$dbValue[4] = QuoteValue(DPE_NUMERIC,$numD);
	$dbValue[5] = QuoteValue(DPE_CHAR,$idDept);
	if($idJob) $dbValue[6] = QuoteValue(DPE_CHAR,$idJob);
	else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");

	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

	$dtmodel->Insert() or die("insert  error");	
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);
	unset($dbTable);
	
	$tradId = $dtaccess->GetNewID("gl.gl_buffer_transaksidetil","id_trad",DB_SCHEMA_GL);
	$dbTable = "gl.gl_buffer_transaksidetil";

	$dbField[0] = "id_trad";   // PK
	$dbField[1] = "tra_id";
	$dbField[2] = "prk_id";
	$dbField[3] = "ket_trad";
	$dbField[4] = "jumlah_trad";
	$dbField[5] = "dept_id";
	$dbField[6] = "job_id";

	$dbValue[0] = QuoteValue(DPE_NUMERIC,$tradId);
	$dbValue[1] = QuoteValue(DPE_NUMERIC,$traId);
	$dbValue[2] = QuoteValue(DPE_CHAR,$idPrkK);            
	$dbValue[3] = QuoteValue(DPE_CHAR,$ketTra);
	$dbValue[4] = QuoteValue(DPE_NUMERIC,$numK);
	$dbValue[5] = QuoteValue(DPE_CHAR,$idDept);
	if($idJob) $dbValue[6] = QuoteValue(DPE_CHAR,$idJob);
	else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");

	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

	$dtmodel->Insert() or die("insert  error");	
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);
	// -- end insert detil			
     
   //posting langsung
   $sql = "select posting_gl(ARRAY[".$traId."],1,".QuoteValue(DPE_CHAR,$idDept).")";
			$dtaccess->Execute($sql) or die(errorPage());    


			// Operasi Untuk return Earning ada di sini.

			updateReturnEarning($namaPrd,$idDept);
			// update RE buat periode berikutnya
			$sql = "SELECT nama_prd from gl.gl_periode a where a.awal_prd > 
					( select awal_prd 
					FROM gl.gl_periode WHERE nama_prd = ".QuoteValue(DPE_CHAR,$namaPrd)." 
					) limit 1";
			$rs = $dtaccess->Execute($sql);
			$dataPeriodeLanjut = $dtaccess->Fetch($rs);

			updateReturnEarning($dataPeriodeLanjut["nama_prd"],$idDept);

	return $refTra;

}
?>
