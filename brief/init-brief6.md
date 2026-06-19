The compiler is getting incredibly powerful! We need to add two more directives for raw HTML injection: `@append` and `@prepend`. 

Currently, `@html` acts like `innerHTML` (it destroys and replaces the node's contents). We need the equivalent of JavaScript's `insertAdjacentHTML('beforeend')` and `insertAdjacentHTML('afterbegin')` so the Backend team can inject emergency `<script>` or `<style>` tags without destroying the existing layout.

### TASK:
Inside the `$applyRules` closure, specifically inside the nested logic directives block (`elseif (is_array($value))`), add the logic for `@append` and `@prepend`.

1. Logic for `@append`:
If `isset($value['@append'])`:
- Loop through `$targetNodes`.
- Generate a `$marker` and assign it to `$replacements[$marker] = "<?= {$value['@append']} ?? '' ?>";` (raw output, bypass XSS, exactly like `@html`).
- Append the marker using: `$node->appendChild($dom->createTextNode($marker));`
- `unset($value['@append']);`

2. Logic for `@prepend`:
If `isset($value['@prepend'])`:
- Loop through `$targetNodes`.
- Generate a `$marker` and assign it to `$replacements[$marker] = "<?= {$value['@prepend']} ?? '' ?>";` (raw output, bypass XSS).
- Prepend the marker using: `$node->insertBefore($dom->createTextNode($marker), $node->firstChild);` (Note: if firstChild is null, insertBefore automatically acts like appendChild, which is perfect).
- `unset($value['@prepend']);`

Please integrate these two new directives safely next to the existing `@html` logic and return the updated `compiler.php` code.