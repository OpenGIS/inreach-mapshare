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
	var markers_jq = {};
	
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

			//Make accessible			
			markers_jq[id] = jQuery(marker_l.getElement());
			
			//Create info item
			info_jq.append(jQuery('<div />')
				.addClass('inmap-info-item')
				.html(feature.properties.description)
				.on('mouseenter', function() {
					//Remove active
					jQuery('.inmap-point', map_jq).removeClass('inmap-active');
		
					//Add active
					jQuery(marker_l.getElement()).addClass('inmap-active');
				})
				.on('click', function() {
					map_l.setView(marker_l.getLatLng(), 14)
				})

			);
			
			return marker_l;			
		},
		
		//Events
		onEachFeature: function(feature, layer) {}
	});
	
	//Add
	data_layer.addTo(map_l);
	
	//Events
	data_layer.on('click', function(e) {
		var feature = e.layer.feature;
		var target_jq = jQuery(e.originalEvent.target);
		
		//Description?
		if(typeof feature.properties.description === 'string') {
			//Get target
			if(! target_jq.hasClass('inmap-point')) {
				target_jq = target_jq.parents('inmap-point');		
		
				if(! target_jq.length) {
					return false;
				}
			}
			
			var markers = jQuery('.inmap-point');
			markers.removeClass('inmap-active');
			
			//
			target_jq.addClass('inmap-active');			
 			info_jq.html(feature.properties.description);
		}		
	});
	
	map_l.fitBounds(data_layer.getBounds());
	map_l.setMaxBounds(data_layer.getBounds());
};