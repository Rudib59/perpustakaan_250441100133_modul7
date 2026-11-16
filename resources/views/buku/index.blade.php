@extends('layouts.perpustakaan')

@section('title', 'Daftar Buku')

@section('content')
<style>
    .buku-wrap {
        max-width: 1000px;
        margin: 0 auto;
        padding: 24px 16px;
    }

    .buku-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .buku-form label {
        display: block;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .buku-form input,
    .buku-form select {
        width: 100%;
        padding: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-sizing: border-box;
    }

    .buku-aksi {
        grid-column: 1 / -1;
        display: flex;
        gap: 8px;
    }

    .buku-wrap button {
        padding: 8px 14px;
        border: 0;
        border-radius: 6px;
        cursor: pointer;
        background: #2563eb;
        color: #fff;
    }

    .buku-wrap button.sekunder {
        background: #64748b;
    }

    .buku-wrap button.bahaya {
        background: #dc2626;
    }

    .buku-tabel {
        width: 100%;
        border-collapse: collapse;
    }

    .buku-tabel th,
    .buku-tabel td {
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        text-align: left;
    }

    .buku-tabel th {
        background: #f1f5f9;
    }

    #pesan {
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        display: none;
    }

    #pesan.sukses {
        display: block;
        background: #dcfce7;
        color: #166534;
    }

    #pesan.gagal {
        display: block;
        background: #fee2e2;
        color: #991b1b;
    }

    form.inline {
        display: inline;
    }

    .btn-kecil {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        color: #fff;
        background: #2563eb;
        border: 0;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        margin-left: 6px;
    }

    .btn-kecil:first-child {
        margin-left: 0;
    }

    .btn-kecil.bahaya {
        background: #dc2626;
    }
</style>

<div class="buku-wrap"
    data-kelola="{{ auth()->user()->can('kelola-buku') ? '1' : '0' }}"
    data-csrf="{{ csrf_token() }}">
    <h1>Daftar Buku</h1>

    @if (session('error'))
    <div style="background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
        {{ session('error') }}
    </div>
    @endif
    @if ($errors->any())
    <div style="background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
        {{ $errors->all()[0] }}
    </div>
    @endif
    @if (session('sukses'))
    <div class="sukses" style="padding:10px 12px;border-radius:6px;margin-bottom:12px;background:#dcfce7;color:#166534;">
        {{ session('sukses') }}
    </div>
    @endif

    @can('kelola-buku')
    <form id="form-buku" class="buku-form" method="POST"
        action="{{ route('buku.store') }}" data-url-dasar="{{ url('/buku') }}">
        @csrf
        <input type="hidden" name="_method" id="metode" value="PUT" disabled>
        <div>
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                <option value="">-- Pilih kategori --</option>
                @foreach ($kategoris as $k)
                <option value="{{ $k->id }}" @selected(old('kategori_id')==$k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required>
        </div>
        <div>
            <label for="penulis">Penulis</label>
            <input type="text" id="penulis" name="penulis" value="{{ old('penulis') }}" required>
        </div>
        <div>
            <label for="tahun_terbit">Tahun Terbit</label>
            <input type="text" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit') }}" required>
        </div>
        <div class="buku-aksi">
            <button type="submit" id="btn-simpan">Tambah Buku</button>
            <button type="button" id="btn-batal" class="sekunder" style="display:none;">Batal</button>
        </div>
    </form>
    @endcan

    {{-- Tabel dirender oleh Blade (server-side), bukan lagi oleh JavaScript.
         Ini diperlukan agar setiap baris bisa memiliki form Hapus asli
         dengan @csrf dan @method('DELETE') sesuai Soal 2(c). --}}
    <table class="buku-tabel">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tahun Terbit</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabel-buku">
            @forelse ($bukus as $buku)
            <tr>
                <td>{{ $buku->kategori->nama ?? '-' }}</td>
                {{-- Soal 2(a): escaping Blade default, judul selalu tampil sebagai teks --}}
                <td>{{ $buku->judul }}</td>
                <td>{{ $buku->penulis }}</td>
                <td>{{ $buku->tahun_terbit }}</td>
                <td>
                    <a href="{{ url('/buku/' . $buku->id) }}" class="btn-kecil">Detail</a>

                    @can('kelola-buku')
                    <button type="button"
                        class="btn-kecil sekunder btn-ubah"
                        data-id="{{ $buku->id }}"
                        data-kategori_id="{{ $buku->kategori_id }}"
                        data-judul="{{ $buku->judul }}"
                        data-penulis="{{ $buku->penulis }}"
                        data-tahun_terbit="{{ $buku->tahun_terbit }}">
                        Ubah
                    </button>

                    {{-- Soal 2(c): penghapusan lewat form DELETE dengan @csrf --}}
                    <form class="inline" method="POST" action="{{ route('buku.destroy', $buku) }}"
                        onsubmit="return confirm('Hapus buku &quot;{{ $buku->judul }}&quot;?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-kecil bahaya">Hapus</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Belum ada data buku.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    const form = document.getElementById('form-buku');

    // Form hanya ada untuk admin/petugas
    if (form) {
        const URL_DASAR = form.dataset.urlDasar;
        const URL_TAMBAH = form.getAttribute('action');
        const metode = document.getElementById('metode');
        const btnSimpan = document.getElementById('btn-simpan');
        const btnBatal = document.getElementById('btn-batal');

        function resetForm() {
            form.reset();
            form.setAttribute('action', URL_TAMBAH);
            metode.disabled = true;
            btnSimpan.textContent = 'Tambah Buku';
            btnBatal.style.display = 'none';
        }

        function mulaiUbah(tombol) {
            document.getElementById('kategori_id').value = tombol.dataset.kategori_id;
            document.getElementById('judul').value = tombol.dataset.judul;
            document.getElementById('penulis').value = tombol.dataset.penulis;
            document.getElementById('tahun_terbit').value = tombol.dataset.tahun_terbit;
            form.setAttribute('action', URL_DASAR + '/' + tombol.dataset.id);
            metode.disabled = false;
            btnSimpan.textContent = 'Simpan Perubahan';
            btnBatal.style.display = 'inline-block';
            form.scrollIntoView({
                behavior: 'smooth'
            });
        }

        document.querySelectorAll('.btn-ubah').forEach((tombol) => {
            tombol.addEventListener('click', () => mulaiUbah(tombol));
        });

        btnBatal.addEventListener('click', resetForm);
    }
</script>
@endsection