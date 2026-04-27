(function () {
  var STORAGE_KEY = 'eperpustakaan-theme';

  function applyTheme(mode) {
    var body = document.body;
    if (!body) return;
    body.classList.remove('light', 'dark');
    body.classList.add(mode === 'dark' ? 'dark' : 'light');
  }

  function initTheme() {
    var saved = localStorage.getItem(STORAGE_KEY);
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var mode = saved || (prefersDark ? 'dark' : 'light');
    applyTheme(mode);

    var btn = document.getElementById('themeToggle');
    if (btn) {
      btn.addEventListener('click', function () {
        var next = document.body.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyTheme(next);
      });
    }
  }

  function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach(function (root) {
      var trigger = root.querySelector('[data-dropdown-trigger]');
      var menu = root.querySelector('[data-dropdown-menu]');
      if (!trigger || !menu) return;

      function close() {
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
      }

      function open() {
        menu.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
      }

      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menu.hidden) open();
        else close();
      });

      document.addEventListener('click', function () {
        close();
      });

      menu.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    });
  }

  function filterBookCards(input) {
    if (!input) return;
    
    var query = input.value.trim().toLowerCase();
    var cards = document.querySelectorAll('[data-book-card]');
    var visibleCount = 0;
    
    cards.forEach(function (card) {
      var title = (card.getAttribute('data-title') || '').toLowerCase();
      var author = (card.getAttribute('data-author') || '').toLowerCase();
      
      // Show if query is empty OR matches title or author
      var isVisible = query === '' || title.includes(query) || author.includes(query);
      
      if (isVisible) {
        card.style.display = '';  // Reset to default (grid item)
        card.hidden = false;
        visibleCount++;
      } else {
        card.style.display = 'none';  // Hide with display:none
        card.hidden = true;
      }
    });
    
    console.log('Filter: "' + query + '" → ' + visibleCount + '/' + cards.length + ' books visible');
  }

  function initBookSearch() {
    var input = document.querySelector('[data-book-search]');
    if (!input) {
      console.warn('Book search input not found');
      return;
    }
    
    // Real-time search on input event
    input.addEventListener('input', function () {
      filterBookCards(input);
    });
    
    // Also handle keyup for better compatibility
    input.addEventListener('keyup', function () {
      filterBookCards(input);
    });
    
    // Handle paste events
    input.addEventListener('paste', function () {
      setTimeout(function () {
        filterBookCards(input);
      }, 0);
    });
    
    console.log('Book search initialized');
  }

  window.EPerpus = window.EPerpus || {};
  window.EPerpus.initBookDetailPanel = function (options) {
    var grid = document.getElementById('bookGrid');
    var panelBody = document.getElementById('detailPanelBody');
    var layout = document.querySelector('[data-books-layout]');
    if (!grid || !panelBody) return;

    var isAdmin = !!options.isAdmin;
    var apiBase = options.apiUrl || 'api/book-detail.php';

    function escapeHtml(s) {
      if (!s) return '';
      var d = document.createElement('div');
      d.textContent = s;
      return d.innerHTML;
    }

    function setSelected(id) {
      grid.querySelectorAll('[data-book-card]').forEach(function (c) {
        c.classList.toggle('is-selected', c.getAttribute('data-id') === String(id));
      });
    }

    function showLoading() {
      panelBody.innerHTML = '<p class="empty-state">Memuat detail…</p>';
    }

    function showError() {
      panelBody.innerHTML = '<p class="empty-state">Gagal memuat data buku.</p>';
    }

    function render(book) {
      var stok = parseInt(book.stok, 10) || 0;
      var statusLabel = stok > 0 ? 'Tersedia' : 'Habis';
      var cover = escapeHtml(book.cover_url || 'assets/img/default-book.svg');
      function safeText(value) {
        if (value === null || value === undefined || value === '') return '-';
        return escapeHtml(String(value));
      }

      var adminBlock = '';
      if (isAdmin) {
        adminBlock =
          '<div class="admin-actions">' +
          '<a class="btn btn-primary" href="buku-form.php?id=' +
          encodeURIComponent(book.id_buku) +
          '">Edit</a>' +
          '<form method="post" action="buku-hapus.php" style="display:inline" onsubmit="return confirm(\'Hapus buku ini?\');">' +
          '<input type="hidden" name="id_buku" value="' +
          escapeHtml(String(book.id_buku)) +
          '">' +
          '<button type="submit" class="btn btn-danger">Hapus</button></form></div>';
      }

      panelBody.innerHTML =
        '<img class="detail-cover" src="' +
        cover +
        '" alt="">' +
        '<h2>' +
        safeText(book.judul) +
        '</h2>' +
        '<p class="detail-meta">' +
        safeText(book.penulis) +
        '</p>' +
        '<dl class="detail-rows">' +
        '<div><dt>Penerbit</dt><dd>' +
        safeText(book.penerbit) +
        '</dd></div>' +
        '<div><dt>Tahun terbit</dt><dd>' +
        safeText(book.tahun_terbit) +
        '</dd></div>' +
        '<div><dt>Jumlah halaman</dt><dd>' +
        safeText(book.jumlah_halaman) +
        '</dd></div>' +
        '<div><dt>Rak</dt><dd>' +
        safeText(book.rak) +
        '</dd></div>' +
        '<div><dt>Stok</dt><dd>' +
        safeText(book.stok) +
        '</dd></div>' +
        '<div><dt>Status</dt><dd><span class="book-card-status ' +
        (stok > 0 ? 'status-tersedia' : 'status-habis') +
        '">' +
        statusLabel +
        '</span></dd></div>' +
        '</dl>' +
        '<div class="detail-desc">' +
        safeText(book.deskripsi) +
        '</div>' +
        adminBlock;
    }

    grid.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var card = e.target.closest('[data-book-card]');
      if (!card) return;
      e.preventDefault();
      card.click();
    });

    grid.addEventListener('click', function (e) {
      var card = e.target.closest('[data-book-card]');
      if (!card) return;
      var id = card.getAttribute('data-id');
      if (!id) return;
      setSelected(id);
      showLoading();
      if (layout && window.matchMedia('(max-width: 1024px)').matches) {
        layout.classList.add('panel-open');
      }
      fetch(apiBase + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function (r) {
          if (!r.ok) throw new Error('fail');
          return r.json();
        })
        .then(render)
        .catch(showError);
    });

    var closeBtn = document.querySelector('[data-panel-close]');
    if (closeBtn && layout) {
      closeBtn.addEventListener('click', function () {
        layout.classList.remove('panel-open');
      });
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initDropdowns();
    initBookSearch();
  });
})();
