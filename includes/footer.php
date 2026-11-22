</main>
<script>
// Menu sidebar di HP
document.querySelector('.menu-toggle')?.addEventListener('click', () => document.body.classList.toggle('menu-open'));
document.querySelector('.overlay')?.addEventListener('click', () => document.body.classList.remove('menu-open'));
// Pesan sukses/gagal hilang otomatis setelah 4 detik
const f = document.querySelector('.flash[data-auto]');
if (f) setTimeout(() => { f.style.opacity = '0'; setTimeout(() => f.remove(), 300); }, 4000);
</script>
</body>
</html>
