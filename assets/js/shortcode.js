const inmap_maps = [];

const inmap_create_map = function(map_hash = null, map_geojson = null) {
	if(! map_hash || ! map_geojson || ! jQuery) {
		return false;
	}
	
	var map_id = 'inmap-' + map_hash;
	
	//Map Container
	var map_jq = jQuery('#' + map_id);
	if(! map_jq.length) {
		return false;
	}
	
	var map_l = L.map(map_id);

	//Make accessible
	map_jq.data('map_l', map_l)
	inmap_maps[map_hash] = map_l;
	
	//UI
	var wrap_jq = map_jq.parents('.inmap-wrap');
	var info_jq = jQuery('.inmap-info', wrap_jq);
	var markers_l = {};
	var markers_jq = {};
	var infos_jq = {};
	var info_last_jq = {};
	
	var setup_info = function() {
		for(id in infos_jq) {
			var title_jq = jQuery('.inmap-info-title', infos_jq[id]);
			var title_html = title_jq.text().replace('[', '<span>').replace(']', '</span>');
			title_jq.html(title_html);
			
			//Show full details if only
			if(! infos_jq[id].hasClass('inmap-only')) {
				infos_jq[id].addClass('inmap-hide-extended');
			}

			//Info Icon
			var info_icon = jQuery('.inmap-icon', markers_jq[id]);		
			infos_jq[id].append(info_icon.clone());

// 			jQuery('table tr', infos_jq[id]).each(function() {
// 				var tr = jQuery(this);
// 				var td = jQuery('td', tr);
// 				jQuery('th', tr).addClass('inmap-info-extended');
// 				var key = tr.attr('class').replace('joe-assoc_array-', '');
// 				var value = td.text();
				
// 				switch(key) {
// 					//GPS
// 					case 'time_utc' :
// 					case 'valid_gps_fix' :
// 						tr.addClass('inmap-info-extended');
// 
// 						break;
// 				}
				
// 			});
			
			//Setup Latest
			if(infos_jq[id].hasClass('inmap-last')) {
				//Make accessible!
				info_last_jq = infos_jq[id];
				
				info_last_jq.css({
					'width' : info_jq.width() + 'px',
				});

				info_jq.css('padding-top', info_last_jq.height() + 'px');
			}
		}
	};

	var update_point_status = function(update_id = null, update_status = 'active', scroll_to = false) {
		var expand_zoom_level = 14;
		
		//Leaflet Markers
		for(this_id in markers_l) {
			//Update
			if(this_id === update_id) {
				//Already active - Expand
				if(update_status == 'active' && infos_jq[this_id].hasClass('inmap-active')) {
					//Go to wrapper
// 					var map_hash = '#' + map_jq.attr('id');
// 					document.location.replace(map_hash, '');
// 					document.location += map_hash;

					//Show extended info
					infos_jq[this_id].removeClass('inmap-hide-extended');
					
					//Zoom in & center?
					if(map_l.getZoom() < expand_zoom_level) {
						map_l.setView(markers_l[this_id].getLatLng(), expand_zoom_level);
					//Just center
					} else {
						map_l.setView(markers_l[this_id].getLatLng());					
					}
				//Add classes
				} else {
					markers_jq[this_id].addClass('inmap-' + update_status);		
					infos_jq[this_id].addClass('inmap-' + update_status);						
				}

				//Active only
				if(update_status == 'active') {
					if(scroll_to) {
						infos_jq[this_id].get(0).scrollIntoView({
							behaviour: 'smooth',
							block: "center"
						});
					}

					//Center
					map_l.setView(markers_l[this_id].getLatLng());					
				}
			//Inactive
			} else {
				//Remove classes
				markers_jq[this_id].removeClass('inmap-' + update_status);

				//Active
				if(update_status == 'active') {
					infos_jq[this_id].removeClass('inmap-active');							
				//Other
				} else {
					infos_jq[this_id].removeClass('inmap-' + update_status);											
				}
			}
		}

		//Active only
		if(update_status == 'active') {
			//Resize Latest
			info_jq.css('height', (info_jq.height() - info_last_jq.height()) + 'px');
			info_jq.css('padding-top', info_last_jq.height());
		}		
	};

	// Create Tile Layer

	//Basemap
	var basemap_url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';	
	if(typeof inmap_shortcode_js.basemap_url === 'string' && inmap_shortcode_js.basemap_url.length) {
		var basemap_url = inmap_shortcode_js.basemap_url;
	}
	var basemap_attribution = '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';	
	if(typeof inmap_shortcode_js.basemap_attribution === 'string' && inmap_shortcode_js.basemap_attribution.length) {
		var basemap_attribution = inmap_shortcode_js.basemap_attribution;
	}		
	var tiles = L.tileLayer(basemap_url, {
		maxZoom: 19,
		attribution: basemap_attribution
	}).addTo(map_l);

	//Data layer
	var data_layer = L.geoJSON(map_geojson, {
		//Read style from GeoJSON
		style: function(feature) {
			if(typeof feature.properties.style === 'object') {
				return feature.properties.style;
			}
		},
		
		//Marker Icons
		pointToLayer: function (feature, latlng) {
			if(typeof feature.properties.id === 'undefined') {
				return false;
			}
			var id = feature.properties.id.toString();
			
			if(typeof feature.properties.icon === 'object') {
				markers_l[id] = L.marker(latlng, {
					icon: L.divIcon(feature.properties.icon)
				});		
			} else {
				markers_l[id] = L.marker(latlng);					
			}				
			
			//Info Item
			infos_jq[id] = jQuery('<div />')
 				.addClass(feature.properties.className)
				.attr('title', feature.properties.title)
				.html(feature.properties.description)
				.hover(
					function() {
						update_point_status(id, 'hover');
					},
					function() {
						update_point_status(null, 'hover');
					}
				)
				.on('click dblclick', function() {
					update_point_status(id, 'active');
				})
			;	
			
			//Add Item to container
			info_jq.append(infos_jq[id]);		
			
			return markers_l[id];			
		},
		
		//Events
		onEachFeature: function(feature, layer) {
			if(typeof feature.properties.id === 'undefined') {
				return false;
			}
			var id = feature.properties.id.toString();
			
			//Added to DOM
			layer.on('add', function(e) {
				//Accessible jQuery reference
				markers_jq[id] = jQuery(e.target.getElement())
					.attr('title', feature.properties.title)
					.addClass(feature.properties.className)
					.data('marker_l', e.target)
					.on('mouseenter', function() {
						update_point_status(id, 'hover', true);
					})
					.on('click', function() {
						update_point_status(id, 'active', true);
					})
				;
			});
		}
	});
	
	//Add
	data_layer.addTo(map_l);
	
	//Once data layer loaded
	data_layer.on('add', function() {
		setup_info();
	});
	
	map_l.fitBounds(data_layer.getBounds());
};