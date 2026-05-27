/**
 * Expose jQuery on window before legacy IIFE modules evaluate (imports run top-down).
 */
import $ from 'jquery';

declare global {
  interface Window {
    jQuery: typeof $;
    $: typeof $;
  }
}

window.jQuery = $;
window.$ = $;

export default $;
