<?php

require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\CallbackMessageProvider;
use Swarrot\Consumer;
use Swarrot\Processor\Callback\CallbackProcessor;

class WeatherIntervalMessageProvider
{
    private $date;
    private $interval;

    public function __construct(DateInterval $interval)
    {
        $this->interval = $interval;
    }

    public function __invoke()
    {
        if (null === $this->date || (new DateTime())->sub($this->interval) > $this->date) {
            $this->date = new DateTime();

            return new Message(
                file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20item.condition%20from%20weather.forecast%20where%20woeid%20%3D%20615702&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys')
            );
        }

        return null;
    }
}

class WeatherMessageProcessor
{
    public function process(Message $message, array $options)
    {
        $weather = json_decode($message->getBody(), true);
        if (empty($weather['query']['results']['channel']['item']['condition']['text'])) {
            echo 'Invalid Input';

            return;
        }

        printf("The weather is %s in Paris\n", $weather['query']['results']['channel']['item']['condition']['text']);
    }
}

$intervalMessageProvider = new WeatherIntervalMessageProvider(new DateInterval('PT5S'));
$weatherMessageProcessor = new WeatherMessageProcessor();
$messageProvider = new CallbackMessageProvider($intervalMessageProvider);
$processor = new CallbackProcessor([$weatherMessageProcessor, 'process']);

$consumer = new Consumer($messageProvider, $processor);

$consumer->consume([]);
