import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './index.scss';

type EditProps = {
  context: {
    postId: number;
  };
};

/**
 * The create-wordpress-plugin/theme-post-subheadline block edit function.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({
  context: { postId },
}: EditProps): JSX.Element {
  return (
    <p {...useBlockProps()}>
      { __('Subheading') }
      { postId }
    </p>
  );
}
