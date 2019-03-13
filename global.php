<?php
function alog($details) {
	$errc = file_get_contents('./logs/errc.txt');
	if (!isset($errc)) {
		$errc = "1";
	}
	$log = $errc . " | " . date('h:i:s a', time()) . " > " . $details . "\n";
	file_put_contents('./logs/auth_errors_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
	error_log(date("j.n.Y")." ".$_SERVER['REMOTE_ADDR'].$log);
	file_put_contents('./logs/errc.txt',"".(string)($errc+1));
	return $errc;
}
?>