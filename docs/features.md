---
last_commit: 0bfa5d5
---

# Features & Data Fields

## Features

- Embed an interactive map displaying track points and messages shared with your MapShare page.
- Display your planned route using the Shortcode (URL accessible GeoJSON supported).
- The plugin updates your MapShare every 15 minutes and is cached to improve performance (adjustable in Settings).
- Use the in-built demo to preview how your MapShare will display (`[inreach-mapshare mapshare_identifier="demo"]`).
- Customise in WP Admin > Settings > inReach MapShare.
- Appearance settings:
  - Basemap — the [Waymark](waymark-js/README.md) default vector basemap (Open GIS Outdoors) by default. Optionally set a Basemap URL in Settings to replace it with a raster tile service; attribution, opacity, and max zoom are configurable (no basemap title setting).
  - Colour — pick a colour to suit your theme.
- Content is responsive and adapts to suit devices both small and large.
- Customise content with your own CSS rules.
- Translation ready.

## Displayed Data Fields

Each point displayed contains the following information:

| Field         | Description                                                                                 |
| ------------- | ------------------------------------------------------------------------------------------- |
| Time UTC      | US-formatted version of the event timestamp as UTC                                          |
| Time (local)  | US-formatted version of the event timestamp in the preferred time zone of the account owner |
| Event         | The event log type (e.g. a message, or track point)                                         |
| Text          | Message text, if any, in Unicode                                                            |
| Latitude      | Latitude in degrees WGS84 where negative is south of the equator                            |
| Longitude     | Longitude in degrees WGS84 where negative is west of the Prime Meridian                     |
| Elevation     | Value is always meters from Mean Sea Level                                                  |
| Velocity      | Ground speed of the device. Value is always in kilometers per hour.                         |
| Valid GPS Fix | True if the device has a GPS fix. This not a measure of the quality of GPS fix.             |

For the full KML extended data reference, see [kml-fields.md](kml-fields.md).
