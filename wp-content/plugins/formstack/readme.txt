=== Formstack Online Forms ===
Contributors: mmattax, noahwesley, jeremyformstack, brianFormstack
Donate link: http://www.formstack.com
Tags: forms, online forms, web forms, form builder, lead generation, contact form, contact forms, surveys, order forms, online order forms, online surveys, registration forms, event registration forms, lead generation form
Requires at least: 2.8
Tested up to: 4.3
Stable tag: 1.0.10

This plugin allows you to easily embed Web forms built with Formstack's online form builder
into your sidebar, pages, and posts.

== Description ==

The Formstack plugin makes embedding your [Formstack](http://www.formstack.com "Online Form Builder")
web forms as easy as clicking a button. This plugin features two components:

*    Formstack Widget
*    Formstack Plugin

All you need is a Formstack account and the plugin.  [Signup for free at Formstack](http://www.formstack.com/pricing?source=wp-plugin_utm_medium=pluginlisting "Free Online Forms")

The Formstack widget allows you to embed Formstack web forms into your sidebar. The widget automatically optimizes the web form's
CSS so that your online form will fit and look great in the sidebar.

The Formstack plugin will add a button to the TinyMCE editor that allows you to easily select a Formstack web form
you wish to embed. Once a form is selected, a shortcode will be inserted into the editor, which will be converted
to the selected form once your page or post is rendered.

This plugin supports the following shortcodes `[Formstack]`, `[formstack]`, and `[fs]`.

This plugin requires a [Formstack](http://www.formstack.com "Online Form Builder") account and an API key.

== Installation ==

1. Upload `Formstack.zip` via the Upload link in the WordPress plugins dashboard.
2. Activate the Formstack Plugin through the 'Plugins' menu in WordPress
3. Activate the Formstack Widget through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where can I find my Formstack API key? =

Your Formstack API key can be found or created in your account settings. Go here:
to find to create an API key: https://www.formstack.com/admin/apiKey/main

== Changelog ==

= 1.0.10 =
* Update compatibility reference for 4.2

= 1.0.9 =
* Update compatibility reference for 4.1

= 1.0.8 =
* Resolve issues with the Widget in Wordpress 3.9
* Resolve issues with Widget when no API Key is saved for the Plugin

= 1.0.7 =
* Fix incompatibilities with Worpdress 3.9

= 1.0.6 =
* Formstack side-menu now properly links to appropriate Formstack
* functionality, no longer hardcoding embedded forms's version

= 1.0.5 =
* Formstack side-menu now defaults to the bottom (instead of possibly over-writing an existing menu).

= 1.0.4 =
* Increased functionality, build forms within Wordpress, better error messages

= 1.0.3 =
* Added PHP version to the options page for easier troubleshooting.

= 1.0.2 =
* Added Formstack API status area on the plugin settings page.

= 1.0.1 =
* Now using wp_remote_fopen for improved server compatibility. Should fix errors some people were having when loading the widgets page.
* Minor housekeeping

= 1.0.0 =
* Hello World

== Upgrade Notice ==

= 1.0.8 =
* Resolve issues with the Widget in Wordpress 3.9
* Resolve issues with Widget when no API Key is saved for the Plugin

= 1.0.7 =
* Fix incompatibilities with Worpdress 3.9

= 1.0.6 =
Fix Forms link and removed broken Submissions link. Forms embeeded through
this plugig are no longer hard-coded to -v2.

= 1.0.4 =
Significant upgrade to the plugin. Should resolve many problems experienced when inserting a form in a page/post.

= 1.0.2 =
Fix for servers that don't support CURL. Upgrade if you are seeing error messages.
