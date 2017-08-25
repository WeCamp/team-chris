self.addEventListener('install', event => {
  console.log('Installing service worker');
});

self.addEventListener('activate', event => {
  console.log('Now ready to handle fetches!');
});

// self.addEventListener('fetch', event => {
//   const url = new URL(event.request.url);
//
//   // console.log(event.request.url);
//
//   event.respondWith(
//     caches.match(event.request).then(function (response) {
//       // console.log(response);
//       return response || fetch(event.request);
//     })
//   );
// });