<?php

declare(strict_types=1);

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\Subscribe;
use unreal4u\MQTT\Protocol\Subscribe\Topic;

// TODO implement this

/*
function writeStateFile(\DateTimeImmutable $date, string $command, Logger $logger): bool
{
    $logger->debug('Writing statefile', ['date' => $date->format('c'), 'command' => $command]);
    file_put_contents('statefile.json', json_encode(['commandDate' => $date, 'command' => $command]));
    return true;
}

function getStateFile(Logger $logger): array
{
    $logger->debug('Getting statefile');
    $stateFile = json_decode(file_get_contents('statefile.json'), true);
    return [
        'commandDate' => new \DateTimeImmutable(
            $stateFile['commandDate']['date'],
            new \DateTimeZone($stateFile['commandDate']['timezone'])
        ),
        'command' => $stateFile['command'],
    ];
}

function forcedLightShutdown(Logger $logger, int $minutes = 5): bool
{
    $stateFile = getStateFile($logger);
    if ($stateFile['command'] === 'on') {
        $logger->debug('Lights on, checking whether it is time to turn it off');
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        /** @var \DateTimeImmutable $optionalFutureDate */
/*
        $optionalFutureDate = $stateFile['commandDate']->add(new \DateInterval('PT' . $minutes . 'M'));
        $logger->info('Dates', [
            'now' => $now->format('YmdHis'),
            'optionalFuture' => $optionalFutureDate->format('YmdHis'),
            'diff' => $optionalFutureDate > $now
        ]);
        if ($now > $optionalFutureDate) {
            $logger->debug('Light has been on for more than ' . $minutes . ' minutes, turning it off');
            return true;
        }
        $logger->debug('Not time yet to turn light off', [
            'turnedOn' => $stateFile['commandDate']->format('Y-m-d H:i'),
            'turnOffTime' => $optionalFutureDate->format('Y-m-d H:i'),
        ]);
    }

    return false;
}

chdir(__DIR__ . '/../');
include 'vendor/autoload.php';

// Which pin controls this relay
const RELAY_PIN = 27;

// Generate an unique 6 character string id for this run (doesn't need to be cryptographically secure)
$uniqId = substr(uniqid('fbr' . mt_rand(0, 255), true), -8);

$logger = new Logger('main-' . $uniqId);
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new RotatingFileHandler('logs/ControllerLightsBaseroom.log', 14, Logger::DEBUG));
$logger->info('Program startup', ['uniqueId' => $uniqId]);

$connectionParameters = new Parameters('lbr-' . $uniqId, '192.168.1.1');
$connectionParameters->setKeepAlivePeriod(30);
$connectionParameters->setUsername('homeassistant');
$connectionParameters->setPassword('QWmin129');
$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

try {
    $client = new Client();
    $client->sendData($connect);
} catch (\Exception $e) {
    printf($e->getMessage());
    die();
}
$logger->info('Client connected, continuing...');

if (!file_exists('statefile.json')) {
    writeStateFile(new \DateTimeImmutable('now', new \DateTimeZone('UTC')), 'stop', $logger);
}

if (forcedLightShutdown($logger, 5) === true) {
    $logger->notice('Forcing light off');
    $message = new Message();
    $message->setTopicName('commands/light/kelder');
    $message->setQoSLevel(1);
    $message->setPayload(json_encode([
        'commandDate' => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        'command' => 'off'
    ]));
    $publish = new Publish();
    $publish->setMessage($message);
    $client->sendData($publish);
}

$lockFile = fopen('commandReaderLight.lock', 'wb+');
if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
    $logger->notice('Could not adquire lock, creating notification and dying');
    $message = new Message();
    $message->setTopicName('notifications/telegram');
    $message->setPayload('[RPi3] Could not adquire lock on file ' . __FILE__ . ':' . __LINE__);

    $publish = new Publish();
    $publish->setMessage($message);

    $client->sendData($publish);
    die();
}

$subscribe = new Subscribe();
$subscribe->addTopics(new Topic('commands/light/kelder'));
$logger->info('Everything ready, subscribing to topic and waiting...');

foreach ($subscribe->loop($client) as $message) {
    $decodedObject = json_decode($message->getPayload(), true);
    $commandDate = new \DateTimeImmutable(
        $decodedObject['commandDate']['date'],
        new \DateTimezone($decodedObject['commandDate']['timezone'])
    );
    $command = $decodedObject['command'];

    $stateFile = getStateFile($logger);
    $stateFileDate = $stateFile['commandDate'];
    $logger->debug('Reading from statefile', [
        'date' => $stateFileDate->format('c'),
        'command' => $stateFile['command']
    ]);

    if ($stateFile['command'] !== $command) {
        $logger->info('Got change in command', [
            'stateFileCommand' => $stateFile['command'],
            'mqttCommand' => $command,
        ]);
        writeStateFile($commandDate, $command, $logger);
        $gpio = new GPIO();
        $pin = $gpio->getOutputPin(RELAY_PIN);

        if ($command === 'on') {
            $pin->setValue(PinInterface::VALUE_LOW);
        } else {
            $pin->setValue(PinInterface::VALUE_HIGH);
        }

        $logger->notice('Turn light ' . $command);
        $message = new Message();
        $message->setTopicName('status/light/kelder');
        $message->setRetainFlag(true);
        $message->setQoSLevel(1);
        $message->setPayload(json_encode(['date' => $commandDate, 'status' => ($command === 'on') ? 'on' : 'off']));

        $publish = new Publish();
        $publish->setMessage($message);
        $client->sendData($publish);
    } else {
        $logger->warning('Statefile and mqtt command are the same!');
    }
}
*/