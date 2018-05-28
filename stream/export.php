<?php
/**
 * Html2Pdf Library - example
 *
 * HTML => PDF converter
 * distributed under the OSL-3.0 License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2017 Laurent MINGUET
 */
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__.'/html2pdf/vendor/autoload.php');

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

try {
    ob_start();
    include dirname(__FILE__).'/cron.php';
    $content = ob_get_clean();

    $html2pdf = new Html2Pdf('P', 'A4', 'en');
    //$html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($content);
    $html2pdf->output(__ROOT__.'/reports/product.pdf','F');

} 
catch (Html2PdfException $e) {
    $html2pdf->clean();

    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}




// ob_start();
// include 'cron.php';
// $content = ob_get_clean();

// $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en');
// $html2pdf->writeHTML('<h1>HelloWorld</h1>This is my first page');
// ob_end_clean();
// $html2pdf->output();

//$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en');
//$html2pdf->pdf->SetDisplayMode('fullpage');
//$html2pdf->writeHTML($content);
//ob_end_clean();
//$html2pdf->output('my_doc.pdf');

?>