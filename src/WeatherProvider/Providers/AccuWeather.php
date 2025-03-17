<?php

namespace Noki\WeatherDataProvider\WeatherProvider\Providers;

use Noki\WeatherDataProvider\Dto\Humidity;
use Noki\WeatherDataProvider\Dto\Pressure;
use Noki\WeatherDataProvider\Dto\Wind;
use Noki\WeatherProvider\Interfaces\CustomDataInterface;
use Noki\WeatherProvider\Exeptions\WeatherDataException as WeatherDataException;
use Illuminate\Support\Facades\Http;
use Noki\WeatherDataProvider\WeatherConfig as WeatherConfig;
use Illuminate\Support\Arr;
use Noki\WeatherDataProvider\Dto\Temperature;
use Noki\WeatherProvider\Providers\Provider;

class AccuWeather extends Provider implements CustomDataInterface
{
    protected array $unit_allowed = [
        WeatherConfig::UNITS_METRIC => [
            'temperature' => 'C',
            'wind' => 'km/h',
            'humidity' => '%',
            'pressure' => 'mb',
        ],
        WeatherConfig::UNITS_IMPERIAL => [
            'temperature' => 'F',
            'wind' => 'mph',
            'humidity' => '%',
            'pressure' => 'inHg'
        ]
    ];

    protected array $allowed_frequencies = [
      'minutely' => false,
      'hourly' => true,
      'daily' => false,
    ];

    protected $location_id = null;

    public function __construct(WeatherConfig $config)
    {
        // API key is required. Check for API key in configuration.
        $this->api_key = config('package.Noki.WeatherDataProvider.app.accuweather.api_key');

        if (empty($this->api_key)) {
            throw new WeatherDataException('ACCUWEATHER_API_KEY variable is not set in .env file.');
        }

        $this->config = $config;

    }

    /**
     * @param $request_method
     * @return array
     * @throws WeatherDataException
     */
    public function fetchDataFromAPI($request_method = 'GET'):array
    {
        $response = Http::send($request_method, $this->generateUrl());
        $this->handleErrors($response);

        return $response->json() ?? [];
    }

    protected function generateUrl():string
    {
        return 'http://dataservice.accuweather.com/currentconditions/v1/' . $this->location_id .
        '/historical/24?apikey=' . $this->api_key . '&language=en-us&details=true';
    }

    public function fetchLocationDataFromAPI($request_method = 'GET'):void
    {
        $response = Http::send($request_method, $this->generateLocationUrl());
        $this->handleErrors($response);

        $this->location_id = $response->json() != NULL && count($response->json()) > 0 ? $response->json()[0]['Key'] : null;
    }

    protected function generateLocationUrl():string
    {
        $location = $this->config->getLocation();
        //$units = $this->config->getUnits();

        return 'http://dataservice.accuweather.com/locations/v1/cities/search?apikey=' . $this->api_key .
            '&language=en-us&details=true&q='. $location->longitude . ',' .$location->latitude;

    }

    public function getCustomData():array
    {
        if(!isset($this->location_id)){
            $this->fetchLocationDataFromAPI();
        }
        $data = [];
        $api_response = $this->fetchDataFromAPI();

        if (!empty($api_response)) {
            $response_data = [];
            $response_data = array_merge($response_data, $this->provideDataForFrequency('hourly', $api_response));

            $data['data'] = $response_data;
            $data['location'] = $this->config->getLocation();
        }

        return $data;
    }



    protected function provideDataForFrequency($frequency, $api_response):array
    {
        $data = [];
        $frequency_selected = $this->config->getFrequency();
        $frequency_selected_in_config = $frequency_selected->$frequency ?? false;
        $is_frequency_supported = Arr::get($this->allowed_frequencies, $frequency, false);

        if($is_frequency_supported && $frequency_selected_in_config){
            $weather_data = $this->config->getWeatherData();
            $temperature_unit = $this->getSelectedUnit('temperature');
            $wind_unit = $this->getSelectedUnit('wind');
            $humidity_unit = $this->getSelectedUnit('humidity');
            $pressure_unit = $this->getSelectedUnit('pressure');
            $config_units = ucfirst($this->config->getUnits());

            $i = 1;

            foreach ($api_response  as $frequency_data) {
                $time = $this->convertTime(Arr::get($frequency_data, 'LocalObservationDateTime'));

                if($weather_data->temperature) {
                    $data[$frequency]['temperature'][] = new Temperature(
                        [
                            'id' => $i,
                            'time' => $time,
                            'temperature' => Arr::get($frequency_data, 'Temperature.' . $config_units . '.Value'),
                            'relative_temperature' => Arr::get($frequency_data, 'RealFeelTemperature.' . $config_units . '.Value'),
                            'unit' => $temperature_unit,
                        ]
                    );
                }

                if($weather_data->wind){
                    $data[$frequency]['wind'][] = new Wind(
                        [
                            'id' => $i,
                            'time' => $time,
                            'speed' => Arr::get($frequency_data, 'Wind.Speed.' . $config_units . '.Value'),
                            'direction' => $this->getWindDirection(
                                Arr::get($frequency_data, 'Wind.Direction.Degrees')
                            ),
                            'unit' => $wind_unit
                        ]
                    );
                }

                if($weather_data->humidity){
                    $data[$frequency]['humidity'][] = new Humidity(
                        [
                            'id' => $i,
                            'time' => $time,
                            'value' => Arr::get($frequency_data, 'RelativeHumidity'),
                            'unit' => $humidity_unit
                        ]
                    );
                }

                if($weather_data->pressure){
                    $data[$frequency]['pressure'][] = new Pressure(
                        [
                            'id' => $i,
                            'time' => $time,
                            'value' => Arr::get($frequency_data, 'Pressure.' . $config_units . '.Value'),
                            'unit' => $pressure_unit
                        ]
                    );
                }

                $i++;
            }

        }

        return $data;

    }


}
