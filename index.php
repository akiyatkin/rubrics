<?php
use infrajs\path\Path;
use infrajs\config\Config;
use infrajs\ans\Ans;
use infrajs\view\View;
use infrajs\router\Router;
use infrajs\rubrics\Rubrics;


if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}

$ans = array();

$src = Ans::GET('src');
if ($src) {
	if (!Path::isNest('~', $src)) return Ans::err($ans, 'Передан некорректный или небезопасный путь');
	$id = Ans::GET('id');
	$src = Rubrics::find($src, $id);
	$text = Rubrics::article($src);
	return Ans::html($text);
}





$conf = Config::get('rubrics');

$type = Path::toutf($_GET['type']);
/*
	type два смысла.. type blog - имя рубрики и type list то как отображается всё
*/
if (empty($conf['list'][$type])) {
	return Ans::err($ans, 'Undefined type '.$type);
}

$dir = '~'.$type.'/';
if (in_array($conf['list'][$type]['type'], array('list','info'))) {
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
		if (!$res) {
			header("HTTP/1.0 404 Not Found");
			return;
		} else {
			$src = $dir.$res['file'];
			$text=rub_article($src);
			echo $text;
			return;
		}
	} elseif (isset($_GET['gallery'])) {
		
		$src = Rubrics::find($dir,$id);
		$info = Rubrics::info($src);
		$ans['info'] = $info;
		return Ans::ret($ans);
	} elseif (isset($_GET['load'])) {

		if (!$res) {
			//@header("Status: 404 Not Found");
			//@header("HTTP/1.0 404 Not Found");
			@header('location: '.View::getPath().'?'.$type.'/'.$id);//Просто редирект на страницу со списокм всех файлов
		} else {
			//echo View::getPath().$dir.$res['file'];
			//exit;
			@header('location: '.View::getPath().$dir.$res['file']);
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
