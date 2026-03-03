<?php

declare(strict_types=1);

namespace BlueRegister\Events;

use BlueEvent\Event\BaseEvent;

class RegisterException extends BaseEvent
{
    /**
     * @var bool
     */
    protected static bool $allowKill = false;

    /**
     * Set var that allow to kill application if register exception is throwed away
     *
     * @param bool $allowKill
     */
    public static function allowKill(bool $allowKill): void
    {
        self::$allowKill = $allowKill;
    }

    /**
     * @return bool
     */
    public static function isKillingAllowed(): bool
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
            throw new \RuntimeException('System killed by Register Exception. Unknown class: ' . $parameters[0]);
        }
    }
}
