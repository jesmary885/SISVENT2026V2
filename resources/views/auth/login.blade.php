<x-guest-layout>


      <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<div class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-6xl w-full flex flex-col lg:flex-row rounded-2xl overflow-hidden shadow-2xl">
        <!-- Sección de Imagen -->
        <div class="lg:w-1/2 bg-white p-8 lg:p-12 flex flex-col justify-center order-2 lg:order-1">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Bienvenido de nuevo</h1>
                <p class="text-gray-600">Inicia sesión en tu cuenta</p>
            </div>
            
            <!-- Formulario de Login -->

            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf
     
                <!-- Campo Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope text-blue-500 mr-2"></i>Correo Electrónico
                    </label>
                    <div class="relative">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                            placeholder="tu@email.com"
                            required
                        >
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Campo Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock text-blue-500 mr-2"></i>Contraseña
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 pl-10 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Recordar contraseña y Olvidé contraseña -->
                {{-- <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Recordarme
                        </label>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition duration-300">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div> --}}
                
                <!-- Botón de Iniciar Sesión -->
                <button 
                    type="submit" 
                    class="w-full cursor-pointer bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-300 transform hover:-translate-y-0.5"
                >
                    Iniciar Sesión
                </button>
                

            </form>
        </div>
        
        <!-- Sección de Imagen -->
        <div class="lg:w-1/2 bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center p-8 lg:p-12 order-1 lg:order-2">
            <div class="text-center text-white">
                <div class="mb-6">
                    <i class="fas fa-user-shield text-6xl text-white mb-4 opacity-90"></i>
                    <h2 class="text-3xl font-bold mb-4">Tu seguridad es nuestra prioridad</h2>
                    <p class="text-lg opacity-90 max-w-md mx-auto">
                        Accede de forma segura a tu cuenta y disfruta de todos nuestros servicios.
                    </p>
                </div>
                
                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-shield-alt text-2xl mb-2 text-gray-50"></i>
                        <span>Protección avanzada</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-bolt text-2xl mb-2"></i>
                        <span>Acceso rápido</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-lock text-2xl mb-2"></i>
                        <span>Datos seguros</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad para mostrar/ocultar contraseña
        document.querySelector('button[type="button"]').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Efecto de elevación en los botones de redes sociales
        document.querySelectorAll('button[type="button"]').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.classList.add('shadow-md');
            });
            
            button.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-md');
            });
        });
    </script>
</div>








    {{-- <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <div class="bg-no-repeat bg-cover bg-center relative" style="background-image: url({{Storage::url('imagen/login.jpg')}})"><div class="absolute bg-gradient-to-b from-blue-500 to-blue-400 opacity-75 inset-0 z-0"></div>
        <div class="min-h-screen sm:flex justify-around  mx-0 ">
            <div class=" p-10  mx-12 z-10">
                <div class="self-start hidden lg:flex flex-col  text-white">
                <img src="" class="mb-3">
             
                </div>
            </div>
            <div class="flex justify-start self-center mr-20 z-10">
                <div class="p-12 bg-white mx-auto rounded-2xl  ">
                    <div class="mb-4">
                    <h3 class="font-semibold text-2xl justify-center text-center text-gray-800">Inicio de sesión </h3>

                    </div>

                    <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="space-y-5">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700 tracking-wide">Email</label>
                    <input  id="email" type="email" name="email" class=" w-full text-base px-4 py-2 border  border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" placeholder="mail@gmail.com">
                    </div>
                                <div class="space-y-2">
                    <label class="mb-5 text-sm font-medium text-gray-700 tracking-wide">
                        Contraseña
                    </label>
                    <input id="password" type="password" name="password" class="w-full content-center text-base px-4 py-2 border  border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" placeholder="*****************">
                    </div>
                    <div class="flex items-center justify-between">
                    <div class="flex items-center">
               
                    </div>
                    <div class="text-sm">
                      
                    </div>
                    </div>
                    <div>
                    <button type="submit" class="w-full flex justify-center bg-blue-400  hover:bg-blue-500 text-gray-100 p-3  rounded-full tracking-wide font-semibold  shadow-lg cursor-pointer transition ease-in duration-500">
                        Sign in
                    </button>
                    </div>
                    </div>

                    </form>
                    <div class="pt-5 text-center text-gray-400 text-xs">
                    <span>
                         COPYRIGHT© 2025 <a href="https://www.instagram.com/codesupportonline/">CODESUPPORT</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}




</x-guest-layout>
