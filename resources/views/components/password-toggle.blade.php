@props([
    /** CSS selector pointing at the password input this button toggles. */
    'for',
])

{{-- Reveal/hide a password field. Pair with right padding on the input
     (e.g. `pr-16`) so the typed value never runs underneath the button. --}}
<button type="button" data-password-toggle="{{ $for }}" aria-pressed="false"
        class="password-toggle absolute inset-y-0 right-0 flex items-center">
    Show
</button>
