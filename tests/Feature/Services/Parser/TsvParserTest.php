<?php

use App\Services\Parser\TsvParser;
use Illuminate\Support\Facades\Storage;

it('correctly parses a csv file with headers', function () {
    Storage::fake('local');

    Storage::put('test.tsv', file_get_contents(__DIR__.'/files/test.tsv'));

    expect((new TsvParser('test.tsv'))->toCollection())
        ->toBeIterable()
        ->toHaveCount(5);
});
