import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import './index.scss';

/**
 * The create-wordpress-plugin/theme-faceted-search block edit function.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit() {
  const blockProps = useBlockProps();
  const SEARCH_RESULTS_TEMPLATE = [
    ['core/heading', { level: 1, textAlign: 'center', content: __('Search', 'create-wordpress-plugin') }],
    ['core/search', {
      label: __('Search', 'create-wordpress-plugin'),
      showLabel: false,
      placeholder: __('Search', 'create-wordpress-plugin'),
      buttonText: __('Search', 'create-wordpress-plugin'),
    }],
    ['core/separator', { align: 'wide' }],
    ['core/columns', { align: 'wide' }, [
      ['core/column', { width: '33.33%' }, [
        ['create-wordpress-plugin/theme-faceted-search-facets', {}],
      ]],
      ['core/column', { width: '66.66%' }, [
        ['create-wordpress-plugin/theme-search-meta', {}],
        ['core/query', {
          query: {
            pages: 0,
            offset: 0,
            postType: 'post',
            order: 'desc',
            orderBy: 'date',
            inherit: true,
          },
        }, [
          ['core/post-template', {}, [
            ['core/columns', {}, [
              ['core/column', { width: '' }, [
                ['core/post-title', { isLink: true }],
                ['core/post-date'],
                ['core/post-excerpt'],
              ]],
              ['core/column', { width: '150px' }, [
                ['core/post-featured-image', { isLink: true, width: '150px' }],
              ]],
            ]],
          ]],
          ['core/query-pagination', { paginationArrow: 'chevron', showLabel: false }, [
            ['core/query-pagination-previous', {}],
            ['core/query-pagination-numbers', {}],
            ['core/query-pagination-next', {}],
          ]],
        ]],
      ]],
    ]],
  ];

  return (
    <div {...blockProps}>
      {/* @ts-ignore */ }
      <InnerBlocks template={SEARCH_RESULTS_TEMPLATE} />
    </div>
  );
}
