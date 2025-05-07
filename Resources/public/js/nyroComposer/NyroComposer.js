import NyroComposerDialog from "./NyroComposerDialog.js";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
    padding:
        calc(var(--composer-panel-top-height) + var(--composer-panel-space))
        var(--composer-panel-space)
        var(--composer-panel-space)
        calc(var(--composer-panel-side-width) + var(--composer-panel-space));
}
</style>
<slot></slot>
`;

class NyroComposer extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._navsHtml = {};
        this._itemCfgs = {
            handler: {
                editables: [],
            },
        };
        this._tinymceOptions = {};

        this.addEventListener("change", () => {
            this.topPanel.changed = true;
        });

        this.addEventListener("composerSelect", (e) => {
            this.sidePanel.selected = e.target;
        });

        this.addEventListener("composerUnselect", (e) => {
            if (this.sidePanel.selected === e.target) {
                this.sidePanel.selected = false;
            }
        });
    }

    connectedCallback() {
        this._trans = JSON.parse(this.querySelector("#uiTranslations").value);
        jQuery(() => {
            this.workspace.init();
            this.topPanel.init();
            this.sidePanel.init();
        });
    }

    get form() {
        return this.querySelector("form");
    }

    get topPanel() {
        return this.querySelector("nyro-composer-top-panel");
    }

    get sidePanel() {
        return this.querySelector("nyro-composer-side-panel");
    }

    get workspace() {
        return this.querySelector("nyro-composer-workspace");
    }

    get noStructureChange() {
        // @todo not implemented
        return this.hasAttribute("no-structure-change");
    }

    get noMediaChange() {
        // @todo not implemented
        return this.hasAttribute("no-media-change");
    }

    _getAddNavHtml(type) {
        if (this._navsHtml[type]) {
            return this._navsHtml[type];
        }

        const html = [];

        this.getTemplates(type).forEach((template) => {
            const cfg = JSON.parse(template.dataset.cfg);
            if (cfg && !cfg.addable) {
                return;
            }
            html.push(
                '<a href="#" class="add_' +
                    type +
                    '" data-type="' +
                    template.dataset.id +
                    '" part="nyroComposerBtn nyroComposerBtnUi">' +
                    template.title +
                    "</a>"
            );
        });

        this._navsHtml[type] = html.join(" ");
        return this._navsHtml[type];
    }

    getTemplates(type) {
        return this.querySelectorAll("template." + type);
    }

    getAddBlocksNavHtml() {
        return this._getAddNavHtml("block");
    }

    getAddItemsNavHtml() {
        return this._getAddNavHtml("item");
    }

    _getTemplate(type, id) {
        const template = this.querySelector("template." + type + '[data-id="' + CSS.escape(id) + '"]');
        if (!template) {
            throw type + " " + id + " not found.";
        }

        return template;
    }

    getNewBlock(id) {
        return this._getTemplate("block", id).content.cloneNode(true).querySelector("nyro-composer-block");
    }

    getNewItem(id) {
        return this._getTemplate("item", id).content.cloneNode(true).querySelector("nyro-composer-item");
    }

    getItemCfg(id) {
        if (this._itemCfgs[id]) {
            return this._itemCfgs[id];
        }

        const template = this._getTemplate("item", id);

        this._itemCfgs[id] = JSON.parse(template.dataset.cfg);

        return this._itemCfgs[id];
    }

    get tinymceUrl() {
        return this.dataset.tinymceurl;
    }

    getTinymceOptions(simple) {
        const key = simple ? "simple" : "regular";
        if (this._tinymceOptions[key]) {
            return this._tinymceOptions[key];
        }

        this._tinymceOptions[key] = jQuery(this).myTinymceDataSearch(simple ? "tinymcesimple_" : "");

        return structuredClone(this._tinymceOptions[key]);
    }

    alert(config) {
        const dialog = new NyroComposerDialog();

        const div = document.createElement("div");
        if (config.title) {
            div.innerHTML = "<h1>" + config.title + "</h1>";
        }
        if (config.content) {
            div.innerHTML += "<p>" + config.content + "</p>";
        }
        if (config.html) {
            div.innerHTML += config.html;
        }
        dialog.setContent(div);

        dialog.classList.add("nyroComposerDialogConfirm");
        this.appendChild(dialog);
        dialog.open();
    }

    confirm(clb, cancelClb) {
        const dialog = new NyroComposerDialog();

        dialog.setContent(this._getTemplate("ui", "confirm").content.cloneNode(true));

        dialog.in.addEventListener("click", (e) => {
            const confirm = e.target.closest(".confirm");
            if (!confirm) {
                return;
            }
            e.preventDefault();
            cancelClb = false;
            clb();
            dialog.close();
        });
        dialog.addEventListener("close", () => {
            if (cancelClb) {
                cancelClb();
            }
        });

        dialog.classList.add("nyroComposerDialogConfirm");
        this.appendChild(dialog);
        dialog.open();
    }

    selectMedia(type, clb) {
        const dialog = new NyroComposerDialog();
        const iframe = document.createElement("iframe");
        iframe.name = "";
        iframe.src = this.dataset.tinymce_external_filemanager_path.replace("_TYPE_", type);
        dialog.setContent(iframe);

        const messageListener = (e) => {
            if (e.source === iframe.contentWindow && e.data && e.data.mceAction === "customAction") {
                clb(e.data.data);
                dialog.close();
            }
        };
        window.addEventListener("message", messageListener);
        dialog.addEventListener("close", () => {
            window.removeEventListener("message", messageListener);
        });

        dialog.classList.add("nyroComposerDialogIframe");
        this.appendChild(dialog);
        dialog.open();
    }

    fillNav(nav, btns) {
        btns.forEach((btn) => {
            nav.appendChild(this._getTemplate("ui", "btn_" + btn).content.cloneNode(true));
        });
    }

    trans(id, def) {
        if (!def) {
            def = id;
        }
        return this._trans[id] ?? def;
    }
}

window.customElements.define("nyro-composer", NyroComposer);

export default NyroComposer;
