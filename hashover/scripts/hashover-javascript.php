<?php

	// Attempt to obtain URL via GET
	if (!empty ($_GET['url'])) {
		$page_url = $_GET['url'];
	} else {
		// Attempt to obtain URL via POST
		if (!empty ($_POST['url'])) {
			$page_url = $_POST['url'];
		}
	}

	// Attempt to obtain URL via HTTP referer
	if (empty ($page_url) and !empty ($_SERVER['HTTP_REFERER'])) {
		$page_url = $_SERVER['HTTP_REFERER'];
		$referer = parse_url ($page_url);
		$referer_host = $referer['host'];
		$http_host = $_SERVER['HTTP_HOST'];

		// Add referer port to referer host
		if (!empty ($referer['port'])) {
			$referer_host .= ':' . $referer['port'];
		}

		// Error if the script wasn't requested by this server
		if ($referer_host !== $http_host) {
			exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver</b>: External use not allowed.\';');
		}
	}

	// Instantiate HashOver class
	$hashover = new HashOver ('javascript', $page_url, $page_title);
	$hashover->parseAll ();

	// Attempt to include JavaScript frontend code
	if (!include ('./javascript-mode.php'))
