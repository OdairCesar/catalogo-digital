<?php

use App\Models\ToolSubmission;
use Illuminate\Support\Facades\URL;

function signedToolPdfUrl(string $toolSlug, int $submissionId): string
{
    return URL::temporarySignedRoute('tools.submission.pdf', now()->addDays(7), [
        'tool' => $toolSlug,
        'submission' => $submissionId,
    ]);
}

test('a completed submission can have its pdf downloaded via a signed url', function () {
    $submission = ToolSubmission::factory()->completed()->create(['tool_slug' => 'quanto-vale-minha-ideia']);

    $response = $this->get(signedToolPdfUrl('quanto-vale-minha-ideia', $submission->id));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('the pdf route rejects a submission that belongs to a different tool', function () {
    $submission = ToolSubmission::factory()->completed()->create(['tool_slug' => 'quanto-vale-minha-ideia']);

    $response = $this->get(signedToolPdfUrl('minha-ideia-e-viavel', $submission->id));

    $response->assertNotFound();
});

test('the pdf route rejects a submission that has not completed yet', function () {
    $submission = ToolSubmission::factory()->generatingReport()->create(['tool_slug' => 'quanto-vale-minha-ideia']);

    $response = $this->get(signedToolPdfUrl('quanto-vale-minha-ideia', $submission->id));

    $response->assertNotFound();
});

test('the pdf route rejects an unsigned url', function () {
    $submission = ToolSubmission::factory()->completed()->create(['tool_slug' => 'quanto-vale-minha-ideia']);

    $response = $this->get(route('tools.submission.pdf', ['tool' => 'quanto-vale-minha-ideia', 'submission' => $submission->id]));

    $response->assertForbidden();
});
