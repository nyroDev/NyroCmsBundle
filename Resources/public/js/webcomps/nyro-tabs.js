const templateTab = document.createElement("template");
templateTab.innerHTML = `
<style>
:host {
    display: block;
    cursor: pointer;
    padding: 0.5em 1em;
    border-right: var(--nyro-tab-border);
    border-bottom: var(--nyro-tab-border);

    flex-shrink: 0;

    transition: color 0.3s, background-color 0.3s;
}
:host(:hover),
:host([selected]) {
    color: #000;
    background-color: #ccc;
}
</style>
<slot></slot>
`;

class NyroTab extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(templateTab.content.cloneNode(true));
    }

    get selected() {
        return this.hasAttribute("selected");
    }

    set selected(selected) {
        if (selected) {
            this.setAttribute("selected", "");
        } else {
            this.removeAttribute("selected");
        }
    }

    get index() {
        return this.hasAttribute("index") ? parseInt(this.getAttribute("index")) : 0;
    }

    set index(index) {
        if (index || index === 0) {
            this.setAttribute("index", parseInt(index));
        } else {
            this.removeAttribute("index");
        }
    }
}

window.customElements.define("nyro-tab", NyroTab);

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    --nyro-tab-border-color: #ccc;
    --nyro-tab-border: 1px solid var(--nyro-tab-border-color);

    --nyro-tab-nav-border: var(--nyro-tab-border);
    --nyro-tab-nav-background: #fff;

    display: block;
}
:host([html-nav]) nav {
    display: none;
}
nav {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    background: var(--nyro-tab-nav-background);
}
:host(.noWrapNav) nav {
    flex-wrap: nowrap;
    overflow: auto;
}
nav:after {
    content: "";
    position: absolute;
    bottom: 0;
    height: 1px;
    left: 0;
    right: 0;
    border-bottom: var(--nyro-tab-nav-border);
}
#hiddenContent {
    display: none;
}
</style>
<slot name="htmlNav"></slot>
<nav>
    <slot name="nav"></slot>
</nav>
<main>
    <slot name="header"></slot>
    <slot name="content"></slot>
    <slot name="footer"></slot>
</main>
<slot id="hiddenContent"></slot>
`;

class NyroTabs extends HTMLElement {
    static get observedAttributes() {
        return ["tab"];
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "tab") {
            this._selectTab();
        }
    }

    get tab() {
        return this.hasAttribute("tab") ? parseInt(this.getAttribute("tab")) : 0;
    }

    set tab(tab) {
        if (tab) {
            this.setAttribute("tab", parseInt(tab));
        } else {
            this.removeAttribute("tab");
        }
    }

    get htmlNav() {
        return this.hasAttribute("html-nav");
    }

    get listenPrevNext() {
        return this.hasAttribute("listen-prev-next");
    }

    get isLastTab() {
        return this.hasAttribute("is-last-tab");
    }

    get selector() {
        return this.getAttribute("selector") || ':scope > *:not([slot="nav"])';
    }

    set selector(selector) {
        if (selector) {
            this.setAttribute("selector", selector);
        } else {
            this.removeAttribute("selector");
        }
    }

    get titleSelector() {
        return this.getAttribute("title-selector") || "[title]";
    }

    set titleSelector(titleSelector) {
        if (titleSelector) {
            this.setAttribute("title-selector", titleSelector);
        } else {
            this.removeAttribute("title-selector");
        }
    }

    get elements() {
        return this.querySelectorAll(this.selector + ':not([slot="header"], [slot="footer"], [slot="nav"], [slot="htmlNav"])');
    }

    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        this.shadowRoot.querySelector("nav").addEventListener("click", (e) => {
            this._handleNavClick(e);
        });
    }

    _handleNavClick(e) {
        const tab = e.target.closest("nyro-tab");
        if (tab) {
            e.preventDefault();
            this.tab = tab.index;
        }
    }

    connectedCallback() {
        if (this.htmlNav) {
            this.setupHtmlNav();
        } else {
            this.writeTabs();
        }

        if (this.listenPrevNext) {
            this.addEventListener("click", (e) => {
                const prevNext = e.target.closest(".nyro-tabs-prev, .nyro-tabs-next");
                if (!prevNext) {
                    return;
                }
                e.preventDefault();
                if (prevNext.classList.contains("nyro-tabs-prev")) {
                    this.tab = Math.max(0, this.tab - 1);
                } else if (prevNext.classList.contains("nyro-tabs-next")) {
                    this.tab = Math.min(this.elements.length - 1, this.tab + 1);
                }
            });
        }

        const form = this.closest("form");
        if (form) {
            let lastInvalidTimestamp = false;
            form.addEventListener(
                "invalid",
                (e) => {
                    if (!lastInvalidTimestamp || lastInvalidTimestamp + 250 < e.timeStamp) {
                        lastInvalidTimestamp = e.timeStamp;
                        this.elements.forEach((el, index) => {
                            if (el.contains(e.target)) {
                                this.tab = index;
                            }
                        });
                    }
                },
                true
            );
        }
    }

    setupHtmlNav() {
        if (!this.htmlNav) {
            console.warn("NyroTabs: htmlNav is not set, should ne setup html nav.");
            return;
        }
        const htmlNav = this.querySelector('[slot="htmlNav"]');
        if (!htmlNav) {
            console.warn("NyroTabs: htmlNav slot is not found, should be set.");
            return;
        }
        htmlNav.addEventListener("click", (e) => {
            this._handleNavClick(e);
        });
        this._selectTab();
    }

    writeTabs() {
        if (this.htmlNav) {
            console.warn("NyroTabs: htmlNav is set, no tabs will be written.");
            return;
        }

        const elements = this.elements;

        this.querySelectorAll("nyro-tab").forEach((tab) => {
            tab.remove();
        });

        if (elements.length === 0) {
            return;
        }

        elements.forEach((el, index) => {
            const tab = new NyroTab();
            tab.slot = "nav";

            let title = false;
            if (this.titleSelector.startsWith("[")) {
                title = el.getAttribute(this.titleSelector.slice(1, -1));
            } else {
                const titleEl = el.querySelector(this.titleSelector);
                if (titleEl) {
                    title = titleEl.innerHTML;
                }
            }

            tab.innerHTML = title || `Tab ${index + 1}`;
            tab.index = index;
            if (index === this.tab) {
                tab.selected = true;
                el.slot = "content";
            }
            this.appendChild(tab);
        });
    }

    _selectTab() {
        const elements = Array.from(this.elements);
        const selectedTab = this.querySelector("nyro-tab[selected]");
        const newSelectedTab = this.querySelector(`nyro-tab[index="${this.tab}"]`);

        if (selectedTab && selectedTab !== newSelectedTab) {
            elements[selectedTab.index].slot = "";
            selectedTab.selected = false;
        }

        if (!newSelectedTab) {
            return;
        }

        newSelectedTab.selected = true;
        elements[this.tab].slot = "content";

        if (this.tab === elements.length - 1) {
            this.setAttribute("is-last-tab", "");
        } else {
            this.removeAttribute("is-last-tab");
        }

        this.dispatchEvent(
            new CustomEvent("tabchange", {
                bubbles: true,
                cancelable: true,
                detail: this.pos,
            })
        );
    }
}

window.customElements.define("nyro-tabs", NyroTabs);

export { NyroTab, NyroTabs };

export default NyroTabs;
