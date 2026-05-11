@php
$partners = [
    'facebook',
    'instagram',
    'tinder',
    'whatsapp',
    'google',
    'dribbble',
];
@endphp

<div class="row justify-content-center">
    @foreach ($partners as $partner)
        <div class="col-lg-2 col-md-3 col-6 text-center py-4">
            <a href="javascript:void(0)" class="d-block">
                <img src="{{ asset('assets/images/client/' . $partner . '.svg') }}" class="avatar avatar-ex-md" alt="">
            </a>
        </div><!--end col-->
    @endforeach
</div><!--end row-->
