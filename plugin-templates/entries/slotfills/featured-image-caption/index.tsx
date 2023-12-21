/**
 * Adds a "Featured Image Caption" option to the featured image box.
 */
import { addFilter } from '@wordpress/hooks';
import JSXElement from 'react';
import FeaturedImageCaption from './FeaturedImageCaption';

/* @ts-ignore - can't find the correct type for OriginalComponent */
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
