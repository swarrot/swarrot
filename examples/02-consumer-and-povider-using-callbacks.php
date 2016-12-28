<?php

require_once __DIR__.'/../vendor/autoload.php';

use Swarrot\Consumer;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\CallbackMessageProvider;
use Swarrot\Processor\Callback\CallbackProcessor;

$i = 0;
$messageProvider = new CallbackMessageProvider(function () use (&$i) {
    $isPair = $i++ % 2 == 0;

    return $isPair ?
        new Message(
            file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20item.condition%20from%20weather.forecast%20where%20woeid%20%3D%20615702&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys')
        ) :
        null;
});

$processor = new CallbackProcessor(function (Message $message, array $options) {
    $weather = json_decode($message->getBody(), true);
    if (empty($weather['query']['results']['channel']['item']['condition']['text'])) {
        echo 'Invalid Input';

        return;
    }

    printf("The weather is %s in Paris\n", $weather['query']['results']['channel']['item']['condition']['text']);
});

$consumer = new Consumer($messageProvider, $processor);

$consumer->consume([]);
