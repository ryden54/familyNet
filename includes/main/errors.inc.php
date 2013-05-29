<?php
function myTrace($trace) {
	$res = '';
	foreach ($trace As $caller) {
		$res .=
				(isset($caller['file']) === true ? str_replace(
						array(
							"\\", substr($_SERVER['DOCUMENT_ROOT'], 0, strrpos($_SERVER['DOCUMENT_ROOT'], '/'))
						),
						array(
							'/', ''
						),
						$caller['file']) : 'unknown') . ' at line ' . (isset($caller['line']) ? $caller['line'] : '?') . ' : <b>'
						. (isset($caller['class']) === true ? $caller['class'] . '::' : '') . $caller['function'] . '</b>(';
		$args = array();

		if (isset($caller['args']) === true) {
			foreach ($caller['args'] As $a) {
				$args[] = var_export($a, true);
			}
		}
		$res .= implode(', ', $args) . ')<br/>';
	}
	return $res;
}

function displayError($errorType, $errorText, $errorDetail = null) {
	if (Config::get('DEBUG') === TRUE) {

		if ($errorDetail === null) {
			$exception = new Exception();
			$errorDetail = myTrace($exception->getTrace());
		}

		echo '<link href="/static/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">';

		echo "<div class='alert alert-error'><i class='icon-bell'></i> <b>" . $errorType . " :</b> " . $errorText . '<br/><br/>';
		echo $errorDetail . '<br/>';

		echo '</div>';
	}
}

// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline) {
	if (Config::get('DEBUG') === TRUE) {

		$errorTypes =
				array(
						E_COMPILE_ERROR => 'Compile Error',
						E_CORE_ERROR => 'Code Error',
						E_CORE_WARNING => 'Core Warning',
						E_DEPRECATED => 'Deprecated',
						E_ERROR => 'Error',
						E_NOTICE => 'Notice',
						E_PARSE => 'Parse Error',
						E_RECOVERABLE_ERROR => 'Recoverable Error',
						E_STRICT => 'Strict Error',
						E_USER_DEPRECATED => 'User Depretacted Error',
						E_USER_ERROR => 'User Error',
						E_USER_NOTICE => 'User Notice',
						E_USER_WARNING => 'User Warning',
						E_WARNING => 'Warning',
				);

		$detail = "Using PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />";
		$detail .= '<br/>' . myTrace(debug_backtrace()) . '<br/>';

		$detail .= '</div>';

		displayError(
				(isset($errorTypes[$errno]) === true ? $errorTypes[$errno] : 'Unknown Error [' . $errno . ']'),
				$errstr . ' in '
						. str_replace(
								array(
									"\\", substr($_SERVER['DOCUMENT_ROOT'], 0, strrpos($_SERVER['DOCUMENT_ROOT'], '/'))
								),
								array(
									'/', ''
								),
								$errfile) . ' at line ' . $errline,
				$detail);

		if ($errno & (E_ERROR)) {
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
		}
		/* Don't execute PHP internal error handler */
	}
	return true;
}

// set to the user defined error handler
$old_error_handler = set_error_handler("myErrorHandler", E_ALL);

function myExceptionHandler(Exception $exception) {
	if (Config::get('DEBUG') === TRUE) {
		echo '<link href="/static/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">';
		echo "<div class='alert alert-error'><i class='icon-bell'></i> <b>Uncaught Exception </b> " . get_class($exception) . '<br/>';

		echo '<b>' . $exception->getMessage() . '</b><br/>';

		echo '<br/>' . myTrace($exception->getTrace()) . '<br/>';
		echo "</div>";
	}
	return null;
}

$old_exception_handler = set_exception_handler("myExceptionHandler");

function myShutdownFunction() {
	if (($e = error_get_last()) !== null) {
		myErrorHandler($e['type'], $e['message'], $e['file'], $e['line']);
	}
}

register_shutdown_function('myShutdownFunction');
