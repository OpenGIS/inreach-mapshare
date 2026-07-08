---
last_commit: d59e8a7
---

# inReach MapShare

> Display **live** Garmin inReach [MapShare](https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7) data on your WordPress site with a simple Shortcode.

[View Example &raquo;](https://www.morehawes.ca/trips/arizona-2024/)

> [!CAUTION]
> Even if you have a MapShare password set, this plugin simply uses it to request your data; it does not protect it from being viewed. You are responsible for [protecting access](https://wordpress.org/support/article/using-password-protection/) if needed.

> [!NOTE]
> This plugin is free, open-source software and is not maintained by Garmin. Data is requested from the Garmin [inReach KML Feeds](https://support.garmin.com/?faq=tdlDCyo1fJ5UxjUbA9rMY8) service.

```
[inreach-mapshare mapshare_identifier="your_identifier"]
```

Accepts optional parameters `mapshare_date_start`, `mapshare_date_end`, and `mapshare_route_url` (GeoJSON URL). See [docs/configuration.md](docs/configuration.md) for full setup.

![inReach MapShare plugin map view](https://raw.githubusercontent.com/opengis/inreach-mapshare/master/src/img/screenshot-1.png)

**If you find value in this software please consider supporting it's continued development through [sponsorship](https://github.com/sponsors/OpenGIS). Any amount is appreciated.**

For features and displayed data fields, see [docs/features.md](docs/features.md).

If you experience any issues with the plugin, ensure that your MapShare page (i.e. share.garmin.com/[your_identifier]) is displaying data. This is important — this plugin can only display data available to your MapShare.

## Installation

1. [Download the plugin (.zip)](https://github.com/OpenGIS/inreach-mapshare/archive/refs/heads/master.zip).
2. In your WordPress Admin, go to Plugins > Add New Plugin.
3. Click the "Upload Plugin" button.
4. Upload the `inreach-mapshare.zip` file downloaded.
5. Activate the plugin.
6. Configure the plugin in WP Admin > Settings > inReach MapShare.

## Configuration

See [docs/configuration.md](docs/configuration.md) for step-by-step setup instructions.

### Development

> [!NOTE]
> **Development dependencies:** [Node.js and npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) and [Docker](https://docs.docker.com/engine/install/) are required.

```bash
# 1. Clone and enter the repo
git clone --recurse-submodules https://github.com/OpenGIS/inreach-mapshare.git
cd inreach-mapshare

# 2. Install dependencies
npm install

# 3. Start the local WordPress environment (Docker must be running!)
npm run dev

# 4. Build assets (Vite compiles JS, CSS, LESS)
npm run build

# 5. Run browser tests (Playwright smoke suite)
npm run test:browser
```

[Vite](https://vitejs.dev/) handles the build pipeline. The local dev environment uses [`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) which manages WordPress and MySQL in Docker containers.

Pull requests are welcome!

## Further Reading

- [Features & data fields](docs/features.md)
- [Configuration](docs/configuration.md)
- [FAQ](docs/faq.md)
- [KML Extended Data Fields](docs/kml-fields.md)


