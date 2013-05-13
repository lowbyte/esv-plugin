<?php
/*
Plugin Name: ESV Plugin
Plugin URI: http://croberts.me/wordpress-esv-plugin/
Description: Allows the user to utilize services from the ESV Web Service
Version: 3.9.1
Author: Chris Roberts
Author URI: http://croberts.me/
*/

/*  Copyright 2013 Chris Roberts (email : chris@dailycross.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class ESV_Plugin
{
	// Initialize settings
	private $lastBook;
	private $lastChapter;

	// Initialize everything
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addoptions'));
		add_action('wp_print_styles', array($this, 'display'), 40);

		if (get_option('esv_process_ref', 'runtime') == 'save') {
			add_action('save_post', array($this, 'edit_post'), 4);
		}

		add_filter('the_content', array($this, 'runtime_modify'), 4);
		add_filter('comment_text', array($this, 'runtime_modify'), 4);

		register_activation_hook(__FILE__, array($this, 'esv_activate'));

		add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
    }

    public function settings_link($links, $file) { 
        if ($file == 'esv-plugin/esv.php') {
            $settings_link = '<a href="options-general.php?page=esv.php">Settings</a>'; 
            array_push($links, $settings_link);
        }
        
        return $links; 
    }

    public function addoptions()
	{
		if (function_exists('add_options_page')) {
			require_once(plugin_dir_path(__FILE__) .'esv_adminOptions.php');
			add_options_page('ESV Plugin Options', 'ESV', 'manage_options', basename(__FILE__), 'esv_options_subpanel');
		}
	}

	public function edit_post($Post_Id)
	{
		global $wpdb;
		
		$query = "SELECT post_content FROM wp_posts WHERE ID = ". $Post_Id .";";
		$rslt = $wpdb->get_results($query);
		
		foreach ($rslt as $result) {
			$content = $result->post_content;
			
			$content = $this->esv_verse($content);
			
			$query = "UPDATE wp_posts SET post_content = '". addslashes($content) ."' WHERE ID = ". $Post_Id .";";
			$wpdb->query($query);
		}
	}

	// runtime_modify is called when the user reads a post. It modifies the
	// link formed when the post is saved and converts it to the style specified
	// in the link.
	public function runtime_modify($content)
	{
		// Are we parsing everything at runtime?
		if (get_option('esv_process_ref', 'runtime') == 'runtime') {
			$content = $this->esv_verse($content);
		}

        // Find any of our formatted links. It is looking for a url with the
        // format:
        // <a href="urltoESV" esv_reference="scripture_ref" esv_header="on|off" esv_format="tooltip|inline|block|link">scripture_ref</a>
        preg_match_all(
        '/'
        .'\<a\shref=["\'](?:[^"\']+?)["\']\s'
        .'(?:class="bibleref"\stitle=".+?"\s)?'
        .'esv_reference=["\'](.+?)["\']\s'
        .'esv_header=["\']((?:on|off){1})["\']\s'
        .'esv_format=["\']((?:tooltip|inline|block|link){1})["\']'
        .'\>'
        .'(.+?)'
        .'\<\/a\>'
        .'/', $content, $Verses);

        for ($i = 0 ; $i < sizeof($Verses[1]) ; $i++) {
            $reference = $Verses[1][$i];
            $header = $Verses[2][$i];
            $format = $Verses[3][$i];
            $linktext = $Verses[4][$i];
            $VerseText = "";

            $VerseText = $this->formatReference(array('reference' => $reference, 'header' => $header, 'format' => $format, 'linktext' => $linktext));

            $content = str_replace($Verses[0][$i], $VerseText, $content);
        }

		// Check for specific tags
		preg_match_all('/\[esvignore\](.+?)\[\/esvignore\]/', $content, $matches);
		for ($i = 0 ; $i < sizeof($matches[1]) ; $i++) {
			$reference = trim($matches[1][$i]);
			$linkmatch = $reference;
			$content = str_replace($matches[0][$i], $linkmatch, $content);
		}

		return $content;
	}

	public function esv_verse($content)
	{
		$esvref = get_option('esv_ref_action', 'link');

		if (get_option('esv_webkey', '') == '') {
			return $content;
		}

		// First, check for references that don't have tags
		// Make sure we're not using a ref already included in tags
		$anchor_regex = '<a\s+href.*?<\/a>';
		$pre_regex = '<pre>.*<\/pre>';
		$code_regex = '<code>.*<\/code>';
		$bible_regex = '\[bible\].*\[\/bible\]';
		$bibleblock_regex = '\[bibleblock\].*\[\/bibleblock\]';
		$biblelink_regex = '\[biblelink\].*\[\/biblelink\]';
		$bibleignore_regex = '\[bibleignore\].*\[\/bibleignore\]';
		$esvbible_regex = '\[esvbible\s?.*\].*\[\/esvbible\]';
		$esvignore_regex = '\[esvignore\].*\[\/esvignore\]';
		$tag_regex = '<(?:[^<>\s]*)(?:\s[^<>]*){0,1}>';
		$split_regex = "/((?:$anchor_regex)|(?:$pre_regex)|(?:$bible_regex)|(?:$bibleblock_regex)|(?:$esvignore_regex)|(?:$biblelink_regex)|(?:$bibleignore_regex)|(?:$esvbible_regex)|(?:$code_regex)|(?:$tag_regex))/i";

		$parsed_text = preg_split($split_regex, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		$linked_text = '';

		while (list($key, $value) = each($parsed_text)) {
			if (preg_match($split_regex, $value)) {
				$linked_text .= $value; // if it is within an element, leave it as is
			} else {
				// Okay, we have text not inside of tags. Now do something with it... And please, try not to break anything.
				$linked_text .= $this->extract_ref($value); // parse it for Bible references
			}
		}

		$content = $linked_text;

		// Check for the [esvbible] tag
		preg_match_all('/\[esvbible([^\]]+)?\]([^\[]+)\[\/esvbible\]/', $content, $matches);
		for ($i = 0 ; $i < sizeof($matches[0]) ; $i++) {
			$options = $matches[1][$i];
			$linktext = $matches[2][$i];

			$reference = "";
			$header = "";
			$format = "";

			preg_match('/reference=["\']([^"]+)["\']/', $options, $references);
			if (is_array($references)) {
				$reference = $references[1];
			}

			preg_match('/header=["\']((?:on|off|yes|no){1})["\']/', $options, $headers);
			if (is_array($headers)) {
				$header = $headers[1];
			}

			preg_match('/format=["\']((?:tooltip|inline|block|link|ignore){1})["\']/', $options, $formats);
			if (is_array($formats)) {
				$format = $formats[1];
			}

			if ($reference == "") {
				$reference = $linktext;
			}

			if ($header == "") {
				$header = "on";
			}

			if ($format == "") {
				$format = get_option('esv_ref_action', 'link');
			}

			if (get_option('esv_process_ref', 'runtime') == "save") {
				$linkmatch = $this->buildLink($reference, $header, $format, $linktext);
			} else {
				$linkmatch = $this->formatReference(array('reference' => $reference, 'header' => $header, 'format' => $format, 'linktext' => $linktext));
			}

			$content = str_replace($matches[0][$i], $linkmatch, $content);
		}

		return $content;
	}

	public function extract_ref($text)
	{
		$volume_list = '1|2|3|I|II|III|1st|2nd|3rd|First|Second|Third';
		$volume_regex = '(?:'. $volume_list .'\s?)';

		$book_list  = 'Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|Samuel|Kings|Chronicles|Ezra|Nehemiah|Esther';
		$book_list .= '|Job|Psalms?|Proverbs?|Ecclesiastes|Songs? of Solomon|Song of Songs|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Obadiah|Jonah|Micah|Nahum|Habakkuk|Zephaniah|Haggai|Zechariah|Malachi';
		$book_list .= '|Mat+hew|Mark|Luke|John|Acts?|Acts of the Apostles|Romans|Corinthians|Galatians|Ephesians|Phil+ippians|Colossians|Thessalonians|Timothy|Titus|Philemon|Hebrews|James|Peter|Jude|Revelations?|chapters?|verses?';

		$abbrev_list  = 'Gen|Ex|Exo|Lev|Num|Nmb|Deut?|Dt|Josh?|Judg?|Jdg|Rut|Sam|Ki?n|Chr(?:on?)?|Ezr|Neh|Est';
		$abbrev_list .= '|Jb|Psa?|Pr(?:ov?)?|Eccl?|Song?|Isa|Jer|Lam|Eze|Da?n|Hos|Joe|Amo?|Oba|Jon|Mic|Nah|Hab|Zeph?|Hag|Zech?|Mal';
		$abbrev_list .= '|M(?:at)?t|Mr?k|Lu?k|Jh?n|Jo|Act|Rom|Cor|Gal|Eph|Col|Phi(?:l?)?|The?|Thess?|Ti?m|Tit|Phile|Heb|Ja?m|Pe?t|Ju?d|Rev';

		$book_regex = '(?:'. $book_list .'|'. $abbrev_list .')\.?';

		$verse_substr_regex = '(?:[:.][0-9]{1,3})?(?:[-&,;]\s?[0-9]{1,3}(?!\s?'. $book_regex .'))*';
	
		$verse_regex = '[0-9]{1,3}(?:'. $verse_substr_regex .')+';

		$passage_regex = '/\b(?:('. $volume_list .')\s?)?('. $book_regex .')\s('. $verse_regex .')/i';
		
		$text = preg_replace_callback(
			$passage_regex, 
			array($this, 'assemble_ref'),
			$text
		);

		return $text;
	}

	public function assemble_ref($matches)
	{
		$volume = $matches[1];
		$book = $matches[2];
		$verse = $matches[3];

		$esvref = get_option('esv_ref_action', 'link');

		if ($volume) {
			$volume = str_replace('III','3',$volume);
			$volume = str_replace('Third','3',$volume);
			$volume = str_replace('II','2',$volume);
			$volume = str_replace('Second','2',$volume);
			$volume = str_replace('I','1',$volume);
			$volume = str_replace('First','1',$volume);
			$volume = $volume{0}; // will remove st,nd,and rd (presupposes regex is correct)
		}

		// Note the original reference
		$original_ref = $matches[0];

		// Check to see if we've matched "chapter" or "verse"
		if (stristr($matches[0], "verse")) {
			if (!empty($this->lastBook)) {
				$setChapter = (!empty($this->lastChapter)) ? $this->lastChapter : "1";

				$reference = $this->lastBook ." ". $setChapter .":". $verse;
			} else {
				return $matches[0];
			}
		} else if (stristr($matches[0], "chapter")) {
			if (!empty($this->lastBook)) {
				$reference = $this->lastBook ." ". $verse;
			} else {
				return $matches[0];
			}
		} else {
			if (!empty($volume)) {
				$this->lastBook = $volume ." ". $book;
			} else {
				$this->lastBook = $book;
			}

			// Try to extract a chapter for $this->lastChapter
			// Split for ranges
			$ranges = explode('-', $verse);

			// Find the last range that has a chapter
			$ranges = array_reverse($ranges);

			foreach ($ranges as $range) {
				if (strstr($range, ':') !== false) {
					$lastRange = $range;
					break;
				} else {
					$lastRange = $range;
				}
			}

			$chaptersplit = explode(':', $lastRange);
			$this->lastChapter = $chaptersplit[0];

			// Set the complete reference

			$reference = $this->lastBook ." ". $verse;
		}

		$reference = trim($reference);

		// Check to make sure books requiring a volume have one.
		$book_vol_regex = '/\b(Samuel|Kings|Chronicles|Corinthians|Thessalonians|Timothy|Peter|Sam|Ki?n|Chr(?:on?)?|Cor|The?|Thess?|Ti?m|Pe?t)\b/i';
		if (preg_match($book_vol_regex, $book, $matches)) {
			if (empty($volume)) {
				// This isn't a Bible passage, return without further modifications.
				return $reference;
			}
		}
		
		if (get_option('esv_process_ref', 'runtime') == "save") {
			return $this->buildLink($reference);
		} else {
			return $this->formatReference(array('reference' => $reference, 'linktext' => $original_ref));
		}
	}

	// Builds the link used when modifying the post in the database
	public function buildLink($reference, $header="on", $format="", $linktext="")
	{
		if ($format == "") {
			$format = get_option('esv_ref_action', 'link');
		}

		if ($linktext == "") {
			$linktext = $reference;
		}

		$newref = "";
		$versehref = $this->getScriptureLink($reference);
		$linkhead = '<a href="'. $versehref .'" class="bibleref" title="'. $reference .'"';
		$linkfoot = $linktext ."</a>";

		switch ($format) {
			case "tooltip":
				$linkhead .= ' esv_reference="'. $reference .'" esv_header="'. $header .'" esv_format="tooltip">';
				break;
			case "inline":
				$linkhead .= ' esv_reference="'. $reference .'" esv_header="'. $header .'" esv_format="inline">';
				break;
			case "block":
				$linkhead .= ' esv_reference="'. $reference .'" esv_header="'. $header .'" esv_format="block">';
				break;
			case "link":
				$linkhead .= ' esv_reference="'. $reference .'" esv_header="'. $header .'" esv_format="link">';
				break;
			case "ignore":
				$linkhead = $reference;
				$linkfoot = '';
				break;
			default:
				$linkhead .= '>';
				break;
		}

		return $linkhead . $linkfoot;
	}

	public function getScriptureLink($reference)
	{
		// Biblia doesn't recognize the entity for :. Change : to . Using . as separator seems
		// to be recognized by all sites.
		$reference = str_replace(":", ".", $reference);
		$reference = rawurlencode($reference);
		$scriptureLink = "";
		
		switch(get_option('esv_scriptureSite', 'esvonline')) {
			case "esvonline":
				$scriptureLink = "http://www.esvbible.org/search/". $reference ."/";
				break;
				
			case "biblia":
				$scriptureLink = "http://biblia.com/bible/esv/". $reference;
				break;
				
			case "biblegateway":
				$scriptureLink = "http://www.biblegateway.com/passage/?search=". $reference ."&version=ESV";
				break;
		}
		
		return $scriptureLink;
	}

	public function formatReference($parameters)
	{
		$reference = (isset($parameters['reference'])) ? $parameters['reference'] : '';
		$header = (isset($parameters['header'])) ? $parameters['header'] : 'on';
		$format = (isset($parameters['format'])) ? $parameters['format'] : get_option('esv_ref_action', 'link');
		$linktext = (isset($parameters['linktext'])) ? $parameters['linktext'] : $reference;
		
		switch ($format) {
			case "tooltip":
				$VerseText = $this->getVerse($reference, "tooltip", $header, $linktext);
				break;
			case "inline":
				$VerseText = $this->getVerse($reference, "inline", $header);
				break;
			case "block":
				$VerseText = $this->getVerse($reference, "block", $header);
				break;
			case "link":
				if (get_option('esv_linkWindow', 'same') == 'new') {
					$esv_linkTarget = ' target="_blank"';
				} else {
					$esv_linkTarget = '';
				}
				
				$VerseText = '<a class="bibleref" title="'. $reference .'" href="'. $this->getScriptureLink($reference) .'"'. $esv_linkTarget .'>'. $linktext .'</a>';
				break;
			case "ignore":
				$VerseText = $reference;
				break;
		}

		return $VerseText;
	}

	public function getVerse($reference, $format, $header, $linktext="")
	{
		global $wpdb, $table_prefix, $doing_rss;
		$table_name = $table_prefix . "esv";

		// use $readerr for error checking below
		$readerr = 0;

		$ESVKey = get_option('esv_webkey', '');
		$url_reference = urlencode($reference);

		// See if we have this cached already
		$query = "SELECT Reference, Verse FROM ". $table_name ." WHERE Reference = '". $reference ."';";
		$result = $wpdb->get_row($query, ARRAY_A);

		if ($result['Reference'] == $reference) {
			$VerseText = stripslashes($result['Verse']);
		} else {
			// Build the options string based on stored options
			$options = "include-passage-references=". get_option('esv_include_reference', 'true') ."&include-first-verse-numbers=". get_option('esv_first_verse_num', 'true') ."&include-verse-numbers=". get_option('esv_verse_num', 'true') ."&include-footnotes=". get_option('esv_footnote', 'false') ."&include-footnote-links=". get_option('esv_footnote_link', 'false') ."&include-headings=". get_option('esv_incl_headings', 'false') ."&include-subheadings=". get_option('esv_incl_subheadings', 'false') ."&include-surrounding-chapters=". get_option('esv_surround_chap', 'false') ."&include-audio-link=". get_option('esv_inc_audio', 'false') ."&audio-format=". get_option('esv_audio_fmt', 'flash') ."&audio-version=". get_option('esv_audio_src', 'mm') ."&include-short-copyright=". get_option('esv_incl_short_copyright', 'true') ."&include-copyright=". get_option('esv_incl_copyright', 'false') ."&include-word-ids=". get_option('esv_incl_word_ids', 'false');
			$VerseText = "";

			$url = "http://www.esvapi.org/v2/rest/passageQuery?key=". $ESVKey ."&passage=". $url_reference ."&". $options;

			if (function_exists("curl_init")) {
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$VerseText = curl_exec($ch);
				curl_close($ch);
			} else if (ini_get('allow_url_fopen') == true) {
				if ($rvs = fopen($url, 'r')) {
					$VerseText = "";

					while (!feof($rvs)) {
						$VerseText .= fgets($rvs);
					}

					fclose($rvs);
				}
			} else {
				$readerr = 1;
				$VerseText = "Error retrieving passage text - curl and remote fopen both seem to be disabled on your host.";
			}

			if ($readerr == 0) {
				if (strpos($VerseText, "ERROR: You have exceeded your quota") === false) {
					$query = "INSERT INTO ". $table_name ." (Reference, Verse, Added) VALUES ('". $reference ."', '". addslashes($VerseText) ."', NOW());";
					$wpdb->query($query);
				} else {
					$VerseText = "Error retrieving Bible passage. Please check again tomorrow.";
				}
			}
		}

		if ($linktext == "") {
			$linktext = $reference;
		}

		// Format the verse
		if ($format == "tooltip" && $doing_rss != 1) {
			$VerseText = str_replace("\n", "", $VerseText);
			$VerseText = str_replace("\r", "", $VerseText);
			$VerseText = str_replace("'", "&#8217;", $VerseText);
			
			if (get_option('esv_inc_audio', 'false') == "true") {
				// Extract the listen link if it is an <object>
				preg_match_all('/(\<object(?:[^>]+)?\>.*\<\/object\>)/', $VerseText, $matchListens);
				$listenLink = $matchListens[1][0];
				
				// Do we need to try another way?
				if (empty($listenLink)) {
					preg_match_all('/\<h2\>([^<]+)\s?(?:\<small(?:[^>]+)?\>\((.*)\)\<\/small\>)?\<\/h2\>/', $VerseText, $matchItems);
					$listenLink = $matchItems[2][0];
				}
			}

			preg_match('/<div class="esv">(.*?)<div class="esv-text">/i', $VerseText, $matches);
			$VerseRef = $matches[0];
			$VerseRef = strip_tags($VerseRef, "");
			$VerseText = preg_replace('/<div class="esv">(.*?)<div class="esv-text">/i', '', $VerseText);
			
			if (get_option('esv_inc_audio', 'false') == "true" && $listenLink != "") {
				$VerseText = "<br />". $listenLink . $VerseText;
			}

			if ($header == "on") {
				$headertext = trim($reference);
			} else {
				$headertext = "";
			}
			
			// Get a formatted Tippy link
			global $tippy;

	    	// Make sure $tippy is our object
			if (is_object($tippy)) {
				$tippyValues['header'] = 'on';
				$tippyValues['headertext'] = tippy_format_title($reference);
				$tippyValues['title'] = tippy_format_title($linktext);
				$tippyValues['href'] = $this->getScriptureLink($reference);
				$tippyValues['text'] = tippy_format_text($VerseText);
				$tippyValues['class'] = 'esv';
				$tippyValues['item'] = 'esv';
				
				$tippyLink = $tippy->getLink($tippyValues);
				$ReturnText = '<cite class="bibleref" title="'. $reference .'" style="display: none;"></cite>'. $tippyLink;
			} else {
				// Either no Tippy or it's an old version. Produce a link only.
				if (get_option('esv_linkWindow', 'same') == "new") {
					$esv_linkTarget = ' target="_blank"';
				} else {
					$esv_linkTarget = '';
				}
				
				$ReturnText = '<a class="bibleref" title="'. $reference .'" href="'. $this->getScriptureLink($reference) .'"'. $esv_linkTarget .'>'. $linktext .'</a>';
			}
		} else if ($format == "tooltip" && $doing_rss == 1) {
			$ReturnText = '<cite class="bibleref" title="'. $reference .'">'. $linktext .'</cite>';
		} else if ($format == "inline" || $format == "block") {
			// Extract the listen link if it is an <object>
			preg_match_all('/(\<object(?:[^>]+)?\>.*\<\/object\>)/', $VerseText, $matchListens);
			$listenLink = $matchListens[1][0];
			$VerseText = preg_replace('/\<object(?:[^>]+)?\>.*\<\/object\>/', '', $VerseText);
			
			preg_match_all('/\<h2\>([^<]+)\s?(?:\<small(?:[^>]+)?\>\((.*)\)\<\/small\>)?\<\/h2\>/', $VerseText, $matchItems);
			$VerseText = preg_replace('/\<h2\>([^<]+)\s?(?:\<small(?:[^>]+)?\>\((.*)\)\<\/small\>)?\<\/h2\>/', "<span class='esv_inline_header'></span>", $VerseText);

			$verseRef = $matchItems[1][0];
			
			if (empty($listenLink)) {
				$listenLink = $matchItems[2][0];
			}

			if ($header == "on") {
				if (get_option('esv_include_reference', 'true') == "true") {
					if (get_option('esv_linkWindow', 'same') == "new") {
						$esv_linkTarget = ' target="_blank"';
					} else {
						$esv_linkTarget = '';
					}
					
					$VerseText = preg_replace('/\<span class=\'esv_inline_header\'\>\<\/span\>/', "<span style='font-size: larger; font-weight: bold;'><a class=\"bibleref\" title=\"". $reference ."\" href=\"". $this->getScriptureLink($reference) ."\"". $esv_linkTarget .">". $verseRef ."</a></span><span class='esv_inline_header'></span>", $VerseText);
				}

				if (get_option('esv_inc_audio', 'false') == "true" && $listenLink != "") {
					$VerseText = preg_replace('/\<span class=\'esv_inline_header\'\>\<\/span\>/', "<span style='font-size: smaller;'>". $listenLink ."</span>", $VerseText);
				}
			} else if ($format != "ignore") {
				$VerseText = "<cite class=\"bibleref\" title=\"". $reference ."\" style=\"display: none;\">". $reference ."</cite>". $VerseText;
			} else {
                return $reference;
            }

			if ($format == "block") {
				$ReturnText = "<blockquote class=\"esvblock\">". $VerseText ."</blockquote>";
			} else {
				$ReturnText = "<div class='esvblock'>". $VerseText ."</div><br />";
			}
		}

		return $ReturnText;
	}

	public function display()
	{
		wp_register_style('ESV_CSS', get_bloginfo('wpurl') .'/wp-content/plugins/esv-plugin/esv.css');
		wp_enqueue_style('ESV_CSS');
	}

	// Check settings and see if we need to initialize the plugin or update any
	// new options.
	public function esv_activate()
	{
		global $wpdb;
		
		// Set all the default options, starting with creating the table to
		// store ESV passages.
		$table_name = $wpdb->prefix . "esv";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE ". $table_name ." (
			Reference tinytext,
			Verse     blob,
			Added     datetime
            );";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			update_option("esv_webkey", "IP");
			
			update_option("esv_include_reference", "true");
			update_option("esv_first_verse_num", "true");
			update_option("esv_verse_num", "true");
			update_option("esv_footnote", "false");
			update_option("esv_footnote_link", "false");
			update_option("esv_incl_headings", "false");
			update_option("esv_incl_subheadings", "false");
			update_option("esv_surround_chap", "false");
			update_option("esv_inc_audio", "false");
			update_option("esv_audio_fmt", "flash");
			update_option("esv_incl_short_copyright", "true");
			update_option("esv_incl_copyright", "false");
			update_option("esv_ref_action", "link");
			update_option("esv_show_header", "true");
			update_option("esv_process_ref", "runtime");
			update_option("esv_audio_src", "mm");
			update_option("esv_incl_word_ids", "false");
		}
	}
}

$esv_plugin = new ESV_Plugin();

?>