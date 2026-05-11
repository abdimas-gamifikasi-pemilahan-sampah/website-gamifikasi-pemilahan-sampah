@php
$reviews = [
    [
        'name' => 'Thomas Israel',
        'title' => 'CEO',
        'image' => asset('assets/images/testimonial/client/1.jpg'),
        'comment' => 'This is an awesome platform to work with.',
        'rating' => 5
    ],
    [
        'name' => 'Barbara McIntosh',
        'title' => 'MD',
        'image' => asset('assets/images/testimonial/client/2.jpg'),
        'comment' => 'Absolutely wonderful! Best experience ever!',
        'rating' => 5
    ],
    [
        'name' => 'Carl Oliver',
        'title' => 'PA',
        'image' => asset('assets/images/testimonial/client/3.jpg'),
        'comment' => 'Great features and amazing support team.',
        'rating' => 5
    ],
    [
        'name' => 'Christa Smith',
        'title' => 'Manager',
        'image' => asset('assets/images/testimonial/client/4.jpg'),
        'comment' => 'The interface is very easy to use and intuitive.',
        'rating' => 5
    ],
    [
        'name' => 'Dean Tolle',
        'title' => 'Developer',
        'image' => asset('assets/images/testimonial/client/5.jpg'),
        'comment' => 'Excellent service with perfect documentation!',
        'rating' => 5
    ],
    [
        'name' => 'Jill Webb',
        'title' => 'Designer',
        'image' => asset('assets/images/testimonial/client/6.jpg'),
        'comment' => 'Simply outstanding! Highly recommended.',
        'rating' => 5
    ]
];
@endphp

<div class="tiny-slide-one">
    @foreach ($reviews as $review)
        <div class="tiny-slide-item">
            <div class="card review rounded-lg p-4 shadow-none border">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $review['image'] }}" alt="" class="avatar avatar-medium rounded-circle">
                    <div class="ms-3">
                        <h6 class="mb-0">{{ $review['name'] }}</h6>
                        <p class="text-muted mb-0 small">{{ $review['title'] }}</p>
                    </div>
                </div>
                <p class="text-muted">{{ $review['comment'] }}</p>
                <div class="d-flex align-items-center">
                    @for ($i = 0; $i < $review['rating']; $i++)
                        <i class="mdi mdi-star text-warning"></i>
                    @endfor
                </div>
            </div>
        </div>
    @endforeach
</div>
