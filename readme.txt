=== Gwolle-GB ===
Contributors: Gwolle, mpol
Tags: guestbook, guest book, comments, feedback, antispam
Requires at least: 2.8
Tested up to: 4.1
Stable tag: 1.0.1

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.
Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate
a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle-GB and
have a real guestbook.


Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* Simple and clean admin interface that integrates seamlessly into WordPress admin.
* Easy import of DMSGuestbook entries into Gwolle-GB.
* Notification by mail when a new entry has been posted.
* Moderation, so that you can check an entry before it is visible in your guestbook (all optional).
* Akismet integration for fighting spam.
* reCAPTCHA integration for fighting spam, too.
* Localisation (currently English, German, French, Spanish, Polish and Dutch). Own languages can be added very easily,
  check WP documentation on this. If you translated the plugin I'd be glad to include your language in a future release,
  so please send them to my via mail, thanks!
* Different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin'
* A log for each entry, so that you know which member of the staff released and edited a guestbook-entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* Smiley integration (uses the WordPress smiley engine).
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

You may have "DMSGuestbook" installed - that's great, because since version 0.9.5 Gwolle-GB enables you
to import DMSGuestbook's entries easily using an assistant. The importer does not delete any of your data,
so you can go back to DMSGuestbook without loss of data, if you want to. Trying Gwolle-GB is as easy as 1-2-3.

Please note: At the moment, Gwolle-GB does *not* work with WordPress MU.


= Languages =

* de_DE [Wolfgang Timme]
* en_GB [Wolfgang Timme]
* es_ES [José Luis Sanz Ruiz]
* fr_FR [Cobestran.com]
* nl_NL [Marcel Pol](http://zenoweb.nl)
* pl_PL [Daniel Speichert]


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

* Gwolle-GB uses the Shortcode API now. Make sure your Guestbook page uses [gwolle_gb] instead of the old one.
* The entries that are visible to visitors have changed. Make sure to check if you have everything
  visible that you want and nothing more.
* CSS has changed somewhat. If you have custom CSS, you want to check if it still applies.

= Todo/coming up in future releases =

First priority is getting the plugin working with current WordPress and resolve a lot of bugs.

* Bughunting (check all the FIXME's in the code).
* Pagination is sometimes funky.
* Add possibility to empty trash and spam.
* Make Install method more safe.
* Bring Import method back and refactor it. Add more sources.
* Bring Uninstall back, this time in a separate admin page?
* Bring AJAX back, this time inside actions.
* Bring Frontend Widget and Dashboard Widget back.
* Redo Settings page with tabs for separate parts.
* Add an RSS Feed for Guestbook Entries.
* Better database structure (e. g. 'id' instead of 'entry_id').
* 'Thank you' mail to the poster (requested by Joakim from Sweden).

If you have a feature request please use the forum on WordPress.org. It may be added to the list then.

= Known bugs in 0.9.7 (the old version) =

* Some WP installations with permalinks activated receive a 404-error.
* Some WP installations don't send the notification mails (currently only known from WP MU installations).
* Marking multiple entries as spam sometimes takes very long or results in an error message.

Have something to add here? Please add a new thread in the WP.org forums and tag it with "Gwolle-GB". I am subscribed to that forum,
so I will get your message. Or send me an email: marcel (at) zenoweb (dot) nl. Thanks.

= Thank you =

* timomaas for the original dutch translation
* Daniel Speichert for the polish translation
* José Luis Sanz Ruiz for the spanish translation
* cobestran.com for the french translation
* All the bug posters, including Berrie Pelser, Sebastian Moeller, voodoobanshee, Peter Pollack, Werner Traschuetz, Dean Suhr, Georg K., Kristin

= Licence =

The plugin itself is released under the GNU General Public License; a copy of this licence can be found at the licence homepage or
in the gwolle-gb.php file at the top.

For the licences regarding the use of reCAPTCHA or the icons you may ask the authors.


== Frequently Asked Questions ==

= Which entries are visible on the Frontend? =

Starting with version 1.0, the following entries are listed on the Frontend:

* Checked
* Not in the Trash
* Not marked as Spam

Before that, in 0.9.7, all the 'checked' entries were visible.

= I have a lot of unchecked entries. What do I do? =

For the entries that you consider spam, but were not caught by Akismet, you can mark them as spam, and they will not be visible anymore.
For entries that are not spam, but you still don't want them visible, you can move them to trash.
The entries that you want visible, select these to be checked.

= Why aren't entries really deleted, and instead stored in the database? =

First, it's not a heavy load for the database, and second, it's that if you clicked wrong you're not lost without the possibility
to 'undo' it via your database interface.
In a future version this might be implemented.

= What about Spam? =

Your first option is to use Akismet. It works like a charm. Fighting spam has never been easier.

You can also use reCAPTCHA. It helps you and your visitors to fight spam at the slight cost of usability.

= What capabilities are needed? =

For moderating comments you need the capability moderate_comments.

For managing options you need the capability manage_options. For subscribing to notifications, this one is also needed.

= How do I localize a plugin? =

A good start to learn about localization and WP plugins is the guide "Localizing a WordPress plugin using poEdit" (http://weblogtoolscollection.com/archives/2007/08/27/localizing-a-wordpress-plugin-using-poedit/)
and also the WordPress documentation.

= Is this plugin actively maintained? =

Yes, it is again actively maintained.

== Screenshots ==

1. Overview panel, so that you easily can see what's the status.
2. List of the entries. Notice the icons displaying the status of an entry (Can be turned off in the settings panel).
3. The editor for entries.
4. Settings panel (showing version 0.9.4.1).


== Changelog ==

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


