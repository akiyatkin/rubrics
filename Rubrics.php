<?php

namespace infrajs\rubrics;

use infrajs\load\Load;
use infrajs\path\Path;
use infrajs\doc\Docx;
use infrajs\doc\Mht;
use akiyatkin\boo\Cache;
use infrajs\cache\Cache as OldCache;
use infrajs\template\Template;

Path::req('-rubrics/rubrics.inc.php');


class Rubrics
{
	public static $conf = array(
		"404" => "Статья на сайте не найдена.",
		"main" => "pages",
		"link" => "Перейти на главную страницу",
		"list" => array(
			"pages" => array(
				"title" => "Информация",
				"type" => "info"
			),
			"blog" => array(
				"title" => "Блог",
				"type" => "list"
			),
			"files" => array(
				"title" => "Файлы",
				"type" => "files"
			),
			"events" => array(
				"title" => "События",
				"type" => "list"
			)
		)
	);
	public static function search($type, $id)
	{
		if (!isset(Rubrics::$conf['list'][$type])) return false;
		$exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		$dir = rub_getdir($type);
		$files = rub_list($dir, 0, 0, $exts);


		if (isset($files[$id])) {
			$files[$id]['idfinded'] = true; //Найдено по id
			return $files[$id];
		}
		foreach ($files as $d) {
			if (mb_strtolower($d['name']) == mb_strtolower($id)) {
				return $d;
			}
		}
		return array();
	}
	public static function list($dir, $what = 'articles')
	{
		if (is_array($what)) $exts = $what;
		if ($what == 'images') $exts = array('jpg', 'gif', 'png', 'jpeg');
		if ($what == 'articles') $exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		if ($what == 'image') $exts = array('jpg', 'gif', 'png', 'jpeg');
		if ($what == 'article') $exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		if ($what == 'html') $exts = array('tpl', 'html');
		if ($what == 'doc') $exts = array('docx', 'mht');
		if ($what == 'dir') $exts = array();

		$files = rub_list($dir, 0, 0, $exts);
		return $files;
	}
	public static function find($dir, $id, $what = 'articles')
	{
		if (is_array($what)) $exts = $what;
		else if ($what == 'images') $exts = array('jpg', 'gif', 'png', 'jpeg');
		else if ($what == 'articles') $exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		else if ($what == 'image') $exts = array('jpg', 'gif', 'png', 'jpeg');
		else if ($what == 'article') $exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		else if ($what == 'json') $exts = array('json');
		else if ($what == 'html') $exts = array('tpl', 'html');
		else if ($what == 'doc') $exts = array('docx', 'mht');
		else if ($what == 'dir') $exts = array();
		else $exts = false;

		$files = rub_list($dir, 0, 0, $exts);
		if (isset($files[$id])) {
			$files[$id]['idfinded'] = true; //Найдено по id
			$src = $dir . $files[$id]['file'];
			if ($what == 'dir') return $src . '/';
			else return $src;
		}
		foreach ($files as $d) {
			if (mb_strtolower($d['name']) == mb_strtolower($id)) {
				$src = $dir . $d['file'];
				if ($what == 'dir') return $src . '/';
				else return $src;
			}
		}
	}
	public static function findArticals($dir, $id)
	{
		return Rubrics::find($dir, $id, 'articles');
	}
	public static function findImages($dir, $id)
	{
		return Rubrics::find($dir, $id, 'images');
	}
	public static function info($src)
	{
		if (!Path::theme($src)) return array();
		$rr = Load::srcInfo($src);

		$ext = $rr['ext'];
		$size = filesize(Path::theme($src));

		if (in_array($ext, array('mht', 'tpl', 'html', 'txt', 'php'))) {
			$rr = Mht::preview($src);
		} elseif (in_array($ext, array('docx'))) {
			$rr = Docx::preview($src);
		}
		$rr['size'] = round($size / 1000000, 2); //Mb
		if (!empty($rr['links'])) {
			$links = $rr['links'];
			unset($rr['links']);

			foreach ($links as $v) {
				$r = preg_match('/http.*youtube\.com.*watch.*=([\w\-]+).*/', $v['href'], $match);
				if (!$r) $r2 = preg_match('/http.{0,1}:\/\/youtu\.be\/([\w\-]+)/', $v['href'], $match);
				if ($r) {
					if (empty($rr['video'])) $rr['video'] = array();
					$v['id'] = $match[1];
					$rr['video'][] = $v;
				} elseif ($r2) {
					if (empty($rr['video'])) $rr['video'] = array();
					$v['id'] = $match[1];
					$rr['video'][] = $v;
				} else {
					if (empty($rr['links'])) $rr['links'] = array();
					$rr['links'][] = $v;
				}
			}
		}
			
		if (!empty($rr['name'])) {
			$slide = Rubrics::find($rr['folder'], $rr['name'], 'images');
			if ($slide) $rr['slide'] = $slide;

			$json = Rubrics::find($rr['folder'], $rr['name'], 'json');
			if ($json) $rr['json'] = Load::loadJSON($json);

			$dir = Path::theme($rr['folder'] . $rr['name'] . '/');
			if ($dir) {
				$list = array();
				array_map(function ($file) use (&$list, $src) {
					if ($file[0] == '.') return;
					//if (!is_file($dir.$file)) return;
					$fd = Load::nameinfo(Path::toutf($file));
					if (!in_array($fd['ext'], ['jpeg', 'jpg', 'png'])) return;
					$list[] = $fd;
				}, scandir($dir));
				Load::sort($list, 'ascending');

				//foreach ($list as $k=>$fd) {
				//	$list[$k] = $fd['file'];
				//}
				$rr['gallerydir'] = $rr['folder'] . $rr['name'] . '/';
				$rr['gallery'] = $list;
			}
		}
		return $rr;
	}
	public static function file_force_download($file) {
		if (file_exists($file)) {
			// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
			// если этого не сделать файл будет читаться в память полностью!
			if (ob_get_level()) {
			ob_end_clean();
			}
			// заставляем браузер показать окно сохранения файла
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			// читаем файл и отправляем его пользователю
			readfile($file);
			exit;
		}
	}
	public static function parse($html, $soft = false)
	{

		if (!$soft) {
			$html = preg_replace('/<table>/', '<table class="table table-sm table-striped">', $html);
		}
		if (!$soft) {
			$html = preg_replace('/<img/', '<img alt="" class="img-thumbnail"', $html);
		}

		$html = preg_replace("/<\/a>/", "</a>\n", $html);

		//youtube
		$ptube = rub_ptube();
		$pattern = '/<a[^>]*>' . $ptube . '(<\/a>)/i';

		$youtpl = <<<END
		<center><div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" width="640" height="480" src="https://www.youtube.com/embed/{1}" frameborder="0" allowfullscreen></iframe></div></center>
END;

		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$files[] = $match[1];
				$youhtml = Template::parse(array($youtpl), $match);
				$html = preg_replace($pattern, $youhtml, $html, 1);
			}
		} while (sizeof($match) > 1);

		//youtube2
		$ptube = rub_ptube2();
		$pattern = '/<a[^>]*>' . $ptube . '(<\/a>)/i';
		$youtpl = <<<END
		<center><div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" width="640" height="480" src="https://www.youtube.com/embed/{1}" frameborder="0" allowfullscreen></iframe></div></center>
END;
		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$a = $match[1];
				$files[] = $match[2];
				$youhtml = Template::parse(array($youtpl), $match);
				$html = preg_replace($pattern, $youhtml, $html, 1);
			}
		} while (sizeof($match) > 1);

		//files
		//setlocale(LC_ALL, 'ru_RU.UTF-8');
		$files = array();


		$pattern = '/(<a.*href="\/\-rubrics\/?[^"]*id=(\w+)&type=(\w+)&[^"]*load".*>)([^~<]*?)(<\/a>)/u';
		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$a = $match[1];
				$id = $match[2];
				$type = $match[3];
				$title = $match[4];

				$aa = $match[5];
				$files[] = array('id' => $id, 'type' => $type);
				$html = preg_replace($pattern, $a . '~' . $title . $aa, $html, 1);
			}
		} while (sizeof($match) > 1);

		$filesd = array();
		foreach ($files as $f) {
			$filed = rub_get($f['type'], $f['id'], array());
			if ($filed) {
				$filed['type'] = $f['type'];
				$filesd[$f['id']] = $filed;
			}
		}
		$pattern = '/(<a.*href="\/\-rubrics\/\?[^"]*id=(\w+)&type=(\w+)&[^"]*load".*>)~([^~<]*?)(<\/a>)/u';
		$tpl = <<<END
			<nobr><a href="/-rubrics/?id={id}&type={type}&load" title="{name}">{title}</a>&nbsp;<img alt="" style="margin-right:3px;" src="/-imager/?src=-rubrics/icons/{ext}.png&w=16" title="{name}"> {size} Мб</nobr>
END;
		do {
			preg_match($pattern, $html, $match);

			if (sizeof($match) > 1) {
				$a = $match[1];
				$title = $match[4];
				$aa = $match[5];
				$type = $match[3];
				$id = $match[2];

				if (isset($filesd[$id])) {
					$d = $filesd[$id];
					$d['title'] = $title;
					$t = Template::parse(array($tpl), $d);
					$html = preg_replace($pattern, $t, $html, 1);
				} else {
					$html = preg_replace($pattern, $a . $title . $aa, $html, 1);
				}
			}
		} while (sizeof($match) > 1);
		$html = preg_replace("/<\/a>\n/", '</a>', $html);

		return $html;
	}
	public static function article($src)
	{
		return Cache::exec('Подготовленные статьи', function ($src) {
			$html = Load::loadTEXT('-doc/get.php?src=' . $src);
			$info = Load::srcInfo($src);
			if (!in_array($info['ext'], array('html', 'tpl', 'php'))) {
				$soft = true;
			} else {
				$soft = false;
			}
			return Rubrics::parse($html, $soft);
		}, array($src), ['akiyatkin\boo\Cache', 'getModifiedTime'], [$src]);
	}
}
