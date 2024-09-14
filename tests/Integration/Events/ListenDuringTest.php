<?php

declare(strict_types=1);

namespace Illuminate\Tests\Integration\Events;

use Orchestra\Testbench\TestCase;

class ListenDuringTest extends TestCase
{
    public function testListenDuring()
    {
        $event = new TestEventForListenDuring();

        $this->app['events']->listenDuring(
            fn () => $this->app['events']->dispatch($event),
            TestEventForListenDuring::class,
            function (TestEventForListenDuring $event) {
                $event->foo = 'bar';
            },
        );

        $this->assertCount(0, $this->app['events']->getListeners(TestEventForListenDuring::class));
        $this->assertEquals('bar', $event->foo);
    }

    public function testListenDuringCallable()
    {
        $event = new TestEventForListenDuring();

        $this->app['events']->listenDuring(
            fn () => $this->app['events']->dispatch($event),
            TestEventForListenDuring::class,
            [new TestEventForListenDuringCallable, 'handle'],
        );

        $this->assertCount(0, $this->app['events']->getListeners(TestEventForListenDuring::class));
        $this->assertEquals('bar', $event->foo);
    }

    public function testListenDuringClass()
    {
        $event = new TestEventForListenDuring();

        $this->app['events']->listenDuring(
            fn () => $this->app['events']->dispatch($event),
            TestEventForListenDuring::class,
            TestEventForListenDuringCallable::class,
        );

        $this->assertCount(0, $this->app['events']->getListeners(TestEventForListenDuring::class));
        $this->assertEquals('bar', $event->foo);
    }

    public function testListenDuringClosure()
    {
        $event = new TestEventForListenDuring();

        $this->app['events']->listenDuring(
            fn () => $this->app['events']->dispatch($event),
            function (TestEventForListenDuring $event) {
                $event->foo = 'bar';
            },
        );

        $this->assertCount(0, $this->app['events']->getListeners(TestEventForListenDuring::class));
        $this->assertEquals('bar', $event->foo);
    }

    public function testItDoesNotListenToADifferentEvent()
    {
        $event = new TestEventForListenDuring();

        $this->app['events']->listenDuring(
            fn () => $this->app['events']->dispatch($event),
            function (AnotherEventForListenDuring $event) {
                $event->foo = 'bar';
            },
        );

        $this->assertCount(0, $this->app['events']->getListeners(TestEventForListenDuring::class));
        $this->assertNull($event->foo);
    }
}

class TestEventForListenDuring
{
    public $foo = null;
}

class TestEventForListenDuringCallable
{
    public function handle(TestEventForListenDuring $event)
    {
        $event->foo = 'bar';
    }
}

class AnotherEventForListenDuring
{
    public $foo = null;
}
