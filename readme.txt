=== Gwolle-GB ===
Contributors: Gwolle, mpol
Tags: guestbook, guest book, comments, feedback, antispam, review
Requires at least: 2.8
Tested up to: 4.1
Stable tag: 1.1.4

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.
Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate
a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle-GB and
have a real guestbook.


Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* Widget to display your last or your best entries.
* Simple and clean admin interface that integrates seamlessly into WordPress admin.
* Dashboard Widget to easily manage the latest entries from your Admin dashboard.
* Easy import from other guestbooks into Gwolle-GB.
* Notification by mail when a new entry has been posted.
* Moderation, so that you can check an entry before it is visible in your guestbook (all optional).
* Akismet integration for fighting spam.
* Custom Anti-Spam question for fighting spam, too.
* reCAPTCHA integration for fighting spam, as well.
* Simple Form Builder to select which form-fields you want to use.
* Localisation. Own languages can be added very easily, so please send po-files to marcel at timelord.nl.
* Different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin'
* A log for each entry, so that you know which member of the staff released and edited a guestbook-entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* Smiley integration (uses the WordPress smiley engine).
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

You may have another guestbook installed. That's great, because Gwolle-GB enables you to import entries easily.
The importer does not delete any of your data, so you can go back to your previous setup without loss of data, if you want to.
Trying Gwolle-GB is as easy as 1-2-3.

= Import / Export =

Import is supported from:

* DMSGuestbook.
* WordPress comments from a specific page.
* Gwolle-GB itself, with Export supported as well.


Please note: At the moment, Gwolle-GB does *not* work with WordPress MU.


= Languages =

* cs_CZ [Jan Korous]
* de_DE [Jenny Gaulke]
* en_GB [Wolfgang Timme]
* es_ES [José Luis Sanz Ruiz]
* fr_FR [Charles-Aurélien PONCET](http://www.brie-informatique.com/)
* nl_NL [Marcel Pol](http://zenoweb.nl)
* pl_PL [Daniel Speichert]
* zh_TW [Chun-I Lee]

Other languages can be added very easily, so please send po-files to marcel at timelord.nl.


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

If you have a feature request please use the forum on WordPress.org.

= Licence =

The plugin itself is released under the GNU General Public License; a copy of this licence can be found at the licence homepage or
in the gwolle-gb.php file at the top.

For the licences regarding the use of reCAPTCHA or the icons you may ask the authors.


== Frequently Asked Questions ==

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

You can also use reCAPTCHA. It might scare off some visitors, though.

= I get a warning about another reCAPTCHA library =

Apparently you use a theme or other plugin with its own reCAPTCHA library. If you get a warning that it is old and incompatible, please
ask the maintainer of that theme or plugin to update their version of reCAPTCHA. If the warning is that another version will be used by
Gwolle-GB, and you experience problems when submitting guestbook entries, please tell me on the forums, and also tell me which other plugin
you use.

= My reCAPTCHA doesn't show up in the form. =

If you did enable reCAPTCHA, but didn't get a warning, in most cases it should show your reCAPTCHA. It might be that you have JavaScript errors.
You can check this in the Inspector/console of your browser.

= After submitting a new entry, I get errors about reCAPTCHA. =
If the form gives you errors, even though you filled in everything correctly, there might be something strange happening.
On some setups reCAPTCHA doesn't report Succes or Error. If you are able to debug this, and tell me what is happening, I would be happy to hear that.

= I don't get a notification email. =

First check your spambox in your mailaccount. On the settingspage you can change the From address for the email that is sent.
Sometimes there are problems sending it from the default address, so this is a good thing to set first.
If it still doesn't work, request the maillog at your hosting provider, or ask if they can take a look.

= How do I enable or disable avatars? =

Gwolle-GB uses the default WordPress setting, under Settings / Discussion.

= What capabilities are needed? =

For moderating comments you need the capability 'moderate_comments'.

For managing options you need the capability 'manage_options'.

= Should I really not use WordPress comments for a guestbook? =

Sure you can if you want to. In my personal opinion however it can be a good thing to keep comments and guestbook entries separated.
So if you already have a blog with comments, the guestbook entries might get lost in there, and keeping a separatge guestbook can be good.
But if you don't use standard comments, you can just as easily use the comment section for a guestbook.

= How do I localize a plugin? =

A good start to learn about localization and WP plugins is the guide "Localizing a WordPress plugin using poEdit" (http://weblogtoolscollection.com/archives/2007/08/27/localizing-a-wordpress-plugin-using-poedit/)
and also the WordPress documentation. When you made a translation, you can send the po-file to marcel at timelord.nl.

= Is this plugin actively maintained? =

Yes, it is again actively maintained.

== Screenshots ==

1. Frontend view of the list of guestbook entries. On top the button that will show the form when clicked. Then pagination. Then the list of entries.
2. Dashboard widget with new and unchecked entries.
3. Main page with the overview panel, so that you easily can see what's the overall status.
2. List of guestbook entries. Notice the icons displaying the status of an entry (Can be turned off in the settings panel).
3. The editor for a single entry. The Actions are using AJAX. There is a log of each entry what happened to this entry.
4. Settings panel, showing version 1.1.4. This is the first tab where you can select which parts of the form to show and use.


== Changelog ==

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


