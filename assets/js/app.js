/**
 * SponsorFlow - Core Vanilla JavaScript Utilities
 */

// Helper shortcut
const $ = (id) => document.getElementById(id);

/**
 * Format currency in Indian Rupees
 */
function formatCurrency(amount) {
  return '₹' + Number(amount || 0).toLocaleString('en-IN');
}

/**
 * Format Date to readable format
 */
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d) ? dateStr : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

/**
 * Escapes HTML characters to prevent XSS
 */
function escapeHTML(str) {
  if (str === null || str === undefined) return '';
  return String(str).replace(/[&<>"']/g, c => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[c]));
}

/**
 * Shows transient toast notification
 */
function showToast(message, duration = 2800) {
  let toast = $('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.className = 'show';
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.className = '';
  }, duration);
}

/**
 * Open dialog modal
 */
function openModal(id) {
  const dlg = $(id);
  if (dlg && typeof dlg.showModal === 'function') {
    dlg.showModal();
  }
}

/**
 * Close dialog modal
 */
function closeModal(id) {
  const dlg = $(id);
  if (dlg && typeof dlg.close === 'function') {
    dlg.close();
  }
}

/**
 * Confirm delete prompt
 */
function confirmDelete(message = 'Are you sure you want to delete this record? This action cannot be undone.') {
  return confirm(message);
}

/**
 * Generic client-side form validator
 */
function validateForm(form, rules = {}) {
  let isValid = true;
  for (const [fieldName, rule] of Object.entries(rules)) {
    const input = form.elements[fieldName];
    const errSpan = $(`err_${fieldName}`) || form.querySelector(`.err[data-for="${fieldName}"]`);
    if (!input) continue;

    const val = input.value.trim();
    let msg = '';

    if (rule.required && !val) {
      msg = `${rule.label || fieldName} is required.`;
    } else if (val) {
      if (rule.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) {
        msg = 'Please enter a valid email address.';
      } else if (rule.type === 'phone' && !/^[6-9]\d{9}$/.test(val.replace(/\D/g, ''))) {
        msg = 'Please enter a valid 10-digit Indian phone number.';
      } else if (rule.type === 'number' && (isNaN(val) || Number(val) < 0)) {
        msg = 'Must be a valid positive amount.';
      } else if (rule.custom) {
        msg = rule.custom(val, form) || '';
      }
    }

    if (errSpan) {
      errSpan.textContent = msg;
    }
    if (msg) isValid = false;
  }
  return isValid;
}

// Global dismiss handlers for dialog close buttons
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('dialog [data-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const dlg = e.target.closest('dialog');
      if (dlg) dlg.close();
    });
  });

  // Automatically show flash toast if flash payload exists
  const flashEl = document.querySelector('[data-flash-msg]');
  if (flashEl) {
    showToast(flashEl.dataset.flashMsg);
  }
});
