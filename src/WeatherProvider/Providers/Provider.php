<?php

namespace Noki\WeatherProvider\Providers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Noki\WeatherDataProvider\WeatherConfig as WeatherConfig;
use Noki\WeatherProvider\Exeptions\WeatherDataException as WeatherDataException;
use Noki\WeatherProvider\Interfaces\FetchFromAPIInterface;
use Noki\WeatherProvider\Interfaces\AllDataInterface;
use Illuminate\Http\Client\Response;

abstract class Provider implements FetchFromAPIInterface, AllDataInterface
{
    const string DATE_FORMAT = 'Y-m-d H:i:s';
    protected string $api_key; // API key for every provider
    protected WeatherConfig $config;
    protected array $unit_allowed = []; // Define allowed units in every provider class

    protected array $allowed_frequencies = []; // Define allowed frequencies in provider class

    public function fetchDataFromAPI():array
    {
        return [];
    }

    /*
     * Set time in custom format and into application timezone
     */
    protected function convertTime($time): string
    {
        $timezone = config('app.timezone');

        return Carbon::make($time)->setTimezone($timezone)->format(self::DATE_FORMAT);
    }

    protected function getSelectedUnit(string $field):string
    {
        $config_units = $this->config->getUnits();
        $selected_units = Arr::get($this->unit_allowed, $config_units, []);

        if(empty($selected_units)){
            throw new WeatherDataException("Selected provider does not support $config_units units.");
        }

        return Arr::get($selected_units, $field);
    }

    /**
     * @param Response $response
     * @return void
     * @throws WeatherDataException
     */
    protected function handleErrors(Response $response):void
    {
        if(!$response->successful() || $response->failed()) {
            $response_message = 'Error code: ' . $response->status() . ' Message: ' .$response->body();
            throw new WeatherDataException($response_message, 'API Error');
        }
    }

    public function allDataMinutely():array
    {
        $is_frequency_supported = Arr::get($this->allowed_frequencies, 'minutely', false);

        if($is_frequency_supported){
            $this->config->minutely(true)->hourly(false)->daily(false)
                ->humidity(true)->pressure(true)->wind(true)->temperature(true);

            return  $this->getCustomData();
        }

        return [];

    }

    public function allDataHourly():array
    {
        $is_frequency_supported = Arr::get($this->allowed_frequencies, 'hourly', false);

        if($is_frequency_supported){
            $this->config->minutely(false)->hourly(true)->daily(false)
                ->humidity(true)->pressure(true)->wind(true)->temperature(true);

            return  $this->getCustomData();
        }

        return [];

    }

    public function allDataDaily():array
    {
        $is_frequency_supported = Arr::get($this->allowed_frequencies, 'daily', false);

        if($is_frequency_supported){
            $this->config->minutely(false)->hourly(false)->daily(true)
                ->humidity(true)->pressure(true)->wind(true)->temperature(true);

            return  $this->getCustomData();
        }

        return [];

    }

    protected function getWindDirection(int|float $value):string
    {
        if( (45 < $value) && ($value <= 135) ) {
            $direction = 'East';
        }elseif ( (135 < $value) && ($value <= 225) ) {
            $direction = 'South';
        }elseif ( (225 < $value) && ($value <= 315) ) {
            $direction = 'West';
        }else{
            $direction = 'North';
        }

        return $direction;
    }

}





