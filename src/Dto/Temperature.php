<?php

namespace Noki\WeatherDataProvider\Dto;
class Temperature extends AbstractWeatherData
{
    public mixed $time;
    public string|int|float|null $temperature = null;
    public string|int|float|null $relative_temperature = null;
    public string|int|float|null $temperature_min = null;
    public string|int|float|null $temperature_max = null;
    public string|int|float|null $temperature_average = null;

    public string|int|float|null $relative_temperature_min = null;
    public string|int|float|null $relative_temperature_max = null;
    public string|int|float|null $relative_temperature_average = null;
    public string $unit;
    public null|string $description = null;
}

