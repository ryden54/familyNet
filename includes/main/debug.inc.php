<?php
/**
 * Debug function, displaying content for all arguments
 */
function mvd() {
	if (Config::get('DEBUG') === TRUE) {

		$bt = debug_backtrace();
		$caller = array_shift($bt);
		echo '<div class="alert alert-info"><i class="icon-eye-open"></i> <b>'
				. str_replace(array(
					"\\", $_SERVER['DOCUMENT_ROOT']
				), array(
					'/', ''
				), $caller['file']) . ' at line ' . $caller['line'] . '</b><br/>';
		foreach (func_get_args() As $a) {
			echo '<br/>';
			ob_start();
			var_dump($a);
			$d = ob_get_clean();
			echo nl2br(str_replace(array(
				' ', '	'
			), array(
				'&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;'
			), $d));
			echo '<br/>';
		}
		echo '</div>';
	}
}
