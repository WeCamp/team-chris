var googleMap;
var API = {
	url:  '/api'
};

function Map(element) {
	
	var placesApi;
	var element = element;
	var defaultLocation = {
		lat: -34.397,
		lng: 150.644
	};

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
	    $(marker).data('place', place);
	    addInfoWindow(place, marker);

	    marker.addListener('click', showBusinessDetails);
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

    var showBusinessDetails = function(e) {
    	var marker = this;
    	var place = $(marker).data('place');

    	var infoBox = $('#info-box');

    	$(infoBox).data('place', place);

    	$('.name', infoBox).text(place.name);
    	if (!place.phone) {
    		$('.phone', infoBox).hide();
    	} else {
			$('.phone', infoBox).text(place.phone).show();
    	}
    	$('.address', infoBox).html(place.formatted_address.split(/\s*\,\s*/).join('<br>'));

    	$(infoBox).addClass('show');
    	mobileAnimation(infoBox);
    };

    var mobileAnimation = function(infoBox) {
		if ($(window).width() <= 640) {
			infoBox.css('margin-top', ($(window).height() + 'px'));
    		scrollToElement(infoBox);
    	} else {
    		infoBox.css('margin-top', 'auto');
    	}
    }

    var scrollToElement = function(element) {
    	$('html, body').animate({
        	scrollTop: element.offset().top
    	}, 1000);
    };

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

function Voter (element) {
	$('.up, .down', element).on('click', function() {
		var infoBox = $(this).closest('#info-box');
		var place = $(infoBox).data('place');

		console.log(place);

		var vote = 1;
		
		if ($(this).hasClass('down')) {
			vote = -1;
		}

		$.post({
			url: API.url + '/reviews',
			data: [
				{
					"placeId": place.id,
					"category": "lgbt",
					"vote": vote
				}
			]
		})
	});
}

var map = new Map(document.getElementById('map'));
var search = new Search(map);
search.submitForm();
var voter = new Voter(document.getElementById('rate'));