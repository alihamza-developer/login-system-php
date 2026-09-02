class Dialog {

    constructor() {
        this.open = null;
    }

    // Ask the user
    ask(options = {}) {
        let title = options.title || 'Are you sure?',
            text = options.text || "You won't be able to revert this!",
            confirmText = options.confirmButtonText || 'Yes',
            cancelText = options.cancelButtonText || 'Cancel',
            danger = options.danger !== false;

        return new Promise(resolve => {
            let overlay = this.build(title, text, confirmText, cancelText, danger),
                done = value => this.close(overlay, resolve, value);

            overlay.querySelector('.confirm-yes').onclick = () => done(true);
            overlay.querySelector('.confirm-no').onclick = () => done(false);
            overlay.onclick = e => {
                if (e.target === overlay) done(false);
            };

            overlay.onkeydown = e => {
                if (e.key === 'Escape') done(false);
            };

            document.body.appendChild(overlay);
            this.open = overlay;
            requestAnimationFrame(() => overlay.classList.add('is-in'));
            overlay.querySelector('.confirm-yes').focus();
        });
    }

    // Build the markup
    build(title, text, confirmText, cancelText, danger) {
        let overlay = document.createElement('div'),
            box = document.createElement('div'),
            heading = document.createElement('p'),
            message = document.createElement('p'),
            actions = document.createElement('div'),
            no = document.createElement('button'),
            yes = document.createElement('button');

        overlay.className = 'confirm-overlay';
        overlay.tabIndex = -1;

        box.className = 'confirm-box';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');

        heading.className = 'confirm-title';
        heading.textContent = title;

        message.className = 'confirm-text';
        message.textContent = text;

        actions.className = 'confirm-actions';

        no.type = 'button';
        no.className = 'confirm-btn confirm-no';
        no.textContent = cancelText;

        yes.type = 'button';
        yes.className = 'confirm-btn confirm-yes' + (danger ? ' is-danger' : '');
        yes.textContent = confirmText;

        actions.appendChild(no);
        actions.appendChild(yes);
        box.appendChild(heading);
        box.appendChild(message);
        box.appendChild(actions);
        overlay.appendChild(box);

        return overlay;
    }

    // Close and resolve
    close(overlay, resolve, value) {
        overlay.classList.remove('is-in');
        setTimeout(() => overlay.remove(), 180);
        this.open = null;
        resolve({ value: value, isConfirmed: value });
    }
}

let Confirm = new Dialog();
