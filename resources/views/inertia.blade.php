<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

@php
    // SEO из SeoManager (пакет teo/pages); пока пакет не отдаёт данные — дефолты
    $seo = class_exists(\Teo\Pages\Support\SeoManager::class)
        ? \Teo\Pages\Support\SeoManager::current()
        : [];

    $seoTitle = $seo['title'] ?? config('app.name', 'Мой сайт');
    $seoDescription = $seo['description'] ?? '';
    $seoKeywords = $seo['keywords'] ?? '';
    $seoRobots = $seo['robots'] ?? 'index, follow';
    $seoCanonical = $seo['canonical'] ?? url()->current();
    $seoOgTitle = $seo['og_title'] ?? $seoTitle;
    $seoOgDescription = $seo['og_description'] ?? $seoDescription;
    $seoOgUrl = $seo['og_url'] ?? url()->current();
    $seoOgImage = $seo['og_image'] ?? null;
    $seoOgSiteName = $seo['og_site_name'] ?? config('app.name');
    $seoOgLocale = $seo['og_locale'] ?? 'ru_RU';
    $seoTwitterCard = $seo['twitter_card'] ?? 'summary_large_image';
    $seoTwitterTitle = $seo['twitter_title'] ?? $seoOgTitle;
    $seoTwitterDescription = $seo['twitter_description'] ?? $seoDescription;
    $seoTwitterImage = $seo['twitter_image'] ?? $seoOgImage;
    $siteFavicon = $seo['favicon'] ?? 'favicon.ico';
@endphp

    <!-- SEO метатеги -->
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <!-- Open Graph -->
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:site_name" content="{{ $seoOgSiteName }}">
    <meta property="og:locale" content="{{ $seoOgLocale }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoOgUrl }}">
    @if($seoOgImage)
        <meta property="og:image" content="{{ $seoOgImage }}">
    @endif

    <!-- Twitter Card -->
    <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    <meta name="twitter:title" content="{{ $seoTwitterTitle }}">
    <meta name="twitter:description" content="{{ $seoTwitterDescription }}">
    @if($seoTwitterImage)
        <meta name="twitter:image" content="{{ $seoTwitterImage }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset($seo['favicon_svg'] ?? 'favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset($siteFavicon) }}">

    <!-- Structured Data (JSON-LD) -->
    @php($seoJsonLd = $seo['json_ld'] ?? [])
    @if(is_array($seoJsonLd) && $seoJsonLd !== [])
        @foreach($seoJsonLd as $schema)
            @if(is_array($schema))
                <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
            @endif
        @endforeach
    @endif

    <!-- Vite -->
    @vite(['resources/js/public/app.js'])
</head>
<body>
@inertia
</body>
</html>