<?php

use App\Core\View;

$pageTitle = 'Vipasa Yoga — Find your practice, your people, your peace';
$pageCss = 'landing';
$hideFloatingThemeToggle = true;

$reviews = [
    ['name' => 'Ananya R.', 'rating' => 5, 'text' => 'Best yoga booking experience I\'ve had online — genuinely simple.'],
    ['name' => 'Karthik S.', 'rating' => 5, 'text' => 'Instructors are attentive, not just reading off a script.'],
    ['name' => 'Fatima Z.', 'rating' => 4, 'text' => 'Rescheduling a session took less than a minute.'],
    ['name' => 'Wei L.', 'rating' => 5, 'text' => 'The dashboard and invoices alone make this worth it.'],
];

$blogPosts = [
    ['title' => '5 Morning Rituals for a Calmer Day', 'excerpt' => 'Small, sustainable habits that set the tone before you even open your laptop.', 'img' => '/assets/img/blog/blog-01.webp', 'tag' => 'Mindfulness'],
    ['title' => "Eating for Energy: A Yogi's Guide to Nutrition", 'excerpt' => 'What to eat before and after practice to actually feel the difference.', 'img' => '/assets/img/blog/blog-02.jpg', 'tag' => 'Nutrition'],
    ['title' => 'Yoga for Busy People: 10-Minute Desk Stretches', 'excerpt' => 'No mat, no studio — just a few stretches you can do between meetings.', 'img' => '/assets/img/blog/blog-03.webp', 'tag' => 'Practice'],
];

$galleryPreview = [
    'pose-plank-fold.webp', 'pose-headstand-1.webp', 'pose-crow-1.webp',
    'pose-bridge.webp', 'pose-boat-balcony-2.webp', 'about-journey.webp',
];
?>
<header class="nav-glass">
    <div class="nav-glass__brand">
        <a href="#hero">
            <img src="/assets/img/brand/logo-dark.png" alt="Vipasa Yoga" class="nav-glass__brand-logo brand-logo--dark">
            <img src="/assets/img/brand/logo-light.jpg" alt="Vipasa Yoga" class="nav-glass__brand-logo brand-logo--light">
        </a>
    </div>
    <button type="button" class="nav-glass__toggle" data-nav-toggle aria-expanded="false" aria-label="Toggle menu">
        <span class="nav-glass__toggle-bar"></span>
        <span class="nav-glass__toggle-bar"></span>
        <span class="nav-glass__toggle-bar"></span>
    </button>
    <nav class="nav-glass__links">
        <a href="#about" class="nav-glass__link">About</a>
        <a href="#packages" class="nav-glass__link">Packages</a>
        <a href="#instructors" class="nav-glass__link">Instructors</a>
        <a href="/gallery" class="nav-glass__link">Gallery</a>
        <a href="#reviews" class="nav-glass__link">Reviews</a>
        <a href="#faq" class="nav-glass__link">FAQ</a>
        <a href="#contact" class="nav-glass__link">Contact</a>
    </nav>
    <div class="nav-glass__actions">
        <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode">
            <span class="theme-toggle__track">
                <span class="theme-toggle__thumb">
                    <span class="theme-toggle__icon-sun">☀</span>
                    <span class="theme-toggle__icon-moon">☾</span>
                </span>
            </span>
        </button>
        <a href="/login" class="btn btn-ghost btn-sm">Log in</a>
        <a href="/signup" class="btn btn-accent btn-sm">Get started</a>
    </div>
</header>

<section class="hero" id="hero">
    <div class="hero__bg" aria-hidden="true">
        <span class="hero__orb hero__orb--1"></span>
        <span class="hero__orb hero__orb--2"></span>
        <span class="hero__orb hero__orb--3"></span>
        <span class="hero__orb hero__orb--4"></span>
        <span class="hero__grain"></span>
    </div>
    <div class="container hero__content">
        <div>
            <span class="badge hero__eyebrow"><?= View::e($hero['eyebrow']) ?></span>
            <p class="hero__tagline"><?= View::e($hero['tagline']) ?></p>
            <h1 class="hero__title"><?= View::e($hero['title']) ?></h1>
            <p class="hero__subtitle"><?= View::e($hero['subtitle']) ?></p>
            <div class="hero__actions">
                <a href="/signup" class="btn btn-accent btn-lg">Start your first session</a>
                <a href="#packages" class="btn btn-secondary btn-lg">Explore packages</a>
            </div>
        </div>
        <div class="hero__parallax-layer" data-parallax-depth="14">
            <div class="hero__photo-frame">
                <img src="/assets/img/client/instructor-portrait-smiling.webp" alt="Vijaya Parameswaran guiding a live yoga session">
                <div class="hero__photo-badge">
                    <strong><?= $stats['rating'] !== null ? View::e(number_format($stats['rating'], 1)) : 'New' ?></strong>
                    <span>Average instructor rating</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="community-band">
    <img class="community-band__img" src="/assets/img/client/hero-banner.webp" alt="Vijaya practicing yoga" loading="lazy">
    <div class="community-band__overlay"></div>
    <div class="container community-band__content">
        <p class="community-band__quote">"Every session is guided by someone who's really watching — not just a recording."</p>
    </div>
</section>

<section class="landing-section about-section" id="about">
    <div class="container about-layout">
        <div class="about-layout__gallery" data-reveal="left">
            <img class="about-layout__img about-layout__img--main" src="/assets/img/client/philosophy-meditation.webp" alt="Stillness practice at Vipasa Yoga" loading="lazy">
            <img class="about-layout__img about-layout__img--accent" src="/assets/img/client/pose-childs-pose.webp" alt="A restorative moment in practice" loading="lazy">
        </div>
        <div class="about-layout__copy" data-reveal="right">
            <span class="section-heading__eyebrow"><?= View::e($about['eyebrow']) ?></span>
            <h2><?= View::e($about['heading']) ?></h2>
            <p><?= View::e($about['body']) ?></p>
            <div class="about-layout__points">
                <div class="about-layout__point"><?= View::icon('certificate', 'icon', 20) ?> <span>Certified, government-accredited instruction</span></div>
                <div class="about-layout__point"><?= View::icon('clock', 'icon', 20) ?> <span>Live sessions, real-time feedback</span></div>
                <div class="about-layout__point"><?= View::icon('sliders', 'icon', 20) ?> <span>Flexible plans that fit your life</span></div>
            </div>
            <a href="/signup" class="btn btn-accent mt-6">Begin Your Journey</a>
        </div>
    </div>
</section>

<section class="landing-section landing-section--alt" id="why-choose">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Why Vipasa</span>
            <h2 class="section-heading__title">Everything a great studio has — without the commute</h2>
        </div>
        <div class="why-grid">
            <div class="why-card" data-reveal data-reveal-delay="1">
                <div class="why-card__icon"><?= View::icon('certificate', 'icon', 24) ?></div>
                <h3>Certified &amp; vetted</h3>
                <p class="text-muted">Every instructor is verified, experienced, and rated by real students after every session.</p>
            </div>
            <div class="why-card" data-reveal data-reveal-delay="2">
                <div class="why-card__icon"><?= View::icon('calendar-week', 'icon', 24) ?></div>
                <h3>Flexible scheduling</h3>
                <p class="text-muted">Book a single drop-in session or a recurring weekly plan — change anytime as life changes.</p>
            </div>
            <div class="why-card" data-reveal data-reveal-delay="3">
                <div class="why-card__icon"><?= View::icon('sliders', 'icon', 24) ?></div>
                <h3>Personalized practice</h3>
                <p class="text-muted">Sessions are shaped around your goals and experience level, not a one-size-fits-all script.</p>
            </div>
            <div class="why-card" data-reveal data-reveal-delay="4">
                <div class="why-card__icon"><?= View::icon('shield', 'icon', 24) ?></div>
                <h3>Secure payments</h3>
                <p class="text-muted">Every booking is paid through a trusted gateway with an instant invoice — nothing off the books.</p>
            </div>
        </div>
    </div>
</section>

<section class="landing-section" id="benefits">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Benefits</span>
            <h2 class="section-heading__title">What a consistent practice gives you back</h2>
        </div>
        <div class="benefit-grid">
            <div class="benefit-card" data-reveal data-reveal-delay="1">
                <img src="/assets/img/client/pose-standing-fold.webp" alt="" loading="lazy">
                <div class="benefit-card__body">
                    <h3>Flexibility &amp; strength</h3>
                    <p class="text-muted">Build mobility and core strength with sequences shaped around your body, not a generic routine.</p>
                </div>
            </div>
            <div class="benefit-card" data-reveal data-reveal-delay="2">
                <img src="/assets/img/client/pose-seated-twist.webp" alt="" loading="lazy">
                <div class="benefit-card__body">
                    <h3>Mindfulness &amp; stress relief</h3>
                    <p class="text-muted">Breathing-led sequences that lower stress and sharpen focus, on and off the mat.</p>
                </div>
            </div>
            <div class="benefit-card" data-reveal data-reveal-delay="3">
                <img src="/assets/img/benefits/connection.webp" alt="" loading="lazy">
                <div class="benefit-card__body">
                    <h3>Community &amp; connection</h3>
                    <p class="text-muted">Practice solo or alongside others in group sessions — either way, you're never just a username.</p>
                </div>
            </div>
            <div class="benefit-card" data-reveal data-reveal-delay="4">
                <img src="/assets/img/client/pose-childs-pose-2.webp" alt="" loading="lazy">
                <div class="benefit-card__body">
                    <h3>Recovery &amp; better sleep</h3>
                    <p class="text-muted">Restorative sequences designed to wind the body down for deeper, more consistent rest.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($programs)): ?>
<section class="landing-section landing-section--alt" id="programs">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Our Programs</span>
            <h2 class="section-heading__title">Practice styles for every goal</h2>
            <p class="section-heading__subtitle">Every program can be booked as a single session, a weekly rhythm, or a full month.</p>
        </div>
        <div class="program-grid">
            <?php foreach ($programs as $i => $program): ?>
            <article class="program-card-v2" data-reveal data-reveal-delay="<?= ($i % 4) + 1 ?>">
                <img src="<?= View::e($program['img']) ?>" alt="<?= View::e($program['name']) ?>" loading="lazy">
                <div class="program-card-v2__overlay"></div>
                <div class="program-card-v2__body">
                    <h3><?= View::e($program['name']) ?></h3>
                    <p class="program-card-v2__description"><?= View::e($program['description']) ?></p>
                    <div class="program-card-v2__actions">
                        <button type="button" class="program-card-v2__more" data-program-more>Read More</button>
                        <a href="/signup" class="btn btn-accent btn-sm">Book Now</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="landing-section" id="packages">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Packages &amp; pricing</span>
            <h2 class="section-heading__title">Choose the practice that fits your life</h2>
            <p class="section-heading__subtitle">Every plan includes live sessions with a certified instructor and a full booking &amp; payment history in your dashboard.</p>
        </div>
        <?php if (empty($packages)): ?>
        <p class="text-muted text-center">Packages are being finalized — check back soon.</p>
        <?php else: ?>
        <div class="service-grid service-grid--3">
            <?php foreach ($packages as $i => $package): ?>
            <div class="package-card<?= $i === 1 ? ' is-featured' : '' ?>" data-reveal data-reveal-delay="<?= $i + 1 ?>">
                <?php if ($i === 1): ?>
                <span class="package-card__ribbon">Most popular</span>
                <?php endif; ?>
                <div class="package-card__name"><?= (int) $package['sessions_count'] ?> session<?= (int) $package['sessions_count'] > 1 ? 's' : '' ?></div>
                <h3><?= View::e($package['name']) ?></h3>
                <div class="package-card__price">
                    <span class="package-card__price-value"><?= View::e($package['currency']) ?> <?= number_format((float) $package['price'], 2) ?></span>
                </div>
                <p class="package-card__description text-muted"><?= View::e($package['description']) ?></p>
                <div class="package-card__features">
                    <div class="package-card__feature">
                        <?= View::icon('check') ?>
                        <?= (int) $package['duration_minutes'] ?> minutes per session
                    </div>
                    <div class="package-card__feature">
                        <?= View::icon('check') ?>
                        Up to <?= (int) $package['max_participants'] ?> participant<?= (int) $package['max_participants'] > 1 ? 's' : '' ?>
                    </div>
                    <div class="package-card__feature">
                        <?= View::icon('check') ?>
                        Instant invoice &amp; booking history
                    </div>
                </div>
                <a href="/signup" class="btn btn-accent btn-block">Choose plan</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section landing-section--alt" id="instructors">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Meet Your Instructor</span>
            <h2 class="section-heading__title">Learn directly from a certified teacher</h2>
        </div>
        <?php if (empty($instructors)): ?>
        <p class="text-muted text-center">Instructor profile coming soon.</p>
        <?php else: $instructor = $instructors[0]; $certificates = $instructor['certificates'] ?? []; $specialties = $instructor['specialties'] ?? []; ?>
        <div class="instructor-spotlight">
            <div class="instructor-spotlight__photo" data-reveal="left">
                <img src="<?= View::e($instructor['avatar_path']) ?>" alt="<?= View::e($instructor['name']) ?>" loading="lazy">
                <span class="instructor-spotlight__badge"><?= View::icon('certificate', 'icon', 16) ?> Govt. Certified</span>
            </div>
            <div class="instructor-spotlight__copy" data-reveal="right">
                <span class="section-heading__eyebrow">Ms. <?= View::e($instructor['name']) ?></span>
                <h3><?= View::e($instructor['headline']) ?></h3>
                <p><?= View::e($instructor['bio']) ?></p>

                <?php if (!empty($certificates)): ?>
                <h4 class="instructor-spotlight__label">Qualifications</h4>
                <ul class="instructor-spotlight__list">
                    <?php foreach ($certificates as $cert): ?>
                    <li><?= View::icon('certificate', 'icon', 16) ?> <span><?= View::e((string) $cert) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if (!empty($specialties)): ?>
                <div class="instructor-spotlight__tags">
                    <?php foreach ($specialties as $tag): ?>
                    <span class="chip"><?= View::e((string) $tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <a href="/signup" class="btn btn-accent btn-lg mt-6">Book a Session</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section" id="gallery">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Gallery</span>
            <h2 class="section-heading__title">Inside the practice</h2>
        </div>
        <div class="gallery-preview-grid">
            <?php foreach ($galleryPreview as $i => $file): ?>
            <a href="/gallery" class="gallery-preview-grid__item" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
                <img src="/assets/img/client/<?= View::e($file) ?>" alt="" loading="lazy">
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="/gallery" class="btn btn-secondary btn-lg">View full gallery</a>
        </div>
    </div>
</section>

<section class="landing-section landing-section--alt" id="testimonials">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Loved by our members</span>
            <h2 class="section-heading__title">What practicing with us feels like</h2>
        </div>
        <div class="testimonial-carousel">
            <div class="testimonial-slides">
                <?php foreach ($testimonials as $i => $t): ?>
                <blockquote class="testimonial-slide<?= $i === 0 ? ' is-active' : '' ?>">
                    <p class="testimonial-slide__quote"><?= View::e($t['quote']) ?></p>
                    <footer class="testimonial-slide__author">
                        <div class="avatar avatar-md">
                            <img src="<?= View::e($t['photo']) ?>" alt="" loading="lazy">
                        </div>
                        <div>
                            <div class="testimonial-slide__name"><?= View::e($t['name']) ?></div>
                            <div class="testimonial-slide__role"><?= View::e($t['role']) ?></div>
                        </div>
                    </footer>
                </blockquote>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-dots">
                <?php foreach ($testimonials as $i => $t): ?>
                <button type="button" class="testimonial-dots__dot<?= $i === 0 ? ' is-active' : '' ?>" aria-label="Testimonial <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="landing-section" id="reviews">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Client reviews</span>
            <h2 class="section-heading__title">Rated <?= $stats['rating'] !== null ? View::e(number_format($stats['rating'], 1)) : '5.0' ?>/5 by our members</h2>
        </div>
        <div class="review-grid">
            <?php foreach ($reviews as $i => $r): ?>
            <div class="review-card" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
                <div class="rating-stars">
                    <?php for ($s = 0; $s < 5; $s++): ?>
                    <?= View::icon('star', 'star' . ($s < $r['rating'] ? '' : ' is-empty')) ?>
                    <?php endfor; ?>
                </div>
                <p class="review-card__text">&ldquo;<?= View::e($r['text']) ?>&rdquo;</p>
                <div class="review-card__name"><?= View::e($r['name']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="stat-counters" id="stats">
    <div class="container stat-counters__grid">
        <div class="stat-counter">
            <div class="stat-counter__number">
                <span class="stat-counter__value" data-count-to="<?= (int) $stats['instructors'] ?>">0</span><span class="stat-counter__suffix">+</span>
            </div>
            <div class="stat-counter__label">Certified instructors</div>
        </div>
        <div class="stat-counter">
            <div class="stat-counter__number">
                <span class="stat-counter__value" data-count-to="<?= (int) $stats['years'] ?>">0</span><span class="stat-counter__suffix">+</span>
            </div>
            <div class="stat-counter__label">Years combined experience</div>
        </div>
        <div class="stat-counter">
            <div class="stat-counter__number">
                <span class="stat-counter__value" data-count-to="<?= (int) $stats['programs'] ?>">0</span>
            </div>
            <div class="stat-counter__label">Programs available</div>
        </div>
        <div class="stat-counter">
            <div class="stat-counter__number">
                <span class="stat-counter__value" data-count-to="<?= $stats['rating'] !== null ? (int) round($stats['rating']) : 5 ?>">0</span>
            </div>
            <div class="stat-counter__label"><?= $stats['rating'] !== null ? View::e(number_format($stats['rating'], 1)) . ' average rating' : 'New — be our first review' ?></div>
        </div>
    </div>
</section>

<section class="landing-section landing-section--alt" id="faq">
    <div class="container" style="max-width:760px">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Questions</span>
            <h2 class="section-heading__title">Frequently asked questions</h2>
        </div>
        <div class="faq-list">
            <?php foreach ($faqs as $faq): ?>
            <div class="faq-item">
                <button type="button" class="faq-item__question" aria-expanded="false">
                    <span><?= View::e($faq['q']) ?></span>
                    <?= View::icon('chevron-down', 'faq-item__chevron') ?>
                </button>
                <div class="faq-item__answer">
                    <div class="faq-item__answer-inner">
                        <p><?= View::e($faq['a']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="landing-section" id="blog">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">From the journal</span>
            <h2 class="section-heading__title">Notes on practice &amp; wellbeing</h2>
        </div>
        <div class="blog-grid">
            <?php foreach ($blogPosts as $i => $post): ?>
            <article class="blog-card" data-reveal data-reveal-delay="<?= $i + 1 ?>">
                <div class="blog-card__img"><img src="<?= View::e($post['img']) ?>" alt="" loading="lazy"></div>
                <div class="blog-card__body">
                    <span class="chip"><?= View::e($post['tag']) ?></span>
                    <h3><?= View::e($post['title']) ?></h3>
                    <p class="text-muted"><?= View::e($post['excerpt']) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="text-muted text-center mt-6">More articles coming soon — subscribe below to get notified.</p>
    </div>
</section>

<section class="newsletter-band" id="newsletter">
    <div class="container newsletter-band__inner">
        <div class="newsletter-band__copy">
            <h2>Stay in the loop</h2>
            <p>New programs, instructor spotlights, and mindful living tips — no spam.</p>
        </div>
        <form id="newsletter-form" class="newsletter-band__form">
            <input type="email" name="email" class="newsletter-band__input form-group__control" placeholder="you@example.com" aria-label="Email address" required>
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>
</section>

<section class="landing-section landing-section--alt" id="contact">
    <div class="container">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Get in touch</span>
            <h2 class="section-heading__title">Questions before you book?</h2>
        </div>
        <div class="contact-layout">
            <div class="contact-info">
                <div class="contact-info__item">
                    <?= View::icon('mail') ?>
                    <span><?= View::e($contact['email']) ?></span>
                </div>
                <div class="contact-info__item">
                    <?= View::icon('phone') ?>
                    <span><?= View::e($contact['phone']) ?></span>
                </div>
                <div class="contact-info__item">
                    <?= View::icon('map-pin') ?>
                    <span><?= View::e($contact['location']) ?></span>
                </div>
            </div>
            <form id="contact-form" novalidate>
                <div class="form-group">
                    <input type="text" id="contact-name" name="name" class="form-group__control" placeholder=" " required minlength="2" maxlength="150">
                    <label class="form-group__label" for="contact-name">Full name</label>
                    <div class="form-group__hint"></div>
                    <div class="form-group__error"></div>
                </div>
                <div class="form-group">
                    <input type="email" id="contact-email" name="email" class="form-group__control" placeholder=" " required maxlength="190">
                    <label class="form-group__label" for="contact-email">Email address</label>
                    <div class="form-group__hint"></div>
                    <div class="form-group__error"></div>
                </div>
                <div class="form-group">
                    <textarea id="contact-message" name="message" class="form-group__control" placeholder=" " required minlength="5" maxlength="2000" style="min-height:120px"></textarea>
                    <label class="form-group__label" for="contact-message">Your message</label>
                    <div class="form-group__hint"></div>
                    <div class="form-group__error"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" id="contact-submit">Send message</button>
            </form>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__brand">
            <div class="site-footer__logo">Vipasa Yoga</div>
            <p class="site-footer__tagline">Live yoga sessions with certified instructors, booked in minutes.</p>
            <div class="site-footer__social">
                <a class="site-footer__social-link" href="#" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
                </a>
                <a class="site-footer__social-link" href="#" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a class="site-footer__social-link" href="#" aria-label="YouTube">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="4"/><polygon points="10 9 15 12 10 15" fill="currentColor" stroke="none"/></svg>
                </a>
            </div>
        </div>
        <nav class="site-footer__col">
            <h3 class="site-footer__col-title">Explore</h3>
            <a class="site-footer__link" href="#about">About</a>
            <a class="site-footer__link" href="#packages">Packages</a>
            <a class="site-footer__link" href="#instructors">Instructors</a>
            <a class="site-footer__link" href="/gallery">Gallery</a>
            <a class="site-footer__link" href="#faq">FAQ</a>
        </nav>
        <nav class="site-footer__col">
            <h3 class="site-footer__col-title">Account</h3>
            <a class="site-footer__link" href="/login">Log in</a>
            <a class="site-footer__link" href="/signup">Sign up</a>
        </nav>
        <nav class="site-footer__col">
            <h3 class="site-footer__col-title">Contact</h3>
            <a class="site-footer__link" href="#contact">Get in touch</a>
            <a class="site-footer__link" href="mailto:<?= View::e($contact['email']) ?>"><?= View::e($contact['email']) ?></a>
        </nav>
    </div>
    <div class="site-footer__bottom">
        <div class="container site-footer__bottom-inner">
            <p class="site-footer__copyright">&copy; <?= date('Y') ?> Vipasa Yoga. All rights reserved.</p>
        </div>
    </div>
</footer>
<script type="module">
import { initLanding } from '/assets/js/modules/landing.js';
initLanding();
</script>
