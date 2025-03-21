<?php

namespace Noki\WeatherDataProvider;

use Illuminate\Support\Arr;
use Noki\WeatherDataProvider\Dto\ConfigFrequency;
use Noki\WeatherDataProvider\Dto\ConfigLocation;
use Noki\WeatherDataProvider\Dto\ConfigWeatherData;
use Noki\WeatherProvider\Exeptions\WeatherDataException as WeatherDataException;

class WeatherConfig
{
    const string UNITS_METRIC = 'metric';
    const string UNITS_IMPERIAL = 'imperial';
    protected string $units = self::UNITS_METRIC;
    protected int $provider_id;

    protected ConfigWeatherData $config_weather_data;
    protected ConfigFrequency $config_frequency;
    protected ConfigLocation $config_location;
    protected array $allowed_units = [self::UNITS_METRIC, self::UNITS_IMPERIAL];

    protected array $allowed_config_properties = [
        'config_weather_data' => [
            'temperature',
            'wind',
            'pressure',
            'humidity'
        ],
        'config_frequency' => [
            'minutely',
            'hourly',
            'daily'
        ],
        'units'
    ];

    protected array $allowed_provider_ids = [
        ProviderGenerator::TOMMOROW_IO_ID,
        ProviderGenerator::ACCU_WEATHER_ID,
    ];

    public function __construct()
    {
        $this->config_weather_data = new ConfigWeatherData();
        $this->config_frequency = new ConfigFrequency();
        $this->config_location = new ConfigLocation();
    }

    public function humidity(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_weather_data->humidity = $action;

        return $this;
    }

    public function wind(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_weather_data->wind = $action;

        return $this;
    }

    public function pressure(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_weather_data->pressure = $action;

        return $this;
    }

    public function temperature(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_weather_data->temperature = $action;

        return $this;
    }

    public function imperial(): self
    {
        $this->units = self::UNITS_IMPERIAL;

        return $this;
    }

    public function minutely(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_frequency->minutely = $action;

        return $this;
    }

    public function hourly(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_frequency->hourly = $action;

        return $this;
    }

    public function daily(bool|null $action = null): self
    {
        $action = !isset($action) || (bool)$action;
        $this->config_frequency->daily = $action;

        return $this;
    }

    public static function fromArray(array $config):self
    {
        $object = new self();
        $provider_id = Arr::get($config, 'provider_id');
        $longitude = Arr::get($config, 'config_location.longitude');
        $latitude = Arr::get($config, 'config_location.latitude');
        $selected_one_from_weather_data = false;
        $selected_one_from_frequency = false;

        // Set all values to false. Only selected values will be true.
        $object->temperature(false);
        $object->wind(false);
        $object->humidity(false);
        $object->pressure(false);
        $object->minutely(false);
        $object->hourly(false);
        $object->daily(false);

        foreach ($config as $property => $value) {
            if(is_array($value)){
                if (property_exists($object, $property) && isset($object->$property)){
                    foreach ($value as $sub_property => $sub_value) {
                        // Handle properties with only name as true.
                        if(is_int($sub_property) && is_string($sub_value)){
                            $sub_property = $sub_value;
                            $sub_value = true;
                        }

                        if($property == 'config_weather_data'){
                            if($sub_property == 'temperature'){
                                $object->temperature($sub_value);
                            }elseif ($sub_property == 'wind'){
                                $object->wind($sub_value);
                            }elseif ($sub_property == 'pressure'){
                                $object->pressure($sub_value);
                            }elseif ($sub_property == 'humidity'){
                                $object->humidity($sub_value);
                            }
                            if($sub_value){
                                $selected_one_from_weather_data = true;
                            }
                        }

                        if($property == 'config_frequency'){
                            if($sub_property == 'minutely'){
                                $object->minutely($sub_value);
                            }elseif ($sub_property == 'hourly'){
                                $object->hourly($sub_value);
                            }elseif ($sub_property == 'daily'){
                                $object->daily($sub_value);
                            }
                            if($sub_value){
                                $selected_one_from_frequency = true;
                            }
                        }

                    }
                }

            }else{
                if (property_exists($object, $property) && in_array($property, $object->allowed_config_properties)) {
                    if($property == 'units'){
                        $object->units($value);
                    }
                }
            }

        }

        if(!$selected_one_from_weather_data){
            throw new WeatherDataException('None property from config weather data is true.', 'Config Error');
        }

        if(!$selected_one_from_frequency){
            throw new WeatherDataException('None property from config frequency is true.', 'Config Error');
        }

        return $object->create($provider_id, $longitude, $latitude, $object);

    }

    public static function create(int|null $provider_id, int|float|string|null  $longitude,
                                  int|float|string|null $latitude, $object = null): self
    {
        if(is_null($object)){
            $object = new self();
        }

        if(!isset($provider_id)){
            throw new WeatherDataException('Provider id is not set.', 'Config Error');
        }

        $object = $object::setProviderId($object, $provider_id);

        if(!isset($longitude)){
            throw new WeatherDataException('Longitude is not set.', 'Config Error');
        }

        $longitude = (float) $longitude;
        if((180 < $longitude) || (-180 > $longitude)) {
            throw new WeatherDataException('Longitude value is not valid.', 'Config Error');
        }

        if(!isset($latitude)){
            throw new WeatherDataException('Latitude is not set.', 'Config Error');
        }

        $latitude = (float) $latitude;
        if((90 < $latitude) || (-90 > $latitude)) {
            throw new WeatherDataException('Latitude value is not valid.', 'Config Error');
        }

        $object->config_location->longitude = $longitude;
        $object->config_location->latitude = $latitude;

        return $object;
    }

    public function units(string $units): self
    {
        if(in_array($units, $this->allowed_units)) {
            $this->units = $units;
        }

        return $this;
    }

    public function getProviderId(): int
    {
        return $this->provider_id;
    }

    private static function setProviderId($object, int $provider_id)
    {
        if(in_array($provider_id, $object->allowed_provider_ids)) {
            $object->provider_id = $provider_id;
        }else{
            throw new WeatherDataException('Provider id is not allowed.', 'Config Error');
        }

        return $object;
    }

    public function getLocation(): ConfigLocation
    {
        return $this->config_location;
    }

    public function getFrequency(): ConfigFrequency
    {
        return $this->config_frequency;
    }

    public function getWeatherData(): ConfigWeatherData
    {
        return $this->config_weather_data;
    }

    public function getUnits(): string
    {
        return $this->units;
    }

}
