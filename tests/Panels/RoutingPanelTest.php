<?php

use Mockery as m;
use Recca0120\LaravelTracy\Panels\RoutingPanel;

class RoutingPanelTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_with_laravel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $route = m::mock('stdClass');
        $router = m::mock('Illuminate\Contracts\Routing\Registrar');
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $panel = new RoutingPanel();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $route
            ->shouldReceive('uri')
            ->shouldReceive('getAction')->andReturn([]);
        $router
            ->shouldReceive('getCurrentRoute')->andReturn($route);
        $app
            ->shouldReceive('version')->andReturn(5.2)
            ->shouldReceive('offsetGet')->with('router')->andReturn($router);
        $panel->setLaravel($app);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $panel->getTab();
        $panel->getPanel();
    }

    public function test_without_laravel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $panel = new RoutingPanel();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $panel->getTab();
        $panel->getPanel();
    }

    public function test_without_laravel_but_with_host()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $panel = new RoutingPanel();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $_SERVER['HTTP_HOST'] = 'http://localhost';

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $panel->getTab();
        $panel->getPanel();
    }
}
