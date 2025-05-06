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
    width: 48%;
    margin-bottom: 10px;
}
.image img {
    width: 100%;
    height: auto;
    aspect-ratio: 1;
    object-fit: cover;
}
.image nav {
    text-align: center;
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

        openBtn.innerHTML = this.composer.trans("inputFile.choose." + this.fileType);
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
            const del = e.target.closest(".deleteHandle");
            if (!del) {
                return;
            }

            e.preventDefault();

            this.composer.confirm(() => {
                del.closest(".image").remove();
                this._dispatchChange();
            });
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

        this.composer.fillNav(imageCont.querySelector("nav"), ["drag", "delete"]);

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
