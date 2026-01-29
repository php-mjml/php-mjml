import { onMessage, sendMessageFor } from 'https://cdn.jsdelivr.net/npm/php-cgi-wasm@alpha/msg-bus.mjs';

// Detect base path from current location
const BASE_PATH = new URL('.', import.meta.url).pathname.replace(/\/$/, '');
const WORKER_URL = './cgi-worker.mjs';
const PHP_ENDPOINT = `${BASE_PATH}/cgi-bin/index.php`;
const ZIP_URL = './backups/php-mjml-demo.zip';

const INIT_SCRIPT = `<?php
$docroot = '/persist/www';
if (!file_exists($docroot)) {
    mkdir($docroot, 0777, true);
}
$zip = new ZipArchive();
if ($zip->open('/persist/restore.zip', ZipArchive::RDONLY) === true) {
    $zip->extractTo($docroot);
    $zip->close();
    unlink('/persist/restore.zip');
    echo 'Installation complete!';
} else {
    echo 'Failed to open ZIP';
}
`;

const DEFAULT_MJML = `<mjml>
  <mj-head>
    <mj-title>PHP-MJML Demo</mj-title>
    <mj-attributes>
      <mj-all font-family="Helvetica, Arial, sans-serif" />
      <mj-text font-size="16px" color="#555555" line-height="1.5" />
    </mj-attributes>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <mj-section background-color="#ffffff" padding="40px 20px">
      <mj-column>
        <mj-text font-size="28px" color="#333333" font-weight="bold" align="center">
          Hello from PHP-MJML!
        </mj-text>
        <mj-divider border-color="#1a73e8" border-width="2px" padding="20px 100px" />
        <mj-text>
          This demo runs entirely in your browser using PHP-WASM.
          Edit the MJML source on the left and click "Render" to see the output.
        </mj-text>
        <mj-button background-color="#1a73e8" href="https://github.com/php-mjml/php-mjml">
          View on GitHub
        </mj-button>
      </mj-column>
    </mj-section>
    <mj-section padding="20px">
      <mj-column>
        <mj-text font-size="12px" color="#888888" align="center">
          PHP-MJML - Native PHP port of the MJML email framework
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>`;

let sendMessage;
let lastRenderedHtml = '';

const statusDiv = document.getElementById('status');
const statusText = document.getElementById('status-text');
const editorContainer = document.getElementById('editor-container');
const mjmlInput = document.getElementById('mjml-input');
const renderBtn = document.getElementById('render-btn');
const viewSourceBtn = document.getElementById('view-source-btn');
const preview = document.getElementById('preview');
const sourceModal = document.getElementById('source-modal');
const sourceOutput = document.getElementById('source-output');
const closeModalBtn = document.getElementById('close-modal-btn');

function setStatus(message) {
    statusText.textContent = message;
}

function showReady() {
    statusDiv.classList.add('hidden');
    editorContainer.classList.add('ready');
    renderBtn.disabled = false;
    viewSourceBtn.disabled = false;
}

async function init() {
    try {
        console.log('[init] Starting, BASE_PATH:', BASE_PATH);

        // Check for service worker support
        if (!('serviceWorker' in navigator)) {
            throw new Error('Service Workers are not supported in this browser');
        }

        // Register service worker
        setStatus('Registering service worker...');
        console.log('[init] Registering service worker:', WORKER_URL);
        const registration = await navigator.serviceWorker.register(WORKER_URL, {
            type: 'module',
            scope: './'
        });
        console.log('[init] Service worker registered:', registration);

        // Wait for service worker to be ready
        await navigator.serviceWorker.ready;
        console.log('[init] Service worker ready');

        // Set up message handling
        navigator.serviceWorker.addEventListener('message', onMessage);
        sendMessage = sendMessageFor(WORKER_URL);

        // Wait a moment for the worker to initialize
        console.log('[init] Waiting for worker to initialize...');
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Check if already installed (also check vendor/autoload.php to ensure complete installation)
        setStatus('Checking installation...');
        let isInstalled = false;

        // Force reinstall with ?reinstall in URL
        const forceReinstall = new URLSearchParams(window.location.search).has('reinstall');
        if (forceReinstall) {
            console.log('[init] Force reinstall requested via URL parameter');
        }

        try {
            console.log('[init] Checking if /persist/www/vendor/autoload.php exists...');
            const check = await sendMessage('analyzePath', ['/persist/www/vendor/autoload.php']);
            console.log('[init] analyzePath result:', check);
            isInstalled = !forceReinstall && check && check.exists;
        } catch (e) {
            console.log('[init] analyzePath error (expected on first run):', e);
            // Path doesn't exist, need to install
            isInstalled = false;
        }
        console.log('[init] isInstalled:', isInstalled);

        if (!isInstalled) {
            // Download ZIP
            setStatus('Downloading PHP-MJML...');
            console.log('[init] Downloading ZIP from:', ZIP_URL);
            const zipResponse = await fetch(ZIP_URL);
            if (!zipResponse.ok) {
                throw new Error(`Failed to download ZIP: ${zipResponse.status}`);
            }
            const zipData = new Uint8Array(await zipResponse.arrayBuffer());
            console.log('[init] ZIP downloaded, size:', zipData.length);

            // Write ZIP to virtual filesystem
            setStatus('Preparing installation...');
            console.log('[init] Writing ZIP to /persist/restore.zip');
            await sendMessage('writeFile', ['/persist/restore.zip', zipData]);
            console.log('[init] ZIP written');

            // Create docroot and write init script there
            console.log('[init] Creating /persist/www directory');
            try {
                await sendMessage('mkdir', ['/persist/www']);
            } catch (e) {
                console.log('[init] mkdir error (may already exist):', e);
            }

            console.log('[init] Writing init script to docroot');
            await sendMessage('writeFile', ['/persist/www/init.php', new TextEncoder().encode(INIT_SCRIPT)]);
            console.log('[init] Init script written');

            // Run extraction via HTTP request
            setStatus('Extracting PHP-MJML...');
            console.log('[init] Calling init script via HTTP...');
            const initUrl = `${BASE_PATH}/cgi-bin/init.php`;
            console.log('[init] Init URL:', initUrl);
            const initResponse = await fetch(initUrl);
            const initResult = await initResponse.text();
            console.log('[init] Extraction result:', initResult);

            // Clean up init script
            try {
                await sendMessage('unlink', ['/persist/www/init.php']);
            } catch {
                // Ignore cleanup errors
            }
        }

        // Ready!
        setStatus('Ready!');
        mjmlInput.value = DEFAULT_MJML;
        showReady();

        // Initial render
        await renderMjml();

    } catch (error) {
        console.error('Initialization error:', error);
        setStatus(`Error: ${error.message}`);
    }
}

async function renderMjml() {
    const mjml = mjmlInput.value;
    if (!mjml.trim()) {
        return;
    }

    renderBtn.disabled = true;
    renderBtn.textContent = 'Rendering...';

    try {
        const response = await fetch(PHP_ENDPOINT, {
            method: 'POST',
            body: mjml,
            headers: {
                'Content-Type': 'text/plain'
            }
        });

        lastRenderedHtml = await response.text();
        preview.srcdoc = lastRenderedHtml;
    } catch (error) {
        console.error('Render error:', error);
        preview.srcdoc = `<pre style="color: red; padding: 1rem;">Error: ${error.message}</pre>`;
        lastRenderedHtml = '';
    } finally {
        renderBtn.disabled = false;
        renderBtn.textContent = 'Render';
    }
}

function showSource() {
    if (lastRenderedHtml) {
        sourceOutput.textContent = lastRenderedHtml;
        sourceModal.classList.add('open');
    }
}

function closeModal() {
    sourceModal.classList.remove('open');
}

// Event listeners
renderBtn.addEventListener('click', renderMjml);
viewSourceBtn.addEventListener('click', showSource);
closeModalBtn.addEventListener('click', closeModal);

sourceModal.addEventListener('click', (event) => {
    if (event.target === sourceModal) {
        closeModal();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeModal();
    }
    // Ctrl/Cmd + Enter to render
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault();
        renderMjml();
    }
});

// Start initialization
init();
