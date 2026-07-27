<?php

test('domain layer independence')
    ->expect('Modules\Account\Domain')
    ->not->toUse('Modules\Account\Application')
    ->not->toUse('Modules\Account\Infrastructure')
    ->not->toUse('Illuminate\Support\Facades');

test('application layer independence')
    ->expect('Modules\Account\Application')
    ->not->toUse('Modules\Account\Infrastructure')
    ->not->toUse('Illuminate\Support\Facades');

test('globals')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();
