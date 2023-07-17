declare module '*.svg' {
  import * as React from '@wordpress/element';

  export const ReactComponent: React.FC<React.SVGProps<SVGSVGElement>>;
  const src: string;
  export default src;
}
