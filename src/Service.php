<?php
namespace library;

use think\App;
use think\Container;
use think\Request;

/**
 * 自定义服务接口
 * Class Service
 * @package library
 */
abstract class Service
{
    /**
     * 当前实例应用
     * @var App
     */
    protected $app;

    /**
     * 当前请求对象
     * @var \think\Request
     */
    protected $request;

    /**
     * Service constructor.
     * @param App $app
     * @param Request $request
     */
    public function __construct(App $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
        $this->initialize();
    }

    /**
     * 初始化服务
     * @return $this
     */
    protected function initialize()
    {
        return $this;
    }

    /**
     * 静态实例对象
     * @return static
     */
    public static function instance()
    {
        return Container::getInstance()->make(static::class);
    }

}