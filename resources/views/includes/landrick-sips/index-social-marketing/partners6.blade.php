@php
$partners = [
    [
        'img' => 'assets/images/client/facebook.svg', 
        'style' => 'col-lg-2 col-md-2 col-6 text-center',
    ],
    [
        'img' => 'assets/images/client/instagram.svg', 
        'style' => 'col-lg-2 col-md-2 col-6 text-center',
    ],
    [
        'img' => 'assets/images/client/whatsapp.svg', 
        'style' => 'col-lg-2 col-md-2 col-6 text-center pt-5 pt-sm-0',
    ],
    [
        'img' => 'assets/images/client/google.svg', 
        'style' => 'col-lg-2 col-md-2 col-6 text-center pt-5 pt-sm-0',
    ],
];
@endphp

<div class="row align-items-center text-center">
    <div class="col-12 mb-4">
        <h5 class="mb-0 fw-normal text-muted">Ikuti kami di media sosial untuk update terbaru SIPS</h5>
    </div>
</div>

<div class="row">
    @foreach ($partners as $item)
        <div class="{{ $item['style'] }}">
            <img src="{{ asset($item['img']) }}" class="avatar avatar-ex-sm" alt="">
        </div>
    @endforeach
</div>
