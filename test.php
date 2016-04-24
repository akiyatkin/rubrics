<?php

use infrajs\template\Template;
use infrajs\config\Config;
use infrajs\ans\Ans;
use infrajs\load\Load;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Config::init();
}

$ans = array('title'=>'Запуск шаблонизатора');

$tpl = '{infra.config(name).main}';
$data = array('name'=>'rubrics');
$html = Template::parse(array($tpl), $data);




if (!$html) return Ans::err($ans, $tpl.' возрващает пустую строку '.$html);

$tpl='{infra.config(:rubrics).main}';
$data=true;
$html=Template::parse(array($tpl),$data);
if (!$html) return Ans::err($ans, $tpl.' возрващает пустую строку '.$html);

$conf = Config::get('rubrics');

$tpl='{list::test}{test:}1{:date}{date:}{~date(:F,~true)}';
$data=Load::loadJSON('-rubrics/?type='.$conf['main'].'&list');
if (sizeof($data['list'])) { //Это если есть данные иначе тест этот не проводим
	$html=Template::parse(array($tpl),$data);
	if (!$html) return Ans::err($ans,' возрващает пустую строку '.$html);
}
return Ans::ret($ans);

