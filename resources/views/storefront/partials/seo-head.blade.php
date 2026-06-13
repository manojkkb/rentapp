@php
    $seo = $seo ?? [];
    $seoTitle = $seo['title'] ?? null;
    $seoDescription = $seo['description'] ?? null;
    $seoKeywords = $seo['keywords'] ?? null;
    $canonical = $seo['canonical'] ?? url()->current();
    $ogType = $seo['og_type'] ?? 'website';
    $ogImage = $seo['og_image'] ?? ($vendor->logo_url ?? null);
    $robots = $seo['robots'] ?? 'index, follow';
    $jsonLd = $seo['json_ld'] ?? [];
@endphp
<title>{{ $seoTitle ?? trim($__env->yieldContent('title')) ?: $vendor->name }}</title>
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
@if($seoDescription)
    <meta name="description" content="{{ $seoDescription }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
@endif
@if($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta property="og:title" content="{{ $seoTitle ?? $vendor->name }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle ?? $vendor->name }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
@foreach($jsonLd as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
@stack('seo')
