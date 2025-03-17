<?php

namespace Noki\WeatherProvider\Exeptions;

use Exception;

class WeatherDataException extends Exception
{
    protected $message = '';
    protected string $title = '';

    public function __construct(string $message = '', string $title = 'Error')
    {
        parent::__construct($message);
        $this->title = $title;
        $this->message = $message;
    }

    public function render(){
        return response()->json([
        'exception' =>
            [
                'title'  => $this->title,
                'message' => $this->message
            ],
        ]);
    }
}
