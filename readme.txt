=== Super Light Cache Buster ===
Contributors: mwalek
Tags: asset version, cache, stop cache, prevent caching, cachebuster
Requires at least: 4.0
Tested up to: 5.9.3
Requires PHP: 5.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Stable tag: 1.0.1


Stop browser caching by randomizing asset version numbers.

== Description ==
Can't see the changes supposedly made by your developer? Or maybe you're just tired of your site's annoying cache? Use this plugin to cache a break!

With a compressed size of under 25KB, this simple plugin adds random version numbers to CSS & JS assets to prevent page and browser caching getting in the way of your happiness.

You can completely disable the plugin from the settings page when you are not using it or keep it enabled if the site is under development 😀.

= Feedback =
* I am open for your suggestions and feedback - Thank you for using or trying out one of my plugins!
* Drop me a line [@mwale_and_sons](https://twitter.com/mwale_and_sons) on Twitter
* Follow me on [my Instagram page](https://www.instagram.com/mwale_and_sons/)
* Or visit me at [my website](https://mwale.me/).

== Installation ==
 
1. Upload the entire `super-light-cache-buster` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Clear all caches on your site and server (hosting).
4. ~~Catch~~ Cache a break from caching :-).

== Frequently Asked Questions ==

= Do I have to clear my cache for the plugin to work? =
Yes, you should clear your cache(s)! This plugin doesn't clear pages that have already been cached (coming soon, shhh).

If you are still being served cached pages after activating the plugin, clear the cache at least once.

This will delete all pages/posts from the cache, and Cache Buster will prevent them from being cached again while the plugin is enabled :-). 

== Changelog ==

= 1.1.0 =

Release Date - April 24, 2022

**Added**
* Introduce plugin settings to enable/disable cache prevention and control it's intensity.
* Introduce a menu item to show plugin's status in the admin bar.
* Introduce Cache-Control derictives.

**Enhanced**
* Delete database options when plugin is uninstalled.
* Improve overall code efficiency.
* 
= 1.0.1 =

Release Date - May 23, 2021

= 1.0.0 =

* Switch to semantic versioning.

Release Date - June 24, 2020

* Initial release