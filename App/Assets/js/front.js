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
	var info_items_jq = {};
	
	var update_point_status = function(id = null, status = 'active') {
		console.log(id, status);

		//Leaflet Markers
		for(i in markers_l) {
			//Update
			if(i === id) {
				markers_jq[i].addClass('inmap-' + status);		
				
				if(status == 'active') {
					//Center
					map_l.setView(markers_l[i].getLatLng())				
				}
			//Inactive
			} else {
				markers_jq[i].removeClass('inmap-' + status);
			}
		}

		//Info Area
		for(j in info_items_jq) {
			//Update
			if(j === id) {
				info_items_jq[j].addClass('inmap-' + status);						
				
				if(status == 'active') {
					info_items_jq[j].get(0).scrollIntoView({
						behavior: "smooth",
						block: "nearest",
						inline: "nearest" 
					});
				}
			//Inactive				
			} else {
				info_items_jq[j].removeClass('inmap-' + status);			
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
			
			if(typeof feature.properties.style === 'object') {
				var icon = L.divIcon(feature.properties.icon);
				var marker_l = L.marker(latlng, {
					icon: icon
				});		
			} else {
				var marker_l = L.marker(latlng);					
			}				
			
			//Access!
			markers_l[id] = marker_l;

			//Create Info Item
			info_items_jq[id] = jQuery('<div />')
				.addClass('inmap-info-item')
				.html(feature.properties.description)
				.on('mouseenter', function() {
					update_point_status(id, 'hover');
				})
				.on('click', function() {
					update_point_status(id);
				})
			;
			info_jq.append(info_items_jq[id]);		
			
			return marker_l;			
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
					.on('mouseenter', function() {
						update_point_status(id, 'hover');
					})
					.on('click', function() {
						update_point_status(id);
					})
				;
			});
		}
	});
	
	//Add
	data_layer.addTo(map_l);
	map_l.fitBounds(data_layer.getBounds());
// 	map_l.setMaxBounds(data_layer.getBounds());
};