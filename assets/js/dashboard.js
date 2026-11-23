// assets/js/dashboard.js - segarkan dashboard otomatis tiap 10 detik
(function () {
    var kotak = document.getElementById('auto-refresh');
    if (!kotak) { return; }
    var KUNCI = 'iot-auto-refresh';

    // Ingat pilihan pengguna (jika penyimpanan browser tersedia); bawaan: aktif
    try { kotak.checked = localStorage.getItem(KUNCI) !== 'off'; } catch (e) { kotak.checked = true; }

    kotak.addEventListener('change', function () {
        try { localStorage.setItem(KUNCI, kotak.checked ? 'on' : 'off'); } catch (e) {}
    });

    setInterval(function () {
        var el = document.activeElement;
        // Jangan menyegarkan saat pengguna sedang mengisi filter
        var sedangMengisi = el && el !== kotak && (el.tagName === 'INPUT' || el.tagName === 'SELECT');
        if (kotak.checked && !sedangMengisi && !document.hidden) { location.reload(); }
    }, 10000);
})();
