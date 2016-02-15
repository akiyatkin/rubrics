<?php
use infrajs\path\Path;
use infrajs\load\Load;
use infrajs\template\Template;
use infrajs\cache\Cache;
use infrajs\doc\Docx;
use infrajs\doc\Mht;

function rub_search($dir, $str, $exts)
{
	$files = rub_list($dir, 0, 0, $exts);

	if (@$files[$str]) {
		$files[$str]['idfinded'] = true;//Найдено по id
			   return $files[$str];
	}
	foreach ($files as $d) {

		if (mb_strtolower($d['name']) == mb_strtolower($str)) {
			return $d;
		}
	}

	return array();
}
function rub_ptube()
{
	$ptube = 'http.*youtube\.com.*watch.*=([\w\-]+).*';

	return $ptube;
}
function rub_ptube2()
{
	$ptube = 'http.{0,1}:\/\/youtu\.be\/([\w\-]+)';

	return $ptube;
}
function rub_article($src)
{
	$html = Load::loadTEXT('-doc/get.php?'.$src);
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
	
	$pattern = '/(<a.*href="[^"]*rubrics\/rubrics\.php[^"]*id=(\w+)&type=(\w+)&[^"]-load".*>)~([^~<]*?)(<\/a>)/u';
	$tpl = <<<END
		<nobr>
			<a href="/-rubrics/rubrics.php?id={id}&type={type}&load" title="{name}">{title}</a> 
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

function rub_get($type, $id, $exts)
{
	if(!$type)return;
	$files = rub_list('~'.$type.'/', 0, 0, $exts);
	$res = $files[$id];
	if (!$res) {
		$res = array();
	}

	return $res;
}
function rub_list($dir, $start = 0, $count = 0, $exts = array())
{

	$files = Cache::exec(array($dir), 'rub_list', function ($dir, $start, $count, $exts) {
		$dir = Path::theme($dir);

		return _rub_list($dir, $start, $count, $exts);
	}, array($dir, $start, $count, $exts),isset($_GET['re']));

	return $files;
}
function _rub_list($dir, $start, $count, $exts)
{
	if (!$dir) {
		return array();
	}
	$dir = Path::toutf($dir);
	$dir = Path::theme($dir);

	$res = array();

	if (!$dir || !is_dir($dir)) {
		return $res;
	}
	if (is_dir($dir) && $dh = opendir($dir)) {
		$files = array();
	
		while (($file = readdir($dh)) !== false) {
			if ($file[0] == '.') {
				continue;
			}
			if ($file[0] == '~') {
				continue;
			}
			if ($file == 'Thumbs.db') {
				continue;
			}
			if (!is_file($dir.$file)) {
				continue;
			}
			$rr = Load::nameInfo(Path::toutf($file));
			$ext = $rr['ext'];
			if ($exts && !in_array($ext, $exts)) {
				continue;
			}
			$size = filesize($dir.$file);
			
			$file = Path::toutf($file);
			

			if (in_array($ext, array('mht', 'tpl', 'html', 'txt','php'))) {
				$rr = Mht::preview(Path::toutf($dir).$file);
				
			} elseif (in_array($ext, array('docx'))) {
				$rr = Docx::preview(Path::toutf($dir).$file);
			}

			$rr['size'] = round($size / 1000000, 2);
			$links = @$rr['links'];
			if ($links) {
				unset($rr['links']);
				$ptube = rub_ptube();
				$ptube2 = rub_ptube();

				foreach ($links as $v) {
					$r = preg_match('/'.$ptube.'/', $v['href'], $match);
					$r2 = preg_match('/'.$ptube2.'/', $v['href'], $match);
					if ($r) {
						if (!@$rr['video']) {
							$rr['video'] = array();
						}
						$v['id'] = $match[1];
						$rr['video'][] = $v;
					} elseif ($r2) {
						if (!@$rr['video']) {
							$rr['video'] = array();
						}
						$v['id'] = $match[1];
						$rr['video'][] = $v;
					} else {
						if (!@$rr['links']) {
							$rr['links'] = array();
						}
						$rr['links'][] = $v;
					}
				}
			}
			$files[] = $rr;
		}
		usort($files, function ($b, $a) {
			$a = @$a['date'];
			$b = @$b['date'];

			return $a < $b ? +1 : -1;
		});
		$maxid = 0;
		foreach ($files as $fdata) {
			if (!$fdata['id']) {
				continue;
			}
			if ($fdata['id'] > $maxid) {
				$maxid = $fdata['id'];
			}
		}
		foreach ($files as &$fdata) {
			if ($fdata['id'] && $fdata['date']) {
				continue;
			}
			if (!$fdata['id']) {
				$fdata['id'] = ++$maxid;
			}
		}
		$files = array_reverse($files);
		if ($count || $start) {
			$files = array_splice($files, $start, $count);
		}
		foreach ($files as $fdata) {
			$res[$fdata['id']] = $fdata;
		}
	}

	return $res;
}
