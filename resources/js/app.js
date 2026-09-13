

import Alpine from 'alpinejs';
import './api.js';

window.Alpine = Alpine;

const enforcePhilippinePhoneInput = (input) => {
    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    const formatValue = (value) => {
        const trimmed = value.trim();
        if (! trimmed) {
            return '';
        }

        const digitsOnly = trimmed.replace(/\D/g, '');
        const hasPlus = trimmed.startsWith('+');

        if (hasPlus) {
            return '+' + digitsOnly.slice(0, 12);
        }

        return digitsOnly.slice(0, 11);
    };

    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('autocomplete', 'tel');
    input.setAttribute('pattern', '^(09\\d{9}|\\+639\\d{9})$');

    input.addEventListener('input', () => {
        input.value = formatValue(input.value);
    });
};

const showGlobalToast = (message, type = 'info') => {
    if (!message) {
        return;
    }

    const existing = document.getElementById('global-his-toast');
    if (existing) {
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'global-his-toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    toast.className = 'fixed right-4 top-4 z-[9999] max-w-sm rounded-xl border px-4 py-3 text-sm font-medium shadow-lg backdrop-blur';

    const palette = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        danger: 'border-rose-200 bg-rose-50 text-rose-700',
        warning: 'border-amber-200 bg-amber-50 text-amber-700',
        info: 'border-sky-200 bg-sky-50 text-sky-700',
    };

    toast.className += ` ${palette[type] || palette.info}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3500);
};

window.hisToast = showGlobalToast;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-phone-input]').forEach(enforcePhilippinePhoneInput);
});

Alpine.start();
