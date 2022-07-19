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
	var info_items_jq = {};
	
	var point_active = function(id = null) {
		//Clear all
		for(i in markers_l) {
			var marker_jq = jQuery(markers_l[i].getElement());

			if(i === id) {
				marker_jq.addClass('inmap-active');		
			} else {
				marker_jq.removeClass('inmap-active');
			}
		}
		for(j in info_items_jq) {
			if(j === id) {
				info_items_jq[j].addClass('inmap-active');						
			} else {
				info_items_jq[j].removeClass('inmap-active');			
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
			marker_l.on('click', function() {
				point_active(id)
			});
			markers_l[id] = marker_l;
			
			//Create Info Item
			info_items_jq[id] = jQuery(jQuery('<div />')
				.addClass('inmap-info-item')
				.html(feature.properties.description)
				.hover(
					//On
					point_active(id),
					//Off
					point_active()
				)
				.on('click', function() {
					point_active(id),
					
					map_l.setView(marker_l.getLatLng(), 14)
				})
			);
			info_jq.append(info_items_jq[id]);		
			
			return marker_l;			
		},
		
		//Events
		onEachFeature: function(feature, layer) {}
	});
	
	//Add
	data_layer.addTo(map_l);

	map_l.fitBounds(data_layer.getBounds());
// 	map_l.setMaxBounds(data_layer.getBounds());
};