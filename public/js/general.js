import debounce from "lodash.debounce";

let googleMap;
let API = {
    url: '/api'
};

function Map(element) {

    let scope = this;
    let placesApi;
    let defaultLocation = {
        lat: -34.397,
        lng: 150.644
    };

    let markers = [];

    this.initializeMap = function () {
        if (!navigator.geolocation) {
            displayMap(defaultLocation.lat, defaultLocation.lng);
            return;
        }

        navigator.geolocation.getCurrentPosition(setLocationCallback, geolocationFailedCallback);
    };

    let displayMap = function (lat, lng) {
        googleMap = new google.maps.Map(element, {
            center: {lat: lat, lng: lng},
            zoom: 13
        });

        googleMap.addListener("bounds_changed", debounce(getBusinesses, 500));
    }

    this.updateMap = function (address) {
        let geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function (results, status) {
            if (status === 'OK') {
                googleMap.setCenter(results[0].geometry.location);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }

    let getBusinesses = function () {
        if (!placesApi) {
            placesApi = new google.maps.places.PlacesService(googleMap);
        }
        let request = {
            query: "",
            bounds: googleMap.getBounds(),
            type: "restaurant"
        };
        placesApi.textSearch(request, displayBusinesses);
    };

    let displayBusinesses = function (results, status) {

        if (status !== google.maps.places.PlacesServiceStatus.OK) {
            return;
        }

        let placeIds = [];
        let places = [];

        for (let i = 0; i < results.length; i++) {
            places.push(results[i]);
            placeIds.push(results[i].id);
        }

        let url = API.url + '/reviews?categories[]=lgbt&placeIds[]=' + placeIds.join('&placeIds[]=');

        $.getJSON(url)
            .then(function (response) {

                removeMarkers();

                $.each(places, function (i, place) {
                    let placeId = place.id;
                    console.log('placeId:', placeId);
                    if ('undefined' !== typeof(response[placeId])) {
                        place.ratings = response[placeId].ratings;
                    } else {
                        console.log('No ratings for ' + placeId);
                    }
                    createMarker(place);
                });
            });
    };

    let removeMarkers = function () {
        console.log(markers);
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
        markers = [];
        console.log(markers);
    }

    let createMarker = function (place) {
        let image = {
            url: place.icon,
            size: new google.maps.Size(25, 25),
            scaledSize: new google.maps.Size(25, 25),
        };
        let marker = new google.maps.Marker({
            map: googleMap,
            icon: image,
            position: place.geometry.location
        });
        markers.push(marker);
        $(marker).data('place', place);
        addInfoWindow(place, marker);

        marker.addListener('click', showBusinessDetails);
    }

    let addInfoWindow = function (place, marker) {
        let infoWindow = new google.maps.InfoWindow({
            content: place.name
        });
        marker.addListener("mouseover", function () {
            infoWindow.open(googleMap, marker);
        });
        marker.addListener("mouseout", function () {
            infoWindow.close();
        });
    };

    let showBusinessDetails = function (e) {
        let marker = this;
        let place = $(marker).data('place');

        let infoBox = $('#info-box');

        $(infoBox).data('place', place);

        $('.name', infoBox).text(place.name);
        if (!place.phone) {
            $('.phone', infoBox).hide();
        } else {
            $('.phone', infoBox).text(place.phone).show();
        }
        $('.address', infoBox).html(place.formatted_address.split(/\s*\,\s*/).join('<br>'));

        scope.updateRatings(place, infoBox);

        $(infoBox).addClass('show');
        mobileAnimation(infoBox);

        $('.thank-you', infoBox).hide();
    };

    this.updateRatings = function (ratingObject, scopeElement) {
        let $votes = $('.votes', scopeElement);
        console.log(ratingObject.ratings.lgbt.upAmount);
        let upAmount = ratingObject.ratings.lgbt.upAmount;
        let downAmount = ratingObject.ratings.lgbt.downAmount;

        $('.up-votes', $votes).text(upAmount);
        $('.down-votes', $votes).text(downAmount);
        let totalVotes = Number(upAmount) + Number(downAmount);
        let rating = Math.round((upAmount / totalVotes) * 100);
        let ratingString = String(rating) + '%';
        if (isNaN(rating)) {
            ratingString = '-';
        }
        $('.rating', scopeElement).text(ratingString);
    };

    let mobileAnimation = function (infoBox) {
        if ($(window).width() <= 640) {
            infoBox.css('margin-top', ($(window).height() + 'px'));
            scrollToElement(infoBox);
        } else {
            infoBox.css('margin-top', 'auto');
        }
    };

    let scrollToElement = function (element) {
        $('html, body').animate({
            scrollTop: element.offset().top
        }, 1000);
    };

    let setLocationCallback = function (position) {
        displayMap(position.coords.latitude, position.coords.longitude);
    };

    let geolocationFailedCallback = function () {
        displayMap(defaultLocation.lat, defaultLocation.lng);
    };
}

function Search(map) {

    let searchForm = $('#search-form');

    this.submitForm = function () {
        searchForm.on('submit', function (event) {
            event.preventDefault();
            let formContent = getFormContent();
            map.updateMap(formContent.searchInputValue);
            $('#info-box').removeClass('show');
        });
    };

    let getFormContent = function () {
        return {
            searchInputValue: searchForm.find('#search').val(),
        };
    };
}

function Voter(element) {

    this.addCastVoteHandlers = function () {
        $('.up, .down', element).on('click', function () {
            let infoBox = $(this).closest('#info-box');
            let place = $(infoBox).data('place');

            let vote = 1;

            if ($(this).hasClass('down')) {
                vote = -1;
            }

            let url = API.url + '/reviews';
            let data = {
                data: [
                    {
                        "placeId": place.id,
                        "category": "lgbt",
                        "vote": vote
                    }
                ]
            };

            $.post(url, data, null, 'json')
                .done(function (response) {
                    $('.thank-you', infoBox).show();
                    map.updateRatings(place, infoBox);
                    console.log(response);
                });
        });
    }
}

window.map = new Map(document.getElementById('map'));
let search = new Search(map);
search.submitForm();
let voter = new Voter(document.getElementById('rate'));
voter.addCastVoteHandlers();