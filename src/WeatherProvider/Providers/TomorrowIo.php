<?php

namespace Noki\WeatherProvider\Providers;

use Noki\WeatherDataProvider\Dto\Humidity;
use Noki\WeatherDataProvider\Dto\Pressure;
use Noki\WeatherDataProvider\Dto\Wind;
use Noki\WeatherProvider\Interfaces\CustomDataInterface;
use Noki\WeatherProvider\Exeptions\WeatherDataException as WeatherDataException;
use Illuminate\Support\Facades\Http;
use Noki\WeatherDataProvider\WeatherConfig as WeatherConfig;
use Illuminate\Support\Arr;
use Noki\WeatherDataProvider\Dto\Temperature;

class TomorrowIo extends Provider implements CustomDataInterface
{
    protected array $unit_allowed = [
        WeatherConfig::UNITS_METRIC => [
            'temperature' => 'Celsius',
            'wind' => 'm/s',
            'humidity' => '%',
            'pressure' => 'hPa',
        ],
        WeatherConfig::UNITS_IMPERIAL => [
            'temperature' => 'Fahrenheit',
            'wind' => 'mph',
            'humidity' => '%',
            'pressure' => 'inHg'
        ]
    ];

    protected array $allowed_frequencies = [
      'minutely' => true,
      'hourly' => true,
      'daily' => true,
    ];

    public function __construct(WeatherConfig $config)
    {
        // API key is required. Check for API key in configuration.
        $this->api_key = config('package.Noki.WeatherDataProvider.app.tomorrow_io.api_key');

        if (empty($this->api_key)) {
            throw new WeatherDataException('TOMORROW_IO_API_KEY variable is not set in .env file.');
        }

        $this->config = $config;

    }

    public function fetchDataFromAPI($request_method = 'GET'):array
    {
        $response = Http::send($request_method, $this->generateUrl());
        $this->handleErrors($response);

        return $response->json() ?? [];
    }

    protected function generateUrl():string
    {
        $location = $this->config->getLocation();
        $units = $this->config->getUnits();

        return 'https://api.tomorrow.io/v4/weather/forecast?location=' . $location->longitude . ',' .$location->latitude .
            '&apikey=' . $this->api_key . '&units=' . $units;
    }

    public function getCustomData():array
    {
        $api_response = $this->fetchDataFromAPI();
        $data = [];

        if (!empty($api_response)) {
            $response_data = [];
            $response_data = array_merge($response_data, $this->provideDataForFrequency('minutely', $api_response));
            $response_data = array_merge($response_data, $this->provideDataForFrequency('hourly', $api_response));
            $response_data = array_merge($response_data, $this->provideDataForFrequency('daily', $api_response));

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

            $response_data[$frequency] = Arr::get($api_response, "timelines.$frequency", []);
            $i = 1;

            foreach ($response_data[$frequency]  as $frequency_data) {
                $time = $this->convertTime(Arr::get($frequency_data, 'time'));

                if($weather_data->temperature) {
                    if($frequency == 'daily'){
                        $data[$frequency]['temperature'][] = new Temperature(
                            [
                                'id' => $i,
                                'time' => $time,
                                'temperature_min' => Arr::get($frequency_data, 'values.temperatureMin'),
                                'temperature_max' => Arr::get($frequency_data, 'values.temperatureMax'),
                                'temperature_average' => Arr::get($frequency_data, 'values.temperatureAvg'),
                                'relative_temperature_min' => Arr::get($frequency_data, 'values.temperatureApparentMin'),
                                'relative_temperature_max' => Arr::get($frequency_data, 'values.temperatureApparentMin'),
                                'relative_temperature_average' => Arr::get($frequency_data, 'values.temperatureApparentMin'),
                                'unit' => $temperature_unit,
                            ]
                        );
                    }else{
                        $data[$frequency]['temperature'][] = new Temperature(
                            [
                                'id' => $i,
                                'time' => $time,
                                'temperature' => Arr::get($frequency_data, 'values.temperature'),
                                'relative_temperature' => Arr::get($frequency_data, 'values.temperatureApparent'),
                                'unit' => $temperature_unit,
                            ]
                        );
                    }
                }

                if($weather_data->wind){
                    if($frequency == 'daily'){
                        $data[$frequency]['wind'][] = new Wind(
                            [
                                'id' => $i,
                                'time' => $time,
                                'speed_min' => Arr::get($frequency_data, 'values.windSpeedMin'),
                                'speed_max' => Arr::get($frequency_data, 'values.windSpeedMax'),
                                'speed_average' => Arr::get($frequency_data, 'values.windSpeedAvg'),
                                'direction_average' => $this->getWindDirection(
                                    Arr::get($frequency_data, 'values.windDirectionAvg')
                                ),
                                'unit' => $wind_unit
                            ]
                        );
                    }else{
                        $data[$frequency]['wind'][] = new Wind(
                            [
                                'id' => $i,
                                'time' => $time,
                                'speed' => Arr::get($frequency_data, 'values.windSpeed'),
                                'direction' => $this->getWindDirection(
                                    Arr::get($frequency_data, 'values.windDirection')
                                ),
                                'unit' => $wind_unit
                            ]
                        );
                    }
                }

                if($weather_data->humidity){
                    if($frequency == 'daily'){
                        $data[$frequency]['humidity'][] = new Humidity(
                            [
                                'id' => $i,
                                'time' => $time,
                                'value_min' => Arr::get($frequency_data, 'values.humidityMin'),
                                'value_max' => Arr::get($frequency_data, 'values.humidityMax'),
                                'value_average' => Arr::get($frequency_data, 'values.humidityAvg'),
                                'unit' => $humidity_unit
                            ]
                        );
                    }else{
                        $data[$frequency]['humidity'][] = new Humidity(
                            [
                                'id' => $i,
                                'time' => $time,
                                'value' => Arr::get($frequency_data, 'values.humidity'),
                                'unit' => $humidity_unit
                            ]
                        );
                    }

                }

                if($weather_data->pressure){
                    if($frequency == 'daily'){
                        $data[$frequency]['pressure'][] = new Pressure(
                            [
                                'id' => $i,
                                'time' => $time,
                                'value_min' => Arr::get($frequency_data, 'values.pressureSurfaceLevelMin'),
                                'value_max' => Arr::get($frequency_data, 'values.pressureSurfaceLevelMax'),
                                'value_average' => Arr::get($frequency_data, 'values.pressureSurfaceLevelAvg'),
                                'unit' => $pressure_unit
                            ]
                        );
                    }else{
                        $data[$frequency]['pressure'][] = new Pressure(
                            [
                                'id' => $i,
                                'time' => $time,
                                'value' => Arr::get($frequency_data, 'values.pressureSurfaceLevel'),
                                'unit' => $pressure_unit
                            ]
                        );
                    }
                }

                $i++;
            }

        }

        return $data;

    }


}
