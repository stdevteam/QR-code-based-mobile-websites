<?php
$fileName = "report-".date("Ymd").".xls";
header ("Expires: Mon, 28 Oct 2008 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/octet-stream");
header ("Content-Disposition: attachment; filename=".$fileName );
header ("Content-Description: Generated Report" );
?>
<?php echo $content_for_layout; ?> 
