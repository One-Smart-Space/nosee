<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Content\ContentValidationService;
use Illuminate\Console\Command;

final class ValidateContent extends Command
{
    protected $signature = 'content:validate';

    protected $description = 'Validate file-managed website content';

    public function handle(ContentValidationService $validation): int
    {
        $result = $validation->validateAll();

        if ($result['errors'] === []) {
            $this->info("Content validation passed: {$result['files']} files validated.");

            return self::SUCCESS;
        }

        $this->error('Content validation failed.');

        foreach ($result['errors'] as $path => $messages) {
            $this->newLine();
            $this->line($path);

            foreach ($messages as $message) {
                $this->line("  - {$message}");
            }
        }

        $errorCount = array_sum(array_map(count(...), $result['errors']));

        $this->newLine();
        $this->error(
            "{$errorCount} error(s) found in ".count($result['errors']).' file(s).',
        );

        return self::FAILURE;
    }
}
