import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'preview', 'renderButton'];
    static values = {
        renderUrl: String,
    };

    renderedHtml = '';

    connect() {
        this.render();
    }

    async render() {
        this.renderButtonTarget.disabled = true;
        this.renderButtonTarget.textContent = 'Rendering...';

        try {
            const formData = new FormData();
            formData.append('mjml', this.inputTarget.value);

            const response = await fetch(this.renderUrlValue, {
                method: 'POST',
                body: formData,
            });

            const body = await response.text();

            if (!response.ok) {
                this.showError(body || 'Error rendering MJML');
                return;
            }

            this.renderedHtml = body;
            this.previewTarget.srcdoc = this.renderedHtml;

            this.dispatch('rendered', { detail: { html: this.renderedHtml } });
        } catch (error) {
            console.error('Error rendering MJML:', error);
            this.showError('Error rendering MJML');
        } finally {
            this.renderButtonTarget.disabled = false;
            this.renderButtonTarget.textContent = 'Render';
        }
    }

    getRenderedHtml() {
        return this.renderedHtml;
    }

    showError(message) {
        // Built with textContent: error messages can echo parts of the user's MJML
        const paragraph = document.createElement('p');
        paragraph.style.color = 'red';
        paragraph.textContent = message;

        this.previewTarget.srcdoc = `<html><body>${paragraph.outerHTML}</body></html>`;
        this.renderedHtml = '';
    }
}
