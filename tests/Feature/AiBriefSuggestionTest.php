<?php

use App\Models\AiBriefSuggestion;

test('remember stores a new value for a field', function () {
    AiBriefSuggestion::remember('target_audience', 'Médico');

    expect(AiBriefSuggestion::query()->forField('target_audience')->pluck('value')->all())
        ->toBe(['Médico']);
});

test('remember does not duplicate an existing value for the same field', function () {
    AiBriefSuggestion::remember('target_audience', 'Médico');
    AiBriefSuggestion::remember('target_audience', 'Médico');

    expect(AiBriefSuggestion::query()->forField('target_audience')->count())->toBe(1);
});

test('remember ignores blank values', function () {
    AiBriefSuggestion::remember('target_audience', '');
    AiBriefSuggestion::remember('target_audience', null);
    AiBriefSuggestion::remember('target_audience', '   ');

    expect(AiBriefSuggestion::query()->forField('target_audience')->count())->toBe(0);
});

test('forField scopes suggestions to a single field', function () {
    AiBriefSuggestion::remember('target_audience', 'Médico');
    AiBriefSuggestion::remember('search_intent', 'Quanto custa um sistema ERP');

    expect(AiBriefSuggestion::query()->forField('target_audience')->pluck('value')->all())
        ->toBe(['Médico']);
});
