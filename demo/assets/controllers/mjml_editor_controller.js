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

            this.renderedHtml = await response.text();
            this.previewTarget.srcdoc = this.renderedHtml;

            this.dispatch('rendered', { detail: { html: this.renderedHtml } });
        } catch (error) {
            console.error('Error rendering MJML:', error);
            this.previewTarget.srcdoc = '<html><body><p style="color: red;">Error rendering MJML</p></body></html>';
            this.renderedHtml = '';
        } finally {
            this.renderButtonTarget.disabled = false;
            this.renderButtonTarget.textContent = 'Render';
        }
    }

    getRenderedHtml() {
        return this.renderedHtml;
    }
}
