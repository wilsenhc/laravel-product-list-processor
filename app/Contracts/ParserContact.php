<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ParserContact
{
    public function toCollection(): Collection;
}
