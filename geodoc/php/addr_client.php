<?php
$data = $_POST['pers'];

require_once './general_lib.php';
$bd = bd_connect();

$num = substr($data, -1, 1);
$sql = 'SELECT locNumero, locRue,locCP, locVille FROM localite, client WHERE cliID = '.$num.' AND cliLocID = localiteID;';

$res = mysqli_query($bd, $sql);

$coord = array();

$count = 0;

while ($t = mysqli_fetch_assoc($res)) {

	$coord[$count] = array(
							"num" => strip_tags($t['locNumero']),
							"rue" => strip_tags($t['locRue']),
                            "cp" => strip_tags($t['locCP']),
                            "ville" => strip_tags($t['locVille'])
                        );

	$count++;

}

echo json_encode($coord);

mysqli_close($bd);
exit(0);
?>
