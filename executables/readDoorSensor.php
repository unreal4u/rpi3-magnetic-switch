<?php

/**
 * Copied mainly from https://github.com/PiPHP/GPIO#input-pin-interrupts with a few adjustments
 */
declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\InputPinInterface;
use PiPHP\GPIO\Pin\PinInterface;

// Create a GPIO object
$gpio = new GPIO();
$logger = new Logger('readSensor');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::CRITICAL));
$logger->pushHandler(new RotatingFileHandler('logs/readSensor.log', 14, Logger::DEBUG));

// Retrieve pin 17 and configure it as an input pin
$doorPin = $gpio->getInputPin(17);
// Also retrieve pin 27 and configure it as an output pin
$relayPin = $gpio->getOutputPin(27);

// Configure interrupts for both rising and falling edges
$doorPin->setEdge(InputPinInterface::EDGE_BOTH);

// Create an interrupt watcher
$interruptWatcher = $gpio->createWatcher();

// Register a callback to be triggered on pin interrupts
$interruptWatcher->register($doorPin, function (InputPinInterface $doorPin, $value) use ($logger, $relayPin) {
    if ($value === 1) {
        $logger->info('Door was opened');
        $relayPin->setValue(PinInterface::VALUE_LOW);
        // TODO inform MQTT that door has been opened and light did go on
    } else {
        $logger->info('Door was closed');
        $relayPin->setValue(PinInterface::VALUE_HIGH);
        // TODO inform MQTT that door has been closed, do NOT turn light immediately off
    }

    // Returning false will make the watcher return false immediately
    return true;
});

// Watch for interrupts, timeout after 50000ms (50 seconds)
while ($interruptWatcher->watch(50000)) ;
