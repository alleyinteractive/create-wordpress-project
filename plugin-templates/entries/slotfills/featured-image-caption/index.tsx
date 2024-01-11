/**
 * Adds a "Featured Image Caption" option to the featured image box.
 */
import { addFilter } from '@wordpress/hooks';
import JSXElement from 'react';
import FeaturedImageCaption from './FeaturedImageCaption';

/* @ts-ignore - JSXElement not recognized */
function featuredImageCaption(OriginalComponent: JSXElement) {
  return function (props: object) { /* eslint-disable-line func-names */
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
