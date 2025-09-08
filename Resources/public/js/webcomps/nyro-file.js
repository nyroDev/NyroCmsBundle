const valueMissingMessage = (() => {
    const input = document.createElement("input");
    input.required = true;

    return input.validationMessage;
})();

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
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
    height: 100%;
}
input {
    position: absolute;
    inset: 0;
    margin: 0;
    padding: 0;
    opacity: 0;
    z-index: 1;
}
input::-webkit-file-upload-button {
    width: 100%;
    height: 100%;
    border: none;
}
:host(:hover) input {
    visibility: hidden;
}
</style>
<input type="file" />
<div>
    <slot name="choose"></slot>
    <slot name="current"></slot>
    <slot name="delete"></slot>
</div>
`;

/**
 * Download a file throufh Fetch and return a File object.
 * @param {string} url - L'URL du fichier à télécharger
 * @returns {Promise<File>} - L'objet File créé
 */
async function fetchUrlToFile(url) {
    const urlObject = new URL(url, window.location.href);
    const response = await fetch(url);
    const mimeType = response.headers.get("Content-Type") || "";
    const blob = await response.blob();
    const filename = urlObject.pathname.split("/").pop() || "download";

    return new File([blob], filename, { type: mimeType || blob.type });
}

class NyroFile extends HTMLElement {
    static get formAssociated() {
        return true;
    }

    constructor() {
        super();
        this._internals = this.attachInternals();
    }

    static get observedAttributes() {
        return ["required", "placeholder", "accept"];
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "required") {
            this._setMyValidity();
        } else if (name === "placeholder") {
            this._setPlaceholder();
        } else if (name === "accept") {
            this._setAccept();
        } else if (name === "name-delete") {
            this._setValue();
        }
    }

    connectedCallback() {
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._hasDelete = false;
        this._currentOnInit = false;
        this._currentTextOnInit = false;
        this._lastFile = null;

        this._input = this.shadowRoot.querySelector("input");

        this._chooseButton = this.querySelector('[slot="choose"]');
        if (!this._chooseButton) {
            this._chooseButton = document.createElement("button");
            this._chooseButton.slot = "choose";
            this._chooseButton.textContent = "Choose a file";
            this.appendChild(this._chooseButton);
        }

        this._current = this.querySelector('[slot="current"]');
        if (!this._current) {
            this._current = document.createElement("a");
            this._current.slot = "current";
            this._current.href = "#";
            this._current.innerHTML = "<span></span>";
            this.appendChild(this._current);
        } else if (this._current.getAttribute("href") && this._current.getAttribute("href") !== "#") {
            this._currentOnInit = this._current.getAttribute("href");
            this._currentTextOnInit = this._current.querySelector("span").textContent;
        }
        this._current.target = "_blank";

        this._delete = this.querySelector('[slot="delete"]');
        if (!this._delete) {
            this._delete = document.createElement("a");
            this._delete.slot = "delete";
            this._delete.href = "#";
            this._delete.textContent = "X";
            this.appendChild(this._delete);
        }

        this._chooseButton.addEventListener("click", (e) => {
            e.preventDefault();
            this._input.showPicker();
        });
        this._delete.addEventListener("click", (e) => {
            e.preventDefault();
            this.value = false;
        });

        this._input.addEventListener("change", () => {
            this._setValue();
        });

        if (!this.hasAttribute("tabindex")) {
            this.setAttribute("tabindex", "0");
        }

        this.addEventListener("focus", (e) => {
            if (e.relatedTarget && e.relatedTarget.matches('[type="submit"]')) {
                return;
            }
            this._input.showPicker();
        });

        if (this.hasAttribute("value")) {
            this.value = this.getAttribute("value");
        }
        this._setPlaceholder();
        this._setAccept();
        this._setMyValidity();
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
        return this.getAttribute("placeholder");
    }

    set placeholder(placeholder) {
        if (placeholder) {
            this.setAttribute("placeholder", placeholder);
        } else {
            this.removeAttribute("placeholder");
        }
    }

    get nameDelete() {
        return this.getAttribute("name-delete") || this.name + "Delete";
    }

    set nameDelete(nameDelete) {
        if (nameDelete) {
            this.setAttribute("name-delete", nameDelete);
        } else {
            this.removeAttribute("name-delete");
        }
    }

    get accept() {
        return this.getAttribute("accept");
    }

    set accept(accept) {
        if (accept) {
            this.setAttribute("accept", accept);
        } else {
            this.removeAttribute("accept");
        }
    }

    get hasValue() {
        return (this._input && this._input.value) || (this._currentOnInit && !this._hasDelete);
    }

    get value() {
        return this._input.value;
    }

    set value(value) {
        if (!value) {
            this._input.value = "";
            this._hasDelete = true;
            this._setValue();
        } else {
            if (value instanceof File) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(value);
                this._input.files = dataTransfer.files;
                this._setValue();
            } else {
                // Consider value as an URL
                fetchUrlToFile(value).then((file) => {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    this._input.files = dataTransfer.files;

                    this._setValue();
                });
            }
        }
    }

    _setPlaceholder() {
        if (this._current && !this.hasValue) {
            this._current.querySelector("span").textContent = this.placeholder || "No file selected";
        }
    }

    _setAccept() {
        if (!this._input) {
            return;
        }
        if (this.accept) {
            this._input.setAttribute("accept", this.accept);
        } else {
            this._input.removeAttribute("accept");
        }
    }

    _clearObjectUrl() {
        if (this._lastFile) {
            URL.revokeObjectURL(this._lastFile);
            this._lastFile = false;
        }
    }

    _setValue() {
        const formData = new FormData();

        this._clearObjectUrl();
        this._current.href = "#";
        this._setPlaceholder();

        if (this._input && this._input.files && this._input.files.length > 0) {
            this._lastFile = this._input.files[0];
            formData.append(this.name, this._lastFile);

            this._current.href = URL.createObjectURL(this._lastFile);
            this._current.querySelector("span").textContent = this._lastFile.name;
        } else if (this._currentOnInit && this._hasDelete) {
            formData.append(this.nameDelete, this._currentOnInit);
        }

        this._internals.setFormValue(formData);
        this._setMyValidity();
    }

    _setMyValidity() {
        if (this.required && this._input && !this.hasValue) {
            this.setValidity(
                {
                    valueMissing: true,
                },
                valueMissingMessage,
                this._chooseButton
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

window.customElements.define("nyro-file", NyroFile);

export default NyroFile;
