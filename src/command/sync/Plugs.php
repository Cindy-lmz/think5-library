<?php
namespace library\command\sync;

use library\command\Sync;
use think\console\Input;
use think\console\Output;

/**
 * Class Plugs
 * @package library\command\sync
 */
class Plugs extends Sync
{

    /**
     * 指令属性配置
     */
    protected function configure()
    {
        $this->modules = ['public/static/'];
        $this->setName('xsync:plugs')->setDescription('[同步]覆盖本地Plugs插件代码');
    }

    /**
     * 执行更新操作
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $root = str_replace('\\', '/', env('root_path'));
        if (file_exists("{$root}/public/static/sync.lock")) {
            $this->output->error("--- Plugs 资源已经被锁定，不能继续更新");
        } else {
            parent::execute($input, $output);
        }
    }
}
