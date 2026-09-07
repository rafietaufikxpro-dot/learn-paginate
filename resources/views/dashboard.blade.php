<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Header Halaman -->
        <header class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">TechCorp Indonesia</p>
            </div>
            <div class="flex items-center space-x-3">
                @php
                    $initials = collect(explode(' ', Auth::user()->name))
                        ->map(fn($segment) => strtoupper(substr($segment, 0, 1)))
                        ->take(2)
                        ->join('');
                @endphp
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-sm shadow-sm">
                    {{ $initials }}
                </div>
            </div>
        </header>

        <!-- Main Content Card -->
        <main class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">
                Selamat datang, {{ Auth::user()->name }}
            </h2>
            <p class="text-sm text-gray-600">
                Anda telah masuk ke sistem internal TechCorp Indonesia. Silakan akses direktori karyawan untuk mencari atau mengelola informasi tim Anda.
            </p>
            <div class="pt-2">
                <a href="{{ route('employees.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out shadow-sm">
                    Lihat Direktori Karyawan
                    <svg class="w-4 h-4 ms-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </main>

    </div>
</x-app-layout>
