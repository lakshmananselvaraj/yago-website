/* =========================================================================
   Vipasa Yoga — lightweight vanilla SVG charts (no external charting library).
   Both renderers take { labels: string[], values: number[] } and draw a
   responsive inline SVG into `container`, scaled to a 320x160 viewBox.
   ========================================================================= */

const WIDTH = 320;
const HEIGHT = 160;
const PADDING = { top: 12, right: 12, bottom: 24, left: 12 };

function niceMax(max) {
  if (max <= 0) return 10;
  const magnitude = 10 ** Math.floor(Math.log10(max));
  return Math.ceil(max / magnitude) * magnitude;
}

function svgEl(tag, attrs) {
  const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
  Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, value));
  return el;
}

function renderEmptyState(container, message) {
  container.innerHTML = '';
  const el = document.createElement('p');
  el.className = 'text-muted';
  el.style.cssText = 'font-size:var(--font-size-sm);text-align:center;padding-block:var(--space-8)';
  el.textContent = message;
  container.appendChild(el);
}

export function renderLineChart(container, { labels, values }, options = {}) {
  if (!values || values.length === 0) {
    renderEmptyState(container, options.emptyMessage || 'No data yet.');
    return;
  }

  const color = options.color || 'var(--color-primary)';
  const max = niceMax(Math.max(...values, 0));
  const innerW = WIDTH - PADDING.left - PADDING.right;
  const innerH = HEIGHT - PADDING.top - PADDING.bottom;
  const stepX = values.length > 1 ? innerW / (values.length - 1) : 0;

  const points = values.map((v, i) => {
    const x = PADDING.left + stepX * i;
    const y = PADDING.top + innerH - (v / max) * innerH;
    return [x, y];
  });

  const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p[0].toFixed(1)} ${p[1].toFixed(1)}`).join(' ');
  const areaPath = `${linePath} L ${points[points.length - 1][0].toFixed(1)} ${PADDING.top + innerH} L ${points[0][0].toFixed(1)} ${PADDING.top + innerH} Z`;

  const svg = svgEl('svg', { viewBox: `0 0 ${WIDTH} ${HEIGHT}`, class: 'chart-svg', role: 'img' });

  svg.appendChild(svgEl('path', { d: areaPath, fill: color, opacity: '0.12', stroke: 'none' }));
  svg.appendChild(svgEl('path', { d: linePath, fill: 'none', stroke: color, 'stroke-width': '2', 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }));

  points.forEach(([x, y], i) => {
    const dot = svgEl('circle', { cx: x.toFixed(1), cy: y.toFixed(1), r: '2.5', fill: color });
    dot.appendChild(svgEl('title', {})).textContent = `${labels[i]}: ${values[i]}`;
    svg.appendChild(dot);
  });

  const labelStep = Math.max(1, Math.ceil(labels.length / 6));
  labels.forEach((label, i) => {
    if (i % labelStep !== 0 && i !== labels.length - 1) return;
    const text = svgEl('text', {
      x: points[i][0].toFixed(1),
      y: HEIGHT - 6,
      'font-size': '8',
      'text-anchor': 'middle',
      fill: 'var(--text-muted)',
    });
    text.textContent = label;
    svg.appendChild(text);
  });

  container.innerHTML = '';
  container.appendChild(svg);
}

export function renderBarChart(container, { labels, values }, options = {}) {
  if (!values || values.length === 0) {
    renderEmptyState(container, options.emptyMessage || 'No data yet.');
    return;
  }

  const color = options.color || 'var(--color-accent)';
  const max = niceMax(Math.max(...values, 0));
  const innerW = WIDTH - PADDING.left - PADDING.right;
  const innerH = HEIGHT - PADDING.top - PADDING.bottom;
  const gap = values.length > 15 ? 2 : 6;
  // Uncapped, a sparse series (e.g. only one package/instructor/gateway with
  // any activity yet) computes barW = innerW, which paints one giant block
  // filling the whole chart — indistinguishable from a broken render. Cap
  // bar width for small category counts and center the group instead of
  // stretching it edge to edge; time-series charts (many bars) are
  // unaffected since the cap only binds when there's little to fill with.
  const rawBarW = (innerW - gap * (values.length - 1)) / values.length;
  const barW = values.length <= 8 ? Math.min(rawBarW, 40) : rawBarW;
  const groupW = barW * values.length + gap * (values.length - 1);
  const startX = PADDING.left + Math.max(0, (innerW - groupW) / 2);
  // With many bars (e.g. a 30-day series) a label per bar overlaps into an
  // unreadable smear — thin out labels the same way the line chart does.
  const labelStep = Math.max(1, Math.ceil(labels.length / 6));

  const svg = svgEl('svg', { viewBox: `0 0 ${WIDTH} ${HEIGHT}`, class: 'chart-svg', role: 'img' });

  values.forEach((v, i) => {
    const barH = max > 0 ? (v / max) * innerH : 0;
    const x = startX + i * (barW + gap);
    const y = PADDING.top + innerH - barH;
    const rect = svgEl('rect', {
      x: x.toFixed(1), y: y.toFixed(1), width: Math.max(barW, 1).toFixed(1), height: barH.toFixed(1),
      rx: barW > 4 ? '3' : '1', fill: color,
    });
    rect.appendChild(svgEl('title', {})).textContent = `${labels[i]}: ${values[i]}`;
    svg.appendChild(rect);

    if (i % labelStep !== 0 && i !== values.length - 1) return;
    const text = svgEl('text', {
      x: (x + barW / 2).toFixed(1),
      y: HEIGHT - 6,
      'font-size': '8',
      'text-anchor': 'middle',
      fill: 'var(--text-muted)',
    });
    text.textContent = labels[i].length > 10 ? labels[i].slice(0, 9) + '…' : labels[i];
    svg.appendChild(text);
  });

  container.innerHTML = '';
  container.appendChild(svg);
}
