import Sortable from "sortablejs";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
    min-height: calc(var(--s-header-padding) * 2);
}
nav {
    text-align: center;
}
</style>
<slot></slot>
<nav></nav>
`;

class NyroComposerContainer extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");

        this._nav.addEventListener("click", (e) => {
            const addItem = e.target.closest(".add_item");
            if (!addItem) {
                return;
            }

            e.preventDefault();
            this._addItem(addItem.dataset.type);
        });
    }

    get composer() {
        return this.closest("nyro-composer");
    }

    get readonly() {
        return this.hasAttribute("readonly");
    }

    getPanelOptions() {
        return [];
    }

    init() {
        if (!this.readonly) {
            this._nav.innerHTML = this.composer.getAddItemsNavHtml();

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

        this.querySelectorAll("nyro-composer-item").forEach((element) => {
            element.init();
        });
    }

    get value() {
        const value = [];

        this.querySelectorAll("nyro-composer-item").forEach((item) => {
            value.push(item.value);
        });

        return value;
    }

    _addItem(type) {
        const item = this.composer.getNewItem(type);
        const t = this.appendChild(item);
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
