# PRD: Employee Directory — Search, Filter, Pagination & Seeding

## 1. Overview

**Produk:** Halaman Direktori Karyawan (Employee Directory) untuk TechCorp Indonesia
**Platform:** Web application berbasis Laravel (server-side rendered, Blade)
**Konteks:** Sistem HRD saat ini lambat karena memuat seluruh data karyawan sekaligus tanpa pagination, search, atau filter. PRD ini mendefinisikan kebutuhan untuk membangun ulang halaman tersebut agar performa dan pengalaman pengguna (HRD) meningkat.

## 2. Problem Statement

> **Catatan konsistensi:** Narasi awal skenario menyebut sistem HRD "menampung puluhan ribu data", sementara jumlah karyawan aktual perusahaan disebut 800 orang. Untuk keperluan PRD ini, jumlah data yang dipakai sebagai acuan teknis (seeding, pagination, testing) adalah **800 data**, sesuai dengan scope tugas. Angka "puluhan ribu" dianggap sebagai gambaran skala pertumbuhan bisnis ke depan, bukan jumlah data yang harus di-generate saat ini.

- Data karyawan (800 baris) saat ini dimuat sekaligus tanpa batasan halaman → performa halaman lambat dan berpotensi makin buruk seiring pertumbuhan data.
- HRD tidak bisa mencari karyawan tertentu secara cepat berdasarkan nama/email.
- HRD tidak bisa menyaring karyawan berdasarkan departemen.
- Saat berpindah halaman, parameter pencarian/filter yang sudah diisi hilang (reset).

## 3. Goals

1. Membuat 800 data dummy karyawan sebagai simulasi data produksi.
2. Membatasi tampilan data ke 15 baris per halaman (pagination).
3. Menyediakan fitur pencarian berdasarkan nama atau email.
4. Menyediakan fitur filter berdasarkan departemen.
5. Mempertahankan parameter pencarian/filter saat berpindah halaman.

## 4. Non-Goals (Di luar cakupan)

- Tidak mencakup fitur CRUD (create/update/delete) karyawan.
- Tidak mencakup autentikasi/otorisasi user (login HRD).
- Tidak mencakup export data (Excel/PDF).
- Tidak mencakup sorting kolom (misal klik header untuk sort).

## 5. User & Use Case

**User:** Staff HRD TechCorp Indonesia.

**Use case utama:**
- HRD membuka halaman `/employees`, melihat daftar karyawan (15 per halaman).
- HRD mengetik nama/email di kolom pencarian → hasil tersaring sesuai kata kunci.
- HRD memilih departemen dari dropdown → hasil tersaring sesuai departemen.
- HRD mengombinasikan search + filter, lalu klik halaman berikutnya → kombinasi filter tetap ada di URL dan hasil.

## 6. Functional Requirements

| ID | Requirement | Prioritas |
| --- | --- | --- |
| FR-1 | Sistem menghasilkan 800 data karyawan dummy via Factory & Seeder | Wajib |
| FR-2 | Data karyawan ditampilkan maksimal 15 baris per halaman | Wajib |
| FR-3 | User dapat mencari karyawan via parameter `?search=` berdasarkan `name` ATAU `email` | Wajib |
| FR-4 | User dapat memfilter karyawan via parameter `?department=` | Wajib |
| FR-5 | Search dan filter dapat digunakan bersamaan (kombinasi AND) | Wajib |
| FR-6 | Parameter search & filter tetap muncul di URL saat berpindah halaman pagination | Wajib |
| FR-7 | UI menyediakan form input search, dropdown department, dan tombol submit | Wajib |
| FR-8 | UI menampilkan tabel dengan kolom No, Nama, Email, Posisi, Departemen | Wajib |
| FR-9 | UI menampilkan navigasi halaman (links pagination) | Wajib |

## 7. Technical Specification

### 7.1 Database Schema

**Tabel:** `employees`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| id | bigint, auto increment, primary key | default Laravel |
| name | string | nama karyawan |
| email | string, unique | email karyawan |
| position | string | posisi/jabatan |
| department | string | salah satu: IT, Finance, HR, Marketing, Operations |
| created_at / updated_at | timestamp | default Laravel |

**Migration:** `database/migrations/xxxx_xx_xx_create_employees_table.php`

```php
Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('position');
    $table->string('department');
    $table->timestamps();
});
```

### 7.2 Model

**File:** `app/Models/Employee.php`

```php
class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'position', 'department'];
}
```

### 7.3 Factory

**File:** `database/factories/EmployeeFactory.php`

```php
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(
                ['IT', 'Finance', 'HR', 'Marketing', 'Operations']
            ),
        ];
    }
}
```

### 7.4 Seeder

**File:** `database/seeders/DatabaseSeeder.php`

```php
public function run(): void
{
    Employee::factory()->count(800)->create();
}
```

**Perintah terminal:**

```bash
php artisan migrate:fresh --seed
```

### 7.5 Route

**File:** `routes/web.php`

```php
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
```

### 7.6 Controller

**File:** `app/Http/Controllers/EmployeeController.php`

```php
class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->department, function ($query, $department) {
                $query->where('department', $department);
            })
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }
}
```

**Catatan logika:**
- `when()` pertama hanya dijalankan jika `search` ada nilainya di request.
- `when()` kedua hanya dijalankan jika `department` ada nilainya di request.
- `withQueryString()` memastikan semua query string (search & department) ikut terbawa ke link pagination berikutnya.

### 7.7 View (Blade)

**File:** `resources/views/employees/index.blade.php`

Komponen yang wajib ada:

1. **Form Search & Filter** (method `GET` agar parameter muncul di URL):
```blade
<form method="GET" action="{{ route('employees.index') }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email">
    <select name="department">
        <option value="">Semua Departemen</option>
        @foreach(['IT', 'Finance', 'HR', 'Marketing', 'Operations'] as $dept)
            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                {{ $dept }}
            </option>
        @endforeach
    </select>
    <button type="submit">Cari / Terapkan</button>
</form>
```

2. **Tabel Data:**
```blade
<table>
    <thead>
        <tr><th>No</th><th>Nama</th><th>Email</th><th>Posisi</th><th>Departemen</th></tr>
    </thead>
    <tbody>
        @foreach($employees as $index => $employee)
        <tr>
            <td>{{ $employees->firstItem() + $index }}</td>
            <td>{{ $employee->name }}</td>
            <td>{{ $employee->email }}</td>
            <td>{{ $employee->position }}</td>
            <td>{{ $employee->department }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

3. **Pagination Links:**
```blade
{{ $employees->links() }}
```

## 8. Acceptance Criteria

- [ ] Terdapat 800 data karyawan di database, dihasilkan oleh Seeder.
- [ ] Tabel hanya menampilkan 15 data karyawan per halaman.
- [ ] Pencarian berdasarkan nama/email mengubah URL (contoh: `?search=John`) dan hasil sesuai kata kunci.
- [ ] Filter departemen mengubah URL (contoh: `?department=IT`) dan hasil sesuai departemen.
- [ ] Kombinasi search + filter berfungsi bersamaan.
- [ ] Saat berpindah halaman, parameter search & department tidak hilang dari URL.

## 9. Open Questions / Asumsi

- Diasumsikan tidak ada requirement autentikasi/login untuk mengakses halaman ini.
- Diasumsikan pencarian bersifat case-insensitive dan partial match (`LIKE %keyword%`), mengikuti perilaku default MySQL `LIKE`.
- Diasumsikan tampilan pagination menggunakan style default Laravel (Tailwind), tanpa kustomisasi desain khusus.
- Belum ada requirement mengenai validasi input pada form search/filter (misal karakter khusus).
- Belum ada requirement mengenai tampilan **empty state** (pesan "Data tidak ditemukan") saat hasil search/filter kosong. Disarankan ditambahkan untuk UX yang lebih baik, tapi menunggu konfirmasi karena tidak diminta eksplisit di LKPD asli.
