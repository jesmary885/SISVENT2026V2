<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User; // ¡IMPORTANTE: Asegúrate de importar el modelo User!
use Spatie\Permission\Models\Role;

class UserRoleManager extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $selectedUserId = null;
    public $selectedRoles = [];
    public $selectedUserName = ''; // Agregar esta propiedad

    protected $listeners = ['userRolesUpdated' => 'render'];

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate($this->perPage);

        $roles = Role::orderBy('name')->get();

        return view('livewire.user.user-role-manager', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function editUserRoles($userId)
    {
        $this->selectedUserId = $userId;
        $user = User::with('roles')->find($userId);
        
        if ($user) {
            $this->selectedUserName = $user->name; // Guardar el nombre
            $this->selectedRoles = $user->roles->pluck('id')->toArray();
        }
    }

    public function updateUserRoles()
    {
        try {
            $user = User::find($this->selectedUserId);
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            
            $user->syncRoles($roles);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Roles actualizados correctamente.'
            ]);

            $this->reset(['selectedUserId', 'selectedRoles', 'selectedUserName']);
            $this->emit('userRolesUpdated');

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelEdit()
    {
        $this->reset(['selectedUserId', 'selectedRoles', 'selectedUserName']);
    }
}