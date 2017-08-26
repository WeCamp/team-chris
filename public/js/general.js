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
    if (!googleMap) {
      console.log('Map not setup yet');
      return;
    }
    let geocoder = new google.maps.Geocoder();
    geocoder.geocode({'address': address}, function (results, status) {
      if (status === 'OK') {
        googleMap.setCenter(results[0].geometry.location);
        googleMap.fitBounds(results[0].geometry.bounds);
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
    for (var i = 0; i < markers.length; i++) {
      markers[i].setMap(null);
    }
    markers = [];
  }

  let createMarker = function (place) {

    var iconBase = '../img/';
    var icons = {
      noRating: {
        icon: iconBase + 'noRating.png'
      },
      lowRating: {
        icon: iconBase + 'lowRating.png'
      },
      highRating: {
        icon: iconBase + 'highRating.png'
      }
    };

    let image = getImage(place, icons);
    //console.log(image);
    let marker = new google.maps.Marker({
      map: googleMap,
      icon: image,
      position: place.geometry.location
    });
    markers.push(marker);
    $(marker).data('place', place);
    addInfoWindow(place, marker);

    marker.addListener('click', showBusinessDetails);
  };

  let getImage = function(place, icons) {
    let upAmount = place.ratings.lgbt.upAmount;
    let downAmount = place.ratings.lgbt.downAmount;
    let totalVotes = Number(upAmount) + Number(downAmount);
    let rating = Math.round((upAmount / totalVotes) * 100);

    let ratingUrl;

    if (rating > 55) {
      ratingUrl = icons.highRating.icon;
    } else if (rating <= 55) {
      ratingUrl = icons.lowRating.icon;
    } else {
      ratingUrl = icons.noRating.icon;
    }
    let image = {
      url: ratingUrl,
      size: new google.maps.Size(25, 25),
      scaledSize: new google.maps.Size(25, 25),
    };
    return image;
  };

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

    infoBox.removeClass('voted');

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
          let placeId = place.id;
          place.ratings.lgbt.upAmount = response[placeId].ratings.lgbt.upAmount;
          place.ratings.lgbt.downAmount = response[placeId].ratings.lgbt.downAmount;
          map.updateRatings(place, infoBox);
          infoBox.addClass('voted');
        });
    });
  }
}

window.map = new Map(document.getElementById('map'));
let search = new Search(map);
search.submitForm();
let voter = new Voter(document.getElementById('rate'));
voter.addCastVoteHandlers();