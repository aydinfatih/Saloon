<?php

declare(strict_types=1);

use Saloon\Http\Request;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Responses\Response;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;

test('assertSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent(UserRequest::class);
});

test('assertSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $originalRequest = new UserRequest();
    $originalResponse = $originalRequest->send($mockClient);

    $mockClient->assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($originalRequest);
        expect($response)->toBe($originalResponse);

        return true;
    });

    $newRequest = new ErrorRequest();
    $newResponse = $newRequest->send($mockClient);

    $mockClient->assertSent(function ($request, $response) use ($newRequest, $newResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($newRequest);
        expect($response)->toBe($newResponse);

        return true;
    });
});

test('assertSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent('saloon.dev/*');
    $mockClient->assertSent('/user');
    $mockClient->assertSent('api/user');
});

test('assertNotSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    (new ErrorRequest())->send($mockClient);

    $mockClient->assertNotSent(UserRequest::class);
});

test('assertNotSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = $originalRequest->send($mockClient);

    $mockClient->assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('assertNotSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertNotSent('google.com/*');
    $mockClient->assertNotSent('/error');
});

test('assertSentJson works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);
});

test('assertSentJson works with multiple requests in history', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(201, ['name' => 'Taylor']),
        MockResponse::make(204, ['name' => 'Marcel']),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Taylor',
    ]);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Marcel',
    ]);
});

test('assertNothingSent works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(200, ['name' => 'Sam']),
    ]);

    $mockClient->assertNothingSent();
});

test('assertSentCount works properly', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Taylor']),
        MockResponse::make(200, ['name' => 'Marcel']),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

    $mockClient->assertSentCount(3);
});

test('assertSent with a closure works with more than one request in the history', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(201, ['name' => 'Taylor']),
        MockResponse::make(204, ['name' => 'Marcel']),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Sam'] && $response->status() === 200;
    });

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Taylor'] && $response->status() === 201;
    });

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Marcel'] && $response->status() === 204;
    });
});
