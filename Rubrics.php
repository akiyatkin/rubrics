<?php
namespace infrajs\rubrics;
use infrajs\load\Load;
use infrajs\path\Path;
use infrajs\template\Template;

Path::req('-rubrics/rubrics.inc.php');


class Rubrics {
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
	public static function search($type, $id) {
		if (!isset(Rubrics::$conf['list'][$type])) return false;
		$exts = array('docx', 'mht', 'tpl', 'html', 'txt', 'php');
		$files = rub_list('~'.$type.'/', 0, 0, $exts);
		

		if (isset($files[$id])) {
			$files[$id]['idfinded'] = true;//Найдено по id
			return $files[$id];
		}
		foreach ($files as $d) {
			if (mb_strtolower($d['name']) == mb_strtolower($id)) {
				return $d;
			}
		}
		return array();
	}
	public static function article ($src) {
		$html = Load::loadTEXT('-doc/get.php?src='.$src);
		$info = Load::srcInfo($src);
		if (!in_array($info['ext'], array('html', 'tpl', 'php'))) {
			$html = preg_replace('/<table>/', '<table class="table table-striped">', $html);
		}

		$html = preg_replace("/<\/a>/", "</a>\n", $html);

		//youtube
		$ptube = rub_ptube();
		$pattern = '/(<a.*href="'.$ptube.'".*>)'.$ptube.'(<\/a>)/i';

		$youtpl = <<<END
		<img title="Видео" style="cursor:pointer" onclick="$(this).hide().after($(this).data('html'));" data-html='<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" width="640" height="480" src="http://www.youtube.com/embed/{3}?autoplay=1" frameborder="0" allowfullscreen></iframe></div>' class="img-responsive" src="https://i.ytimg.com/vi/{3}/hqdefault.jpg">
END;

		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$a = $match[1];
				$aa = $match[4];
				$files[] = $match[2];
				$youhtml = Template::parse(array($youtpl), $match);
				$html = preg_replace($pattern, $youhtml, $html, 1);
			}
		} while (sizeof($match) > 1);

		//youtube2
		$ptube = rub_ptube2();
		$pattern = '/(<a.*href="'.$ptube.'".*>)'.$ptube.'(<\/a>)/i';
		$youtpl = <<<END
		<img title="Видео" style="cursor:pointer" onclick="$(this).hide().after($(this).data('html'));" data-html='<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" width="640" height="480" src="http://www.youtube.com/embed/{3}?autoplay=1" frameborder="0" allowfullscreen></iframe></div>' class="img-responsive" src="https://i.ytimg.com/vi/{3}/hqdefault.jpg">
END;
		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$a = $match[1];
				$aa = $match[4];
				$files[] = $match[2];
				$youhtml = Template::parse(array($youtpl), $match);
				$html = preg_replace($pattern, $youhtml, $html, 1);
			}
		} while (sizeof($match) > 1);

		//files
		//setlocale(LC_ALL, 'ru_RU.UTF-8');
		$files = array();
		$pattern = '/(<a.*href="[^"]*rubrics\/rubrics\.php[^"]*id=(\w+)&type=(\w+)&[^"]-load".*>)([^~<]*?)(<\/a>)/u';
		do {
			$match = array();
			preg_match($pattern, $html, $match);
			if (sizeof($match) > 1) {
				$a = $match[1];
				$id = $match[2];
				$type = $match[3];
				$title = $match[4];
				$aa=$match[5];
				$files[] = array('id' => $id, 'type' => $type);
				$html = preg_replace($pattern, $a.'~'.$title.$aa, $html, 1);
			}
		} while (sizeof($match) > 1);
		
		$filesd = array();
		foreach ($files as $f) {
			$filed = rub_get($f['type'], $f['id'], array());
			if ($filed) {
				$filed['type']=$f['type'];
				$filesd[$id] = $filed;
			}
		}
		
		$pattern = '/(<a.*href="[^"]*rubrics\/\?[^"]*id=(\w+)&type=(\w+)&[^"]-load".*>)~([^~<]*?)(<\/a>)/u';
		$tpl = <<<END
			<nobr>
				<a href="/-rubrics/?id={id}&type={type}&load" title="{name}">{title}</a> 
				<img style="margin-right:3px; margin-bottom:-4px;" src="/-imager/?src=-autoedit/icons/{ext}.png&w=16" title="{name}"> {size} Mb</nobr>
END;
		do {
			preg_match($pattern, $html, $match);
			
			if (sizeof($match) > 1) {
				$a = $match[1];
				$title = $match[4];
				$aa = $match[5];
				$type = $match[3];
				$id = $match[2];

				if ($filesd[$id]) {
					$d = $filesd[$id];
					$d['title'] = $title;
					$t = Template::parse(array($tpl), $d);
					$html = preg_replace($pattern, $t, $html, 1);
				} else {
					$html = preg_replace($pattern, $a.$title.$aa, $html, 1);
				}
			}
		} while (sizeof($match) > 1);
		$html = preg_replace("/<\/a>\n/", '</a>', $html);

		return $html;
	}
}