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
#ini_set('max_execution_time', "10");
#include 'C:/Users/Admin/vendor/autoload.php';

function array_intersect_fixed($array1, $array2) {
    $result = array();
    foreach ($array1 as $val) {
      if (($key = array_search($val, $array2, TRUE))!==false) {
         $result[] = $val;
         unset($array2[$key]);
      }
    }
    return $result;
} 

$fp = fopen('file7.csv', 'w+'); # csv файл для сохранения результатов

echo '<table>
		<tr><th>id текста 1</th><th>id текста 2</th><th>Word N-Gram Containment Measure 1 и 2</th><th>Word N-Gram Containment Measure 2 и 1</th><th>Количество совпавших n-gram</th><th>N-Gram resemblance</th><th>simhash</th></tr>';

$stmt = mysqli_query($db, "SELECT id FROM `texts`");
$textsid = [];
while ($row = mysqli_fetch_array($stmt)) {
$textsid[] =  $row['id'];  
}	
print_r($textsid);
foreach ($textsid as $key => $value) {
    $text1 = mysqli_fetch_array(mysqli_query($db, "SELECT text FROM `texts` WHERE id = '{$value}'"))['text'];
	$text1id = $value;
	$b = $key+1;
	while (1){
		if ($b > count($textsid)-1) {
			break;
		}
		$text2 = mysqli_fetch_array(mysqli_query($db, "SELECT text FROM `texts` WHERE id = '{$textsid[$b]}'"))['text'];
		if ($text2 == false) {
			break;
		}
		$text2id = $textsid[$b];
		$bigrams1 = ngrams(tokenize(mb_strtolower($text1))); 
		$bigrams2 = ngrams(tokenize(mb_strtolower($text2)));
		#$bigrams1 = ngrams(tokenize($text1)); 
		#$bigrams2 = ngrams(tokenize($text2));
		#$result = array_intersect($bigrams1, $bigrams2);
		$result = array_intersect_fixed($bigrams1, $bigrams2); # доработанная функция array_intersect. Был неверный результат при наличии дубликатов	
		$c_result = count($result);
		$WNGC12 = $c_result/count($bigrams1);
		$WNGC21 = $c_result/count($bigrams2);
		$WNGR = $c_result/(count($bigrams1)+count($bigrams2)-$c_result);
		
		$fields = array($text1id, $text2id, $WNGC12, $WNGC21, $c_result, $WNGR, $distsim);
		fputcsv($fp, $fields);
		$cutoff = 0.6;
		if ($WNGC12 > $cutoff or $WNGC21 > $cutoff ) {
			echo '<tr><td>'.$text1id.'</td><td>'.$text2id.'</td><td>'.$WNGC12.'</td><td>'.$WNGC21.'</td><td>'.$c_result.'</td><td>'.$WNGR.'</td><td>'.$distsim.'</td></tr>';	
		}
		++$b;
	}
}
echo '</table>';
?>
</body>
</html>