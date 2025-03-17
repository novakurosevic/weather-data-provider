<?php

namespace Noki\WeatherDataProvider\Dto;

class Humidity extends AbstractWeatherData
{
    public mixed $time;
    public string|int|float $value;
    public string|int|float $value_min;
    public string|int|float $value_max;
    public string|int|float $value_average;
    public string $unit;

}
