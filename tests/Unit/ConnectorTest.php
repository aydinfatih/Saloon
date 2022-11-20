<?php

declare(strict_types=1);

use Saloon\Http\Request;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Responses\Response;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\RequestSelectionConnector;

test('a connector class can be instantiated using the make method', function () {
    $connectorA = TestConnector::make();

    expect($connectorA)->toBeInstanceOf(TestConnector::class);

    $connectorB = RequestSelectionConnector::make('yee-haw-1-2-3');

    expect($connectorB)->toBeInstanceOf(RequestSelectionConnector::class);
    expect($connectorB)->apiKey->toEqual('yee-haw-1-2-3');
});

test('you can prepare a request through the connector', function () {
    $connector = new TestConnector();
    $connector->unique = true;

    $request = $connector->request(new UserRequest);

    expect($request)->toBeInstanceOf(Request::class);
    expect($request->connector())->toEqual($connector);
});

test('the same connector instance is kept if you instantiate it on the request', function () {
    $request = new UserRequest;
    $connector = $request->connector();

    expect($connector)->toBe($request->connector());
});

test('you can send a request through the connector', function () {
    $mockClient = new MockClient([
        new MockResponse(200, ['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new TestConnector();
    $response = $connector->send(new UserRequest, $mockClient);

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->json())->toEqual(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']);
});

test('you can send an asynchronous request through the connector', function () {
    $mockClient = new MockClient([
        new MockResponse(200, ['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new TestConnector();
    $promise = $connector->sendAsync(new UserRequest, $mockClient);

    expect($promise)->toBeInstanceOf(Promise::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->json())->toEqual(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']);
});
