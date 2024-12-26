// Comment on this line if not using the ZF framework.
import Foundation from './3rd/_zf';

import { nanoid } from 'nanoid';
import Cookies from 'js-cookie';

Object.assign( window, { Cookies } );

import BackToTop from './components/back-to-top';
import lazyLoader from './components/lazy-loader';
import { initializeSocialShare } from './components/social-share';

const customOptions = {
    displays: [
        'facebook',
        'ex',
        'whatsapp',
        'messenger',
        'telegram',
        'linkedin',
        'send-email',
        'copy-link',
        'web-share',
    ]
};

// Custom

// Initialize
function init() {
    new BackToTop();
    lazyLoader( 4000, "script[data-type='lazy']" );
    initializeSocialShare( 'social-share', customOptions );
}

// Document ready
document.addEventListener( 'DOMContentLoaded', function () {
    // Initialize
    init();
} );
