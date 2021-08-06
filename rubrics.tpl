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
	<style>
		#{div} .date {
			float: right; 
			margin-left: 10px; 
			margin-top: 5px;
			margin-bottom: 5px;
			font-size: 1rem;
		}
		#{div} .block {
			display: grid; 
			clear: both;
			gap: 20px;
			grid-template-areas: "preview image";
			grid-template-columns: 1fr 330px ;
		}
		@media(max-width: 991px) {
			#{div} .block {
				grid-template-areas: 	"image" 
										"preview";
				grid-template-columns: 1fr;
			}
		}
	</style>
	<div>
		{data.list::Pitem}
	</div>
	{Pitem:}
		<div style="margin-top:1em; max-width: 1000px;" id="item{~key}">
			<h3>{heading|name} <span class="date">{data.type.onlyyear?date:Pdateyear?date:Pdate}</span></h3>
			<div class="block">
				<div style="grid-area: image;justify-content: center;">
					{images.0:imgt}
				</div>
				<div style="grid-area: preview">
					{preview}
					<a href="/{:link}{name}">Читать полностью</a>
				</div>
			</div>
		</div>
	{Pdate:}<div style="text-align:right"><i style="color: #aaaaaa;">{:date}</i></div>
	{Pdateyear:}<div style="text-align:right"><i style="color: #aaaaaa;">{~date(:Y,.)}</i></div>
	{date:}{~date(:j F Y,.)}
	{j F Y:}j{:nbsp}F{:nbsp}Y{:nbsp}
	{nbsp:}&\n\b\s\p;
{link:}{~conf.rubrics.main=crumb.name??:cn}
	{cn:}{link|crumb.name}/
{imgt:}
	<a href="/{:link}{...name}">
		<img style="max-width:100%" src="/-imager/?src={src}&w=360">
	</a>