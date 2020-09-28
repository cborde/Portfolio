<?php
$data = $_POST['id'];

require_once './geodoc_lib.php';
require_once './general_lib.php';
$bd = bd_connect();

$res = get_first_dispo($data, $bd);

if ($res == null){
    $res = '';
}

echo json_encode($res);

mysqli_close($bd);
exit(0);
?>
