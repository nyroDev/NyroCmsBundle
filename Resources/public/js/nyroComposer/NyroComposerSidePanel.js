import NyroComposerInputFile from "./NyroComposerInputFile.js"; // Used in template
import NyroComposerInputVideoUrl from "./NyroComposerInputVideoUrl.js"; // Used in template

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    font-family: var(--composer-font-family);
    padding-top: var(--composer-panel-space);
}
.toggle {
    position: absolute;
    top: 60px;
    left: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 26px;
    height: 66px;
    background-color: var(--composer-color-panel-side-bg);
    color: var(--composer-color-secondary);
    text-decoration: none;
    border: 1px solid var(--composer-color-secondary);
    border-left: none;
    box-shadow: var(--composer-shadow-panel);
    transition: color var(--composer-transition-time);
}
.toggle:before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    right: 100%;
    width: 10px;
    pointer-events: none;
    background-color: var(--composer-color-panel-side-bg);
}

.toggle:hover {
    color: var(--composer-color-panel-side-text);
}

.toggle .icon {
    margin-left: -3px;
    width: 13px;
    height: 100%;
    transform: rotate(0);
    transition: transform var(--composer-transition-time);
}
:host(.sideToggled) .toggle .icon {
    transform: rotate(180deg);
}

.noConfig,
:host(.hasSelection) .noSelection,
form {
    display: none;
}

:host(.hasNoConfig) .noConfig {
    display: contents;
}
:host(.hasConfig) form {
    display: block;
}

.iconCont,
.title,
.text,
.buttons {
    text-align: center;
    margin: var(--composer-panel-space);
    color: var(--composer-color);
}

.iconCont .icon,
.buttons .icon {
    width: 38px;
    height: 38px;
}

.title {
    font-weight: var(--composer-font-bold-weight);
    font-size: 20px;
}

.text,
.buttons.conditional span {
    font-size: 11px;
    text-align: left;
}

.text.help,
.buttons.conditional span {
    color: var(--composer-color-secondary);
    font-style: italic;
}

.buttons {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin: var(--composer-panel-space) 10px;
}

.buttons .button {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 110px;
    height: 88px;
    text-decoration: none;
    font-size: 12px;
    box-sizing: border-box;
    border: 1px solid var(--composer-color-secondary);
    --border-hover-color: var(--composer-color-secondary);
    color: var(--composer-color);
    margin-bottom: 10px;
    transition: border-color var(--composer-transition-time), background-color var(--composer-transition-time);
}
.buttons .icon {
    margin-bottom: 5px;
}

.buttons .button.type_block {
    --border-hover-color: var(--composer-color-block);
}

.buttons .button.type_item {
    --border-hover-color: var(--composer-color-item);
}

.buttons .button:hover,
.buttons .button.active {
    border-width: 2px;
    border-color: var(--border-hover-color);
    background-color: var(--composer-color-light-hover);
}

.buttons.conditional .button {
    display: none;
}

.buttons.conditional span {
    display: block;
    width: 100%;
}

.buttons.conditional span a {
    color: var(--composer-color-secondary);
    font-weight: var(--composer-font-bold-weight);
}
.buttons.conditional span a:hover {
    text-decoration: none;
}

.option {
    margin: var(--composer-panel-space);
    color: var(--composer-color);
}

label {
    display: block;
    font-size: 12px;
    margin-bottom: 1em;
}

.inputRadio {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
}
.inputRadio label {
    width: 33%;
    margin-bottom: 0.5em;
}
.inputRadio label:has(.input:checked) {
    color: var(--composer-color-highlight);
}

.inputText {
    width: 100%;
    box-sizing: border-box;
    font-family: var(--composer-font-family);
    font-size: 12px;
    padding: 2px 5px;
    border-radius: 5px;
    line-height: 26px;
    border: 1px solid var(--composer-color-secondary);
    outline: none;
    transition: border-color var(--composer-transition-time);
}
.inputText:focus {
    border-color: var(--composer-color);
}

</style>
<a href="#" class="toggle"></a>
<div class="noSelection">Empty Selection</div>
<div class="noConfig">Nothing to edit</div>
<form action=""></form>
<slot></slot>
`;

const templateIcon = document.createElement("template");
templateIcon.innerHTML = `
<div class="iconCont"></div>
`;

const templateTitle = document.createElement("template");
templateTitle.innerHTML = `
<div class="title"></div>
`;

const templateText = document.createElement("template");
templateText.innerHTML = `
<div class="text"></div>
`;

const templateButtons = document.createElement("template");
templateButtons.innerHTML = `
<div class="buttons"></div>
`;

const templateInput = document.createElement("template");
templateInput.innerHTML = `
<div class="option">
    <label></label>
    <span></span>
</div>
`;

const templateInputTypes = {
    text: document.createElement("template"),
    url: document.createElement("template"),
    number: document.createElement("template"),
    boolean: document.createElement("template"),
    select: document.createElement("template"),
    radio: document.createElement("template"),
    radioOption: document.createElement("template"),
    image: document.createElement("template"),
    file: document.createElement("template"),
    images: document.createElement("template"),
    videoUrl: document.createElement("template"),
};

const exportParts =
    "nyroComposerBtn, nyroComposerBtnNav, nyroComposerBtnDisabled, nyroComposerBtnUi, nyroComposerBtnUiSelect, nyroComposerBtnUiDrag, nyroComposerBtnUiDel";

templateInputTypes.text.innerHTML = `
    <input type="text" class="input inputText" />
`;

templateInputTypes.url.innerHTML = `
    <input type="url" class="input inputText" />
`;

templateInputTypes.number.innerHTML = `
    <input type="number" class="input inputText" />
`;

templateInputTypes.boolean.innerHTML = `
    <input type="checkbox" value="1" class="input" />
`;

templateInputTypes.select.innerHTML = `
    <select class="input"></select>
`;

templateInputTypes.radio.innerHTML = `
    <div class="inputRadio"></div>
`;

templateInputTypes.radioOption.innerHTML = `
<label>
    <input type="radio" class="input" />
    <span></span>
<label>
`;

templateInputTypes.image.innerHTML = `
    <nyro-composer-input-file file-type="image" class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateInputTypes.images.innerHTML = `
    <nyro-composer-input-file file-type="image" multiple class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateInputTypes.file.innerHTML = `
    <nyro-composer-input-file class="input" exportparts="${exportParts}"></nyro-composer-input-file>
`;

templateInputTypes.videoUrl.innerHTML = `
    <nyro-composer-input-video-url class="input" exportparts="${exportParts}"></nyro-composer-input-video-url>
`;

class NyroComposerSidePanel extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._toggle = this.shadowRoot.querySelector(".toggle");
        this._toggle.addEventListener("click", (e) => {
            e.preventDefault();
            this.toggle();
        });

        this._form = this.shadowRoot.querySelector("form");

        this._form.addEventListener("change", (e) => {
            const input = e.target.closest(".input");
            if (!input) {
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (input.type === "radio" && !input.checked) {
                return;
            }

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
            if (this._selected.unselected) {
                this._selected.unselected();
            }
        }
        this._selected = selected;
        if (selected) {
            this.toggle(false);
            selected.classList.add("composerSelected");
        }
        this._handleSelected();
    }

    init() {
        this.shadowRoot.querySelector(".noSelection").innerHTML = this.composer.trans("noSelection");
        this.shadowRoot.querySelector(".noConfig").innerHTML = this.composer.trans("noConfig");
        this._toggle.innerHTML = this.composer.getIcon("back");
    }

    toggle(force) {
        this.classList.toggle("sideToggled", force);
        this.composer.classList.toggle("sideToggled", force);
    }

    _handleSelected() {
        this._form.innerHTML = "";
        this.classList.remove("hasSelection", "hasNoConfig", "hasConfig");

        if (!this._selected) {
            this.classList.remove("hasSelection");
            return;
        }

        this.classList.add("hasSelection");

        const panelConfig = this._selected.getPanelConfig();
        if (panelConfig.length === 0) {
            this.classList.add("hasNoConfig");
            return;
        }

        this.classList.add("hasConfig");

        panelConfig.forEach((panelCfg) => {
            switch (panelCfg.type) {
                case "icon":
                    this._handlePanelIcon(panelCfg);
                    break;
                case "title":
                    this._handlePanelTitle(panelCfg);
                    break;
                case "text":
                    this._handlePanelText(panelCfg);
                    break;
                case "buttons":
                    this._handlePanelButtons(panelCfg);
                    break;
                case "input":
                    this._handlePanelInput(panelCfg);
                    break;
            }
        });
    }

    _handlePanelIcon(panelCfg) {
        const iconCont = templateIcon.content.cloneNode(true),
            icon = this.composer.getIcon(panelCfg.icon);

        iconCont.querySelector("div").innerHTML = icon;

        this._form.appendChild(iconCont);
    }

    _handlePanelTitle(panelCfg) {
        const titleCont = templateTitle.content.cloneNode(true);

        titleCont.querySelector("div").innerHTML = panelCfg.title;

        this._form.appendChild(titleCont);
    }

    _handlePanelText(panelCfg) {
        const textCont = templateText.content.cloneNode(true);

        textCont.querySelector("div").innerHTML = panelCfg.text;
        if (panelCfg.help) {
            textCont.querySelector("div").classList.add("help");
        }

        this._form.appendChild(textCont);
    }

    _handlePanelButtons(panelCfg) {
        const buttonsCont = templateButtons.content.cloneNode(true),
            div = buttonsCont.querySelector("div"),
            buttons = panelCfg.buttons ? panelCfg.buttons : [];

        if (panelCfg.conditional) {
            div.classList.add("conditional");
            const condtionalText = document.createElement("span");
            condtionalText.innerHTML = panelCfg.conditional;
            div.appendChild(condtionalText);

            condtionalText.addEventListener("click", (e) => {
                const a = e.target.closest("a");
                if (!a) {
                    return;
                }
                e.preventDefault();
                condtionalText.remove();
                div.classList.remove("conditional");
            });
        }

        if (panelCfg.templateType) {
            this.composer.getTemplates(panelCfg.templateType).forEach((template) => {
                const cfg = JSON.parse(template.dataset.cfg);
                if (cfg && !cfg.addable) {
                    return;
                }
                buttons.push({
                    id: template.dataset.id,
                    type: panelCfg.templateType,
                    icon: cfg.icon,
                    title: template.title,
                    active: panelCfg.active === template.dataset.id,
                });
            });
        }

        buttons.forEach((buttonCfg) => {
            const button = document.createElement("a");
            button.href = "#";
            button.className = "button";
            button.innerHTML = this.composer.getIcon(buttonCfg.icon) + buttonCfg.title;
            button.dataset.id = buttonCfg.id;

            if (buttonCfg.type) {
                button.classList.add("type_" + buttonCfg.type);
            }

            if (buttonCfg.active) {
                button.classList.add("active");
            }

            div.appendChild(button);
        });

        div.addEventListener("click", (e) => {
            const button = e.target.closest(".button");
            if (!button || !this._selected) {
                return;
            }
            e.preventDefault();

            this._selected.setValue(panelCfg.name, button.dataset.id);
        });

        this._form.appendChild(buttonsCont);
    }

    _handlePanelInput(panelCfg) {
        const ident = "composerPanel_" + panelCfg.name,
            option = templateInput.content.cloneNode(true),
            inputOuter = templateInputTypes[panelCfg.dataType].content.cloneNode(true),
            input = inputOuter.querySelector(".input");

        option.querySelector("label").innerHTML = panelCfg.label;
        option.querySelector("label").setAttribute("for", ident);

        if (input) {
            input.id = ident;
            input.dataset.name = panelCfg.name;
        }

        if (panelCfg.dataType === "select" && panelCfg.dataOptions) {
            const options = [];
            panelCfg.dataOptions.forEach((dataOption) => {
                options.push('<option value="' + dataOption.value + '">' + dataOption.name + "</option>");
            });
            input.innerHTML = options.join("");
        } else if (panelCfg.dataType === "radio" && panelCfg.dataOptions) {
            const container = inputOuter.querySelector(".inputRadio");
            panelCfg.dataOptions.forEach((dataOption) => {
                const optionHtml = templateInputTypes.radioOption.content.cloneNode(true),
                    inputRadio = optionHtml.querySelector(".input");

                inputRadio.name = ident;
                inputRadio.value = dataOption.value;
                inputRadio.dataset.name = panelCfg.name;

                if (panelCfg.value === dataOption.value) {
                    inputRadio.checked = true;
                }

                inputRadio.id = ident + "_" + dataOption.value;
                optionHtml.querySelector("span").innerHTML = dataOption.name;

                container.appendChild(optionHtml);
            });
        }

        option.querySelector("span").appendChild(inputOuter);

        this._form.appendChild(option);

        if (input) {
            if (input.type === "checkbox") {
                input.checked = !!panelCfg.value;
            } else {
                input.value = panelCfg.value;
            }

            if (input.init) {
                input.init();
            }
        }
    }
}

window.customElements.define("nyro-composer-side-panel", NyroComposerSidePanel);

export default NyroComposerSidePanel;
