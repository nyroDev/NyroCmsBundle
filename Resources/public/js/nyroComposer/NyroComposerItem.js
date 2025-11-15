const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    position: relative;
    display: block;
    margin-top: var(--composer-ui-margin-top);
}
:host(.composerSelected) {
    z-index: 1;
}
:host(:hover) {
    z-index: 2;
}
nav {
    position: absolute;
    bottom: 100%;
    left: -1px;
    right: -1px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    opacity: 0;
    visibility: hidden;
}
nav div {
    display: flex;
}

:host(:not([parent-readonly]):hover) nav,
:host(.composerSelected:not([parent-readonly])) nav {
    opacity: 1;
    visibility: var(--composer-ui-visibility);
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
    font-family: var(--composer-font-family);
    color: var(--composer-elt-color);
    font-size: 12px;
    padding: 0 5px;
    width: auto;
}
nav .actions {
    flex-direction: row-reverse;
}
</style>
<nav></nav>
<slot></slot>
`;

const TINYMCE_TYPES = ["simpleText", "text"];

class NyroComposerItem extends HTMLElement {
    constructor() {
        super();
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

    get container() {
        return this.closest("nyro-composer-container");
    }

    get type() {
        return this.getAttribute("type");
    }

    set type(type) {
        this.setAttribute("type", type);
    }

    get cfg() {
        return this.composer.getItemCfg(this.type);
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
    }

    get value() {
        const value = {
            _type: this.type,
        };
        if (this.readonly) {
            value.readonly = true;
        }

        for (const [key, cfg] of Object.entries(this.cfg.editables)) {
            const element = this.querySelector(cfg.selector);
            if (!element) {
                throw key + " element not found with selector " + cfg.selector;
            }

            switch (cfg.type) {
                case "simpleText":
                case "text":
                    value[key] = element.innerHTML;
                    break;
                case "attr":
                    value[key] = element.getAttribute(key);
                    if (key === "src" && cfg.dataType === "image") {
                        value[key] = this.composer.unresizeUrl(value[key]);
                    }
                    break;
                case "dataAttr":
                    value[key] = element.dataset[key];
                    break;
                case "class":
                    const prefixClass = this.type + "_" + key;
                    if (cfg.dataType === "boolean") {
                        value[key] = element.classList.contains(prefixClass);
                    } else {
                        const foundClassName = Array.from(element.classList.values()).find((className) => {
                            return className.indexOf(prefixClass) === 0;
                        });
                        if (foundClassName) {
                            value[key] = foundClassName.replace(prefixClass + "_", "");
                        }
                    }
                    break;
                case "style":
                    value[key] = element.style[key];
                    break;
                case "dom":
                    if (cfg.dataType === "select" || cfg.dataType === "radio") {
                        value[key] = element.nodeName.toLowerCase();
                    } else if (cfg.dataType === "images") {
                        value[key] = [];
                        element.querySelectorAll("img").forEach((img) => {
                            value[key].push({
                                src: this.composer.unresizeUrl(img.getAttribute("src")),
                                alt: img.getAttribute("alt"),
                                width: img.getAttribute("width"),
                                height: img.getAttribute("height"),
                            });
                        });
                    } else {
                        throw "DOM dataType " + cfg.dataType + " not supported";
                    }
                    break;
            }

            if (cfg.dataType === "boolean" && value[key] === "false") {
                value[key] = false;
            }
        }

        return value;
    }

    getClone() {
        const newItem = this.composer.getNewItem(this.type);
    }

    _edit() {
        this.select();
    }

    _duplicate() {
        const newItem = this.composer.getNewItem(this.type);

        this.after(newItem);
        newItem.init();

        newItem.setValueFrom(this);

        newItem.select();
    }

    _delete() {
        this.composer.confirm(
            () => {
                const parent = this.parentElement;
                this.unselect();
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
                text: this.composer.trans("item." + this.type + ".deleteConfirm", false),
            }
        );
    }

    _moveTop() {
        if (this.container) {
            this.container.insertBefore(this, this.container.firstChild);
            this._dispatchChange();
        }
    }

    _moveBottom() {
        if (this.container) {
            this.container.appendChild(this);
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

    init() {
        if (this._inited) {
            return;
        }
        this._inited = true;
        if (this.readonly) {
            return;
        }
        this._configureComposer(true);

        this._nav.appendChild(this.composer.getElementNav(this.composer.getTemplate("item", this.type).title));
    }

    getPanelConfig() {
        const panelConfig = [],
            value = this.value,
            prefixTrans = "item." + this.type + ".";

        panelConfig.push({
            type: "icon",
            icon: this.cfg.icon,
        });
        panelConfig.push({
            type: "title",
            title: this.composer.getTemplate("item", this.type).title,
        });

        if (this.readonly) {
            panelConfig.push({
                type: "text",
                text: this.composer.trans("item.readonly"),
            });

            return panelConfig;
        }

        const intro = this.composer.trans("item." + this.type + ".intro", false);
        if (intro) {
            panelConfig.push({
                type: "text",
                text: intro,
            });
        }

        for (const [key, cfg] of Object.entries(this.cfg.editables)) {
            if (cfg.auto || cfg.type === "text" || cfg.type === "simpleText") {
                continue;
            }

            const panelCfg = {
                type: "input",
                name: key,
                label: this.composer.trans(prefixTrans + key, key),
                dataType: cfg.dataType,
                value: value[key],
            };

            if ((cfg.dataType === "select" || cfg.dataType === "radio") && cfg.dataOptions) {
                panelCfg.dataOptions = [];
                cfg.dataOptions.forEach((dataOption) => {
                    panelCfg.dataOptions.push({
                        value: dataOption,
                        name: this.composer.trans(prefixTrans + key + "_options." + dataOption),
                    });
                });
            }

            panelConfig.push(panelCfg);
        }

        return panelConfig;
    }

    setValueFrom(item) {
        for (const [key, value] of Object.entries(item.value)) {
            if (key === "_type") {
                continue;
            }
            if (key === "readonly") {
                this.readonly = value;
                continue;
            }
            this.setValue(key, value, true);
        }
    }

    setValue(name, value, direct) {
        const editableCfg = this.cfg.editables[name];
        if (!editableCfg) {
            return;
        }

        const element = this.querySelector(editableCfg.selector);
        if (!element) {
            throw key + " element not found with selector " + editableCfg.selector;
        }

        if (!direct && (editableCfg.dataType === "file" || editableCfg.dataType === "image")) {
            // value is a media JSON here.
            if (editableCfg.dataType === "image") {
                // We should update width and height, and replace value by URL only
                if (value.w) {
                    this.setValue("width", value.w);
                }
                if (value.h) {
                    this.setValue("height", value.h);
                }
                if (this._contentPlaceholder) {
                    this._contentPlaceholder.remove();
                    delete this._contentPlaceholder;
                }
            }
            value = value.url;
            if (editableCfg.dataType === "image") {
                const imageAttrs = this._computeImageAttrs(value);
                Object.keys(imageAttrs).forEach((attr) => {
                    if (attr === "src") {
                        value = imageAttrs.src;
                    } else {
                        element.setAttribute(attr, imageAttrs[attr]);
                    }
                });
            }
        } else if (!direct && (editableCfg.dataType === "videoUrl" || editableCfg.dataType === "iframeUrl")) {
            this.setValue("src", value.src);
            if (this.cfg.editables.autoplay) {
                this.setValue("autoplay", value.autoplay);
            }
            if (value.data && value.data.width && value.data.height) {
                this.setValue("aspectRatio", value.data.width + "/" + value.data.height);
            }
            if (this._contentPlaceholder) {
                this._contentPlaceholder.remove();
                delete this._contentPlaceholder;
            }
            value = value.url;
        }

        switch (editableCfg.type) {
            case "simpleText":
            case "text":
                element.innerHTML = value;
                break;
            case "attr":
                element.setAttribute(name, value);
                break;
            case "dataAttr":
                element.dataset[name] = value;
                break;
            case "class":
                const prefixClass = this.type + "_" + name;
                if (editableCfg.dataType === "boolean") {
                    element.classList.toggle(prefixClass, value);
                } else {
                    editableCfg.dataOptions.forEach((dataOption) => {
                        element.classList.toggle(prefixClass + "_" + dataOption, value === dataOption);
                    });
                }
                break;
            case "style":
                element.style[name] = value;
                break;
            case "dom":
                if (editableCfg.dataType === "select" || editableCfg.dataType === "radio") {
                    if (element.nodeName.toLowerCase() !== value) {
                        const newElement = document.createElement(value);
                        newElement.innerHTML = element.innerHTML;
                        element.after(newElement);
                        element.remove();

                        this._configureComposer();
                    }
                } else if (editableCfg.dataType === "images") {
                    const exsitingImgs = Array.from(element.querySelectorAll("img"));
                    value.forEach((imgValue, idx) => {
                        if (!exsitingImgs[idx]) {
                            const img = new Image();
                            img.loading = "lazy";
                            if (idx === 0) {
                                element.prepend(img);
                            } else {
                                exsitingImgs[exsitingImgs.length - 1].after(img);
                            }
                            exsitingImgs.push(img);
                        }

                        const imageAttrs = this._computeImageAttrs(imgValue.src);
                        Object.keys(imageAttrs).forEach((attr) => {
                            exsitingImgs[idx].setAttribute(attr, imageAttrs[attr]);
                        });

                        exsitingImgs[idx].alt = imgValue.alt;
                        exsitingImgs[idx].width = imgValue.width;
                        exsitingImgs[idx].height = imgValue.height;
                    });
                    for (let idx = value.length; idx < exsitingImgs.length; idx++) {
                        exsitingImgs[idx].remove();
                    }

                    if (this._contentPlaceholder) {
                        this._contentPlaceholder.remove();
                        delete this._contentPlaceholder;
                    }
                }
                break;
        }
        this._dispatchChange();
    }

    _computeImageAttrs(path) {
        const attrs = {
            src: "",
            srcset: "",
            sizes: "",
        };

        const widthContainer = this.container.widthContainer;
        if (widthContainer) {
            attrs.src = this.composer.getResizeUrl(path, widthContainer.dims);
            attrs.sizes = widthContainer.sizes;
            if (widthContainer.srcset) {
                const srcset = [];
                widthContainer.srcset.forEach((srcsetCfg) => {
                    srcset.push(this.composer.getResizeUrl(path, srcsetCfg.dims) + " " + srcsetCfg.width);
                });
                attrs.srcset = srcset.join(", ");
            }
        } else {
            attrs.src = this.composer.getResizeUrl(path, "1200x1200");
        }

        return attrs;
    }

    select() {
        this.dispatchEvent(
            new Event("composerSelect", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    unselect() {
        this.dispatchEvent(
            new Event("composerUnselect", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    _configureComposer(isInit) {
        let listenInput = false;
        for (const [key, cfg] of Object.entries(this.cfg.editables)) {
            const element = this.querySelector(cfg.selector);
            if (!element) {
                throw key + " element not found with selector " + cfg.selector;
            }

            let needContentPlaceholder = false;
            if (cfg.dataType === "image" && !element.getAttribute("src")) {
                element.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
                needContentPlaceholder = element;
            } else if (cfg.dataType === "image" && element.getAttribute("src").startsWith("data:")) {
                needContentPlaceholder = element;
            } else if (cfg.dataType === "images" && !element.querySelector("img")) {
                needContentPlaceholder = element;
            } else if ((cfg.dataType === "videoUrl" || cfg.dataType === "iframeUrl") && !element.getAttribute("src")) {
                needContentPlaceholder = element;
            }

            if (needContentPlaceholder) {
                this._contentPlaceholder = document.createElement("div");
                this._contentPlaceholder.classList.add("nyroComposerContentPlaceholder");
                this._contentPlaceholder.innerHTML = this.composer.getIcon("item_" + this.type);
                needContentPlaceholder.before(this._contentPlaceholder);
            }

            if (TINYMCE_TYPES.includes(cfg.type) && !element.contentEditable != "true") {
                const tinymceOptions = this.composer.getTinymceOptions(cfg.type === "simpleText");

                if (cfg.type === "text") {
                    const cont = document.createElement("div");
                    cont.classList.add("nyroComposerTinymce");
                    this.insertBefore(cont, this.firstChild);

                    tinymceOptions.fixed_toolbar_container_target = cont;
                }

                const jqElement = jQuery(element);
                tinymceOptions.setup = (ed) => {
                    ed.on("change", () => {
                        this._dispatchChange();
                    });
                    ed.on("blur", () => {
                        jqElement.tinymce().execCommand("mceCleanup");
                        jqElement.tinymce().save();
                        this._dispatchChange();
                    });
                };

                jqElement.myTinymce(tinymceOptions, this.composer.tinymceUrl);
                listenInput = true;
            }
        }

        if (isInit && listenInput) {
            this.addEventListener("input", () => {
                this._dispatchChange();
            });
        }
    }

    _dispatchChange() {
        this.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }
}

window.customElements.define("nyro-composer-item", NyroComposerItem);

export default NyroComposerItem;
