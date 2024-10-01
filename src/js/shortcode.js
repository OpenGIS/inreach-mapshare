//Leaflet
window.inmap_maps = [];

window.inmap_create_map = function (
	map_hash = null,
	map_geojson = null,
	route_geojson = null,
) {
	if (!map_hash || !map_geojson || !jQuery || typeof inmap_L !== "object") {
		return false;
	}

	// Leaflet Map options
	var map_options = {
		attributionControl: false,
	};

	// Map Container
	var map_id = "inmap-" + map_hash;

	var map_jq = jQuery("#" + map_id);
	if (!map_jq.length) {
		return false;
	}

	// Leaflet Map object
	var map_l = inmap_L.map(map_id, map_options);

	// Add attribution
	var map_attribution = inmap_L.control.attribution({
		prefix: false,
	});
	map_attribution.addAttribution(
		'<a href="https://github.com/OpenGIS/inreach-mapshare">Inreach Mapshare</a>',
	);
	map_attribution.addTo(map_l);

	//Make accessible
	map_jq.data("map_l", map_l);
	inmap_maps[map_hash] = map_l;

	//UI
	var body_jq = jQuery("body").first();
	var wrap_jq = map_jq.parents(".inmap-wrap", body_jq);
	var info_jq = jQuery(".inmap-info", wrap_jq);
	var markers_l = {};
	var markers_jq = {};
	var infos_jq = {};
	var info_last_jq = {};
	var info_active_jq = {};
	var map_ui_jq = {};
	var marker_active_l = {};

	//Resize Latest
	var redraw_last = function () {
		var container_height = info_jq.height();
		var item_height = info_last_jq.height();
		var height_diff = container_height - item_height;

		//Only if container is taller than item
		if (height_diff > 0) {
			info_jq.css("padding-top", item_height + "px");
		} else {
			info_jq.removeAttr("style");
		}
	};

	var setup_ui = function () {
		setup_info_ui();
		setup_map_ui();

		jQuery(window).on("resize", function () {
			redraw_ui();
		});
		redraw_ui();
	};

	var redraw_ui = function () {
		if (wrap_jq.hasClass("inmap-fullscreen")) {
			wrap_jq.css({
				width: body_jq.width() + "px",
				height: body_jq.height() + "px",
				zIndex: 9999999999999,
			});
		} else {
			wrap_jq.removeAttr("style");
		}

		redraw_last();

		if (typeof info_active_jq.get === "function") {
			info_active_jq.get(0).scrollIntoView({
				behaviour: "smooth",
				block: "center",
			});
		}

		//Redraw inmap_L
		map_l.invalidateSize();

		// If valid Leaflet Marker
		if (marker_active_l && marker_active_l.getLatLng) {
			map_l.setView(marker_active_l.getLatLng());
		}
	};

	var setup_map_ui = function () {
		map_ui_jq = jQuery(
			".leaflet-control-container .leaflet-top",
			map_jq,
		).first();

		// Fullscreen
		var fullscreen_control = jQuery("<div />")
			.attr({
				class: "inmap-control leaflet-bar leaflet-control",
			})
			.append(
				jQuery("<a />")
					.attr({
						class: "inmap-button inmap-icon inmap-icon-fullscreen",
						href: "#",
						title: "Fullscreen",
						role: "button",
						"aria-label": "Fullscreen",
					})
					.on("click", function (e) {
						e.preventDefault();

						body_jq.toggleClass("inmap-has-single");

						wrap_jq.toggleClass("inmap-fullscreen");

						redraw_ui();
					}),
			);
		map_ui_jq.addClass("inmap-map-ui").append(fullscreen_control);

		// Details Expand
		var details_control = jQuery("<div />")
			.attr({
				class: "inmap-control leaflet-bar leaflet-control",
			})
			.append(
				jQuery("<a />")
					.attr({
						class: "inmap-button inmap-icon inmap-icon-detail",
						href: "#",
						title: "Details",
						role: "button",
						"aria-label": "Details",
					})
					.on("click", function (e) {
						e.preventDefault();

						wrap_jq.toggleClass("inmap-info-hidden");

						//Redraw
						redraw_ui();
					}),
			);
		map_ui_jq.append(details_control);
	};

	var setup_info_ui = function () {
		for (id in infos_jq) {
			var title_jq = jQuery(".inmap-info-title", infos_jq[id]);
			var title_html = title_jq
				.text()
				.replace("[", "<span>")
				.replace("]", "</span>");
			title_jq.html(title_html);

			//Show full details if only
			if (!infos_jq[id].hasClass("inmap-only")) {
				infos_jq[id].addClass("inmap-hide-extended");
			}

			//Info Icon
			var info_icon = jQuery(".inmap-icon", markers_jq[id]);
			infos_jq[id].append(info_icon.clone());

			jQuery("table tr", infos_jq[id]).each(function () {
				var tr = jQuery(this);
				var td = jQuery("td", tr);
				jQuery("th", tr).addClass("inmap-info-extended");
				var key = tr.attr("class").replace("inmap-assoc_array-", "");
				var value = td.text();

				switch (key) {
					//Elevation
					case "elevation":
						var m_ele_float = parseFloat(value);

						//Valid
						if (!isNaN(m_ele_float)) {
							//Update
							td.text(
								m_ele_float.toFixed(1) +
									" (m) " +
									(m_ele_float * 3.28084).toFixed(1) +
									" (ft) ",
							);
						}

						break;

					//Velocity
					case "velocity":
						var km_hour_float = parseFloat(value);

						//Valid
						if (!isNaN(km_hour_float)) {
							//Update
							td.text(
								km_hour_float.toFixed(1) +
									" (km/h) " +
									(km_hour_float / 1.609344).toFixed(1) +
									" (mph) ",
							);
						}

						break;

					//GPS
					case "time_utc":
					case "valid_gps_fix":
						tr.addClass("inmap-info-extended");

						break;
				}
			});

			//Setup Latest
			if (infos_jq[id].hasClass("inmap-last")) {
				//Make accessible!
				info_last_jq = infos_jq[id];
			}
		}

		// Hide if not expanded initially
		if (inmap_shortcode_js.detail_expanded === "false") {
			wrap_jq.addClass("inmap-info-hidden");
		}
	};

	var update_point_status = function (
		update_id = null,
		update_status = "active",
		scroll_to = false,
	) {
		var expand_zoom_level = 14;

		//inmap_L Markers
		for (this_id in markers_l) {
			//Update
			if (this_id === update_id) {
				//Already active - Expand
				if (
					update_status == "active" &&
					infos_jq[this_id].hasClass("inmap-active")
				) {
					//Show extended info
					infos_jq[this_id].removeClass("inmap-hide-extended");

					//Zoom in & center?
					if (map_l.getZoom() < expand_zoom_level) {
						map_l.setView(markers_l[this_id].getLatLng(), expand_zoom_level);
						//Just center
					} else {
						map_l.setView(markers_l[this_id].getLatLng());
					}

					//Add classes
				} else {
					markers_jq[this_id].addClass("inmap-" + update_status);
					infos_jq[this_id].addClass("inmap-" + update_status);
				}

				//Active only
				if (update_status == "active") {
					// Ensure Details are shown
					wrap_jq.removeClass("inmap-info-hidden");

					if (scroll_to) {
						infos_jq[this_id].get(0).scrollIntoView({
							behaviour: "smooth",
							block: "center",
						});
					}

					//Center
					map_l.setView(markers_l[this_id].getLatLng());
				}
				//Inactive
			} else {
				//Active
				if (update_status == "active") {
					//Remove active
					infos_jq[this_id].removeClass("inmap-active");

					//Always keep Latest open on Map
					if (!infos_jq[this_id].hasClass("inmap-last")) {
						markers_jq[this_id].removeClass("inmap-active");
					}
					//Other
				} else {
					//Remove classes
					infos_jq[this_id].removeClass("inmap-" + update_status);
					markers_jq[this_id].removeClass("inmap-" + update_status);
				}
			}
		}

		//Active only
		if (update_status == "active") {
			// Set last active marker
			if (update_id) {
				marker_active_l = markers_l[update_id];
			}

			redraw_last();
		} else {
			// Unset active marker
			marker_active_l = {};
		}

		// Redraw UI
		redraw_ui();
	};

	var display_route = function () {
		//Route? (must be valid JSON)
		if (!route_geojson || !JSON.stringify(route_geojson)) {
			return false;
		}

		// Style
		var style = {
			weight: 3,
			opacity: 0.5,
		};

		if (
			typeof inmap_shortcode_js.route_colour === "string" &&
			inmap_shortcode_js.route_colour.length
		) {
			style.color = inmap_shortcode_js.route_colour;
		}

		//JSON layer
		var route_layer = inmap_L
			.geoJSON(route_geojson, {
				style,
				pointToLayer: function (feature, latlng) {
					// Skip points
				},
				onEachFeature: function (feature, layer) {
					layer.bindTooltip("Planned Route");
				},
			})
			.addTo(map_l);

		// Extend bounds to include data_layer.getBounds() and route
		map_l.fitBounds(data_layer.getBounds().extend(route_layer.getBounds()));
	};

	// Create Tile Layer

	// Default URL (OSM)
	var basemap_url = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

	// Custom URL
	if (
		typeof inmap_shortcode_js.basemap_url === "string" &&
		inmap_shortcode_js.basemap_url.length
	) {
		var basemap_url = inmap_shortcode_js.basemap_url;
	}

	// Default Attribution
	var basemap_attribution =
		'&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';

	// Custom Attribution
	if (
		typeof inmap_shortcode_js.basemap_attribution === "string" &&
		inmap_shortcode_js.basemap_attribution.length
	) {
		var basemap_attribution = inmap_shortcode_js.basemap_attribution;
	}

	var tiles = inmap_L
		.tileLayer(basemap_url, {
			maxZoom: 19,
			attribution: basemap_attribution,
		})
		.addTo(map_l);

	//Data layer
	var data_layer = inmap_L.geoJSON(map_geojson, {
		//Read style from GeoJSON
		style: function (feature) {
			if (typeof feature.properties.style === "object") {
				return feature.properties.style;
			}
		},

		//Marker Icons
		pointToLayer: function (feature, latlng) {
			if (typeof feature.properties.id === "undefined") {
				return false;
			}
			var id = feature.properties.id.toString();

			if (typeof feature.properties.icon === "object") {
				markers_l[id] = inmap_L.marker(latlng, {
					icon: inmap_L.divIcon(feature.properties.icon),
				});
			} else {
				markers_l[id] = inmap_L.marker(latlng);
			}

			//Info Item
			infos_jq[id] = jQuery("<div />")
				.addClass(feature.properties.className)
				.attr("title", feature.properties.title)
				.html(feature.properties.description)
				.hover(
					function () {
						update_point_status(id, "hover");
					},
					function () {
						update_point_status(null, "hover");
					},
				)
				.on("click", function () {
					info_active_jq = jQuery(this);

					update_point_status(id, "active");
				});

			//Add Item to container
			info_jq.append(infos_jq[id]);

			return markers_l[id];
		},

		//Events
		onEachFeature: function (feature, layer) {
			if (typeof feature.properties.id === "undefined") {
				return false;
			}
			var id = feature.properties.id.toString();

			//Added to DOM
			layer.on("add", function (e) {
				//Accessible jQuery reference
				markers_jq[id] = jQuery(e.target.getElement())
					.attr("title", feature.properties.title)
					.addClass(feature.properties.className)
					.data("marker_l", e.target)
					.on("mouseenter", function () {
						update_point_status(id, "hover", true);
					})
					.on("click", function () {
						update_point_status(id, "active", true);
					});
			});
		},
	});

	//Add
	data_layer.addTo(map_l);

	//Once data layer loaded
	data_layer.on("add", function () {
		setup_ui();
	});

	map_l.fitBounds(data_layer.getBounds());

	// Route?
	display_route();
};
