<?php

test('domain layer independence')
    ->expect('Modules\Account\Domain') // @phpstan-ignore-line
    ->not->toUse('Modules\Account\Application')
    ->not->toUse('Modules\Account\Infrastructure')
    ->not->toUse('Illuminate\Support\Facades');

test('application layer independence')
    ->expect('Modules\Account\Application') // @phpstan-ignore-line
    ->not->toUse('Modules\Account\Infrastructure')
    ->not->toUse('Illuminate\Support\Facades');

test('globals')
    ->expect(['dd', 'dump']) // @phpstan-ignore-line
    ->not->toBeUsed();
