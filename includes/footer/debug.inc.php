<h4 data-toggle="collapse" data-target="#debugPanel" style="float:none;clear:both;">Debug</h4>
<div class="debug alert alert-info collapse" id="debugPanel">
	<table class="table table-bordered table-striped table-hover ">
		<thead>
			<tr>
				<td>Type</td>
				<td>Sous-type</td>
				<td>Data</td>
				<td>Result</td>
				<td>Time</td>
			</tr>
		</thead>
		<tbody>
			<?php
foreach (Context::getInstance()->getDebug()->getDatas() As $d) {
			?>
			<tr>
				<td><?=$d['type']; ?></td>
				<td><?=$d['subtype']; ?></td>
				<td><?php var_dump($d['data']); ?></td>
				<td><?php var_dump($d['result']); ?></td>
				<td><?=$d['time']; ?></td>
			</tr>
			<?php
}
			?>
		</tbody>
	</table>
	<table class="table table-bordered table-striped table-hover ">
		<thead>
			<tr>
				<td>Included files</td>
			</tr>
		</thead>
		<tbody>
			<?php
foreach (get_included_files() As $f) {
	echo '<tr><td>' . $f . '</td></tr>';
}
			?>
		</tbody>
	</table>
</div>