<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalConfirmModal extends Component
{
    public ?string $modelId = null;

    public string $title = '';

    public string $description = '';

    public string $eventName = '';

    public string $actionText = 'Confirm';

    public string $actionVariant = 'primary';

    #[On('confirm-action')]
    public function confirm(
        string $id,
        string $eventName,
        string $title,
        string $description,
        string $actionText,
        string $actionVariant = 'primary',
    ): void {
        $this->modelId = $id;
        $this->eventName = $eventName;
        $this->title = $title;
        $this->description = $description;
        $this->actionText = $actionText;
        $this->actionVariant = $actionVariant;
        $this->modal('global-confirm-modal')->show();
    }

    public function resetState(): void
    {
        $this->reset([
            'modelId',
            'title',
            'description',
            'eventName',
            'actionText',
            'actionVariant',
        ]);
    }

    public function render(): View
    {
        return view('livewire.global-confirm-modal');
    }
}
