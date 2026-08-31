<?php

namespace App\Http\Controllers\States;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Services\Landing\StateViewModelFactory;
use Illuminate\Contracts\View\View;

class StateShowController extends Controller
{
    public function __construct(private readonly StateViewModelFactory $viewModelFactory) {}

    public function show(State $state): View
    {
        return view('pages.states.show', [
            'vm' => $this->viewModelFactory->makeForState($state),
        ]);
    }
}
