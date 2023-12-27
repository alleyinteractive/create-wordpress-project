import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { Editor } from '@tinymce/tinymce-react';
import { usePostMetaValue } from '@alleyinteractive/block-editor-tools';

const { tinyMCEPreInit } = window as any;

function Subheadline() {
  const [subheadline, setSubheadline] = usePostMetaValue('subheadline');
  return (
    <PluginDocumentSettingPanel
      // @ts-ignore
      name="subheadline"
      title={__('Subheadline', 'create-wordpress-project')}
    >
      <Editor
        value={subheadline}
        tinymceScriptSrc={`${tinyMCEPreInit.baseURL}/tinymce.min.js`}
        init={{
          height: 200,
          menubar: false,
          plugins: [
            'lists link image fullscreen media paste',
          ],
          toolbar: 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link fullscreen',
          content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        }}
        onEditorChange={setSubheadline}
      />
    </PluginDocumentSettingPanel>
  );
}

export default Subheadline;
