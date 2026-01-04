<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserRoleManager extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    
    // Para gestión de roles
    public $selectedUserId = null;
    public $selectedRoles = [];
    public $selectedUserName = '';
    
    // Para crear/editar usuarios
    public $showUserModal = false;
    public $userForm = [
        'id' => null,
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role_id' => ''
    ];
    
    // Validación
    public $isEditing = false;

    protected $listeners = ['userRolesUpdated' => 'render'];

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            'userForm.name' => 'required|string|min:3|max:255',
            'userForm.email' => 'required|email|unique:users,email',
            'userForm.role_id' => 'required|exists:roles,id',
        ];

        // Solo requerir password si está creando o cambiando
        if (!$this->isEditing || !empty($this->userForm['password'])) {
            $rules['userForm.password'] = 'required|min:8|confirmed';
            $rules['userForm.password_confirmation'] = 'required';
        }

        // Para edición, excluir el usuario actual de la validación de email único
        if ($this->isEditing) {
            $rules['userForm.email'] = 'required|email|unique:users,email,' . $this->userForm['id'];
        }

        return $rules;
    }

    // Mensajes de validación
    protected $messages = [
        'userForm.name.required' => 'El nombre es obligatorio.',
        'userForm.name.min' => 'El nombre debe tener al menos 3 caracteres.',
        'userForm.email.required' => 'El email es obligatorio.',
        'userForm.email.email' => 'El email debe ser válido.',
        'userForm.email.unique' => 'Este email ya está registrado.',
        'userForm.password.required' => 'La contraseña es obligatoria.',
        'userForm.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'userForm.password.confirmed' => 'Las contraseñas no coinciden.',
        'userForm.password_confirmation.required' => 'Debes confirmar la contraseña.',
        'userForm.role_id.required' => 'Debes seleccionar un rol.',
    ];

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $roles = Role::orderBy('name')->get();

        return view('livewire.user.user-role-manager', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    // ========== GESTIÓN DE ROLES ==========
    public function editUserRoles($userId)
    {
        $this->selectedUserId = $userId;
        $user = User::with('roles')->find($userId);
        
        if ($user) {
            $this->selectedUserName = $user->name;
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

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // ========== CREAR/EDITAR USUARIOS ==========
    public function createUser()
    {
        $this->resetUserForm();
        $this->isEditing = false;
        $this->showUserModal = true;
    }

    public function editUser($userId)
    {
        $user = User::with('roles')->find($userId);
        
        if ($user) {
            $this->userForm = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
                'role_id' => $user->roles->first()->id ?? ''
            ];
            
            $this->isEditing = true;
            $this->showUserModal = true;
        }
    }

    public function saveUser()
    {
        $this->validate();

        try {
            $userData = [
                'name' => $this->userForm['name'],
                'email' => $this->userForm['email'],
            ];

            // Actualizar contraseña solo si se proporcionó una nueva
            if (!empty($this->userForm['password'])) {
                $userData['password'] = Hash::make($this->userForm['password']);
            }

            if ($this->isEditing) {
                $user = User::find($this->userForm['id']);
                $user->update($userData);
                
                $message = 'Usuario actualizado correctamente.';
            } else {
                $user = User::create($userData);
                $message = 'Usuario creado correctamente.';
            }

            // Asignar rol
            if (!empty($this->userForm['role_id'])) {
                $role = Role::find($this->userForm['role_id']);
                if ($role) {
                    $user->syncRoles([$role]);
                }
            }

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => $message
            ]);

            $this->closeUserModal();
            $this->resetPage();

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteUser($userId)
    {
        try {
            // Evitar que se elimine a sí mismo
            if ($userId === auth()->id()) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No puedes eliminar tu propio usuario.'
                ]);
                return;
            }

            $user = User::find($userId);
            
            if ($user) {
                $userName = $user->name;
                $user->delete();

                $this->dispatchBrowserEvent('notify', [
                    'type' => 'success',
                    'message' => "Usuario '{$userName}' eliminado correctamente."
                ]);

                $this->resetPage();
            }

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->resetUserForm();
        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->reset(['selectedUserId', 'selectedRoles', 'selectedUserName']);
    }

    // ========== MÉTODOS AUXILIARES ==========
    private function resetUserForm()
    {
        $this->userForm = [
            'id' => null,
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role_id' => ''
        ];
        
        $this->isEditing = false;
        $this->resetValidation();
    }
}