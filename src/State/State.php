<?php

declare(strict_types=1);

namespace State;

use Exception;

class State
{
    public const STATE_SUCCESS = 'success';
    public const STATE_ERROR = 'error';

    public const LAST_NOTIFY_PREFIX = 'last_notify_';

    public const LAST_STATE_FILENAME = 'last_state';

    public const VALID_STATES = [
        self::STATE_SUCCESS,
        self::STATE_ERROR,
    ];

    public static int $successMinElapsedTime = 0;
    public static int $errorMinElapsedTime = 0;

    /**
     * @param string $state
     * @throws Exception
     */
    public static function save(string $state): void
    {
        if (!in_array($state, static::VALID_STATES, true)) {
            throw new Exception('Invalid state: ' . $state);
        }

        file_put_contents(static::LAST_STATE_FILENAME, $state);
    }

    /**
     * @param string $state
     * @throws Exception
     */
    public static function saveNotificationTime(string $state): void
    {
        if (!in_array($state, static::VALID_STATES, true)) {
            throw new Exception('Invalid state: ' . $state);
        }

        file_put_contents(static::LAST_NOTIFY_PREFIX . $state, time());
    }

    /**
     * @param string $state
     * @return bool
     * @throws Exception
     */
    public static function needNotify(string $state): bool
    {
        $lastState = static::getLastState();

        // First check
        if ($lastState === null) {
            return true;
        }

        // State changed
        if ($lastState !== $state) {
            return true;
        }

        return static::needReNotify($state);
    }

    /**
     * @param string $state
     * @return bool
     * @throws Exception
     */
    private static function needReNotify(string $state): bool
    {
        $last = static::getLastNotificationTime($state);

        $minElapsedSecByState = static::getMinElapsedTimeByState($state);

        if ($minElapsedSecByState === 0) {
            return false;
        }

        return time() - $last > $minElapsedSecByState;
    }

    /**
     * @return string|null
     */
    protected static function getLastState(): ?string
    {
        $fileName = static::LAST_STATE_FILENAME;
        if (!file_exists($fileName)) {
            return null;
        }

        return (string) file_get_contents($fileName);
    }

    /**
     * @param string $state
     * @return int
     * @throws Exception
     */
    protected static function getLastNotificationTime(string $state): int
    {
        if (!in_array($state, static::VALID_STATES, true)) {
            throw new Exception('Invalid state: ' . $state);
        }
        $fileName = static::LAST_NOTIFY_PREFIX . $state;

        // Means not notified yet.
        if (!file_exists($fileName)) {
            return 0;
        }

        return (int) file_get_contents($fileName);
    }

    /**
     * @throws Exception
     */
    protected static function getMinElapsedTimeByState(string $state): int
    {
        return match ($state) {
            static::STATE_SUCCESS => static::$successMinElapsedTime,
            static::STATE_ERROR => static::$errorMinElapsedTime,
            default => throw new Exception('Invalid state: ' . $state),
        };
    }
}
