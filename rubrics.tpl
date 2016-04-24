{404:}
	<h1>{crumb.name}</h1><p>{infra.config(:rubrics).404}</p><p><a href='/'>{infra.config(:rubrics).link}</a></p>
{comma:},
{FILES:}
	<table class="table table-striped" style="width:auto">
		{data.list::Fitem}
	</table>
	{Fitem:}
		<tr>
			<td>
				<img src="/-imager/?src=-autoedit/icons/{ext}.png&w=16"></td>
			<td><a href="/-rubrics/?id={id|name}&type={crumb.name}&load" title="{file}">{name}</a></td>
			<td>{size}&nbsp;Mb</td>
			<td>{~date(:j.m.Y,date)}</td>
		</tr>
{PAGES:}
	<hr>
	{data.list::Pitem}
	{Pitem:}
		<div style="margin-bottom:30px">
			{date:Pdate}
			<h2 style="margin-top: 5px;">{heading|name}</h2>
			{preview}
			<a style="text-decoration: none; float: right;" href="/{:link}{name}">Читать полностью</a>
			<div style="clear:both"></div>
		</div>
		<hr>
	{Pdate:}<i style="color: #aaaaaa;">{:date}</i>
	{date:}{~date(:j F Y,.)}
	{j F Y:}j{:nbsp}F{:nbsp}Y{:nbsp}
	{nbsp:}&\n\b\s\p;
{link:}{infra.config(:rubrics).main=crumb.name??:cn}
	{cn:}{crumb.name|link}/
