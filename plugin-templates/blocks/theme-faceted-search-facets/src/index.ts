import domReady from '@wordpress/dom-ready';
import autosubmit from './autosubmit';

/**
 * Initialize faceted search functions.
 */
function init() {
  autosubmit();
}

domReady(() => init());
