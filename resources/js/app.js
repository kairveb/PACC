

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

const initializeFilterPanels = () => {
    const triggers = [...document.querySelectorAll('[data-filter-trigger]')];
    const panels = [...document.querySelectorAll('.filter-panel')];
    const hoverState = new Map();
    const closeDelayMs = 250;

    if (!triggers.length) {
        return;
    }

    const getPanel = (trigger) => document.getElementById(trigger.getAttribute('data-filter-target'));

    const positionPanel = (trigger, panel) => {
        const triggerRect = trigger.getBoundingClientRect();
        const panelHeight = panel.getBoundingClientRect().height;
        const panelWidth = panel.getBoundingClientRect().width;
        const spaceBelow = window.innerHeight - triggerRect.bottom;
        const left = Math.max(8, Math.min(triggerRect.left, window.innerWidth - panelWidth - 8));

        panel.style.left = `${left}px`;
        panel.style.right = 'auto';
        if (panelHeight > spaceBelow) {
            panel.style.top = `${Math.max(8, triggerRect.top - panelHeight - 8)}px`;
            panel.style.bottom = 'auto';
            panel.style.marginTop = '0';
            panel.style.marginBottom = '0';
        } else {
            panel.style.top = `${triggerRect.bottom + 8}px`;
            panel.style.bottom = 'auto';
            panel.style.marginTop = '0';
            panel.style.marginBottom = '0';
        }
    };

    const closePanels = () => {
        panels.forEach((panel) => panel.classList.add('hidden'));
        triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
    };

    const openPanel = (trigger) => {
        const panel = getPanel(trigger);
        if (!panel) {
            return;
        }

        closePanels();
        if (panel.parentElement !== document.body) {
            document.body.appendChild(panel);
        }
        panel.style.position = 'fixed';
        panel.style.zIndex = '1080';
        panel.classList.remove('hidden');
        positionPanel(trigger, panel);
        trigger.setAttribute('aria-expanded', 'true');
    };

    const cancelClose = (trigger) => {
        const state = hoverState.get(trigger);
        if (state?.closeTimer) {
            clearTimeout(state.closeTimer);
            state.closeTimer = null;
        }
    };

    const scheduleClose = (trigger) => {
        const state = hoverState.get(trigger);
        if (!state || state.triggerHovered || state.panelHovered) {
            return;
        }

        cancelClose(trigger);
        state.closeTimer = setTimeout(() => {
            if (!state.triggerHovered && !state.panelHovered) {
                getPanel(trigger)?.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
            }
        }, closeDelayMs);
    };

    triggers.forEach((trigger) => {
        const panel = getPanel(trigger);
        if (!panel) {
            return;
        }

        const state = { triggerHovered: false, panelHovered: false, closeTimer: null };
        hoverState.set(trigger, state);

        trigger.addEventListener('mouseenter', () => {
            state.triggerHovered = true;
            cancelClose(trigger);
            openPanel(trigger);
        });

        trigger.addEventListener('mouseleave', () => {
            state.triggerHovered = false;
            scheduleClose(trigger);
        });

        panel.addEventListener('mouseenter', () => {
            state.panelHovered = true;
            cancelClose(trigger);
        });

        panel.addEventListener('mouseleave', () => {
            state.panelHovered = false;
            scheduleClose(trigger);
        });

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = !panel.classList.contains('hidden');

            closePanels();

            if (!isOpen) {
                openPanel(trigger);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-filter-trigger]') && !event.target.closest('.filter-panel')) {
            closePanels();
        }
    });

    window.addEventListener('resize', () => {
        triggers.forEach((trigger) => {
            const panel = getPanel(trigger);
            if (panel && !panel.classList.contains('hidden')) {
                positionPanel(trigger, panel);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-phone-input]').forEach(enforcePhilippinePhoneInput);
    initializeFilterPanels();
});

Alpine.start();
