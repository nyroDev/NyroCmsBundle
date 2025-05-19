import Sortable from "sortablejs";

const template = document.createElement("template");
template.innerHTML = `
<style>
.images {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}
.image {
    position: relative;
    width: 48%;
    margin-bottom: 10px;
    border: 1px solid var(--composer-color-item);
    box-sizing: border-box;
}
.image img {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 1;
    object-fit: cover;
}
.image nav {
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.image nav a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: var(--composer-action-size);
    height: var(--composer-action-size);
    background-color: var(--composer-color-bg-nav);
    border: 1px solid var(--composer-color-item);
    text-decoration: none;
    color: var(--composer-color);
    transition: color var(--composer-transition-time), background-color var(--composer-transition-time);
}
.image nav a:hover {
    color: var(--composer-color-bg-nav);
    background-color: var(--composer-color-item);
}
.image nav a[data-action="delete"]:hover {
    background-color: var(--composer-color-error);
}

.image nav a .icon {
    width: 16px;
    height: 16px;
}
</style>
<a href="#" part="nyroComposerBtn nyroComposerBtnUi" class="open">Choose file...</a>
<div class="images"></div>
`;

const templateImg = document.createElement("template");
templateImg.innerHTML = `
<div class="image">
    <img src="" alt="" width="" height="" loading="lazy" />
    <nav></nav>
</div>
`;

class NyroComposerInputFile extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._value = {};

        const openBtn = this.shadowRoot.querySelector(".open");

        openBtn.innerHTML = this.composer.trans("inputFile.choose." + this.fileType + (this.multiple ? "s" : ""));
        openBtn.addEventListener("click", (e) => {
            e.preventDefault();
            this.composer.selectMedia(this.fileType, (imageData) => {
                this._handleImageData(imageData);
            });
        });
    }

    get fileType() {
        return this.getAttribute("file-type") || "file";
    }

    set fileType(fileType) {
        this.setAttribute("file-type", fileType);
    }

    get composer() {
        return document.querySelector("nyro-composer");
    }

    get multiple() {
        return this.hasAttribute("multiple");
    }

    set value(value) {
        if (!this.multiple) {
            this._value.url = value;
            return;
        }

        this._imagesCont = this.shadowRoot.querySelector(".images");

        this._imagesCont.addEventListener("click", (e) => {
            const actionable = e.target.closest("[data-action]");
            if (!actionable) {
                return;
            }

            e.preventDefault();
            if (actionable.dataset.action === "delete") {
                this.composer.confirm(
                    () => {
                        actionable.closest(".image").remove();
                        this._dispatchChange();
                    },
                    false,
                    {
                        text: this.composer.trans("inputFile.deleteConfirm." + this.fileType, false),
                    }
                );
            }
        });

        this._sortable = Sortable.create(this._imagesCont, {
            group: "inputImages",
            handle: ".dragHandle",
            animation: 150,
            onEnd: (e) => {
                this._dispatchChange();
            },
        });

        value.forEach((imgValue) => {
            this._apendImage(imgValue);
        });
    }

    get value() {
        if (!this.multiple) {
            return this._value;
        }

        const value = [];

        this._imagesCont.querySelectorAll("img").forEach((img) => {
            value.push({
                src: img.getAttribute("src"),
                alt: img.getAttribute("alt"),
                width: img.getAttribute("width"),
                height: img.getAttribute("height"),
            });
        });

        return value;
    }

    _apendImage(imgValue) {
        const imageCont = templateImg.content.cloneNode(true),
            img = imageCont.querySelector("img");

        img.src = imgValue.src;
        img.alt = imgValue.alt;
        img.width = imgValue.width;
        img.height = imgValue.height;

        imageCont.querySelector("nav").appendChild(this.composer.getTemplate("ui", "multipleFilesNav").content.cloneNode(true));

        this._imagesCont.appendChild(imageCont);
    }

    _dispatchChange() {
        this.dispatchEvent(
            new Event("change", {
                bubbles: true,
                cancelable: true,
            })
        );
    }

    _handleImageData(imageData) {
        if (!this.multiple) {
            this._value = imageData;
            this._dispatchChange();
            return;
        }

        this._apendImage({
            src: imageData.url,
            alt: imageData.name,
            width: imageData.w,
            height: imageData.h,
        });

        this._dispatchChange();
    }
}

window.customElements.define("nyro-composer-input-file", NyroComposerInputFile);

export default NyroComposerInputFile;
