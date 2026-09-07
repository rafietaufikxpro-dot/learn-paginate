<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Header Halaman -->
        <header class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Direktori Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">TechCorp Indonesia</p>
            </div>
            <div class="flex items-center space-x-3">
                @auth
                    @php
                        $userInitials = collect(explode(' ', Auth::user()->name))
                            ->filter()
                            ->map(fn($segment) => strtoupper(substr($segment, 0, 1)))
                            ->take(2)
                            ->join('');
                    @endphp
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-sm shadow-sm">
                        {{ $userInitials }}
                    </div>
                @else
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-sm shadow-sm">
                        HR
                    </div>
                @endauth
            </div>
        </header>

        <!-- Form Search & Filter -->
        <section class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nama/email..." 
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    >
                </div>

                <!-- Select Department -->
                <div class="w-full sm:w-56">
                    <select 
                        name="department" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white"
                    >
                        <option value="">Semua Departemen</option>
                        @foreach(['IT', 'Finance', 'HR', 'Marketing', 'Operations'] as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition duration-150 ease-in-out shadow-sm"
                >
                    Cari / Terapkan
                </button>
            </form>
        </section>

        <!-- Main Content Table & Footer -->
        <main class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 font-semibold border-b border-gray-200 uppercase tracking-wider text-xs">
                        <tr>
                            <th scope="col" class="py-3.5 px-4 w-16 text-center">No</th>
                            <th scope="col" class="py-3.5 px-4">Nama</th>
                            <th scope="col" class="py-3.5 px-4">Email</th>
                            <th scope="col" class="py-3.5 px-4">Posisi</th>
                            <th scope="col" class="py-3.5 px-4">Departemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($employees as $index => $employee)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4 text-center font-medium text-gray-500">
                                    {{ $employees->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4 font-medium text-gray-900">
                                    {{ $employee->name }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    {{ $employee->email }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    {{ $employee->position }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClasses = match($employee->department) {
                                            'IT' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Finance' => 'bg-green-100 text-green-800 border-green-200',
                                            'HR' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'Marketing' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'Operations' => 'bg-red-100 text-red-800 border-red-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                                        {{ $employee->department }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 px-4 text-center text-gray-500">
                                    Data tidak ditemukan. Coba ubah kata kunci atau filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination & Info -->
            @if($employees->total() > 0)
                <div class="px-4 py-3.5 bg-white border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }} dari {{ $employees->total() }} karyawan
                    </div>
                    <div>
                        {{ $employees->links() }}
                    </div>
                </div>
            @endif
        </main>
    </div>
</x-app-layout>
