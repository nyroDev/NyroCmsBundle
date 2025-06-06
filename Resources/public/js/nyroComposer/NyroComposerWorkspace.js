import Sortable from "sortablejs";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
}
nav {
    display: var(--composer-ui-display, block);
    margin-top: var(--composer-panel-space);
    visibility: var(--composer-ui-visibility);
}
nav .add_block {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px;
    border: 1px dashed var(--composer-color-secondary);
    color: var(--composer-color-secondary);
    font-weight: var(--composer-font-weight-bold);
    font-style: italic;
    text-decoration: none;
    transition: color var(--composer-transition-time), border-color var(--composer-transition-time);
}
nav .add_block .icon {
    width: 20px;
    height: 20px;
    margin: 0 5px;
}
nav .add_block:hover,
nav .add_block.active {
    color: var(--composer-color-block);
    border-color: var(--composer-color-block);
}
</style>
<div>
    <slot></slot>
</div>
<nav>
    <a href="#" class="add_block"></a>
</nav>
`;

class NyroComposerWorkspace extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");

        this._addBlockBtn = this._nav.querySelector(".add_block");

        this._nav.addEventListener("click", (e) => {
            const addBlock = e.target.closest(".add_block");
            if (!addBlock) {
                return;
            }

            e.preventDefault();
            this._showAddBlock();
        });
    }

    get composer() {
        return this.closest("nyro-composer");
    }

    getPanelConfig() {
        const panelConfig = [];

        panelConfig.push({
            type: "icon",
            icon: "blocks",
        });

        panelConfig.push({
            type: "title",
            title: this.composer.trans("block.add"),
        });

        panelConfig.push({
            type: "buttons",
            name: "addBlock",
            templateType: "block",
        });

        return panelConfig;
    }

    setValue(name, value) {
        if (name === "addBlock") {
            this._addBlock(value);
        }
    }

    init() {
        const addIcon = this.composer.getIcon("add");
        this._addBlockBtn.innerHTML = addIcon + this.composer.trans("block.add") + addIcon;

        this._sortable = Sortable.create(this, {
            group: "block",
            handle: ".dragHandle",
            animation: 150,
        });

        this.initBlocks();
    }

    initBlocks() {
        this.querySelectorAll("nyro-composer-block").forEach((element) => {
            element.init();
        });
    }

    unselected() {
        if (this._addBlockBtn.classList.contains("active")) {
            this._addBlockBtn.classList.remove("active");
        }
    }

    _showAddBlock() {
        if (this.classList.contains("composerSelected")) {
            this.composer.sidePanel.selected = false;
            return;
        }

        this._addBlockBtn.classList.add("active");
        this.composer.sidePanel.selected = this;
    }

    _addBlock(type) {
        const block = this.composer.getNewBlock(type);
        this.appendChild(block);
        block.init();
        block.selectFirstContainer();

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
