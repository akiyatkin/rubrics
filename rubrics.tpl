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
				<img src="/-imager/?src=-autoedit/icons/{ext}.png&amp;w=16"></td>
			<td><a href="/-rubrics/?id={id|name}&amp;type={crumb.name}&amp;load" title="{file}">{name}</a></td>
			<td>{size}&nbsp;Mb</td>
			<td>{~date(:j.m.Y,date)}</td>
		</tr>
{PAGES:}
	<div>
		{data.list::Pitem}
	</div>
	{Pitem:}
		<div style="margin-top:1em">
			
			<div style="font-size:1.4em;">{heading|name}</div>
			{date:Pdate}
			{preview}
			<a style="float: right;" href="/{:link}{name}">Читать полностью</a>
			<div style="clear:both"></div>
		</div>
		<hr>
	{Pdate:}<div style="text-align:right"><i style="color: #aaaaaa;">{:date}</i></div>
	{date:}{~date(:j F Y,.)}
	{j F Y:}j{:nbsp}F{:nbsp}Y{:nbsp}
	{nbsp:}&\n\b\s\p;
{link:}{infra.config(:rubrics).main=crumb.name??:cn}
	{cn:}{crumb.name|link}/
