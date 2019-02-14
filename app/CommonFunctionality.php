<?php

declare(strict_types=1);

trait CommonFunctionality {
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    private function createLockFile(): self
    {
        $lockFile = fopen('commandReaderVentilator.lock', 'wb+');
        if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
            $this->logger->notice('Could not adquire lock, creating notification and dying');
            $this->createFailedLockfileCreationMessage();
            die();
        }

        return $this;
    }

    private function createFailedLockfileCreationMessage(): self
    {
        $message = new Message();
        $message->setTopicName('notifications/telegram');
        $message->setPayload('[RPi3] Could not adquire lock on file ' . __FILE__ . ':' . __LINE__);

        $publish = new Publish();
        $publish->setMessage($message);

        $client->sendData($publish);

        return $this;
    }
}
