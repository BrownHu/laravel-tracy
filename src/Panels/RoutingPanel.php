<?php

namespace Recca0120\LaravelTracy\Panels;

use Illuminate\Support\Arr;

class RoutingPanel extends AbstractPanel
{
    /**
     * getAttributes.
     *
     * @method getAttributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $rows = [
            'uri' => 404,
        ];
        if ($this->isLaravel() === true) {
            $router = $this->laravel['router'];
            $currentRoute = $router->getCurrentRoute();
            if ($currentRoute !== null) {
                $rows = array_merge([
                    'uri' => $currentRoute->uri(),
                ], $currentRoute->getAction());
            }
        } else {
            $rows['uri'] = empty(Arr::get($_SERVER, 'HTTP_HOST')) === true ?
                404 : Arr::get($_SERVER, 'REQUEST_URI');
        }

        return compact('rows');
    }
}
