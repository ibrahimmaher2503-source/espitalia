<script>
/**
 * Sanitize a string for safe DOM insertion via template literals.
 * Prevents XSS when using innerHTML with server-returned JSON values.
 */
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}
</script>
