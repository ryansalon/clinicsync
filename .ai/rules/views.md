---
paths:
  - 'resources/views/**/*.blade.php'
---

# Views

## Avoid inline @php(...) when the file also uses block @endphp
Laravel's storePhpBlocks regex (@php(.*?)@endphp) treats an inline @php($x = 1) as a block start and swallows all content up to a later @endphp as raw, uncompiled text - producing literal @if/@php in output and stray-endif parse errors. If one file contains an inline @php(...), never add a block @php...@endphp after it; use the inline @php($var = expr) form instead. (Found via physician/encounters/show.blade.php.)
