# LGBT Friendly Restaurant Lists

A nice LGBT friendly restaurants list that you can vote them based on their friendliness

## Getting Started

A 3 day nice out of comfort zone project made in WeCamp 2017 by Team Grumpy

Needs PHP 7+


### Prerequisites

What things you need to install the software and how to install them

- Minimum PHP7
- Composer
- PHPUnit

### Demo


### Installing

Add this to your composer.json file to use the local Toran proxy

"repositories": [
    {"type":"composer",
     "url": "http://toran.weca.mp/repo/packagist"},
    {"packagist": false}
    ],
"config": {"secure-http": false },

After you go into the app directory, you should execute following commands.
OS X & Linux:

```sh
composer install
```

For installation, we need to consider some topics:
 - Firstly, install PHP 7.0 or 7.1
 - Secondly, install PHPUnit for testing proposes
 
Now, follow these steps:
 - Clone repository
 - composer install
 
## Running the tests

To run all tests of the app, execute these commands in the app directory.

```sh
composer install
phpunit
```

## Deployment



## Built With

* [Slim](https://www.slimframework.com/) - Small Rest Framework

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

## Authors

* **Midori Kocak** - [Midori Kocak](https://github.com/midorikocak)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details