@extends('partials.layouts.master')

@section('title', 'Panduan Import OCR | SIPS')
@section('title-sub', 'Panduan')
@section('pagetitle', 'Panduan Import Setoran via AI / OCR')

@section('content')
<div id="layout-wrapper">

    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- Intro --}}
            <div class="card mb-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded p-3" style="background-color:#f3e8ff;">
                        <i class="ri-sparkling-line fs-2" style="color:#7030a0;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Cara Baru Mencatat Setoran Sampah</h5>
                        <p class="text-muted mb-0 fs-13">
                            Petugas tidak perlu langsung input di website saat pengangkutan.
                            Cukup catat di kertas, foto, minta AI ubah ke Excel, lalu upload ke SIPS.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Step 1: Catat di kertas --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-white" style="background-color:#7030a0; width:28px; height:28px; line-height:20px; font-size:14px;">1</span>
                    <h6 class="card-title mb-0">Catat di Kertas saat Pengangkutan</h6>
                </div>
                <div class="card-body">
                    <p class="fs-13 text-muted mb-3">Gunakan buku atau kertas biasa. Tulis informasi berikut untuk setiap warga:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered fs-13">
                            <thead class="table-light">
                                <tr><th>Informasi</th><th>Contoh</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Tanggal</td><td>9 Juli 2026</td><td>Cukup tulis di kepala halaman sekali</td></tr>
                                <tr><td>Nama</td><td>Bu Siti Rahayu</td><td>Tulis nama lengkap agar sistem mengenali</td></tr>
                                <tr><td>RT/RW</td><td>RT 02 / RW 06</td><td>Nomor RT dan RW warga</td></tr>
                                <tr><td>Jenis Sampah</td><td>kardus, botol, koran</td><td>Tulis per jenis jika lebih dari satu</td></tr>
                                <tr><td>Berat</td><td>3,5 kg</td><td>Ukur dengan timbangan saat itu</td></tr>
                                <tr><td>Dipilah?</td><td>✓ / ✗</td><td>Centang jika sampah sudah dipilah</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info py-2 fs-13">
                        <i class="ri-lightbulb-line me-1"></i>
                        <strong>Tips:</strong> Jika warga menyetor lebih dari satu jenis sampah, tulis di baris terpisah
                        agar sistem bisa menghitung nilai masing-masing dengan benar.
                    </div>
                </div>
            </div>

            {{-- Step 2: Foto --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-white" style="background-color:#7030a0; width:28px; height:28px; line-height:20px; font-size:14px;">2</span>
                    <h6 class="card-title mb-0">Foto Catatan dengan HP</h6>
                </div>
                <div class="card-body fs-13">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <i class="ri-sun-line text-warning fs-5 mt-1"></i>
                                <div><strong>Pencahayaan cukup</strong><br>
                                    Foto di tempat terang. Hindari bayangan jatuh di atas tulisan.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <i class="ri-focus-3-line text-success fs-5 mt-1"></i>
                                <div><strong>Tidak buram</strong><br>
                                    Pegang HP dengan stabil. Pastikan semua tulisan terlihat tajam.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <i class="ri-file-text-line text-primary fs-5 mt-1"></i>
                                <div><strong>Seluruh halaman</strong><br>
                                    Satu foto per halaman. Semua baris harus masuk dalam frame.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Kirim ke AI --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-white" style="background-color:#7030a0; width:28px; height:28px; line-height:20px; font-size:14px;">3</span>
                    <h6 class="card-title mb-0">Proses Foto dengan ChatGPT</h6>
                </div>
                <div class="card-body">

                    {{-- Cara cepat: tombol langsung --}}
                    <div class="alert alert-success py-2 fs-13 mb-3">
                        <i class="ri-sparkling-line me-1"></i>
                        <strong>Cara paling cepat:</strong>
                        Di halaman <a href="{{ route('sips.import.ocr.index') }}" class="alert-link">Import AI / OCR</a>,
                        klik tombol <strong>"Buka ChatGPT"</strong> —
                        prompt sudah terisi otomatis. Tinggal upload foto dan tekan kirim.
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2"><i class="ri-openai-line me-1 text-success"></i> ChatGPT (manual)</div>
                        <ol class="fs-13 ps-3 mb-0">
                            <li>Buka <strong>chatgpt.com</strong> di browser</li>
                            <li>Klik ikon klip untuk upload foto</li>
                            <li>Upload foto catatan</li>
                            <li>Salin prompt dari halaman <a href="{{ route('sips.import.ocr.index') }}">Import OCR</a> dan tempel</li>
                            <li>Tekan Enter</li>
                            <li>Minta ChatGPT: <em>"Tolong simpan sebagai file Excel"</em> — lalu unduh</li>
                        </ol>
                    </div>

                    {{-- Custom GPT --}}
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-semibold fs-13 mb-2">
                            <i class="ri-robot-line me-1" style="color:#7030a0;"></i>
                            Pengalaman Terbaik: Custom GPT
                        </div>
                        <p class="fs-13 text-muted mb-2">
                            Admin dapat membuat <strong>Custom GPT</strong> yang sudah diprogram permanen dengan instruksi SIPS —
                            pengguna cukup upload foto tanpa perlu menyalin prompt sama sekali.
                        </p>
                        <ol class="fs-13 ps-3 mb-0 text-muted">
                            <li>Buka <strong>chatgpt.com → Explore GPTs → Create</strong></li>
                            <li>Isi nama: misal "SIPS OCR Banjarsari"</li>
                            <li>Di kolom <em>Instructions</em>, tempel prompt dari halaman Import OCR</li>
                            <li>Tambahkan: <em>"Selalu kembalikan output dalam format tabel Excel tanpa penjelasan tambahan"</em></li>
                            <li>Simpan dan klik <strong>Share link</strong></li>
                            <li>Tempel link di kolom <strong>Konfigurasi Link Custom GPT</strong> di halaman Import OCR</li>
                        </ol>
                    </div>

                    <div class="alert alert-warning py-2 fs-13 mt-3 mb-0">
                        <i class="ri-error-warning-line me-1"></i>
                        <strong>Pastikan header kolom Excel dari ChatGPT sesuai:</strong>
                        <code>tanggal, nama_penyetor, rt, rw, status_pemilahan, jenis_sampah, berat_kg, catatan</code>
                        (huruf kecil, pakai underscore, bukan spasi). Jika tidak sesuai, upload akan gagal.
                    </div>
                </div>
            </div>

            {{-- Step 4: Upload --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-white" style="background-color:#7030a0; width:28px; height:28px; line-height:20px; font-size:14px;">4</span>
                    <h6 class="card-title mb-0">Upload ke SIPS dan Review</h6>
                </div>
                <div class="card-body fs-13">
                    <ol class="ps-3">
                        <li class="mb-2">Buka halaman <a href="{{ route('sips.import.ocr.index') }}"><strong>Import AI / OCR</strong></a></li>
                        <li class="mb-2">Klik <em>Pilih File</em> dan pilih file Excel dari AI</li>
                        <li class="mb-2">Klik <strong>Preview &amp; Verifikasi Data</strong></li>
                        <li class="mb-2">
                            Di halaman preview, periksa setiap baris:
                            <ul class="mt-1">
                                <li><span class="badge bg-success">Otomatis</span> — nama warga cocok, tidak perlu tindakan</li>
                                <li><span class="badge bg-warning text-dark">Review</span> — pilih warga yang tepat dari dropdown</li>
                                <li><span class="badge bg-secondary">Baru</span> — warga belum terdaftar, isi nama penyetor</li>
                                <li><span class="badge bg-danger">Error</span> — nama tidak terbaca, isi nama atau centang "Lewati"</li>
                            </ul>
                        </li>
                        <li class="mb-2">Periksa kolom <em>Jenis Sampah</em>. Jika ada tanda
                            <span class="badge text-white" style="background-color:#7030a0;">Flat</span>,
                            pilih tarif yang benar dari dropdown</li>
                        <li class="mb-2">Centang <strong>Sinonim</strong> pada baris yang jenisnya perlu diingat sistem untuk import berikutnya</li>
                        <li>Klik <strong>Konfirmasi &amp; Simpan</strong></li>
                    </ol>
                </div>
            </div>

            {{-- FAQ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-question-line me-2"></i>Pertanyaan Umum</h6>
                </div>
                <div class="card-body">
                    <div class="accordion accordion-flush fs-13" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 fs-13" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Nama warga tidak cocok di sistem, apa yang terjadi?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body py-2 text-muted">
                                    Sistem mencoba mencocokkan nama dengan cerdas — termasuk mengabaikan sapaan seperti
                                    "Bu", "Pak", "Ibu", dan menoleransi typo ringan.
                                    Jika tidak cocok, admin bisa memilih warga yang benar dari dropdown di halaman preview,
                                    atau isi nama baru untuk penyetor yang belum terdaftar.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 fs-13" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Jenis sampah tidak dikenal sistem, apakah data hilang?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body py-2 text-muted">
                                    Tidak. Jenis yang tidak dikenal dihitung dengan <strong>flat rate</strong> sementara
                                    dan ditandai untuk review. Admin bisa menetapkan tarif yang benar setelah import,
                                    dan menyimpannya sebagai sinonim agar dikenal di import berikutnya.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 fs-13" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Bagaimana jika AI salah membaca tulisan?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body py-2 text-muted">
                                    Halaman preview menampilkan data asli dari Excel AI. Admin bisa memperbaiki
                                    nama, berat, jenis, dan status pemilahan langsung di halaman itu sebelum disimpan.
                                    Baris yang tidak bisa diperbaiki bisa dilewati dan diinput manual terpisah.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 fs-13" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Apakah bisa input ulang data yang sama dua kali?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body py-2 text-muted">
                                    Sistem tidak otomatis mencegah duplikasi. Pastikan satu file Excel hanya diupload
                                    sekali. Jika terlanjur ganda, admin dapat menghapus setoran duplikat dari
                                    halaman daftar setoran.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-4">
                <a href="{{ route('sips.import.ocr.index') }}" class="btn text-white px-5" style="background-color:#7030a0;">
                    <i class="ri-upload-cloud-2-line me-1"></i> Mulai Import OCR
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
