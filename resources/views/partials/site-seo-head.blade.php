@php
    $seo = $seo ?? [];
    $seoTitle = $seo['title'] ?? trim($__env->yieldContent('title')) ?: \App\Support\SiteSeo::BRAND;
    $seoDescription = $seo['description'] ?? trim($__env->yieldContent('meta_description')) ?: 'Rent anything, anytime from trusted local vendors on Rentkia.';
    $seoKeywords = $seo['keywords'] ?? null;
    $canonical = $seo['canonical'] ?? url()->current();
    $ogType = $seo['og_type'] ?? 'website';
    $ogImage = $seo['og_image'] ?? asset('vendor/icons/icon-512.png');
    $robots = $seo['robots'] ?? 'index, follow';
    $jsonLd = $seo['json_ld'] ?? [];
@endphp
<title>{{ $seoTitle }}</title>
<meta name="application-name" content="{{ \App\Support\SiteSeo::BRAND }}">
<meta name="apple-mobile-web-app-title" content="{{ \App\Support\SiteSeo::BRAND }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="description" content="{{ $seoDescription }}">
@if($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta property="og:site_name" content="{{ \App\Support\SiteSeo::BRAND }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="en_IN">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
<meta name="theme-color" content="#059669">
@foreach($jsonLd as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
@stack('seo')
