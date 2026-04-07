<?php

namespace App\Filament\Pages;

use App\Services\FrontendApiService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageFormSubmissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Form Submissions';
    protected static ?string $title = 'Form Submissions';
    protected static string $view = 'filament.pages.manage-form-submissions';
    protected static ?int $navigationSort = 5;

    public array $submissions = [];
    public array $pagination = [];
    public int $currentPage = 1;
    public string $search = '';
    public string $formType = '';

    // Detail modal
    public bool $showDetailModal = false;
    public ?array $selectedSubmission = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(FrontendApiService::class);
        $response = $service->getFormSubmissions(
            page: $this->currentPage,
            formType: $this->formType ?: null,
            search: $this->search ?: null,
        );

        $this->submissions = $response['data'] ?? [];
        $this->pagination = [
            'current_page' => $response['current_page'] ?? 1,
            'last_page' => $response['last_page'] ?? 1,
            'total' => $response['total'] ?? 0,
            'per_page' => $response['per_page'] ?? 15,
        ];
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->loadData();
    }

    public function updatedFormType(): void
    {
        $this->currentPage = 1;
        $this->loadData();
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = $page;
        $this->loadData();
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadData();
        }
    }

    public function nextPage(): void
    {
        if ($this->currentPage < ($this->pagination['last_page'] ?? 1)) {
            $this->currentPage++;
            $this->loadData();
        }
    }

    public function viewSubmission(int $id): void
    {
        $service = app(FrontendApiService::class);
        $this->selectedSubmission = $service->getFormSubmission($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedSubmission = null;
    }

    public function deleteSubmission(int $id): void
    {
        $service = app(FrontendApiService::class);

        if ($service->deleteFormSubmission($id)) {
            Notification::make()
                ->title('Submission deleted')
                ->success()
                ->send();

            $this->loadData();
        } else {
            Notification::make()
                ->title('Failed to delete submission')
                ->danger()
                ->send();
        }
    }
}
