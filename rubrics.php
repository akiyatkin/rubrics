<?php

Path::req('-rubrics/rubrics.inc.php');
$type = Path::toutf($_GET['type']);
$conf = Config::get();
$ans = array();
/*
	type два смысла.. type blog - имя рубрики и type list то как отображается всё
*/
if (empty($conf['rubrics']['list'][$type])) {
	return Ans::err($ans, 'Undefined type '.$type);
}

$dir = '~'.$type.'/';
if ($conf['rubrics']['list'][$type]['type'] == 'info') {
	$exts = array('docx','tpl','mht','html','php');
} else {
	$exts = array();
}
if (!empty($_GET['id'])) {
	//Загрузка файла
	$id = Path::toutf($_GET['id']);

	$res = rub_search($dir, $id, $exts);
	if (isset($_GET['image'])) {
		if ($res['images']) {
			$data = file_get_contents(Path::tofs($res['images'][0]['src']));
			echo $data;
		} else {
			//@header('HTTP/1.1 404 Not Found');
		}

		return;
	} elseif (isset($_GET['show'])) {
		$conf = Config::get();
		if (!$res) {
			header("HTTP/1.0 404 Not Found");
			return;
		} else {
			$conf = Config::get();
			$src = $dir.$res['file'];
			$text=rub_article($src);
			echo $text;
			return;
		}

	} elseif (isset($_GET['load'])) {
		$conf = Config::get();

		if (!$res) {
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
			@header('location: '.infra_view_getPath().'?'.$type.'/'.$id);//Просто редирект на страницу со списокм всех файлов
		} else {
			@header('location: '.infra_view_getPath().'?-autoedit/download.php?'.$dir.$res['file']);
		}
		exit;
	} else {
		return Ans::err($res, 'id что?');
	}
} elseif (isset($_GET['list'])) {
	if (isset($_GET['lim'])) {
		$lim = $_GET['lim'];
	} else {
		$lim='0,100';
	}

	$p = explode(',', $lim);
	if(sizeof($p)!=2){
		return Ans::err($ans, 'Is wrong paramter lim');
	}
	$start = (int)$p[0];
	$count = (int)$p[1];


	$ar = rub_list($dir, $start, $count, $exts);
	$ar = array_values($ar);
	if (!empty($_GET['chunk'])) {
		$chunk = (int) $_GET['chunk'];
		if (!$chunk) $chunk = 1;
		$ar = array_chunk($ar, $chunk);
	}
	$ans['list'] = $ar;

	return Ans::ret($ans);
} else {
	return Ans::err($ans, 'Недостаточно параметров');
}
