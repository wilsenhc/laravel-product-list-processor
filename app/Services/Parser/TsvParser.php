<?php

namespace App\Services\Parser;

use App\Contracts\ParserContact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TsvParser implements ParserContact
{
    public const SEPARATOR = '/[\t]+/';

    public function __construct(public string $filename) {}

    public function toCollection(): Collection
    {
        $headersLine = File::lines(Storage::drive('local')->path($this->filename))->first();

        $headers = Str::of($headersLine)->split(static::SEPARATOR)->map(fn ($header) => Str::replace('"', '', $header));

        return File::lines(Storage::drive('local')->path($this->filename))
            ->map(function (string $line, int $index) use ($headers) {
                if ($index === 0) {
                    return null;
                }

                if ($line === '') {
                    return null;
                }

                $values = Str::of($line)->split(static::SEPARATOR);

                return $headers->mapWithKeys(fn ($header, $key) => [$header => Str::replace('"', '', $values[$key])])->toArray();
            })
            ->filter()
            ->collect();
    }
}
