import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { usePostMetaValue } from '@alleyinteractive/block-editor-tools';

function Subheadline() {
  const [subheadline, setSubheadline] = usePostMetaValue('create_wordpress_plugin_subheadline');
  return (
    <PluginDocumentSettingPanel
      name="subheadline"
      title={__('Subheadline', 'create-wordpress-plugin')}
    >
      <TextControl
        label={__('Subheadline', 'create-wordpress-plugin')}
        hideLabelFromVision
        value={subheadline ?? ''}
        onChange={setSubheadline}
        __nextHasNoMarginBottom
        __next40pxDefaultSize
      />
    </PluginDocumentSettingPanel>
  );
}

export default Subheadline;
