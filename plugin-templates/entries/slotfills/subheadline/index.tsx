import { registerPlugin } from '@wordpress/plugins';

import Subheadline from './Subheadline';

registerPlugin(
  'create-wordpress-plugin-subheadline',
  {
    // @ts-ignore
    render: Subheadline,
  },
);
