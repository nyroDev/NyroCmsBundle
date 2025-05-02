import Sortable from "sortablejs";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
}
</style>
<div>
    <slot></slot>
</div>
<nav></nav>
`;

class NyroComposerWorkspace extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");

        this._nav.addEventListener("click", (e) => {
            const addBlock = e.target.closest(".add_block");
            if (!addBlock) {
                return;
            }

            e.preventDefault();
            this._addBlock(addBlock.dataset.type);
        });
    }

    get composer() {
        return this.closest("nyro-composer");
    }

    getPanelOptions() {
        return [];
    }

    init() {
        this._nav.innerHTML = this.composer.getAddBlocksNavHtml();

        this._sortable = Sortable.create(this, {
            group: "block",
            handle: ".dragHandle",
            animation: 150,
        });

        this.querySelectorAll("nyro-composer-block").forEach((element) => {
            element.init();
        });
    }

    _addBlock(type) {
        const block = this.composer.getNewBlock(type);
        this.appendChild(block);
        block.init();
        block.select();

        this.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }
}

window.customElements.define("nyro-composer-workspace", NyroComposerWorkspace);

export default NyroComposerWorkspace;
