<?php

namespace Noki\WeatherProvider\Interfaces;

interface AllDataInterface
{
    public function allDataMinutely():array;
    public function allDataHourly():array;
    public function allDataDaily():array;



}
