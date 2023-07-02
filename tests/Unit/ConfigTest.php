<?php

declare(strict_types=1);

use Saloon\Helpers\Config;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Responses\Response;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Tests\Fixtures\Senders\ArraySender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

afterEach(function () {
    Config::resetMiddleware();
    Config::resetDefaultSender();
});

test('the config can specify global middleware', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Jake Owen - Beachin']),
    ]);

    $count = 0;

    Config::middleware()->onRequest(function (PendingRequest $pendingRequest) use (&$count) {
        $count++;
    });

    Config::middleware()->onResponse(function (Response $response) use (&$count) {
        $count++;
    });

    TestConnector::make()->send(new UserRequest, $mockClient);

    expect($count)->toBe(2);
});

test('you can change the global default sender used', function () {
    Config::setDefaultSender(ArraySender::class);

    $response = TestConnector::make()->send(new UserRequest);

    expect($response->getPendingRequest()->getSender())->toBeInstanceOf(ArraySender::class);

    Config::resetDefaultSender();

    $response = TestConnector::make()->send(new UserRequest);

    expect($response->getPendingRequest()->getSender())->toBeInstanceOf(GuzzleSender::class);
});

test('you can change how the global default sender is resolved', function () {
    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    Config::resolveSenderWith(static fn () => new ArraySender);

    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(ArraySender::class);

    Config::resolveSenderWith(null);

    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);
});
