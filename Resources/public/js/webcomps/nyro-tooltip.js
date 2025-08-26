import { registerScrollFrom, unregisterScrollFrom } from "./scrollUtility.js";

const getOpenedTooltips = () => {
    return document.querySelectorAll("nyro-tooltip[open]");
};
const closeAllOtherTooltips = (currentTooltip) => {
    getOpenedTooltips().forEach((tooltip) => {
        if (tooltip !== currentTooltip) {
            tooltip.open = false;
        }
    });
};

let registeredPointerDown = false;

const registerPointerDown = () => {
    if (registeredPointerDown) {
        return;
    }

    registeredPointerDown = true;

    document.addEventListener("pointerdown", (e) => {
        if (typeof e.target.closest !== "function" || e._tooltipToggle) {
            return;
        }
        closeAllOtherTooltips(e.target.closest("nyro-tooltip"));
    });
};

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    --nyro-tooltip-background-color: #ffffff;
    --nyro-tooltip-border-color: #0078d4;
    --nyro-tooltip-border-size: 2px;
    --nyro-tooltip-border-radius: 4px;
    --nyro-tooltip-box-shadow: none;
    --nyro-tooltip-move: 0px;

    --nyro-tooltip-arrow-color: var(--nyro-tooltip-border-color);
    --nyro-tooltip-arrow-size: 12px;
    --nyro-tooltip-arrow-move: 0px;

    --nyro-tooltip-transition-time: 0.3s;

    display: inline-block;
    position: relative;
}
:host(:focus) {
    outline: none;
}
.trigger {
    cursor: pointer;
}
.tooltip {
    position: absolute;
    z-index: 10;
    background: var(--nyro-tooltip-background-color);
    border: var(--nyro-tooltip-border-size) solid var(--nyro-tooltip-border-color);
    border-radius: var(--nyro-tooltip-border-radius);
    box-shadow: var(--nyro-tooltip-box-shadow);

    opacity: 0;
    visibility: hidden;
    transition: opacity var(--nyro-tooltip-transition-time), visibility var(--nyro-tooltip-transition-time);
}
.tooltip:after {
    content: "";
    position: absolute;
    height: 0;
    width: 0;
    border: solid transparent;
    border-color: rgba(0, 0, 0, 0);
    border-width: var(--nyro-tooltip-arrow-size);
    pointer-events: none;
}

:host([open]) .tooltip {
    opacity: 1;
    visibility: visible;
}
@media (hover: hover) {
    :host(:hover) .tooltip {
        opacity: 1;
        visibility: visible;
        transition: opacity var(--nyro-tooltip-transition-time), visibility var(--nyro-tooltip-transition-time);
    }
}

:host([valign="down"]) .tooltip,
:host(:not([valign])) .tooltip {
    top: 100%;
    margin-top: var(--nyro-tooltip-arrow-size);
}
:host([valign="down"]) .tooltip:after,
:host(:not([valign])) .tooltip:after {
    bottom: 100%;
    border-bottom-color: var(--nyro-tooltip-arrow-color);
}

:host([valign="up"]) .tooltip {
    bottom: 100%;
    margin-bottom: var(--nyro-tooltip-arrow-size);
}
:host([valign="up"]) .tooltip:after {
    top: 100%;
    border-top-color: var(--nyro-tooltip-arrow-color);
}

:host([halign="center"]) .tooltip,
:host(:not([halign], [halign-default])) .tooltip {
    left: 50%;
    transform: translateX(-50%);
}
:host([halign="center"]) .tooltip:after,
:host(:not([halign], [halign-default])) .tooltip:after {
    left: 50%;
    transform: translateX(-50%);
}

:host([halign="left"]) .tooltip,
:host([halign-default="left"]:not([halign])) .tooltip {
    left: var(--nyro-tooltip-move);
}
:host([halign="left"]) .tooltip:after,
:host([halign-default="left"]:not([halign])) .tooltip:after {
    left: calc(-1 * var(--nyro-tooltip-border-size) + var(--nyro-tooltip-arrow-move));
}

:host([halign="right"]) .tooltip,
:host([halign-default="right"]:not([halign])) .tooltip {
    right: var(--nyro-tooltip-move);
}
:host([halign="right"]) .tooltip:after,
:host([halign-default="right"]:not([halign])) .tooltip:after {
    right: calc(-1 * var(--nyro-tooltip-border-size) + var(--nyro-tooltip-arrow-move));
}
</style>
<span class="trigger"><slot name="trigger"></slot></span>
<div class="tooltip">
    <div>
        <slot name="tooltip"></slot>
        <slot name="content"></slot>
    </div>
</div>
`;

class NyroTooltip extends HTMLElement {
    static get observedAttributes() {
        return ["open"];
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "open") {
            if (this.open) {
                this._handleDropdownPosition();
                closeAllOtherTooltips(this);
                registerScrollFrom(this, () => {
                    this._handleDropdownPosition();
                });
            } else {
                unregisterScrollFrom(this);
            }
        }
    }

    connectedCallback() {
        registerPointerDown();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this._trigger = this.querySelector('[slot="trigger"]');
        if (!this._trigger) {
            console.error("No trigger slot found");
            return;
        }

        this._template = this.querySelector("template");
        const div = document.createElement("div");
        div.slot = "content";
        div.innerHTML = this._template ? this._template.innerHTML : "No content";
        this.appendChild(div);

        this._tooltip = this.shadowRoot.querySelector(".tooltip");

        this._trigger.addEventListener("pointerenter", () => this._handleDropdownPosition());
        this._trigger.addEventListener("pointerdown", (e) => {
            e._tooltipToggle = true;
            this.open = !this.open;
        });

        this._handleDropdownPosition();
    }

    _handleDropdownPosition() {
        this.valign = "";
        this.halign = "";

        const bounding = this._tooltip.getBoundingClientRect();

        if (bounding.bottom >= document.documentElement.clientHeight) {
            this.valign = "up";
        }
        if (bounding.right >= document.documentElement.clientWidth) {
            this.halign = "right";
        } else if (bounding.left <= 0) {
            this.halign = "left";
        }
    }

    get open() {
        return this.hasAttribute("open");
    }

    set open(open) {
        if (open) {
            this.setAttribute("open", "");
        } else {
            this.removeAttribute("open");
        }
    }

    get valign() {
        return this.getAttribute("valign");
    }

    set valign(valign) {
        if (valign) {
            this.setAttribute("valign", valign);
        } else {
            this.removeAttribute("valign");
        }
    }

    get halign() {
        return this.getAttribute("halign");
    }

    set halign(halign) {
        if (halign) {
            this.setAttribute("halign", halign);
        } else {
            this.removeAttribute("halign");
        }
    }
}

window.customElements.define("nyro-tooltip", NyroTooltip);

export default NyroTooltip;
