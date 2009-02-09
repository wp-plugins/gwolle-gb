=== Gwolle-GB ===
Contributors: Gwolle
Tags: guestbook, feedback, antispam
Requires at least: 2.7
Tested up to: 2.7

Gwolle-GB is the Wordpress guestbook you've just been looking for. Beautiful and easy.

== Description ==

Looking for a guestbook that just fits my needs, I found myself lost in a Wordpress plugin database full of plugins
which just pretend to be a guestbook, like the guestbook builder does. But it is not more than just a page with a comment
form. That wasn't what I was looking for, so I came up with developing my own plugin for Wordpress.
It works quite good actually, and I'm looking forward to integrate some other cool features, but for the moment you have:

* Notification by mail when a new entry has been posted.
* Moderation, so that you have to check an entry before it is visible in your guestbook (can be turned off).
* Recaptcha integration. (Fighting spam!)
* Akismet integration (Fighting spam, too!)
* Localisation (currently english and german). Own languages can be added very easily, check WP documentation on this.
* different-styled admin entries, so that the visitor can tell which entry is written by the 'real admin'
* a log for each entry, so that you know which member of the staff released an article to the public and when.
* IP-address- and host-logging
... and all that integrated in the stylish Wordpress 2.7 look.

== Installation ==

1. Upload the directory 'gwolle-gb' (where the 'gwolle-gb.php' is in) to your '/wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
1. Place '[gwolle-gb]' in an empty article. That's it.

==	Todo/coming up in future releases/plans ==

The following list contains things I'd like to include in future releases. If you have a feature request please use the forum
on wordpress.org; I'll may add it to the list then.

* Customizable email texts
* More inline documentation
*	Better permission management (the current one is very poor)

== Frequently Asked Questions ==

= Why aren't entries really deleted, and instead stored in the database? =

First, it's not a heavy load for the database, and second, it's that if you clicked wrong you're not lost without the possibility
to 'undo' it via your database interface.

= What about spam? =

I thought about that problem and came up with the solution of Recaptcha. It helps you and your visitors help them. It's really THAT easy.
Second, I integrated Akismet and it works like a charm. Fighting spam has never been easiser!

== Licence ==

The plugin itself (the "code") is released under the GNU General Public License; a copy of this licence can be found at the licence' homepage or
in the gwolle-gb.php file at the top.

For the licences regarding the Askimet classes, the use of reCAPTCHA or the icons you may ask the authors.

== Screenshots ==

1. Overview panel, so that you easily can see what's the status.
2. List of the entries. Notice the icons displaying the status of an entry. (Can be turned off in the settings panel.)
3. The editor for entries.
4. Settings panel (showing version 0.9.4.1).