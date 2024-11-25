<?php

use App\Services\Parser\CsvParser;
use Illuminate\Support\Facades\Storage;

it('correctly parses a csv file with headers', function () {
    Storage::fake('local');

    Storage::put('test.csv', file_get_contents(__DIR__.'/../../files/test.csv'));

    expect((new CsvParser('test.csv'))->toCollection())
        ->toBeIterable()
        ->toHaveCount(5);
});
