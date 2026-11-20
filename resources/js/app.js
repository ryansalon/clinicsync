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
 * - Live queue polling with sync status feedback
 */

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
        if (prefersReducedMotion()) {
            flash.remove();
            return;
        }

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

        const openDrawer = document.querySelector('[data-drawer]:not(.hidden)');
        if (openDrawer) {
            openDrawer.dispatchEvent(new CustomEvent('drawer:close'));

            return;
        }

        const openDialog = document.querySelector('[data-dialog]:not(.hidden)');
        if (openDialog) {
            openDialog.dispatchEvent(new CustomEvent('dialog:close'));

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

        const setSyncText = (text, warn = false) => {
            const label = document.querySelector(`[data-poll-status="${container.id}"]`);
            if (!label) {
                return;
            }
            label.textContent = text;
            label.classList.toggle('text-warn', warn);
        };

        const refresh = async () => {
            // Never yank content out from under someone who is actively using a form or inspector drawer.
            if (
                inFlight
                || document.hidden
                || container.contains(document.activeElement)
                || document.querySelector('[data-drawer]:not(.hidden)')
            ) {
                return;
            }

            inFlight = true;

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.status === 401 || response.status === 419) {
                    stop();
                    setSyncText('Session paused', true);
                    if (!container.querySelector('[data-poll-expired]')) {
                        const note = document.createElement('div');
                        note.setAttribute('data-poll-expired', 'true');
                        note.setAttribute('role', 'status');
                        note.className = 'mb-3 rounded-lg border border-warn-border bg-warn-tint px-3 py-2 text-xs text-warn';
                        note.textContent = 'Live updates paused — your session may have expired. Refresh or sign in to resume.';
                        container.prepend(note);
                    }

                    return;
                }

                if (!response.ok) {
                    setSyncText('Reconnecting…', true);
                    return;
                }

                const payload = await response.json();
                const html = typeof payload.html === 'string' ? payload.html : null;
                const stamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                setSyncText(`Synced ${stamp}`, false);

                if (html === null || html === lastHtml) {
                    return;
                }

                lastHtml = html;
                container.innerHTML = html;
                container.dispatchEvent(new CustomEvent('clinicsync:polled', { bubbles: true }));
            } catch (error) {
                setSyncText('Offline — retrying', true);
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
    /* Accessible slide-over / modal dialogs with focus trap               */
    /* ------------------------------------------------------------------ */
    const appShell = document.getElementById('app-shell');

    document.querySelectorAll('[data-dialog]').forEach((dialog) => {
        let opener = null;

        const open = (trigger) => {
            opener = trigger || document.activeElement;
            dialog.classList.remove('hidden');
            dialog.classList.add('flex');
            if ('inert' in HTMLElement.prototype && appShell && !appShell.contains(dialog)) {
                appShell.setAttribute('inert', '');
            }
            const firstInput = dialog.querySelector('input:not([type="hidden"]), select, textarea, button');
            firstInput?.focus();
        };

        const close = () => {
            dialog.classList.add('hidden');
            dialog.classList.remove('flex');
            appShell?.removeAttribute('inert');
            opener?.focus?.();
            opener = null;
        };

        dialog.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab' || dialog.classList.contains('hidden')) {
                return;
            }

            const focusable = Array.from(dialog.querySelectorAll(FOCUSABLE)).filter((el) => el.offsetParent !== null);
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

        dialog.addEventListener('dialog:close', close);
        dialog.querySelectorAll('[data-dialog-close]').forEach((btn) => btn.addEventListener('click', close));

        document.querySelectorAll(`[data-dialog-open="${dialog.id}"]`).forEach((btn) => {
            btn.addEventListener('click', () => open(btn));
        });

        if (dialog.querySelector('.input-invalid, .field-error')) {
            open(null);
        }
    });

    /* ------------------------------------------------------------------ */
    /* Conditional role inputs (hide physician fields for other roles)    */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-role-picker]').forEach((select) => {
        const scope = select.closest('form') || document;
        const sync = () => {
            scope.querySelectorAll('[data-role-only]').forEach((block) => {
                const match = block.dataset.roleOnly === select.value;
                block.hidden = !match;
                block.querySelectorAll('input, select, textarea').forEach((el) => {
                    el.disabled = !match;
                });
            });
        };

        select.addEventListener('change', sync);
        sync();
    });

    /* ------------------------------------------------------------------ */
    /* Dirty-form guard for clinical notes                                */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('form[data-dirty-guard]').forEach((form) => {
        let dirty = false;

        form.addEventListener('input', () => {
            dirty = true;
        });

        form.addEventListener('change', () => {
            dirty = true;
        });

        form.addEventListener('submit', () => {
            dirty = false;
        });

        window.addEventListener('beforeunload', (event) => {
            if (!dirty) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    });

    /* ------------------------------------------------------------------ */
    /* Client-side queue status tabs and quick search (reception desk)    */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-queue-filter-root]').forEach((root) => {
        const searchInput = root.querySelector('[data-queue-search]');
        const tabButtons = root.querySelectorAll('[data-queue-tab]');
        let activeStatus = 'all';

        const apply = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();

            tabButtons.forEach((button) => {
                const selected = button.dataset.queueTab === activeStatus;
                button.setAttribute('aria-pressed', String(selected));
                if (button.classList.contains('tab-btn')) {
                    button.classList.toggle('tab-btn-active', selected);
                } else {
                    button.classList.toggle('btn-primary', selected);
                    button.classList.toggle('btn-secondary', !selected);
                }
            });

            const rows = root.querySelectorAll('[data-queue-row]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const status = row.dataset.status || '';
                const haystack = (row.dataset.search || '').toLowerCase();
                const statusMatch =
                    activeStatus === 'all'
                    || (activeStatus === 'waiting' ? status === 'pending' || status === 'confirmed' : status === activeStatus);
                const queryMatch = query === '' || haystack.includes(query);
                const visible = statusMatch && queryMatch;

                row.hidden = !visible;
                if (visible) {
                    visibleCount += 1;
                }
            });

            const emptyBanner = root.querySelector('[data-queue-filter-empty]');
            if (emptyBanner) {
                emptyBanner.classList.toggle('hidden', rows.length === 0 || visibleCount > 0);
            }
        };

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeStatus = button.dataset.queueTab || 'all';
                apply();
            });
        });

        searchInput?.addEventListener('input', apply);
        root.addEventListener('clinicsync:polled', apply);
        apply();
    });

    /* ------------------------------------------------------------------ */
    /* Delegated slide-over row inspector drawers (survives live polling) */
    /* ------------------------------------------------------------------ */
    let activeDrawer = null;
    let drawerOpener = null;

    const closeActiveDrawer = () => {
        if (!activeDrawer) {
            return;
        }

        activeDrawer.classList.add('hidden');
        activeDrawer.classList.remove('flex');
        activeDrawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

        if (drawerOpener instanceof HTMLElement) {
            drawerOpener.focus();
        }

        activeDrawer = null;
        drawerOpener = null;
    };

    const openDrawerById = (drawerId, trigger) => {
        const drawer = document.getElementById(drawerId);
        if (!drawer) {
            return;
        }

        if (activeDrawer && activeDrawer !== drawer) {
            closeActiveDrawer();
        }

        activeDrawer = drawer;
        drawerOpener = trigger || document.activeElement;
        drawer.classList.remove('hidden');
        drawer.classList.add('flex');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        const firstFocusable = drawer.querySelector(FOCUSABLE);
        firstFocusable?.focus();
    };

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-drawer-open]');
        if (opener) {
            const targetId = opener.getAttribute('data-drawer-open');
            if (targetId) {
                openDrawerById(targetId, opener);
            }

            return;
        }

        if (event.target.closest('[data-drawer-close]')) {
            closeActiveDrawer();
        }
    });

    document.addEventListener('drawer:close', closeActiveDrawer, true);

    document.addEventListener('keydown', (event) => {
        if (!activeDrawer || event.key !== 'Tab') {
            return;
        }

        const focusable = Array.from(activeDrawer.querySelectorAll(FOCUSABLE)).filter((el) => el.offsetParent !== null);
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

    /* ------------------------------------------------------------------ */
    /* Client-side tabbed panels (Sticky Physician EMR Reference Rail)    */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('[data-tabs]').forEach((root) => {
        const tabs = Array.from(root.querySelectorAll('[data-tab-trigger]'));
        const panels = Array.from(root.querySelectorAll('[data-tab-panel]'));

        const activate = (targetName) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.tabTrigger === targetName;
                tab.setAttribute('aria-selected', String(active));
                tab.setAttribute('tabindex', active ? '0' : '-1');
                tab.classList.toggle('tab-btn-active', active);
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.tabPanel !== targetName;
            });
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => activate(tab.dataset.tabTrigger));
            tab.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                    return;
                }

                event.preventDefault();
                const delta = event.key === 'ArrowRight' ? 1 : -1;
                const next = tabs[(index + delta + tabs.length) % tabs.length];
                activate(next.dataset.tabTrigger);
                next.focus();
            });
        });
    });

    /* ------------------------------------------------------------------ */
    /* Keyboard "/" shortcut to focus queue or table search inputs        */
    /* ------------------------------------------------------------------ */
    document.addEventListener('keydown', (event) => {
        if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        const target = event.target;
        if (
            target instanceof HTMLElement
            && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable)
        ) {
            return;
        }

        if (document.querySelector('[data-dialog]:not(.hidden), [data-drawer]:not(.hidden)')) {
            return;
        }

        const searchInput = document.querySelector('[data-queue-search], main input[type="search"]');
        if (searchInput instanceof HTMLInputElement && searchInput.offsetParent !== null) {
            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});
