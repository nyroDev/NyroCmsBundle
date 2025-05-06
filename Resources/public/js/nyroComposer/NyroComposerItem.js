const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    position: relative;
    display: block;
    margin-top: 25px;
}
nav {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    text-align: center;
    opacity: 0;
    visibility: hidden;
}
:host(:hover) nav {
    opacity: 1;
    visibility: visible;
}
</style>
<nav></nav>
<slot></slot>
`;

class NyroComposerItem extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._nav = this.shadowRoot.querySelector("nav");
        this._nav.addEventListener("click", (e) => {
            const del = e.target.closest(".deleteHandle");
            if (!del) {
                return;
            }

            e.preventDefault();
            this.composer.confirm(() => {
                const parent = this.parentElement;
                this.unselect();
                this.remove();
                parent.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                        cancelable: true,
                    })
                );
            });
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
                case "dom":
                    if (cfg.dataType === "images") {
                        value[key] = [];
                        element.querySelectorAll("img").forEach((img) => {
                            value[key].push({
                                src: img.getAttribute("src"),
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

    init() {
        if (this._inited) {
            return;
        }
        this._inited = true;
        if (this.readonly) {
            return;
        }
        this._configureComposer();
        this.composer.fillNav(this._nav, ["select", "drag", "delete"]);
    }

    getPanelOptions() {
        const panelOptions = [],
            value = this.value,
            prefixTrans = "item." + this.type + ".";

        for (const [key, cfg] of Object.entries(this.cfg.editables)) {
            if (cfg.auto || cfg.type === "text" || cfg.type === "simpleText") {
                continue;
            }

            const panelOption = {
                name: key,
                label: this.composer.trans(prefixTrans + key, key),
                dataType: cfg.dataType,
                value: value[key],
            };

            if (cfg.dataType === "select" && cfg.dataOptions) {
                panelOption.dataOptions = [];
                cfg.dataOptions.forEach((dataOption) => {
                    panelOption.dataOptions.push({
                        value: dataOption,
                        name: this.composer.trans(prefixTrans + key + "_options." + dataOption),
                    });
                });
            }

            panelOptions.push(panelOption);
        }

        return panelOptions;
    }

    setValue(name, value) {
        const editableCfg = this.cfg.editables[name];
        if (!editableCfg) {
            return;
        }

        const element = this.querySelector(editableCfg.selector);
        if (!element) {
            throw key + " element not found with selector " + editableCfg.selector;
        }

        if (editableCfg.dataType === "file" || editableCfg.dataType === "image") {
            // value is a media JSON here.
            if (editableCfg.dataType === "image") {
                // We should update width and height, and replace value by URL only
                if (value.w) {
                    this.setValue("width", value.w);
                }
                if (value.h) {
                    this.setValue("height", value.h);
                }
            }
            value = value.url;
        } else if (editableCfg.dataType === "videoUrl") {
            this.setValue("src", value.src);
            if (this.cfg.editables.autoplay) {
                this.setValue("autoplay", value.autoplay);
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
            case "dom":
                if (editableCfg.dataType === "images") {
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
                        exsitingImgs[idx].src = imgValue.src;
                        exsitingImgs[idx].alt = imgValue.alt;
                        exsitingImgs[idx].width = imgValue.width;
                        exsitingImgs[idx].height = imgValue.height;
                    });
                    for (let idx = value.length; idx < exsitingImgs.length; idx++) {
                        exsitingImgs[idx].remove();
                    }
                }
                break;
        }
        this._dispatchChange();
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

    _configureComposer() {
        let listenInput = false;
        for (const [key, cfg] of Object.entries(this.cfg.editables)) {
            const element = this.querySelector(cfg.selector);
            if (!element) {
                throw key + " element not found with selector " + cfg.selector;
            }

            if (cfg.dataType === "image" && !element.getAttribute("src")) {
                element.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
            }

            switch (cfg.type) {
                case "simpleText":
                case "text":
                    const tinymceOptions = this.composer.getTinymceOptions(cfg.type === "simpleText");

                    tinymceOptions.setup = (ed) => {
                        ed.on("change", () => {
                            this._dispatchChange();
                        });
                    };

                    jQuery(element).myTinymce(tinymceOptions, this.composer.tinymceUrl);
                    listenInput = true;
                    break;
            }
        }

        if (listenInput) {
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
