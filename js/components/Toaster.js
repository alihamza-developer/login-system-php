class Toaster {

    // Toast icons
    static icons = {
        success: '<svg viewBox="0 0 24 24"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>',
        error: '<svg viewBox="0 0 24 24"><path d="M19 6.4 17.6 5 12 10.6 6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12z"/></svg>',
        warning: '<svg viewBox="0 0 24 24"><path d="M1 21h22L12 2zm12-3h-2v-2h2zm0-4h-2v-4h2z"/></svg>',
        info: '<svg viewBox="0 0 24 24"><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/></svg>',
    };

    constructor() {
        this.stack = null;
        this.timeout = 4500;
        this.errorTimeout = 7000;
    }

    // Toast container
    getStack() {
        if (!this.stack || !this.stack.isConnected) {
            this.stack = document.querySelector('.notify-stack');
        }
        if (!this.stack) {
            this.stack = document.createElement('div');
            this.stack.className = 'notify-stack';
            document.body.appendChild(this.stack);
        }
        return this.stack;
    }

    // Show a toast
    show(message, type = 'info', options = {}) {
        if (!message) return null;
        if (!Toaster.icons[type]) type = 'info';

        let title = options.title || '',
            isHtml = options.html === true,
            duration = ('duration' in options) ? options.duration : this.durationFor(type),
            toast = document.createElement('div');
        toast.className = 'notify-toast notify-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        toast.appendChild(this.buildIcon(type));
        toast.appendChild(this.buildBody(message, title, isHtml));
        toast.appendChild(this.buildClose(toast));

        if (duration) toast.appendChild(this.buildBar(duration));

        this.getStack().appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('is-in'));

        if (duration) this.startTimer(toast, duration);
        return toast;
    }

    // Default duration
    durationFor(type) {
        return type === 'error' ? this.errorTimeout : this.timeout;
    }

    // Icon element
    buildIcon(type) {
        let icon = document.createElement('span');
        icon.className = 'notify-icon';
        icon.innerHTML = Toaster.icons[type];
        return icon;
    }

    // Title and message
    buildBody(message, title, isHtml) {
        let body = document.createElement('div'),
            text = document.createElement('p');

        body.className = 'notify-body';

        if (title) {
            let heading = document.createElement('p');
            heading.className = 'notify-title';
            heading.textContent = title;
            body.appendChild(heading);
        }

        text.className = 'notify-text';
        if (isHtml) text.innerHTML = message;
        else text.textContent = message;
        body.appendChild(text);

        return body;
    }

    // Close button
    buildClose(toast) {
        let close = document.createElement('button');
        close.type = 'button';
        close.className = 'notify-close';
        close.setAttribute('aria-label', 'Dismiss');
        close.innerHTML = '&times;';
        close.onclick = () => this.dismiss(toast);
        return close;
    }

    // Countdown bar
    buildBar(duration) {
        let bar = document.createElement('span');
        bar.className = 'notify-bar';
        bar.style.transition = 'transform ' + duration + 'ms linear';
        bar.style.transform = 'scaleX(1)';
        requestAnimationFrame(() => bar.style.transform = 'scaleX(0)');
        return bar;
    }

    // Auto dismiss, pause on hover
    startTimer(toast, duration) {
        toast.timer = setTimeout(() => this.dismiss(toast), duration);

        toast.onmouseenter = () => {
            clearTimeout(toast.timer);
            let bar = toast.querySelector('.notify-bar');
            if (!bar) return;
            let left = bar.getBoundingClientRect().width / toast.offsetWidth;
            bar.style.transition = 'none';
            bar.style.transform = 'scaleX(' + left + ')';
        };

        toast.onmouseleave = () => {
            toast.timer = setTimeout(() => this.dismiss(toast), 1500);
            let bar = toast.querySelector('.notify-bar');
            if (!bar) return;
            bar.style.transition = 'transform 1500ms linear';
            bar.style.transform = 'scaleX(0)';
        };
    }

    // Remove a toast
    dismiss(toast) {
        if (!toast || toast.dataset.closing) return;
        toast.dataset.closing = '1';
        clearTimeout(toast.timer);
        toast.classList.remove('is-in');
        toast.classList.add('is-out');
        setTimeout(() => toast.remove(), 220);
    }

    // Remove all toasts
    clear() {
        document.querySelectorAll('.notify-toast').forEach(toast => this.dismiss(toast));
    }

    // Shortcuts
    success(message, options = {}) { return this.show(message, 'success', options); }
    error(message, options = {}) { return this.show(message, 'error', options); }
    warning(message, options = {}) { return this.show(message, 'warning', options); }
    info(message, options = {}) { return this.show(message, 'info', options); }
}

let Notify = new Toaster();
