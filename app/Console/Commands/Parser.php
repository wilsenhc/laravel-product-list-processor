<?php

namespace App\Console\Commands;

use App\Services\Parser\CsvParser;
use App\Services\Parser\TsvParser;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:parser',
    description: 'Parses the list of products and gives total count of each unique type of product.',
)]
class Parser extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parser {--file= : The file to parse} {--unique-combinations= : The name of the output file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses the list of products and gives total count of each unique type of product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->option('file');
        $uniqueCombinations = $this->option('unique-combinations');

        if (is_null($filename) || is_null($uniqueCombinations)) {
            $this->error(__('You must provide both the --file and --unique-combinations arguments.'));

            return static::FAILURE;
        }

        $fileExtension = File::extension(Storage::drive('local')->path($filename));

        $products = match ($fileExtension) {
            'csv' => (new CsvParser($filename))->toCollection(),
            'tsv' => (new TsvParser($filename))->toCollection(),
            // 'json' => null,
            // 'xml' => null,
            default => null,
        };

        if ($products === null) {
            $this->error(__('The file extension is not supported.'));

            return static::FAILURE;
        }

        $countedProducts = $products->groupBy(fn ($product) => "{$product['brand_name']}_{$product['model_name']}_{$product['colour_name']}_{$product['gb_spec_name']}_{$product['network_name']}_{$product['grade_name']}_{$product['condition_name']}")
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'brand_name' => $first['brand_name'],
                    'model_name' => $first['model_name'],
                    'colour_name' => $first['colour_name'],
                    'gb_spec_name' => $first['gb_spec_name'],
                    'network_name' => $first['network_name'],
                    'grade_name' => $first['grade_name'],
                    'condition_name' => $first['condition_name'],
                    'count' => $group->count(),
                ];
            })
            ->values();

        $this->writeToCsv($countedProducts, $uniqueCombinations);

        return static::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'file' => __('What is the name of the input file?'),
            'unique-combinations' => __('Name of the output file?'),
        ];
    }

    protected function writeToCsv(Collection $products, string $filename): void
    {
        $headers = [
            'make',
            'model',
            'colour',
            'capacity',
            'network',
            'grade',
            'condition',
            'count',
        ];

        $lines = [];

        $lines[] = implode(',', $headers);

        foreach ($products as $product) {
            $lines[] = implode(',', [
                $product['brand_name'],
                $product['model_name'],
                $product['colour_name'],
                $product['gb_spec_name'],
                $product['network_name'],
                $product['grade_name'],
                $product['condition_name'],
                $product['count'],
            ]);
        }

        Storage::drive('local')->put($filename, implode("\n", $lines));
    }
}
