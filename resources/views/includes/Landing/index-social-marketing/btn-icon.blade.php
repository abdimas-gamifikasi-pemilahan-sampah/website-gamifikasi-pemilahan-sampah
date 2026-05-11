@php
$icons = [
    ['icon' => 'facebook', 'icon2' => 'icons', 'title' => 'Facebook', 'color' => 'primary'],
    ['icon' => 'instagram', 'icon2' => 'icons', 'title' => 'Instagram', 'color' => 'danger'],
    ['icon' => 'linkedin', 'icon2' => 'icons', 'title' => 'Linkedin', 'color' => 'secondary'],
    ['icon' => 'twitter', 'icon2' => 'icons', 'title' => 'Twitter', 'color' => 'info'],
    ['icon' => 'gitlab', 'icon2' => 'icons', 'title' => 'Gitlab', 'color' => 'warning'],
    ['icon' => 'youtube', 'icon2' => 'icons', 'title' => 'Youtube', 'color' => 'danger'],
    ['icon' => '', 'icon2' => 'mdi mdi-whatsapp', 'title' => 'Whatsapp', 'color' => 'success'],
    ['icon' => '', 'icon2' => 'fab fa-telegram-plane', 'title' => 'Telegram', 'color' => 'info'],
];
@endphp

@foreach ($icons as $item)
    <li class="list-inline-item mx-2">
        <a href="javascript:void(0)" class="btn btn-icon btn-lg btn-soft-{{ $item['color'] }} rounded-circle">
            @if($item['icon'])
                <i data-feather="{{ $item['icon'] }}" class="{{ $item['icon2'] }}"></i>
            @else
                <i class="{{ $item['icon2'] }}"></i>
            @endif
        </a>
    </li>
@endforeach
