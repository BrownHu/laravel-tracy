<?php

namespace Recca0120\LaravelTracy\Panels;

use Tracy\IBarPanel;

abstract class AbstractPanel implements IBarPanel
{
    public $data = [];

    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function id()
    {
        return str_replace('panel', '', strtolower($this->getClassBasename()));
    }

    public function getClassBasename()
    {
        return class_basename(get_class($this));
    }

    public function getId()
    {
        return str_replace('panel', '', strtolower($this->getClassBasename()));
    }

    public function setData($data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toJson()
    {
        $jsonData = array_merge([
            'id' => $this->getClassBasename(),
        ], $this->data);

        return $jsonData;
    }

    /**
     * Renders HTML code for custom tab.
     *
     * @return string
     */
    public function getTab()
    {
        $data = array_merge($this->getData(), [
            'toHtmlOption' => array_get($this->config, 'dumpOption', []),
        ]);
        $view = 'laravel-tracy::'.$this->getClassBasename().'.tab';
        $response = null;
        if (view()->exists($view)) {
            $response = view($view, $data);
        }

        return $response;
    }

    /**
     * Renders HTML code for custom panel.
     *
     * @return string
     */
    public function getPanel()
    {
        $data = array_merge($this->getData(), [
            'toHtmlOption' => array_get($this->config, 'dumpOption', []),
        ]);
        $view = 'laravel-tracy::'.$this->getClassBasename().'.panel';
        $response = null;
        if (view()->exists($view)) {
            $response = view($view, $data);
        }

        return $response;
    }
}
