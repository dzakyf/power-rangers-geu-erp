(function () {
    var PING_URL = '/mobile/ping.php';
    var checking = false;

    function toast(msg) {
        var el = document.getElementById('offline-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'offline-toast';
            el.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);' +
                'background:#dc3545;color:#fff;padding:10px 18px;border-radius:24px;font:14px sans-serif;' +
                'z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,.25);max-width:90%;text-align:center;display:none';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        el.style.display = 'block';
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.display = 'none'; }, 3500);
    }

    function cekServer() {
        if (!navigator.onLine) return Promise.resolve(false);
        var ctrl = new AbortController();
        var t = setTimeout(function () { ctrl.abort(); }, 4000);
        return fetch(PING_URL + '?t=' + Date.now(), {
            cache: 'no-store', credentials: 'same-origin', signal: ctrl.signal
        }).then(function (r) { clearTimeout(t); return r.ok; })
          .catch(function () { clearTimeout(t); return false; });
    }

    // Untuk perpindahan halaman lewat JavaScript: bukaHalaman('logout.php')
    window.bukaHalaman = function (url) {
        if (checking) return;
        checking = true;
        cekServer().then(function (ok) {
            checking = false;
            if (ok) { window.location.href = url; }
            else { toast('Tidak ada koneksi atau server tidak dapat dihubungi.'); }
        });
    };

    // Untuk semua link (menu, tombol) di halaman
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^(javascript:|tel:|mailto:|geo:)/i.test(href)) return;
        if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle')) return;
        if (a.origin && a.origin !== location.origin) return;

        e.preventDefault();
        window.bukaHalaman(a.href);
    });

    window.addEventListener('offline', function () { toast('Koneksi terputus.'); });
    window.addEventListener('online',  function () { toast('Koneksi kembali.'); });
})();