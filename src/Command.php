<?php

namespace Maba;

class Command
{
    private $weatherProvider;
    private $notifier;

    public function __construct(WeatherProviderInterface $weatherProvider, NotifierInterface $notifier)
    {
        $this->weatherProvider = $weatherProvider;
        $this->notifier = $notifier;
    }

    public function run()
    {
        $temperatureToday = $this->weatherProvider->getTemperature('today');
        $temperatureTomorrow = $this->weatherProvider->getTemperature('tomorrow');

        $this->notifier->notify("Today is $temperatureToday");
        $this->notifier->notify("Tomorrow will be $temperatureTomorrow");
    }
}
