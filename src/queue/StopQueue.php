<?php
namespace library\queue;

use library\service\ProcessService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 平滑停止任务的所有进程
 * Class StopQueue
 * @package library\queue
 */
class StopQueue extends Command
{

    /**
     * 指令属性配置
     */
    protected function configure()
    {
        $this->setName('xtask:stop')->setDescription('Smooth stop of all task processes');
    }

    /**
     * 停止所有任务执行
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $process = ProcessService::instance();
        $command = $process->think('xtask:');
        if (count($result = $process->query($command)) < 1) {
            $output->writeln("There is no task process to finish");
        } else foreach ($result as $item) {
            $process->close($item['pid']);
            $output->writeln("Sending end process {$item['pid']} signal succeeded");
        }
    }
}
