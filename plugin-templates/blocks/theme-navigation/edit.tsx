import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Placeholder, SelectControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { menu } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * A global type should be declared for this package. This is a temporary workaround until we
 * decide on where global types should be declared.
 */
// @ts-ignore
import ServerSideRender from '@wordpress/server-side-render';

import './index.scss';

interface MenuLocation {
  name: string;
  description: string;
}

export interface SelectOptions {
  label: string;
  value: string;
}

/**
 * Given an array of menu locations, return an array of objects that can be used for
 * SelectControl components.
 *
 * @param menuLocations Registered menu locations.
 */
function getMenuLocationsSelectOptions(menuLocations: MenuLocation[]): SelectOptions[] {
  const menuLocationsList = [];

  menuLocationsList.push({ label: __('Select a menu location', 'create-wordpress-plugin'), value: '' });

  menuLocations
    .forEach((location) => {
      const { description, name } = location;
      menuLocationsList.push({ label: description, value: name });
    });

  return menuLocationsList;
}

interface Attributes {
  menuLocation: string;
}

interface EditProps {
  attributes: Attributes;
  setAttributes: (next: Partial<Attributes>) => void;
}

export default function Edit({
  attributes: {
    menuLocation,
  },
  setAttributes,
}: EditProps) {
  const blockProps = useBlockProps();

  // @ts-ignore - useSelect doesn't export proper types.
  const menuLocations = useSelect((select) => select('core').getMenuLocations('root', 'menu'), []);

  // Loading menu locations.
  if (!Array.isArray(menuLocations)) {
    return (
      <div {...blockProps}>
        <Spinner />
      </div>
    );
  }

  // No menu locations have been registered.
  if (menuLocations.length === 0) {
    return (
      <div {...blockProps}>
        <Placeholder
          icon={ menu }
          label={ __('Theme Navigation', 'create-wordpress-plugin') }
          instructions={ __('Please register menu locations.', 'create-wordpress-plugin') }
        />
      </div>
    );
  }

  return (
    <div {...blockProps}>
      {
        menuLocation ? (
          <ServerSideRender block="create-wordpress-plugin/theme-navigation" attributes={{ menuLocation }} />
        ) : (
          <Placeholder
            icon={ menu }
            label={ __('Theme Navigation', 'create-wordpress-plugin') }
            instructions={ __('Please select a menu location.', 'create-wordpress-plugin') }
          />
        )
      }

      <InspectorControls>
        <PanelBody
          title={__('Select Menu Location', 'create-wordpress-plugin')}
          initialOpen
        >
          <SelectControl
            onChange={(next: string) => setAttributes({ menuLocation: next })}
            value={menuLocation || ''}
            options={getMenuLocationsSelectOptions(menuLocations)}
          />
        </PanelBody>
      </InspectorControls>
    </div>
  );
}
