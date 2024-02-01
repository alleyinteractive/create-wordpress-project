import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { usePostById } from '@alleyinteractive/block-editor-tools';
import { WP_REST_API_Post } from 'wp-types'; // eslint-disable-line camelcase
import { ToggleControl, PanelRow, PanelBody } from '@wordpress/components';

type EditProps = {
  attributes: {
    isLink: boolean;
    taxonomy: string;
  };
  context: {
    postId?: number;
  };
  setAttributes: (newAttributes: Partial<EditProps['attributes']>) => void;
};

type PostWithPrimaryTerm = WP_REST_API_Post & { // eslint-disable-line camelcase
  create_wordpress_plugin_primary_term: {
    [taxonomy: string]: {
      term_id: number;
      term_name: string;
      term_link: string;
    };
  };
};

/**
 * The create-wordpress-plugin/primary-term block edit function.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({
  attributes: {
    isLink = true,
    taxonomy = 'category',
  },
  context: {
    postId,
  },
  setAttributes,
}: EditProps) {
  const record = usePostById(postId);

  let primaryTerm = null;
  if (record) {
    const primaryTermField = (record as PostWithPrimaryTerm).create_wordpress_plugin_primary_term;
    primaryTerm = primaryTermField[taxonomy];
  } else {
    primaryTerm = {
      term_name: __('Primary Term', 'wp-newsletter-builder'),
      term_id: null,
      term_link: '',
    };
  }

  return (
    <>
      <div {...useBlockProps()}>
        {isLink ? (
          <a href="#"> {/* eslint-disable-line */}
            {primaryTerm.term_name}
          </a>
        ) : primaryTerm.term_name}
      </div>
      <InspectorControls>
        <PanelBody title={__('Primary Term Settings', 'create-wordpress-plugin')}>
          <PanelRow>
            <ToggleControl
              label={__('Link to term archive', 'create-wordpress-plugin')}
              checked={isLink}
              onChange={(next) => setAttributes({ isLink: next })}
            />
          </PanelRow>
        </PanelBody>
      </InspectorControls>
    </>
  );
}
