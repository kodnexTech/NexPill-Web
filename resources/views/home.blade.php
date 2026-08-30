@extends('layouts.marketing')

@section('content')
<section class="hero overflow-hidden">
    <div class="shell grid items-center gap-14 py-16 lg:grid-cols-[1.02fr_.98fr] lg:py-24">
        <div>
            <div class="eyebrow"><span></span>Smarter medication routines</div>
            <h1>Your medicines.<br><em>On time. Together.</em></h1>
            <p class="hero-copy">NexPill brings dose reminders, refill tracking, appointments and family support into one clear daily experience—private, practical and easy to follow.</p>
            <div class="mt-9 flex flex-wrap gap-3"><a class="button button-coral" href="{{ route('support') }}">Join the early access list</a><a class="button button-ghost" href="#how-it-works">See how it works <span>↓</span></a></div>
            <div class="trust-row"><span class="avatar-stack"><i>AM</i><i>RJ</i><i>SK</i></span><span><strong>Built for everyday care</strong><small>Clear reminders · Private records · Family-friendly</small></span></div>
        </div>

        <div class="app-stage" aria-label="NexPill daily dashboard preview">
            <div class="floating-circle circle-a"></div><div class="floating-circle circle-b"></div>
            <img class="hero-brand-art" src="/images/nexpill-icon.png" alt="" width="440" height="440">
            <article class="phone-frame">
                <div class="phone-speaker"></div>
                <div class="phone-screen">
                    <div class="phone-top"><span>9:41</span><span>● ● ◒</span></div>
                    <div class="phone-greeting"><div><small>GOOD MORNING</small><h2>Hi, Anita <span>👋</span></h2></div><button aria-label="Notifications">●</button></div>
                    <div class="progress-card">
                        <div><small>TODAY'S PROGRESS</small><strong>2 of 4</strong><span>doses taken</span></div>
                        <div class="progress-ring"><b>50%</b></div>
                    </div>
                    <div class="dose-heading"><strong>Up next</strong><span>View schedule</span></div>
                    <div class="dose-card"><span class="pill-icon coral">✦</span><div><strong>Metformin</strong><small>500 mg · With food</small></div><time>12:30 PM</time></div>
                    <div class="dose-card muted"><span class="pill-icon mint">●</span><div><strong>Vitamin D3</strong><small>1 capsule</small></div><time>8:00 PM</time></div>
                    <button class="phone-action">+ Add medicine</button>
                </div>
            </article>
            <aside class="floating-note"><span>✓</span><div><strong>Dose logged</strong><small>Right on time</small></div></aside>
            <aside class="floating-refill"><small>REFILL CHECK</small><strong>7 doses left</strong><span>We'll remind you early</span></aside>
        </div>
    </div>
</section>

<section class="capability-strip" aria-label="NexPill capabilities">
    <div class="shell capability-grid">
        <div><span>01</span><strong>Smart reminders</strong><small>Take, snooze or skip</small></div>
        <div><span>02</span><strong>Refill signals</strong><small>Know before supply runs low</small></div>
        <div><span>03</span><strong>Appointments</strong><small>Keep care dates together</small></div>
        <div><span>04</span><strong>Family circle</strong><small>Consent-based support</small></div>
    </div>
</section>

<section class="section" id="features">
    <div class="shell">
        <div class="section-kicker">Made for the whole care journey</div>
        <div class="section-title-row"><h2>Less remembering.<br><em>More living.</em></h2><p>NexPill handles the small details that are easy to miss and important to get right.</p></div>
        <div class="feature-grid">
            <article class="feature-card feature-large"><span class="feature-number">01</span><div class="feature-icon">◷</div><h3>Reminders that adapt</h3><p>Flexible schedules, snooze controls and clear dose status—built around your timezone and routine.</p><div class="schedule-strip"><span>8:00<small>Taken</small></span><span class="active">12:30<small>Next</small></span><span>20:00<small>Later</small></span></div></article>
            <article class="feature-card"><span class="feature-number">02</span><div class="feature-icon peach">⌁</div><h3>Refills before the last pill</h3><p>See supply at a glance and get a nudge while there is still time to reorder.</p></article>
            <article class="feature-card"><span class="feature-number">03</span><div class="feature-icon blue">♡</div><h3>Care circles, with consent</h3><p>Invite a caregiver or manage medicines for someone who does not use the app.</p></article>
            <article class="feature-card feature-wide"><span class="feature-number">04</span><div><h3>History you can actually use</h3><p>Understand adherence, symptoms and patterns. Export a clear report before the next appointment.</p></div><div class="mini-chart"><i style="height:42%"></i><i style="height:70%"></i><i style="height:55%"></i><i style="height:88%"></i><i style="height:76%"></i><i style="height:96%"></i><i style="height:82%"></i></div></article>
        </div>
    </div>
</section>

<section class="family-section" id="families">
    <div class="shell grid items-center gap-14 py-20 lg:grid-cols-2">
        <div class="family-visual"><div class="family-card"><small>FAMILY CIRCLE</small><h3>Everyone sees what matters.</h3><div class="family-person"><span>RK</span><div><strong>Raj Kapoor</strong><small>All doses taken today</small></div><b>ON TRACK</b></div><div class="family-person"><span>MK</span><div><strong>Maya Kapoor</strong><small>1 dose needs attention</small></div><b class="warn">CHECK IN</b></div></div></div>
        <div><div class="eyebrow light"><span></span>Care without hovering</div><h2>Support that feels like support.</h2><p>Share only what is useful. Family members can view adherence and send a gentle nudge, while private notes and appointments stay with the account owner.</p><ul class="check-list"><li>Role-based caregiver and viewer access</li><li>Managed profiles for children or older adults</li><li>Clear alerts for missed and overdue doses</li></ul></div>
    </div>
</section>

<section class="section" id="how-it-works"><div class="shell"><div class="center-heading"><div class="section-kicker">Simple from day one</div><h2>Three steps to a calmer routine.</h2></div><div class="steps"><article><b>1</b><h3>Add what you take</h3><p>Set strength, schedule, instructions and refill level.</p></article><article><b>2</b><h3>Follow today's plan</h3><p>Take, snooze or skip from a focused daily timeline.</p></article><article><b>3</b><h3>Learn from the pattern</h3><p>Review adherence and share a useful report with care teams.</p></article></div></div></section>

<section class="privacy-feature">
    <div class="shell privacy-feature-card">
        <div class="privacy-art">
            <div class="privacy-shield-container">
                <div class="privacy-shield-badge">
                    <div class="privacy-shield-icon">
                        <svg class="h-8 w-8 text-[#20D6B6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="privacy-shield-text">
                        <strong>Zero-Knowledge Vault</strong>
                        <small>Owner-Scoped Encryption</small>
                    </div>
                    <span class="privacy-status-pill">Active & Protected</span>
                </div>

                <div class="privacy-mini-grid">
                    <div class="privacy-mini-item">
                        <span class="privacy-mini-icon">🔒</span>
                        <div>
                            <strong>No Data Selling</strong>
                            <small>100% telemetry & tracker free</small>
                        </div>
                    </div>
                    <div class="privacy-mini-item">
                        <span class="privacy-mini-icon">👥</span>
                        <div>
                            <strong>Granular Consent</strong>
                            <small>Revoke family access anytime in 1 tap</small>
                        </div>
                    </div>
                    <div class="privacy-mini-item">
                        <span class="privacy-mini-icon">🗑️</span>
                        <div>
                            <strong>1-Click Hard Purge</strong>
                            <small>Permanent data erasure on demand</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="eyebrow light"><span></span>Private by design</div>
            <h2>Your care data stays under your control.</h2>
            <p>Authenticated access, owner-scoped records, private prescription storage and permanent account deletion are built into the platform—not added as an afterthought.</p>
            <div class="privacy-points">
                <span>✓ Secure account access</span>
                <span>✓ Consent-based family sharing</span>
                <span>✓ Export and deletion controls</span>
            </div>
            <a class="button button-light" href="{{ route('privacy') }}">Read our privacy approach</a>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="shell">
        <div class="cta-card">
            <div>
                <span class="section-kicker">Ready when you are</span>
                <h2>Make medication care feel lighter.</h2>
                <p>Join early access and help shape a safer, calmer NexPill experience.</p>
            </div>
            <a class="button button-coral" href="{{ route('support') }}">Request early access</a>
        </div>
    </div>
</section>
@endsection
