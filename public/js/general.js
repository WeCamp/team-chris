var googleMap;

function Map(element) {
	
	var placesApi;
	var element = element;
	var defaultLocation = {
		lat: -34.397,
		lng: 150.644
	}

	this.initializeMap = function() {
		if (!navigator.geolocation) {
	        displayMap(defaultLocation.lat, defaultLocation.lng);
	        return;
	    }

	    navigator.geolocation.getCurrentPosition(setLocationCallback, geolocationFailedCallback);
	};

	var displayMap = function(lat, lng) {
		googleMap = new google.maps.Map(element, {
		    center: {lat: lat, lng: lng},
		    zoom: 13
		});
		googleMap.addListener("bounds_changed", getBusinesses);
	}

	this.updateMap = function(address) {
		var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status === 'OK') {
                googleMap.setCenter(results[0].geometry.location);
            } else {
            	alert('Geocode was not successful for the following reason: ' + status);
          	}
        });
    }

    var getBusinesses = function() {
    	if (!placesApi) {
    		placesApi = new google.maps.places.PlacesService(googleMap);
    	}
    	var request = {
    		query: "",
    		bounds: googleMap.getBounds(),
    		type: "restaurant"
    	};
    	placesApi.textSearch(request, displayBusinesses);
    };

    var displayBusinesses = function(results, status) {
		if (status == google.maps.places.PlacesServiceStatus.OK) {
		    for (var i = 0; i < results.length; i++) {
		        var place = results[i];
		        createMarker(results[i]);
		    }
		}
    };

    var createMarker = function(place) {
	    var image = {
	        url: place.icon,
	        size: new google.maps.Size(25, 25),
	        scaledSize: new google.maps.Size(25, 25),
	    };
	    var marker = new google.maps.Marker({
		    map: googleMap,
		    icon: image,
		    position: place.geometry.location
	    });
	    addInfoWindow(place, marker);
    }

    var addInfoWindow = function(place, marker) {
    	var infoWindow = new google.maps.InfoWindow({
	    	content: place.name
	    });
	    marker.addListener("mouseover", function() {
	    	infoWindow.open(googleMap, marker);
	    });
	    marker.addListener("mouseout", function() {
	    	infoWindow.close();
	    });
    };

    var showInfoBox

	var setLocationCallback = function(position) {
		displayMap(position.coords.latitude, position.coords.longitude);
	};

	var geolocationFailedCallback = function() {
		displayMap(defaultLocation.lat, defaultLocation.lng);
	};
}

function Search(map) {

	var searchForm = $('#search-form');

	this.submitForm = function() {
		searchForm.on('submit', function(event) {
			event.preventDefault();
			var formContent = getFormContent();
			map.updateMap(formContent.searchInputValue);
		});
	};

	var getFormContent = function() {

		formContent = {
			searchInputValue: searchForm.find('#search').val(),
		};

		return formContent;
	};

}

var map = new Map(document.getElementById('map'));
var search = new Search(map);
search.submitForm();