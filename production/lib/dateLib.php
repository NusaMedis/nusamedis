<?php
$yearName = array("2006","2007","2008");

$monthName = array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September",
				"Oktober","Nopember","Desember");
				
$monthName2 = array("","Jan","Feb","Mar","Apr","Mei","Jun","Jul","Ags","Sep",
				"Oct","Nov","Des");

$monthDay = array("","31","28","31","30","31","30","31","31","30",
				"31","30","31");

$monthNameShort = array("","Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agt","Sep",
				"Okt","Nop","Des");

$dayName = array("Minggu","Senin","Selasa","Rabu","Kamis", "Jumat", "Sabtu","Minggu");

$monthRomawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");

$formatCal = "%d-%m-%Y";

$monthNameNew["01"]="Januari";
$monthNameNew["02"]="Februari";
$monthNameNew["03"]="Maret";
$monthNameNew["04"]="April";
$monthNameNew["05"]="Mei";
$monthNameNew["06"]="Juni";
$monthNameNew["07"]="Juli";
$monthNameNew["08"]="Agustus";
$monthNameNew["09"]="September";
$monthNameNew["10"]="Oktober";
$monthNameNew["11"]="Nopember";
$monthNameNew["12"]="Desember";


$monthNameTglNew["Januari"]="01";
$monthNameTglNew["Februari"]="02";
$monthNameTglNew["Maret"]="03";
$monthNameTglNew["April"]="04";
$monthNameTglNew["Mei"]="05";
$monthNameTglNew["Juni"]="06";
$monthNameTglNew["Juli"]="07";
$monthNameTglNew["Agustus"]="08";
$monthNameTglNew["September"]="09";
$monthNameTglNew["Oktober"]="10";
$monthNameTglNew["Nopember"]="11";
$monthNameTglNew["Desember"]="12";

$dayNameNew["01"]="Satu";
$dayNameNew["02"]="Dua";
$dayNameNew["03"]="Tiga";
$dayNameNew["04"]="Empat";
$dayNameNew["05"]="Lima";
$dayNameNew["06"]="Enam";
$dayNameNew["07"]="Tujuh";
$dayNameNew["08"]="Delapan";
$dayNameNew["09"]="Sembilan";
$dayNameNew["10"]="Sepuluh";
$dayNameNew["11"]="Sebelas";
$dayNameNew["12"]="Dua Belas";
$dayNameNew["13"]="Tiga Belas";
$dayNameNew["14"]="Empat Belas";
$dayNameNew["15"]="Lima Belas";
$dayNameNew["16"]="Enam Belas";
$dayNameNew["17"]="Tujuh Belas";
$dayNameNew["18"]="Delapan Belas";
$dayNameNew["19"]="Sembilan Belas";
$dayNameNew["20"]="Dua Puluh";
$dayNameNew["21"]="Dua Puluh Satu";
$dayNameNew["22"]="Dua Puluh Dua";
$dayNameNew["23"]="Dua Puluh Tiga";
$dayNameNew["24"]="Dua Puluh Empat";
$dayNameNew["25"]="Dua Puluh Lima";
$dayNameNew["26"]="Dua Puluh Enam";
$dayNameNew["27"]="Dua Puluh Tujuh";
$dayNameNew["28"]="Dua Puluh Delapan";
$dayNameNew["29"]="Dua Puluh Sembilan";
$dayNameNew["30"]="Tiga Puluh";
$dayNameNew["31"]="Tiga Puluh Satu";

function getdateToday() 
{
    $_today = getdate();
    return($_today["year"]."-".str_pad($_today["mon"], 2, "0", STR_PAD_LEFT)."-".str_pad($_today["mday"], 2, "0", STR_PAD_LEFT));
}

function getdateTodayReg() 
{
    $_today = getdate();
    return($_today["year"].str_pad($_today["mon"], 2, "0", STR_PAD_LEFT).str_pad($_today["mday"], 2, "0", STR_PAD_LEFT));
}


function getMonth($_next=0,$_type="text") {
    global $monthName;
    $_today = getdate();
    $_mon = $_today["mon"] + $_next;
    if ($_mon > 12) {
        $_mon -= 12;
    }
    if ($_type == "text")
        return $monthName[$_mon];
    else if ($_type == "value")
        return $_mon; 
}

function getYear($_next=0) {
    $_today = getdate(); 
    $_mon = $_today["mon"] + $_next;
 
    if ($_mon > 12) {
        return $_today["year"]+1;    
    } else {
        return $_today["year"];
    }    
}

/*
 * @param _date_ string format yyyy-mm-dd
 */
function format_date($_date_) {
    if ($_date_) {
        list ($_year_, $_month_, $_day_,) = explode ('-', $_date_);
        return $_day_."-".$_month_."-".$_year_;
    } else {
        return "";
    }
}


/*
 * @param _date_ string format yyyy-mm-dd
 * @return date long 7 juni 2006
 */
function format_date_long($_date_) {
    global $monthName;
    if ($_date_) {
        list ($_year_, $_month_, $_day_,) = explode('-', $_date_);
        return $_day_." ".$monthName[intval($_month_)]." ".$_year_;
    } else {
        return "";
    }
}

/*
 * @param _date_ string format yyyy-mm-dd
 */
function view_date($_date_){
    global $monthName;
    if($_date_){
        list ($_year_, $_month_, $_day_,) = explode ('-', $_date_);
        $tmpDate = $_day_." ".$monthName[intval($_month_)]." ".$_year_;
        return $tmpDate;
    }
}

/*
 * @param _date_ string format mm-dd-yyyy
 */
function date_db($_date_) {
    if ($_date_) {
        list ($_day_, $_month_, $_year_,) = explode ('-', $_date_);
        return $_year_."-".$_month_."-".$_day_;
    } else {
        return "";
    }
}

/*
 * @param date(yyyy-mm-dd)
 * @return bool check date is valid
 */
function check_date($_date_) {
    if ($_date_) {
        list($_year_,$_month_,$_day_) = explode('-',$_date_);
		if(!$_year_ || (strlen($_year_)!=4)) return false;
		if(!$_month_ || (strlen($_month_)>2)) return false;
		if(!$_day_ || (strlen($_day_)>2)) return false;

        return checkdate($_month_,$_day_,$_year_);     
    } else {
        return false;
    }
}

function checkDateRange($_begin, $_end) {
    if ($_begin && $_end) {
        list($_from["month"],$_from["day"],$_from["year"],) = explode('-',$_begin);
        list($_to["month"],$_to["day"],$_to["year"],) = explode('-',$_end);
        
        if (checkdate(settype($_from["month"],"int"), settype($_from["day"],"int"), settype($_from["year"],"int")) && 
            checkdate(settype($_to["month"],"int"), settype($_to["day"],"int"), settype($_to["year"],"int"))) {
            $_from_stamp = mktime(0,0,0,$_from["month"],$_from["day"],$_from["year"]);
            $_to_stamp = mktime(0,0,0,$_to["month"],$_to["day"],$_to["year"]);
            if ($_from_stamp < $_to_stamp) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function checkMasterDateRange($_mbegin,$_mend,$_begin,$_end) {
    list($_mfrom["month"],$_mfrom["day"],$_mfrom["year"],) = explode('-',$_mbegin);
    list($_mto["month"],$_mto["day"],$_mto["year"],) = explode('-',$_mend);
    list($_from["month"],$_from["day"],$_from["year"],) = explode('-',$_begin);
    list($_to["month"],$_to["day"],$_to["year"],) = explode('-',$_end);
    if (checkdate(settype($_mfrom["month"],"int"), settype($_mfrom["day"],"int"), settype($_mfrom["year"],"int")) && 
        checkdate(settype($_mto["month"],"int"), settype($_mto["day"],"int"), settype($_mto["year"],"int")) && 
        checkdate(settype($_from["month"],"int"), settype($_from["day"],"int"), settype($_from["year"],"int")) && 
        checkdate(settype($_to["month"],"int"), settype($_to["day"],"int"), settype($_to["year"],"int"))) {
        
        $_mfrom_stamp = mktime(0,0,0,$_mfrom["month"],$_mfrom["day"],$_mfrom["year"]);
        $_mto_stamp = mktime(0,0,0,$_mto["month"],$_mto["day"],$_mto["year"]);
        $_from_stamp = mktime(0,0,0,$_from["month"],$_from["day"],$_from["year"]);
        $_to_stamp = mktime(0,0,0,$_to["month"],$_to["day"],$_to["year"]);
        
        if (($_from_stamp >= $_mfrom_stamp) && ($_from_stamp < $_mto_stamp) 
            && ($_to_stamp > $_mfrom_stamp) && ($_to_stamp <= $_mto_stamp)) {
            return true;     
        } else {
            return false; 
        }                  
    } else {
        return false;
    }
}

// fungsi perbandingan waktu skr =>  input(dd,mm,yyy) < finput(dd,mm,yyy)
// usage IsDateLessThen(dd, mm, yyyy,dd, mm, yyyy)
function IsDateMoreThen($in_day,$in_month,$in_year,$f_day,$f_month,$f_year)
{
    $tanggal=date("U",mktime(0,0,0,$in_month,$in_day,$in_year));
    $future=date("U",mktime(0,0,0,$f_month,$f_day,$f_year));

    if ($future > $tanggal)
        return true;
    else
        return false;
}

function TimestampDiff($in_start, $in_end)
{
    $in_start = explode(" ",$in_start);
    $start_time = explode(":",$in_start[1]);
    $start_date = explode("-",$in_start[0]);


    $in_end = explode(" ",$in_end);
    $end_time = explode(":",$in_end[1]);
    $end_date = explode("-",$in_end[0]);

    $tanggal=date("U",mktime(intval($start_time[0]),intval($start_time[1]),intval($start_time[2]),intval($start_date[1]),intval($start_date[2]),intval($start_date[0])));
    $future=date("U",mktime(intval($end_time[0]),intval($end_time[1]),intval($end_time[2]),intval($end_date[1]),intval($end_date[2]),intval($end_date[0])));

    return ($future - $tanggal);
}

// -- param date yyyy-mm-dd
function DateDiff($start_date, $end_date)
{
   return floor((strtotime($end_date) - strtotime($start_date))/86400);
}

// -- param date yyyy-mm-dd
function HitungUmur($tgllahir)
{
   return floor((strtotime(date("Y-m-d")) - strtotime($tgllahir))/86400/365);
}

// -- param date yyyy-mm-dd
function DateDiffYear($start_date, $end_date)
{
   return floor((strtotime($end_date) - strtotime($start_date))/86400/365);
}


function DateAdd($in_tgl,$jumlah)
{
	$tanggal = explode("-",$in_tgl);
	return date("Y-m-d",mktime(0,0,0,$tanggal[1],$tanggal[2]+$jumlah,$tanggal[0]));
}

// -- param date yyyy-mm-dd
function HitungBulan($start_date, $end_date)
{
    $start = explode("-",$start_date);
    $end = explode("-",$end_date);
	return (($end[0]-$start[0])+($end[1]-$start[1])+1);
}

function HitungHari($start_date, $end_date)
{
    $start = explode("-",$start_date);
    $end = explode("-",$end_date);
	return (($end[0]-$start[0])+($end[2]-$start[2])+1);
}

// -- param time HH:ii:ss
function TimeDiff($start_time, $end_time)
{
	$start = explode(":",$start_time);
	$end = explode(":",$end_time);

	$timeStart = mktime($start[0],$start[1],$start[2]);
	$timeEnd = mktime($end[0],$end[1],$end[2]);
	return ($timeEnd-$timeStart);
}

function GetDay($in_tgl)
{
	global $dayName;
	$tanggal = explode("-",$in_tgl);
	$hari =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $hari;
}

function GetYearName($in_tgl)
{
	global $dayName;
	$tanggal = explode("-",$in_tgl);
	return $tanggal[2];
}

function GetMonthName($in_tgl)
{
	global $monthNameNew;
	$tanggal = explode("-",$in_tgl);
  //return $tanggal[1];
  //$bulan =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $monthNameNew[$tanggal[1]];
}

function GetDayName($in_tgl)
{
	global $dayName;
	$tanggal = explode("-",$in_tgl);
	$hari =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $dayName[$hari];
}

function GetDayNameNew($in_tgl)
{
	global $dayNameNew;
	$tanggal = explode("-",$in_tgl);
  //return $tanggal[1];
  //$bulan =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $dayNameNew[$tanggal[0]];
}

function GetSundayBefore( $year, $month, $day ) {
     $weekday = date ( "w", mktime ( 3, 0, 0, $month, $day, $year ) );
     $newdate = mktime ( 3, 0, 0, $month, $day - $weekday, $year );
     return $newdate;
}

/*
 * @param _date_ string format mm-dd-yyyy
 */
function timestamp_db($in_timestamp) {
    if ($in_timestamp) {
        return date_db(substr($in_timestamp,0,10))." ".substr($in_timestamp,11,8);
    } else {
        return "";
    }
}

// --param yyyy-mm-dd HH:mm:ss
function FormatTimestamp($in_timestamp) {
    return format_date(substr($in_timestamp,0,10))." ".substr($in_timestamp,11,8);
}


function FormatDateFromTimestamp($in_timestamp) {
    return format_date(substr($in_timestamp,0,10));
}

// --param second
// --- return HH:mm:ss
function FormatTime($in_second) {

    $jam = floor($in_second/3600);
    $menit = floor(($in_second % 3600)/60);
    $detik = (($in_second % 3600) % 60);
    return str_pad($jam,2,"0",STR_PAD_LEFT).":".str_pad($menit,2,"0",STR_PAD_LEFT).":".str_pad($detik,2,"0",STR_PAD_LEFT);
}

function FormatPukul($time) {
	$a = explode(':',$time);
	return $a[0].':'.$a[1];
}

function real_date($tanggalpanjang) {
    global $monthNameTglNew;
	$tanggalreal = explode(" ",$tanggalpanjang);
  //return $tanggalreal[1];
  $bulanAngka = $monthNameTglNew[$tanggalreal[1]];
  $tanggalAngka = $tanggalreal[0]."-".$bulanAngka."-".$tanggalreal[2];
	return $tanggalAngka;

}

function durasi( $prev , $start )
{
$prev = new DateTime($prev);
$start = new DateTime($start);
$since_start = $start->diff($prev);
$since_start->days.' days total<br>';
$tahun = $since_start->y.' years<br>';
$bulan = $since_start->m.' months<br>';
$hari = $since_start->d.' days<br>';
$jam = $since_start->h.' Jam ';
$menit = $since_start->i.' Mnt ';
$detik = $since_start->s.' Dtk ';

return $jam.$menit.$detik; 

}

function durasiDetik( $prev , $start )
{
$prev = new DateTime($prev);
$start = new DateTime($start);
$since_start = $start->diff($prev);
$since_start->days.' days total<br>';
$tahun = $since_start->y.' years<br>';
$bulan = $since_start->m.' months<br>';
$hari = $since_start->d.' days<br>';
$jam = $since_start->h.' Jam ';
$menit = $since_start->i.' Mnt ';
//$detik = $since_start->s.' Dtk ';

$detik = $since_start->days * 24 * 60;
$detik += $since_start->h * 60;
$detik += $since_start->i * 60;
$detik += $since_start->s;

return $detik; 

}

function nice_date($bad_date, $format)
{
    if (!empty($bad_date)){
        $b = date($bad_date);       
        return date($format, strtotime($b));
    }
}

?>
