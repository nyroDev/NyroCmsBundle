const template = document.createElement("template");
template.innerHTML = `
<style>
.inputUrl {
    width: 100%;
    box-sizing: border-box;
    font-family: var(--composer-font-family);
    font-size: 12px;
    padding: 2px 5px;
    border-radius: 5px;
    line-height: 26px;
    margin-bottom: 10px;
    border: 1px solid var(--composer-color-secondary);
    outline: none;
    transition: border-color var(--composer-transition-time);
}
.inputUrl:focus {
    border-color: var(--composer-color);
}
.error {
    margin: 10px 0;
    display: none;
    color: var(--composer-color-error);
}
.error:not(:empty) {
    display: block;
}
</style>
<form>
    <input type="url" name="iframeUrl" class="inputUrl" required placeholder="https://www.canva.com/design/DAGoAeyHSBk/FPyJ5FLcXVj3bB4Z4VT6JA/view" /><br />
    <button type="submit" class="apply" part="nyroComposerBtn nyroComposerBtnUi">Validate URL</button><br />
</form>
<div class="error"></div>
`;

class NyroComposerInputIframeUrl extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._form = this.shadowRoot.querySelector("form");
        this._url = this.shadowRoot.querySelector('input[type="url"]');
        this._error = this.shadowRoot.querySelector(".error");

        const applyBtn = this.shadowRoot.querySelector(".apply");
        applyBtn.innerHTML = this.composer.trans("item.videoEmbed.validate");

        const templateIframeAnalyser = document.createElement("template");
        this._url.addEventListener("input", () => {
            this._iframeInput = false;
            if (this._url.value.indexOf("</iframe>") === -1) {
                return;
            }

            templateIframeAnalyser.innerHTML = this._url.value;
            const iframe = templateIframeAnalyser.content.querySelector("iframe");
            if (iframe) {
                this._iframeInput = {
                    width: iframe.width || 600,
                    height: iframe.height || 450,
                    rawIframe: this._url.value,
                };
                this._url.value = iframe.src;
            }
        });

        this._form.addEventListener("submit", (e) => {
            e.preventDefault();
            this._fetchUrl();
        });

        this._value = {};
    }

    get composer() {
        return document.querySelector("nyro-composer");
    }

    get selected() {
        return this.composer.sidePanel.selected;
    }

    set value(value) {
        this._value.url = value;
        this._url.value = value;
    }

    get value() {
        return this._value;
    }

    _fetchUrl() {
        this._error.innerHTML = "";

        fetch(document.location.href, {
            method: "POST",
            body: new FormData(this._form),
        })
            .then((response) => {
                return response.json();
            })
            .then((response) => {
                if (response.err) {
                    this._error.innerHTML = response.err;
                    return;
                }

                if (this._iframeInput) {
                    response.data = this._iframeInput;
                }

                this._value = response;
                this.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                        cancelable: true,
                    })
                );
            });
    }
}

window.customElements.define("nyro-composer-input-iframe-url", NyroComposerInputIframeUrl);

export default NyroComposerInputIframeUrl;
