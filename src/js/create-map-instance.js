/**
 * Create an inReach MapShare map instance using Waymark.
 *
 * All config is passed explicitly — no globals, no window.* reads.
 */
export async function createMapInstance({
  hash,
  geojson,
  routeGeojson,
  waymarkUrl,
  basemapUrl,
  basemapAttribution,
  basemapTitle,
  basemapOpacity,
  basemapMaxzoom,
  messageColour,
  trackingColour,
  routeColour,
}) {
  if (!waymarkUrl || !hash || (!geojson && !routeGeojson)) {
    console.warn("inReach MapShare: missing required parameters");
    return;
  }

  let createInstance;
  try {
    ({ createInstance } = await import(waymarkUrl));
  } catch (err) {
    console.warn("inReach MapShare: failed to load Waymark", err);
    return;
  }

  const pointFeatures = [];
  const lineFeatures = [];

  if (geojson?.features) {
    for (const feature of geojson.features) {
      if (feature.geometry?.type === "Point") {
        pointFeatures.push(feature);
      } else if (feature.geometry?.type === "LineString") {
        lineFeatures.push(feature);
      }
    }
  }

  // Always include the vector basemap (OpenFreeMap liberty)
  const basemaps = {
    vector: [
      {
        title: "OpenFreeMap",
        styleURL: "https://tiles.openfreemap.org/styles/liberty",
        attributionHTML:
          '&copy; <a href="https://www.openfreemap.org/">OpenFreeMap</a> &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> Data from <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      },
    ],
  };

  // Build raster basemap entry
  const rasterAttribution =
    basemapAttribution &&
    typeof basemapAttribution === "string" &&
    basemapAttribution.length
      ? basemapAttribution
      : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, SRTM | &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';

  const rasterTitle =
    basemapTitle && typeof basemapTitle === "string" && basemapTitle.length
      ? basemapTitle
      : undefined;

  const rasterUrl =
    basemapUrl && typeof basemapUrl === "string" && basemapUrl.length
      ? basemapUrl
      : "https://tile.opentopomap.org/{z}/{x}/{y}.png";

  const rasterEntry = {
    tileURLTemplates: [rasterUrl],
    attributionHTML: rasterAttribution,
  };

  if (rasterTitle) {
    rasterEntry.title = rasterTitle;
  }

  // Parse opacity as float (0-1), only set if valid
  const parsedOpacity = parseFloat(basemapOpacity);
  if (!isNaN(parsedOpacity) && parsedOpacity >= 0 && parsedOpacity <= 1) {
    rasterEntry.opacity = parsedOpacity;
  }

  // Parse maxzoom as integer, only set if valid positive number
  const parsedMaxzoom = parseInt(basemapMaxzoom, 10);
  if (!isNaN(parsedMaxzoom) && parsedMaxzoom > 0) {
    rasterEntry.maxZoom = parsedMaxzoom;
  }

  basemaps.raster = [rasterEntry];

  const defaultMessageColour = messageColour || "#e524ab";
  const defaultTrackingColour = trackingColour || "#e524ab";
  const defaultRouteColour = routeColour || "#e29809";

  const instanceDocument = {
    config: {
      id: "inmap-" + hash,
      map: { basemaps },
      types: {
        message: {
          icon: "envelope",
          paint: {
            circle: {
              "circle-color": defaultMessageColour,
              "circle-radius": 8,
            },
          },
        },
        tracking: {
          icon: "position",
          iconSize: 1,
          paint: {
            circle: {
              "circle-color": defaultTrackingColour,
              "circle-radius": [
                "interpolate",
                ["linear"],
                ["zoom"],
                3,
                2,
                8,
                4,
                13,
                7,
                18,
                11,
                22,
                15,
              ],
            },
          },
        },
      },
      paint: {
        line: {
          "line-color": defaultTrackingColour,
          "line-width": 3,
          "line-opacity": 0.6,
        },
      },
    },
  };

  const instance = createInstance(instanceDocument);

  instance.data.featureProperties.addWhitelistKeys([
    "time_utc",
    "time",
    "event",
    "text",
    "elevation",
    "velocity",
    "valid_gps_fix",
    "waymarkType",
  ]);
  instance.data.featureProperties.setEnabled(true);

  /** Normalise any GeoJSON type into an array of Feature objects. */
  function extractFeatures(geojson) {
    if (!geojson || typeof geojson !== "object") return [];
    if (geojson.type === "FeatureCollection" && Array.isArray(geojson.features))
      return geojson.features;
    if (geojson.type === "Feature") return [geojson];
    // Bare geometry object — wrap as a feature
    if (geojson.type)
      return [{ type: "Feature", properties: {}, geometry: geojson }];
    return [];
  }

  instance.on("waymark:map.load", () => {
    const routeFeatures = extractFeatures(routeGeojson);
    const allFeatures = [...pointFeatures, ...lineFeatures, ...routeFeatures];

    // Bounds-only layer: invisible, just sets the camera to encompass everything
    if (allFeatures.length) {
      instance.data.addLayer(
        {
          data: { type: "FeatureCollection", features: allFeatures },
          paint: {
            circle: { "circle-opacity": 0 },
            line: { "line-opacity": 0 },
            fill: { "fill-opacity": 0 },
          },
        },
        { fitBounds: true },
      );
    }

    // Track points (styled via config.types)
    if (pointFeatures.length) {
      instance.data.addLayer(
        { data: { type: "FeatureCollection", features: pointFeatures } },
        { fitBounds: false },
      );
    }

    // Track lines (styled via config.paint.line)
    if (lineFeatures.length) {
      instance.data.addLayer(
        { data: { type: "FeatureCollection", features: lineFeatures } },
        { fitBounds: false },
      );
    }

    // Route overlay with distinct paint
    if (routeFeatures.length) {
      instance.data.addLayer(
        {
          data: { type: "FeatureCollection", features: routeFeatures },
          paint: {
            circle: {
              "circle-color": defaultRouteColour,
              "circle-radius": 5,
            },
            line: {
              "line-color": defaultRouteColour,
              "line-width": 4,
              "line-opacity": 0.8,
            },
          },
        },
        { fitBounds: false },
      );
    }

    // After the initial fitBounds animation completes, auto-open the
    // most recent point's popup and zoom in on it regardless of type.
    if (pointFeatures.length) {
      instance.once("waymark:map.moveend", () => {
        // First point is the most recent (server reverses the feed)
        const mostRecent = pointFeatures[0];
        if (mostRecent) {
          instance.data.featureProperties.showPopup(mostRecent);
        }
      });
    }
  });
}
