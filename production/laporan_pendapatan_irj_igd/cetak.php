<?php
require_once("../penghubung.inc.php");
require_once("../" . $LIB . "dompdf/autoload.inc.php");
// require_once($LIB . "login.php");
// require_once($LIB . "encrypt.php");
// require_once($LIB . "datamodel.php");
// require_once($LIB . "currency.php");
// require_once($LIB . "dateLib.php");
// require_once($LIB . "tampilan.php");
// require_once($LIB . "tree.php");
// require_once($LIB . "expAJAX.php");
// setting dompdf 

use Dompdf\Dompdf;

$dompdf = new Dompdf();

session_start()

?>

<?php
$html = $_SESSION["html"];
unset($_SESSION['html']);

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();
// Melakukan output file Pdf
$dompdf->stream('laporan .pdf', array("Attachment" => 0));
?>