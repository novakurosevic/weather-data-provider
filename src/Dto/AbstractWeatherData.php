<?php

namespace Noki\WeatherDataProvider\Dto;

abstract class AbstractWeatherData
{
    public string $id;

    public function __construct($params = null)
    {
        if(isset($params)) {
            foreach ($params as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }
}

