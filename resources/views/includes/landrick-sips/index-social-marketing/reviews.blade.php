@php
$reviews = [
    [
        'img' => 'assets/images/client/01.jpg', 
        'name' => 'Siti Nurhaliza',
        'title' => 'Warga RW 01',
        'desc' => '" Sistem SIPS sangat membantu saya dalam mengelola sampah rumah tangga. Sekarang saya lebih peduli dengan lingkungan! "',
    ],
    [
        'img' => 'assets/images/client/02.jpg', 
        'name' => 'Budi Santoso',
        'title' => 'Warga RW 02',
        'desc' => '" Papan peringkat membuat saya termotivasi untuk terus berkontribusi. Kompetisi sehat ini luar biasa! "',
    ],
    [
        'img' => 'assets/images/client/03.jpg', 
        'name' => 'Dewi Lestari',
        'title' => 'Ketua RW 03',
        'desc' => '" Dashboard analitik memberikan insights yang berharga untuk program lingkungan kami di tingkat RW. "',
    ],
    [
        'img' => 'assets/images/client/04.jpg', 
        'name' => 'Ahmad Wijaya',
        'title' => 'Warga RW 01',
        'desc' => '" Aplikasi ini mudah digunakan dan sangat bermanfaat untuk keluarga saya. Terima kasih! "',
    ],
    [
        'img' => 'assets/images/client/05.jpg', 
        'name' => 'Rina Puspita',
        'title' => 'Warga RW 02',
        'desc' => '" Saya senang bisa melihat kontribusi saya berkontribusi nyata terhadap lingkungan desa kami. "',
    ],
    [
        'img' => 'assets/images/client/06.jpg', 
        'name' => 'Joko Setiawan',
        'title' => 'Koordinator SIPS',
        'desc' => '" Implementasi SIPS di Desa Banjarsari telah mengubah cara kami mengelola sampah secara signifikan. "',
    ]
];
@endphp

@foreach ($reviews as $item)
    <div class="tiny-slide">
        <div class="d-flex client-testi m-1">
            <img src="{{ asset($item['img']) }}" class="avatar avatar-small client-image rounded shadow" alt="">
            <div class="card flex-1 content p-3 shadow rounded position-relative">
                <ul class="list-unstyled mb-0">
                    <li class="list-inline-item"><i class="mdi mdi-star text-warning"></i></li>
                    <li class="list-inline-item"><i class="mdi mdi-star text-warning"></i></li>
                    <li class="list-inline-item"><i class="mdi mdi-star text-warning"></i></li>
                    <li class="list-inline-item"><i class="mdi mdi-star text-warning"></i></li>
                    <li class="list-inline-item"><i class="mdi mdi-star text-warning"></i></li>
                </ul>
                <p class="text-muted mt-2">{{ $item['desc'] }}</p>
                <h6 class="text-primary">- {{ $item['name'] }} <small class="text-muted">{{ $item['title'] }}</small></h6>
            </div>
        </div>
    </div>
@endforeach
