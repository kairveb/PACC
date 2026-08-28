

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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-phone-input]').forEach(enforcePhilippinePhoneInput);
});

Alpine.start();
