<?php

namespace App\Http\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $confirmingDelete = null;
    public $roleIdToDelete = null;
    public $showForm = false;
    public $editingRoleId = null;

    protected $listeners = [
        'roleUpdated' => 'handleRoleUpdated',
        'closeForm' => 'closeForm'  // Agregar este listener
    ];

    public function render()
    {
        $roles = Role::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })
        ->paginate($this->perPage);

        return view('livewire.roles.role-index', [
            'roles' => $roles,
        ]);
    }

    // Crear nuevo rol
    public function createRole()
    {
        $this->editingRoleId = null;
        $this->showForm = true;
    }

    // Editar rol existente
    public function editRole($roleId)
    {
        $this->editingRoleId = $roleId;
        $this->showForm = true;
    }

    // Cerrar formulario - método público
    public function closeForm()
    {
        $this->showForm = false;
        $this->editingRoleId = null;
    }

    // Cuando se actualiza un rol
    public function handleRoleUpdated()
    {
        $this->closeForm();
        $this->resetPage();
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Operación completada correctamente.'
        ]);
    }

    public function deleteRole($roleId)
    {
        $this->confirmingDelete = true;
        $this->roleIdToDelete = $roleId;
    }

    public function confirmDelete()
    {
        try {
            $role = Role::find($this->roleIdToDelete);
            
            if (in_array($role->name, ['Super Admin', 'Admin'])) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No se puede eliminar este rol del sistema.'
                ]);
                return;
            }

            $role->delete();
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Rol eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el rol: ' . $e->getMessage()
            ]);
        }

        $this->confirmingDelete = false;
        $this->roleIdToDelete = null;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->roleIdToDelete = null;
    }
}