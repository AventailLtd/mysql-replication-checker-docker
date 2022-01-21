<?php

declare(strict_types=1);
declare(ticks = 1);

use Checker\Checker;
use State\State;
use Slack\SlackMessage;

include 'vendor/autoload.php';

pcntl_signal(SIGTERM, 'signalHandler');
pcntl_signal(SIGINT, 'signalHandler');

function signalHandler($signal) {
    switch($signal) {
        case SIGTERM:
        case SIGKILL:
        case SIGINT:
            exit;
    }
}

$mysqlHost = getenv('MYSQL_HOST');
$mysqlPort = getenv('MYSQL_PORT');
$mysqlUser = getenv('MYSQL_USER');
$mysqlDatabase = getenv('MYSQL_DATABASE');
$mysqlPassword = getenv('MYSQL_PASSWORD');
$slackUrl = getenv('SLACK_URL');
$successMinElapsedTime = (int) getenv('SUCCESS_MIN_ELAPSED_TIME');
$errorMinElapsedTime = (int) getenv('ERROR_MIN_ELAPSED_TIME');

if ($mysqlHost === false || $mysqlUser === false || $mysqlDatabase === false || $mysqlPassword === false) {
    throw new Exception('MySQL config missing.');
}

State::$successMinElapsedTime = $successMinElapsedTime;
State::$errorMinElapsedTime = $errorMinElapsedTime;

while (1) {
    $pdo = new PDO(
        'mysql:host=' . $mysqlHost . ';port=' . ($mysqlPort ?: '3306') . ';' . 'dbname=' . $mysqlDatabase . ';',
        $mysqlUser,
        $mysqlPassword,
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $checker = new Checker($pdo);
    $checker->run();
    $errors = $checker->getErrors();

    $slackMessage = new SlackMessage($slackUrl);

    if (count($errors) > 0) {
        if (State::needNotify(State::STATE_ERROR)) {
            $slackMessage->setTitle('MySQL replication error');
            $slackMessage->setText(implode("\n", $errors));
            $slackMessage->setColor(SlackMessage::COLOR_RED);
            $slackMessage->send();

            State::saveNotificationTime(State::STATE_ERROR);
        }

        State::save(State::STATE_ERROR);

        echo implode('; ', $errors) . "\n";
    } else {
        if (State::needNotify(State::STATE_SUCCESS)) {
            $slackMessage->setTitle('MySQL replication working');
            $slackMessage->setColor(SlackMessage::COLOR_GREEN);
            $slackMessage->send();

            State::saveNotificationTime(State::STATE_SUCCESS);
        }

        State::save(State::STATE_SUCCESS);

        echo "MySQL replication working.\n";
    }

    sleep(60); // 1 min
}
