// Import jQuery and assign to window
Object.assign( window, { $: jQuery, jQuery } );

// Import Foundation core and utilities
import { Foundation } from 'foundation-sites/js/foundation.core';
import * as CoreUtils from 'foundation-sites/js/foundation.core.utils';

// Foundation Utilities
import { Keyboard } from 'foundation-sites/js/foundation.util.keyboard';
import { Box } from 'foundation-sites/js/foundation.util.box';
import { Nest } from 'foundation-sites/js/foundation.util.nest';
import { MediaQuery } from 'foundation-sites/js/foundation.util.mediaQuery';
import { Touch } from 'foundation-sites/js/foundation.util.touch';
import { Triggers } from 'foundation-sites/js/foundation.util.triggers';
import { Move, Motion } from 'foundation-sites/js/foundation.util.motion';
import { onImagesLoaded } from 'foundation-sites/js/foundation.util.imageLoader';
import { Timer } from 'foundation-sites/js/foundation.util.timer';

// Assign Foundation utilities
Object.assign( Foundation, {
    rtl: CoreUtils.rtl,
    GetYoDigits: CoreUtils.GetYoDigits,
    RegExpEscape: CoreUtils.RegExpEscape,
    transitionend: CoreUtils.transitionend,
    onLoad: CoreUtils.onLoad,
    ignoreMousedisappear: CoreUtils.ignoreMousedisappear,
    Keyboard,
    Box,
    Nest,
    onImagesLoaded,
    MediaQuery,
    Motion,
    Move,
    Touch,
    Triggers,
    Timer,
} );

// Initialize utilities
Touch.init( $ );
Triggers.init( $, Foundation );
MediaQuery._init();

// Import and initialize Foundation plugins
import { Dropdown } from 'foundation-sites/js/foundation.dropdown';
import { DropdownMenu } from 'foundation-sites/js/foundation.dropdownMenu';
import { Accordion } from 'foundation-sites/js/foundation.accordion';
import { AccordionMenu } from 'foundation-sites/js/foundation.accordionMenu';
import { ResponsiveMenu } from 'foundation-sites/js/foundation.responsiveMenu';
import { ResponsiveToggle } from 'foundation-sites/js/foundation.responsiveToggle';
import { OffCanvas } from 'foundation-sites/js/foundation.offcanvas';
import { Reveal } from 'foundation-sites/js/foundation.reveal';
import { Tooltip } from 'foundation-sites/js/foundation.tooltip';
import { SmoothScroll } from 'foundation-sites/js/foundation.smoothScroll';
import { Magellan } from 'foundation-sites/js/foundation.magellan';
import { Sticky } from 'foundation-sites/js/foundation.sticky';
import { Toggler } from 'foundation-sites/js/foundation.toggler';
import { Equalizer } from 'foundation-sites/js/foundation.equalizer';
import { Interchange } from 'foundation-sites/js/foundation.interchange';
import { Abide } from 'foundation-sites/js/foundation.abide';

const plugins = [
    { plugin: Dropdown, name: 'Dropdown' },
    { plugin: DropdownMenu, name: 'DropdownMenu' },
    { plugin: Accordion, name: 'Accordion' },
    { plugin: AccordionMenu, name: 'AccordionMenu' },
    { plugin: ResponsiveMenu, name: 'ResponsiveMenu' },
    { plugin: ResponsiveToggle, name: 'ResponsiveToggle' },
    { plugin: OffCanvas, name: 'OffCanvas' },
    { plugin: Reveal, name: 'Reveal' },
    { plugin: Tooltip, name: 'Tooltip' },
    { plugin: SmoothScroll, name: 'SmoothScroll' },
    { plugin: Magellan, name: 'Magellan' },
    { plugin: Sticky, name: 'Sticky' },
    { plugin: Toggler, name: 'Toggler' },
    { plugin: Equalizer, name: 'Equalizer' },
    { plugin: Interchange, name: 'Interchange' },
    { plugin: Abide, name: 'Abide' },
];

plugins.forEach( ( { plugin, name } ) => {
    Foundation.plugin( plugin, name );
} );

Foundation.addToJquery( $ );

//
// pattern, validator
//

/**
 * @param $el
 * @param required
 * @param parent
 * @returns {boolean}
 */
function notEqualToValidator( $el, required, parent ) {
    if ( !required ) return true;

    let input1Value = $( '#' + $el.attr( 'data-notEqualTo' ) ).val(),
        input2Value = $el.val();

    // Return true if they are different, false if they are the same
    return input1Value !== input2Value;
}

Foundation.Abide.defaults.validators['notEqualTo'] = notEqualToValidator;

$( () => $( document ).foundation() );

export default Foundation;
