<!-- Simplebar Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}">
<!-- Swiper Css -->
<link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
<!-- Nouislider Css -->
<link href="{{ asset('assets/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
<!-- Bootstrap Css -->
<link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
<!--icons css-->
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
<!-- App Css-->
<link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

<style>
/* Ensure custom SIPS single-level links collapse cleanly in icon sidebar mode. */
[data-sidebar="icon"] .pe-app-sidebar .pe-main-menu .pe-slide > .pe-nav-link .pe-nav-content,
[data-sidebar="icon"] .pe-app-sidebar .pe-main-menu .pe-slide > .pe-nav-link .pe-nav-arrow {
	display: none;
}

[data-sidebar="icon"] .pe-app-sidebar .pe-main-menu .pe-slide > .pe-nav-link {
	justify-content: center;
}

/* Shared SIPS surfaces for consistent light/dark experience. */
.sips-soft-card {
	background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%);
	border: 1px solid #e7eef9;
}

.sips-summary-card {
	background-color: var(--bs-tertiary-bg);
}

.leaderboard-outline-card {
	border: 1px solid rgba(148, 163, 184, 0.22);
	box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
	background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.78));
	border-radius: 1rem;
}

.leaderboard-outline-card .badge {
	border-width: 1px;
	border-style: solid;
}

[data-bs-theme="dark"] .leaderboard-outline-card {
	background: linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(17, 24, 39, 0.78));
	border-color: rgba(255, 255, 255, 0.1);
	box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .leaderboard-outline-card .badge {
	opacity: 0.98;
}

.sips-page-shell .card-title,
.sips-page-shell h4,
.sips-page-shell h5,
.sips-page-shell h6 {
	letter-spacing: -0.01em;
}

.sips-page-shell .card-header {
	padding-top: 1rem;
	padding-bottom: 1rem;
}

.sips-page-shell .table > :not(caption) > * > * {
	padding-top: 0.9rem;
	padding-bottom: 0.9rem;
}

[data-bs-theme="dark"] .sips-page-shell .card {
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

[data-bs-theme="dark"] .sips-page-shell .card-header {
	border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}

[data-bs-theme="dark"] .sips-soft-card {
	background: linear-gradient(135deg, #1a202c 0%, #141a24 100%);
	border-color: #2a3342;
}

[data-bs-theme="dark"] .sips-summary-card {
	background-color: rgba(255, 255, 255, 0.03) !important;
}

[data-bs-theme="dark"] #layout-wrapper .table-light td {
	background-color: rgba(255, 255, 255, 0.04) !important;
	color: var(--bs-body-color) !important;
}

[data-bs-theme="dark"] #layout-wrapper .table-light th,
[data-bs-theme="dark"] #layout-wrapper .sips-table-head th {
	background-color: rgba(255, 255, 255, 0.05) !important;
	color: #ffffff !important;
}

[data-bs-theme="dark"] #layout-wrapper .sips-table-head th {
	font-weight: 600;
	border-color: rgba(255, 255, 255, 0.1) !important;
}

.sips-pagination .page-link {
	display: inline-flex;
	align-items: center;
	gap: 0.15rem;
	border-radius: 0.5rem;
	padding-inline: 0.85rem;
}

.sips-pagination .page-item.active .page-link {
	font-weight: 600;
}

[data-bs-theme="dark"] #layout-wrapper .sips-pagination .page-link {
	background-color: rgba(255, 255, 255, 0.05);
	border-color: rgba(255, 255, 255, 0.1);
	color: #e5eefc;
}

[data-bs-theme="dark"] #layout-wrapper .sips-pagination .page-item.disabled .page-link {
	background-color: rgba(255, 255, 255, 0.03);
	color: rgba(229, 238, 252, 0.45);
}

[data-bs-theme="dark"] #layout-wrapper .text-dark {
	color: var(--bs-body-color) !important;
}

[data-bs-theme="dark"] #layout-wrapper .btn.btn-light {
	background-color: rgba(255, 255, 255, 0.06);
	border-color: rgba(255, 255, 255, 0.12);
	color: var(--bs-body-color);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-success-subtle {
	background-color: rgba(25, 135, 84, 0.18) !important;
	color: #7ddc9a !important;
	border: 1px solid rgba(25, 135, 84, 0.35);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-primary-subtle {
	background-color: rgba(13, 110, 253, 0.18) !important;
	color: #8bb8ff !important;
	border: 1px solid rgba(13, 110, 253, 0.35);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-info-subtle {
	background-color: rgba(13, 202, 240, 0.16) !important;
	color: #86e8ff !important;
	border: 1px solid rgba(13, 202, 240, 0.35);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-warning-subtle {
	background-color: rgba(255, 193, 7, 0.16) !important;
	color: #ffd86b !important;
	border: 1px solid rgba(255, 193, 7, 0.32);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-danger-subtle {
	background-color: rgba(220, 53, 69, 0.16) !important;
	color: #ff8f9a !important;
	border: 1px solid rgba(220, 53, 69, 0.32);
}

[data-bs-theme="dark"] #layout-wrapper .badge.bg-warning.text-dark {
	color: #111827 !important;
}

/* Sticky footer — footer spans full viewport width and sits below all content */
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main.app-wrapper {
    flex: 1;
}
</style>
