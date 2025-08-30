const valueMissingMessage = (() => {
    const input = document.createElement("input");
    input.required = true;

    return input.validationMessage;
})();

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    --nyro-password-font-size: 14px;
    --nyro-password-color: currentColor;
    --nyro-password-placeholder-color: #a9a9a9;

    position: relative;
    display: inline-block;
    font-size: 0.8em;
    font-family: "Arial";
    color: #000;
    background: #fff;
    border: 1px solid #767676;
    border-radius: 2px;
    padding: 1px 2px;
}
:host([disabled]) {
    pointer-events: none;
    opacity: 0.7;
}
:host(:focus) {
    outline: 2px solid #000;
}
div {
    display: flex;
    align-items: center;
}
input {
    min-width: 10em;
    flex-grow: 1;
    width: 0;
    font-family: inherit;
    font-weight: inherit;
    font-style: inherit;
    font-size: var(--nyro-password-font-size);
    color: var(--nyro-password-color);
    border: none;
    background: transparent;
    padding: 0;
    outline: none;
}
input::placeholder {
    color: var(--nyro-password-placeholder-color);
    opacity: 1;
}
input::-webkit-search-decoration,
input::-webkit-search-cancel-button,
input::-webkit-search-results-button,
input::-webkit-search-results-decoration {
    display: none;
}
.toggle a {
    color: var(--nyro-password-color);
    text-decoration: none;
}
.toggle .hide,
:host([show]) .toggle .show {
    display: none;
}
:host([show]) .toggle .hide {
    display: inline;
}

@supports (-webkit-touch-callout: none) {
    input {
        font-size: calc(max(var(--nyro-password-font-size), 16px));
    }
}
</style>
<div>
    <input type="password" />
    <nav class="toggle">
        <a href="#" class="show" tabindex="-1">
            <slot name="show">Show</slot>
        </a>
        <a href="#" class="hide" tabindex="-1">
            <slot name="hide">Hide</slot>
        </a>
    </nav>
</div>
`;

class NyroPassword extends HTMLElement {
    static get formAssociated() {
        return true;
    }

    constructor() {
        super();
        this._internals = this.attachInternals();
    }

    static get observedAttributes() {
        return ["required", "placeholder", "show"];
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "required") {
            this._setMyValidity();
        } else if (name === "placeholder") {
            this.placeholder = next;
        } else if (name === "show") {
            this._setType();
        }
    }

    connectedCallback() {
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._input = this.shadowRoot.querySelector("input");

        this._input.addEventListener("input", () => {
            this._setValue();
        });

        this._input.addEventListener("change", () => {
            this._setValue();
        });

        this._input.addEventListener("keyup", (e) => {
            if (e.key === "Enter") {
                if (this._internals.form) {
                    if (this._internals.form.requestSubmit) {
                        this._internals.form.requestSubmit();
                    } else {
                        this._internals.form.submit();
                    }
                }
            }
        });

        this.shadowRoot.querySelector(".toggle").addEventListener("click", (e) => {
            e.preventDefault();
            const toggle = e.target.closest("a");
            if (toggle) {
                this.show = toggle.classList.contains("show");
                return;
            }

            const slot = e.target.closest('[slot="show"], [slot="hide"]');
            if (slot) {
                this.show = slot.slot === "show";
            }
        });

        if (!this.hasAttribute("tabindex")) {
            this.setAttribute("tabindex", "0");
        }

        this.addEventListener("focus", (e) => {
            if (e.relatedTarget && e.relatedTarget.matches('[type="submit"]')) {
                return;
            }
            if (e.relatedTarget) {
                this._input.select();
            } else {
                this._input.focus();
            }
        });

        if (this._internals.form) {
            this._internals.form.addEventListener("submit", () => {
                this.show = false;
            });
        }

        if (this.hasAttribute("value")) {
            this.value = this.getAttribute("value");
        }
        if (this.hasAttribute("placeholder")) {
            this.placeholder = this.getAttribute("placeholder");
        }
        this._setMyValidity();
    }

    get show() {
        return this.hasAttribute("show");
    }

    set show(show) {
        if (show) {
            this.setAttribute("show", "");
        } else {
            this.removeAttribute("show");
        }
    }

    get required() {
        return this.hasAttribute("required");
    }

    set required(required) {
        if (required) {
            this.setAttribute("required", "");
        } else {
            this.removeAttribute("required");
        }
    }

    get placeholder() {
        return this._input.placeholder;
    }

    set placeholder(placeholder) {
        if (this._input) {
            this._input.placeholder = placeholder;
        }
    }

    get value() {
        return this._input.value;
    }

    set value(value) {
        this._input.value = value;
        this._setValue();
    }

    _setType() {
        this._input.type = this.show ? "text" : "password";
    }

    _setValue() {
        this._internals.setFormValue(this._input.value);
        this._setMyValidity();
    }

    _setMyValidity() {
        if (this.required && this._input && !this._input.value) {
            this.setValidity(
                {
                    valueMissing: true,
                },
                valueMissingMessage,
                this._input
            );
        } else {
            this.setValidity({});
        }
    }

    checkValidity() {
        return this._internals.checkValidity();
    }

    reportValidity() {
        return this._internals.reportValidity();
    }

    setValidity(flags, message, anchor) {
        return this._internals.setValidity(flags, message, anchor || this._input);
    }

    get form() {
        return this._internals.form;
    }

    get name() {
        return this.getAttribute("name");
    }

    get type() {
        return this.localName;
    }

    get validity() {
        return this.internals_.validity;
    }

    get validationMessage() {
        return this.internals_.validationMessage;
    }

    get willValidate() {
        return this.internals_.willValidate;
    }

    // @todo read and implement more functions described here
    // https://web.dev/more-capable-form-controls/
}

window.customElements.define("nyro-password", NyroPassword);

export default NyroPassword;
