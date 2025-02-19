export default function autosubmit() {
  const facetContainer = document.querySelector('.wp-block-create-wordpress-plugin-theme-faceted-search-facets');
  if (!facetContainer) {
    return;
  }

  const form = facetContainer.closest('form');
  if (!form) {
    return;
  }

  const checkboxes = facetContainer.querySelectorAll('input[type="checkbox"]');
  for (const checkbox of checkboxes) {
    checkbox.addEventListener('change', (event) => {
      if (event.target && event.target instanceof HTMLInputElement) {
        form.submit();
      }
    });
  }
}
