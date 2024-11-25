<?php

use App\Console\Commands\Parser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('correctly parses a csv file with headers', function () {
    Storage::fake('local');

    Storage::put('test.csv', file_get_contents(__DIR__.'/../../files/test.csv'));

    artisan(Parser::class, [
        '--file' => 'test.csv',
        '--unique-combinations' => 'unique-combinations.csv',
    ])
        ->assertExitCode(Command::SUCCESS);

    Storage::assertExists('unique-combinations.csv');
});

it('correctly parses a tsv file with headers', function () {
    Storage::fake('local');

    Storage::put('test.tsv', file_get_contents(__DIR__.'/../../files/test.tsv'));

    artisan(Parser::class, [
        '--file' => 'test.tsv',
        '--unique-combinations' => 'unique-combinations.csv',
    ])
        ->assertExitCode(Command::SUCCESS);

    Storage::assertExists('unique-combinations.csv');
});

it('fails when the file extension is not supported', function () {
    Storage::fake('local');

    Storage::put('test.json', file_get_contents(__DIR__.'/../../files/test.json'));

    artisan(Parser::class, [
        '--file' => 'test.json',
        '--unique-combinations' => 'unique-combinations.csv',
    ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('The file extension is not supported.');
});
