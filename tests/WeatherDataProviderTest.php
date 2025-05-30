<?php

namespace Noki\WeatherDataProvider\Tests;

use PHPUnit\Framework\TestCase;
use Noki\WeatherDataProvider\ProviderGenerator;
use Noki\WeatherDataProvider\WeatherConfig as WeatherConfig;
use Noki\WeatherProvider\Providers\TomorrowIo;
use Illuminate\Container\Attributes\Config;


class WeatherDataProviderTest extends TestCase
{
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        // Mock config value here if necessary for the test
//        //Config::set('package.Noki.WeatherDataProvider.app.tomorrow_io.api_key', 'fake-api-key');
//
//        // Ensure the facades are bootstrapped if needed
//        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
//    }

    public function test_tomorrow_io_hourly()
    {
        // Set the environment variable temporarily
        putenv('TOMORROW_IO_API_KEY=B1mraCRF1EN1DTNJhAxaglGuxAwmY1MS');
        //Config::set('world.name' , 'foo');



       // app()->make('config')->get('database.default')



        $config = WeatherConfig::create(1, '45.5682',19.6438);
        $provider = new ProviderGenerator($config);
dd('123');
        $result = $provider->selected_provider->getCustomData();

        dd($result);

//        dd('1');
//        $this->config = new \stdClass();
//
//        dd($this->config );
//        Config::set('tomorrow_io.api_key', 'Test App Name');
//        dd($this->config->get('tomorrow_io.api_key'));


//        $config = WeatherConfig::create(1, '45.5682',19.6438);
//        $provider = new ProviderGenerator($config);
//        $result_data = $provider->selected_provider->getCustomData();
//
//        dd($result_data);

//        $this->assertEquals(123, $result);
    }



}
