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

$ans=array('title'=>'Запуск шаблонизатора');

$tpl='{infra.config(name).main}';
$data=array('name'=>'rubrics');
$html=Template::parse(array($tpl),$data);

if (!$html) return Ans::err($ans,'{infra.config(name).main} возрващает пустую строку '.$html);

$tpl='{infra.config(:rubrics).main}';
$data=true;
$html=Template::parse(array($tpl),$data);
if (!$html) return Ans::err($ans,'{infra.config(:rubrics).main} возрващает пустую строку '.$html);


$tpl='{list::test}{test:}1{:date}{date:}{~date(:F,~true)}';
$data=Load::loadJSON('-rubrics/?type=events&list');
$html=Template::parse(array($tpl),$data);
if (!$html) return Ans::err($ans,' возрващает пустую строку '.$html);

return Ans::ret($ans);

