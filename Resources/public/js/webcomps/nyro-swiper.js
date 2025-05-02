const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    --nyro-swiper-animation-time: 300ms;
    --nyro-swiper-slide-width: 100%;

    display: inline-block;
}
:host(:not([disable-swipe])) {
    touch-action: pinch-zoom pan-y;
}
main {
    --pos: 0;
    --nb: 1;

    --delta: calc(100% - var(--nyro-swiper-slide-width));
    --maxTranslate: calc((var(--nb) - 1) * -1 * var(--nyro-swiper-slide-width) + var(--delta));

    width: 100%;
    height: 100%;
    --width: 100%;
    overflow: hidden;
}
main div {
    display: flex;
    transform: translateX(
        max(
            var(--pos) * -1 * var(--nyro-swiper-slide-width),
            var(--maxTranslate)
        )
    );
    transition: transform var(--nyro-swiper-animation-time);
}
::slotted(*) {
    flex-shrink: 0;
}
.calc {
    display: block;
    width: var(--nyro-swiper-slide-width);
    height: 0;
}
</style>
<main>
    <div>
        <slot></slot>
    </div>
</main>
<slot name="nav"></slot>
<span class="calc"></span>
`;

const pointerPos = {
        nyroSwiper: false,
        first: {},
        last: {},
    },
    downCallback = (e) => {
        if (pointerPos.nyroSwiper) {
            // We already passed through a pointerup, without ending it.
            // It's probably a pinch-zoom gesture, abort here to let it going though naturally
            unbind();
            return;
        }

        e.preventDefault();

        pointerPos.nyroSwiper = e.target.closest("nyro-swiper:not([disable-swipe])");
        pointerPos.first.x = e.clientX;
        pointerPos.first.y = e.clientY;

        document.body.addEventListener("pointermove", pointerMove);
        document.body.addEventListener("pointerup", pointerUp);
        document.body.addEventListener("pointercancel", pointerCancel);
    },
    checkSwipe = () => {
        const diffX = pointerPos.first.x - pointerPos.last.x;

        if (Math.abs(diffX) > 30) {
            if (diffX < 0) {
                pointerPos.nyroSwiper.prev();
            } else {
                pointerPos.nyroSwiper.next();
            }
            return true;
        }
    },
    pointerMove = (e) => {
        if (!pointerPos.nyroSwiper) {
            return;
        }
        pointerPos.last.x = e.clientX;
        pointerPos.last.y = e.clientY;

        e.preventDefault();

        if (checkSwipe()) {
            unbind();
        }
    },
    pointerUp = (e) => {
        if (!pointerPos.nyroSwiper) {
            return;
        }
        pointerPos.last.x = e.clientX;
        pointerPos.last.y = e.clientY;

        checkSwipe();
        unbind();
    },
    pointerCancel = () => {
        unbind();
    },
    unbind = () => {
        document.body.removeEventListener("pointermove", pointerMove);
        document.body.removeEventListener("pointerup", pointerUp);
        document.body.removeEventListener("pointercancel", pointerCancel);
        pointerPos.nyroSwiper = false;
    };

class NyroSwiper extends HTMLElement {
    static get observedAttributes() {
        return ["pos", "disable-swipe"];
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "pos") {
            this._writePos();
        } else if (name === "disable-swipe") {
            this._handleSwipe();
        }
    }

    get pos() {
        return this.hasAttribute("pos") ? parseInt(this.getAttribute("pos")) : 0;
    }

    set pos(pos) {
        if (pos) {
            this.setAttribute("pos", parseInt(pos));
        } else {
            this.removeAttribute("pos");
        }
    }

    get disableSwipe() {
        return this.hasAttribute("disable-swipe");
    }

    set disableSwipe(disableSwipe) {
        if (disableSwipe) {
            this.setAttribute("disable-swipe", "");
        } else {
            this.removeAttribute("disable-swipe");
        }
    }

    get elements() {
        return this.querySelectorAll(":scope > *:not([slot])");
    }

    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._main = this.shadowRoot.querySelector("main");
        this._calcSpan = this.shadowRoot.querySelector(".calc");

        this._nbElements = 1;
        this.shadowRoot.addEventListener("slotchange", () => {
            this._countElements();
        });

        this._countElements();
        this.calcLayout();

        this.bindNav();
    }

    connectedCallback() {
        this._handleSwipe();
        this._inited = true;
    }

    _handleSwipe() {
        if (this.disableSwipe) {
            this.removeEventListener("pointerdown", downCallback);
        } else {
            this.addEventListener("pointerdown", downCallback);
        }
    }

    bindNav() {
        const prev = this.querySelector('.navPrev[slot="nav"]');
        if (prev) {
            prev.addEventListener("click", (e) => {
                e.preventDefault();
                this.prev();
            });
        }
        const next = this.querySelector('.navNext[slot="nav"]');
        if (next) {
            next.addEventListener("click", (e) => {
                e.preventDefault();
                this.next();
            });
        }
    }

    _writePos() {
        if (this.pos < 0) {
            this.pos = 0;
            return;
        }
        if (this.pos >= this._nbElements) {
            this.pos = this._nbElements - 1;
            return;
        }

        this._main.style.setProperty("--pos", this.pos);

        if (this._inited) {
            this.dispatchEvent(
                new CustomEvent("nyroSwiperChangedPosition", {
                    bubbles: true,
                    cancelable: true,
                    detail: this.pos,
                })
            );
        }
    }

    _countElements() {
        this._nbElements = this.elements.length;

        this.calcLayout();
    }

    calcLayout() {
        this._swiperWidth = this.clientWidth;
        this._slideWidth = this._calcSpan.clientWidth;
        this._nbSlidesShown = Math.round(Math.floor((100 * this._swiperWidth) / this._slideWidth) / 100);

        if (this._nbElements) {
            this._maxPos = Math.ceil(this._nbElements / this._nbSlidesShown) - 1;
            this._main.style.setProperty("--nb", Math.max(this._nbElements, this._nbSlidesShown));
            this.classList.toggle("nyroSwiperNoNav", this._maxPos < 1);
            if (this.pos > this._maxPos) {
                this.pos = this._maxPos;
            }
        }
    }

    prev() {
        let prevPos = this.pos - this._nbSlidesShown;
        if (prevPos < 0) {
            prevPos = this._nbElements - this._nbSlidesShown;
        }
        this.pos = prevPos;
    }

    next() {
        let nextPos = this.pos + this._nbSlidesShown;
        if (nextPos >= this._nbElements) {
            nextPos = 0;
        }
        this.pos = nextPos;
    }

    goTo(element) {
        if (element.parentElement !== this) {
            console.warn("Element for goTo should be a direct child");
            return;
        }

        const newPos = Array.prototype.indexOf.call(this.elements, element);
        if (newPos !== -1) {
            this.pos = newPos;
        }
    }
}

window.customElements.define("nyro-swiper", NyroSwiper);

export default NyroSwiper;
