<?php


	// Use UTF-8 character set
	ini_set ('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set ('display_errors', true);
	error_reporting (E_ALL);

	// Tell browser this is XML/RSS
	header ('Content-Type: application/xml; charset=utf-8');

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

	// Create new DOM document.
	$xml = new DOMDocument ('1.0', 'UTF-8');
	$xml->preserveWhiteSpace = false;
	$xml->formatOutput = true;

	// Create main RSS element
	$rss = $xml->createElement ('rss');
	$rss->setAttribute ('version', '2.0');
	$rss->setAttribute ('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
	$rss->setAttribute ('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
	$rss->setAttribute ('xmlns:atom', 'http://www.w3.org/2005/Atom');

	// Display error if the API is disabled
	if ($hashover->setup->APIStatus ('rss') === 'disabled') {
		$title = $xml->createElement ('title');
		$title_value = $xml->createTextNode ('HashOver: RSS API is not enabled.');
		$title->appendChild ($title_value);
		$rss->appendChild ($title);

		$description = $xml->createElement ('description');
		$description_value = $xml->createTextNode ('Error!');
		$description->appendChild ($description_value);
		$rss->appendChild ($description);

		// Add main RSS element to XML
		$xml->appendChild ($rss);

		// Return RSS XML
		exit (str_replace ('  ', "\t", $xml->saveXML ()));
	}

	// Create channel element
	$channel = $xml->createElement ('channel');

	// Create channel title element
	$title = $xml->createElement ('title');
	$title_value = $xml->createTextNode (html_entity_decode ($hashover->setup->metadata['title'], ENT_COMPAT, 'UTF-8'));
	$title->appendChild ($title_value);

	// Add channel title to channel element
	$channel->appendChild ($title);

	// Create channel link element
	$link = $xml->createElement ('link');
	$link_value = $xml->createTextNode (html_entity_decode ($hashover->setup->metadata['url'], ENT_COMPAT, 'UTF-8'));
	$link->appendChild ($link_value);

	// Add channel link to channel element
	$channel->appendChild ($link);

	// Create channel description element
	$description = $xml->createElement ('description');
	$count_plural = ($hashover->readComments->totalCount !== 1);
	$count_locale = str_replace ('_NUM_', $hashover->readComments->totalCount - 1, $hashover->locales->locale['showing_comments'][$count_plural]);
	$description_value = $xml->createTextNode ($count_locale);
	$description->appendChild ($description_value);

	// Add channel description to channel element
	$channel->appendChild ($description);

	// Create channel atom link element
	$atom_link = $xml->createElement ('atom:link');
	$atom_link->setAttribute ('href', 'http://' . $hashover->settings->domain . $_SERVER['PHP_SELF'] . '?url=' . $hashover->setup->metadata['url']);
	$atom_link->setAttribute ('rel', 'self');

	// Add channel atom link to channel element
	$channel->appendChild ($atom_link);

	// Create channel language element
	$language = $xml->createElement ('language');
	$language_value = $xml->createTextNode ('en-us');
	$language->appendChild ($language_value);

	// Add channel language to channel element
	$channel->appendChild ($language);

	// Create channel ttl element
	$ttl = $xml->createElement ('ttl');
	$ttl_value = $xml->createTextNode ('40');
	$ttl->appendChild ($ttl_value);

	// Add channel ttl to channel element
	$channel->appendChild ($ttl);

	// Add channel element to main RSS element
	$rss->appendChild ($channel);

	// Parse comments
	function parse_comments (&$comments, &$rss, &$xml, &$hashover)
	{
		foreach ($comments as $comment) {
			// Skip deleted/unmoderated comments
			if (isset ($comment['notice'])) {
				continue;
			}

			// Remove [img] tags
			$comment['body'] = preg_replace ('/\[(img|\/img)\]/i', '', $comment['body']);

			// Get name from comment or use configured default
			$name = !empty ($comment['name']) ? $comment['name'] : $hashover->settings->defaultName;

			// Create item element
			$item = $xml->createElement ('item');

			// Generate comment summary item title
			$title = $name . ' : ';
			$single_comment = str_replace (PHP_EOL, ' ', $comment['body']);

			if (mb_strlen ($single_comment) > 40) {
				$title .= substr ($single_comment, 0, 40) . '...';
			} else {
				$title .= $single_comment;
			}

			// Create item title element
			$item_title = $xml->createElement ('title');
			$item_title_value = $xml->createTextNode (html_entity_decode ($title, ENT_COMPAT, 'UTF-8'));
			$item_title->appendChild ($item_title_value);

			// Add item title element to item element
			$item->appendChild ($item_title);

			// Create item name element
			$item_name = $xml->createElement ('name');
			$item_name_value = $xml->createTextNode (html_entity_decode ($name, ENT_COMPAT, 'UTF-8'));
			$item_name->appendChild ($item_name_value);

			// Add item name element to item element
			$item->appendChild ($item_name);

			// Add HTML anchor tag to URLs (hyperlinks)
			$comment['body'] = preg_replace ('/((ftp|http|https):\/\/[a-z0-9-@:%_\+.~#?&\/=]+) {0,}/i', '<a href="\\1" target="_blank">\\1</a>', $comment['body']);

			// Replace newlines with break tags
			$comment['body'] = str_replace (PHP_EOL, '<br>', htmlentities ($comment['body'], ENT_COMPAT, 'UTF-8', true));

			// Create item description element
			$item_description = $xml->createElement ('description');
			$item_description_value = $xml->createTextNode (html_entity_decode ($comment['body'], ENT_COMPAT, 'UTF-8'));
			$item_description->appendChild ($item_description_value);

			// Add item description element to item element
			$item->appendChild ($item_description);

			// Create item avatar element
			$item_avatar = $xml->createElement ('avatar');
			$web_root = 'http://' . $hashover->settings->domain . $hashover->settings->httpDirectory;
			$item_avatar_value = $xml->createTextNode ($web_root . $comment['avatar']);
			$item_avatar->appendChild ($item_avatar_value);

			// Add item avatar element to item element
			$item->appendChild ($item_avatar);

			if (!empty ($comment['likes'])) {
				// Create item likes element
				$item_likes = $xml->createElement ('likes');
				$item_likes_value = $xml->createTextNode ($comment['likes']);
				$item_likes->appendChild ($item_likes_value);

				// Add item likes element to item element
				$item->appendChild ($item_likes);
			}

			if ($hashover->settings->allowsDislikes === true) {
				if (!empty ($comment['dislikes'])) {
					// Create item dislikes element
					$item_dislikes = $xml->createElement ('dislikes');
					$item_dislikes_value = $xml->createTextNode ($comment['dislikes']);
					$item_dislikes->appendChild ($item_dislikes_value);

					// Add item dislikes element to item element
					$item->appendChild ($item_dislikes);
				}
			}

			// Create item publication date element
			$item_pubDate = $xml->createElement ('pubDate');
			$item_pubDate_value = $xml->createTextNode (date ('D, d M Y H:i:s O', $comment['sort_date']));
			$item_pubDate->appendChild ($item_pubDate_value);

			// Add item pubDate element to item element
			$item->appendChild ($item_pubDate);

			// URL to comment for item guide and link elements
			$item_permalink_url = $hashover->setup->metadata['url'] . '#' . $comment['permalink'];

			// Create item guide element
			$item_guid = $xml->createElement ('guid');
			$item_guid_value = $xml->createTextNode ($item_permalink_url);
			$item_guid->appendChild ($item_guid_value);

			// Add item guide element to item element
			$item->appendChild ($item_guid);

			// Create item link element
			$item_link = $xml->createElement ('link');
			$item_link_value = $xml->createTextNode ($item_permalink_url);
			$item_link->appendChild ($item_link_value);

			// Add item link element to item element
			$item->appendChild ($item_link);

			// Add item element to main RSS element
			$rss->appendChild ($item);

			// Recursively parse replies
			if (!empty ($comment['replies'])) {
				parse_comments ($comment['replies'], $rss, $xml, $hashover);
			}
		}
	}

	// Add item element to main RSS element
	parse_comments ($hashover->comments['comments'], $rss, $xml, $hashover);

	// Add main RSS element to XML
	$xml->appendChild ($rss);

	// Return RSS XML
	echo preg_replace_callback ('/^(\s+)/m', function ($spaces) {
		return str_repeat ("\t", strlen ($spaces[1]) / 2);
	}, $xml->saveXML ());
