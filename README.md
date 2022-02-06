# Banner Carousel
This PHP package allows you to rotate banners based on date duration, weighting, and IP access.

# Installation
```php
composer install
```

# Usage
Include the package
```php
use Ridvanbaluyos\BannerCarousel\BannerCarousel as BannerCarousel;
```

## Setting Up Banners and Office IPs
There are two (2) ways:
1. Chaining the setters.
```php
$banner = new BannerCarousel();
$banner
    ->setBanners($banners)
    ->setOfficeIpAddresses($officeIps);
```
2. Passing in the constructor.
```php
$banner = new BannerCarousel($banners, $officeIps);
```
Failure to set these two configurations will throw an Exception.
## Setting Up the Timezone
If none is set, it will use 'UTC' by default.
```php
$banner = new BannerCarousel();
$banner->setTimezone('Asia/Tokyo');
```

## Setting Up the IP Address
Since this is not a web application, we cannot fetch the IP address.
If none is set, it will use `127.0.0.1` meaning it is running locally.
```php
$banner = new BannerCarousel();
$banner->setIpAddress($ip);
```

## Showing the banner.
Once the correct configurations are done, we can now fetch the correct banner.
```php
$bannerCarousel
    ->setBanners($banners)
    ->setOfficeIpAddresses($officeIps)
    ->setIpAddress($ip)
    ->setTimezone('Asia/Tokyo');

// Returns ISO8601 date format.
$now = $bannerCarousel->now();
$banner = $bannerCarousel->showBanner($now);
```
Alternately, for whichver purposes, you can pass a date of your choice.
```php
$now = '2021-08-11T00:00:00+00:00'; // ISO8601
$banner = $bannerCarousel->showBanner($now);
```

# Test
To run the tests, type the ff. commands:
```php
./vendor/bin/phpunit
```

# Configurations
This package does not care where the source is coming from.
As long as you pass the correct JSON format to the setters/constructor, it should work.
This is being pragmatic in our approach because we don't know if the configuration sources
are coming from the DB, KVS, API, etc.
## Banners
```json
[
  {
    "name" : "Banner_NAME",
    "start_date" : "2021-08-07T00:00:00+00:00",
    "end_date" : "2021-08-10T23:59:59+00:00",
    "weight" : "0.1",
    "url" : "https://via.placeholder.com/728x90.png?text=Banner%20A"
  },
  ...
]
```

## Office IP Addresses
```json
[
  "192.0.2.10",
  "198.51.100.3",
  "203.0.113.254"
]
```

# Notes
- I decided not to use any other package for dates (eg. nesbot/carbon) because it bloats up the size.
- When dealing with dates, it is always better to convert to unixtime and do the calculations from there.
- Exception handling is done in a straightforward manner.
- Unit Testing tests only want needs to be tested.