<?php
/**
 * Created by PhpStorm.
 * User: webmalc
 * Date: 12/14/16
 * Time: 11:48 AM
 */

namespace MBH\Bundle\BaseBundle\Lib\Task;
use Symfony\Bridge\Monolog\Logger;

trait LoggerTrait
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param array $message
     * @return string
     */
    public function logStart(array $message):string
    {
        $log = self::class . 'message get. data: ';

        foreach ($message as $key => $value) {
            if ($value instanceof \DateTime) {
                $value = $value->format('d.m.Y H:i');
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $log .= $key . ' - ' . $value . '; ';
        }
        $this->logger->info($log);

        return $log;
    }

    /**
     * @return string
     */
    public function logCompete():string
    {
        $log = self::class . ': complete.';
        $this->logger->info($log);

        return $log;
    }
}