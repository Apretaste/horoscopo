<center>
	<h1>Selecciona el signo que deseas consultar</h1>
</center>

<table style="text-align:center;" width="100%">
	{foreach from=$signos key=name item=signo}
		{strip}
		<tr bgcolor="{cycle values="#f2f2f2,white"}">
			<td style="font-weight: bold;font-size: 3em;">{$signo.codHtml}</td>
			<td>{$signo.nombre}</td>
			<td>{$signo.rangoFechas}</td>
			<td>{$signo.elemento}</td>
			<td>{button href="HOROSCOPO {$name}" caption="{$signo.nombre}" color="green" size="small"}</td>
		</tr>
		{/strip}
	{/foreach}
</table>
