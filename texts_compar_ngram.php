<?php
include("connection.php"); # подключение к бд
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '86400');
include 'C:/Users/Admin/vendor/autoload.php'; # подкючение сторонней библиотеки  yooper/php-text-analysis

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

function compare_texts($text1, $text2) { #Сравнить два текста между собой. Выводит массив из метрик.
	$bigrams1 = ngrams(tokenize(mb_strtolower($text1))); 
	$bigrams2 = ngrams(tokenize(mb_strtolower($text2)));
	$result = array_intersect_fixed($bigrams1, $bigrams2); # доработанная функция array_intersect. Был неверный результат при наличии дубликатов	
	$c_result = count($result);
	$WNGC12 = $c_result/count($bigrams1); # подсчёт метрики ngram containment текста 1 в тексте 2
	$WNGC21 = $c_result/count($bigrams2); # подсчёт метрики ngram containment текста 2 в тексте 1
	$WNGR = $c_result/(count($bigrams1)+count($bigrams2)-$c_result); # подсчёт метрики ngram resemblance
	$fields = array($WNGC12, $WNGC21, $c_result, $WNGR);
	return $fields;
}

function compare_all_texts($db) { #Сравнивнить все тексты между собой в БД.
	$fp = fopen('compare_texts.csv', 'w+'); # csv файл для сохранения результатов
	$stmt = mysqli_query($db, "SELECT id FROM `texts`");
	$textsid = [];
	while ($row = mysqli_fetch_array($stmt)) {
		$textsid[] =  $row['id'];  
	}
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
			$results = compare_texts($text1, $text2);
			$fields = array_merge(array($text1id, $text2id), $results);
			fputcsv($fp, $fields);
			++$b;
		}
	}
	fclose($fp);
}

compare_all_texts($db);

?>