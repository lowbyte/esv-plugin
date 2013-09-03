=== ESV Plugin ===
Contributors: Columcille
Tags: esv, bible, scripture
Requires at least: 2.1.5
Tested up to: 3.6
Stable tag: 3.9.4

Automatically scans WordPress posts to replace Scripture references with a link to the ESV or with the text itself.

== Description ==

The WordPress ESV Plugin can automatically scan posts, looking for Scripture references. References can be turned into links to the text on the ESV website, can have the Scripture passage automatically included on your site, using the Tippy plugin references can be turned into tooltips containing the Scripture text, or references can be ignored.

The plugin is highly configurable, allowing the user to have control over several actions. The user is also able to style the appearance of included Scripture passages by editing the css file that comes with the plugin.

== Installation ==

Upload the esv directory to your /wp-content/plugins/ directory
Activate the plugin through your dashboard
Visit the ESV Options page under the Settings section of your dashboard. This will set up all of the initial settings.

== Changelog ==

= 3.9.4 =
* Update interactions with Tippy

= 3.9.3 =
* Fix for formatting the ESV text for the new Tippy

= 3.9.2 =
* Updated to work with the latest Tippy

= 3.9.1 =
* Fix quirk with new verse/chapter match
* Added Settings link to plugins dashboard page

= 3.9.0 =
* ESV Plugin is now wrapped in a class
* Switched from preg_replace to preg_replace_callback
* Plugin now recognizes 'verse' and 'chapter' - ie: 'verse 9'. Only works if a previous text was recognized. If a post says, "John 3 describes Jesus' conversation with Nicodemus. In verse 16 we find one of the best-known passages of the Bible." verse 16 will be correctly linked to John 3:16.
* Corrected more glitchy match conditions.
* Increased specificity of h3 and h4 in esv.css so it won't override theme styling.

= 3.8.4 =
* Minor updates

= 3.8.3 =
* Still more pattern matching improvements

= 3.8.2 =
* Additional improvements to Scripture matching

= 3.8.1 =
* added the comment_text filter to tag Scripture references in comments

= 3.8.0 =
* Improved the accuracy of Scripture matching
	* Previously matched a phrase like "the 1925 edition" - tagging "the 192" as being Thessalonians; now properly looks for a volume number for books that require a volume.
 	* Pattern is now case-insensitive so it will match things like jn 1:1
 	* Added a few new abbreviation cases
 
= 3.7.2 =
* Updated to work with Tippy 3.6.1
 
= 3.7.1 =
* Added option to select which Bible site links point to<br />
* Added option to make links open in a new window<br />
* A few tweaks for Tippy compatibility
 
= 3.7.0 =
* Apologies for this release, it was not supposed to go out yet.

= 3.6.1 =
* Updated to work with Tippy 3.4.0