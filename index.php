<?php
use infrajs\path\Path;
use infrajs\config\Config;
use infrajs\ans\Ans;
use infrajs\view\View;
use infrajs\load\Load;
use infrajs\router\Router;
use infrajs\rubrics\Rubrics;


$ans = array();

$src = Ans::GET('src');
$lang = Ans::GET('lang');

if ($src) {
	if (!Path::isNest('~', $src)) return Ans::err($ans, 'Передан некорректный или небезопасный путь');
	$src = Path::theme($src);
	$id = Ans::GET('id');
	$ext = Ans::GET('ext','string','article');
	
	

	if (isset($_GET['gallery'])) {
		$src = Rubrics::find($src, $id);
		$info = Rubrics::info($src);
		$ans['info'] = $info;
		return Ans::ret($ans);
	} else if (isset($_GET['find'])) {
		$src = Rubrics::find($src, $id, $ext);
		if (!$src) return Ans::err($ans,'Файл не найден');
		$ans['src'] = $src;
		return Ans::ret($ans);
	} else if (isset($_GET['load'])) { 
		if ($id) {
			$src = Rubrics::find($src, $id, false);
		} else {
			$src = Path::theme($src);
		}
		if (!$src) {
			@header('HTTP/1.1 404 Not Found');
			exit;
		} else {
			Rubrics::file_force_download($src);
			//$src = View::getPath().$dir.$res['file'];
			//@header('location: '.$src);
		}
	} else {
		$src = Rubrics::find($src, $id);
		$text = Rubrics::article($src);	
		return Ans::html($text);
	}
}





$conf = Config::get('rubrics');

$type = Ans::GET('type');
/*
	type два смысла.. type blog - имя рубрики и type list то как отображается всё
*/
if (empty($conf['list'][$type])) {
	return Ans::err($ans, 'Undefined type '.$type);
}


$ans['type'] = $conf['list'][$type];
$dir = rub_getdir($type);

if (in_array($conf['list'][$type]['type'], array('list','info'))) {
	$exts = array('docx','tpl','mht','html','php');
} else {
	$exts = array();
}
if (!empty($_GET['id'])) {
	//Загрузка файла
	$id = Path::toutf($_GET['id']);

	$res = rub_search($dir, $id, $exts, $lang);

	
	if ($res && $id != $res['name']) { //Обращаться к страницам можно по id
		$r = Load::isphp();
		if ($r) {
			if ($type == $conf['main']) {
				header('Location: /'.$res['name']);
				exit;
			} else {
				//header('Location: /'.$type.'/'.$res['name']);
			}
					
		}
	}
	

	if (isset($_GET['image'])) {
		if ($res['images']) {
			$data = file_get_contents(Path::tofs($res['images'][0]['src']));
			if (Ans::isReturn()) return $data;
			echo $data;
		} else {
			//@header('HTTP/1.1 404 Not Found');
		}

		return;
	} elseif (isset($_GET['show'])) {
		if (!$res) {
			echo '<h1>'.$id.'</h1>';
			if (!$lang || $lang == 'ru') {
				echo '<p>Ошибка 404. Статья на сайте не найдена.</p>';
			} else {
				echo '<p>Error 404. The article was not found on the site.</p>';
			}			
			header("HTTP/1.0 404 Not Found");
			//exit;
		} else {
			$src = $dir.$res['file'];
			$text = Rubrics::article($src);
			
			$removeh1 = isset($_GET['removeh1']);
			$headingstodiv = isset($_GET['headingstodiv']);

			if ($removeh1) {
				$text = preg_replace('/<h1[^>]*?>.*?<\/h1>/si', '', $text);
				
			}
			if ($headingstodiv) {
				$text = preg_replace('/<(h\d)[^>]*?>(.*?)<\/h\d>/si', '<div class="${1}">${2}</div>', $text);
			}
			if (Ans::isReturn()) return $text;
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
			
			$src = Path::theme($dir.$res['file']);
			Rubrics::file_force_download($src);
			//$src = View::getPath().$dir.$res['file'];
			//@header('location: '.$src);
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


	$ar = rub_list($dir, $start, $count, $exts, $lang);
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
