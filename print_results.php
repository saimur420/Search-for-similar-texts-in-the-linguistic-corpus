<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<style>
/* внешние границы таблицы серого цвета толщиной 1px */
table {
   border: 1px solid grey;
}
/* границы ячеек первого ряда таблицы */
th {
   border: 1px solid grey;
}
/* границы ячеек тела таблицы */
td {
   border: 1px solid grey;
}
</style>
</head>
<body>	
<?php

include ("connection.php"); # подключение к бд
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '86400');
#ini_set('max_execution_time', "15");

$fp = fopen('file5.csv', 'r'); # Файл с результатами

echo '<table>
		<tr><th>id текста 1</th><th>id текста 2</th><th>Word N-Gram Containment Measure 1 и 2</th><th>Word N-Gram Containment Measure 2 и 1</th><th>Количество совпавших n-gram</th><th>N-Gram resemblance</th></tr>';

while (($data = fgetcsv($fp, null, ",")) !== FALSE) {
	$cutoff = 0.01;
	if ($data[2] > $cutoff or $data[3] > $cutoff ) {
	#if ($data[5] > $cutoff) {
		$datasort[] = array('id1' => $data[0], 'id2' => $data[1],'WNG12' => $data[2],'WNG21' => $data[3],'CNG' => $data[4],'WNGR' => $data[5]);
		#echo '<tr><td>'.$data[0].'</td><td>'.$data[1].'</td><td>'.$data[2].'</td><td>'.$data[3].'</td><td>'.$data[4].'</td><td>'.$data[5].'</td></tr>';		}
	}
}
foreach ($datasort as $key => $row) {
    $id1[$key]  = $row['id1'];
    $id2[$key] = $row['id2'];
	$WNG12[$key]  = $row['WNG12'];
    $WNG21[$key] = $row['WNG21'];
	$CNG[$key]  = $row['CNG'];
    $WNGR[$key] = $row['WNGR'];
}
array_multisort($WNG12, SORT_DESC, $WNG21, SORT_DESC, $CNG , SORT_DESC, $id2, SORT_DESC, $id1, SORT_DESC, $WNGR, SORT_DESC, $datasort);
#array_multisort($WNGR, SORT_DESC, $WNG12, SORT_DESC, $WNG21, SORT_DESC, $CNG, SORT_DESC, $id1, SORT_DESC, $id2, SORT_DESC, $datasort);
$count = 1;
foreach ($datasort as $key => $row) {
	if ($count > 100) {
		break;
	}
	++$count;
	echo '<tr><td>'.$row['id1'].'</td><td>'.$row['id2'].'</td><td>'.$row['WNG12'].'</td><td>'.$row['WNG21'].'</td><td>'.$row['CNG'].'</td><td>'.$row['WNGR'].'</td></tr>';
	
}
echo '</table>';
?>
</body>
</html>