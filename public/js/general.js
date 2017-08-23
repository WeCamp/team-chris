function Map(element) {
	var googleMap;
	var element = element;

	this.initializeMap = function() {
		googleMap = new google.maps.Map(element, {
		  center: {lat: -34.397, lng: 150.644},
		  zoom: 8
		});
	};

	this.updateMap = function(address) {
		var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status === 'OK') {
                googleMap.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
	                map: googleMap,
	                position: results[0].geometry.location
            	});
            } else {
            	alert('Geocode was not successful for the following reason: ' + status);
          	}
        });
    }
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