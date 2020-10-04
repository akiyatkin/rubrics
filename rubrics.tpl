{TITLE:}
	
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/">Главная</a></li>
		<li class="breadcrumb-item"><a href="/{crumb.parent.name}">{~conf.rubrics.list[crumb.parent.name].title}</a></li>
		<li class="breadcrumb-item active">{data.info.heading}</li>
	</ol>
	<div class="float-right badge badge-secondary">{data.info.date:date}</div>
{404:}
	<h1>{crumb.name}</h1><p>{Config.get(:rubrics).404}</p><p><a href='/'>{Config.get(:rubrics).link}</a></p>
{comma:},
{FILES:}
	<table class="table table-striped" style="width:auto">
		{data.list::Fitem}
	</table>
	{Fitem:}
		<tr>
			<td>
				<img src="/-imager/?src=-rubrics/icons/{ext}.png&amp;w=16"></td>
			<td><a href="/-rubrics/?id={id|name}&amp;type={crumb.name}&amp;load" title="{file}">{name}</a></td>
			<td>{size}&nbsp;Mb</td>
			<td>{~date(:j.m.Y,date)}</td>
		</tr>
{PAGESTITLE:}
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/">Главная</a></li>
		<li class="active breadcrumb-item">{~conf.rubrics.list[crumb.name].title|data.heading}</li>
	</ol>
	{~conf.rubrics.list[crumb.name].title:h1title}
	{h1title:}<h1>{~conf.rubrics.list[crumb.name].title}</h1>
{PAGES:}
	<div>
		{data.list::Pitem}
	</div>
	{Pitem:}
		<div style="margin-top:1em">
			<h4>{heading|name}</h4>
			{data.type.onlyyear?date:Pdateyear?date:Pdate}
			{images.0:imgt}
			{preview}
			{images.0:imgb}
			<a href="/{:link}{name}">Читать полностью</a>
			<div style="clear:both"></div>
		</div>
		<hr>
	{Pdate:}<div style="text-align:right"><i style="color: #aaaaaa;">{:date}</i></div>
	{Pdateyear:}<div style="text-align:right"><i style="color: #aaaaaa;">{~date(:Y,.)}</i></div>
	{date:}{~date(:j F Y,.)}
	{j F Y:}j{:nbsp}F{:nbsp}Y{:nbsp}
	{nbsp:}&\n\b\s\p;
{link:}{~conf.rubrics.main=crumb.name??:cn}
	{cn:}{link|crumb.name}/
{imgt:}
<a class="d-none d-md-block" href="/{:link}{...name}">
	<img class="img-thumbnail ml-2 mb-2 float-right" src="/-imager/?src={src}&w=360">
</a>
{imgb:}
<a class="d-block d-md-none" href="/{:link}{...name}">
	<img class="img-thumbnail mb-2 img-fluid" src="/-imager/?src={src}&w=800&h=400&crop=1">
</a>