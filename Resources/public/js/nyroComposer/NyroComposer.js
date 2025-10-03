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

const fetchOptions = (options = {}) => {
    return Object.assign(
        {
            method: "GET",
            mode: "cors",
            credentials: "same-origin",
            cache: "no-cache",
            redirect: "follow",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-JS-FETCH": 1,
            },
        },
        options
    );
};

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
            if (e.target.closest("nyro-composer-block, nyro-composer-side-panel, nyro-composer-workspace, nyro-composer-dialog")) {
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

    get selectedTemplate() {
        if (this._selectedTemplate !== undefined) {
            return this._selectedTemplate;
        }

        this._selectedTemplate = false;

        const templateInputValue = this.templateInput.value;
        if (templateInputValue) {
            const tmp = JSON.parse(templateInputValue);
            this._selectedTemplate = tmp["_template"] || false;
        }

        return this._selectedTemplate;
    }

    get templateInput() {
        if (this._templateInput !== undefined) {
            return this._templateInput;
        }

        this._templateInput = this.workspace.querySelector("input[data-template]");

        if (!this._templateInput) {
            this._templateInput = document.createElement("input");
            this._templateInput.type = "hidden";
            this._templateInput.name = "content[]";
            this._templateInput.dataset.template = "1";
            this.workspace.prepend(this._templateInput);
        }

        return this._templateInput;
    }

    get availableTemplates() {
        if (this._availableTemplates !== undefined) {
            return this._availableTemplates;
        }

        this._availableTemplates = false;

        if (!this.dataset.availableTemplates) {
            return this._availableTemplates;
        }

        const avlTemplates = JSON.parse(this.dataset.availableTemplates);
        if (!avlTemplates || avlTemplates.length === 0) {
            return this._availableTemplates;
        }

        this._availableTemplates = {
            templates: {},
            categories: {},
        };

        avlTemplates.forEach((template) => {
            this._availableTemplates.templates[template.id] = template;

            const tplKey = template.custom ? "custom" : "standard";

            const category = template.category;
            if (!category) {
                if (!this._availableTemplates.noCategoryTemplates) {
                    this._availableTemplates.noCategoryTemplates = {
                        standard: [],
                        custom: [],
                    };
                }
                this._availableTemplates.noCategoryTemplates[tplKey].push(template.id);
                return;
            }

            if (!this._availableTemplates.categories[category.id]) {
                this._availableTemplates.categories[category.id] = category;
                this._availableTemplates.categories[category.id].templates = {
                    standard: [],
                    custom: [],
                };
            }

            this._availableTemplates.categories[category.id].templates[tplKey].push(template.id);
        });

        return this._availableTemplates;
    }

    chooseTemplate(id) {
        if (!id || this._choosingTemplate) {
            return;
        }

        this._choosingTemplate = true;

        const formData = new FormData(this.form);
        formData.append("template", id);

        fetch(
            document.location.href,
            fetchOptions({
                method: "POST",
                body: formData,
            })
        )
            .then((response) => {
                return response.text();
            })
            .then((response) => {
                this.workspace.innerHTML = response;
                this.workspace.initBlocks();
                this._choosingTemplate = false;
            });
    }

    convertToTemplate(url) {
        const dialog = document.createElement("nyro-cms-dialog");

        dialog.appendChild(this.getTemplate("ui", "closeTpl").content.cloneNode(true));

        dialog.addEventListener("nyroCmsDialogFetched", (e) => {});

        document.body.appendChild(dialog);
        dialog.open();
        dialog.loadUrl(url);
    }

    getPanelConfig(init) {
        const panelConfig = [];

        panelConfig.push({
            type: "icon",
            icon: "page",
        });

        panelConfig.push({
            type: "title",
            title: this.trans("page.title"),
        });

        panelConfig.push({
            type: "text",
            text: this.trans("page.intro"),
        });

        panelConfig.push({
            type: "template",
            conditional: this.trans("page.chooseTemplate"),
        });

        return panelConfig;
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

    unresizeUrl(path) {
        if (!this.dataset.resizeUrl) {
            return path;
        }

        const tmpPath = path.split("/resize/");
        if (tmpPath.length === 2) {
            const tmpPath2 = tmpPath[1].split("/");
            tmpPath2.shift(); // Remove 'dims'
            return "/" + tmpPath2.join("/");
        }

        return path;
    }

    getResizeUrl(path, dims) {
        if (!this.dataset.resizeUrl) {
            return path;
        }

        path = this.unresizeUrl(path);
        if (path.startsWith("/")) {
            path = path.substring(1);
        }

        return this.dataset.resizeUrl.replace("--DIMS--", dims).replace("--PATH--", path);
    }

    getIcon(name) {
        if (iconsCache.has(name)) {
            return iconsCache.get(name);
        }

        const icon = this.getTemplate("ui", "icon").innerHTML.replaceAll("IDENT", name);

        iconsCache.set(name, icon);

        return icon;
    }

    getIconAdmin(name, iconFallback) {
        if (!name) {
            return this.getIcon(iconFallback);
        }
        const cacheKey = "admin_" + name;
        if (iconsCache.has(cacheKey)) {
            return iconsCache.get(cacheKey);
        }

        const icon = this.getTemplate("ui", "iconAdmin").innerHTML.replaceAll("IDENT", name);

        iconsCache.set(cacheKey, icon);

        return icon;
    }
}

window.customElements.define("nyro-composer", NyroComposer);

export default NyroComposer;
