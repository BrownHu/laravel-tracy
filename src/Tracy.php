<?php

namespace Recca0120\LaravelTracy;

use ErrorException;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tracy\Debugger;
use Tracy\Helpers;
use Tracy\IBarPanel;

class Tracy
{
    /**
     * $config.
     *
     * @var array
     */
    protected $config;

    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * $request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * $panels.
     *
     * @var array
     */
    public $panels = [];

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param array $config
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Http\Request                     $request
     */
    public function __construct($config = [], ApplicationContract $app = null, Request $request = null)
    {
        $this->config = $config;
        $this->app = $app;
        $this->request = is_null($request) === true ? Request::capture() : $request;
    }

    /**
     * initialize.
     *
     * @method initialize
     *
     * @return bool
     */
    public function initialize()
    {
        if (Debugger::getBar()->dispatchAssets() === true) {
            exit;
        }

        if ($this->isRunningInConsole() === true || array_get($this->config, 'enabled', true) === false) {
            return false;
        }

        Debugger::$editor = array_get($this->config, 'editor', Debugger::$editor);
        Debugger::$maxDepth = array_get($this->config, 'maxDepth', Debugger::$maxDepth);
        Debugger::$maxLength = array_get($this->config, 'maxLength', Debugger::$maxLength);
        Debugger::$scream = array_get($this->config, 'scream', true);
        Debugger::$showLocation = array_get($this->config, 'showLocation', true);
        Debugger::$strictMode = array_get($this->config, 'strictMode', true);
        Debugger::$time = array_get($_SERVER, 'REQUEST_TIME_FLOAT', microtime(true));

        $panels = array_get($this->config, 'panels', []);
        foreach ($panels as $panel => $enabled) {
            if ($panel === 'user') {
                $panel = 'auth';
            }
            if ($enabled === false) {
                continue;
            }

            $className = '\\'.__NAMESPACE__.'\Panels\\'.ucfirst($panel).'Panel';
            $class = new $className();

            $this->addPanel($class, $panel);
        }

        return true;
    }

    /**
     * isRunningInConsole.
     *
     * @method isRunningInConsole
     *
     * @return bool
     */
    protected function isRunningInConsole()
    {
        return is_null($this->app) === false && $this->app->runningInConsole() === true;
    }

    /**
     * renderBlueScreen.
     *
     * @method renderBlueScreen
     *
     * @param  \Exception $exception
     *
     * @return string
     */
    public function renderBlueScreen(Exception $exception)
    {
        $error = error_get_last();
        if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_USER_ERROR], true)) {
            $exception = Helpers::fixStack(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }

        ob_start();
        Helpers::improveException($exception);
        Debugger::getBlueScreen()->render($exception);
        $content = ob_get_clean();

        return $content;
    }

    /**
     * renderResponse.
     *
     * @method renderResponse
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderResponse(Response $response)
    {
        if (
            $response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse ||
            $response->isRedirection() === true ||
            $this->acceptRenderResponse($response) === false
        ) {
            return $response;
        }

        $response->setContent($this->appendDebugbar($response->getContent()));

        return $response;
    }

    /**
     * acceptRenderResponse.
     *
     * @method acceptRenderResponse
     *
     * @param \Symfony\Component\HttpFoundation\Response $response $response
     *
     * @return bool
     */
    protected function acceptRenderResponse($response)
    {
        if ($this->request->ajax() === true) {
            return true;
        }

        $accepts = array_get($this->config, 'accepts', []);

        if (count($accepts) === 0) {
            return false;
        }

        $contentType = strtolower($response->headers->get('Content-type'));

        foreach ($accepts as $accept) {
            if (strpos($contentType, $accept) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * appendDebugbar.
     *
     * @method appendDebugbar
     *
     * @param string $content
     *
     * @return string
     */
    public function appendDebugbar($content)
    {
        if (array_get($this->config, 'showBar', true) === false) {
            return $content;
        }

        $barPanels = $this->renderPanel();
        $pos = strripos($content, '</body>');
        if ($pos !== false) {
            $content = substr($content, 0, $pos).$barPanels.substr($content, $pos);
        }

        return $content;
    }

    /**
     * addPanel description.
     *
     * @method addPanel
     *
     * @param \Tracy\IBarPanel  $panel
     * @param string            $id
     *
     * @return $this
     */
    public function addPanel(IBarPanel $panel, $id)
    {
        $panel->setLaravel($this->app);
        $this->panels[$id] = $panel;

        return $this;
    }

    /**
     * getPanel.
     *
     * @method getPanel
     *
     * @param string  $id
     *
     * @return \Tracy\IBarPanel
     */
    public function getPanel($id)
    {
        return array_get($this->panels, $id);
    }

    /**
     * getPanels.
     *
     * @method getPanels
     *
     * @return array
     */
    public function getPanels()
    {
        return $this->panels;
    }

    /**
     * renderPanel.
     *
     * @method renderPanel
     *
     * @return string
     */
    public function renderPanel()
    {
        $this->sessionStart();
        Debugger::dispatch();
        $bar = Debugger::getBar();
        $this->setupPanels($bar);
        ob_start();
        $bar->render();
        $content = ob_get_clean();
        $this->sessionClose();

        return $content;
    }

    /**
     * setupPanels.
     *
     * @method setupPanels
     *
     * @param \Tracy\Bar $bar
     *
     * @return static
     */
    protected function setupPanels($bar)
    {
        $isAjax = $this->request->ajax();
        foreach ($this->getPanels() as $panel) {
            if ($isAjax === true && $panel->supportAjax === false) {
                continue;
            }
            $bar->addPanel($panel);
        }

        return $this;
    }

    /**
     * obStart.
     *
     * @method obStart
     *
     * @return $this
     */
    public function obStart()
    {
        ob_start();

        return $this;
    }

    /**
     * obEnd.
     *
     * @method obEnd
     *
     * @return $this
     */
    public function obEnd()
    {
        ob_end_flush();

        return $this;
    }

    /**
     * sessionStart.
     *
     * @method sessionStart
     */
    public function sessionStart()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_path', '/');
            ini_set('session.cookie_httponly', '1');
            @session_start();
        }

        return $this;
    }

    /**
     * sessionClose.
     *
     * @method sessionClose
     */
    private function sessionClose()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $this;
    }

    /**
     * enable.
     *
     * @method enable
     *
     * @param  array$config
     * @return static
     */
    public static function enable($config = [], $sessionStart = true)
    {
        $config = array_merge([
            'enabled'      => true,
            'showBar'      => true,
            'editor'       => 'subl://open?url=file://%file&line=%line',
            'maxDepth'     => 4,
            'maxLength'    => 1000,
            'scream'       => true,
            'showLocation' => true,
            'strictMode'   => true,
            'panels'       => [
                'routing'  => false,
                'database' => true,
                'view'     => false,
                'event'    => false,
                'session'  => true,
                'request'  => true,
                'auth'     => true,
                'terminal' => false,
            ],
        ], $config);

        $tracy = new static($config);
        $tracy->initialize();

        if ($sessionStart === true) {
            $tracy->sessionStart();
        }

        return $tracy;
    }
}
