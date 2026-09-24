// Navbar toggle
const navToggle = document.getElementById('navToggle');
const navbar = document.querySelector('.navbar');
if (navToggle) {
    navToggle.addEventListener('click', () => navbar.classList.toggle('open'));
}

// Qty buttons
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.closest('.qty-control').querySelector('.qty-input');
        let val = parseInt(input.value) || 1;
        if (this.dataset.action === 'plus') val++;
        if (this.dataset.action === 'minus' && val > 1) val--;
        const max = parseInt(input.max) || 999;
        if (val > max) val = max;
        input.value = val;
    });
});

// Toast notification
function showToast(message) {
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Show toast from URL param
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success')) showToast(decodeURIComponent(urlParams.get('success')));

// Confirm delete
document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Yakin ingin menghapus data ini?')) e.preventDefault();
    });
});

// Image preview
const imgInput = document.getElementById('gambar_produk');
if (imgInput) {
    imgInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                let preview = document.getElementById('imgPreview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'imgPreview';
                    preview.style.cssText = 'max-width:200px;max-height:150px;margin-top:12px;border-radius:8px;object-fit:cover;border:2px solid var(--cream-dark)';
                    this.parentElement.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}
