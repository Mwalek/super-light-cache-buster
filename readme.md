# Super Light Cache Buster

Contributors: mwalek  
Tags: cache, cachebuster, prevent, clear, buster  
Requires at least: 4.6  
Tested up to: 6.1  
Requires PHP: 5.2  
License: GPLv3 or later  
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html  
Stable tag: 1.2.0

Stop browser caching by randomizing asset version numbers.

## Description

Can't see the changes supposedly made by your developer? Or maybe you're just tired of your site's annoying cache? Use this plugin to cache a break!

With a compressed size of under 21KB, this simple plugin adds random version numbers to CSS & JS assets to prevent page and browser caching getting in the way of your happiness.

You can completely disable the plugin from the settings page when you are not using it or keep it enabled if the site is under development 😀.

### Feedback

- I am open for your suggestions and feedback - Thank you for using or trying out one of my plugins!
- Drop me a line [@mwale_and_sons](https://twitter.com/mwale_and_sons) on Twitter
- Follow me on [my LinkedIn page](https://www.linkedin.com/in/mwale-kalenga)
- Or visit me at [my website](https://mwale.me/).

## Installation

1. Upload the entire `super-light-cache-buster` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Clear all caches on your site and server (hosting).
4. Cache a break from caching :-).

## Frequently Asked Questions

### Do I have to clear my cache for the plugin to work?

Yes, you should clear your cache(s)! This plugin doesn't clear pages that have already been cached (coming soon, shhh).

If you are still being served cached pages after activating the plugin, clear the cache at least once.

This will delete all pages/posts from the cache, and Cache Buster will prevent them from being cached again while the plugin is enabled :-).

### Does this plugin work on Multisite?

No, Cache Buster hasn't been tested on Multisite Networks.

## Changelog

### 1.2.0

Release Date - November 13, 2022

**Added**

- Introduce Internationalization (Make Cache Buster translatable).
- Introduce a notice to clear the cache when settings are saved.

**Fixed**

- Patch XSS vulnerability.
- Patch File-Handling Vulnerability.

**Enhanced**

- Improve inline code documentation.
- Improve WordPress Coding Standards conformance.

### 1.1.2

Release Date - September 29, 2022

**Fixed**

- Fix warning/error thrown when Cache Buster is uninstalled.

**Enhanced**

- Use a static method when register_uninstall_hook is invoked.

### 1.1.1

Release Date - May 06, 2022

**Enhanced**

- Don't randomize asset version numbers in the admin area.
- Hide Cache Buster's status from non-admins.

### 1.1.0

Release Date - April 25, 2022

**Added**

- Introduce plugin settings to enable/disable cache prevention and control its intensity.
- Introduce a menu item to show Cache Buster's status in the admin bar.
- Introduce `Cache-Control` directives.

**Enhanced**

- Delete database options when Cache Buster is uninstalled.
- Improve overall code efficiency.

### 1.0.0

Release Date - June 24, 2020

- Initial release

## Upgrade Notice

### 1.1.1

Fixes PHP vulnerabilities and adds a reminder to clear the cache after updating settings.

### 1.1.1

Fixes the visibility of Cache Buster's status, preventing non-admins from seeing it.
