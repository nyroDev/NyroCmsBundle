import NyroComposerInputFile from "./NyroComposerInputFile.js"; // Used in template
import NyroComposerInputVideoUrl from "./NyroComposerInputVideoUrl.js"; // Used in template

const template = document.createElement("template");
template.innerHTML = `
<style>
.noOptions,
:host(.hasSelection) .noSelection,
form {
    display: none;
}

:host(.hasNoOptions) .noOptions {
    display: contents;
}
:host(.hasOptions) form {
    display: block;
}
label {
    display: block
}
</style>
<div class="noSelection">Empty Selection</div>
<div class="noOptions">Nothing to edit</div>
<form action=""></form>
<slot></slot>
`;

const templateOption = document.createElement("template");
templateOption.innerHTML = `
<div class="option">
    <label></label>
    <span></span>
</div>
`;

const templateTypes = {
    text: document.createElement("template"),
    url: document.createElement("template"),
    number: document.createElement("template"),
    boolean: document.createElement("template"),
    select: document.createElement("template"),
    image: document.createElement("template"),
    file: document.createElement("template"),
    images: document.createElement("template"),
    videoUrl: document.createElement("template"),
};

const exportParts =
    "nyroComposerBtn, nyroComposerBtnDisabled, nyroComposerBtnUi, nyroComposerBtnUiSelect, nyroComposerBtnUiDrag, nyroComposerBtnUiDel";

templateTypes.text.innerHTML = `
    <input type="text" class="input" />
`;

templateTypes.url.innerHTML = `
    <input type="url" class="input" />
`;

templateTypes.number.innerHTML = `
    <input type="number" class="input" />
`;

templateTypes.boolean.innerHTML = `
    <input type="checkbox" value="1" class="input" />
`;

templateTypes.select.innerHTML = `
    <select class="input"></select>
`;

templateTypes.image.innerHTML = `
    <nyro-composer-input-file file-type="image" class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateTypes.images.innerHTML = `
    <nyro-composer-input-file file-type="image" multiple class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateTypes.file.innerHTML = `
    <nyro-composer-input-file class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateTypes.videoUrl.innerHTML = `
    <nyro-composer-input-video-url class="input" exportparts="${exportParts}"></nyro-composer-input-video-url>
`;

class NyroComposerSidePanel extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._form = this.shadowRoot.querySelector("form");

        this._form.addEventListener("change", (e) => {
            const input = e.target.closest(".input");
            if (!input) {
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();
            if (this._selected) {
                let value = input.value;
                if (input.type === "checkbox") {
                    value = input.checked;
                }
                this._selected.setValue(input.dataset.name, value);
            }
        });

        this._selected = false;
    }

    get composer() {
        return this.closest("nyro-composer");
    }

    get selected() {
        return this._selected;
    }

    set selected(selected) {
        if (this._selected) {
            this._selected.classList.remove("composerSelected");
        }
        this._selected = selected;
        if (selected) {
            selected.classList.add("composerSelected");
        }
        this._handleSelected();
    }

    init() {
        this.shadowRoot.querySelector(".noSelection").innerHTML = this.composer.trans("noSelection");
        this.shadowRoot.querySelector(".noOptions").innerHTML = this.composer.trans("noOptions");
    }

    _handleSelected() {
        this._form.innerHTML = "";
        this.classList.remove("hasSelection", "hasNoOptions", "hasOptions");

        if (!this._selected) {
            this.classList.remove("hasSelection");
            return;
        }

        this.classList.add("hasSelection");

        const panelOptions = this._selected.getPanelOptions();
        if (panelOptions.length === 0) {
            this.classList.add("hasNoOptions");
            return;
        }

        this.classList.add("hasOptions");

        panelOptions.forEach((panelOption) => {
            const ident = "composerPanel_" + panelOption.name,
                option = templateOption.content.cloneNode(true),
                inputOuter = templateTypes[panelOption.dataType].content.cloneNode(true),
                input = inputOuter.querySelector(".input");

            option.querySelector("label").innerHTML = panelOption.label;
            option.querySelector("label").setAttribute("for", ident);

            input.id = ident;
            input.dataset.name = panelOption.name;

            if (panelOption.dataType === "select" && panelOption.dataOptions) {
                const options = [];
                panelOption.dataOptions.forEach((dataOption) => {
                    options.push('<option value="' + dataOption.value + '">' + dataOption.name + "</option>");
                });
                input.innerHTML = options.join("");
            }

            option.querySelector("span").appendChild(inputOuter);

            this._form.appendChild(option);

            if (input.type === "checkbox") {
                input.checked = !!panelOption.value;
            } else {
                input.value = panelOption.value;
            }

            if (input.init) {
                input.init();
            }
        });
    }
}

window.customElements.define("nyro-composer-side-panel", NyroComposerSidePanel);

export default NyroComposerSidePanel;
