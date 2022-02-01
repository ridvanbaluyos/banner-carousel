<?php
namespace Ridvanbaluyos\BannerCarousel;

use Carbon\Carbon;

class BannerCarousel
{
	public function __construct()
	{
		$current = Carbon::now();

		echo $current;
	}
}
