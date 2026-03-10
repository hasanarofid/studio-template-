// Intersection Observer for Animations
const observerOptions = {
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in').forEach(element => {
    observer.observe(element);
});

// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const item = button.parentElement;
        const isActive = item.classList.contains('active');
        
        // Close all other items
        document.querySelectorAll('.faq-item').forEach(otherItem => {
            otherItem.classList.remove('active');
        });

        if (!isActive) {
            item.classList.add('active');
        }
    });
});

// Header scroll effect
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.style.padding = '10px 0';
        header.style.boxShadow = 'var(--shadow)';
    } else {
        header.style.padding = '0';
        header.style.boxShadow = 'none';
    }
});

// Auth Logic
const API_URL = './api';
const authModal = document.getElementById('auth-modal');
const loginBtn = document.getElementById('login-btn');
const closeModal = document.querySelector('.close-modal');
const authTabs = document.querySelectorAll('.auth-tab');
const authForms = document.querySelectorAll('.auth-form');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const userProfile = document.getElementById('user-profile');
const userNameDisplay = document.getElementById('user-name');
const logoutBtn = document.getElementById('logout-btn');
const toast = document.getElementById('toast');

// Toast Function
function showToast(message, isError = false) {
    if (!toast) return;
    toast.textContent = message;
    toast.className = `toast show ${isError ? 'error' : ''}`;
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

// Modal Toggle
if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        authModal.style.display = 'block';
    });
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        authModal.style.display = 'none';
    });
}

window.addEventListener('click', (e) => {
    if (e.target === authModal) {
        authModal.style.display = 'none';
    }
});

// Tab Switching
authTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        authTabs.forEach(t => t.classList.remove('active'));
        authForms.forEach(f => f.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(`${tab.dataset.tab}-form`).classList.add('active');
    });
});

// Register
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;

        try {
            const response = await fetch(`${API_URL}/user_register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password })
            });
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message);
                document.querySelector('[data-tab="login"]').click();
            } else {
                showToast(data.message, true);
            }
        } catch (error) {
            showToast('Gagal menghubungkan ke server.', true);
        }
    });
}

// Login
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        try {
            const response = await fetch(`${API_URL}/user_login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                showToast(data.message);
                authModal.style.display = 'none';
                updateAuthState();
            } else {
                showToast(data.message, true);
            }
        } catch (error) {
            showToast('Gagal menghubungkan ke server.', true);
        }
    });
}

// Logout
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        updateAuthState();
        showToast('Berhasil logout.');
    });
}

// Auth State
function updateAuthState() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (user && userProfile && loginBtn && userNameDisplay) {
        loginBtn.classList.add('hidden');
        userProfile.classList.remove('hidden');
        userNameDisplay.textContent = `Halo, ${user.name}`;
    } else if (loginBtn && userProfile) {
        loginBtn.classList.remove('hidden');
        userProfile.classList.add('hidden');
    }
}

// Initial State Check
document.addEventListener('DOMContentLoaded', () => {
    updateAuthState();
    fetchTemplates();
});

async function fetchTemplates() {
    const container = document.getElementById('templates-container');
    if (!container) return;

    try {
        const response = await fetch('./api/get_templates.php');
        const data = await response.json();
        
        if (data.success) {
            renderTemplates(data.templates);
            setupPaymentLogic(); // Re-bind payment listeners after rendering
        } else {
            container.innerHTML = `<div class="error">Gagal memuat template: ${data.message}</div>`;
        }
    } catch (error) {
        container.innerHTML = '<div class="error">Gagal menghubungkan ke server.</div>';
    }
}

function renderTemplates(templates) {
    const container = document.getElementById('templates-container');
    container.innerHTML = templates.map(tpl => `
        <div class="template-card ${tpl.category === 'UMKM' ? 'highlight' : ''}">
            <div class="template-img">
                <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&fit=crop&q=80&w=600" alt="${tpl.name}">
                <span class="category">${tpl.category}</span>
                ${tpl.category === 'UMKM' ? '<span class="category badge-popular">Best Seller</span>' : ''}
            </div>
            <div class="template-info">
                <h3>${tpl.name}</h3>
                <p>${tpl.description}</p>
                <div class="price">Rp${parseInt(tpl.price).toLocaleString('id-ID')}</div>
                <div class="template-actions">
                    <a href="${tpl.preview_url || '#'}" class="btn btn-sm btn-secondary">Preview</a>
                    <button class="btn btn-sm btn-primary buy-now-btn" 
                        data-id="${tpl.id}" 
                        data-name="${tpl.name}" 
                        data-price="${tpl.price}">Buy Now</button>
                </div>
            </div>
        </div>
    `).join('');
}

function setupPaymentLogic() {
    const paymentModal = document.getElementById('payment-modal');
    const closePaymentModal = document.querySelector('.close-payment-modal');
    const paymentForm = document.getElementById('payment-form');
    
    // Clean up old listeners (simple way for this context)
    // In a real app, use event delegation or more robust lifecycle management.
    
    // Open Modal
    document.querySelectorAll('.buy-now-btn').forEach(btn => {
        // Remove existing listener to avoid duplicates if called multiple times
        btn.onclick = () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const price = btn.dataset.price;
            
            document.getElementById('pay-template-id').value = id;
            document.getElementById('pay-amount').value = price;
            document.getElementById('payment-template-name').textContent = name;
            document.getElementById('payment-total').textContent = `Rp${parseInt(price).toLocaleString('id-ID')}`;

            const user = JSON.parse(localStorage.getItem('user'));
            if (user) {
                document.getElementById('pay-name').value = user.name;
                document.getElementById('pay-email').value = user.email;
            }
            
            paymentModal.style.display = 'block';
        };
    });

    if (closePaymentModal) {
        closePaymentModal.addEventListener('click', () => {
            paymentModal.style.display = 'none';
        });
    }

    // Submit Payment
    if (paymentForm) {
        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-payment');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            const formData = new FormData();
            formData.append('name', document.getElementById('pay-name').value);
            formData.append('email', document.getElementById('pay-email').value);
            formData.append('template_id', document.getElementById('pay-template-id').value);
            formData.append('amount', document.getElementById('pay-amount').value);
            formData.append('receipt', document.getElementById('pay-receipt').files[0]);

            try {
                const response = await fetch('./api/submit_payment.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message);
                    paymentModal.style.display = 'none';
                    paymentForm.reset();
                } else {
                    showToast(data.message, true);
                }
            } catch (error) {
                showToast('Gagal mengirim data ke server.', true);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Kirim Bukti Pembayaran';
            }
        });
    }
}
