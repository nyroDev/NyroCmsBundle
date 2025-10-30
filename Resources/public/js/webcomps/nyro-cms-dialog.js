const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    font-family: var(--composer-font-family);
}
dialog {
	border: none;
	background: var(--c-white);
    padding: 0;
    border-radius: var(--s-radius);
	overflow: visible;
}
dialog::backdrop {
    background-color: rgba(60, 60, 60, 0.3);
    backdrop-filter: blur(0.5rem);
}

dialog header {
    min-height: var(--s-svg-size);
    border-radius: var(--s-radius) var(--s-radius) 0 0;
    padding: var(--s-padding);
    margin-right: calc(var(--s-padding) + var(--s-svg-size))
}

dialog main {
    border-radius: 0 0 var(--s-radius) var(--s-radius);
    padding: var(--s-padding);
    background-color: var(--c-light-background);
    overflow: auto;
    max-height: 80vh;
}

:host(.nyroCmsDialogConfirm) {
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
:host(.nyroCmsDialogIframe) dialog main {
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
    <header>
        <slot name="title"></slot>
        <slot name="close"></slot>
    </header>
    <main>
        <slot name="content"></slot>
    </main>
</dialog>
`;

const fetchOptions = (options = {}) => {
    return Object.assign(
        {
            method: "GET",
            mode: "cors",
            credentials: "same-origin",
            cache: "no-cache",
            redirect: "follow",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-JS-FETCH": 1,
            },
        },
        options
    );
};

class NyroCmsDialog extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._dialog = this.shadowRoot.querySelector("dialog");

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
            let closeDialog = e.target.closest(".cancel, .closeDialog, .nyroCmsDialogClose, .closeDialogAfterClick");
            if (closeDialog && closeDialog.classList.contains("cancel") && closeDialog.closest(".form_button")) {
                closeDialog = false;
            }
            if (!closeDialog) {
                if (this.keepInDialog) {
                    const link = e.target.closest("a");
                    if (link && link.target !== "_blank") {
                        e.preventDefault();
                        this.loadUrl(link.href);
                    }
                }
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

        this.addEventListener("submit", (e) => {
            if (!this.keepInDialog) {
                return;
            }

            const form = e.target.closest("form");
            if (!form) {
                return;
            }
            e.preventDefault();

            const url = new URL(form.action || document.location.href),
                method = form.method.toUpperCase() || "POST",
                formData = new FormData(form);

            if (method === "GET") {
                for (const [key, value] of formData.entries()) {
                    url.searchParams.append(key, value);
                }
            }

            this.loadFetch(
                fetch(
                    url,
                    fetchOptions({
                        method: method,
                        body: method === "GET" ? undefined : formData,
                    })
                )
            );
        });
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

    set keepInDialog(keepInDialog) {
        if (keepInDialog) {
            this.setAttribute("keep-in-dialog", "");
        } else {
            this.removeAttribute("keep-in-dialog");
        }
    }

    get keepInDialog() {
        return this.hasAttribute("keep-in-dialog");
    }

    loadUrl(url) {
        this.keepInDialog = true;
        return this.loadFetch(fetch(url, fetchOptions()));
    }

    loadFetch(fetchPromise) {
        this.keepInDialog = true;
        this.classList.add("loading");
        return fetchPromise
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then((html) => {
                this.classList.remove("loading");

                this.querySelectorAll("[slot=content], [slot=title]").forEach((slotted) => {
                    slotted.remove();
                });

                const template = document.createElement("template");
                template.innerHTML = html;

                const slotContent = template.content.querySelector("[slot=content]");
                if (slotContent) {
                    const slotTitle = template.content.querySelector("[slot='title']");
                    if (slotTitle) {
                        this.appendChild(slotTitle);
                    }
                    this.appendChild(slotContent);
                } else {
                    const div = document.createElement("div");
                    div.slot = "content";
                    div.innerHTML = html;
                    this.appendChild(div);
                }

                this.dispatchEvent(
                    new Event("nyroCmsDialogFetched", {
                        bubbles: true,
                        cancelable: true,
                    })
                );

                const goToUrl = this.querySelector(".goToUrl");
                if (goToUrl) {
                    document.location.href = goToUrl.href;
                }
            })
            .catch((error) => {
                this.classList.remove("loading");
                console.error("Error loading dialog content:", error);
            });
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
