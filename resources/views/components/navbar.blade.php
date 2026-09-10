<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <div class="flex items-center space-x-3">
                <a href="/dashboard" class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Kanal Kalimantan" class="h-9 w-auto object-contain">
                    <span class="text-red-600 font-extrabold text-xl tracking-tight">Kanal Kalimantan</span>
                </a>
            </div>

            <nav class="flex space-x-8">
                <!-- Link Beranda / Dashboard -->
                <a href="/dashboard" 
                   class="inline-flex items-center px-1 pt-1 text-sm font-bold transition-colors duration-150 {{ request()->is('dashboard*') ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Beranda
                </a>

                <!-- Link Bonus -->
                <a href="/bonus" 
                   class="inline-flex items-center px-1 pt-1 text-sm font-bold transition-colors duration-150 {{ request()->is('bonus*') ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Bonus
                </a>

                <!-- Link Berita -->
                <a href="/berita" 
                   class="inline-flex items-center px-1 pt-1 text-sm font-bold transition-colors duration-150 {{ request()->is('berita*') ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Berita
                </a>
            </nav>

        </div>
    </div>
</header>