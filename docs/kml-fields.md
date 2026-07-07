---
last_commit: d59e8a7
---

# KML Extended Data Fields

| Name             | Description                                                                                           | Example                   |
| ---------------- | ----------------------------------------------------------------------------------------------------- | ------------------------- |
| ID               | Garmin internal ID for the event                                                                      | 868926                    |
| Time UTC         | US-formatted version of the event timestamp as UTC                                                    | 5/2/2020 6:01:30 AM       |
| Time             | US-formatted version of the event timestamp in the preferred time zone of the account owner           | 5/2/2020 9:01:30 AM       |
| Name             | First and last name of the user assigned to the device that sent the message                          | Joe User                  |
| Map Display Name | Map Display Name for this user. This field is editable by the user in their Account or Settings page. | Joe the inReach User      |
| Device Type      | The hardware type of the device in use                                                                | inReach 2.5               |
| IMEI             | The IMEI of the device sending the message                                                            | 300000000000000           |
| Incident ID      | The ID of the emergency event if there is one.                                                        | 1234                      |
| Latitude         | Latitude in degrees WGS84 where negative is south of the equator                                      | 43.790485                 |
| Longitude        | Longitude in degrees WGS84 where negative is west of the Prime Meridian                               | -70.192015                |
| Elevation        | Value is always meters from Mean Sea Level                                                            | 120.39 m from MSL         |
| Velocity         | Ground speed of the device. Value is always in kilometers per hour.                                   | 1.0 km/h                  |
| Course           | Approximate direction of travel of the device, always in true degrees.                                | 292.50° True              |
| Valid GPS Fix    | True if the device has a GPS fix. This not a measure of the quality of GPS fix.                       | True                      |
| In Emergency     | True if the device is in SOS state.                                                                   | False                     |
| Text             | Message text, if any, in Unicode                                                                      | I am doing good!          |
| Event            | The event log type. See table below under Event Log Types                                             | Tracking Message Received |
