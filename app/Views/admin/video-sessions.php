<?php

use App\Core\View;

$pageTitle = 'Video Session Management — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'video-sessions';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16);max-width:640px">
    <div class="card" style="padding:var(--space-8);text-align:center">
        <h1 style="margin:0 0 var(--space-4)">Video Session Management</h1>
        <p class="text-muted" style="margin:0 0 var(--space-4)">
            The inbuilt video classroom — live video and audio, mute/unmute controls, in-session chat,
            raise hand, screen share, attendance tracking, a session timer, and a waiting room — is a
            dedicated phase being built separately. This page will host that management dashboard once
            it's ready.
        </p>
        <p class="text-muted" style="margin:0 0 var(--space-6)">
            Until then, sessions run on the meeting-link field: for each booking, an admin pastes an
            externally-created Google Meet or Zoom link from the Bookings admin page.
        </p>
        <a href="/admin/bookings" class="btn btn-primary btn-sm">Go to Bookings</a>
    </div>
</div>
