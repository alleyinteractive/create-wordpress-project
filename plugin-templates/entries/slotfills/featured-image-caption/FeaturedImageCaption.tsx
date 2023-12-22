import { TextareaControl } from '@wordpress/components';
import { usePostMetaValue } from '@alleyinteractive/block-editor-tools';
import { __ } from '@wordpress/i18n';

function FeaturedImageCaption() {
  const [featuredImageCaption, setFeaturedImageCaption] = usePostMetaValue('create_wordpress_plugin_featured_image_caption');

  return (
    <>
      <hr />
      <TextareaControl
        name="create_wordpress_plugin_featured_image_caption"
        value={featuredImageCaption}
        label={__('Featured Image Caption', 'create-wordpress-plugin')}
        onChange={(value) => { setFeaturedImageCaption(value); }}
      />
    </>
  );
}

export default FeaturedImageCaption;
