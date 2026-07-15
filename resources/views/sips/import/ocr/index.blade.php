@extends('partials.layouts.master')

@section('title', 'Import AI/OCR | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Import Setoran via AI / OCR')

@section('content')
<div id="layout-wrapper">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Alur Kerja ── --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-secondary">1</span>
                    <span class="fs-13">Catat di kertas</span>
                </div>
                <i class="ri-arrow-right-line text-muted"></i>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-secondary">2</span>
                    <span class="fs-13">Foto &amp; kirim ke AI</span>
                </div>
                <i class="ri-arrow-right-line text-muted"></i>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-secondary">3</span>
                    <span class="fs-13">Simpan Excel dari AI</span>
                </div>
                <i class="ri-arrow-right-line text-muted"></i>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background-color: #7030a0;">4</span>
                    <span class="fs-13 fw-semibold">Upload &amp; review di sini</span>
                </div>
                <i class="ri-arrow-right-line text-muted"></i>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-success">5</span>
                    <span class="fs-13">Simpan ke database</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ── Upload Form ── --}}
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="ri-upload-cloud-2-line" style="color: #7030a0;"></i>
                    <h5 class="card-title mb-0">Upload File Excel dari AI</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" action="{{ route('sips.import.ocr.preview') }}" enctype="multipart/form-data"
                          class="grow d-flex flex-column">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file"
                                   class="form-control @error('file') is-invalid @enderror"
                                   accept=".xlsx,.xls" required>
                            <div class="form-text">Format: .xlsx atau .xls — maks 10 MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info py-2 fs-13">
                            <i class="ri-information-line me-1"></i>
                            Pastikan file Excel dari AI menggunakan header kolom sesuai template:
                            <code>tanggal, nama_penyetor, rt, rw, status_pemilahan, jenis_sampah, berat_kg, catatan</code>
                        </div>

                        <div class="mt-auto">
                            <button type="submit" class="btn w-100 text-white" style="background-color: #7030a0;">
                                <i class="ri-eye-line me-1"></i> Preview &amp; Verifikasi Data
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('sips.import.setoran.template', 'ocr') }}"
                       class="btn btn-sm btn-outline-secondary w-100">
                        <i class="ri-download-line me-1"></i> Unduh Template Excel OCR
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Prompt Template ── --}}
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-sparkling-line" style="color: #7030a0;"></i>
                        <h5 class="card-title mb-0">Prompt untuk ChatGPT</h5>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="btnCopyPrompt" onclick="copyPrompt()">
                        <i class="ri-clipboard-line me-1"></i> Salin
                    </button>
                </div>
                <div class="card-body p-0">
                    <pre id="promptText" class="m-0 p-3 bg-light rounded-bottom fs-12 text-dark" style="white-space: pre-wrap; word-break: break-word; min-height: 220px;">Tolong konversikan catatan pengangkutan sampah di foto ini menjadi file Excel dengan kolom berikut:
- tanggal (format YYYY-MM-DD)
- nama_penyetor (nama lengkap sesuai tulisan)
- rt (nomor RT, hanya angka)
- rw (nomor RW, hanya angka)
- status_pemilahan (isi "dipilah" atau "tidak_dipilah")
- jenis_sampah (nama jenis sampah; kosongkan jika tidak dipilah)
- berat_kg (berat dalam kg, angka desimal)
- catatan (keterangan tambahan, boleh kosong)

Aturan penting:
1. Satu baris = satu jenis sampah dari satu orang
2. Jika seseorang menyetorkan lebih dari satu jenis sampah, buat baris terpisah untuk setiap jenisnya
3. Jika nama tidak jelas atau tidak terbaca, tulis "???"
4. Jika tanggal tidak disebutkan per baris, gunakan tanggal di kepala halaman
5. Kembalikan HANYA baris data tabel (baris pertama = header kolom, baris berikutnya = data)
6. Jangan tambahkan penjelasan atau teks lain di luar tabel</pre>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Buka di AI ── --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="ri-robot-line" style="color: #7030a0;"></i>
                <h5 class="card-title mb-0">Buka Langsung di AI</h5>
            </div>
            <span class="badge text-white fs-11" style="background-color:#7030a0;">Prompt sudah terisi otomatis</span>
        </div>
        <div class="card-body">
            <p class="text-muted fs-13 mb-3">
                Klik tombol di bawah — AI akan terbuka di tab baru dengan prompt yang sudah terisi.
                Tinggal <strong>upload foto</strong> catatan dan tekan <em>kirim</em>.
            </p>

            {{-- Tombol Buka ChatGPT --}}
            <div class="d-flex flex-wrap gap-3 mb-3">
                @php $customChatGpt = $chatGptLink ?? ''; @endphp

                @if($customChatGpt)
                    <a href="{{ $customChatGpt }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-success d-flex align-items-center gap-2 px-4">
                        <i class="ri-openai-line fs-5"></i>
                        <div class="text-start">
                            <div class="fw-semibold">Buka Custom GPT</div>
                            <div class="fs-11 opacity-75">AI sudah diprogram khusus</div>
                        </div>
                    </a>
                @else
                    <button onclick="openChatGPT()" type="button"
                            class="btn btn-outline-dark d-flex align-items-center gap-2 px-4">
                        <i class="ri-openai-line fs-5"></i>
                        <div class="text-start">
                            <div class="fw-semibold">Buka ChatGPT</div>
                            <div class="fs-11 text-muted">Prompt terisi, upload foto & kirim</div>
                        </div>
                    </button>
                @endif
            </div>

            @if(!$customChatGpt)
            <div class="alert alert-light border fs-12 py-2 mb-3">
                <i class="ri-lightbulb-line text-warning me-1"></i>
                <strong>Tips:</strong> Buat <strong>Custom GPT</strong> dengan prompt yang sudah terprogram permanen —
                pengguna tidak perlu lagi menyalin prompt, cukup upload foto.
                Admin bisa menyimpan link Custom GPT di bawah.
            </div>
            @endif

            {{-- Admin: simpan link Custom GPT --}}
            @if(auth()->user()->isAdmin())
            <div class="border-top pt-3 mt-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ri-settings-3-line text-muted"></i>
                    <span class="fw-semibold fs-13">Konfigurasi Link Custom GPT (Admin)</span>
                    <button class="btn btn-sm btn-link fs-12 p-0 ms-1 text-muted" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAiLinks">
                        atur link
                    </button>
                </div>
                <div class="collapse {{ $customChatGpt ? 'show' : '' }}" id="collapseAiLinks">
                    <form method="POST" action="{{ route('sips.import.ocr.save-ai-links') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-10">
                            <label class="form-label fs-12 mb-1">Link Custom GPT</label>
                            <input type="url" name="chatgpt_ocr_link" class="form-control form-control-sm"
                                   placeholder="https://chatgpt.com/g/g-xxxxx-nama-gpt"
                                   value="{{ $customChatGpt }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-secondary w-100">
                                <i class="ri-save-line me-1"></i> Simpan
                            </button>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 mb-0">
                                Kosongkan dan simpan untuk kembali ke mode tombol generik dengan prompt otomatis.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Tips ── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0"><i class="ri-lightbulb-line me-2 text-warning"></i> Tips Penggunaan</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <div class="shrink-0 text-muted fs-5"><i class="ri-camera-line"></i></div>
                        <div>
                            <div class="fw-semibold fs-13">Foto yang baik</div>
                            <p class="text-muted fs-13 mb-0">Pastikan pencahayaan cukup, foto tidak buram, dan seluruh catatan terlihat. AI lebih akurat dengan tulisan yang jelas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <div class="shrink-0 text-muted fs-5"><i class="ri-file-warning-line"></i></div>
                        <div>
                            <div class="fw-semibold fs-13">Nama tidak terbaca</div>
                            <p class="text-muted fs-13 mb-0">Jika AI menulis "???" untuk nama penyetor, baris tersebut akan diblokir. Anda perlu mengisi nama secara manual di halaman preview.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <div class="shrink-0 text-muted fs-5"><i class="ri-recycle-line"></i></div>
                        <div>
                            <div class="fw-semibold fs-13">Jenis sampah baru</div>
                            <p class="text-muted fs-13 mb-0">Sampah yang belum terdaftar di sistem akan dihitung dengan flat rate. Anda dapat menyimpannya sebagai sinonim agar dikenali di import berikutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('sips.import.ocr.panduan') }}" class="btn btn-outline-secondary">
            <i class="ri-question-line me-1"></i> Panduan Penggunaan
        </a>
        <a href="{{ route('sips.import.setoran.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali ke Import Setoran
        </a>
    </div>

</div>
@endsection

@section('js')
<script>
function copyPrompt() {
    const text = document.getElementById('promptText').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('btnCopyPrompt');
        btn.innerHTML = '<i class="ri-check-line me-1"></i> Tersalin!';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = '<i class="ri-clipboard-line me-1"></i> Salin';
            btn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 2000);
    });
}

function openChatGPT() {
    const prompt = document.getElementById('promptText').textContent.trim();
    window.open('https://chatgpt.com/?q=' + encodeURIComponent(prompt), '_blank', 'noopener,noreferrer');
}


</script>
@endsection
