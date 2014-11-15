=== Gwolle-GB ===
Contributors: Gwolle
Tags: guestbook, feedback, antispam
Requires at least: 2.8
Tested up to: 4.0
Stable tag: 0.9.7

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.

It does not use the WordPress comment system, but uses its own functionality and tables in the database.

Please note that this plugin has been unmaintained for some time, and needs some work to get in better shape again.
If you have a current install of this plugin, it might be a good thing to update to a recent version.
If you are looking for setting up a new guestbook, you should know that the current state of this plugin is mostly beta quality. There are bugs here.

Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* Simple and clean admin interface that integrates seamlessly into /wp-admin.
* Easy import of DMSGuestbook entries into Gwolle-GB.
* Notification by mail when a new entry has been posted.
* Moderation, so that you have to check an entry before it is visible in your guestbook (all optional).
* Recaptcha integration for fighting spam.
* Akismet integration for fighting spam, too.
* Localisation (currently english, german, french, spanish, polish and dutch). Own languages can be added very easily, check WP documentation on this. If you translated the plugin I'd be glad to include your language in a future release, so please send them to my via mail, thanks!
* different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin'
* A log for each entry, so that you know which member of the staff released and edited a guestbook-entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* Smiley integration (uses the WordPress smiley engine).
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

You may have "DMSGuestbook" installed - that's great, because since version 0.9.5 Gwolle-GB enables you
to import DMSGuestbook's entries easily using an assistant. The importer does not delete any of your data,
so you can go back to DMSGuestbook without loss of data, if you want to. Trying Gwolle-GB is as easy as 1-2-3.

For a demo, visit the plugin's homepage (http://example.com/). Feel free to drop me a message
in the WordPress.org forums or send an email to marcel (at) zenoweb (dot) nl. You may also
use my homepage's comment section. I'd be glad to hear your opinion and/or feature request.

Please note: At the moment, Gwolle-GB does *not* work with WordPress MU. I'm working on it, but at the moment it's just not working. Sorry, guys!


= Languages =

* de_DE [Wolfgang Timme]
* en_GB [Wolfgang Timme]
* es_ES [José Luis Sanz Ruiz]
* fr_FR [Cobestran.com]
* nl_NL [Marcel Pol](http://zenoweb.nl)
* pl_PL [Daniel Speichert]


== Installation ==

1. Upload the directory 'gwolle-gb' (where the 'gwolle-gb.php' is in) to your '/wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place '[gwolle-gb]' in an article or page. That's it.
4. You may disable comments in this arcticle/page, because it may look confusing when there's the possibility to write a guestbook entry. ;)

Gwolle-GB takes the `the_content`-hook and replaces an article/page containing [gwolle-gb] with the guestbook. You may also use
`add_gwolle_gb_frontend_css();` to include the frontend CSS of Gwolle-GB and `show_gwolle_gb();`
to show the guestbook in your templates. It couldn't be easier.

If you still got problems, or if errors come up please leave a message at the WordPress forum so I can help you and fix it. Thanks!


= Todo/coming up in future releases =

First priority is getting the plugin working with current WordPress and resolve a lot of bugs.

* Bughunting.
* Use $wpdb and $wpdb->prepare everywhere so we are ready for PHP 5.5 and MySQLi.
* Only use the main Akismet plugin, do not include it ourselves.
* Same for ReCaptcha probably.
* Get rid of include() inside if/else structures.
* Maybe move functions into a class to share code and have less redundancy.
  (one file with crud functions, one file with validation, one with misc functions).
* Consider autoloading of that class (https://wiki.php.net/rfc/function_autoloading).
* Have frontend/backend views and controllers.
* Maybe use JavaScript to load the frontend form dynamically.
* Update CSS and always load it. Use an id to avoid conflicting CSS.

If you have a feature request please use the forum on WordPress.org. I may add it to the list then.

The following list is from the original author.

* More inline documentation.
* Better permission management (the current one is poor).
* Gravatar support.
* Custom fields (e. g. 'ICQ number' etc.).
* More functions & methods, less code redundancy.
* Support for WordPress MU.
* Better database structure (e. g. 'id' instead of 'entry_id').
* Widget (at the dashboard as well as the sidebar).
* 'Thank you' mail to the poster (requested by Joakim from Sweden).

= Known bugs =

* Some WP installations with permalinks activated receive a 404-error.
* Some WP installations don't send the notification mails (currently only known from WP MU installations).
* Marking multiple entries as spam sometimes takes very long or results in an error message.

Have something to add here? Please add a new thread in the WP.org forums and tag it with "Gwolle-GB". I am subscribed to that forum,
so I will get your message. Or send me an email: marcel (at) zenoweb (dot) nl. Thanks.

= Thank you! =

* timomaas for the dutch translation
* Daniel Speichert for the polish translation
* José Luis Sanz Ruiz for the spanish translation
* cobestran.com for the french translation
* All the bug posters, including Berrie Pelser, Sebastian Moeller, voodoobanshee, Peter Pollack, Werner Traschuetz, Dean Suhr, Georg K., Kristin

= Beta testers wanted =

I'm currently looking for people willing to test a new version of the plugin before I release it to the public.
Due the fact that I've got only two sites to test the plugin with I need some people who test the plugin on their site.
If you want to participate please let me know. Write me an email or add a comment to the plugin's homepage. Thanks in advance!

= Licence =

The plugin itself (the "code") is released under the GNU General Public License; a copy of this licence can be found at the licence' homepage or
in the gwolle-gb.php file at the top.

For the licences regarding the Askimet classes, the use of reCAPTCHA or the icons you may ask the authors.


== Frequently Asked Questions ==

= Why aren't entries really deleted, and instead stored in the database? =

First, it's not a heavy load for the database, and second, it's that if you clicked wrong you're not lost without the possibility
to 'undo' it via your database interface.

= What about spam? =

I thought about that problem and came up with the solution of Recaptcha. It helps you and your visitors help them. It's really THAT easy.
Second, I integrated Akismet and it works like a charm. Fighting spam has never been easier.

= I'm experiencing problems with the redirection. =

Although the plugin tries to automatically calculate the URL of your guestbook it may be that it doesn't do this job well.
For users who have that problem there's a new option integrated (since version 0.9.4.3) that allows you to set the complete URL
of your guestbook manually.

= How do I localize a plugin? =

A good start to learn about localization and WP plugins is the guide "Localizing a WordPress plugin using poEdit" (http://weblogtoolscollection.com/archives/2007/08/27/localizing-a-wordpress-plugin-using-poedit/)
and also the WordPress documentation.


== Screenshots ==

1. Overview panel, so that you easily can see what's the status.
2. List of the entries. Notice the icons displaying the status of an entry. (Can be turned off in the settings panel.)
3. The editor for entries.
4. Settings panel (showing version 0.9.4.1).


== Changelog ==

0.9.9.0
* 2014-10-22
* Use $wpdb everywhere
* Many small fixes
* Reformat code for readability. It will break diffs in svn though
* Mind your head; Frontend and Backend are open for SQL Injection Attacks.

0.9.8.1
[fix] Fixed bug that prevented posted entries from being published.

0.9.8
[new] Name of the Gwolle-GB directory can now be changed.
[new] News for Gwolle-GB are now shown at the dashboard of the plugin
[new] Dashboard widget for a quick glance at the latest guestbook entries.
[new] Deleted entries are now 'moved to trash', just like the 'trash' feature in the new WP version.
[fix] Rewritten some code. Stills looks messy, but we're on the right track. :)

0.9.7
[new] Removed the 'guestbook link' setting and replaced it with a field for the corresponding $post_id; it should be detected by default.
[fix] New entries are validated and if this fails the user will be sent back to the 'write' page, but now without any $_POST data.
Please note: The widget is planned for a future release. Use it at your own risk.

0.9.6.2 (2nd emergency release)
[fix] 'Number of entries' setting is now applied again to the 'read' frontpage. (Thanks, Kristin!)

0.9.6.1 (emergency release)
[new] Added log message to track if an entry has been changed using the editor.
[fix] Fixed several bugs.

0.9.6
[new] When viewing 'all' entries you can now enable/disable entries by just clicking at the icon. (AJAX, Yey!)
[new] French language (thanks, cobestran.com)
[new] Author's can now be linked to his/her website. (Can be turned off in the settings.)
[new] Smilies are now replaced using the built-in WP smiley engine. (Can be turned off in the settings.)
[fix] 'Spam' is now only an attribute, no longer a state. This results in only two states: 'checked' and 'unchecked'.
[fix] Cleaned up the code a lot and using a lot of functions. (Check the "func.php" files for futher reading.)
[fix] Date is now displayed as configured at the WP options page.
[fix] Admin entries: Only show stuff member's name if found.

0.9.5
[new] You can now import guestbook entries from the popular "DMSGuestbook" plugin easily.

0.9.4.7
[fix] Correctly displaying author's name if it contains HTML elements such as <script> tags
[fix] stripslashes on author's location, so locations like "My aunt's house" are displayed correctly.
[fix] the_content() now just returns the guestbook instead of echo'ing it. This way, other plugins are able to modify the content.
[fix] Added success message when uninstall is completed.

0.9.4.6
[new] Option to output text before/after the [gwolle-gb]-Tag, as requested
[new] Whois link for IP address of the entry author
[new] Entry content can now be send with the notification mail. For security reasons '<' and '>' are send as '{' and '}'.
[new] Polish language (thanks, Daniel Speichert!)
[new] Spanish language (thanks, José Luis Sanz Ruiz!)
[fix] Support for localization of the frontend
[fix] Now coded in XHTML, just as pointed out by "KriLL3"
[fix] German special chars ("Umlaute") are now displayed correctly.
[fix] Metaboxes ('Save' etc.) are displayed again. Sorry for that one, folks!
Now online again with http://wolfgangtimme.de/blog/!

0.9.4.5
As this release alters your Gwolle-GB database tables I highly recommend to backup the old ones.
If you're experiencing any errors please report them immediately. Thanks!
[new] Option to toggle the visibility of line breaks
[fix] All tables & fields are now UTF8 (-> support for Croation letters)

0.9.4.4a
[new] After deleting an entry directly from the entries page you'll be redirected to that specific page (spam/unlocked/locked/all)
[fix] reCAPTCHA library is not include if that's already been done by another plugin.
[new] Dutch translation added. Thank you, timomaas!
[fix] Now using wp_mail() instead of mail() to send email notifications.
[fix] stripslashes on the user-defined admin mail text.
[new] mass editing of entries added
[new] Notification list shows ALL subscribers, including the current user.

0.9.4.3
[fix] Redirection failed again.
[new] Added an option to set link to the guestbook manually.

0.9.4.2.1
[rem] Removed the version check because of some problems.

0.9.4.2
[fix] Redirection to reading page after posting entry sometimes failed.
[new] Option to set the number of entries displayed in reading mode.
[new] Option to set the mail notification text
[new] When uninstalling via the plugin's setting panel you'll be asked to confirm again.
[new] Notification mails now can include a direct link to the editor, speeding things up for you.


