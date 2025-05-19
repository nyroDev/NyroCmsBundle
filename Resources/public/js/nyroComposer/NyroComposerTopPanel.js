const template = document.createElement("template");
template.innerHTML = `
<style>
:host > * {
    margin-left: var(--composer-panel-space);
}
.flexSpacer {
    flex-grow: 1;
}
</style>
<slot name="nav"></slot>
<span class="flexSpacer"></span>
<slot name="title"></slot>
<span class="flexSpacer"></span>
<a href="#" part="nyroComposerBtn nyroComposerBtnCancel" class="cancel">Cancel</a>
<a href="#" part="nyroComposerBtn nyroComposerBtnDisabled" class="submit">Save</a>
`;

class NyroComposerTopPanel extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._cancel = this.shadowRoot.querySelector(".cancel");
        this._cancel.addEventListener("click", (e) => {
            e.preventDefault();
            document.location.href = this.getAttribute("cancel-url");
        });

        this._submit = this.shadowRoot.querySelector(".submit");
        this._submit.addEventListener("click", (e) => {
            e.preventDefault();
            if (this._unloadListener) {
                window.removeEventListener("beforeunload", this._unloadListener);
                this._unloadListener = false;
            }
            this.composer.form.submit();
        });
    }

    init() {
        this._cancel.innerHTML = this.composer.trans("cancel");
        this._submit.innerHTML = this.composer.trans("save");

        this.addEventListener("change", (e) => {
            if (e.target.id === "templateChoose") {
                e.preventDefault();

                const formData = new FormData(this.composer.form);

                formData.append("template", e.target.value);

                fetch(document.location.href, {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => {
                        if (response.err) {
                            this.composer.alert({
                                content: response.err,
                            });
                            return;
                        }
                        return response.text();
                    })
                    .then((response) => {
                        if (response) {
                            this.composer.workspace.innerHTML = response;
                            this.composer.workspace.initBlocks();
                        }
                    });

                return;
            } else if (e.target.id === "themeChoose") {
                e.preventDefault();
                this.composer.form.querySelector('input[name="theme"]').value = e.target.value;

                this.composer.form.classList.values().forEach((className) => {
                    if (className.indexOf("composerTheme_") === 0) {
                        this.composer.form.classList.remove(className);
                    }
                });
                this.composer.form.classList.add("composerTheme_" + e.target.value);
                this.changed = true;

                return;
            }

            const autoLocation = e.target.closest(".nyroComposerSelectAutoLocation");
            if (!autoLocation) {
                return;
            }

            e.preventDefault();

            document.location.href = autoLocation.value;
        });
    }

    set changed(changed) {
        this._submit.part.toggle("nyroComposerBtnDisabled", !changed);
        if (!this._unloadListener) {
            this._unloadListener = (e) => {
                e.preventDefault();
                e.returnValue = true;
            };
            window.addEventListener("beforeunload", this._unloadListener);
        }
    }

    get composer() {
        return this.closest("nyro-composer");
    }
}

window.customElements.define("nyro-composer-top-panel", NyroComposerTopPanel);

export default NyroComposerTopPanel;
