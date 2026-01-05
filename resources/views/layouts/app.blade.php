<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'v12026') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

          <link rel="stylesheet" href="{{ mix('css/app.css') }}">

          {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>

        {{-- sweetalert2 --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

           <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Edu+AU+VIC+WA+NT+Dots:wght@400..700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Allerta+Stencil&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/simplycountdown.js@2.0.0/dist/simplyCountdown.min.js"></script>

        <link rel="stylesheet" href="https://cdn.tailgrids.com/tailgrids-fallback.css" />



    
        <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v5.15.1/css/pro.min.css" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">


       

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen ">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts

        <script>
        Livewire.on('errorSize', mensaje => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: mensaje,
            }) /*  */
        });
    </script>
    
    <script>
        livewire.on('confirm', (ms,item1,item2,ms2) => {
            Swal.fire({
            title: ms,
            text: "No podrá revertir esto",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, seguro'
            }).then((result) => {
            if (result.isConfirmed) {
                        livewire.emitTo(item1,item2)
                        Swal.fire(
                        'Listo',
                        ms2,
                        'success'
                        )
                }
            })
        })
    </script>

    <!-- En tu layout principal (ej: app.blade.php) -->
    <script>
        // En tu layout principal (app.blade.php) o en un script separado
        window.addEventListener('imprimir-comprobante', event => {
            console.log('Evento imprimir-comprobante recibido:', event.detail);
            
            // Abrir en nueva ventana/tab SIN cerrar automáticamente
            const printWindow = window.open(
                event.detail.url + '?print=true&t=' + Date.now(),
                '_blank',
                'width=800,height=600,scrollbars=yes'
            );
            
            // Verificar si se bloqueó el popup
            if (!printWindow || printWindow.closed) {
                // Si se bloqueó, mostrar notificación
                alert('Por favor permite las ventanas emergentes para ver el comprobante');
                
                // O mostrar un enlace alternativo en la página
                const linkContainer = document.createElement('div');
                linkContainer.id = 'comprobante-alternativo';
                linkContainer.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 5px;
                    padding: 15px;
                    z-index: 9999;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                `;
                
                linkContainer.innerHTML = `
                    <p style="margin: 0 0 10px 0; color: #721c24;">
                        <strong>Comprobante listo</strong><br>
                        La ventana emergente fue bloqueada.
                    </p>
                    <a href="${event.detail.url}" target="_blank" 
                    style="display: inline-block; background: #007bff; color: white; 
                            padding: 8px 15px; border-radius: 4px; text-decoration: none;">
                        Ver comprobante
                    </a>
                    <button onclick="this.parentElement.remove()" 
                            style="margin-left: 10px; background: #6c757d; color: white; 
                                border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                        ✕
                    </button>
                `;
                
                document.body.appendChild(linkContainer);
                
                // Auto-remover después de 30 segundos
                setTimeout(() => {
                    if (document.getElementById('comprobante-alternativo')) {
                        document.getElementById('comprobante-alternativo').remove();
                    }
                }, 30000);
            }
        });
    </script>
<!-- En app.blade.php o tu layout principal -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

        <script>
            document.addEventListener('livewire:initialized', function() {
                Livewire.on('notify', (event) => {
                    Toastify({
                        text: event.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: event.type === 'success' ? '#10B981' : '#EF4444',
                        stopOnFocus: true,
                    }).showToast();
                });
            });
        </script>

        <script>
            document.addEventListener('livewire:initialized', () => {
                // Escuchar eventos de cierre
                Livewire.on('closeForm', () => {
                    // El componente principal manejará esto
                });
                
                // También puedes cerrar con Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        Livewire.dispatch('closeForm');
                    }
                });
            });
        </script>




    </body>
</html>
