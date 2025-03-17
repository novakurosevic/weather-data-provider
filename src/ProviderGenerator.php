<?php

namespace Noki\WeatherDataProvider;

use Noki\WeatherDataProvider\WeatherConfig as WeatherConfig;
use Noki\WeatherDataProvider\WeatherProvider\Providers\AccuWeather;
use Noki\WeatherProvider\Providers\Provider;
use Noki\WeatherProvider\Providers\TomorrowIo;

class ProviderGenerator
{
    public const int TOMMOROW_IO_ID = 1;
    public const int ACCU_WEATHER_ID = 2;

    protected array $allowed_providers = [
        self::TOMMOROW_IO_ID => TomorrowIo::class,
        self::ACCU_WEATHER_ID => AccuWeather::class,
    ];

    public Provider $selected_provider;

    /**
     * @throws \Exception
     */
    public function __construct(WeatherConfig $config)
    {
        $provider_id = $config->getProviderId();

        if(isset($this->allowed_providers[$provider_id])) {
            $this->selected_provider = new $this->allowed_providers[$provider_id]($config);
        }else{
            throw new \Exception("Provider with id $provider_id not allowed");
        }

    }

}
