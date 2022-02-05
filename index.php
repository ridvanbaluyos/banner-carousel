<?php
use Ridvanbaluyos\BannerCarousel\BannerCarousel as BannerCarousel;

require_once 'vendor/autoload.php';

$banners = file_get_contents('./banners.json');
$officeIps = file_get_contents('./office_ip_addresses.json');
$bannerCarousel = new BannerCarousel();

$now = '2021-08-11T00:00:00+00:00';
$ip = '192.0.2.10';

$bannerCarousel
    ->setBanners($banners)
    ->setOfficeIpAddresses($officeIps)
    ->setIpAddress($ip)
    ->setTimezone('Asia/Tokyo');


$banner = $bannerCarousel->showBanner($now);
var_dump($banner);


