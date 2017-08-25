(function () {
  const CACHE = 'me:al';

  let filesToCache = [
    './',
    './css/style.css',
    './img/search.png',
    './js/bundle.js',
    'https://maps.googleapis.com/maps/api/js?key=AIzaSyDcyPKK0R-yKJugZ-RYguIKfr6ZgjB-81o&callback=map.initializeMap&libraries=places'
  ];

  self.addEventListener('install', event => {
    console.log('Installing service worker');

    event.waitUntil(
      caches.open(CACHE)
        .then(
          cache => {
            cache.addAll(filesToCache);
          }
        )
    );
  });

  self.addEventListener('activate', event => {
    console.log('Now ready to handle fetches!');
  });

  self.addEventListener('fetch', event => {
    console.log('SW serving the asset ' + event.request.url);

    if (event.request.url.match(/^chrome-extension:\/\//)) {
      console.log('Ignoring extension file');
      return;
    }

    event.respondWith(fromCache(event.request));

    event.waitUntil(
      update(event.request)
        .then(refresh)
        .catch(err => {
          console.log('Update error - ' + err);
        })
    );
  });

  /**
   * Serve the file from cache
   *
   * @param request
   *
   * @return {*}
   */
  function fromCache (request) {

    return caches.open(CACHE)
      .then(cache => {
        return cache.match(request);
      })
      .catch(err => {
        console.log(err, arguments);
      });
  }

  /**
   * Update the cached version of a file
   *
   * @param request
   *
   * @return {*}
   */
  function update (request) {

    return caches.open(CACHE)
      .then(cache => {
        return fetch(request, {mode: 'no-cors'})
          .then(response => {
            return cache.put(request, response.clone())
              .then(() => {
                return response;
              });
          });
      });
  }

  /**
   * Refresh the previously loaded files with the currenly cached version of the file (probably updated just before this
   * is called
   *
   * @param response
   *
   * @return {Promise.<TResult>|*}
   */
  function refresh (response) {
    return self.clients.matchAll()
      .then(clients => {
        clients.forEach(client => {

          let message = {
            type: 'refresh',
            url:  response.url,
            eTag: response.headers.get('ETag')
          };

          client.postMessage(JSON.stringify(message));
        })
      })
  }
}());