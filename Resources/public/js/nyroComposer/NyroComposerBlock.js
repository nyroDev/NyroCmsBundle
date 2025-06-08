const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    position: relative;
    display: block;
    padding-top: var(--composer-ui-margin-top);
}
:host(.composerSelected) {
    z-index: 1;
}
:host(:hover) {
    z-index: 2;
}
nav {
    position: absolute;
    top: -1px;
    left: calc(-1 * var(--composer-action-size) - 2px);
    right: calc(-1 * var(--composer-action-size) - 2px);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    opacity: 0;
    visibility: hidden;
}
:host(:hover) nav,
:host(.composerSelected) nav {
    opacity: 1;
    visibility: var(--composer-ui-visibility);
}
nav > div {
    display: flex;
    flex-direction: column;
    align-items: center;
}
nav a,
nav .title {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: var(--composer-action-size);
    height: var(--composer-action-size);
    background-color: var(--composer-color-bg-nav);
    border: 1px solid var(--composer-elt-color);
    text-decoration: none;
    color: var(--composer-color);
    transition: color var(--composer-transition-time), background-color var(--composer-transition-time);
}
nav a:hover {
    color: var(--composer-color-bg-nav);
    background-color: var(--composer-elt-color);
}
nav a[data-action="delete"]:hover {
    background-color: var(--composer-color-error);
}
nav a .icon {
    width: 16px;
    height: 16px;
}
nav .title {
    color: var(--composer-elt-color);
    font-family: var(--composer-font-family);
    font-size: 12px;
    padding: 5px 0;
    height: auto;
    letter-spacing: -0.2em;
    writing-mode: vertical-rl;
    text-orientation: upright;
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
            const actionable = e.target.closest("[data-action]");
            if (!actionable) {
                return;
            }

            e.preventDefault();
            if (this["_" + actionable.dataset.action]) {
                this["_" + actionable.dataset.action]();
            }
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

    get workspace() {
        return this.closest("nyro-composer-workspace");
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
        this._setChildParentReadonly();
    }

    connectedCallback() {
        this.classList.add("composerBlock");
        this.setAttribute("name", "content[]");
    }

    init() {
        this._setValue();

        this.containers.forEach((element) => {
            element.init();
            if (this.readonly) {
                element.parentReadonly = true;
            }
        });

        this._nav.appendChild(this.composer.getElementNav(this.composer.getTemplate("block", this.blockType).title));
    }

    _setChildParentReadonly() {
        this.containers.forEach((container) => {
            container.parentReadonly = this.readonly;
        });
    }

    _edit() {
        this.select();
    }

    _duplicate() {
        const newBlock = this.composer.getNewBlock(this.blockType);
        this.after(newBlock);

        newBlock.init();

        if (this.readonly) {
            newBlock.readonly = true;
        }

        this.containers.forEach((container, idxContainer) => {
            const newContainer = newBlock.containers[idxContainer];
            container.items.forEach((item) => {
                const newItem = this.composer.getNewItem(item.type);
                newContainer.appendChild(newItem);
                newItem.init();
                newItem.setValueFrom(item);
            });
        });

        newBlock.select();

        newBlock.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    _delete() {
        if (this.querySelector('nyro-composer-item[type="handler"]')) {
            this.composer.alert({
                content: this.composer.trans("cannotDeleteBlockWithHandler"),
            });
            return;
        }

        this.composer.confirm(
            () => {
                const parent = this.parentElement;
                this.remove();
                parent.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                        cancelable: true,
                    })
                );
            },
            false,
            {
                text: this.composer.trans("block.deleteConfirm", false),
            }
        );
    }

    _moveTop() {
        if (this.workspace) {
            this.workspace.insertBefore(this, this.workspace.firstChild);
            this._dispatchChange();
        }
    }

    _moveBottom() {
        if (this.workspace) {
            this.workspace.appendChild(this);
            this._dispatchChange();
        }
    }

    _moveUp() {
        if (this.previousElementSibling) {
            this.parentElement.insertBefore(this, this.previousElementSibling);
            this._dispatchChange();
        }
    }

    _moveDown() {
        if (this.nextElementSibling) {
            this.parentElement.insertBefore(this.nextElementSibling, this);
            this._dispatchChange();
        }
    }

    getPanelConfig() {
        const panelConfig = [];

        panelConfig.push({
            type: "icon",
            icon: "block",
        });

        panelConfig.push({
            type: "title",
            title: this.composer.trans("block.title"),
        });

        if (this.readonly) {
            panelConfig.push({
                type: "text",
                text: this.composer.trans("block.readonly"),
            });

            return panelConfig;
        }

        panelConfig.push({
            type: "text",
            text: this.composer.trans("block.intro"),
        });

        panelConfig.push({
            type: "buttons",
            name: "type",
            templateType: "block",
            active: this.blockType,
            conditional: this.composer.trans("block.editText"),
        });

        return panelConfig;
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

    selectFirstContainer() {
        const firstContainer = this.containers[0];
        if (firstContainer) {
            firstContainer.select();
        }
    }

    unselect() {
        this.dispatchEvent(
            new Event("composerUnselect", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    _dispatchChange() {
        this.dispatchEvent(
            new Event("change", {
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
