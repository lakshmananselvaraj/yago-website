<?php

use App\Core\View;

$pageTitle = 'Website Content — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'content';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="margin:0 0 var(--space-6);text-align:left">
        <h1 class="services-hero__title" style="margin:0">Website Content</h1>
        <p class="services-hero__subtitle" style="margin:0">Edit what visitors see on the homepage — changes go live immediately.</p>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Hero</h2>
        <form id="hero-form">
            <div class="form-group">
                <input type="text" name="eyebrow" class="form-group__control" placeholder=" " maxlength="150" value="<?= View::e($hero['eyebrow']) ?>">
                <label class="form-group__label">Eyebrow badge</label>
            </div>
            <div class="form-group">
                <input type="text" name="tagline" class="form-group__control" placeholder=" " maxlength="100" value="<?= View::e($hero['tagline']) ?>">
                <label class="form-group__label">Tagline</label>
            </div>
            <div class="form-group">
                <input type="text" name="title" class="form-group__control" placeholder=" " maxlength="200" value="<?= View::e($hero['title']) ?>">
                <label class="form-group__label">Headline</label>
            </div>
            <div class="form-group">
                <textarea name="subtitle" class="form-group__control" placeholder=" " rows="2"><?= View::e($hero['subtitle']) ?></textarea>
                <label class="form-group__label">Subtitle</label>
            </div>
            <button type="submit" class="btn btn-primary">Save Hero</button>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">About / Philosophy</h2>
        <form id="about-form">
            <div class="form-group">
                <input type="text" name="eyebrow" class="form-group__control" placeholder=" " maxlength="150" value="<?= View::e($about['eyebrow']) ?>">
                <label class="form-group__label">Eyebrow</label>
            </div>
            <div class="form-group">
                <input type="text" name="heading" class="form-group__control" placeholder=" " maxlength="150" value="<?= View::e($about['heading']) ?>">
                <label class="form-group__label">Heading</label>
            </div>
            <div class="form-group">
                <textarea name="body" class="form-group__control" placeholder=" " rows="4"><?= View::e($about['body']) ?></textarea>
                <label class="form-group__label">Body text</label>
            </div>
            <button type="submit" class="btn btn-primary">Save About</button>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Contact Details</h2>
        <form id="contact-form-admin">
            <div class="flex gap-3 flex-wrap">
                <div class="form-group" style="flex:1;min-width:200px">
                    <input type="text" name="email" class="form-group__control" placeholder=" " maxlength="190" value="<?= View::e($contact['email']) ?>">
                    <label class="form-group__label">Email</label>
                </div>
                <div class="form-group" style="flex:1;min-width:160px">
                    <input type="text" name="phone" class="form-group__control" placeholder=" " maxlength="30" value="<?= View::e($contact['phone']) ?>">
                    <label class="form-group__label">Phone</label>
                </div>
                <div class="form-group" style="flex:1;min-width:200px">
                    <input type="text" name="location" class="form-group__control" placeholder=" " maxlength="150" value="<?= View::e($contact['location']) ?>">
                    <label class="form-group__label">Location text</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Contact</button>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <div class="flex items-center justify-between mb-4">
            <h2 style="font-size:var(--font-size-lg);margin:0">Programs</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="programs">+ Add Program</button>
        </div>
        <form id="programs-form">
            <div id="programs-rows" class="flex flex-col gap-3 mb-4">
                <?php foreach ($programs as $program): ?>
                <div class="repeatable-row card" style="padding:var(--space-4)">
                    <div class="flex gap-3 flex-wrap mb-2">
                        <input type="text" data-field="name" class="form-group__control" style="flex:1;min-width:160px" placeholder="Name" value="<?= View::e($program['name']) ?>">
                        <input type="text" data-field="img" class="form-group__control" style="flex:1;min-width:200px" placeholder="Image path" value="<?= View::e($program['img']) ?>">
                        <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
                    </div>
                    <textarea data-field="description" class="form-group__control" placeholder="Description" rows="2"><?= View::e($program['description']) ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Save Programs</button>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <div class="flex items-center justify-between mb-4">
            <h2 style="font-size:var(--font-size-lg);margin:0">Testimonials</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="testimonials">+ Add Testimonial</button>
        </div>
        <form id="testimonials-form">
            <div id="testimonials-rows" class="flex flex-col gap-3 mb-4">
                <?php foreach ($testimonials as $t): ?>
                <div class="repeatable-row card" style="padding:var(--space-4)">
                    <div class="flex gap-3 flex-wrap mb-2">
                        <input type="text" data-field="name" class="form-group__control" style="flex:1;min-width:140px" placeholder="Name" value="<?= View::e($t['name']) ?>">
                        <input type="text" data-field="role" class="form-group__control" style="flex:1;min-width:160px" placeholder="Role / membership" value="<?= View::e($t['role']) ?>">
                        <input type="text" data-field="photo" class="form-group__control" style="flex:1;min-width:160px" placeholder="Photo path" value="<?= View::e($t['photo']) ?>">
                        <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
                    </div>
                    <textarea data-field="quote" class="form-group__control" placeholder="Quote" rows="2"><?= View::e($t['quote']) ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Save Testimonials</button>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <div class="flex items-center justify-between mb-4">
            <h2 style="font-size:var(--font-size-lg);margin:0">FAQs</h2>
            <button type="button" class="btn btn-secondary btn-sm" data-add-row="faqs">+ Add FAQ</button>
        </div>
        <form id="faqs-form">
            <div id="faqs-rows" class="flex flex-col gap-3 mb-4">
                <?php foreach ($faqs as $faq): ?>
                <div class="repeatable-row card" style="padding:var(--space-4)">
                    <div class="flex items-center justify-between mb-2">
                        <input type="text" data-field="q" class="form-group__control" style="flex:1;margin-right:var(--space-3)" placeholder="Question" value="<?= View::e($faq['q']) ?>">
                        <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
                    </div>
                    <textarea data-field="a" class="form-group__control" placeholder="Answer" rows="2"><?= View::e($faq['a']) ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Save FAQs</button>
        </form>
    </div>
</div>
<script type="module">
import { apiPut } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

function serializeForm(form) {
    const payload = {};
    form.querySelectorAll('[name]').forEach((el) => {
        payload[el.name] = el.value.trim();
    });
    return payload;
}

['hero-form', 'about-form'].forEach((formId) => {
    const form = document.getElementById(formId);
    const endpoint = formId === 'hero-form' ? '/admin/content/hero' : '/admin/content/about';
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await apiPut(endpoint, serializeForm(form));
            toast.success('Saved.');
        } catch (err) {
            toast.error(err.message);
        }
    });
});

document.getElementById('contact-form-admin').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    try {
        await apiPut('/admin/content/contact', {
            email: form.email.value.trim(),
            phone: form.phone.value.trim(),
            location: form.location.value.trim(),
        });
        toast.success('Saved.');
    } catch (err) {
        toast.error(err.message);
    }
});

const ROW_TEMPLATES = {
    programs: () => `
        <div class="repeatable-row card" style="padding:var(--space-4)">
            <div class="flex gap-3 flex-wrap mb-2">
                <input type="text" data-field="name" class="form-group__control" style="flex:1;min-width:160px" placeholder="Name">
                <input type="text" data-field="img" class="form-group__control" style="flex:1;min-width:200px" placeholder="Image path">
                <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
            </div>
            <textarea data-field="description" class="form-group__control" placeholder="Description" rows="2"></textarea>
        </div>`,
    testimonials: () => `
        <div class="repeatable-row card" style="padding:var(--space-4)">
            <div class="flex gap-3 flex-wrap mb-2">
                <input type="text" data-field="name" class="form-group__control" style="flex:1;min-width:140px" placeholder="Name">
                <input type="text" data-field="role" class="form-group__control" style="flex:1;min-width:160px" placeholder="Role / membership">
                <input type="text" data-field="photo" class="form-group__control" style="flex:1;min-width:160px" placeholder="Photo path">
                <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
            </div>
            <textarea data-field="quote" class="form-group__control" placeholder="Quote" rows="2"></textarea>
        </div>`,
    faqs: () => `
        <div class="repeatable-row card" style="padding:var(--space-4)">
            <div class="flex items-center justify-between mb-2">
                <input type="text" data-field="q" class="form-group__control" style="flex:1;margin-right:var(--space-3)" placeholder="Question">
                <button type="button" class="btn btn-ghost btn-sm remove-row-btn">Remove</button>
            </div>
            <textarea data-field="a" class="form-group__control" placeholder="Answer" rows="2"></textarea>
        </div>`,
};

document.querySelectorAll('[data-add-row]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const key = btn.dataset.addRow;
        const container = document.getElementById(`${key}-rows`);
        container.insertAdjacentHTML('beforeend', ROW_TEMPLATES[key]());
    });
});

document.addEventListener('click', (event) => {
    if (event.target.matches('.remove-row-btn')) {
        event.target.closest('.repeatable-row').remove();
    }
});

function readRows(containerId) {
    const rows = document.querySelectorAll(`#${containerId} .repeatable-row`);
    return Array.from(rows).map((row) => {
        const item = {};
        row.querySelectorAll('[data-field]').forEach((el) => {
            item[el.dataset.field] = el.value.trim();
        });
        return item;
    });
}

document.getElementById('programs-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        await apiPut('/admin/content/programs', { items: readRows('programs-rows') });
        toast.success('Programs saved.');
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('testimonials-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        await apiPut('/admin/content/testimonials', { items: readRows('testimonials-rows') });
        toast.success('Testimonials saved.');
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('faqs-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        await apiPut('/admin/content/faqs', { items: readRows('faqs-rows') });
        toast.success('FAQs saved.');
    } catch (err) {
        toast.error(err.message);
    }
});
</script>
