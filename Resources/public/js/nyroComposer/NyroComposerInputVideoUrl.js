const template = document.createElement("template");
template.innerHTML = `
<style>
input[type="url"] {
    font-family: var(--font);
    font-size: 12px;
    border-radius: none;
    border: none;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    padding: 2px 4px;
}
.error {
    display: none;
    color: red;
}
.error:not(:empty) {
    display: block;
}
</style>

<br />
<input type="url" required placeholder="https://www.youtube.com/watch?v=KbNiayAk-70" /><br />
<div class="autoplayOption">
    <input type="checkbox" id="autoplay" value="1" />
    <label for="autoplay">Autoplay</label>
</div>
<a href="#" part="nyroComposerBtn nyroComposerBtnUi" class="apply">Apply</a><br />
<div class="error"></div>
`;

class NyroComposerInputVideoUrl extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._url = this.shadowRoot.querySelector('input[type="url"]');
        this._autoplayOption = this.shadowRoot.querySelector(".autoplayOption");
        this._autoplay = this._autoplayOption.querySelector("input");
        this._error = this.shadowRoot.querySelector(".error");

        this._autoplayOption.querySelector("label").innerHTML = this.composer.trans("autoplay");
        const applyBtn = this.shadowRoot.querySelector(".apply");
        applyBtn.innerHTML = this.composer.trans("apply");
        applyBtn.addEventListener("click", (e) => {
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
        if (!this._url.reportValidity()) {
            return;
        }

        const url = this._url.value.trim();
        const data = new FormData();
        data.append("videoUrl", url);
        if (this._autoplay.checked) {
            data.append("autoplay", 1);
        }

        fetch(document.location.href, {
            method: "POST",
            body: data,
        })
            .then((response) => {
                return response.json();
            })
            .then((response) => {
                if (response.err) {
                    this._error.innerHTML = response.err;
                    return;
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

    init() {
        if (this.selected.cfg.editables.autoplay) {
            if (this.selected.value.autoplay) {
                this._autoplay.checked = true;
            }
        } else {
            this._autoplayOption.style.display = "none";
        }
    }
}

window.customElements.define("nyro-composer-input-video-url", NyroComposerInputVideoUrl);

export default NyroComposerInputVideoUrl;
