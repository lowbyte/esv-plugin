<?php
if (! function_exists('esv_options_subpanel')) {
	function esv_options_subpanel() {
		global $wpdb;

		$table_name = $wpdb->prefix . "esv";

		if (isset($_GET['action']) && !isset($_POST['info_update']))
		{
			if ($_GET['action'] == "esv_purgedbase")
			{
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
				{
					echo '<div class="updated"><p><strong>Purge Error: ESV database table not found.</strong></p></div>';
				} else {
					$query = "DELETE FROM ". $table_name .";";
					$wpdb->query($query);
					
					echo '<div class="updated"><p><strong>Stored ESV passages have been cleared.</strong></p></div>';
				}
			}
		}

		if (isset($_GET['action']) && !isset($_POST['info_update']))
		{
			if ($_GET['action'] == "esv_purgeall")
			{
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
					echo '<div class="updated"><p><strong>Purge Error: ESV database table not found.</strong></p></div>';
				} else {
					$query = "DROP TABLE ". $table_name .";";
					$wpdb->query($query);

					$query = "DELETE FROM wp_options WHERE option_name LIKE 'esv%';";
					$wpdb->query($query);
					
					echo '<div class="updated"><p><strong>All ESV Plugin data have been cleared. To re-load, visit the ESV Options page again.</strong></p></div>';
				}
			}
		}
		
		if ($_GET['action'] != "esv_purgeall")
		{
			// Check all of our settings
			esv_activate();
		}

		if (isset($_POST['info_update'])) {
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
            update_option('esv_audio_src', $_POST['esv_audio_src']);
			update_option('esv_incl_short_copyright', $_POST['esv_incl_short_copyright']);
			update_option('esv_incl_copyright', $_POST['esv_incl_copyright']);
			update_option('esv_ref_action', $_POST['esv_ref_action']);
			update_option('esv_show_header', $_POST['esv_show_header']);
			update_option('esv_process_ref', $_POST['esv_process_ref']);
            update_option('esv_incl_word_ids', $_POST['esv_incl_word_ids']);
            update_option('esv_linkWindow', $_POST['esv_linkWindow']);
            update_option('esv_scriptureSite', $_POST['esv_scriptureSite']);
			
			echo '<div class="updated"><p><strong>Your options have been updated.</strong></p></div>';
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
			<a href="#WebKey">ESV Web Key</a><br />
			<a href="#Retrieval">Passage Retrieval Options</a><br />
			<a href="#Format">Format Options</a><br />
			<a href="#LinkTarget">Link Target</a><br />
			<a href="#ScriptureSite">Scripture Site</a><br />
			<a href="#Settings">Manage Database/Settings</a><br />
			<a href="#Using">How to use</a><br />
		</div>

		<br />

		Help for the options can be found at the <a href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/">ESV Plugin Options</a> page.
	</div>

	<span class="esvOptionLabel"><a name="WebKey"></a>ESV Web Key</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/#webkey">Help</a>)</span><br />
	<div class="esvOptionSection">
		Visit the <a href="http://www.gnpcb.org/esv/share/services/">ESV Bible Web Service</a> to obtain a personal key.<br /><br />

		<div class="esvOptions">
			<label for="esv_webkey">ESV Web key:</label> <input id="esv_webkey" type="text" name="esv_webkey" value="<?php echo get_option('esv_webkey'); ?>" size="30">
		</div>
	</div>

	<span class="esvOptionLabel"><a name="Retrieval"></a>Passage Retrieval Options</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/#retrieval">Help</a>)</span><br />
	<div class="esvOptionSection">
	    Visit the <a href="http://www.gnpcb.org/esv/share/services/api/#html">ESV Web Service API</a> for more information about these options.<br /><br />

		<div class="esvOptions">
			<input id="esv_include_reference" name="esv_include_reference" type="checkbox" value="true" <?php if(get_option('esv_include_reference', 'true') == "true") echo "checked" ?> />
				<label for="esv_include_reference" title="Display the passage reference within the retrieved text">
					include-passage-references
				</label><br />

			<input id="esv_verse_num" name="esv_verse_num" type="checkbox" value="true" <?php if(get_option('esv_verse_num', 'true') == "true") echo "checked" ?> />
				<label for="esv_verse_num" title="Show the verse numbers with the text">
					include-verse-numbers
				</label><br />

			<input id="esv_first_verse_num" name="esv_first_verse_num" type="checkbox" value="true" <?php if(get_option('esv_first_verse_num', 'true') == "true") echo "checked" ?> />
				<label for="esv_first_verse_num" title="Show the first verse number of a chapter">
					include-first-verse-numbers
				</label><br />

			<input id="esv_footnote" name="esv_footnote" type="checkbox" value="true" <?php if(get_option('esv_footnote', 'false') == "true") echo "checked" ?> />
				<label for="esv_footnote" title="Include footnotes with the text">
					include-footnotes
				</label><br />

			<input id="esv_footnote_link" name="esv_footnote_link" type="checkbox" value="true" <?php if(get_option('esv_footnote_link', 'false') == "true") echo "checked" ?> />
				<label for="esv_footnote_link" title="Footnote references in the text are links to the footnote">
					include-footnote-links
				</label><br />

			<input id="esv_incl_headings" name="esv_incl_headings" type="checkbox" value="true" <?php if(get_option('esv_incl_headings', 'false') == "true") echo "checked" ?> />
				<label for="esv_incl_headings" title="Display section headings">
					include-headings
				</label><br />

			<input id="esv_incl_subheadings" name="esv_incl_subheadings" type="checkbox" value="true" <?php if(get_option('esv_incl_subheadings', 'false') == "true") echo "checked" ?> />
				<label for="esv_incl_subheadings" title="Display sub-headings such as those in Psalms">
					include-subheadings
				</label><br />

			<input id="esv_surround_chap" name="esv_surround_chap" type="checkbox" value="true" <?php if(get_option('esv_surround_chap', 'false') == "true") echo "checked" ?> />
				<label for="esv_surround_chap" title="Show prev and next links to surrounding passages">
					include-surrounding-chapters
				</label><br />

			<input id="esv_incl_short_copyright" name="esv_incl_short_copyright" type="checkbox" value="true" <?php if(get_option('esv_incl_short_copyright', 'true') == "true") echo "checked" ?> />
				<label for="esv_incl_short_copyright" title="Display a short ESV copyright notice">
					include-short-copyright
				</label><br />

			<input id="esv_incl_copyright" name="esv_incl_copyright" type="checkbox" value="true" <?php if(get_option('esv_incl_copyright', 'false') == "true") echo "checked" ?> />
				<label for="esv_incl_copyright" title="Display a longer ESV copyright notice">
					include-copyright
				</label><br />

            <input id="esv_incl_word_ids" name="esv_incl_word_ids" type="checkbox" value="true" <?php if(get_option('esv_incl_word_ids', 'false') == "true") echo "checked" ?> />
                <label for="esv_incl_word_ids" title="Includes an id with each word. See the API documentation.">
                    include-word-ids
                </label><br />

			<br />
			Audio format: <br />

			<input id="esv_inc_audio" name="esv_inc_audio" type="checkbox" value="true" <?php if(get_option('esv_inc_audio', 'false') == "true") echo "checked" ?> />
				<label for="esv_inc_audio" title="Display a link to play the audio for this passage">
					include-audio-link
				</label><br />

            <input id="esv_audio_fmt_flash" name="esv_audio_fmt"  type="radio" value="flash" <?php if(get_option('esv_audio_fmt', 'flash') == "flash") echo "checked" ?> />
                <label for="esv_audio_fmt_flash" title="Use Flash format">
                    Flash Format
                </label>

            <input id="esv_audio_fmt_mp3" name="esv_audio_fmt" type="radio" value="mp3" <?php if(get_option('esv_audio_fmt', 'flash') == "mp3") echo "checked" ?> />
                <label for="esv_audio_fmt_mp3" title="Use MP3 format">
                    MP3 Format
                </label>

			<input id="esv_audio_fmt_real" name="esv_audio_fmt"  type="radio" value="real" <?php if(get_option('esv_audio_fmt', 'flash') == "real") echo "checked" ?> />
				<label for="esv_audio_fmt_real" title="Use Real Media format">
					Real Format
				</label>

			<input id="esv_audio_fmt_wma" name="esv_audio_fmt" type="radio" value="wma" <?php if(get_option('esv_audio_fmt', 'flash') == "wma") echo "checked" ?> />
				<label for="esv_audio_fmt_wma" title="Use Windows Media format">
					WMA Format
				</label><br />

            <br />
            Audio source: <br />
            For this to work Audio Format must be set to Flash or MP3.<br />

            <input id="esv_audio_src_mm" name="esv_audio_src" type="radio" value="mm" <?php if(get_option('esv_audio_src', 'mm') == "mm") echo "checked" ?> />
                <label for="esv_audio_src_mm" title="The Bible as read by MaxMcLean">
                    Max McLean (Whole Bible)
                </label><br />

            <input id="esv_audio_src_ml" name="esv_audio_src" type="radio" value="ml" <?php if(get_option('esv_audio_src', 'mm') == "ml") echo "checked" ?> />
                <label for="esv_audio_src_ml" title="The New Testament as read by Marquis Laughlin">
                    Marquis Laughlin (New Testament Only)
                </label><br />

            <input id="esv_audio_src_mm_ml" name="esv_audio_src" type="radio" value="ml-mm" <?php if(get_option('esv_audio_src', 'mm') == "ml-mm") echo "checked" ?> />
                <label for="esv_audio_src_mm_ml" title="The OT read by Max McLean, the NT read by Marquis Laughlin">
                    Mixed (McLean OT, Laughlin NT)
                </label><br />
		</div>
	</div>

	<span class="esvOptionLabel"><a name="Format"></a>Format Options</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/#reference">Help</a>)</span><br />
	<div class="esvOptionSection">
		<div class="esvOptions">
			<input id="esv_ref_action_1" name="esv_ref_action" type="radio" value="tooltip" <?php if(get_option('esv_ref_action', 'link') == "tooltip") echo "checked" ?> />
				<label for="esv_ref_action_1" title="Mousing over the passage reference displays a tooltip containing the passage text">
					Tooltip
				</label> (To use the tooltip you must have the <a href="http://www.musterion.net/tippy/">Tippy</a> plugin installed.)<br />

			<input id="esv_ref_action_2" name="esv_ref_action" type="radio" value="inline" <?php if(get_option('esv_ref_action', 'link') == "inline") echo "checked" ?> />
				<label for="esv_ref_action_2" title="The passage text is displayed on the line following the reference">
					Inline
				</label><br />

			<input id="esv_ref_action_3" name="esv_ref_action" type="radio" value="block" <?php if(get_option('esv_ref_action', 'link') == "block") echo "checked" ?> />
				<label for="esv_ref_action_3" title="The passage text is displayed within the post as a block element">
					Blockquote
				</label><br />

			<input id="esv_ref_action_4" name="esv_ref_action" type="radio" value="link" <?php if(get_option('esv_ref_action', 'link') == "link") echo "checked" ?> />
				<label for="esv_ref_action_4" title="The reference is made a link pointing to the ESV website">
					Link to ESV
				</label><br />

			<input id="esv_ref_action_5" name="esv_ref_action" type="radio" value="ignore" <?php if(get_option('esv_ref_action', 'link') == "ignore") echo "checked" ?> />
				<label for="esv_ref_action_5" title="Nothing is done with the reference">
					Do Nothing
				</label><br />
		</div>
	</div>
	
	<span class="esvOptionLabel"><a name="LinkTarget"></a>Link Target</span> <span style="padding-left: 8px;"></span><br />
	<div class="esvOptionSection">
		Do you want Scripture links to open in a new window or the current window? (When using the tooltip, link target is specified under Tippy's settings)<br /><br />
		<div class="esvOptions">
			<input id="esv_linkWindow_same" name="esv_linkWindow" type="radio" value="same" <?php if(get_option('esv_linkWindow', 'same') == "same") echo "checked" ?> />
				<label for="esv_linkWindow_same" title="Open Scripture links in the same window">
					Open Scripture links in the same window
				</label><br />

			<input id="esv_linkWindow_new" name="esv_linkWindow" type="radio" value="new" <?php if(get_option('esv_linkWindow', 'same') == "new") echo "checked" ?> />
				<label for="esv_linkWindow_new" title="Open Scripture links in a new window">
					Open Scripture links in a new window
				</label><br />
		</div>
	</div>
	
	<span class="esvOptionLabel"><a name="ScriptureSite"></a>Scripture Site</span> <span style="padding-left: 8px;"></span><br />
	<div class="esvOptionSection">
		What website do you want your Scripture links to point to?<br /><br />
		<div class="esvOptions">
			<input id="esv_scriptureSite_Crossway" name="esv_scriptureSite" type="radio" value="esvonline" <?php if(get_option('esv_scriptureSite', 'esvonline') == "esvonline") echo "checked" ?> />
				<label for="esv_scriptureSite_Crossway" title="Open Scripture links in the same window">
					<a href="http://www.esvbible.org/">ESV Online</a> from <a href="http://www.crossway.org/">Crossway</a>
				</label><br />

			<input id="esv_scriptureSite_Biblia" name="esv_scriptureSite" type="radio" value="biblia" <?php if(get_option('esv_scriptureSite', 'esvonline') == "biblia") echo "checked" ?> />
				<label for="esv_scriptureSite_Biblia" title="Open Scripture links in a new window">
					<a href="http://biblia.com/">Biblia</a> from <a href="http://www.logos.com/">Logos Bible Software</a>
				</label><br />
				
			<input id="esv_scriptureSite_Gateway" name="esv_scriptureSite" type="radio" value="biblegateway" <?php if(get_option('esv_scriptureSite', 'esvonline') == "biblegateway") echo "checked" ?> />
				<label for="esv_scriptureSite_Gateway" title="Open Scripture links in a new window">
					<a href="http://www.biblegateway.com/">Bible Gateway</a> from <a href="http://www.gospel.com/">Gospel.com</a>
				</label><br />
		</div>
	</div>

	<span class="esvOptionLabel">When to Process References</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/#process">Help</a>)</span><br />
	<div class="esvOptionSection">
		Should Scripture references be found and formatted when you save a post, or when visitors come to your site?<br /><br />

		<div class="esvOptions">
			<input id="esv_process_ref_1" name="esv_process_ref" type="radio" value="runtime" <?php if(get_option('esv_process_ref', 'runtime') == "runtime") echo "checked" ?> />
				<label for="esv_process_ref_1" title="This will modify only what is sent to the visitor when they view your site">
                    Dynamic: Find and format Bible passages when visitors come to your site
				</label><br />

			<input id="esv_process_ref_2" name="esv_process_ref" type="radio" value="save" <?php if(get_option('esv_process_ref', 'runtime') == "save") echo "checked" ?> />
				<label for="esv_process_ref_2" title="This will modify your posts when you save them">
                    Static: Find and format Bible passages when saving the post
				</label><br />

		</div>
	</div>

	<span class="esvOptionLabel"><a name="Settings"></a>Manage Database/Settings</span> <span style="padding-left: 8px;">(<a target="_blank" href="http://croberts.me/2011/05/28/wordpress-esv-plugin-options-3-6-x/#manage">Help</a>)</span><br />
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
?>