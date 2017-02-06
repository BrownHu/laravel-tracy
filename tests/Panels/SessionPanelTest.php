<?php

namespace Recca0120\LaravelTracy\Tests\Panels;

use Mockery as m;
use Recca0120\LaravelTracy\Panels\SessionPanel;

class SessionPanelTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @runTestsInSeparateProcesses
     */
    public function testRender()
    {
        @session_start();
        $panel = new SessionPanel();
        $panel->setLaravel(
            $laravel = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $laravel->shouldReceive('offsetGet')->once()->with('session')->andReturn(
             $sessionManager = m::mock('Illuminate\Session\SessionManager')
        );
        $sessionManager->shouldReceive('getId')->once()->andReturn($id = 'foo');
        $sessionManager->shouldReceive('getSessionConfig')->once()->andReturn($sessionConfig = ['foo']);
        $sessionManager->shouldReceive('all')->once()->andReturn($laravelSession = ['foo']);
        $panel->getTab();
        $panel->getPanel();
        $this->assertAttributeSame([
            'rows' => [
                'sessionId' => $id,
                'sessionConfig' => $sessionConfig,
                'laravelSession' => $laravelSession,
                'nativeSessionId' => session_id(),
                'nativeSession' => $_SESSION,
            ],
        ], 'attributes', $panel);
    }
}
