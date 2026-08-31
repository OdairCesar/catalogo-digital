<?php

use App\Mail\NewLeadReceived;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;

test('submitting the contact form creates a lead and sends a notification email', function () {
    Mail::fake();

    $response = $this->post(route('contact.store'), [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'phone' => '11999999999',
        'company' => 'Acme Ltda',
        'message' => 'Gostaria de um orçamento.',
    ]);

    $response->assertRedirect(route('contact.show'));

    $lead = Lead::sole();

    expect($lead->source)->toBe(Lead::SOURCE_CONTACT)
        ->and($lead->name)->toBe('Maria Silva')
        ->and($lead->email)->toBe('maria@example.com')
        ->and($lead->payload)->toBe(['company' => 'Acme Ltda']);

    Mail::assertSent(NewLeadReceived::class, fn (NewLeadReceived $mail): bool => $mail->lead->is($lead));
});

test('the contact form requires name, email and phone', function () {
    $this->post(route('contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'phone']);

    expect(Lead::count())->toBe(0);
});

test('the contact form succeeds without a message', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'phone' => '11999999999',
    ]);

    $response->assertRedirect(route('contact.show'));

    expect(Lead::count())->toBe(1);
});

test('the contact form still succeeds when the notification email fails to send', function () {
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP down'));

    $response = $this->post(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '11988887777',
        'message' => 'Preciso de um orçamento.',
    ]);

    $response->assertRedirect(route('contact.show'));
    $response->assertSessionHas('status');

    expect(Lead::count())->toBe(1);
});

test('the contact form rejects submissions with the honeypot field filled', function () {
    $this->post(route('contact.store'), [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'phone' => '11999999999',
        'message' => 'spam',
        'website' => 'http://spam.example.com',
    ])->assertSessionHasErrors('website');

    expect(Lead::count())->toBe(0);
});

test('the contact form throttles after five submissions from the same ip', function () {
    Mail::fake();

    // Fixed, unique client IP via the Envoy header so this test's throttle
    // bucket never collides with the 127.0.0.1 used by the other tests.
    $this->withHeaders(['X-Envoy-External-Address' => '198.51.100.50']);

    $payload = [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'phone' => '11999999999',
    ];

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post(route('contact.store'), $payload)->assertRedirect(route('contact.show'));
    }

    $this->post(route('contact.store'), $payload)->assertStatus(429);

    expect(Lead::count())->toBe(5);
});

test('rotating the forwarded-for header does not bypass the contact throttle', function () {
    Mail::fake();

    $payload = [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'phone' => '11999999999',
    ];

    // The real client IP (Envoy header) is constant while the spoofable
    // X-Forwarded-For is rotated on every request — the limit must still hit.
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->withHeaders([
            'X-Envoy-External-Address' => '198.51.100.51',
            'X-Forwarded-For' => "203.0.113.{$attempt}",
        ])->post(route('contact.store'), $payload)->assertRedirect(route('contact.show'));
    }

    $this->withHeaders([
        'X-Envoy-External-Address' => '198.51.100.51',
        'X-Forwarded-For' => '203.0.113.250',
    ])->post(route('contact.store'), $payload)->assertStatus(429);
});
