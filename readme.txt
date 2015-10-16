=== Gwolle Guestbook ===
Contributors: Gwolle, mpol
Tags: guestbook, guest book, comments, feedback, antispam, review, gastenboek, livre d'or, Gästebuch, libro de visitas, livro de visitas
Requires at least: 3.7
Tested up to: 4.4
Stable tag: 1.5.4
License: GPLv2 or later

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.
Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate
a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and
have a real guestbook.


Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* Widget to display your last or your best entries.
* Simple and clean admin interface that integrates seamlessly into WordPress admin.
* Dashboard Widget to easily manage the latest entries from your Admin Dashboard.
* Easy Import from other guestbooks into Gwolle Guestbook.
* Notification by mail when a new entry has been posted.
* Moderation, so that you can check an entry before it is visible in your guestbook (all optional).
* Akismet integration for fighting spam.
* Custom Anti-Spam question for fighting spam, too.
* CAPTCHA integration for fighting spam, as well.
* Simple Form Builder to select which form-fields you want to use.
* Simple Entry Builder with the parts of each entry that you want to show.
* Multiple guestbooks are possible.
* MultiSite is supported.
* Localization. Own languages can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
* Different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin' (optional).
* Admins can add a reply to each entry.
* A log for each entry, so that you know which member of the staff released and edited a guestbook-entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* RSS Feed.
* BBcode, Emoji and Smiley integration.
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

= Import / Export =

You may have another guestbook installed. That's great, because Gwolle Guestbook enables you to import entries easily.
The importer does not delete any of your data, so you can go back to your previous setup without loss of data, if you want to.
Trying Gwolle Guestbook is as easy as 1-2-3.

Import is supported from:

* DMSGuestbook.
* WordPress comments from a specific post, page or just all comments.
* Gwolle Guestbook itself, with Export supported as well (CSV-file).


= Languages =

* Bulgarian, bg_BG, Kostadin Petrichkov
* Czech, cs_CZ, Jan Korous
* Danish, da_DK, [Bo Fischer Nielsen](http://bfn.dk)
* German, de_DE, Jenny Gaulke and Eckhard Henkel
* Greek, el, dbonovas
* Spanish, es_ES, José Luis Sanz Ruiz
* Finnish, fi, Ilkka Kivelä and Timo Hintsa
* French, fr_FR, [Charles-Aurélien PONCET](http://www.brie-informatique.com/) and [Florence Bourmault-Gohin](http://www.mon-coin-de-bourgogne.fr)
* Italian, it_IT, Mariachiara Corradini
* Norwegian Bokmål, nb_NO, Bjørn Inge Vårvik
* Dutch, nl_NL, [Marcel Pol](http://zenoweb.nl)
* Polish, pl_PL, Andrzej Sobaniec
* Portuguese, pt_BR, [Alexandre Rocha](http://alexandre-rocha.com)
* Portuguese, pt_PT, Jose Quintas
* Russian, ru_RU, zhonglyor
* Slovak, sk_SK, Marcel Klacan
* Swedish, sv_SE, [Roffe Bentsen](http://macodesign.se)
* Traditional Chinese, zh_TW, Chun-I Lee

Other languages can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).


= Demo =

Check out the demo at [http://demo.zenoweb.nl](http://demo.zenoweb.nl/wordpress-plugins/gwolle-gb/)

== Installation ==

= Installation =

* Install the plugin through the admin page "Plugins".
* Alternatively, unpack and upload the contents of the zipfile to your '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Place '[gwolle_gb]' in a page. That's it.
* You may disable comments in this post or page, because it may look confusing when there's the possibility to write a guestbook entry.

As an alternative for the shortcode, you can use the function `show_gwolle_gb();` to show the guestbook in your templates.
It couldn't be easier.

= Updating from an old version =

With version 1.0 there have been some changes:

* Gwolle Guestbook uses the Shortcode API now. Make sure your Guestbook page uses '[gwolle_gb]' instead of the old one.
* The entries that are visible to visitors have changed. Make sure to check if you have everything
  visible that you want and nothing more.
* CSS has changed somewhat. If you have custom CSS, you want to check if it still applies.

= License =

The plugin itself is released under the GNU General Public License. A copy of this license can be found at the license homepage or
in the gwolle-gb.php file at the top.

= API, add an entry =

It is not hard to add an entry in PHP code.

	<?php
		$entry = new gwolle_gb_entry();

		// Set the data in the instance, returns true
		$set_data = $entry->set_data( $args );

		// Save entry, returns the id of the entry
		$save = $entry->save();
	?>

The Array $args can have the following key/values:

* id, int with the id, leave empty for a new entry.
* author_name, string with the name of the autor.
* author_id, id with the WordPress user ID of the author.
* author_email, string with the email address of the author.
* author_origin, string with the city of origin of the author.
* author_website, string with the website of the author.
* author_ip, string with the ipaddress of the author.
* author_host, string with the hostname of that ip.
* content, string with content of the message.
* datetime, timestamp of the entry.
* ischecked, bool if it is checked by a moderator.
* checkedby, int with the WordPress ID of that moderator.
* istrash, bool if it is in trash or not.
* isspam, bool if it is spam or not.
* admin_reply, string with content of the admin reply message.
* admin_reply_uid, id with the WordPress user ID of the author of the admin_reply.
* book_id, int with the Book ID of that entry, default is 1.

= Filter an entry on the frontend =

On the frontend you can filter each entry. You can use a function like:

	<?php
	function your_custom_function( $entry_html, $entry ) {
		// $entry_html is a string
		$entry_html = $entry_html . " Hi There. ";
		return $entry_html;
	}
	add_filter( 'gwolle_gb_entry_read', 'your_custom_function', 10, 2 );
	?>

= Filter all the entries on the frontend =

= Add text to each entry in the list of entries. =

Adding text to the entries list can be done with several hooks.
The next filters will add the string of text before each entry, in the content of each entry, or after each entry.
Initially $string is empty.

	<?php
	function gw_add_content( $string, $entry ) {
		$string .= "Filter add content.";
		return $string;
	}
	add_filter( 'gwolle_gb_entry_read_add_before',    'gw_add_content', 10, 2 );
	add_filter( 'gwolle_gb_entry_read_add_content',   'gw_add_content', 10, 2 );
	add_filter( 'gwolle_gb_entry_read_add_after',     'gw_add_content', 10, 2 );
	add_filter( 'gwolle_gb_entry_widget_add_before',  'gw_add_content', 10, 2 );
	add_filter( 'gwolle_gb_entry_widget_add_content', 'gw_add_content', 10, 2 );
	add_filter( 'gwolle_gb_entry_widget_add_after',   'gw_add_content', 10, 2 );
	?>

You can also filter the complete list of entries.

	<?php
	function your_custom_function($entries) {
		// $entries is a string
		$entries = $entries . " Hello my friend. ";
		return $entries;
	}
	add_filter( 'gwolle_gb_entries_read', 'your_custom_function');
	?>

= Filter the form as a whole. =

The form can be filtered as well:

	<?php
	function your_custom_function($form) {
		// $form is a string
		$form = $form . " Please fill this in. ";
		return $form;
	}
	add_filter( 'gwolle_gb_write', 'your_custom_function');
	?>

= Add html to the form. =

You can add html to the form at three different places. Initially $string is empty.

	<?php
	function gw_add_to_form( $string ) {
		$string .= "Filter add to form.";
		return $string;
	}
	// Use this filter to just add something
	add_filter( 'gwolle_gb_write_add_before', 'gw_add_to_form' );
	add_filter( 'gwolle_gb_write_add_form', 'gw_add_to_form' );
	add_filter( 'gwolle_gb_write_add_after', 'gw_add_to_form' );
	?>

= Filter an entry before saving =

When saving an entry you can filter it like this.

	<?php
	function your_custom_function($entry) {
		// $entry is an array.
		// Example where every entry that gets saved gets the current time
		$entry['datetime'] = current_time( 'timestamp' );
		return $entry;
	}
	add_filter( 'gwolle_gb_entry_save', 'your_custom_function');
	?>

= Format for importing through CSV-file =

The importer expects a certain format of the CSV-file. If you need to import from a custom solution, your CSV needs to conform.
The header needs to look like this:

	<?php
	array(
		'id',
		'author_name',
		'author_email',
		'author_origin',
		'author_website',
		'author_ip',
		'author_host',
		'content',
		'datetime',
		'isspam',
		'ischecked',
		'istrash',
		'admin_reply'
	)
	?>

The next lines are made up of the content.

Date needs to be a UNIX timestamp. For manually creating a timestamp, look at
the [timestamp generator](http://www.timestampgenerator.com/).

It expects quotes around each field, so having quotes inside the content of the entry can break the import process.

With version 1.4.1 and older, the field datetime was called date.

You could make a test-entry, export that, and look to see what the importer expects from the CSV.
Make sure you use UNIX line-endings. Any decent text-editor can transform a textdocument to UNIX line-endings.

== Frequently Asked Questions ==

= How do I get people to post messages in my guestbook? =

You could start by writing the first entry yourself, and invite people to leave a message.

= Which entries are visible on the Frontend? =

Starting with version 1.0, the following entries are listed on the Frontend:

* Checked
* Not marked as Spam
* Not in the Trash

Before that, in 0.9.7, all the 'checked' entries were visible.

= I have a lot of unchecked entries. What do I do? =

For the entries that you consider spam, but were not caught by Akismet, you can mark them as spam, and they will not be visible anymore.
For entries that are not spam, but you still don't want them visible, you can move them to trash.
The entries that you want visible, select these to be checked.

= What about Spam? =

Your first option is to use Akismet. It works like a charm. Fighting spam has never been easier.

If that doesn't work enough, use the Custom Anti-Spam question.

You can also use a CAPTCHA. It might scare off some visitors, though.

= I enabled the CAPTCHA, but I don't see it in the form. =

The CAPTCHA uses the one provided by the [Really Simple Captcha plugin](https://wordpress.org/plugins/really-simple-captcha/).
Please install and activate that plugin.

If it still doesn't show, it could be that the plugin has no write permission in the '/tmp' folder of the Really Simple Captcha plugin.
Please fix this in your install.

= How can I use Multiple Guestbooks? =

You can add a parameter to the shortcode, like:

	[gwolle_gb book_id="2"]

This will make that page show all the entries in Book ID 2.

If you use the template function, you can use it like this:

	show_gwolle_gb( array('book_id'=>2) );

= With multiple guestbook, how do I keep track? =

There is no need to use id's that are incrementing. If you have a lot of guestbooks on lots of pages,
you can just use the id of the post as the id of the guestbook. That way you won't have double id's.

= I don't see the labels in the form. =

This plugin doesn't apply any CSS to the label elements. It is possible that your label elements have a white color on a white background.
You can check this with the Inspector in your browser. If that is the case, you have a theme or plugin that is applying that CSS to your
label elements. Please contact them.

= I would like to have the form visible by default. =

You could add custom CSS to your website/theme like this:

	body form#gwolle_gb_new_entry {
		display: block;
	}
	body div#gwolle_gb_write_button {
		display: none;
	}

That should do the trick.

= I don't get a notification email. =

First check your spambox in your mailaccount.

Second, on the settingspage you can change the From address for the email that is sent.
Sometimes there are problems sending it from the default address, so this is a good thing to change to a real address.

If it still doesn't work, request the maillog at your hosting provider, or ask if they can take a look.

= I want to show the form and the list on different pages =

There are different shortcodes that you can use. Instead of the '[gwolle_gb]' shortcode, you can use '[gwolle_gb_write]' for just the form,
and '[gwolle_gb_read]' for the list of entries.

= Moderation is enabled, but my entry is marked as checked =

If a user with capability of 'moderate_comments' posts an entry, it will be marked as checked by default, because he can mark it as checked anyway.

= Moderation is disabled, but some entries are still unchecked =

There is validation of the length of words in the content and author name. If the words are too long and it looks
abusive, it will be marked as unchecked. A moderator will still be needed to manually edit and check these entries.

= When opening the RSS Feed, I get a Error 404 =

You can refresh your rewrite rules, by going to Settings / Permalinks, and save your permalinks again.
This will most likely add the rewrite rule for the RSS Feed.

= I use a caching plugin, and my entries are not visible after posting =

When you have moderation disabled, Gwolle Guestbook will try to refresh the cache. If it doesn't on your setup,
please let me know which caching plugin you use, and support for it might be added.

You can also refresh or delete your cache manually. Most caching plugins offer support for that.

= I use a Multi-Lingual plugin =

There are 2 settings that you need to pay attention to. If you saved the settings for the form tab, you should save an
empty header and notice text. It will fill in the default there after saving, but that is okay.
As long as you saved an empty option, or it is still not-saved, then it will show the translated text from your MO file.

= What capabilities are needed? =

For moderating comments you need the capability 'moderate_comments'.

For managing options you need the capability 'manage_options'.

= Can I override a template? =

You can look at 'frontend/gwolle_gb-entry.php', and copy it to your theme folder. Then it will be loaded by the plugin.
Make sure you keep track of changes in the default templatefile though.

= Should I really not use WordPress comments for a guestbook? =

Sure you can if you want to. In my personal opinion however it can be a good thing to keep comments and guestbook entries separated.
So if you already have a blog with comments, the guestbook entries might get lost in there, and keeping a separate guestbook can be good.
But if you don't use standard comments, you can just as easily use the comment section for a guestbook.


== Screenshots ==

1. Frontend View of the list of guestbook entries. On top the button that will show the form when clicked. Then pagination. Then the list of entries.
2. Widget with different options.
3. Main Admin Page with the overview panel, so that you easily can see what's the overall status.
4. List of guestbook entries. The icons display the status of an entry.
5. The Editor for a single entry. The Actions are using AJAX. There is a log of each entry what happened to this entry.
6. Settings Page. This is the first tab where you can select which parts of the form to show and use.
7. Dashboard Widget with new and unchecked entries.


== Changelog ==

= 1.5.4 =
* 2015-10-16
* Security fix: Use AJAX the proper way for CAPTCHA check (thanks htbridge.ch).
* Translations moved to GlotPress.
* Support Shortcode UI.
* Properly enqueue frontend JavaScript.
* Use plugins_url() for admin enqueue.
* Don't use WP_PLUGIN_URL and GWOLLE_GB_URL anymore.

= 1.5.3 =
* 2015-10-01
* When email is disabled, save it anyway when user is logged in.
* Add nb_NO (thanks Bjørn Inge Vårvik).
* Update ru_RU.

= 1.5.2 =
* 2015-09-15
* First stab at supporting MultiSite.
* When deleting an entry on the Editor page, start with a clean slate.
* On Entries page, give Book ID its own column.
* Do not cache page when using a Captcha.
* Admin Enqueue moved to its own function and action.
* Only toggle our own postboxes.
* Update pot, nl_NL, zh_TW.

= 1.5.1 =
* 2015-09-14
* Support Multiple Guestbooks.
* Add book_id field to database and class-entry, default is book_id 1.
* Add parameter book_id to shortcodes.
* Add parameter book_id to get_entries and get_entry_count functions.
* Show and Edit book_id on Admin Editor.
* Add icons for book_id and admin_reply, add title attributes.
* Don't show so much metadata on unpublished entry.
* Fix link to entry in moderation mail.
* Add notice for using CAPTCHA with a caching plugin.
* No need to add options on install, we have defaults for that.
* Rename install/upgrade functions.
* Test if db tables exist, before adding them.
* Update pot, nl_NL, ru_RU.

= 1.5.0 =
* 2015-09-10
* Only support WordPress 3.7+, since they really are supported.
* Add option for widget to not show admin entries.
* When saving an entry, reset author_id and checkedby for non-existent users.
* Add action for deleted_user to reset author_id (and maybe checkedby).
* Add parameter author_id to gwolle_gb_get_entries function.
* Add parameter no_moderators to gwolle_gb_get_entries function.
* Add function gwolle_gb_get_moderators.
* Update pot, nl_NL, ru_RU.

= 1.4.9 =
* 2015-09-09
* Add HTML5 Markup.
* Use content_url() for the Captcha (thanks harald.reingruber).
* Rename class and function files to WP standards.
* Move mail functions to own file.
* Add permalink of guestbook to mails.
* Add mail to author on Admin Reply with option on editor page.
* Translate BBcode icontext with wp_localize_script.
* Add Emoji to Admin Editor.
* Add number of unchecked entries to admin bar, if > 0.
* Change text-domain to slug.
* Update pot, de_DE, nl_NL.

= 1.4.8 =
* 2015-08-27
* Add Admin Reply.
* Update Importer and Exporter.
* Add filters to entry-list and to widget.
* Add parameter $entry to many filters.
* Add CSS class for counter in entry-list.
* Add filters to the form.
* Turn linebreaks off in Settings when excerpt_length is being used.
* Update pot, de_DE, nl_NL.

= 1.4.7 =
* 2015-08-14
* Fix adding an entry without CAPTCHA enabled.
* Make header and notice compatible with Multi-Lingual plugins.
* Add parameter to template function gwolle_gb_entry_template.
* Add CSS class for even/uneven entry.
* Have better usability in handling disabled submit buttons on admin pages.
* Have sensible attributes for submit-button on settings page.
* Move pagination to own files and functions.
* Use h1 headings on admin pages.

= 1.4.6 =
* 2015-08-12
* Improve Responsive Layout of Admin Pages.
* Add option to paginate All entries.
* Clear Cache plugins on admin changes as well.
* Support Cachify, W3 Total Cache, WP Fastest Cache.
* Improve support for WP Super Cache.
* Refactor BBcode and Emoji functions into own file.
* Add function gwolle_gb_get_emoji.
* Improve html of author_name.
* Update pot, nl_NL.

= 1.4.5 =
* 2015-08-10
* Drop reCAPTCHA completely.
* Use Really Simple CAPTCHA plugin from now on.
* Rename from Gwolle-GB to Gwolle Guestbook.
* Add function gwolle_gb_bbcode_strip.
* Strip BBcode from Widget and Dashboard Widget.
* Strip BBcode from Entry when BBcode is disabled.
* Strip BBcode for Akismet service request.
* Fix link in Widget for WPML.
* Add placeholder to textarea, also in admin editor.
* Fix PHP notice in AJAX request.
* Add word-break and word-wrap to admin CSS.
* Add Greek, el (thanks dbonovas).
* Update pot, nl_NL.

= 1.4.4 =
* 2015-07-29
* Fix textdomain on Donate string.
* Use 'strong' for bold in bbcode.
* Update meta_key on save_post action.
* Update pot, de_DE, ru_RU, zh_TW.

= 1.4.3 =
* 2015-07-17
* Upgrade reCAPTCHA to 1.1.1 (Requires PHP 5.3+).
* DB: drop column 'date'.
* Improve html of new editor options.
* Update pot, nl_NL.

= 1.4.2 =
* 2015-07-13
* Fix quotes in subjectline and From-header of emails.
* Translate description of the plugin too.
* Set CSS for span to display:inline.
* Check for array when getting settings.
* DB: Move date to datetime with bigint(8), so sorting on date works correctly.
* Mark $entry->get_date and $entry->set_date as deprecated.
* Rename actions.php to hooks.php.
* Add function gwolle_gb_get_postid.
* Add button for frontend to some adminpages.
* Improve and update Admin CSS for WP 4.3.
* Add function gwolle_gb_touch_time.
* Edit author_name and datetime on editor.php.
* Don't spam the logs when editing an entry.
* Update pot, nl_NL.

= 1.4.1 =
* 2015-06-17
* Add author_ip to possible fields in notification mail.
* Use Gwolle version for enqueue of Markitup css and js.
* Add a bit of reset-CSS to Markitup CSS so themes are less conflicting.
* Show author name as it was put in by the author, not from the user profile.
* Add da_DK (thanks Bo Fischer Nielsen).

= 1.4.0 =
* 2015-06-14
* Add template for single entry.
* Fix quoting issues in notification email.
* Fix sending headers in write.php.
* Set scoped attribute for more CSS.
* Set language for RSS Feed.

= 1.3.9 =
* 2015-06-13
* Fix for WP 3.4, which has no function has_shortcode.
* Change 'at' time to 'on'.
* Update pot and affected translations.

= 1.3.8 =
* 2015-06-10
* Add RSS Feed.
* Set scoped properly.
* Update pot, nl_NL, ru_RU.

= 1.3.7 =
* 2015-06-04
* Add Emoji to form.
* Add action to frontend form for validation.
* Add scoped attribute to style element.
* Switch place of metaboxes on main admin page.
* Add sv_SE (Swedish) (thanks Roffe Bentsen).
* Update pt_BR.

= 1.3.6 =
* 2015-05-31
* Close span element in widget (thanks Ferdinand).
* Redo Donate metabox, add Review text.
* Add pt_BR (Thanks Alexandre Rocha).
* Update pot, nl_NL and ru_RU.

= 1.3.5 =
* 2015-05-19
* Support BBcode in editor.
* Improve Emoji support, for name, city and content.
* Fix posting an entry on old WP installs.
* Update bg_BG, nl_NL, pot.

= 1.3.4 =
* 2015-05-15
* Update the cache when using cache plugins.
* Support WP Super Cache.
* Convert our database tables to utf8mb4 if possible, so Emoji are functional.
* Also support (encode) Emoji on old db-collation.
* Frontend: Only listen to clicks on the button, not the whole div.
* Update ru_RU.

= 1.3.3 =
* 2015-05-08
* Only check for double entry if the content is mandatory.
* Only offer to export when there are entries.
* When login required, show login form.
* Also show the register link then.
* Update ru_RU.

= 1.3.2 =
* 2015-04-20
* PageNum is always an int.
* Add sk_SK (Slovenian) (Thanks Marcel Klacan).

= 1.3.1 =
* 2015-04-08
* Explain interaction between limiting words and linebreaks.
* Make notices (messages) dismissible in WP 4.2.
* Import from post, or just all comments.
* Only show pages and posts with comments on import page.
* Use get_comments everywhere, also for counting, for consistency.
* Really sanitize everywhere.
* Use htmlspecialchars instead of htmlentities.
* Use esc_attr_e for attributes.
* Add it_IT (thanks Mariachiara Corradini).
* Update pot, nl_NL.

= 1.3.0 =
* 2015-04-02
* Place div around list of entries.
* Update bg_BG, fr_FR.

= 1.2.9 =
* 2015-03-28
* Sanitize output for notification email.
* Remove "hours" from the entries list. Nobody likes it.
* Update bg_BG, ru_RU.

= 1.2.8 =
* 2015-03-25
* Do show the form when user is logged in and login is required.
* Widget uses get_home_url for the pagelink.
* Update bg_BG (Thanks Kostadin Petrichkov).
* Add missing es_ES.po from mo-file.

= 1.2.7 =
* 2015-03-24
* Add options for admins to (un)subscribe moderators to notifications.
* Add buttons to empty spam and trash.
* Add options to change text around the form.
* Separate settingspage into more files.
* Use more sanitizing for output.
* Add stub for bg_BG.
* Update pot, nl_NL.

= 1.2.6 =
* 2015-03-22
* Extend notification email.
* Add options for mail to the author.
* Really disable dashboard widget with too few capabilities.
* Update pot, nl_NL, ru_RU.

= 1.2.5 =
* 2015-03-21
* Check for abusive length of words, and mark unchecked.
* Compatibility with PHP 5.3 and Unicode.
* Use get_object_vars for casting to an array for the save filter.
* Don't set background-color on error-messages.
* Update ru_RU.

= 1.2.4 =
* 2015-03-18
* Add option for max words in content.
* Add ru_RU (thanks zhonglyor).
* Update pl_PL.
* Update fr_FR (Thanks Florence Bourmault-Gohin).

= 1.2.3 =
* 2015-03-17
* Add options for widget (changed defaults).
* CSS frontend uses % instead of px for width.
* Update pl_PL (Thanks Andrzej Sobaniec).
* Update fi (Thanks Timo Hintsa).

= 1.2.2 =
* 2015-03-13
* Import, check for timestamp on date, else convert.
* Add option to have labels float or not.
* Add option to enable/disable admin entry styling.
* Use maybe_unserialize.
* Add filters to the API.
* Update pot and nl_NL.

= 1.2.1 =
* 2015-03-10
* Frontend entries: class s/first/gwolle_gb_first.
* Rename fi_FI to fi, so it loads.
* Update pot, de_DE and nl_NL.

= 1.2.0 =
* 2015-03-08
* Add shortcodes for just the form and the list.
* Add option to only allow logged-in users to post an entry.
* Add options to configure the shown entries.
* Import: fix test for mimetype.
* Import supports PHP 5.2.
* s/Homepage/Website.
* Update pot, de_DE, nl_NL.

= 1.1.9 =
* 2015-02-16
* Validate URL for Website as well, even though most url's validate.
* Sanitize Formdata.
* Sanitize Settings.
* Update de_DE.

= 1.1.8 =
* 2015-02-14
* Move anti-spam question to the label on the left.
* Add better error messages to the form.
* Add autofocus to first formfield with an error.
* Use validation for the email.
* Add visibility:visible for tr.invisible.
* Add pt_PT (Only frontend yet).

= 1.1.7 =
* 2015-02-13
* Settingspage; make it possible to remove anti-spam and reCAPTCHA settings.
* All strings really use our text-domain.
* Update de_DE.

= 1.1.6 =
* 2015-02-10
* Fix CSS for check-all checkbox on entrylist in admin.
* Better CSS for admin entries, grey instead of pink.
* Also style admin entries on admin pages.
* Always load jQuery, it's just easier.
* All strings use our text-domain.

= 1.1.5 =
* 2015-02-09
* Fix js when jQuery is loaded in the footer.
* Fix error submitting new entries.
* Do pagination link a bit cheaper.
* Add fi_FI (thanks Ilkka Kivelä).

= 1.1.4 =
* 2015-02-03
* Fix pagination links.
* Slightly improve installsplash. Maybe it just needs to go alltogether.
* Update zh_TW.

= 1.1.3 =
* 2015-02-01
* Add a simple Form Builder.
* Add custom Anti-Spam question.
* Add CSS for the widget.
* Fix default MailText.
* Cleanup old options.

= 1.1.2 =
* 2015-01-31
* Settingspage uses Tabs.
* Settingspage uses more labels.
* Uninstall is back.
* Give the CSS file a version in the GET.
* Put date and time in spans on frontend.
* Only show paginaton on frontend when there is more then 1 page.
* Add Donate link.
* Don't count arrays when not needed.
* Use strpos instead of preg_match.
* Use sprintf for formatting instead of str_replace.
* Update pot-file, fr_FR, nl_NL, zh_TW.

= 1.1.1 =
* 2015-01-10
* Add Edit link to frontend for moderators.
* Work around old and incompatible other recaptcha libraries.
* Get_entries function supports limit of -1 (no limit).
* Import from WordPress comments.
* Export/Import from/to Gwolle-GB through a CSV file.
* Add zh_TW (Thanks Chun-I Lee).
* Remove unmaintained en_GB.

= 1.1.0 =
* 2015-01-06
* Admin entries page: fix table header and footer (ordering).
* Auto-fill the form if the user is already logged in.
* Bring Ajax to the editor page as well.
* Simplify Options on editor page.

= 1.0.9 =
* 2015-01-05
* Fix small but nasty error, sorry about that.
* More specific HTML / CSS on Frontend.

= 1.0.8 =
* 2015-01-04
* Ajax is back on Dashboard Widget and on Entries page.
* Move notification option to main page so moderators can subscribe.
* New option for the From address in notification mail.
* Small fixes and cleanups.
* Update de_DE and nl_NL.

= 1.0.7 =
* 2014-12-27
* Update de_DE (Thanks Jenny Gaulke).

= 1.0.6 =
* 2014-12-24
* Change database structure for guestbook entries.
* Fix install for db and log entries.
* Use '...' instead of '& hellip;'.

= 1.0.5 =
* 2014-12-21
* Add best entries to Frontend Widget.
* Start of the Dashboard Widget (for now without AJAX).
* Fix small pagination issue.
* Cleanup obsolete options.
* Class entry; integrate setters and checkers, it's all the same.
* gwolle_gb_entries: entry_id is not a parameter anymore.
* Change database structure for log entries.
* Small cleanups.
* Update pot, nl_NL and cs_CZ.

= 1.0.4 =
* 2014-12-16
* Bring back Import from DMSGuestbook.
* Postboxes can be closed now.
* Be more gentle on the database.
* Add cs_CZ (Thanks Jan Korous).

= 1.0.3 =
* 2014-12-14
* Add delete function in editor and mass-edit.
* Fix pagination on Frontend.
* Frontend Widget is back.
* Excerpt is now counted in words and uses wp_trim_words.
* Updates for pot, nl_NL and fr_FR.

= 1.0.2 =
* 2014-12-13
* HTML uses labels now.
* HTML has more classes.
* New generated pot file. Please send in your translations.
* Update for nl_NL.
* Update for fr_FR (Thanks Charles-Aurélien)

= 1.0.1 =
* 2014-12-05
* Frontend uses now input-button for the write link.
* Frontend checks again for double post.
* Main admin page also shows trashed entries.
* Settings page now saves Recaptcha setting.
* reCAPTCHA is back.

= 1.0 =
* 2014-11-28
* Release stable and updated version 1.0 to the public.
* Go on holiday, have a few beers, and watch the girls do the hoolahoop().

= 0.9.9.3 =
* 2014-11-28
* Admin page entries.php is redone, Mass-Edit works.
* Add option to check entries with Akismet.
* Streamlined all the options with default values.
* Logging is back.
* Icons are back.
* Admin CSS is more specific, less conflicting.
* Enqueue the Javascript that we use.
* Do not load the currently unused Javascript.
* Use wpdb->prepare for input everywhere.
* This thing may even be quite allright.

= 0.9.9.2 =
* 2014-11-18
* Admin page editor.php is redone.
* Admin page entries.php is still in need of handling the _POST (Mass-Edit doesn't work)
* Submit-Ham and Submit-Spam in Akismet are back.
* Use get_current_user_id instead of a global variable.
* Many options on Settings page (temporarily) removed.
* Use new option on the Settings page to set the number of entries on the admin.
* Many many many cleanups.

= 0.9.9.1 =
* 2014-11-15
* Use $wpdb everywhere.
* Many small fixes.
* Redo the Readme.
* Reformat code for readability. It will break diffs in svn though.
* Do most of the includes in the main gwolle-gb file. Put lots of code inside functions.
* Move actions to actions.php and partly clean up main gwolle-gb.php.
* Load language files from an action.
* Use Settings API.
* Use Shortcode API.
* Use standard WordPress capabilities.
* Only use the Automattic Akismet plugin, not any other class.
* Added functions/function.gwolle_gb_akismet.php for spamchecking against Akismet.
* Made Frontend CSS more specific and less conflicting. Small cleanups.
* Only load Frontend CSS when the plugin is active.
* Have the frontend-form on the main page, and show it with Javascript.
* Make email field obligatory on new entries.
* Show avatars when enabled in General / Comments.
* Start of class.gwolle_gb_entry.php.
* Have the frontend use the class gwolle_gb_entry.
* Show counter of unchecked entries in admin menu.
* Clean up Akismet in the Settings page.
* Save user notification list in one option as an array, so we follow the Settings API.
* Many more changes in the Settings page.
* On admin page editor.php; show if entry is listed as spam or not.
* Mind your head, only the frontend is secure, the backend is open for SQL Injection Attacks.

= 0.9.9.0 =
* 2014-10-22
* Use $wpdb everywhere
* Many small fixes
* Reformat code for readability. It will break diffs in svn though.
* Mind your head; Frontend and Backend are open for SQL Injection Attacks.

= 0.9.8.1 =
* Somewhere in 2010.
* [fix] Fixed bug that prevented posted entries from being published.
* Update Readme to 2014.

= 0.9.8 =
* [new] Name of the Gwolle-GB directory can now be changed.
* [new] News for Gwolle-GB are now shown at the dashboard of the plugin
* [new] Dashboard widget for a quick glance at the latest guestbook entries.
* [new] Deleted entries are now 'moved to trash', just like the 'trash' feature in the new WP version.
* [fix] Rewritten some code. Stills looks messy, but we're on the right track. :)

= 0.9.7 =
* [new] Removed the 'guestbook link' setting and replaced it with a field for the corresponding $post_id; it should be detected by default.
* [fix] New entries are validated and if this fails the user will be sent back to the 'write' page, but now without any $_POST data.
* Please note: The widget is planned for a future release. Use it at your own risk.

= 0.9.6.2 (2nd emergency release) =
* [fix] 'Number of entries' setting is now applied again to the 'read' frontpage. (Thanks, Kristin!)

= 0.9.6.1 (emergency release) =
* [new] Added log message to track if an entry has been changed using the editor.
* [fix] Fixed several bugs.

= 0.9.6 =
* [new] When viewing 'all' entries you can now enable/disable entries by just clicking at the icon. (AJAX, Yey!)
* [new] French language (thanks, cobestran.com)
* [new] Author's can now be linked to his/her website. (Can be turned off in the settings.)
* [new] Smilies are now replaced using the built-in WP smiley engine. (Can be turned off in the settings.)
* [fix] 'Spam' is now only an attribute, no longer a state. This results in only two states: 'checked' and 'unchecked'.
* [fix] Cleaned up the code a lot and using a lot of functions. (Check the "func.php" files for futher reading.)
* [fix] Date is now displayed as configured at the WP options page.
* [fix] Admin entries: Only show stuff member's name if found.

= 0.9.5 =
* [new] You can now import guestbook entries from the popular "DMSGuestbook" plugin easily.

= 0.9.4.7 =
* [fix] Correctly displaying author's name if it contains HTML elements such as <script> tags
* [fix] stripslashes on author's location, so locations like "My aunt's house" are displayed correctly.
* [fix] the_content() now just returns the guestbook instead of echo'ing it. This way, other plugins are able to modify the content.
* [fix] Added success message when uninstall is completed.

= 0.9.4.6 =
* [new] Option to output text before/after the [gwolle-gb]-Tag, as requested
* [new] Whois link for IP address of the entry author
* [new] Entry content can now be send with the notification mail. For security reasons '<' and '>' are send as '{' and '}'.
* [new] Polish language (thanks, Daniel Speichert!)
* [new] Spanish language (thanks, José Luis Sanz Ruiz!)
* [fix] Support for localization of the frontend
* [fix] Now coded in XHTML, just as pointed out by "KriLL3"
* [fix] German special chars ("Umlaute") are now displayed correctly.
* [fix] Metaboxes ('Save' etc.) are displayed again. Sorry for that one, folks!
* Now online again with http://wolfgangtimme.de/blog/!

= 0.9.4.5 =
* As this release alters your Gwolle-GB database tables I highly recommend to backup the old ones.
* If you're experiencing any errors please report them immediately. Thanks!
* [new] Option to toggle the visibility of line breaks
* [fix] All tables & fields are now UTF8 (-> support for Croation letters)

= 0.9.4.4a =
* [new] After deleting an entry directly from the entries page you'll be redirected to that specific page (spam/unlocked/locked/all)
* [fix] reCAPTCHA library is not include if that's already been done by another plugin.
* [new] Dutch translation added. Thank you, timomaas!
* [fix] Now using wp_mail() instead of mail() to send email notifications.
* [fix] stripslashes on the user-defined admin mail text.
* [new] mass editing of entries added
* [new] Notification list shows ALL subscribers, including the current user.

= 0.9.4.3 =
* [fix] Redirection failed again.
* [new] Added an option to set link to the guestbook manually.

= 0.9.4.2.1 =
* [rem] Removed the version check because of some problems.

= 0.9.4.2 =
* [fix] Redirection to reading page after posting entry sometimes failed.
* [new] Option to set the number of entries displayed in reading mode.
* [new] Option to set the mail notification text
* [new] When uninstalling via the plugin's setting panel you'll be asked to confirm again.
* [new] Notification mails now can include a direct link to the editor, speeding things up for you.


