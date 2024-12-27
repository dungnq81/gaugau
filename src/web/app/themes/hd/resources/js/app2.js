// Comment on this line if not using the ZF framework.
import Foundation from './3rd/_zf';

import { nanoid } from 'nanoid';
import Cookies from 'js-cookie';

Object.assign(window, { Cookies });

import BackToTop from './components/back-to-top';
import lazyLoader from './components/lazy-loader';
import { initializeSocialShare } from './components/social-share';

const customOptions = {
	displays: ['facebook', 'ex', 'whatsapp', 'messenger', 'telegram', 'linkedin', 'send-email', 'copy-link', 'web-share'],
};

const ajaxUrl = typeof hd !== 'undefined' && typeof hd.ajaxUrl !== 'undefined' ? hd.ajaxUrl : '/wp/wp-admin/admin-ajax.php';
const baseUrl = typeof hd !== 'undefined' && typeof hd.baseUrl !== 'undefined' ? hd.baseUrl : 'http://localhost:8080/';
const themeUrl = typeof hd !== 'undefined' && typeof hd.themeUrl !== 'undefined' ? hd.themeUrl : 'http://localhost:8080/app/themes/hd/';

'serviceWorker' in navigator &&
	window.addEventListener('load', function () {
		navigator.serviceWorker.register(themeUrl + 'assets/js/workbox.js').then(
			function (e) {
				console.log('ServiceWorker registration successful with scope: ', e.scope);
			},
			function (e) {
				console.log('ServiceWorker registration failed: ', e);
			}
		);
	});

// Initialize
function init() {
	new BackToTop();
	lazyLoader(4000, "script[data-type='lazy']");
	initializeSocialShare('social-share', customOptions);
}

// Document ready
document.addEventListener('DOMContentLoaded', function () {
	init();
});
