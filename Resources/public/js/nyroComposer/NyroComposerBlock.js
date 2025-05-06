const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    position: relative;
    display: block;
    padding-top: 25px;
}
nav {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    text-align: center;
    opacity: 0;
    visibility: hidden;
}
:host(:hover) nav {
    opacity: 1;
    visibility: visible;
}
</style>
<nav></nav>
<slot></slot>
`;

class NyroComposerBlock extends HTMLElement {
    static get formAssociated() {
        return true;
    }

    constructor() {
        super();
        this._internals = this.attachInternals();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");
        this._nav.addEventListener("click", (e) => {
            const del = e.target.closest(".deleteHandle");
            if (!del) {
                return;
            }
            e.preventDefault();

            if (this.querySelector('nyro-composer-item[type="handler"]')) {
                this.composer.alert({
                    content: this.composer.trans("cannotDeleteBlockWithHandler"),
                });
                return;
            }

            this.composer.confirm(() => {
                const parent = this.parentElement;
                this.remove();
                parent.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                        cancelable: true,
                    })
                );
            });
        });

        this.addEventListener("change", () => {
            this._setValue();
        });

        this.addEventListener("click", (e) => {
            if (!e.defaultPrevented) {
                e.preventDefault();
                this.select();
            }
        });
    }

    get composer() {
        return this.closest("nyro-composer");
    }

    get containers() {
        return this.querySelectorAll("nyro-composer-container");
    }

    get blockType() {
        return this.getAttribute("type");
    }

    set blockType(blockType) {
        this.setAttribute("type", blockType);
    }

    get readonly() {
        return this.hasAttribute("readonly");
    }

    set readonly(readonly) {
        if (readonly) {
            this.setAttribute("readonly", "");
        } else {
            this.removeAttribute("readonly");
        }
    }

    connectedCallback() {
        this.setAttribute("name", "content[]");
    }

    init() {
        this._setValue();

        this.containers.forEach((element) => {
            element.init();
        });
        const btns = ["select", "drag"];
        if (!this.readonly) {
            btns.push("delete");
        }
        this.composer.fillNav(this._nav, btns);
    }

    getPanelOptions() {
        if (this.readonly) {
            return [];
        }

        const typeOptions = [];

        this.composer.getTemplates("block").forEach((blockTemplate) => {
            typeOptions.push({
                value: blockTemplate.dataset.id,
                name: blockTemplate.title,
            });
        });

        return [
            {
                name: "type",
                label: "Type",
                dataType: "select",
                value: this.blockType,
                dataOptions: typeOptions,
            },
        ];
    }

    setValue(name, value) {
        if (name !== "type") {
            return;
        }

        const newBlock = this.composer.getNewBlock(value);
        this.after(newBlock);
        const newContainers = Array.from(newBlock.containers);

        if (newContainers.length) {
            this.containers.forEach((container, idx) => {
                const newContainer = newContainers[Math.min(newContainers.length - 1, idx)];
                while (container.childNodes.length > 0) {
                    newContainer.appendChild(container.childNodes[0]);
                }
            });
        }

        this.remove();
        newBlock.init();
        newBlock.select();

        newBlock.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    select() {
        this.dispatchEvent(
            new Event("composerSelect", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    unselect() {
        this.dispatchEvent(
            new Event("composerUnselect", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    _setValue() {
        const conts = [];
        this.containers.forEach((container) => {
            conts.push(container.value);
        });
        const value = {
            _type: this.blockType,
            conts: conts,
        };
        if (this.readonly) {
            value.readonly = true;
        }
        this._internals.setFormValue(JSON.stringify(value));
    }

    checkValidity() {
        return this._internals.checkValidity();
    }

    reportValidity() {
        return this._internals.reportValidity();
    }

    setValidity(flags, message, anchor) {
        return this._internals.setValidity(flags, message, anchor);
    }

    get form() {
        return this.internals.form;
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
}

window.customElements.define("nyro-composer-block", NyroComposerBlock);

export default NyroComposerBlock;
