(() => {
  const frame = document.querySelector(".frame");
  const canvas = document.querySelector(".links-canvas");
  const hub = document.querySelector(".hub");
  const nodes = Array.from(document.querySelectorAll(".orb"));
  const beams = Array.from(document.querySelectorAll(".beam"));

  if (!frame || !canvas || !hub || nodes.length === 0) return;

  const ctx = canvas.getContext("2d");
  let dpr = Math.max(1, window.devicePixelRatio || 1);
  let width = 0;
  let height = 0;

  const links = [];
  const getCenter = (el, rect) => {
    const r = el.getBoundingClientRect();
    return {
      x: r.left - rect.left + r.width / 2,
      y: r.top - rect.top + r.height / 2,
      radius: Math.min(r.width, r.height) / 2
    };
  };

  function rebuildGeometry() {
    const frameRect = frame.getBoundingClientRect();
    width = frameRect.width;
    height = frameRect.height;

    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const hubCenter = getCenter(hub, frameRect);
    links.length = 0;

    const profiles = [
      { ks: [0.16, 0.29, 0.44, 0.62], size: 1.24 }, // top-left
      { ks: [0.2, 0.38, 0.59], size: 1.16 }, // top-right
      { ks: [0.2, 0.38, 0.6], size: 1.16 }, // right-bottom
      { ks: [0.22, 0.41, 0.63], size: 1.08 }, // bottom
      { ks: [0.2, 0.38, 0.6], size: 1.16 } // left-bottom
    ];

    beams.forEach((beam) => { beam.style.width = "0px"; });

    nodes.forEach((node, i) => {
      const nodeCenter = getCenter(node, frameRect);
      const dx = nodeCenter.x - hubCenter.x;
      const dy = nodeCenter.y - hubCenter.y;
      const len = Math.hypot(dx, dy) || 1;
      const ux = dx / len;
      const uy = dy / len;

      // Reach nearly to ring edges so links visibly connect circle-to-circle.
      const startX = hubCenter.x + ux * (hubCenter.radius - 6);
      const startY = hubCenter.y + uy * (hubCenter.radius - 6);
      const endX = nodeCenter.x - ux * (nodeCenter.radius - 5);
      const endY = nodeCenter.y - uy * (nodeCenter.radius - 5);

      const beam = beams[i];
      if (beam) {
        const beamLength = Math.max(0, Math.hypot(endX - startX, endY - startY));
        const beamAngle = Math.atan2(endY - startY, endX - startX) * 180 / Math.PI;
        beam.style.left = `${startX}px`;
        beam.style.top = `${startY}px`;
        beam.style.width = `${beamLength}px`;
        beam.style.transform = `translateY(-50%) rotate(${beamAngle}deg)`;
      }

      links.push({
        startX,
        startY,
        endX,
        endY,
        ux,
        uy,
        profile: profiles[i] || { ks: [0.22, 0.4, 0.58], size: 1 },
        phase: i * 0.9 + Math.random() * 0.35,
        speed: 0.16 + Math.random() * 0.05
      });
    });
  }

  function drawGlowDot(x, y, core, alpha = 1) {
    const g = ctx.createRadialGradient(x, y, 0, x, y, core * 2.8);
    g.addColorStop(0, `rgba(255,255,255,${0.92 * alpha})`);
    g.addColorStop(0.35, `rgba(255,255,255,${0.42 * alpha})`);
    g.addColorStop(1, "rgba(255,255,255,0)");
    ctx.fillStyle = g;
    ctx.beginPath();
    ctx.arc(x, y, core * 2.8, 0, Math.PI * 2);
    ctx.fill();
  }

  function drawLink(link, t) {
    const { startX, startY, endX, endY, ux, uy, profile, phase, speed } = link;
    const vx = endX - startX;
    const vy = endY - startY;

    // Only blob chains, no white stroke line.
    for (let i = 0; i < profile.ks.length; i += 1) {
      const baseK = profile.ks[i];
      const drift = Math.sin((t * speed + phase + i * 0.19) * Math.PI * 2) * 0.02;
      const k = Math.min(0.95, Math.max(0.1, baseK + drift));

      const px = startX + vx * k - uy * (0.9 + i * 0.45);
      const py = startY + vy * k + ux * (0.9 + i * 0.45);

      const pulse = (Math.sin((t * 1.15 + phase + i * 0.2) * Math.PI * 2) + 1) * 0.5;
      const baseR = (7.3 + i * 1.28) * profile.size;
      const r = baseR + pulse * 2.2;
      const alpha = 0.25 + pulse * 0.31;

      drawGlowDot(px, py, r, alpha);
      drawGlowDot(px, py, r * 1.62, alpha * 0.22);
    }

    // Small contact speck near node-side endpoint.
    const tailK = 0.9 + Math.sin((t * speed + phase) * Math.PI * 2) * 0.015;
    const tailX = startX + vx * tailK;
    const tailY = startY + vy * tailK;
    drawGlowDot(tailX, tailY, 5.8, 0.18);

    // Joint pulse near center.
    const p = (Math.sin((t * 2.3 + phase) * Math.PI * 2) + 1) / 2;
    drawGlowDot(startX, startY, 7.2 + p * 3, 0.44 + p * 0.25);
    drawGlowDot(endX, endY, 5.8 + p * 2, 0.24 + p * 0.15);
  }

  function render(ts) {
    const t = ts * 0.001;
    ctx.clearRect(0, 0, width, height);

    ctx.save();
    ctx.globalCompositeOperation = "screen";
    links.forEach((link) => drawLink(link, t));
    ctx.restore();

    requestAnimationFrame(render);
  }

  const ro = new ResizeObserver(() => {
    dpr = Math.max(1, window.devicePixelRatio || 1);
    rebuildGeometry();
  });
  ro.observe(frame);
  window.addEventListener("resize", rebuildGeometry);

  rebuildGeometry();
  requestAnimationFrame(render);
})();
