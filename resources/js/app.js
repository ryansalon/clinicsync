/**
 * ClinicSync UI interactions.
 *
 * Click-triggered behaviour is bound with document-level delegation so that
 * any markup swapped in later (live queue polling) keeps working without re-init.
 *
 * - Mobile sidebar with focus management
 * - Dismissible flash messages
 * - Accessible dropdown menus (notifications, row action menus)
 * - Confirmation modal: Escape, focus trap, focus restore
 * - Double-submit guard with a visible busy state
 * - Password visibility toggles
 * - Debounced search/filter forms
 * - Light / dark theme toggle (preference kept in localStorage)
 * - Live queue polling
 * - Patient search combobox (walk-in registration)
 * - Ribbon Field WebGL background (ThreeUI port, login page)
 */

import { mountRibbonField } from './ribbon-field.js';

document.addEventListener('DOMContentLoaded', () => {
    const FOCUSABLE =
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /** Smooth-scroll that respects the user's motion preference. */
    window.clinicScrollTo = (element, block = 'center') => {
        element.scrollIntoView({
            behavior: prefersReducedMotion() ? 'auto' : 'smooth',
            block,
        });
    };

    /* ------------------------------------------------------------------ */
    /* Mobile sidebar                                                       */
    /* ------------------------------------------------------------------ */
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const mainContent = document.getElementById('main-content');
    const pageHeader = document.querySelector('header');
    let sidebarOpen = false;
    let sidebarReturnFocus = null;

    const openSidebar = () => {
        if (!sidebar || !backdrop || sidebarOpen) {
            return;
        }

        sidebarOpen = true;
        sidebarReturnFocus = document.activeElement;
        sidebar.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.querySelectorAll('[data-sidebar-open]').forEach((button) => button.setAttribute('aria-expanded', 'true'));

        // Keep keyboard focus inside the drawer while it is open.
        if ('inert' in HTMLElement.prototype) {
            mainContent?.setAttribute('inert', '');
            pageHeader?.setAttribute('inert', '');
        }

        sidebar.querySelector(FOCUSABLE)?.focus();
    };

    const closeSidebar = () => {
        if (!sidebar || !backdrop || !sidebarOpen) {
            return;
        }

        sidebarOpen = false;
        sidebar.classList.add('hidden');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.querySelectorAll('[data-sidebar-open]').forEach((button) => button.setAttribute('aria-expanded', 'false'));

        mainContent?.removeAttribute('inert');
        pageHeader?.removeAttribute('inert');

        if (sidebarReturnFocus instanceof HTMLElement) {
            sidebarReturnFocus.focus();
        }
        sidebarReturnFocus = null;
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-sidebar-open]')) {
            openSidebar();
        }

        if (event.target.closest('[data-sidebar-close]')) {
            closeSidebar();
        }
    });

    /* ------------------------------------------------------------------ */
    /* Flash messages                                                       */
    /* ------------------------------------------------------------------ */
    const dismissFlash = (flash) => {
        flash.style.transition = 'opacity 0.2s ease-out';
        flash.style.opacity = '0';

        window.setTimeout(() => flash.remove(), 200);
    };

    document.querySelectorAll('[data-flash]').forEach((flash, index) => {
        const errors = flash.querySelector('.alert-error');

        // Success toasts fade on their own; validation errors stay until dismissed.
        if (!errors) {
            window.setTimeout(() => dismissFlash(flash), 5000 + index * 150);
        }
    });

    document.addEventListener('click', (event) => {
        const dismiss = event.target.closest('[data-flash-dismiss]');
        if (dismiss) {
            const flash = dismiss.closest('[data-flash]');
            if (flash) {
                dismissFlash(flash);
            }
        }
    });

    /* ------------------------------------------------------------------ */
    /* Dropdown menus (notification bell, row action menus)                 */
    /* ------------------------------------------------------------------ */
    const dropdowns = () => Array.from(document.querySelectorAll('[data-dropdown]'));

    const setDropdownState = (dropdown, open) => {
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        if (!menu || !trigger) {
            return;
        }

        menu.classList.toggle('hidden', !open);
        trigger.setAttribute('aria-expanded', String(open));
    };

    const closeDropdowns = (except = null) => {
        dropdowns().forEach((dropdown) => {
            if (dropdown !== except) {
                setDropdownState(dropdown, false);
            }
        });
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dropdown-trigger]');

        if (trigger) {
            const dropdown = trigger.closest('[data-dropdown]');
            const willOpen = dropdown.querySelector('[data-dropdown-menu]')?.classList.contains('hidden') ?? false;

            closeDropdowns(dropdown);
            setDropdownState(dropdown, willOpen);

            return;
        }

        if (!event.target.closest('[data-dropdown-menu]')) {
            closeDropdowns();
        }
    });

    /* ------------------------------------------------------------------ */
    /* Confirmation modal for destructive actions                           */
    /* ------------------------------------------------------------------ */
    const modal = document.getElementById('confirm-modal');

    if (modal) {
        const message = document.getElementById('confirm-modal-message');
        const confirmButton = document.getElementById('confirm-modal-go');
        let pendingElement = null;
        let modalReturnFocus = null;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingElement = null;

            if (modalReturnFocus instanceof HTMLElement) {
                modalReturnFocus.focus();
            }
            modalReturnFocus = null;
        };

        const openModal = (element) => {
            pendingElement = element;
            modalReturnFocus = document.activeElement;
            message.textContent =
                element.dataset.confirm || 'This action cannot be undone. Please confirm you want to continue.';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            confirmButton.focus();
        };

        document.addEventListener('click', (event) => {
            const element = event.target.closest('[data-confirm]');
            if (!element) {
                return;
            }

            event.preventDefault();
            openModal(element);
        });

        modal.querySelectorAll('[data-confirm-dismiss]').forEach((element) => {
            element.addEventListener('click', closeModal);
        });

        confirmButton.addEventListener('click', () => {
            const element = pendingElement;

            closeModal();

            if (!element) {
                return;
            }

            // Drop the guard first so the replayed action is not intercepted again.
            element.removeAttribute('data-confirm');

            if (element.tagName === 'FORM') {
                element.requestSubmit();

                return;
            }

            element.click();
        });

        document.addEventListener('keydown', (event) => {
            if (modal.classList.contains('hidden')) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeModal();

                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            // Trap focus inside the dialog.
            const focusable = Array.from(modal.querySelectorAll(FOCUSABLE)).filter((el) => el.offsetParent !== null);
            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Double-submit guard                                                  */
    /* ------------------------------------------------------------------ */
    const releaseForm = (form) => {
        delete form.dataset.busy;
        form.removeAttribute('aria-busy');
        form.querySelectorAll('[data-busy]').forEach((button) => {
            delete button.dataset.busy;
            button.disabled = false;
        });
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
            return;
        }

        if (form.dataset.guard === 'off') {
            return;
        }

        // A second submit while one is in flight would duplicate records.
        if (form.dataset.busy === 'true') {
            event.preventDefault();

            return;
        }

        form.dataset.busy = 'true';
        form.setAttribute('aria-busy', 'true');

        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.dataset.busy = '';
            button.disabled = true;
        });

        // Safety valve: never leave a form stuck if the request is blocked.
        window.setTimeout(() => releaseForm(form), 15000);
    });

    /* ------------------------------------------------------------------ */
    /* Password visibility toggles                                          */
    /* ------------------------------------------------------------------ */
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-password-toggle]');
        if (!toggle) {
            return;
        }

        const input = document.querySelector(toggle.dataset.passwordToggle);
        if (!input) {
            return;
        }

        const revealing = input.type === 'password';
        input.type = revealing ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(revealing));
        toggle.textContent = revealing ? 'Hide' : 'Show';
        input.focus();
    });

    /* ------------------------------------------------------------------ */
    /* Light / dark theme toggle                                            */
    /* ------------------------------------------------------------------ */
    const THEME_STORAGE_KEY = 'clinicsync-theme';

    // Guest (auth) pages and the authenticated shell each carry their own
    // dark scope class; light mode on guest pages swaps to `guest-light`.
    const isGuestShell = document.body.dataset.shell === 'guest';
    const DARK_CLASS = isGuestShell ? 'guest-dark' : 'app-dark';
    const LIGHT_CLASS = isGuestShell ? 'guest-light' : null;

    const isDarkTheme = () => document.body.classList.contains(DARK_CLASS);

    const applyTheme = (dark) => {
        if (LIGHT_CLASS) {
            document.body.classList.toggle(DARK_CLASS, dark);
            document.body.classList.toggle(LIGHT_CLASS, !dark);
        } else {
            document.body.classList.toggle(DARK_CLASS, dark);
        }
    };

    /** Keep the toggle's accessible name describing the action it will do. */
    const syncThemeToggleLabels = () => {
        const label = isDarkTheme() ? 'Switch to light mode' : 'Switch to dark mode';

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        });
    };

    syncThemeToggleLabels();

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-theme-toggle]')) {
            return;
        }

        applyTheme(!isDarkTheme());

        try {
            localStorage.setItem(THEME_STORAGE_KEY, isDarkTheme() ? 'dark' : 'light');
        } catch (error) {
            // Storage unavailable — the theme still applies to this page.
        }

        syncThemeToggleLabels();
    });

    // Follow the choice made in another open tab.
    window.addEventListener('storage', (event) => {
        if (event.key !== THEME_STORAGE_KEY || (event.newValue !== 'dark' && event.newValue !== 'light')) {
            return;
        }

        applyTheme(event.newValue === 'dark');
        syncThemeToggleLabels();
    });

    /* ------------------------------------------------------------------ */
    /* Popover details panels (create forms in headers)                     */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('details[data-popover]').forEach((details) => {
        // If the form bounced back with validation errors, keep it open and focus the first invalid field.
        if (details.querySelector('.input-invalid, .field-error')) {
            details.open = true;
            window.setTimeout(() => {
                details.querySelector('.input-invalid, input:not([type="hidden"]), select, textarea')?.focus();
            }, 50);
        }

        details.addEventListener('toggle', () => {
            if (details.open) {
                // Focus the first form input when opening
                window.setTimeout(() => {
                    details.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])')?.focus();
                }, 50);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('details[data-popover]')) {
            document.querySelectorAll('details[data-popover][open]').forEach((details) => {
                details.open = false;
            });
        }
    });

    /* ------------------------------------------------------------------ */
    /* Escape closes open layers and returns focus to the trigger           */
    /* ------------------------------------------------------------------ */
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const openPopover = document.querySelector('details[data-popover][open]');
        if (openPopover) {
            openPopover.open = false;
            openPopover.querySelector('summary')?.focus();

            return;
        }

        const openDropdown = dropdowns().find(
            (dropdown) => !dropdown.querySelector('[data-dropdown-menu]')?.classList.contains('hidden'),
        );

        if (openDropdown) {
            closeDropdowns();
            openDropdown.querySelector('[data-dropdown-trigger]')?.focus();

            return;
        }

        closeSidebar();
    });

    /* ------------------------------------------------------------------ */
    /* Debounced search + auto-applying filters                             */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-search-form]').forEach((form) => {
        const input = form.querySelector('input[type="search"], input[type="text"][name]');
        if (!input) {
            return;
        }

        const initialValue = input.value;
        let timer = null;

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => {
                if (input.value === initialValue) {
                    return;
                }

                form.requestSubmit();
            }, 400);
        });
    });

    document.querySelectorAll('[data-auto-submit]').forEach((input) => {
        input.addEventListener('change', () => {
            input.form?.requestSubmit();
        });
    });

    /* ------------------------------------------------------------------ */
    /* Live queue polling                                                   */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-poll]').forEach((container) => {
        const url = container.dataset.pollUrl;
        const interval = Number.parseInt(container.dataset.pollInterval, 10) || 30000;
        let lastHtml = null;
        let timer = null;
        let inFlight = false;

        const refresh = async () => {
            // Never yank content out from under someone who is using it.
            if (inFlight || document.hidden || container.contains(document.activeElement)) {
                return;
            }

            inFlight = true;

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const html = typeof payload.html === 'string' ? payload.html : null;

                if (html === null || html === lastHtml) {
                    return;
                }

                lastHtml = html;
                container.innerHTML = html;
            } catch (error) {
                // Transient network failure — the next tick will retry.
            } finally {
                inFlight = false;
            }
        };

        const start = () => {
            window.clearInterval(timer);
            timer = window.setInterval(refresh, interval);
        };

        const stop = () => window.clearInterval(timer);

        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

        start();
    });

    /* ------------------------------------------------------------------ */
    /* Patient search combobox (walk-in registration)                       */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-combobox]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-combobox-input]');
        const hidden = wrapper.querySelector('[data-combobox-value]');
        const listbox = wrapper.querySelector('[data-combobox-listbox]');
        const status = wrapper.querySelector('[data-combobox-status]');
        const url = wrapper.dataset.combobox;

        if (!input || !hidden || !listbox) {
            return;
        }

        let options = [];
        let activeIndex = -1;
        let selectedLabel = input.value;
        let timer = null;
        let requestId = 0;

        const close = () => {
            listbox.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        };

        const highlight = (index) => {
            const items = listbox.querySelectorAll('[role="option"]');

            items.forEach((item, itemIndex) => {
                const active = itemIndex === index;
                item.setAttribute('aria-selected', String(active));
                item.classList.toggle('bg-teal-50', active);
            });

            activeIndex = index;

            if (index >= 0 && items[index]) {
                input.setAttribute('aria-activedescendant', items[index].id);
                items[index].scrollIntoView({ block: 'nearest' });
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        };

        const choose = (index) => {
            const option = options[index];
            if (!option) {
                return;
            }

            hidden.value = option.id;
            input.value = option.label;
            selectedLabel = option.label;
            if (status) {
                status.textContent = '';
            }
            close();
        };

        const render = (results) => {
            options = results;
            listbox.innerHTML = '';
            activeIndex = -1;

            if (results.length === 0) {
                close();
                if (status) {
                    status.textContent = input.value.trim().length >= 2 ? 'No patients match that search.' : '';
                }

                return;
            }

            results.forEach((option, index) => {
                const item = document.createElement('li');
                item.id = `${input.id}-option-${index}`;
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', 'false');
                item.className = 'cursor-pointer px-3 py-2 text-sm text-slate-700';
                item.innerHTML =
                    '<span class="font-medium text-slate-900"></span><span class="block text-xs text-slate-500"></span>';
                item.children[0].textContent = option.name;
                item.children[1].textContent = option.patient_number;

                item.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    choose(index);
                });

                listbox.appendChild(item);
            });

            listbox.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            if (status) {
                status.textContent = `${results.length} match${results.length === 1 ? '' : 'es'}. Use the arrow keys to choose one.`;
            }
        };

        const search = async () => {
            const query = input.value.trim();

            hidden.value = '';
            selectedLabel = '';

            if (query.length < 2) {
                close();
                if (status) {
                    status.textContent = '';
                }

                return;
            }

            const current = ++requestId;

            try {
                const response = await fetch(`${url}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok || current !== requestId) {
                    return;
                }

                render(await response.json());
            } catch (error) {
                if (status) {
                    status.textContent = 'Search failed. Please try again.';
                }
            }
        };

        input.addEventListener('input', () => {
            if (input.value !== selectedLabel) {
                hidden.value = '';
                selectedLabel = '';
            }

            window.clearTimeout(timer);
            timer = window.setTimeout(search, 250);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();

                if (listbox.hidden && options.length > 0) {
                    listbox.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                }

                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const next = activeIndex === -1 ? (direction === 1 ? 0 : options.length - 1) : activeIndex + direction;

                highlight(next < 0 ? options.length - 1 : next % options.length);

                return;
            }

            if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                choose(activeIndex);

                return;
            }

            if (event.key === 'Escape') {
                close();
            }
        });

        input.addEventListener('blur', () => window.setTimeout(close, 150));
    });

    /* ------------------------------------------------------------------ */
    /* Ribbon Field background (ThreeUI port, login page)                  */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-ribbon-field]').forEach((host) => {
        try {
            mountRibbonField(host, {
                speed: 1,
                pointerAmount: 1,
                smoothing: 0.035,
                hue: 0,
                saturation: 1,
                brightness: 1,
                opacity: 1,
            });
        } catch (error) {
            // WebGL unavailable — the page keeps its flat background.
        }
    });
});
