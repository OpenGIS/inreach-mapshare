const inmap_maps = [];

const inmap_create_map = function(map_hash = null, map_geojson = null) {
	if(! map_hash || ! map_geojson) {
		return false;
	}
	
	var map = L.map('inmap-' + map_hash);

	var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);

	var data_layer = L.geoJSON(map_geojson, {
		//Read style from GeoJSON
		style: function(feature) {
			if(typeof feature.properties.style === 'object') {
				return feature.properties.style;
			}
		},
		
		//Marker Icons
		pointToLayer: function (feature, latlng) {
			if(typeof feature.properties.style === 'object') {
				var icon = L.divIcon(feature.properties.icon);
				return L.marker(latlng, {
					icon: icon
				});		
			} else {
				return L.marker(latlng);					
			}				
		},
		
		//Events
		onEachFeature: function(feature, layer) {
			//Description?
			if(typeof feature.properties.description === 'string') {
	 			layer.bindPopup(feature.properties.description);
	 		}
		}
	});
	
	data_layer.addTo(map);
	map.fitBounds(data_layer.getBounds());
	
	inmap_maps[map_hash] = map;
};