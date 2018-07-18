<?php

namespace Maba\Tests;

use Maba\Command;
use Maba\NotifierInterface;
use Maba\WeatherProviderInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mockery;

class CommandTest extends TestCase
{
    /**
     * This does not work (look at the comments)
     */
    public function testRunWithPhpUnitMock()
    {
        /** @var MockObject|WeatherProviderInterface $weatherProvider */
        $weatherProvider = $this->createMock(WeatherProviderInterface::class);

        /** @var MockObject|NotifierInterface $notifier */
        $notifier = $this->createMock(NotifierInterface::class);

        // fails, as second line overwrites the assertation
        $weatherProvider->method('getTemperature')->with('today')->willReturn(20);
        $weatherProvider->method('getTemperature')->with('tomorrow')->willReturn(25);
        $weatherProvider->method('getTemperature')->with('yesterday')->willReturn(15);

        // the same - not as expected
        $notifier->expects($this->once())->method('notify')->with('Today is 20');
        $notifier->expects($this->once())->method('notify')->with('Tomorrow will be 25');

        $command = new Command($weatherProvider, $notifier);
        $command->run();
    }

    /**
     * So we need to use it like this, but it's not so straightforward
     */
    public function testRunWithPhpUnitMockAndAt()
    {
        /** @var MockObject|WeatherProviderInterface $weatherProvider */
        $weatherProvider = $this->createMock(WeatherProviderInterface::class);

        /** @var MockObject|NotifierInterface $notifier */
        $notifier = $this->createMock(NotifierInterface::class);

        $weatherProvider->method('getTemperature')->willReturnMap([
            ['today', 20],
            ['tomorrow', 25],
            ['yesterday', 15],
        ]);

        // now we need to assert the order, which is not what we want to do
        $notifier->expects($this->at(0))->method('notify')->with('Today is 20');
        $notifier->expects($this->at(1))->method('notify')->with('Tomorrow will be 25');

        $command = new Command($weatherProvider, $notifier);
        $command->run();
    }

    /**
     * Example with mockery
     */
    public function testRunWithMockery()
    {
        /** @var MockInterface|WeatherProviderInterface $weatherProvider */
        $weatherProvider = \Mockery::mock(WeatherProviderInterface::class);

        /** @var MockInterface|NotifierInterface $notifier */
        $notifier = \Mockery::mock(NotifierInterface::class);

        $weatherProvider->shouldReceive('getTemperature')->withArgs(['today'])->andReturn(20);
        $weatherProvider->shouldReceive('getTemperature')->withArgs(['tomorrow'])->andReturn(25);
        $weatherProvider->shouldReceive('getTemperature')->withArgs(['yesterday'])->andReturn(15);

        // this line "eats" one call
        $notifier->shouldReceive('notify')->withArgs(['Today is 20'])->once();
        // so here only single call left, not two
        $notifier->shouldReceive('notify')->times(1);
        // also, expectation is required - mockery does not allow to call methods that were not expected
        // we could also avoid calling ->times(1) here if that's not important for us

        $command = new Command($weatherProvider, $notifier);
        $command->run();
    }

    /**
     * Prophesy lets adding assumptions just like we would call the methods themselves
     *
     * Not good if IDE static analysis is preferred
     */
    public function testRunWithPhpUnitProphesy()
    {
        $weatherProvider = $this->prophesize(WeatherProviderInterface::class);

        $notifier = $this->prophesize(NotifierInterface::class);

        $weatherProvider->getTemperature('today')->willReturn(20);
        $weatherProvider->getTemperature('tomorrow')->willReturn(25);
        $weatherProvider->getTemperature('yesterday')->willReturn(15);

        $notifier->notify('Today is 20')->shouldBeCalledTimes(1);
        $notifier->notify('Tomorrow will be 25')->shouldBeCalledTimes(1);

        $command = new Command($weatherProvider->reveal(), $notifier->reveal());
        $command->run();
    }

    /**
     * Mockery has almost the same thing - we need to call allows() or expects() before making assertion in that style
     */
    public function testRunWithMockeryAllows()
    {
        /** @var MockInterface|WeatherProviderInterface $weatherProvider */
        $weatherProvider = \Mockery::mock(WeatherProviderInterface::class);

        /** @var MockInterface|NotifierInterface $notifier */
        $notifier = \Mockery::mock(NotifierInterface::class);

        $weatherProvider->allows()->getTemperature('today')->andReturns(20);
        $weatherProvider->allows()->getTemperature('tomorrow')->andReturns(25);
        $weatherProvider->allows()->getTemperature('yesterday')->andReturns(15);

        $notifier->expects()->notify('Today is 20')->once();
        $notifier->expects()->notify('Tomorrow will be 25')->once();

        $command = new Command($weatherProvider, $notifier);
        $command->run();
    }

    public function tearDown()
    {
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
    }
}
