<center>
	<h1>Selecciona el signo que deseas consultar</h1>
</center>

<table style="text-align:center;" width="100%">
	{foreach $signos as $signo}
		{strip}
		<tr bgcolor="{cycle values="#f2f2f2,white"}">
			<td style="font-weight: bold;font-size: 3em;">{$signo.codHtml}</td>
			<td style="">{$signo.nombre}</td>
			<td style="">{$signo.rangoFechas}</td>
			<td style="">{$signo.elemento}</td>
			<td style="">
			{button href="HOROSCOPO {$signo.nombre}" caption="{$signo.nombre}" color="green" size="small"}
			</td>
		</tr>
		{/strip}
	{/foreach}
</table>
