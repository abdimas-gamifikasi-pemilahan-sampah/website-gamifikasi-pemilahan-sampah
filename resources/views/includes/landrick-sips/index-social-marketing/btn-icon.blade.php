@php
$icons = [
    [
        'icon' => 'facebook', 
        'icon2' => 'icons', 
        'title' => 'Facebook',
        'color' => 'primary',
    ],
    [
        'icon' => 'instagram', 
        'icon2' => 'icons', 
        'title' => 'Instagram',
        'color' => 'danger',
    ],
    [
        'icon' => 'whatsapp', 
        'icon2' => 'icons', 
        'title' => 'Whatsapp',
        'color' => 'success',
    ],
    [
        'icon' => 'twitter', 
        'icon2' => 'icons', 
        'title' => 'Twitter',
        'color' => 'info',
    ],
];
@endphp

<ul class="text-center mb-0 p-0 list-inline social-icons-row">
    @foreach ($icons as $item)
        <li class="list-inline-item mx-2">
            <a href="javascript:void(0)" class="btn btn-icon btn-lg btn-soft-{{ $item['color'] }} rounded-circle">
                <i class="uil uil-{{ $item['icon'] }} align-middle h5"></i>
            </a>
        </li>
    @endforeach
</ul>
