<?php

declare(strict_types=1);

use Saloon\Managers\RequestManager;
use Psr\Http\Message\RequestInterface;
use Saloon\Tests\Fixtures\Requests\HasXMLRequest;

test('with the hasXMLBody trait, you can pass in a string body response', function () {
    $request = new HasXMLRequest;

    $requestManager = new RequestManager($request);
    $requestManager->hydrate();

    expect($requestManager->getHeaders())->toHaveKey('Accept', 'application/xml');
    expect($requestManager->getHeaders())->toHaveKey('Content-Type', 'application/xml');

    $request->addHandler('hasBodyHandler', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($request->getBody()->getContents())->toEqual('<xml></xml>');

            return $handler($request, $options);
        };
    });
});
