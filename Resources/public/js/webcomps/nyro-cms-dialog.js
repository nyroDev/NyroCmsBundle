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

:host(.nyroCmsDialogConfirm) p {
    max-width: 250px;
}

dialog .actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
dialog .actions a + a {
    margin-left: var(--s-padding);
}

:host(.nyroCmsDialogIframe) dialog {
    width: 80vw;
    height: 90vh;
    padding: 0;
}
:host(.nyroCmsDialogIframe) dialog .dialogIn {
    width: 100%;
    height: 100%;
}
:host(.nyroCmsDialogIframe) dialog iframe {
    width: 100%;
    height: 100%;
    border: none;
}
</style>
<dialog>
    <slot name="close"></slot>
    <div class="dialogIn"><slot name="content"></slot></div>
</dialog>
`;

class NyroCmsDialog extends HTMLElement {
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

        this.addEventListener("click", (e) => {
            const closeDialog = e.target.closest(".cancel, .nyroCmsDialogClose, .closeDialogAfterClick");
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

    connectedCallback() {
        const closeBtn = this.querySelector(".nyroCmsDialogClose");
        if (closeBtn) {
            closeBtn.addEventListener("click", (e) => {
                e.preventDefault();
                this._dialog.close();
            });
        }
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

    open() {
        this._dialog.showModal();
    }

    close() {
        this._dialog.close();
    }
}

window.customElements.define("nyro-cms-dialog", NyroCmsDialog);

export default NyroCmsDialog;
