<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\NewLeadReceived;
use App\Models\Company;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'whatsappLink' => Company::current()?->whatsappLink('Oi Cae! Vim pelo site e quero falar com você 💜'),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $lead = Lead::query()->create([
            'source' => Lead::SOURCE_CONTACT,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'payload' => [
                'company' => $request->validated('company'),
            ],
            'source_url' => $request->headers->get('referer'),
        ]);

        try {
            Mail::to(config('services.lead_notifications.email'))->send(new NewLeadReceived($lead));
        } catch (Throwable $e) {
            Log::error('Falha ao enviar e-mail de notificação de lead.', [
                'lead_id' => $lead->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('contact.show')
            ->with('status', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
    }
}
