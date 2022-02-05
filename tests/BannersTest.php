<?php
use PHPUnit\Framework\TestCase;

use Ridvanbaluyos\BannerCarousel\BannerCarousel as BannerCarousel;

class BannersTest extends TestCase
{
    public function testSetBannersIsOk()
    {
        $banners = '[{"name":"Banner_A","start_date":"2021-08-07T00:00:00+00:00","end_date":"2021-08-10T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20A"}]';

        $banner = new BannerCarousel();
        $banner->setBanners($banners);
        $banners = $banner->getBanners();

        $this->assertIsArray($banners);
    }

    public function testSetBannerEmpty()
    {
        $banners = '';

        $banner = new BannerCarousel();
        try {
            $banner->setBanners($banners);
        } catch (Exception $e) {
            $this->assertSame($e->getMessage(), 'empty banner config');
        }
    }

    public function testSetBannerMalformed()
    {
        $banners = 'thisisatest';

        $banner = new BannerCarousel();
        try {
            $banner->setBanners($banners);
        } catch (Exception $e) {
            $this->assertSame($e->getMessage(), 'malformed banner config');
        }
    }

    public function testSetOfficeIpsAreOk()
    {
        $officeIps = '["192.0.2.10","198.51.100.3","203.0.113.254"]';

        $banner = new BannerCarousel();
        $banner->setOfficeIpAddresses($officeIps);
        $officeIps = $banner->getOfficeIpAddresses();

        $this->assertIsArray($officeIps);
    }

    public function testSetOfficeIpsIsEmpty()
    {
        $officeIps = '';

        $banner = new BannerCarousel();


        try {
            $banner->setOfficeIpAddresses($officeIps);
        } catch (Exception $e) {
            $this->assertSame($e->getMessage(), 'empty office ip config');
        }
    }

    public function testSetOfficeIpsAreMalformed()
    {
        $officeIps = 'thisisatest';

        $banner = new BannerCarousel();

        try {
            $banner->setOfficeIpAddresses($officeIps);
        } catch (Exception $e) {
            $this->assertSame($e->getMessage(), 'malformed office ip config');
        }
    }

    public function testBannerDisplayOutsideOfOffice()
    {
        $banners = '[{"name":"Banner_A","start_date":"2021-08-07T00:00:00+00:00","end_date":"2021-08-10T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20A"},{"name":"Banner_B","start_date":"2021-08-11T00:00:00+00:00","end_date":"2021-08-12T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20B"},{"name":"Banner_C","start_date":"2021-08-11T00:00:00+00:00","end_date":"2021-08-12T23:59:59+00:00","weight":"0.2","url":"https://via.placeholder.com/728x90.png?text=Banner%20C"},{"name":"Banner_D","start_date":"2021-08-13T00:00:00+00:00","end_date":"2021-08-14T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20D"}]';
        $officeIps = '["192.0.2.10","198.51.100.3","203.0.113.254"]';
        $bannerCarousel = new BannerCarousel();
        $ip = '1.1.1.1';

        $bannerCarousel
            ->setBanners($banners)
            ->setOfficeIpAddresses($officeIps)
            ->setIpAddress($ip)
            ->setTimezone('Asia/Tokyo');

        $now = '2021-08-09T00:00:00+00:00';
        $banner = $bannerCarousel->showBanner($now);

        $this->assertSame($banner['name'], 'Banner_A');
    }

    public function testBannerDisplayInsideOfOffice()
    {
        $banners = '[{"name":"Banner_A","start_date":"2021-08-07T00:00:00+00:00","end_date":"2021-08-10T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20A"},{"name":"Banner_B","start_date":"2021-08-11T00:00:00+00:00","end_date":"2021-08-12T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20B"},{"name":"Banner_C","start_date":"2021-08-11T00:00:00+00:00","end_date":"2021-08-12T23:59:59+00:00","weight":"0.2","url":"https://via.placeholder.com/728x90.png?text=Banner%20C"},{"name":"Banner_D","start_date":"2021-08-13T00:00:00+00:00","end_date":"2021-08-14T23:59:59+00:00","weight":"0.1","url":"https://via.placeholder.com/728x90.png?text=Banner%20D"}]';
        $officeIps = '["192.0.2.10","198.51.100.3","203.0.113.254"]';
        $bannerCarousel = new BannerCarousel();
        $ip = '203.0.113.254';

        $bannerCarousel
            ->setBanners($banners)
            ->setOfficeIpAddresses($officeIps)
            ->setIpAddress($ip)
            ->setTimezone('Asia/Tokyo');

        $now = '2021-08-11T00:00:00+00:00';
        $banner = $bannerCarousel->showBanner($now);

        $this->assertContains($banner['name'], ['Banner_B', 'Banner_C', 'Banner_D']);
    }
}