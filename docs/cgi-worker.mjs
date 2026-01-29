// TODO: Waiting for php-wasm to release PHP 8.4 support.
// The php-mjml library requires PHP >= 8.4, but php-wasm@alpha currently only ships PHP 8.3.11.
// Once PHP 8.4 is available in php-wasm, update the `version` config below.
// Track: https://github.com/seanmorris/php-wasm
import { PhpCgiWorker } from 'https://cdn.jsdelivr.net/npm/php-cgi-wasm@alpha/PhpCgiWorker.mjs';

// Import PHP extensions as ESM modules (alpha versions for PHP 8.4 support)
import * as libxml from 'https://cdn.jsdelivr.net/npm/php-wasm-libxml@alpha/index.mjs';
import * as dom from 'https://cdn.jsdelivr.net/npm/php-wasm-dom@alpha/index.mjs';
import * as zlib from 'https://cdn.jsdelivr.net/npm/php-wasm-zlib@alpha/index.mjs';
import * as libzip from 'https://cdn.jsdelivr.net/npm/php-wasm-libzip@alpha/index.mjs';

// Detect base path from service worker location
const BASE_PATH = new URL('.', self.location.href).pathname.replace(/\/$/, '');

console.log('[sw] Initializing PhpCgiWorker with BASE_PATH:', BASE_PATH);
console.log('[sw] libxml:', libxml);
console.log('[sw] dom:', dom);
console.log('[sw] zlib:', zlib);
console.log('[sw] libzip:', libzip);

const config = {
    version: '8.4',
    prefix: `${BASE_PATH}/cgi-bin/`,
    docroot: '/persist/www',
    types: {
        css: 'text/css',
        js: 'application/javascript',
        mjs: 'application/javascript',
        json: 'application/json',
        html: 'text/html',
        php: 'text/html',
    },
    sharedLibs: [
        libxml,
        dom,
        zlib,
        libzip,
    ],
};
console.log('[sw] PhpCgiWorker config:', config);

const php = new PhpCgiWorker(config);
console.log('[sw] PhpCgiWorker created:', php);

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(php.handleInstallEvent(event));
});

self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
    php.handleActivateEvent(event);
});

self.addEventListener('fetch', event => {
    console.log('[sw] Fetch:', event.request.method, event.request.url);
    php.handleFetchEvent(event);
});

self.addEventListener('message', event => php.handleMessageEvent(event));
