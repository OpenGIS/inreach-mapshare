const inmap_maps = [];

const inmap_create_map = function(map_hash = null, map_geojson = null) {
	if(! map_hash || ! map_geojson || ! jQuery) {
		return false;
	}
	
	var map_id = 'inmap-' + map_hash;
	
	//CreateMap
	var map_jq = jQuery('#' + map_id);
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

	var update_point_update_status = function(update_id = null, update_status = 'active') {
		//Leaflet Markers
		for(this_id in markers_l) {
			//Update
			if(this_id === update_id) {
				//Add classes
				markers_jq[this_id].addClass('inmap-' + update_status);		
				infos_jq[this_id].addClass('inmap-' + update_status);						

				//Scroll to info
				infos_jq[this_id].get(0).scrollIntoView({
					behavior: "smooth",
					block: "center"
				});

				//Zoom only
				if(update_status == 'zoom') {
					//Center & Zoom
					map_l.setView(markers_l[this_id].getLatLng(), 14);
				}
				
				//Active only
				if(update_status == 'active') {
					//Center
					map_l.setView(markers_l[this_id].getLatLng());
				}
			//Inactive
			} else {
				//Remove classes
				markers_jq[this_id].removeClass('inmap-' + update_status);
				infos_jq[this_id].removeClass('inmap-' + update_status);							
			}
		}
	};
	
	//Basemap
	var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
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

			//Create Info Item
			infos_jq[id] = jQuery('<div />')
				.addClass('inmap-info-item')
				.html(feature.properties.description)
				.hover(
					function() {
						update_point_update_status(id, 'hover');
					},
					function() {
						update_point_update_status(null, 'hover');
					}
				)
				.on('click', function() {
					update_point_update_status(id, 'active');
				})
				.on('dblclick', function() {
					update_point_update_status(id, 'zoom');
				})				
			;
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
					.data('marker_l', e.target)
					.hover(
						function() {
							update_point_update_status(id, 'hover');
						},
						function() {
							update_point_update_status(null, 'hover');
						}
					)
					.on('click', function() {
						update_point_update_status(id);
					})
				;
			});
		}
	});
	
	//Add
	data_layer.addTo(map_l);
	map_l.fitBounds(data_layer.getBounds());
};