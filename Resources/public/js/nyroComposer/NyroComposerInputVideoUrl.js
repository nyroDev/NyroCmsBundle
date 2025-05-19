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
    border: 1px solid var(--composer-color-secondary);
    outline: none;
    transition: border-color var(--composer-transition-time);
}
.inputUrl:focus {
    border-color: var(--composer-color);
}
.autoplayOption {
    font-size: 12px;
    margin: 10px 0;
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
<input type="url" class="inputUrl" required placeholder="https://www.youtube.com/watch?v=KbNiayAk-70" /><br />
<div class="autoplayOption">
    <input type="checkbox" id="autoplay" value="1" />
    <label for="autoplay">Autoplay when the page is opened</label>
</div>
<a href="#" part="nyroComposerBtn nyroComposerBtnUi" class="apply">Validate URL</a><br />
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

        this._autoplayOption.querySelector("label").innerHTML = this.composer.trans("item.videoEmbed.autoplay");
        const applyBtn = this.shadowRoot.querySelector(".apply");
        applyBtn.innerHTML = this.composer.trans("item.videoEmbed.validate");
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
