(function(window, google, mapster){

	mapster.MAP_OPTIONS = {
		center: {
			lat: 12.459037, 
			lng: 122.192494
		},
		zoom: 7,
		maxZoom: 10,
		zoomControlOptions: {
			position: google.maps.ControlPosition.TOP_LEFT
		},
		panControlOptions: {
			position: google.maps.ControlPosition.TOP_LEFT
		}
	}

}(window, google, window.Mapster || (window.Mapster = {})))