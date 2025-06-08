const templateToaster = document.createElement("template");
templateToaster.innerHTML = `
<style>
:host {
    --nyro-toaster-closing-time: 0.3s;

    display: block;

    background: #fff;
    border: 1px solid #bfbfbf;
    padding: 0.3em;
    margin: 0.3em;

    transition: opacity var(--nyro-toaster-closing-time), visibility var(--nyro-toaster-closing-time);
}
:host(.closed) {
    opacity: 0;
    visibility: hidden;
}
</style>
<slot></slot>
`;

class NyroToaster extends HTMLElement {
    static get observedAttributes() {
        return ["no-autoclose"];
    }

    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(templateToaster.content.cloneNode(true));
    }

    get noAutoclose() {
        return this.hasAttribute("no-autoclose");
    }

    set noAutoclose(noAutoclose) {
        if (noAutoclose) {
            this.setAttribute("no-autoclose", "");
        } else {
            this.removeAttribute("no-autoclose");
        }
        this._setAutoCloseTimeout();
    }

    get autocloseTimeout() {
        return this.hasAttribute("autoclose-timeout") ? parseInt(this.getAttribute("autoclose-timeout")) : 5;
    }

    set autocloseTimeout(autocloseTimeout) {
        if (autocloseTimeout) {
            this.setAttribute("autoclose-timeout", parseInt(autocloseTimeout));
        } else {
            this.removeAttribute("autoclose-timeout");
        }
    }

    connectedCallback() {
        this.addEventListener("click", (e) => {
            const close = e.target.closest(".close");
            if (close) {
                e.preventDefault();
                this.close();
            }
        });
        this._setAutoCloseTimeout();
    }

    _setAutoCloseTimeout() {
        if (this._autoCloseTimeout) {
            clearTimeout(this._autoCloseTimeout);
        }

        if (!this.noAutoclose) {
            this._autoCloseTimeout = setTimeout(() => {
                this.close();
            }, this.autocloseTimeout * 1000);
        }
    }

    close() {
        this.addEventListener("transitionend", () => {
            this.remove();
        });

        this.classList.add("closed");
    }
}

window.customElements.define("nyro-toaster", NyroToaster);

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 999999;
}
</style>
<slot></slot>
`;

class NyroToasterStack extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));
    }
}

window.customElements.define("nyro-toaster-stack", NyroToasterStack);

export { NyroToasterStack, NyroToaster };

export default NyroToaster;
