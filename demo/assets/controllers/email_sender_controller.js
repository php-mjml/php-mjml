import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['emailInput', 'verifiedList', 'statusMessage', 'panel', 'toggleButton', 'sendButton', 'verifyButton'];
    static values = {
        statusUrl: String,
        verifyUrl: String,
        sendUrl: String,
    };

    verifiedEmails = [];

    connect() {
        this.loadStatus();
        this.emailInputTarget.addEventListener('input', () => this.updateSendButtonState());
    }

    async loadStatus() {
        try {
            const response = await fetch(this.statusUrlValue);
            const data = await response.json();
            this.verifiedEmails = data.verified_emails || [];
            this.updateVerifiedList();
            this.updateSendButtonState();
        } catch (error) {
            console.error('Error loading email status:', error);
        }
    }

    updateVerifiedList() {
        if (!this.hasVerifiedListTarget) return;

        this.verifiedListTarget.innerHTML = '';

        if (this.verifiedEmails.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No verified emails yet';
            this.verifiedListTarget.appendChild(option);
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a verified email...';
        this.verifiedListTarget.appendChild(placeholder);

        this.verifiedEmails.forEach(email => {
            const option = document.createElement('option');
            option.value = email;
            option.textContent = email;
            this.verifiedListTarget.appendChild(option);
        });
    }

    updateSendButtonState() {
        if (!this.hasSendButtonTarget) return;

        const email = this.emailInputTarget.value.trim();
        const isVerified = this.verifiedEmails.includes(email);
        this.sendButtonTarget.disabled = !isVerified;
    }

    toggle() {
        this.panelTarget.classList.toggle('hidden');
        const isHidden = this.panelTarget.classList.contains('hidden');
        this.toggleButtonTarget.textContent = isHidden ? 'Send Test Email' : 'Close';
    }

    selectEmail(event) {
        this.emailInputTarget.value = event.target.value;
        this.updateSendButtonState();
    }

    async requestVerification(event) {
        event.preventDefault();

        const email = this.emailInputTarget.value.trim();
        if (!email) {
            this.showStatus('Please enter an email address', 'error');
            return;
        }

        this.verifyButtonTarget.disabled = true;
        this.verifyButtonTarget.textContent = 'Sending...';

        try {
            const formData = new FormData();
            formData.append('email', email);

            const response = await fetch(this.verifyUrlValue, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                this.showStatus(data.message || 'Verification email sent! Check your inbox.', 'success');
            } else {
                this.showStatus(data.error || 'Failed to send verification', 'error');
            }
        } catch (error) {
            console.error('Error requesting verification:', error);
            this.showStatus('Network error. Please try again.', 'error');
        } finally {
            this.verifyButtonTarget.disabled = false;
            this.verifyButtonTarget.textContent = 'Send Verification';
        }
    }

    async sendEmail(event) {
        event.preventDefault();

        const email = this.emailInputTarget.value.trim();
        if (!email) {
            this.showStatus('Please enter an email address', 'error');
            return;
        }

        const editorController = this.application.getControllerForElementAndIdentifier(
            document.querySelector('[data-controller="mjml-editor"]'),
            'mjml-editor'
        );

        const html = editorController?.getRenderedHtml();
        if (!html) {
            this.showStatus('Please render the MJML first', 'error');
            return;
        }

        this.sendButtonTarget.disabled = true;
        this.sendButtonTarget.textContent = 'Sending...';

        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('html', html);

            const response = await fetch(this.sendUrlValue, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                this.showStatus(data.message || 'Test email sent!', 'success');
            } else {
                this.showStatus(data.error || 'Failed to send email', 'error');
            }
        } catch (error) {
            console.error('Error sending email:', error);
            this.showStatus('Network error. Please try again.', 'error');
        } finally {
            this.sendButtonTarget.disabled = false;
            this.sendButtonTarget.textContent = 'Send Test Email';
        }
    }

    showStatus(message, type) {
        if (!this.hasStatusMessageTarget) return;

        this.statusMessageTarget.textContent = message;
        this.statusMessageTarget.className = 'status-message ' + type;
        this.statusMessageTarget.classList.remove('hidden');

        setTimeout(() => {
            this.statusMessageTarget.classList.add('hidden');
        }, 5000);
    }
}
