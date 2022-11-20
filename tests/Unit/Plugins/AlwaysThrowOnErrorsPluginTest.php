<?php

declare(strict_types=1);

use Saloon\Exceptions\RequestException;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

test('it always throws an error if the plugin has been added', function () {
    $this->expectException(RequestException::class);

    (new AlwaysThrowRequest())->send();
});
