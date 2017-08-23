This is the README for the Team Chris WeCamp repo

Add this to your composer.json file to use the local Toran proxy

"repositories": [
    {"type":"composer",
     "url": "http://toran.weca.mp/repo/packagist"},
    {"packagist": false}
    ],
"config": {"secure-http": false },