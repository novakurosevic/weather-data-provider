<?php

namespace Noki\WeatherDataProvider\Dto;

class Wind extends AbstractWeatherData
{
    public mixed $time;
    public string|int|float|null $speed = null;

    public string|int|float|null $speed_min = null;
    public string|int|float|null $speed_max = null;
    public string|int|float|null $speed_average = null;
    public string|null $direction = null;
    public string|null $direction_average = null;
    public string $unit;

}
