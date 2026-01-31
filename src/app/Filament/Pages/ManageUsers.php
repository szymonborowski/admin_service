<?php

namespace App\Filament\Pages;

use App\Services\UsersApiService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageUsers extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $title = 'Manage Users';
    protected static string $view = 'filament.pages.manage-users';
    protected static ?int $navigationSort = 1;

    public array $users = [];
    public array $roles = [];
    public ?int $selectedUserId = null;
    public ?string $selectedRole = null;

    // Edit modal properties
    public bool $showEditModal = false;
    public ?int $editUserId = null;
    public string $editUserName = '';
    public string $editUserEmail = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(UsersApiService::class);
        $response = $service->getUsers();

        $this->users = $response['data'] ?? [];
        $this->roles = $service->getRoles();
    }

    public function assignRole(): void
    {
        if (!$this->selectedUserId || !$this->selectedRole) {
            Notification::make()
                ->title('Please select user and role')
                ->warning()
                ->send();
            return;
        }

        $service = app(UsersApiService::class);

        if ($service->assignRole($this->selectedUserId, $this->selectedRole)) {
            Notification::make()
                ->title('Role assigned successfully')
                ->success()
                ->send();

            $this->loadData();
            $this->selectedUserId = null;
            $this->selectedRole = null;
        } else {
            Notification::make()
                ->title('Failed to assign role')
                ->danger()
                ->send();
        }
    }

    public function removeUserRole(int $userId, string $role): void
    {
        $service = app(UsersApiService::class);

        if ($service->removeRole($userId, $role)) {
            Notification::make()
                ->title('Role removed successfully')
                ->success()
                ->send();

            $this->loadData();
        } else {
            Notification::make()
                ->title('Failed to remove role')
                ->danger()
                ->send();
        }
    }

    public function openEditModal(int $userId, string $name, string $email): void
    {
        $this->editUserId = $userId;
        $this->editUserName = $name;
        $this->editUserEmail = $email;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editUserId = null;
        $this->editUserName = '';
        $this->editUserEmail = '';
    }

    public function saveUser(): void
    {
        if (!$this->editUserId) {
            return;
        }

        $service = app(UsersApiService::class);

        $result = $service->updateUser($this->editUserId, [
            'name' => $this->editUserName,
            'email' => $this->editUserEmail,
        ]);

        if ($result) {
            Notification::make()
                ->title('User updated successfully')
                ->success()
                ->send();

            $this->dispatch('close-modal', id: 'edit-user-modal');
            $this->closeEditModal();
            $this->loadData();
        } else {
            Notification::make()
                ->title('Failed to update user')
                ->danger()
                ->send();
        }
    }

    public function deleteUser(int $userId): void
    {
        $service = app(UsersApiService::class);

        if ($service->deleteUser($userId)) {
            Notification::make()
                ->title('User deleted successfully')
                ->success()
                ->send();

            $this->loadData();
        } else {
            Notification::make()
                ->title('Failed to delete user')
                ->danger()
                ->send();
        }
    }
}
