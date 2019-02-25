<?php

declare(strict_types=1);

namespace unreal4u\rpiMagneticSwitch;

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\InputPin;
use PiPHP\GPIO\Pin\InputPinInterface;
use PiPHP\GPIO\Pin\OutputPin;
use PiPHP\GPIO\Pin\PinInterface;
use unreal4u\rpiCommonLibrary\Functional\Base;
use unreal4u\rpiCommonLibrary\JobContract;

class readDoorSensor extends Base {
    protected static $defaultName = 'kelder:door-sensor';

    /**
     * @var GPIO
     */
    private $gpio;

    /**
     * @var InputPin
     */
    private $doorPin;

    /**
     * @var OutputPin
     */
    private $relayPin;

    /**
     * Will be executed once before running the actual job
     *
     * @return JobContract
     */
    public function setUp(): JobContract
    {
        $this->gpio = new GPIO();
        // Retrieve pin 17 and configure it as an input pin
        $this->doorPin = $this->gpio->getInputPin(17);
        // Also retrieve pin 27 and configure it as an output pin
        $this->relayPin = $this->gpio->getOutputPin(27);

        // Configure interrupts for both rising and falling edges
        $this->doorPin->setEdge(InputPinInterface::EDGE_BOTH);

        return $this;
    }

    public function configure()
    {
        $this
            ->setDescription('Reads out the door sensor and turns light immediately on')
            ->setHelp('Reads out the door sensor and passes this information back to the MQTT broker')
        ;
    }

    /**
     * Runs the actual job that needs to be executed
     *
     * @return bool Returns true if job was successful, false otherwise
     */
    public function runJob(): bool
    {
        // Create an interrupt watcher
        $interruptWatcher = $this->gpio->createWatcher();

        // Register a callback to be triggered on pin interrupts
        $interruptWatcher->register($this->doorPin, function ($value) {
            $mqttCommunicator = $this->communicationsFactory('MQTT');

            if ($value === 1) {
                $this->logger->info('Door was opened');
                // Turn on light first ASAP, do logging afterwards
                $this->relayPin->setValue(PinInterface::VALUE_LOW);
                $mqttCommunicator->sendMessage('sensors/kelder/door', 'open');
                $mqttCommunicator->sendMessage('commands/kelder/light', 'on');
            } else {
                $this->logger->info('Door was closed');
                $this->relayPin->setValue(PinInterface::VALUE_HIGH);
                $mqttCommunicator->sendMessage('sensors/kelder/door', 'closed');
            }

            // Returning false will make the watcher return false immediately
            return true;
        });

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($interruptWatcher->watch($this->forceKillAfterSeconds()));

        return true;
    }

    /**
     * If method runJob returns false, this will return an array with errors that may have happened during execution
     *
     * @return array
     */
    public function retrieveErrors(): array
    {
        return [];
    }

    /**
     * The number of seconds after which this script should kill itself
     *
     * @return int
     */
    public function forceKillAfterSeconds(): int
    {
        return 3600;
    }

    /**
     * The loop should run after this amount of microseconds (1 second === 1000000 microseconds)
     *
     * @return int
     */
    public function executeEveryMicroseconds(): int
    {
        return 0;
    }
}