<?php

infra_require('*rubrics/rubrics.inc.php');
$type = infra_toutf($_GET['type']);
$conf = infra_config();
$ans = array();
/*
	type два смысла.. type blog - имя рубрики и type list то как отображается всё
*/
if (empty($conf['rubrics']['list'][$type])) {
	return infra_err($ans, 'Undefined type '.$type);
}
$dirs = infra_dirs();
$dir = '*'.$type.'/';
if ($conf['rubrics']['list'][$type]['type'] == 'info') {
	$exts = array('docx','tpl','mht','html','php');
} else {
	$exts = array();
}
if (!empty($_GET['id'])) {
	//Загрузка файла
	$id = infra_toutf($_GET['id']);

	$res = rub_search($dir, $id, $exts);
	if (isset($_GET['image'])) {
		if ($res['images']) {
			$data = file_get_contents(infra_tofs($res['images'][0]['src']));
			echo $data;
		} else {
			//@header('HTTP/1.1 404 Not Found');
		}

		return;
	} elseif (isset($_GET['show'])) {
		$conf = infra_config();
		if (!$res) {
			header("HTTP/1.0 404 Not Found");
			return;
		} else {
			$conf = infra_config();
			$src = $dir.$res['file'];
			$text=rub_article($src);
			echo $text;
			return;
		}

	} elseif (isset($_GET['load'])) {
		$conf = infra_config();

		if (!$res) {
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
			@header('location: '.infra_view_getPath().'?'.$type.'/'.$id);//Просто редирект на страницу со списокм всех файлов
		} else {
			@header('location: '.infra_view_getPath().'?*autoedit/download.php?'.$dir.$res['file']);
		}
		exit;
	} else {
		return infra_err($res, 'id что?');
	}
} elseif (isset($_GET['list'])) {
	if (isset($_GET['lim'])) {
		$lim = $_GET['lim'];
	} else {
		$lim='0,100';
	}

	$p = explode(',', $lim);
	if(sizeof($p)!=2){
		return infra_err($ans, 'Is wrong paramter lim');
	}
	$start = (int)$p[0];
	$count = (int)$p[1];


	$ar = rub_list($dir, $start, $count, $exts);
	$ar = array_values($ar);
	$ans['list'] = $ar;

	return infra_ret($ans);
} else {
	return infra_err($ans, 'Недостаточно параметров');
}
