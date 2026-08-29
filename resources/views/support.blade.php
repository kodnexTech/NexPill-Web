@extends('layouts.marketing')
@section('title', 'Support — NexPill')
@section('content')
<section class="legal-hero"><div class="shell narrow"><div class="eyebrow"><span></span>Help center</div><h1>Tell us what you need.</h1><p>For urgent medical concerns, contact a clinician or local emergency service.</p></div></section>
<section class="section"><div class="shell support-grid"><div><h2>We’re here to untangle it.</h2><p class="mt-4 text-slate">Use this form for account, technical, billing or safety feedback. Never include passwords, verification codes or full payment details.</p><div class="support-note"><strong>Typical response</strong><span>Within 1–2 business days</span></div><div class="support-note"><strong>Email</strong><span>support@nexpill.app</span></div></div><form class="support-form" action="{{ route('support.store') }}" method="post">@csrf
@if(session('success'))<div class="success-banner">{{ session('success') }}</div>@endif
@if($errors->any())<div class="error-banner">Please check the highlighted fields and try again.</div>@endif
<div class="form-row"><label>Name<input name="name" value="{{ old('name') }}" required autocomplete="name"></label><label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label></div>
<label>Topic<select name="category" required><option value="general">General question</option><option value="account">Account & privacy</option><option value="technical">Technical issue</option><option value="billing">Billing</option><option value="safety">Safety feedback</option></select></label>
<label>Subject<input name="subject" value="{{ old('subject') }}" required></label><label>How can we help?<textarea name="message" rows="6" required>{{ old('message') }}</textarea></label><button class="button button-dark" type="submit">Send request</button></form></div></section>
@endsection
