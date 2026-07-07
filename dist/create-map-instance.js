async function N({
  hash: f,
  geojson: r,
  routeGeojson: u,
  waymarkUrl: y,
  basemapUrl: a,
  basemapAttribution: i,
  basemapTitle: n,
  basemapOpacity: L,
  basemapMaxzoom: S,
  messageColour: v,
  trackingColour: x,
  routeColour: O,
}) {
  var F, M;
  if (!y || !f || (!r && !u)) {
    console.warn("inReach MapShare: missing required parameters");
    return;
  }
  let d;
  try {
    ({ createInstance: d } = await import(y));
  } catch (e) {
    console.warn("inReach MapShare: failed to load Waymark", e);
    return;
  }
  const o = [],
    c = [];
  if (r != null && r.features)
    for (const e of r.features)
      ((F = e.geometry) == null ? void 0 : F.type) === "Point"
        ? o.push(e)
        : ((M = e.geometry) == null ? void 0 : M.type) === "LineString" &&
          c.push(e);
  const h = {
      vector: [
        {
          title: "OpenFreeMap",
          styleURL: "https://tiles.openfreemap.org/styles/liberty",
          attributionHTML:
            '&copy; <a href="https://www.openfreemap.org/">OpenFreeMap</a> &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> Data from <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        },
      ],
    },
    R =
      i && typeof i == "string" && i.length
        ? i
        : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, SRTM | &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)',
    m = n && typeof n == "string" && n.length ? n : void 0,
    p = {
      tileURLTemplates: [
        a && typeof a == "string" && a.length
          ? a
          : "https://tile.opentopomap.org/{z}/{x}/{y}.png",
      ],
      attributionHTML: R,
    };
  m && (p.title = m);
  const l = parseFloat(L);
  !isNaN(l) && l >= 0 && l <= 1 && (p.opacity = l);
  const s = parseInt(S, 10);
  !isNaN(s) && s > 0 && (p.maxZoom = s), (h.raster = [p]);
  const T = v || "#e524ab",
    g = x || "#e524ab",
    w = O || "#e29809",
    B = {
      config: {
        id: "inmap-" + f,
        map: { basemaps: h },
        types: {
          message: {
            icon: "envelope",
            paint: { circle: { "circle-color": T, "circle-radius": 8 } },
          },
          tracking: {
            icon: "position",
            iconSize: 1,
            paint: {
              circle: {
                "circle-color": g,
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
          line: { "line-color": g, "line-width": 3, "line-opacity": 0.6 },
        },
      },
    },
    t = d(B);
  t.data.featureProperties.addWhitelistKeys([
    "time_utc",
    "time",
    "event",
    "text",
    "elevation",
    "velocity",
    "valid_gps_fix",
    "waymarkType",
  ]),
    t.data.featureProperties.setEnabled(!0);
  function k(e) {
    return !e || typeof e != "object"
      ? []
      : e.type === "FeatureCollection" && Array.isArray(e.features)
        ? e.features
        : e.type === "Feature"
          ? [e]
          : e.type
            ? [{ type: "Feature", properties: {}, geometry: e }]
            : [];
  }
  t.on("waymark:map.load", () => {
    const e = k(u),
      C = [...o, ...c, ...e];
    C.length &&
      t.data.addLayer(
        {
          data: { type: "FeatureCollection", features: C },
          paint: {
            circle: { "circle-opacity": 0 },
            line: { "line-opacity": 0 },
            fill: { "fill-opacity": 0 },
          },
        },
        { fitBounds: !0 },
      ),
      o.length &&
        t.data.addLayer(
          { data: { type: "FeatureCollection", features: o } },
          { fitBounds: !1 },
        ),
      c.length &&
        t.data.addLayer(
          { data: { type: "FeatureCollection", features: c } },
          { fitBounds: !1 },
        ),
      e.length &&
        t.data.addLayer(
          {
            data: { type: "FeatureCollection", features: e },
            paint: {
              circle: { "circle-color": w, "circle-radius": 5 },
              line: { "line-color": w, "line-width": 4, "line-opacity": 0.8 },
            },
          },
          { fitBounds: !1 },
        );
  });
}
export { N as createMapInstance };
