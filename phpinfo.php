<?php
ob_start();
phpinfo();
$page_data = ob_get_contents();
ob_get_clean();


$page_script= <<<EOT

EOT;
?>

<?php require_once('includes/template.php'); ?>
