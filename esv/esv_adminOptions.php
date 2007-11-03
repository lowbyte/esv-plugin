<?php
if (! function_exists('esv_options_subpanel')) {
	function esv_options_subpanel() {
		global $wpdb, $table_prefix, $ESV_Version;

		$table_name = $table_prefix . "esv";

		// Set all the default options, starting with creating
		// the ESV passage table
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			esv_install_table();

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
  		?>
  		<div class="updated"><p><strong>ESV database table has been created.</strong></p></div>
  		<?php
			} else {
  		?>
  		<div class="updated"><p><strong>Unknown error trying to create ESV database table!</strong></p></div>
  		<?php
			}
		}

		// Versions are stored as strings. Reformat them so we can compare them.
		// Starting with version 2.0.5, internal version numbers will always have
		// at least three numbers, even if that means version numbers like
		// 2.1.0 or 3.0.0
		$oldvers = str_replace(".", "", get_option('esv_version'));
		$curvers = str_replace(".", "", $ESV_Version);

		// See if a 1.x version is installed
		if (get_option('esv_audio_fmt') != "" && $oldvers == "")
		{
			$oldvers = 100;
		}

		if ($oldvers < $curvers)
		{
			// Is this a new install? Load default data
			if ($oldvers == "" || $oldvers == 0)
			{
				update_option('esv_include_reference', 'true');
				update_option('esv_first_verse_num', 'true');
				update_option('esv_verse_num', 'true');
				update_option('esv_footnote', 'false');
				update_option('esv_footnote_link', 'false');
				update_option('esv_incl_headings', 'false');
				update_option('esv_incl_subheadings', 'false');
				update_option('esv_surround_chap', 'false');
				update_option('esv_inc_audio', 'true');
				update_option('esv_audio_fmt', 'real');
				update_option('esv_incl_short_copyright', 'true');
				update_option('esv_incl_copyright', 'false');
				update_option('esv_ref_action', 'link');
				update_option('esv_show_header', 'true');
				update_option('esv_process_ref', 'runtime');
				update_option('esv_backward_compat', 'false');
				update_option('esv_parse_saved', 'false');
			} else if ($oldvers < 310) {
				update_option('esv_process_ref', 'runtime');
				update_option('esv_backward_compat', 'false');
				update_option('esv_parse_saved', 'false');
			} else if ($oldvers < 210) {
				update_option('esv_show_header', 'true');
			}

			update_option('esv_version', $ESV_Version);
		}

		if (isset($_GET['action']) && !isset($_POST['info_update'])) {
			if ($_GET['action'] == "esv_purgedbase") {
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      	  ?>
	  	  <div class="updated"><p><strong>Purge Error: ESV database table not found.</strong></p></div>
	 	  <?php
				} else {
					$query = "DELETE FROM ". $table_name .";";
					$wpdb->query($query);

	  	  ?>
     	  <div class="updated"><p><strong>Stored ESV passages have been cleared.</strong></p></div>
	  	  <?php
				}
			}
		}

		if (isset($_GET['action']) && !isset($_POST['info_update'])) {
			if ($_GET['action'] == "esv_purgeall") {
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      	  ?>
	  	  <div class="updated"><p><strong>Purge Error: ESV database table not found.</strong></p></div>
	 	  <?php
				} else {
					$query = "DROP TABLE ". $table_name .";";
					$wpdb->query($query);

					$query = "DELETE FROM wp_options WHERE option_name LIKE 'esv%';";
					$wpdb->query($query);

	  	  ?>
     	  <div class="updated"><p><strong>All ESV Plugin data have been cleared. To re-load, visit the ESV Options page again.</strong></p></div>
	  	  <?php
				}
			}
		}

		if (isset($_POST['info_update'])) {
			if ($_POST['esv_include_reference'] == "") {
				$_POST['esv_include_reference'] = "false";
			}

			if ($_POST['esv_first_verse_num'] == "") {
				$_POST['esv_first_verse_num'] = "false";
			}

			if ($_POST['esv_verse_num'] == "") {
				$_POST['esv_verse_num'] = "false";
			}

			if ($_POST['esv_footnote'] == "") {
				$_POST['esv_footnote'] = "false";
			}

			if ($_POST['esv_footnote_link'] == "") {
				$_POST['esv_footnote_link'] = "false";
			}

			if ($_POST['esv_incl_headings'] == "") {
				$_POST['esv_incl_headings'] = "false";
			}

			if ($_POST['esv_incl_subheadings'] == "") {
				$_POST['esv_incl_subheadings'] = "false";
			}

			if ($_POST['esv_surround_chap'] == "") {
				$_POST['esv_surround_chap'] = "false";
			}

			if ($_POST['esv_inc_audio'] == "") {
				$_POST['esv_inc_audio'] = "false";
			}

			if ($_POST['esv_incl_short_copyright'] == "") {
				$_POST['esv_incl_short_copyright'] = "false";
			}

			if ($_POST['esv_incl_copyright'] == "") {
				$_POST['esv_incl_copyright'] = "false";
			}

			if ($_POST['esv_checkupdates'] == "") {
				$_POST['esv_checkupdates'] = "false";
			}

			if ($_POST['esv_show_header'] == "") {
				$_POST['esv_show_header'] = "false";
			}

			if ($_POST['esv_backward_compat'] == "") {
				$_POST['esv_backward_compat'] = "false";
			}

			if ($_POST['esv_parse_saved'] == "") {
				$_POST['esv_parse_saved'] = "false";
			}

			update_option('esv_webkey', $_POST['esv_webkey']);
			update_option('esv_include_reference', $_POST['esv_include_reference']);
			update_option('esv_first_verse_num', $_POST['esv_first_verse_num']);
			update_option('esv_verse_num', $_POST['esv_verse_num']);
			update_option('esv_footnote', $_POST['esv_footnote']);
			update_option('esv_footnote_link', $_POST['esv_footnote_link']);
			update_option('esv_incl_headings', $_POST['esv_incl_headings']);
			update_option('esv_incl_subheadings', $_POST['esv_incl_subheadings']);
			update_option('esv_surround_chap', $_POST['esv_surround_chap']);
			update_option('esv_inc_audio', $_POST['esv_inc_audio']);
			update_option('esv_audio_fmt', $_POST['esv_audio_fmt']);
			update_option('esv_incl_short_copyright', $_POST['esv_incl_short_copyright']);
			update_option('esv_incl_copyright', $_POST['esv_incl_copyright']);
			update_option('esv_ref_action', $_POST['esv_ref_action']);
			update_option('esv_show_header', $_POST['esv_show_header']);
			update_option('esv_process_ref', $_POST['esv_process_ref']);
			update_option('esv_backward_compat', $_POST['esv_backward_compat']);
			update_option('esv_parse_saved', $_POST['esv_parse_saved']);

	      ?>
		  <div class="updated"><p><strong>Your options have been updated.</strong></p></div>
		  <?php
		}

?>

<style type="text/css">
div.esvOptionSection {
 margin-bottom: 20px;
 margin-left: 10px;
}

span.esvOptionLabel {
 font-size: 16px;
}

div.esvOptions {
 margin-left: 15px;
}

div.esvOptionLeft {
 width: 125px;
 float: left;
}

div.esvOptionRight {
 float: left;
}

div.clearOptions {
 margin-bottom: 5px;
 clear: both;
}
</style>

<div class="wrap">
	<h2>ESV Web Retrieval Options</h2>
	<form method="post">

	<div class="esvOptionSection">
		Options are grouped into the following sections:<br /><br />
		<div class="esvOptions">
			<a href="#uptodate">Up-to-date check</a><br />
			<a href="#WebKey">ESV Web Key</a><br />
			<a href="#Retrieval">Passage Retrieval Options</a><br />
			<a href="#Format">Format Option</a><br />
			<a href="#Settings">Manage Database/Settings</a><br />
			<a href="#Using">How to use</a><br />
		</div>

		<br />

		There are several options for the plugin. You should be safe leaving everything at their defaults, though you will probably want to glance over the Passage Retrieval options and may want to change the default Format option.<br /><br />
		Help for the options can be found at the <a href="http://www.musterion.net/wordpress-esv-plugin/wordpress-esv-plugin-options/">ESV Plugin Options</a> page.
	</div>

	<span class="esvOptionLabel"><a name="uptodate"></a>Up to date check</span><br />
	<div class="esvOptionSection">
		<?php
		$currentVersion = esv_check_version();

		if ($currentVersion != $ESV_Version)
		{
			$curvers = str_replace(".", "", $currentVersion);
	    	$thisvers = str_replace(".", "", $ESV_Version);

			if ($curvers > $thisvers)
			{
				?>
					<b>You are not up to date</b>. Latest version of the plugin is <?php echo $currentVersion; ?> and you have version <?php echo $ESV_Version; ?> installed. Visit the <a href="http://www.musterion.net/wordpress-esv-plugin/">WordPress ESV Plugin</a> page to download the update.<br /><br />
				<?php
			} else {
				?>
					<b>You are living the future!</b>. Latest version of the plugin is <?php echo $currentVersion; ?> and you have version <?php echo $ESV_Version; ?> installed. Thank you for testing this version of the plugin. Visit the <a href="http://www.musterion.net/wordpress-esv-plugin/">WordPress ESV Plugin</a> page to see plugin news.<br /><br />
				<?php
			}
		} else if ($currentVersion == "Error") {
				?>
					<br />
					Error retrieving current version number.<br /><br />
				<?php
		} else {
				?>
					<br />
					Your version of the plugin is up to date.<br /><br />
				<?php
		}
		?>
	</div>

	<span class="esvOptionLabel"><a name="WebKey"></a>ESV Web Key</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#webkey">Help</a>)</span><br />
	<div class="esvOptionSection">
		Visit the <a href="http://www.gnpcb.org/esv/share/services/">ESV Bible Web Service</a> to obtain a personal key.<br /><br />

		<div class="esvOptions">
			<label for="esv_webkey">ESV Web key:</label> <input id="esv_webkey" type="text" name="esv_webkey" value="<?php echo get_option('esv_webkey'); ?>" size="30">
		</div>
	</div>

	<span class="esvOptionLabel"><a name="Retrieval"></a>Passage Retrieval Options</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#retrieval">Help</a>)</span><br />
	<div class="esvOptionSection">
	    Visit the <a href="http://www.gnpcb.org/esv/share/services/api/#html">ESV Web Service API</a> for more information about these options.<br /><br />

		<div class="esvOptions">
			<input id="esv_include_reference" name="esv_include_reference" type="checkbox" value="true" <?php if(get_option('esv_include_reference') == "true") echo "checked" ?> />
				<label for="esv_include_reference" title="Display the passage reference within the retrieved text">
					include-passage-references
				</label><br />

			<input id="esv_verse_num" name="esv_verse_num" type="checkbox" value="true" <?php if(get_option('esv_verse_num') == "true") echo "checked" ?> />
				<label for="esv_verse_num" title="Show the verse numbers with the text">
					include-verse-numbers
				</label><br />

			<input id="esv_first_verse_num" name="esv_first_verse_num" type="checkbox" value="true" <?php if(get_option('esv_first_verse_num') == "true") echo "checked" ?> />
				<label for="esv_first_verse_num" title="Show the first verse number of a chapter">
					include-first-verse-numbers
				</label><br />

			<input id="esv_footnote" name="esv_footnote" type="checkbox" value="true" <?php if(get_option('esv_footnote') == "true") echo "checked" ?> />
				<label for="esv_footnote" title="Include footnotes with the text">
					include-footnotes
				</label><br />

			<input id="esv_footnote_link" name="esv_footnote_link" type="checkbox" value="true" <?php if(get_option('esv_footnote_link') == "true") echo "checked" ?> />
				<label for="esv_footnote_link" title="Footnote references in the text are links to the footnote">
					include-footnote-links
				</label><br />

			<input id="esv_incl_headings" name="esv_incl_headings" type="checkbox" value="true" <?php if(get_option('esv_incl_headings') == "true") echo "checked" ?> />
				<label for="esv_incl_headings" title="Display section headings">
					include-headings
				</label><br />

			<input id="esv_incl_subheadings" name="esv_incl_subheadings" type="checkbox" value="true" <?php if(get_option('esv_incl_subheadings') == "true") echo "checked" ?> />
				<label for="esv_incl_subheadings" title="Display sub-headings such as those in Psalms">
					include-subheadings
				</label><br />

			<input id="esv_surround_chap" name="esv_surround_chap" type="checkbox" value="true" <?php if(get_option('esv_surround_chap') == "true") echo "checked" ?> />
				<label for="esv_surround_chap" title="Show prev and next links to surrounding passages">
					include-surrounding-chapters
				</label><br />

			<input id="esv_incl_short_copyright" name="esv_incl_short_copyright" type="checkbox" value="true" <?php if(get_option('esv_incl_short_copyright') == "true") echo "checked" ?> />
				<label for="esv_incl_short_copyright" title="Display a short ESV copyright notice">
					include-short-copyright
				</label><br />

			<input id="esv_incl_copyright" name="esv_incl_copyright" type="checkbox" value="true" <?php if(get_option('esv_incl_copyright') == "true") echo "checked" ?> />
				<label for="esv_incl_copyright" title="Display a longer ESV copyright notice">
					include-copyright
				</label><br />

			<br />
			Audio settings: <br />

			<input id="esv_inc_audio" name="esv_inc_audio" type="checkbox" value="true" <?php if(get_option('esv_inc_audio') == "true") echo "checked" ?> />
				<label for="esv_inc_audio" title="Display a link to play the audio for this passage">
					include-audio-link
				</label><br />

			<input id="esv_audio_fmt_real" name="esv_audio_fmt"  type="radio" value="real" <?php if(get_option('esv_audio_fmt') == "real") echo "checked" ?> />
				<label for="esv_audio_fmt_real" title="Use Real Media format">
					Real Format
				</label>

			<input id="esv_audio_fmt_wma" name="esv_audio_fmt" type="radio" value="wma" <?php if(get_option('esv_audio_fmt') == "wma") echo "checked" ?> />
				<label for="esv_audio_fmt_wma" title="Use Windows Media format">
					WMA Format
				</label><br />

		</div>
	</div>

	<span class="esvOptionLabel"><a name="Format"></a>Format Option</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#reference">Help</a>)</span><br />
	<div class="esvOptionSection">
		<br />
		<div class="esvOptions">
			<input id="esv_ref_action_1" name="esv_ref_action" type="radio" value="tooltip" <?php if(get_option('esv_ref_action') == "tooltip") echo "checked" ?> />
				<label for="esv_ref_action_1" title="Mousing over the passage reference displays a tooltip containing the passage text">
					Tooltip
				</label> (To use the tooltip you must have the <a href="http://www.musterion.net/tippy/">Tippy</a> plugin installed.)<br />

			<input id="esv_ref_action_2" name="esv_ref_action" type="radio" value="inline" <?php if(get_option('esv_ref_action') == "inline") echo "checked" ?> />
				<label for="esv_ref_action_2" title="The passage text is displayed on the line following the reference">
					Inline
				</label><br />

			<input id="esv_ref_action_3" name="esv_ref_action" type="radio" value="block" <?php if(get_option('esv_ref_action') == "block") echo "checked" ?> />
				<label for="esv_ref_action_3" title="The passage text is displayed within the post as a block element">
					Blockquote
				</label><br />

			<input id="esv_ref_action_4" name="esv_ref_action" type="radio" value="link" <?php if(get_option('esv_ref_action') == "link") echo "checked" ?> />
				<label for="esv_ref_action_4" title="The reference is made a link pointing to the ESV website">
					Link to ESV
				</label><br />

			<input id="esv_ref_action_5" name="esv_ref_action" type="radio" value="ignore" <?php if(get_option('esv_ref_action') == "ignore") echo "checked" ?> />
				<label for="esv_ref_action_5" title="Nothing is done with the reference">
					Do Nothing
				</label><br /><br />
		</div>
	</div>

	<span class="esvOptionLabel">When to Process References</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#process">Help</a>)</span><br />
	<div class="esvOptionSection">
		You will likely want to read the <a href="http://www.musterion.net/?page_id=478#process">Help</a> information for these options.<br /><br />

		<div class="esvOptions">
			<input id="esv_process_ref_1" name="esv_process_ref" type="radio" value="runtime" <?php if(get_option('esv_process_ref') == "runtime") echo "checked" ?> />
				<label for="esv_process_ref_1" title="This will modify only what is sent to the visitor when they view your site">
					Process references at runtime
				</label>

			<input style="margin-left: 25px;" id="esv_parse_saved" name="esv_parse_saved" type="checkbox" value="true" <?php if(get_option('esv_parse_saved') == "true") echo "checked" ?> />
				<label for="esv_parse_saved" title="Search for and process Scripture references saved with Option Two">
					Search for and process Scripture references saved with Option Two
				</label><br />

			<input id="esv_process_ref_2" name="esv_process_ref" type="radio" value="save" <?php if(get_option('esv_process_ref') == "save") echo "checked" ?> />
				<label for="esv_process_ref_2" title="This will modify your posts when you save them">
					Process references when saving posts
				</label><br />

		</div>
	</div>

	<span class="esvOptionLabel">Backward Compatibility</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#backward">Help</a>)</span><br />
	<div class="esvOptionSection">
		<br />
		<div class="esvOptions">
			<input id="esv_backward_compat" name="esv_backward_compat" type="checkbox" value="true" <?php if(get_option('esv_backward_compat') == "true") echo "checked" ?> />
				<label for="esv_backward_compat" title="Have the plugin process old tags">
					Enable Backward Compatibility
				</label><br />
		</div>
	</div>

    <span class="esvOptionLabel"><a name="Settings"></a>Manage Database/Settings</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://www.musterion.net/?page_id=478#manage">Help</a>)</span><br />
	<div class="esvOptionSection">
		If you want to clear out all of your stored ESV passages, just click <a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=esv.php&action=esv_purgedbase">clear stored passages</a>.<br /><br />

		If you want to clear all of your ESV settings and stored passages, you can <a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=esv.php&action=esv_purgeall">clear all</a>. You might want to do this if you want to uninstall the plugin, or just to clear out old settings. After clearing you can re-load default settings by again visiting the ESV Options page.
    </div>

  	<input type="submit" name="info_update" value="Update Options" /><br /><br />

	</form>

	<span class="esvOptionLabel"><a name="Using"></a>How to use</span><br />
	<div class="esvOptionSection">
		The simplest way to use the plugin is to have it automatically detect Bible references in your post and format them based on your settings. But you can also individually format each reference using the [esvbible] tag:<br /><br />

		[esvbible reference="John 3:16" header="on" format="tooltip"]John 3:16[/esvbible]<br /><br />

		Possible values for header are: on, off<br />
		Possible values for format are: tooltip, inline, block, link, ignore<br /><br />
		All attributes are optional.

		If you include the reference attribute, you can replace the text between the tags with anything you want:<br />
		[esvbible reference="John 3:16" format="tooltip"]This is a great verse[/esvbible]<br /><br />

		If you want the plugin to ignore a particular reference, use [esvignore]:<br /><br />

		[esvignore]John 1[/esvignore]<br />

		See <a href="http://www.musterion.net/wordpress-esv-plugin/">http://www.musterion.net/wordpress-esv-plugin/</a> for further information.
	</div>
</div>

<?php
  }
}

if (! function_exists('esv_check_version')) {
	function esv_check_version() {
		$currentversion = 0;

		$url = "http://www.musterion.net/plugins/esv_plugin.txt";

		if (function_exists("curl_init")) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$currentversion = curl_exec($ch);
			curl_close($ch);
		} else if (ini_get('allow_url_fopen') == true) {
			if ($rvs = fopen($url, 'r')) {
				$currentversion = "";

				while (!feof($rvs)) {
					$currentversion .= fgets($rvs);
				}

				fclose($rvs);
			}
		} else {
			$currentversion = "Error";
		}

		return trim($currentversion);
	}
}

if (! function_exists('esv_install_table')) {
	function esv_install_table() {
		global $table_prefix, $wpdb;

		$table_name = $table_prefix . "esv";

		$sql = "CREATE TABLE ". $table_name ." (
              Reference tinytext,
              Verse     blob,
              Added     datetime
            );";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);

		update_option('esv_webkey', 'IP');
	}
}
?>