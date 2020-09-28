<?php

//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

$bd = bd_connect();

begin_html('Géo\'doc | A propos', '../css/geodoc.css', '../', 'apropos');
display_header();

echo "<h4>Cette application a été développée par Marie Smolinski, Corentin Borde, et Guillaume Ségard dans le cadre d'un projet de L3 informatique tutoré par M. Hassan Mountassir.</h4>";

display_footer();

end_html();

mysqli_close($bd);
ob_end_flush();

?>
