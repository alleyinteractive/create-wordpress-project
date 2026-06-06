// WordPress dependencies.
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

domReady(() => {
  document.addEventListener('click', async (event) => {
    const element = event.target as HTMLButtonElement | null;

    if (!element || !element.classList.contains('create-wordpress-project-copy-object-id')) {
      return;
    }

    const id = element.dataset.objectId;

    if (!id) {
      return;
    }

    event.preventDefault();

    try {
      await navigator.clipboard.writeText(id);
    } catch (error) {
      return;
    }

    const target = element;
    const originText = target.textContent;

    // Change the target text to indicate success
    target.textContent = __('Copied!', 'create-wordpress-project');
    target.disabled = true;

    setTimeout(() => {
      target.textContent = originText;
      target.disabled = false;
    }, 2000);
  });
});
