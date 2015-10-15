<?php

namespace Recca0120\LaravelTracy;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tracy\Debugger;

class Tracy
{
    public static function getBar()
    {
        ob_start();
        Debugger::getBar()->render();
        $content = ob_get_clean();

        return $content;
    }

    public static function getBlueScreen($e)
    {
        ob_start();
        Debugger::getBlueScreen()
            ->render($e);
        $content = ob_get_clean();

        return $content;
    }

    public static function getHttpResponse($content, Exception $e)
    {
        if (($e instanceof HttpException) === true) {
            $statusCode = $e->getStatusCode();
            $headers = $e->getHeaders();
        } else {
            $statusCode = 500;
            $headers = [];
        }

        return response($content, $statusCode, $headers);
    }

    /**
     * The Laravel Application.
     *
     * @var Application
     */
    protected $app;

    /**
     * The Exception Handler.
     *
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * Create a new middleware instance.
     *
     * @param Application      $app
     * @param ExceptionHandler $exceptionHandler
     */
    public function __construct(Application $app, ExceptionHandler $exceptionHandler)
    {
        $this->app = $app;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            $this->exceptionHandler->report($e);
            $response = $this->exceptionHandler->render($request, $e);
        }
        $response = $this->appendDebuggerInfo($request, $response);

        return $response;
    }

    public function appendDebuggerInfo($request, $response)
    {
        $content = $response->getContent();
        $pos = strripos($content, '</body>');
        if ($pos !== false and
            $request->isJson() === false and
            $request->wantsJson() === false and
            $request->ajax() === false and
            $request->pjax() === false) {
            $barResponse = self::getBar();
            $content = substr($content, 0, $pos).$barResponse.substr($content, $pos);

            $response->setContent($content);
        } else {
            foreach (str_split(base64_encode(@json_encode($debuggerJavascript)), 4990) as $k => $v) {
                $response->header('tracy-ajax-'.$k, $v);
            }
        }

        return $response;
    }
}
