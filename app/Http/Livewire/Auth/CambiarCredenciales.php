<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class CambiarCredenciales extends Component
{

    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $email;
    public $current_email;

    public function mount()
    {
        $this->email = auth()->user()->email;
        $this->current_email = auth()->user()->email;
    }

    protected $rules = [
        'email' => 'required|email|unique:users,email,' . null . ',id',
        'new_password' => 'nullable|min:8|confirmed',
    ];

    protected $messages = [
        'current_password.required' => 'La contraseña actual es obligatoria',
        'current_password.current_password' => 'La contraseña actual es incorrecta',
        'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
        'new_password.confirmed' => 'Las contraseñas no coinciden',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'Debe ser un email válido',
        'email.unique' => 'Este email ya está en uso',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function cambiarCredenciales()
    {
        $user = auth()->user();

        // Validaciones
        $this->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('La contraseña actual es incorrecta');
                }
            }],
            'email' => 'required|email|unique:users,email,' . $user->id,
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        try {
            // Actualizar email si cambió
            if ($this->email !== $this->current_email) {
                $user->email = $this->email;
                $user->email_verified_at = null; // Requiere verificación nuevamente
            }

            // Actualizar contraseña si se proporcionó una nueva
            if (!empty($this->new_password)) {
                $user->password = Hash::make($this->new_password);
            }

            $user->save();

            // Limpiar campos
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
            $this->current_email = $user->email;

            // Emitir evento de éxito
            $this->dispatchBrowserEvent('credenciales-actualizadas', [
                'type' => 'success',
                'message' => 'Credenciales actualizadas correctamente'
            ]);

        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('credenciales-actualizadas', [
                'type' => 'error',
                'message' => 'Error al actualizar las credenciales: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.auth.cambiar-credenciales');
    }
}
