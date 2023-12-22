import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';

function Subheadline() {
  return (
    <PluginDocumentSettingPanel
      // @ts-ignore
      name="subheadline"
      title={__('Subheadline', 'create-wordpress-project')}
    >
      {/* @ts-ignore */}
      subheadline richtext
    </PluginDocumentSettingPanel>
  );
}

export default Subheadline;
