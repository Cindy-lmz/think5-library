<?php


namespace library\command\sync;

use library\command\Sync;
use think\console\Input;
use think\console\Output;

/**
 * Class Service
 * @package library\command\sync
 */
class Service extends Sync
{
    /**
     * 指令属性配置
     */
    protected function configure()
    {
        $this->modules = ['application/service/'];
        $this->setName('xsync:service')->setDescription('[同步]覆盖本地Service模块代码');
    }

    /**
     * 执行更新操作
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $root = str_replace('\\', '/', env('root_path'));
        if (file_exists("{$root}/application/service/sync.lock")) {
            $this->output->error("--- Service 模块已经被锁定，不能继续更新");
        } else {
            parent::execute($input, $output);
        }
    }
}
