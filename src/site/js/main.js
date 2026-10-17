// Logica Principale: Dark/Light Mode, Lingua, Modale Privacy, FAQ
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initLanguage();
  initYear();
});

/* ==========================================
   GESTIONE DARK / LIGHT MODE
   ========================================== */
function initTheme() {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
    applyTheme('dark');
  } else {
    applyTheme('light');
  }

  // Ascolta cambi preferenze OS se l'utente non ha impostato un override esplicito
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
      applyTheme(e.matches ? 'dark' : 'light');
    }
  });
}

function applyTheme(theme) {
  const lightIcon = document.getElementById('theme-icon-light');
  const darkIcon = document.getElementById('theme-icon-dark');
  const logoPath = document.querySelectorAll('.logo-stroke-adaptive');

  if (theme === 'dark') {
    document.documentElement.classList.add('dark');
    if (lightIcon && darkIcon) {
      lightIcon.classList.remove('hidden');
      darkIcon.classList.add('hidden');
    }
    // Adatta colore stroke del logo al tema scuro
    logoPath.forEach(el => el.setAttribute('stroke', '#f8fafc'));
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    if (lightIcon && darkIcon) {
      lightIcon.classList.add('hidden');
      darkIcon.classList.remove('hidden');
    }
    // Adatta colore stroke del logo al tema chiaro
    logoPath.forEach(el => el.setAttribute('stroke', '#0f172a'));
    localStorage.setItem('theme', 'light');
  }
}

function toggleTheme() {
  const isDark = document.documentElement.classList.contains('dark');
  applyTheme(isDark ? 'light' : 'dark');
}

/* ==========================================
   GESTIONE LINGUA (I18N)
   ========================================== */
let currentLanguage = 'it';

function initLanguage() {
  const savedLang = localStorage.getItem('lang') || 'it';
  setLanguage(savedLang);
}

function setLanguage(lang) {
  if (typeof translations === 'undefined' || !translations[lang]) return;
  currentLanguage = lang;
  localStorage.setItem('lang', lang);

  document.documentElement.lang = lang;

  const itBtn = document.getElementById('lang-it-btn');
  const enBtn = document.getElementById('lang-en-btn');
  if (itBtn && enBtn) {
    if (lang === 'it') {
      itBtn.classList.add('tab-active');
      enBtn.classList.remove('tab-active');
    } else {
      enBtn.classList.add('tab-active');
      itBtn.classList.remove('tab-active');
    }
  }

  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const key = el.getAttribute('data-i18n');
    if (translations[lang][key]) {
      el.innerHTML = translations[lang][key];
    }
  });
}

/* ==========================================
   FAQ ACCORDION
   ========================================== */
function toggleFaq(button) {
  const content = button.nextElementSibling;
  const arrow = button.querySelector('svg');
  const isHidden = content.classList.contains('hidden');

  document.querySelectorAll('#faq-container .faq-answer').forEach((el) => el.classList.add('hidden'));
  document.querySelectorAll('#faq-container svg.faq-arrow').forEach((el) => el.classList.remove('rotate-180'));

  if (isHidden) {
    content.classList.remove('hidden');
    arrow.classList.add('rotate-180');
  }
}

/* ==========================================
   MODALE PRIVACY
   ========================================== */
function openPrivacyModal() {
  const modal = document.getElementById('privacy-modal');
  const dialog = document.getElementById('privacy-dialog');
  if (!modal || !dialog) return;

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    dialog.classList.remove('scale-95');
    dialog.classList.add('scale-100');
  }, 10);
  document.body.style.overflow = 'hidden';
}

function closePrivacyModal() {
  const modal = document.getElementById('privacy-modal');
  const dialog = document.getElementById('privacy-dialog');
  if (!modal || !dialog) return;

  modal.classList.add('opacity-0');
  dialog.classList.remove('scale-100');
  dialog.classList.add('scale-95');
  setTimeout(() => {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }, 200);
}

// Chiusura al click sul backdrop
document.addEventListener('click', (e) => {
  const modal = document.getElementById('privacy-modal');
  if (modal && e.target === modal) {
    closePrivacyModal();
  }
});

// Chiusura con tasto Esc
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closePrivacyModal();
  }
});

/* ==========================================
   FOOTER YEAR
   ========================================== */
function initYear() {
  const yearSpan = document.getElementById('year-span');
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }
}