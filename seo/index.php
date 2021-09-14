<?php
use akiyatkin\meta\Meta;
use infrajs\config\Config;
use infrajs\path\Path;
use infrajs\rubrics\Rubrics;

$meta = new Meta();
$meta->addArgument('dir', function ($dir) {

	if (!Path::isNest('~', $dir)) return $this->err('meta.forbidden');
	echo $dir;
	exit;
	return $dir;
});
$meta->addArgument('id', function ($id) {
	return Path::encode($id);
});
$meta->addArgument('type', function ($type) {
	$conf = Config::get('rubrics');
	if (empty($conf['list'][$type])) return $this::err($ans, 'meta.error', $type);
	return $type;
});
$meta->addVariable('src', function () {
	extract($this->gets(['dir','id']));

	$src = Rubrics::find($dir, $id);
	if (!$src) return $this->err('meta.badrequest'); 
	return $src;
});
$meta->addVariable('src#bytype', function () {
	extract($this->gets(['type','id']));
	$conf = Config::get('rubrics');
	if (in_array($conf['list'][$type]['type'], array('list','info'))) {
		$exts = array('docx','tpl','mht','html','php');
	} else {
		$exts = array();
	}
	$dir = rub_getdir($type);
	if (!$dir) return $this::err($ans, 'meta.error');
	$src = Rubrics::find($dir, $id);
	if (!$src) return $this->err('meta.badrequest'); 
	return $src;
});
$meta->addAction('bytype', function () {
	extract($this->gets(['src#bytype']));
	$info = Rubrics::info($src);
	if (isset($info['images']) && sizeof($info['images'])) {
		$this->ans['image_src'] = $info['images'][0]['src'];
	}
	$this->ans['title'] = $info['heading'];
	return $this->ret('meta.ready');
});
$meta->addAction('json', function () {
	extract($this->gets(['src']));
	$info = Rubrics::info($src);
	if (sizeof($info['images'])) {
		$this->ans['image_src'] = $info['images'][0]['src'];
	}
	$this->ans['title'] = $info['heading'];
	return $this->ret('meta.ready');
});

return $meta->init([	
	'base'=>'-rubrics/seo/'
]);