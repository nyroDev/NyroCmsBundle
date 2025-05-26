import NyroComposerDialog from "./NyroComposerDialog.js";

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: block;
}
</style>
<slot></slot>
`;

const iconsCache = new Map();
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

        document.body.addEventListener("click", (e) => {
            if (e.target.closest("nyro-composer-block, nyro-composer-side-panel, nyro-composer-workspace")) {
                return;
            }
            this.sidePanel.selected = false;
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

    getTemplates(type) {
        return this.querySelectorAll("template." + type);
    }

    getTemplate(type, id) {
        const template = this.querySelector("template." + type + '[data-id="' + CSS.escape(id) + '"]');
        if (!template) {
            throw type + " " + id + " not found.";
        }

        return template;
    }

    getNewBlock(id) {
        return this.getTemplate("block", id).content.cloneNode(true).querySelector("nyro-composer-block");
    }

    getNewItem(id) {
        return this.getTemplate("item", id).content.cloneNode(true).querySelector("nyro-composer-item");
    }

    getItemCfg(id) {
        if (this._itemCfgs[id]) {
            return this._itemCfgs[id];
        }

        const template = this.getTemplate("item", id);

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

    confirm(clb, cancelClb, options) {
        const dialog = new NyroComposerDialog();

        dialog.setContent(this.getTemplate("ui", "confirm").content.cloneNode(true));

        if (options && options.text) {
            dialog.in.querySelector("p").innerHTML = options.text;
        }

        if (options && options.cancel) {
            dialog.in.querySelector(".cancel").innerHTML = options.cancel;
        }

        if (options && options.confirm) {
            dialog.in.querySelector(".confirm").innerHTML = options.confirm;
        }

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

    getElementNav(title) {
        const innerNav = this.getTemplate("ui", "elementNav").content.cloneNode(true);
        innerNav.querySelector(".title").innerHTML = title;

        return innerNav;
    }

    fillNav(nav, btns) {
        btns.forEach((btn) => {
            nav.appendChild(this.getTemplate("ui", "btn_" + btn).content.cloneNode(true));
        });
    }

    trans(id, def) {
        if (def === undefined) {
            def = id;
        }
        return this._trans[id] ?? def;
    }

    getIcon(name) {
        if (iconsCache.has(name)) {
            return iconsCache.get(name);
        }

        const icon = this.getTemplate("ui", "icon").innerHTML.replaceAll("IDENT", name);

        iconsCache.set(name, icon);

        return icon;
    }
}

window.customElements.define("nyro-composer", NyroComposer);

export default NyroComposer;
