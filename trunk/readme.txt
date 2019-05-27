=== Landing Page Creator With Custom Posts ===
Contributors: iranalves85
Tags: lpccp, landing page, custom posts, leading page, pages, custom pages, page creator, page with posts, A-B tests, tests, personalized pages, page creator, page creator posts, landing page plugin, page with sections, sections for pages, sections for landing pages, create sections, page with custom posts, personalized page content, conversion page, generate lead, plugin with advanced custom fields, lead, conversion, custom appeareance, personalized sections, wordpress leading page, wordpress landing page, wordpress pages, wordpress custom section, plugin create pages, construct page, build page, build page posts, custom posts build, build landing page, personalized build, personalize page, tiny builder, tiny block builder, bootstrap builder, lightweight builder, lightweight page builder, page builder, tiny page builder.
Donate link: https://goo.gl/dN6U3T
Requires at least: 3.9.23
Tested up to: 5.2.1
Requires PHP: 5.4 >=
Stable Tag: 0.3.3
License: GPL2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Create landing pages, A-B tests, or other types of pages using custom posts. Set background color, background image or video, amount of content columns and edit as a common post.

== Description ==

Create landing pages, A-B tests, or other types of pages using custom posts. Set background color, background image or video, amount of content columns and edit as a common post. 

Important: Requires at least version 5.4.9 of PHP language

* Create ou convert native WordPress pages in 'Landing Pages'.
* Add scripts inside the head and body tags of pages defined as landing page.
* Set a menu for your landing page using the default editor allowing for free customization, your creativity is the limit.
* Create sections to be included in landing pages, allowing you to create different page variations, A-B tests and content reuse;
* Customize sections by defining background and text colors, adding images or videos as background, and setting your placement.


== Installation ==

1 - Upload the file "landingpage-creator-with-custom-posts.zip" to the "/wp-content/plugins/" directory and unzip.
2 - In the "Plugins" menu, click "Enable" to enable it in the installation of WordPress.
3 - In the "Settings -> Landing Page Creator With Custom Posts" menu, select native WordPress pages to act as landing pages or create a new page in the "Landing Pages" menu.
4 - Create sections for this page in "Sections". Add content, set background attributes such as "color," "image," and more.
5 - Go back to the page defined as landing page, go to "Sections to show" and select the sections you want. When finished save the page.
6 - Ready. Now just view the page, make corrections and adaptations to be in your liking.

== Frequently Asked Questions ==

= How does the plugin work? =
The plugin uses custom posts to compose the sections of a landing page. When creating a section, you can assign it to different pages defined as landing page, so you can create page variations, A-B tests and more. You can customize the sections by choosing background color, background images or video, and edit content in the same way as an ordinary post.
= How to enable a native page to become a landing page? =
Go to the plugin's configuration page in the side menu by locating in "Settings -> Landing Page Creator With Custom Posts". You will find a table listing all current WordPress pages, just select your options and save.
= I liked the plugin and would like to contribute to the developer, how can I help? =
If this plugin helped you in any way and would like to pay for a coffee through [Paypal] (https://goo.gl/dN6U3T) or evaluate the plugin in the repository of WordPress plugins, thank you immensely. WordPress is love!

== Screenshots ==

1. Page as landing page with default header enabled.
2. Leading Page customization and configuration
3. Settings Page to enable native pages act as landing page.

== Changelog ==

= 0.3.3 = 
* Added: Support to Wordpress Gutenberg Editor
* Bugfixes: Fix error when plugin is activated (if ACF plugin is not installed)
* Bugfixes: Fix error when save a empty form in plugin configuration
* Changed: Removed support for video background. Modern browsers doesn't support autoplay videos (https://developers.google.com/web/updates/2017/09/autoplay-policy-changes). 

= 0.3.2 =
* Changed:  Check if required plugin 'Advanced Custom Fields' is installed and active, now is necessary install ACF to use this plugin 
* Changed: Now support version 5.0 and above of 'Advanced Custom Fields'
* Bugfixes: Fix minor errors 

= 0.3.1 =
* Bugfixes: Support PHP 5.4 and above
* Bugfixes: Support Wordpress 3.9 and above 
* Bugfixes: Fix minor errors 

= 0.3 =
* Bugfixes: Fix error support for PHP 5.4 and later versions. 
* Bugfixes: Fix implementation of empty() function in code.
* Changed: Not need file template for each type of section. Now only load a single file for that
* Test: Tested in WordPress 3.9.23 installation with debug enabled 

= 0.2 =
* Bugfixes: Fix PHP errors showing in screen, if no page is defined as landing page.
* Add more tags for better search in plugin repository. 

= 0.1 =
* Created

== Translations ==

* Português - Default!
* English: Must have!

== Credits ==
* Thanks to Elliot Condon for developing [Advanced Custom Fields] plugin (https://www.advancedcustomfields.com) and allowing inclusion in this plugin. The "LPCCP" plugin uses a free version of ACF4 to create custom fields for custom posts and pages.