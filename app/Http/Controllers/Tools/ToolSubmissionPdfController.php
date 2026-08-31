<?php

namespace App\Http\Controllers\Tools;

use App\Enums\ToolSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\ToolSubmission;
use App\Services\Tools\ToolDefinition;
use App\Services\Tools\ToolResultPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ToolSubmissionPdfController extends Controller
{
    public function __invoke(ToolDefinition $tool, ToolSubmission $submission, ToolResultPresenter $presenter): Response
    {
        abort_unless($submission->tool_slug === $tool->slug, 404);
        abort_unless($submission->status === ToolSubmissionStatus::Completed, 404);

        $pdf = Pdf::loadView('pdf.tools.result', [
            'tool' => $tool,
            'submission' => $submission,
            'rows' => $presenter->rows($tool, $submission->result ?? []),
        ]);

        return $pdf->download("resultado-{$tool->slug}.pdf");
    }
}
