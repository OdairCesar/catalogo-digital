<?php

use App\Filament\Resources\ToolSubmissions\Pages\ListToolSubmissions;
use App\Models\ToolSubmission;
use App\Models\User;
use Livewire\Livewire;

test('admin can view and edit tool submissions', function () {
    $admin = User::factory()->admin()->create();
    $submission = ToolSubmission::factory()->create();

    $this->actingAs($admin);

    $this->get('/admin/tool-submissions')->assertOk();
    $this->get("/admin/tool-submissions/{$submission->id}/edit")->assertOk();
});

test('admin sees the conversation as chat bubbles and the result rendered visually instead of as raw json', function () {
    $admin = User::factory()->admin()->create();
    $submission = ToolSubmission::factory()->generatingReport()->completed()->create();

    $this->actingAs($admin);

    $response = $this->get("/admin/tool-submissions/{$submission->id}/edit")
        ->assertOk()
        ->assertSee('Qual tipo de negócio você quer criar?')
        ->assertSee($submission->result['executive_summary'])
        ->assertSee($submission->result['delivery_stages'][0]['name'])
        ->assertSee($submission->result['delivery_stages'][0]['timeframe'])
        ->assertDontSee(json_encode($submission->result, JSON_UNESCAPED_UNICODE));

    // Filament's ->html() entries run through Symfony's HtmlSanitizer, which strips
    // <style> tags entirely — only inline style="" attributes survive. Assert the
    // actual rendered markup keeps its inline styling, not just the plain text.
    $response->assertSee('style="font-weight: 600; color: #0f172a; margin-bottom: 4px;"', false)
        ->assertSee('background: #2563eb; color: #ffffff;', false);
});

test('editor cannot view or edit tool submissions', function () {
    $editor = User::factory()->create();
    $submission = ToolSubmission::factory()->create();

    $this->actingAs($editor);

    $this->get('/admin/tool-submissions')->assertForbidden();
    $this->get("/admin/tool-submissions/{$submission->id}/edit")->assertForbidden();
});

test('nobody can create tool submissions through the admin, including admins', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', ToolSubmission::class))->toBeFalse();

    $this->actingAs($admin);

    $this->get('/admin/tool-submissions/create')->assertNotFound();
});

test('tool submissions can be bulk marked as read and unread', function () {
    $this->actingAs(User::factory()->admin()->create());

    $submissions = ToolSubmission::factory()->count(2)->create(['read_at' => null]);

    Livewire::test(ListToolSubmissions::class)
        ->callTableBulkAction('markAsRead', $submissions);

    expect($submissions->fresh()->pluck('read_at')->filter()->count())->toBe(2);

    Livewire::test(ListToolSubmissions::class)
        ->callTableBulkAction('markAsUnread', $submissions);

    expect($submissions->fresh()->pluck('read_at')->filter()->count())->toBe(0);
});
