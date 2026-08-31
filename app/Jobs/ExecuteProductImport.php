<?php

namespace App\Jobs;

use App\Enums\ProductImportStatus;
use App\Jobs\Concerns\HandlesAiGenerationFailure;
use App\Models\ProductImport;
use App\Services\Products\Import\ProductImportMapping;
use App\Services\Products\Import\ProductImportPreviewBuilder;
use App\Services\Products\Import\ProductImportWriter;
use App\Services\Products\Import\SpreadsheetParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Telescope\Telescope;
use Throwable;

/**
 * Re-derives the preview from the (possibly admin-edited) mapping and the
 * original spreadsheet — never from a cached snapshot — then writes it to
 * the catalog. Dispatched only after an admin has reviewed and confirmed
 * the mapping produced by MapProductImportColumns.
 */
class ExecuteProductImport implements ShouldQueue
{
    use HandlesAiGenerationFailure, Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public readonly ProductImport $import) {}

    public function handle(
        SpreadsheetParser $spreadsheetParser,
        ProductImportPreviewBuilder $previewBuilder,
        ProductImportWriter $writer,
    ): void {
        // A large import can run thousands of queries in this one job;
        // Telescope buffers every one of them in memory (it's configured to
        // record everything locally) and will exhaust the worker's memory
        // limit long before the import itself would run out of anything.
        // withoutRecording (not stopRecording) restores the prior state
        // afterwards, so it doesn't silently disable Telescope for whatever
        // the same queue worker process picks up next.
        Telescope::withoutRecording(function () use ($spreadsheetParser, $previewBuilder, $writer): void {
            $this->import->update(['status' => ProductImportStatus::Importing]);

            if (! is_array($this->import->mapping)) {
                $this->import->update([
                    'status' => ProductImportStatus::Failed,
                    'ai_error' => 'Esta importação ainda não tem um mapeamento de colunas definido.',
                ]);

                return;
            }

            $mapping = ProductImportMapping::fromArray($this->import->mapping);
            $spreadsheet = $spreadsheetParser->parse($this->import->spreadsheet_path, 'local');
            $preview = $previewBuilder->build($this->import->company, $spreadsheet, $mapping);
            $result = $writer->write($this->import->company, $preview);

            $this->import->update([
                'status' => ProductImportStatus::Completed,
                'result' => $result->toArray(),
            ]);
        });
    }

    public function failed(?Throwable $exception): void
    {
        $this->markModelFailed($this->import, ProductImportStatus::Failed, $exception);
    }
}
