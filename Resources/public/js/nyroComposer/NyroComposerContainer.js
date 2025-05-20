import Sortable from "sortablejs";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
    min-height: calc(var(--s-header-padding) * 2);
}
nav {
    display: var(--composer-ui-hidden, block);
}
nav .add_item {
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--composer-color-secondary);
    font-weight: var(--composer-font-weight-bold);
    font-style: italic;
    text-decoration: none;
    transition: color var(--composer-transition-time);
}
nav .add_item .icon {
    width: 20px;
    height: 20px;
    margin: 0 5px;
}
nav .add_item:hover,
nav .add_item.active {
    color: var(--composer-color-item);
}
</style>
<slot></slot>
<nav>
    <a href="#" class="add_item"></a>
</nav>
`;

class NyroComposerContainer extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");

        this._addItemBtn = this._nav.querySelector(".add_item");

        this._nav.addEventListener("click", (e) => {
            const addItem = e.target.closest(".add_item");
            if (!addItem) {
                return;
            }

            e.preventDefault();
            this._showAddItem();
        });
    }

    get composer() {
        return this.closest("nyro-composer");
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

    get parentReadonly() {
        return this.hasAttribute("parent-readonly");
    }

    set parentReadonly(parentReadonly) {
        if (parentReadonly) {
            this.setAttribute("parent-readonly", "");
        } else {
            this.removeAttribute("parent-readonly");
        }
        this._setChildParentReadonly();
    }

    get items() {
        return this.querySelectorAll("nyro-composer-item");
    }

    getPanelConfig() {
        const panelConfig = [];

        panelConfig.push({
            type: "icon",
            icon: "items",
        });

        panelConfig.push({
            type: "title",
            title: this.composer.trans("item.add"),
        });

        panelConfig.push({
            type: "buttons",
            name: "addItem",
            templateType: "item",
        });

        return panelConfig;
    }

    setValue(name, value) {
        if (name === "addItem") {
            this._addItem(value);
        }
    }

    init() {
        if (!this.readonly) {
            const addIcon = this.composer.getIcon("add");
            this._addItemBtn.innerHTML = addIcon + this.composer.trans("item.add") + addIcon;

            this._sortable = Sortable.create(this, {
                group: "container",
                handle: ".dragHandle",
                animation: 150,
                onEnd: (e) => {
                    const toBlock = e.to.closest("nyro-composer-block"),
                        fromBlock = e.from.closest("nyro-composer-block");

                    toBlock._setValue();
                    if (fromBlock !== toBlock) {
                        fromBlock._setValue();
                    }
                },
            });
        }

        this.items.forEach((element) => {
            element.init();
            element.parentReadonly = this.readonly || this.parentReadonly;
        });
    }

    _setChildParentReadonly() {
        this.items.forEach((container) => {
            container.parentReadonly = this.readonly || this.parentReadonly;
        });
    }

    get value() {
        const value = [];

        this.items.forEach((item) => {
            value.push(item.value);
        });

        return value;
    }

    select() {
        this._showAddItem();
    }

    unselected() {
        if (this._addItemBtn.classList.contains("active")) {
            this._addItemBtn.classList.remove("active");
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

    _showAddItem() {
        if (this.classList.contains("composerSelected")) {
            this.composer.sidePanel.selected = false;
            return;
        }

        this._addItemBtn.classList.add("active");
        this.composer.sidePanel.selected = this;
    }

    _addItem(type) {
        const item = this.composer.getNewItem(type);
        this.appendChild(item);
        item.init();
        item.select();

        this.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }
}

window.customElements.define("nyro-composer-container", NyroComposerContainer);

export default NyroComposerContainer;
