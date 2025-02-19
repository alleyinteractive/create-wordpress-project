import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './index.scss';

/**
 * The create-wordpress-plugin/theme-search-meta block edit function.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit() {
  return (
    <div {...useBlockProps()}>
      <span className="wp-block-create-wordpress-plugin-theme-search-meta__results-count">
        { __("7 results for 'search term'") }
      </span>
    </div>
  );
}
