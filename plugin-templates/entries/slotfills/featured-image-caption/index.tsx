/**
 * Adds a "Featured Image Caption" option to the featured image box.
 */
import { addFilter } from '@wordpress/hooks';
import FeaturedImageCaption from './FeaturedImageCaption';

// @ts-ignore
function featuredImageCaption(OriginalComponent) {
  // @ts-ignore
  return function (props) { /* eslint-disable-line func-names */
    return (
      <>
        <OriginalComponent {...props} />
        <FeaturedImageCaption {...props} />
      </>
    );
  };
}

addFilter(
  'editor.PostFeaturedImage',
  'create-wordpress-plugin/featured-image-caption',
  featuredImageCaption,
);
