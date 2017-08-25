(function () {
  if (!navigator.serviceWorker) {
    console.log('Service workers not supported');
    return
  }
  navigator.serviceWorker.register('../sw.js')
    .then(reg => console.log('SW registered!', reg))
    .catch(err => console.log('Boo!', err));
}());