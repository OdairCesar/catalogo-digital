<?php

namespace App\Livewire;

use App\Enums\ToolSubmissionStatus;
use App\Exceptions\AiGenerationException;
use App\Jobs\GenerateToolReport;
use App\Models\ToolSubmission;
use App\Services\Tools\ToolChatService;
use App\Services\Tools\ToolDefinition;
use App\Services\Tools\ToolRegistry;
use App\Support\ClientIp;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ToolChat extends Component
{
    #[Locked]
    public string $slug;

    public string $step = 'chat';

    public string $stage = 'name';

    public ?ToolSubmission $submission = null;

    /** @var list<array{role: string, content: string}> */
    public array $chatLog = [];

    public string $input = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->pushAssistant('Olá! Para começar, qual é o seu nome?');
    }

    public function submitInput(ToolChatService $chatService): void
    {
        match ($this->stage) {
            'name' => $this->handleName(),
            'email' => $this->handleEmail(),
            'phone' => $this->handlePhone($chatService),
            'ai' => $this->handleAiTurn($chatService),
            default => null,
        };
    }

    public function pollReportStatus(): void
    {
        if (! $this->submission instanceof ToolSubmission) {
            return;
        }

        $this->submission->refresh();

        if ($this->submission->status === ToolSubmissionStatus::Completed) {
            $this->step = 'result';
        } elseif ($this->submission->status === ToolSubmissionStatus::Failed) {
            $this->step = 'failed';
        }
    }

    private function handleName(): void
    {
        $this->name = $this->validateInput(['required', 'string', 'max:255'], 'nome');
        $this->pushUser($this->name);
        $this->input = '';
        $this->stage = 'email';
        $this->pushAssistant('Qual é o seu e-mail?');
    }

    private function handleEmail(): void
    {
        $this->email = $this->validateInput(['required', 'email', 'max:255'], 'e-mail');
        $this->pushUser($this->email);
        $this->input = '';
        $this->stage = 'phone';
        $this->pushAssistant('Qual é o seu telefone?');
    }

    private function handlePhone(ToolChatService $chatService): void
    {
        if (! $this->submission instanceof ToolSubmission) {
            $phone = $this->validateInput(['required', 'string', 'max:30'], 'telefone');

            $rateLimitKey = 'tool-submission:'.ClientIp::resolve(request());

            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                $this->addError('input', 'Você atingiu o limite de usos por hora. Tente novamente mais tarde.');

                return;
            }

            RateLimiter::hit($rateLimitKey, 3600);

            $this->phone = $phone;
            $this->pushUser($this->phone);
            $this->input = '';

            $this->submission = ToolSubmission::query()->create([
                'tool_slug' => $this->slug,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'messages' => [],
                'status' => ToolSubmissionStatus::InProgress,
                'source_url' => request()->headers->get('referer'),
            ]);
        }

        try {
            $chatService->startInterview($this->submission, $this->tool());
            $this->stage = 'ai';
        } catch (AiGenerationException) {
            $this->pushAssistant('Não foi possível iniciar a conversa agora. Tente novamente em instantes.');
        }
    }

    private function handleAiTurn(ToolChatService $chatService): void
    {
        if (! $this->submission instanceof ToolSubmission || $this->submission->status !== ToolSubmissionStatus::InProgress) {
            return;
        }

        $message = $this->validateInput(['required', 'string', 'max:2000'], 'resposta');
        $this->input = '';

        try {
            $chatService->sendTurn($this->submission, $this->tool(), $message);

            if ($this->submission->status === ToolSubmissionStatus::GeneratingReport) {
                GenerateToolReport::dispatch($this->submission);
                $this->step = 'generating';
            }
        } catch (AiGenerationException) {
            $this->addError('input', 'Não foi possível processar sua resposta agora. Tente novamente.');
        }
    }

    private function tool(): ToolDefinition
    {
        return app(ToolRegistry::class)->findOrFail($this->slug);
    }

    /**
     * @param  list<string>  $rules
     */
    private function validateInput(array $rules, string $attribute): string
    {
        $this->validate(['input' => $rules], attributes: ['input' => $attribute]);

        return trim($this->input);
    }

    private function pushAssistant(string $content): void
    {
        $this->chatLog[] = ['role' => 'assistant', 'content' => $content];
    }

    private function pushUser(string $content): void
    {
        $this->chatLog[] = ['role' => 'user', 'content' => $content];
    }

    public function render(): View
    {
        $tool = $this->tool();

        return view('livewire.tool-chat', [
            'tool' => $tool,
            'pdfUrl' => $this->step === 'result' && $this->submission instanceof ToolSubmission
                ? URL::temporarySignedRoute('tools.submission.pdf', now()->addDays(7), ['tool' => $tool->slug, 'submission' => $this->submission->id])
                : null,
        ]);
    }
}
