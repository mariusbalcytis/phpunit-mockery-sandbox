<?php

namespace Maba;

interface WeatherProviderInterface
{

    public function getTemperature(string $time): float;
}
