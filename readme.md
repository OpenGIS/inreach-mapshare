# inReach MapShare #
**Contributors:** [morehawes](https://profiles.wordpress.org/morehawes/)  
Tags: 
**Requires at least:** 4.6  
**Tested up to:** 6.0  
**Requires PHP:** 5.2  
**Stable tag:** 1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Display inReach MapShare Data on Your WordPress Site

## Description ##

This plugin enables users of Garmin inReach devices to display live <a href="https://explore.garmin.com/Social">MapShare</a> data using a simple Shortcode:


	[inreach-mapshare mapshare_identifier="your_identifier"]


By default only your most recent location is displayed. To display more data, Start and end dates can be provided through the Shortcode like this:


	[inreach-mapshare mapshare_identifier="your_identifier" mapshare_date_start="2022-06-15T00:00" mapshare_date_end="2022-07-31T23:59"]


The MapShare feature must be enabled in the <a href="https://explore.garmin.com/Social">Social</a> tab of your Garmin Explore account.

Features:

* An interactive Map displays track points and messages sent to your MapShare (supports password protected MapShare pages).
* Each Point displayed contains the following information:
	* Time UTC
	* Time (local)
	* Event
	* Text (for messages)
	* Latitude
	* Longitude
	* Elevation
	* Velocity
	* Valid GPS Fix
* MapShare data refreshes every 15 minutes and is cached to improve performance (adjustable in Settings).
* Use the in-built demo to preview how your MapShare will display (`[inreach-mapshare mapshare_identifier="demo"]`).
* Customise in WP Admin > Settings > inReach MapShare
* Appearance settings:
	* Basemap - <a href="https://www.openstreetmap.org/fixthemap">OpenStreetMap</a> by default, <a href="https://leaflet-extras.github.io/leaflet-providers/preview/">other providers</a> are supported.
	* Colour - pick a colour to suit your theme.
	* Icons - use SVG images as icons.

*This plugin is free, open-source software and is not maintained by Garmin. Data is requested from Garmin using the <a href="https://support.garmin.com/en-CA/?faq=tdlDCyo1fJ5UxjUbA9rMY8">inReach KML Feeds</a> service.*

## Installation ##


## Frequently Asked Questions ##

### What information is displayed? ###



## Screenshots ##

### 1.  ###
![](https://ps.w.org/inreach-mapshare/assets/screenshot-1.jpg)


## Changelog ##

### 1.0 ###

Initial plugin release.