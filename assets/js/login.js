(function () {
  function random(min, max) {
    return min + Math.random() * (max - min);
  }

  function createBookSvg() {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', '0 0 48 64');
    svg.setAttribute('width', '48');
    svg.setAttribute('height', '64');
    svg.setAttribute('aria-hidden', 'true');
    var r = document.createElementNS(ns, 'rect');
    r.setAttribute('width', '48');
    r.setAttribute('height', '64');
    r.setAttribute('rx', '6');
    r.setAttribute('fill', 'currentColor');
    svg.appendChild(r);
    var l1 = document.createElementNS(ns, 'path');
    l1.setAttribute('d', 'M10 18h28M10 28h22');
    l1.setAttribute('stroke', '#fff');
    l1.setAttribute('stroke-width', '3');
    l1.setAttribute('stroke-linecap', 'round');
    l1.setAttribute('opacity', '0.35');
    svg.appendChild(l1);
    return svg;
  }

  function initFlyingBooks() {
    var layer = document.getElementById('loginFlyBooks');
    if (!layer) return;

    var count = 14;
    for (var i = 0; i < count; i++) {
      var wrap = document.createElement('div');
      wrap.className = 'fly-book';
      var size = random(36, 72);
      wrap.style.width = size + 'px';
      wrap.style.height = (size * 1.35) + 'px';
      wrap.style.left = random(-5, 95) + '%';
      wrap.style.top = random(-5, 95) + '%';
      var tx = random(-80, 80) + 'px';
      var ty = random(-60, 60) + 'px';
      var rot = random(-25, 25) + 'deg';
      var dur = random(14, 28) + 's';
      var delay = random(0, 6) + 's';
      wrap.style.setProperty('--tx', tx);
      wrap.style.setProperty('--ty', ty);
      wrap.style.setProperty('--rot', rot);
      wrap.style.animation = 'epFly ' + dur + ' ease-in-out ' + delay + ' infinite alternate';
      var svg = createBookSvg();
      svg.setAttribute('width', '100%');
      svg.setAttribute('height', '100%');
      svg.style.display = 'block';
      wrap.appendChild(svg);
      layer.appendChild(wrap);
    }
  }

  var style = document.createElement('style');
  style.textContent =
    '@keyframes epFly { from { transform: translate(0,0) rotate(0deg); } to { transform: translate(var(--tx),var(--ty)) rotate(var(--rot)); } }';
  document.head.appendChild(style);

  document.addEventListener('DOMContentLoaded', initFlyingBooks);
})();
