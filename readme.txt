=== inReach MapShare ===
Contributors: morehawes
Tags: inreach, mapshare, embed, map, share, location
Requires at least: 4.6
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display Your Live inReach MapShare Data on Your WordPress Site.

== Description ==

This plugin enables users of Garmin inReach devices to display their *live* <a href="https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7">MapShare</a> data using a simple Shortcode:

`
[inreach-mapshare mapshare_identifier="your_identifier"]
`

By default only your most recent location is displayed. To display more data, Start and end dates can be provided through the Shortcode like this:

`
[inreach-mapshare mapshare_identifier="your_identifier" mapshare_date_start="2022-06-15T00:00" mapshare_date_end="2022-07-31T23:59"]
`

The MapShare feature must be enabled in the <a href="https://explore.garmin.com/Social">Social</a> tab of your Garmin Explore account.

Features:

* Embed an interactive Map and timeline containing track points and messages shared with your MapShare page.
* Each point displayed contains the following information:
	* Time UTC
	* Time (local)
	* Event (e.g. a message, or track point)
	* Text (for messages)
	* Latitude
	* Longitude
	* Elevation
	* Velocity
	* Valid GPS Fix
* The plugin updates your MapShare every 15 minutes and is cached to improve performance (adjustable in Settings).
* Use the in-built demo to preview how your MapShare will display (`[inreach-mapshare mapshare_identifier="demo"]`).
* Customise in WP Admin > Settings > inReach MapShare.
* Appearance settings:
	* Basemap - <a href="https://www.openstreetmap.org/fixthemap">OpenStreetMap</a> by default, lots of <a href="https://leaflet-extras.github.io/leaflet-providers/preview/">other providers</a> supported.
	* Colour - pick a colour to suit your theme.
	* Icons - use SVG images as icons.
* Content is responsive, and adapts to suit devices both small and large.
* Customise content with your own CSS rules.

If you experience any issues with the plugin, ensure that your MapShare page (i.e. share.garmin.com/[your_identifier]) is displaying data. This is important - this plugin can only display data available to your MapShare.

Please report issues or make suggestions by creating either a <a href="https://wordpress.org/support/plugin/inreach-mapshare/">support topic</a>, or <a href="https://github.com/morehawes/inreach-mapshare/issues">GitHub issue</a>.

*This plugin is free, open-source software and is not maintained by Garmin. Data is requested from the Garmin <a href="https://support.garmin.com/?faq=tdlDCyo1fJ5UxjUbA9rMY8">inReach KML Feeds</a> service.*

== Installation ==

- Ensure MapShare is enabled in the <a href="https://explore.garmin.com/Social">Social</a> tab of your Garmin Explore account.
- Take note of your unique MapShare Address (i.e. share.garmin.com/[your_identifier]). If you have set a password to protect your MapShare page, you will also need to take note of this.
- Visit your MapShare address in your browser and verify that there is MapShare data (i.e. a message or track point) displaying. Any test message <a href="https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7">shared with your MapShare page</a> will work.
- With this plugin is activated, go to WP Admin > Settings > inReach MapShare.
- Enter your MapShare address and submit.
- You should see your most recent location, or multiple locations if you supplied a date range.
- Add the provided Shortcode anywhere Shortcodes are supported to display the content on your site.

== Frequently Asked Questions ==

= What is an inReach device? =

<a href="https://discover.garmin.com/inreach/personal/">These</a> are small handheld "satellite communicators" which allow users (with an active <a href="https://www.garmin.com/p/837461">subscription</a>) the ability to send tracking and text/email messages from remote areas, unserved by other means of communication.

= What is MapShare? How do I set it up? =

<a href="https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7">MapShare</a> is an inReach feature which allows friends or loved ones the ability follow progress in real time online.

= What does this plugin Do? =

This plugin requests data from your MapShare page and embeds it anywhere Shortcodes are supported.

== Screenshots ==

1. 

== Changelog ==

= 1.0 = 

Initial plugin release.