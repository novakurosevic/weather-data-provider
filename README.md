# Weather Data Provider

Simple Laravel weather data provider

[![Latest Version on Packagist](https://img.shields.io/packagist/v/noki/weather-data-provider.svg?style=flat-square)](https://packagist.org/packages/noki/weather-data-provider)


This is simple Laravel package for providing weather information. At this moment in version 1.0.0 there are only supported two weather providers with option to provide weather data in past. It is possible to provide weather for last 24h with display weather data per hour or weather data for last hour by minute. New features should be added in the future.

## Installation

You can install the package via composer:

```bash
composer require noki/weather-data-provider
```

After composer finish installation run command:
```bash
composer dump-autoload
```

Go to weather providers website and register account. There you will find **api key** that you need to add to **.env** file of Laravel application.

Weather provider links:
- [Tomorrow.io](https://www.tomorrow.io/weather-api/)
- [Accuweather](https://developer.accuweather.com/)


Add to **.env** file:
```.dotenv
TOMORROW_IO_API_KEY="Your Tomorrow.io api key"
ACCUWEATHER_API_KEY="Your Accuweather api key"
```

Run command:
```php
php artisan optimize:clear
```


## Usage

### Before usage
There are required fields that you need to set in weather configuration for providing weather data.

At this moment there are two supported weather providers:

| Weather provider | Provider Id |
|------------------|-------------|
| Tomorrow IO      | 1           |
| AccuWeather      | 2           |



### How to use?

#### Config setting
For providing weather it is required to add provider id in WeatherConfig and longitude and latitude of location that we require weather data.

Example of config with provider Tomorrow IO (id 1) and longitude and latitude for Berlin, Germany. For longitude and latitude you can use strings and numbers (integer and float).

```php
$config = WeatherConfig::create(1, '52.5202',13.4043);
```

**Config example 2:** Setting of minutely, hourly and daily weather data frequencies. **Note:** Some integrations do not support all of these options. Usually all integrations support hourly weather data.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->minutely()->hourly()->daily();
```

**Config example 3:** Setting of minutely and hourly weather data frequencies and turning off daily.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->minutely()->hourly()->daily(false);
```

**Config example 4:** Setting of minutely and hourly weather data frequencies and turning off daily.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->minutely()->hourly()->daily(false);
```

**Config example 5:** Set providing data about temperature, pressure, wind and humidity.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->temperature()->pressure()->wind()->humidity();
```

**Config example 6:** Set imperial units for weather data output.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->imperial();
```

**Config example 7:** Set metric units for weather data output.
```php
$config = WeatherConfig::create(1, '52.5202',13.4043)->metric();
```


**Config example 8:** Set custom settings. 

Required fields:
- **provider_id** is required, 
- **config_location** is required,
- one parameter from **config_weather_data** must be true
- one parameter from **config_frequency** must be true
- **units** is by default set to metric unit, you can overwrite it if you want imperial units. Avoiding this filed will output metric units.

```php
$config = WeatherConfig::fromArray([
        'provider_id' => 1,
        'config_location' => [
            'longitude' => '52.5202',
            'latitude' => '13.4043'
        ],
        'config_weather_data' => [
            'temperature' => true,
            'wind' => true,
            'pressure' => false,
            'humidity' => false
        ],
        'config_frequency' => [
            'minutely' => true,
            'hourly' => true,
            'daily' => false
        ],
        'units' => 'imperial'
    ]);
```

**Config example 9:** Set custom settings other way of setting.

Settings below will provide temperature, wind and pressure data for frequencies per minute and per hour. Units will be metric.

```php
$config = WeatherConfig::fromArray([
        'provider_id' => 1,
        'config_location' => [
            'longitude' => '52.5202',
            'latitude' => '13.4043'
        ],
        'config_weather_data' => [
            'temperature',
            'wind',
            'pressure'
        ],
        'config_frequency' => [
            'minutely',
            'hourly'
        ]
    ]);
```


#### Examples of weather data providing

```php
$config = WeatherConfig::create(1, '52.5202','13.4043');
$provider = new ProviderGenerator($config);
// Weather data for frequencies that you have choose
$provider->selected_provider->getCustomData(); 
```

#
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Stefan Damjanovic](https://github.com/stfndamjanovic)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

