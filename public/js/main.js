'use strict';
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', e => {
            const email = loginForm.querySelector('[name="email"]').value.trim();
            const pwd   = loginForm.querySelector('[name="password"]').value;
            if (!email || !pwd) { e.preventDefault(); showAlert(loginForm, 'Remplissez tous les champs.', 'error'); }
        });
    }
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', e => {
            const pwd     = registerForm.querySelector('[name="password"]').value;
            const confirm = registerForm.querySelector('[name="confirm"]').value;
            if (pwd.length < 6) { e.preventDefault(); showAlert(registerForm, 'Mot de passe minimum 6 caractères.', 'error'); }
            else if (pwd !== confirm) { e.preventDefault(); showAlert(registerForm, 'Les mots de passe ne correspondent pas.', 'error'); }
        });
    }
    const imgInput = document.querySelector('input[type="file"][name="image"]');
    if (imgInput) {
        imgInput.addEventListener('change', () => {
            const file = imgInput.files[0];
            if (!file) return;
            if (file.size > 5*1024*1024) { alert('Max 5MB.'); imgInput.value=''; return; }
            if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { alert('JPEG/PNG/WEBP uniquement.'); imgInput.value=''; return; }
            let p = document.getElementById('img-preview');
            if (!p) { p=document.createElement('img'); p.id='img-preview'; p.style.cssText='width:90px;height:60px;object-fit:cover;border-radius:6px;margin-top:.5rem;display:block'; imgInput.parentNode.appendChild(p); }
            p.src = URL.createObjectURL(file);
        });
    }
});
function showAlert(form, msg, type) {
    const old = form.querySelector('.js-alert'); if (old) old.remove();
    const d = document.createElement('div');
    d.className = `alert alert-${type==='error'?'error':'success'} js-alert`;
    d.textContent = msg; form.prepend(d);
    setTimeout(() => d.remove(), 5000);
}
