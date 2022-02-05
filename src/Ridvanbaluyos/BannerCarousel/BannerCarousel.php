<?php
namespace Ridvanbaluyos\BannerCarousel;

use Exception;

class BannerCarousel
{
    const DEFAULT_TIMEZONE = 'UTC';

    protected array $banners = [];
    protected array $officeIps = [];
    protected string $ip = '127.0.0.1';

    /**
     * Constructor
     */
	public function __construct($banners = null, $officeIps = null)
	{
        // Default timezone
        $this->setTimezone(self::DEFAULT_TIMEZONE);

        // Set default banners
        if (!is_null($banners)) {
            $this->setBanners($banners);
        }

        // Set default bypassed ip addresses
        if (!is_null($officeIps)) {
            $this->setBypassIpAddresses($officeIps);
        }
	}

    /**
     * This function sets the timezone.
     *
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone): BannerCarousel
    {
        date_default_timezone_set($timezone);

        return $this;
    }

    /**
     * Requirement #2: You may also design and implement a way to configure a list of candidate banners.
     *      For this requirement, we will not care where the sources of the banners come from,
     *      whether from a config file, an API, database, KVS, text file, hard-coded, etc.
     *      What we do care about is the data structure format of the banners.
     *
     * @param $banners
     * @return $this
     */
    public function setBanners($banners): BannerCarousel
    {
        if (empty($banners)) {
            throw new Exception('empty banner config');
        }

        $banners = json_decode($banners, true);
        if (is_null($banners)) {
            throw new Exception('malformed banner config');
        }

        $this->banners = $banners;
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBanners(): array
    {
        return (!empty($this->banners))
            ? $this->banners
            : throw new Exception('missing banners');
    }

    /**
     * @param $officeIps
     * @return $this
     * @throws Exception
     */
    public function setOfficeIpAddresses($officeIps): BannerCarousel
    {
        if (empty($officeIps)) {
            throw new Exception('empty office ip config');
        }

        $officeIps = json_decode($officeIps, true);

        if (is_null($officeIps)) {
            throw new Exception('malformed office ip config');
        }
        $this->officeIps = $officeIps;
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getOfficeIpAddresses(): array
    {
        return (!empty($this->officeIps))
            ? $this->officeIps
            : throw new Exception('missing bypass ip addresses');
    }

    /**
     * @param $ip
     * @return $this
     */
    public function setIpAddress($ip): BannerCarousel
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Returns the current timestamp.
     *
     * @return string
     */
    public function now(): string
    {
        // ISO8601
        return date('Y-m-d\TH:i:sP');
    }

    /**
     * Requirement #1: We need at least one function/method in the package/class to return a banner to show.
     *
     * @param $now
     * @return array
     * @throws Exception
     */
    public function showBanner($now): array
    {
        if (is_null($now)) {
            $now = $this->now();
        }

        $now = $this->toUnixTimestamp($now);
        $validBanners = $this->fetchValidBanners($now);
        $banner = $this->raffleBanner($validBanners);

        return $banner;
    }

    /**
     * Requirement #4: Banner display weight
     *      Each banner has a display weight, a float value.
     *      When there are multiple banners within the display period, a banner is randomly
     *          chosen based on its display weight.
     *      For example, at 2021-08-11T00:00:00:+00:00, Banner C is twice more likely to be
     *          shown than Banner B because Banner C's weight is 0.2 and Banner B'c weight is 0.1.
     *
     * This function raffles (or randomizes) the banners depending
     * on the weight value of the banner.
     *
     * @param $validBanners
     * @return array
     */
    private function raffleBanner($validBanners): array
    {
        // Get the total weights of the valid banners
        $weightSum = array_sum(array_column($validBanners, 'weight'));

        // Get the multiplier. This is to normalize the weightings.
        // The weights should be less than 1, regardless if their sum
        // is more than or less than.
        $weightMultiplier = round(1 / $weightSum, 2);

        // Get a randomizer flag
        $rand = (float) rand() / (float) getrandmax();
        $chosenBanner = [];
        foreach ($validBanners as $banner)
        {
            $weight = $banner['weight'] * $weightMultiplier;

            // If the randomizer flag falls below the total weight, choose that banner.
            // Otherwise, keep on subtracting the weight until it is able to find a banner.
            if ($rand < $weight) {
                $chosenBanner = $banner;
                break;
            }
            $rand -= $weight;
        }

        return $chosenBanner;
    }

    /**
     * Requirement #3: Banner display period
     *      The display period can be set individually for each banner.
     *      Each banner will only run for a display period, a specific period.
     *      If the current time is within the display period, display the banner.
     *          For example, at 2021-08-08T00:00:00:+00:00, only Banner A can be shown because
     *          the current time is out of other banners' display period.
     *
     * This function fetches all the valid banners which falls
     * under a specific period.
     *
     * @param $now
     * @return array
     * @throws Exception
     */
    private function fetchValidBanners($now): array
    {
        $validBanners = [];
        $banners = $this->getBanners();

        // As of today, the office's IPv4 addresses are 192.0.2.10, 198.51.100.3, and 203.0.113.254.
        $officeIps = $this->getOfficeIpAddresses();

        foreach ($banners as $banner) {
            $startTime = $this->toUnixTimestamp($banner['start_date']);
            $endTime = $this->toUnixTimestamp($banner['end_date']);

            /**
             * Requirement #5: Access from the office
             *  The function/method takes account of the client's originating IPv4 address.
             *      For testing purposes, when the IPv4 address is one of the office addresses,
             *      the function/method returns a banner even if the banner's display period has not come yet.
             *  When the IPv4 address is an office address, a banner before the display period is shows based on
             *      the display weight logic described above.
             *      When the IPv4 address is an office address, and the current time is 2021-08-11T00:00:00:+00:00,
             *      Banner B, C, and D can be shown with probabilities of 25%, 50%, and 25% respectively.
             *      Banner D's display period has not come yet, but it can be shown because the given IPv4 address
             *          is an office address. And the current time is within Banner B and C's display period.
             *      Banner B, C, D's display weights are 0.1, 0.2, and 0.1
             *
             */

            if (in_array($this->ip, $officeIps)) {
                // An expired banner is not returned even if the IPv4 address is an office address.
                if ($now <= $endTime) {
                    array_push($validBanners, $banner);
                }
            } else {
                if ($now >= $startTime && $now <= $endTime) {
                    array_push($validBanners, $banner);
                }
            }
        }

        return $validBanners;
    }
    /**
     * Converts a date to unix timestamp.
     * Dealing with dates is much easier in integer format.
     *
     * @param $date
     * @return false|int
     */
    private function toUnixTimestamp($date): bool|int
    {
        return strtotime($date);
    }
}
