<?php

use App\Models\Lead;
use App\Models\User;

test('admin can view, edit and delete leads', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();

    expect($admin->can('viewAny', Lead::class))->toBeTrue()
        ->and($admin->can('view', $lead))->toBeTrue()
        ->and($admin->can('update', $lead))->toBeTrue()
        ->and($admin->can('delete', $lead))->toBeTrue()
        ->and($admin->can('deleteAny', Lead::class))->toBeTrue();
});

test('editor cannot view, edit or delete leads', function () {
    $editor = User::factory()->create();
    $lead = Lead::factory()->create();

    expect($editor->can('viewAny', Lead::class))->toBeFalse()
        ->and($editor->can('view', $lead))->toBeFalse()
        ->and($editor->can('update', $lead))->toBeFalse()
        ->and($editor->can('delete', $lead))->toBeFalse()
        ->and($editor->can('deleteAny', Lead::class))->toBeFalse();

    $this->actingAs($editor);

    $this->get("/admin/leads/{$lead->id}/edit")->assertForbidden();
});

test('nobody can create leads, including admins', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Lead::class))->toBeFalse();

    $this->actingAs($admin);

    $this->get('/admin/leads/create')->assertNotFound();
});
