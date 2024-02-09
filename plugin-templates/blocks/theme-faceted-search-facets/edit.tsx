import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled } from '@wordpress/components';

import './index.scss';

/**
* The create-wordpress-plugin/theme-faceted-search-facets block edit function.
*
* @return {WPElement} Element to render.
*/
export default function Edit() {
  return (
    <div {...useBlockProps()}>
      <h2 className="wp-block-create-wordpress-plugin-theme-faceted-search-facets__heading">
        {__('Filter By', 'create-wordpress-plugin')}
      </h2>
      <Disabled>
        <fieldset className="elasticsearch-extensions__checkbox-group">
          <legend>{__('Facet Title', 'create-wordpress-plugin')}</legend>
          <label htmlFor="facet-placeholder-1">
            <input id="facet-placeholder-1" name="facet-placeholder-1" type="checkbox" value="post" />
            {__('Facet Option', 'create-wordpress-plugin')}
          </label>
          <label htmlFor="facet-placeholder-2">
            <input id="facet-placeholder-2" name="facet-placeholder-2" type="checkbox" value="page" />
            {__('Facet Option', 'create-wordpress-plugin')}
          </label>
        </fieldset>
        <a className="wp-block-create-wordpress-plugin-theme-faceted-search-facets__reset" href="/?s=">
          {__('Reset', 'create-wordpress-plugin')}
        </a>
      </Disabled>
    </div>
  );
}
