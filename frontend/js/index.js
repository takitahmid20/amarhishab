
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Scroll reveal
  var allReveal = document.querySelectorAll('.reveal, .reveal-right');
  var obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        e.target.classList.add('show');
        e.target.querySelectorAll('[data-width]').forEach(function (bar) {
          setTimeout(function () { bar.style.width = bar.dataset.width; }, 200);
        });
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -36px 0px' });
  allReveal.forEach(function (el) { obs.observe(el); });

  // Standalone bars (outside reveal containers)
  document.querySelectorAll('[data-width]').forEach(function (bar) {
    var bObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.style.width = e.target.dataset.width; bObs.unobserve(e.target); }
      });
    }, { threshold: 0.5 });
    bObs.observe(bar);
  });

  // Active nav highlight
  var sections = document.querySelectorAll('[id]');
  var links    = document.querySelectorAll('.bottom-nav-link');
  var activeObs = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        links.forEach(function (l) { l.classList.remove('active'); });
        var active = document.querySelector('.bottom-nav-link[href="#' + e.target.id + '"]');
        if (active) active.classList.add('active');
      }
    });
  }, { threshold: 0.45 });
  sections.forEach(function (s) { activeObs.observe(s); });
})();