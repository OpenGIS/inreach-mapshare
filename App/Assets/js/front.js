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
		pointToLayer: function (feature, latlng) {

				var icon = L.divIcon({
					className: 'inmap-marker-icon',
					html: '&#128231;'
				});
				
				return L.marker(latlng, {
					icon: icon
				});		

		},
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