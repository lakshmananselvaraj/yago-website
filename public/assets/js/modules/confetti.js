/* =========================================================================
   Vipasa Yoga — Confetti burst (pure Canvas 2D, no external library)
   ========================================================================= */

const PARTICLE_COUNT = 120;
const DURATION_MS = 3000;
const COLORS = ['#e07a5f', '#81a684', '#3d5a80', '#f2cc8f', '#9b8cc7', '#e8a87c'];

let canvas = null;
let ctx = null;
let animationHandle = null;

function getCanvas() {
  if (canvas) return canvas;
  canvas = document.createElement('canvas');
  canvas.className = 'confetti-canvas';
  document.body.appendChild(canvas);
  ctx = canvas.getContext('2d');
  return canvas;
}

function resizeCanvas() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}

function createParticle() {
  return {
    x: Math.random() * canvas.width,
    y: -20 - Math.random() * canvas.height * 0.3,
    size: 6 + Math.random() * 6,
    color: COLORS[Math.floor(Math.random() * COLORS.length)],
    isCircle: Math.random() > 0.5,
    fallSpeed: 2 + Math.random() * 3,
    driftSpeed: (Math.random() - 0.5) * 2,
    rotation: Math.random() * Math.PI * 2,
    rotationSpeed: (Math.random() - 0.5) * 0.3,
  };
}

function drawParticle(p) {
  ctx.save();
  ctx.translate(p.x, p.y);
  ctx.rotate(p.rotation);
  ctx.fillStyle = p.color;
  if (p.isCircle) {
    ctx.beginPath();
    ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
    ctx.fill();
  } else {
    ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
  }
  ctx.restore();
}

export function fireConfetti() {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  getCanvas();
  resizeCanvas();
  canvas.style.display = 'block';

  if (animationHandle) {
    cancelAnimationFrame(animationHandle);
  }

  const particles = Array.from({ length: PARTICLE_COUNT }, createParticle);
  const startTime = performance.now();

  const onResize = () => resizeCanvas();
  window.addEventListener('resize', onResize);

  function tick(now) {
    const elapsed = now - startTime;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (elapsed >= DURATION_MS) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      canvas.style.display = 'none';
      window.removeEventListener('resize', onResize);
      animationHandle = null;
      return;
    }

    for (const p of particles) {
      p.y += p.fallSpeed;
      p.x += p.driftSpeed;
      p.rotation += p.rotationSpeed;
      drawParticle(p);
    }

    animationHandle = requestAnimationFrame(tick);
  }

  animationHandle = requestAnimationFrame(tick);
}
