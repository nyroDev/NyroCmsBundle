const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    font-family: var(--composer-font-family);
}
dialog {
    border: none;
    background: #fff;
    overflow: visible;
}
dialog::backdrop {
    background-color: rgba(60, 60, 60, 0.3);
    backdrop-filter: blur(0.5rem);
}
dialog .nyroComposerDialogClose {
    position: absolute;
    top: -15px;
    right: -15px;
    display: flex !important;
    justify-content: center;
    align-items: center;
    padding: 0 !important;
    font-size: 18px !important;
    text-align: center !important;
    width: 26px !important;
    border-radius: none !important;
}
dialog .nyroComposerDialogClose .icon {
    width: 16px;
    height: 16px;
}
dialog p {
    margin: 0 0 var(--composer-panel-space) 0;
    text-align: center;
    line-height: 1.5;
}
:host(.nyroComposerDialogConfirm) p {
    max-width: 250px;
}
dialog .actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
dialog .actions a + a {
    margin-left: var(--composer-panel-space);
}
:host(.nyroComposerDialogIframe) dialog {
    width: 80vw;
    height: 90vh;
    padding: 0;
}
:host(.nyroComposerDialogIframe) dialog .dialogIn {
    width: 100%;
    height: 100%;
}
:host(.nyroComposerDialogIframe) dialog iframe {
    width: 100%;
    height: 100%;
    border: none;
}
</style>
<dialog>
    <a href="#" class="nyroComposerDialogClose" part="nyroComposerBtn">X</a>
    <div class="dialogIn"></div>
</dialog>
`;

class NyroComposerDialog extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._dialog = this.shadowRoot.querySelector("dialog");
        this._dialogIn = this._dialog.querySelector(".dialogIn");

        this._dialog.addEventListener("close", () => {
            this.dispatchEvent(
                new Event("close", {
                    bubbles: true,
                    cancelable: true,
                })
            );

            if (!this.noAutoremove) {
                requestAnimationFrame(() => {
                    this.remove();
                });
            }
        });

        this._dialog.querySelector(".nyroComposerDialogClose").innerHTML = this.composer.getIcon("close");
        this._dialog.querySelector(".nyroComposerDialogClose").addEventListener("click", (e) => {
            e.preventDefault();
            this._dialog.close();
        });

        this._dialogIn.addEventListener("click", (e) => {
            const closeDialog = e.target.closest(".cancel, .nyroComposerDialogClose, .closeDialogAfterClick");
            if (!closeDialog) {
                return;
            }

            if (closeDialog.classList.contains("closeDialogAfterClick") && !closeDialog.classList.contains("disabled")) {
                setTimeout(() => {
                    this._dialog.close();
                }, 5);
            } else {
                e.preventDefault();
                this._dialog.close();
            }
        });
    }

    setContent(content) {
        this._dialogIn.appendChild(content);
    }

    get in() {
        return this._dialogIn;
    }

    set noAutoremove(noAutoremove) {
        if (noAutoremove) {
            this.setAttribute("no-autoremove", "");
        } else {
            this.removeAttribute("no-autoremove");
        }
    }

    get noAutoremove() {
        return this.hasAttribute("no-autoremove");
    }

    get composer() {
        return document.querySelector("nyro-composer");
    }

    open() {
        this._dialog.showModal();
    }

    close() {
        this._dialog.close();
    }
}

window.customElements.define("nyro-composer-dialog", NyroComposerDialog);

export default NyroComposerDialog;
