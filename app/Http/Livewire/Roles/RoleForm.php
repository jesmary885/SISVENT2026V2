<?php

namespace App\Http\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleForm extends Component
{
    public $roleId;
    public $name = '';
    public $guard_name = 'web';
    public $selectedPermissions = [];
    
    // Definir reglas básicas
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string|in:web,api',
        ];

        // Si estamos editando, excluir el rol actual de la validación unique
        if ($this->roleId) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $this->roleId;
        } else {
            // Si estamos creando, validar que sea único
            $rules['name'] = 'required|string|max:255|unique:roles,name';
        }

        return $rules;
    }

    public function mount($roleId = null)
    {
        if ($roleId) {
            $this->roleId = $roleId;
            $role = Role::with('permissions')->find($roleId);
            
            if ($role) {
                $this->name = $role->name;
                $this->guard_name = $role->guard_name;
                $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
            }
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->roleId) {
                // Actualizar rol existente
                $role = Role::find($this->roleId);
                $role->update([
                    'name' => $this->name,
                    'guard_name' => $this->guard_name,
                ]);
                $message = 'Rol actualizado correctamente.';
            } else {
                // Crear nuevo rol
                $role = Role::create([
                    'name' => $this->name,
                    'guard_name' => $this->guard_name,
                ]);
                $message = 'Rol creado correctamente.';
            }

            // Sincronizar permisos
            if (!empty($this->selectedPermissions)) {
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            // Emitir evento para cerrar el formulario
            $this->emit('roleUpdated');
            
            // También emitir evento específico para cerrar
            $this->emit('closeForm');

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Método para cerrar manualmente
    public function close()
    {
        $this->emit('closeForm');
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get();
        
        return view('livewire.roles.role-form', [
            'permissions' => $permissions,
        ]);
    }
}