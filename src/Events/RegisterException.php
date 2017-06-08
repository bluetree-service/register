<?php

namespace BlueRegister\Events;

use BlueEvent\Event\BaseEvent;

class RegisterException extends BaseEvent
{
    /**
     * @var \SimpleLog\LogInterface
     */
    protected $log;

    /**
     * @var bool
     */
    protected static $allowKill = false;

    /**
     * Set var that allow to kill application if register exception is throwed away
     *
     * @param bool $allowKill
     */
    public static function allowKill($allowKill)
    {
        self::$allowKill = (bool)$allowKill;
    }

    /**
     * @return bool
     */
    public static function isKillingAllowed()
    {
        return self::$allowKill;
    }

    /**
     * Allow to kill application if register throw an exception
     *
     * @param string $eventName
     * @param array $parameters
     * @throws \Exception
     */
    public function __construct($eventName, array $parameters)
    {
        parent::__construct($eventName, $parameters);

        if (self::$allowKill) {
            $this->log->makeLog('System killed by Register Exception.');
            throw new \RuntimeException('System killed by Register Exception.');
        }
    }
}
