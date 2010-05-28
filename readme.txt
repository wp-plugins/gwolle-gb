=== Gwolle-GB ===
Contributors: Gwolle
Tags: guestbook, feedback, antispam
Requires at least: 2.9
Tested up to: 3.0 RC1
Stable tag: 0.9.7

Gwolle-GB is the WordPress guestbook you've just been looking for. Beautiful and easy.

== Description ==

Looking for a guestbook that just fits my needs, I found myself lost in a WordPress plugin database full of plugins
which just pretend to be a guestbook, like the guestbook builder does. But it is not more than just a page with a comment
form. That wasn't what I was looking for, so I came up with developing my own plugin for WordPress.
It works quite good actually, and I'm looking forward to integrate some other cool features, but for the moment you have:

* Simple and clean interface that integrates seamlessly into /wp-admin.
* Easy import of DMSGuestbook entries into Gwolle-GB.
* Notification by mail when a new entry has been posted.
* Moderation, so that you have to check an entry before it is visible in your guestbook (can be turned off).
* Recaptcha integration. (Fighting spam!)
* Akismet integration (Fighting spam, too!)
* Localisation (currently english, german, french, spanish, polish and dutch). Own languages can be added very easily, check WP documentation on this. If you translated the plugin I'd be glad to include your language in a future release, so please send them to my via mail, thanks!
* different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin'
* a log for each entry, so that you know which member of the staff released and edited an article to the public and when.
* IP-address- and host-logging with link to WHOIS query site
* Smiley integration (uses the WordPress smiley engine)
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress 2.9 look.

You may have "DMSGuestbook" installed - that's great, because since version 0.9.5 Gwolle-GB enables you
to import DMSGuestbook's entries easily using an assistant. The importer does not delete any of your data,
so you can go back to DMSGuestbook without loss of data, if you want to. Trying Gwolle-GB is easy as 1-2-3.

For a demo, visit the plugin's homepage (http://www.wolfgangtimme.de/blog/category/gwolle-gb/). Feel free to drop me a message
in the WordPress.org forums or send me an email to gwolle (at) wolfgangtimme (dot) de. You may also
use my homepage's comment section. I'd be glad to hear your opinion and/or feature request.

Please note: At the moment, Gwolle-GB does *not* work with WordPress MU. I'm working on it, but at the moment it's just not working. Sorry, guys!

= Changelog =

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

== Installation ==

1. Upload the directory 'gwolle-gb' (where the 'gwolle-gb.php' is in) to your '/wp-content/plugins' directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place '[gwolle-gb]' in an article or page. That's it.
1. You may disable comments in this arcticle/page, because it may look stupid when there's the possibility to write a guestbook entry. ;) 

Gwolle-GB take the `the_content`-hook and replaces a article/page containing [gwolle-gb] with the guestbook. You may also use
`add_gwolle_gb_frontend_css();` to include the frontend CSS of Gwolle-GB and `show_gwolle_gb();`
to show the guestbook in your templates. It couldn't be easier.

If you still got problems, or if errors come up please leave a message at the WordPress forum so I can help you and fix it. Thanks!


==	Todo/coming up in future releases/plans ==

The following list contains things I'd like to include in future releases. If you have a feature request please use the forum
on WordPress.org; I'll may add it to the list then.

* More inline documentation
*	Better permission management (the current one is poor)
* Gravatar support
* Custom fields (e. g. 'ICQ number' etc.)
* More functions & methods, less code redundancy.
* Support for WordPress MU.
* Better database structure (e. g. 'id' insted of 'entry_id')
* Widget (at the dashboard as well as the sidebar(s))
* 'Thank you' mail to the poster (requested by Joakim from Sweden)

== Known bugs ==

* Some WP installations with permalinks activated receive a 404-error. Going to fix this, currently going through the WP documentation on this.
* Some WP installations don't send the notification mails (currently only known from WP MU installations)
* Marking multiple entries as spam sometimes takes very long or results in an error message.

Have something to add here? Please add a new thread in the WP.org forums and tag it with "Gwolle-GB". I subscribed to that forums,
so I'll get your message. Or, if you're in a hurry, send me an email: gwolle (at) wolfgangtimme (dot) de. Thanks!

==	Thank you! ==

* timomaas for the dutch translation
* Daniel Speichert for the polish translation
* José Luis Sanz Ruiz for the spanish translation
* cobestran.com for the french translation
* All the bug posters, including Berrie Pelser, Sebastian Moeller, voodoobanshee, Peter Pollack, Werner Traschuetz, Dean Suhr, Georg K., Kristin

== Beta testers wanted ==

I'm currently looking for people willing to test new releases of the plugin before I release it to the public.
Due the fact that I've got only two sites to test the plugin with I need some people who test the plugin on their site.
If you wanted to participate please let me know. Write me an email or add a comment to the plugin's homepage. Thanks in advance!


== Frequently Asked Questions ==

= Why aren't entries really deleted, and instead stored in the database? =

First, it's not a heavy load for the database, and second, it's that if you clicked wrong you're not lost without the possibility
to 'undo' it via your database interface.

= What about spam? =

I thought about that problem and came up with the solution of Recaptcha. It helps you and your visitors help them. It's really THAT easy.
Second, I integrated Akismet and it works like a charm. Fighting spam has never been easiser!

= I'm experiencing problems with the redirection. =

Although the plugin tries to automatically calculate the URL of your guestbook it may be that it doesn't get this job done well.
For users who have that problem there's a new option integrated (since version 0.9.4.3) that allows you to set the complete URL
of your guestbook manually.

= How do I localize a plugin? =

A good start to learn about localization and WP plugins is the guide "Localizing a WordPress plugin using poEdit" (http://weblogtoolscollection.com/archives/2007/08/27/localizing-a-wordpress-plugin-using-poedit/)
and also the WordPress documentation.

== Licence ==

The plugin itself (the "code") is released under the GNU General Public License; a copy of this licence can be found at the licence' homepage or
in the gwolle-gb.php file at the top.

For the licences regarding the Askimet classes, the use of reCAPTCHA or the icons you may ask the authors.

== Screenshots ==

1. Overview panel, so that you easily can see what's the status.
2. List of the entries. Notice the icons displaying the status of an entry. (Can be turned off in the settings panel.)
3. The editor for entries.
4. Settings panel (showing version 0.9.4.1).