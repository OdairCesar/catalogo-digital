<?php

namespace App\Jobs;

use App\Enums\ProductImportStatus;
use App\Exceptions\AiGenerationException;
use App\Jobs\Concerns\HandlesAiGenerationFailure;
use App\Models\ProductImport;
use App\Services\Ai\TextGenerator;
use App\Services\Products\Import\CatalogContextRetriever;
use App\Services\Products\Import\ColumnMappingParser;
use App\Services\Products\Import\ColumnMappingPromptBuilder;
use App\Services\Products\Import\SpreadsheetParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Parses the uploaded spreadsheet, retrieves the catalog terms closest to
 * its columns (the RAG step), and asks the AI to propose a column mapping.
 * Leaves the import in "awaiting_review" for an admin to confirm before
 * anything is written to the catalog.
 */
class MapProductImportColumns implements ShouldQueue
{
    use HandlesAiGenerationFailure, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function __construct(public readonly ProductImport $import) {}

    public function handle(
        SpreadsheetParser $spreadsheetParser,
        CatalogContextRetriever $catalogContextRetriever,
        ColumnMappingPromptBuilder $promptBuilder,
        ColumnMappingParser $mappingParser,
        TextGenerator $textGenerator,
    ): void {
        $this->import->update(['status' => ProductImportStatus::Mapping]);

        $spreadsheet = $spreadsheetParser->parse($this->import->spreadsheet_path, 'local');

        if ($spreadsheet->headers === []) {
            $this->import->update([
                'status' => ProductImportStatus::Failed,
                'ai_error' => 'Não foi possível identificar um cabeçalho na planilha enviada.',
            ]);

            return;
        }

        $catalogContext = $catalogContextRetriever->retrieve($this->import->company, $spreadsheet->headers);
        $messages = $promptBuilder->build($spreadsheet, $catalogContext);

        try {
            $result = $textGenerator->generate(
                [
                    ['role' => 'system', 'content' => $messages['system']],
                    ['role' => 'user', 'content' => $messages['user']],
                ],
                $promptBuilder->responseFormat(),
                0.2,
            );

            $mapping = $mappingParser->parse($result->content, $spreadsheet);
        } catch (AiGenerationException $exception) {
            $this->markFailed($exception);

            return;
        }

        $this->import->update([
            'mapping' => $mapping->toArray(),
            'status' => ProductImportStatus::AwaitingReview,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        $this->markModelFailed($this->import, ProductImportStatus::Failed, $exception);
    }
}
