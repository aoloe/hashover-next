<?php


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// Use UTF-8 character set
	ini_set ('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set ('display_errors', true);
	error_reporting (E_ALL);

	// Tell browser this is JavaScript
	header ('Content-Type: application/javascript');

	// Autoload class files
	spl_autoload_register (function ($classname) {
		$classname = strtolower ($classname);

		if (!@include ('../scripts/' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Instantiate HashOver class
	$hashover = new HashOver ('api', !empty ($_GET['url']) ? $_GET['url'] : '');
	$hashover->parseAll ();

	// Display error if the API is disabled
	if ($hashover->setup->APIStatus ('json') === 'disabled') {
		exit (json_encode (array ('error' => '<b>HashOver</b>: This API is not enabled.')));
	}

	// Display the JSON data
	if (!empty ($hashover->comments['comments'])) {
		echo json_encode ($hashover->comments['comments'], JSON_NUMERIC_CHECK);
	} else {
		echo json_encode (array ('No comments.'));
	}

?>
