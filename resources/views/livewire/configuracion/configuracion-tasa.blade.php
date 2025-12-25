<div>
<style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
         .bod {
            font-family: 'Poppins', sans-serif;
   

            display: flex;
            align-items: center;
            justify-content: center;
        } 
        
        .exchange-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .exchange-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        
        .currency-input {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }
        
        .currency-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .update-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .update-btn:active {
            transform: translateY(0);
        }
        
        .update-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .update-btn:hover::after {
            left: 100%;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
            }
        }
        
        .last-update {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<div class="p-4 bod ">
    <div class="exchange-card w-full max-w-md p-8">
        <!-- Encabezado -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mb-4 pulse-animation">
                <i class="fas fa-exchange-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Tasa de Cambio</h1>
            <p class="text-gray-600">Actualiza el valor de la moneda extranjera</p>
        </div>
        
        <!-- Información de última actualización -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 mb-6 border border-blue-100">
            <div class="">
                <div>
                    <p class="text-sm text-gray-600">Última actualización</p>
                    <p class="text-lg font-semibold last-update"> {{ \Carbon\Carbon::parse($tasa_actual->updated_at)->format('d/m/Y \\a \\l\\a\\s h:i A') }}</p>
                </div>
                <div class="mt-2" >
                    <p class="text-sm text-gray-600 ">Valor actual</p>
                    <p class="text-xl font-bold text-gray-800">1 USD = {{$tasa_actual->tasa_actual}} BS</p>
                </div>
            </div>
        </div>
        
        <!-- Formulario de actualización -->
        <div class="mb-6">
            <label for="exchange-rate" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-dollar-sign text-green-500 mr-1"></i>
                Nueva tasa de cambio
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 font-bold">1 USD =</span>
                </div>
                <input 
                    type="number" 
                    id="exchange-rate" 
                    wire:model="tasa" 
                    step="0.01"
                    min="0"
                    class="currency-input w-full pl-20 pr-4 py-4 text-lg font-semibold rounded-xl focus:outline-none"
                    placeholder="0.00"
                    value="36.50"
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-700 font-medium">BS</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Ingresa el nuevo valor en bolívares para 1 dólar americano
            </p>
        </div>
        
        <!-- Botón de actualización -->
        <button class="update-btn w-full py-4 text-white font-bold text-lg rounded-xl flex items-center justify-center"
            wire:click="save"
            wire:loading.attr="disabled"
                >
            <i class="fas fa-sync-alt mr-2"></i>
            Actualizar Tasa de Cambio
        </button>
        
        <!-- Nota adicional -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500 flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-500 mr-1"></i>
                Esta información afectará todos los cálculos financieros del sistema
            </p>
        </div>
    </div>

    <script>
        // Efecto de animación al hacer clic en el botón
        document.querySelector('.update-btn').addEventListener('click', function() {
            const rate = document.getElementById('exchange-rate').value;
            
            if (!rate || rate <= 0) {
                alert('Por favor ingresa un valor válido para la tasa de cambio');
                return;
            }
            
            // Animación de carga
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...';
            this.disabled = true;
            
            // Simular actualización
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check mr-2"></i> ¡Actualizado!';
                this.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                
                // Actualizar la información de última actualización
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                document.querySelector('.last-update').textContent = `Hoy, ${timeString}`;
                document.querySelector('.text-xl.font-bold').textContent = `1 USD = ${rate} BS`;
                
                // Restaurar después de 2 segundos
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Actualizar Tasa de Cambio';
                    this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                    this.disabled = false;
                }, 2000);
            }, 1500);
        });
        
        // Efecto de foco en el input
        const input = document.getElementById('exchange-rate');
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
            this.parentElement.classList.remove('border-gray-300');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
        });
    </script>
</div>
</div>
