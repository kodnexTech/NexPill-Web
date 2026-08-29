@extends('layouts.marketing')
@section('title', $document->title.' — NexPill')
@section('content')
<section class="legal-hero"><div class="shell narrow"><div class="eyebrow"><span></span>{{ ucfirst($document->type) }}</div><h1>{{ $document->title }}</h1><p>Version {{ $document->version }} · {{ $document->published_at?->format('d F Y') }}</p></div></section>
<section class="legal-body"><div class="shell narrow prose whitespace-pre-line">{{ $document->content }}</div></section>
@endsection
