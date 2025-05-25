import { registerScrollFrom, unregisterScrollFrom } from "./scrollUtility.js";

/////////////////////////////////////////////////////
// START nyro-select-option
/////////////////////////////////////////////////////

const templateOption = document.createElement("template");
templateOption.innerHTML = `
<style>
:host {
    font-family: "Arial";
    font-size: 1em;
    background-color: #fff;
    padding: 2px 3px;
}
:host(:hover) {
    background-color: #e9ecef;
}
:host([focused]) {
    background-color: #d7dfe8;
}
:host([selected]) {
    color: #fff;
    background-color: #15539e;
}
</style>
<slot></slot>
`;

class NyroSelectOption extends HTMLElement {
    connectedCallback() {
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(templateOption.content.cloneNode(true));
    }

    get value() {
        return this.getAttribute("value");
    }

    set value(value) {
        if (value) {
            this.setAttribute("value", value);
        } else {
            this.removeAttribute("value");
        }
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

    get focused() {
        return this.hasAttribute("focused");
    }

    set focused(focused) {
        if (focused) {
            this.setAttribute("focused", "");
        } else {
            this.removeAttribute("focused");
        }
    }

    get label() {
        return this.innerHTML;
    }

    set label(label) {
        this.innerHTML = label;
    }

    get textLabel() {
        return this.textContent;
    }

    getSelected(unique) {
        if (this._selected) {
            this._selected.unique = unique;
            return this._selected;
        }

        this._selected = new NyroSelectSelected();
        this._selected.slot = "selectedValues";
        this._selected.value = this.value;
        this._selected.innerHTML = this.label;

        this._selected.unique = unique;

        return this._selected;
    }
}

window.customElements.define("nyro-select-option", NyroSelectOption);

/////////////////////////////////////////////////////
// END nyro-select-option
/////////////////////////////////////////////////////

/////////////////////////////////////////////////////
// START nyro-select-selected
/////////////////////////////////////////////////////

const templateSelected = document.createElement("template");
templateSelected.innerHTML = `
<style>
:host {
    display: inline-flex;
    align-items: center;
    margin-right: 2px;
}
:host([unique]) a {
    display: none;
}
:host(:not([unique])) {
    --nyro-select-selected-padding: 2px 4px;
    --nyro-select-selected-border-color: #aaa;
    --nyro-select-selected-color: #555;
    --nyro-select-selected-background: #eee;
    --nyro-select-selected-color-hover: var(--nyro-select-selected-color);
    --nyro-select-selected-background-hover: #ddd;

    --nyro-select-selected-border-remove-color: #999;
    --nyro-select-selected-border-remove-color-hover: #333;
    --nyro-select-selected-border-remove-bg-color-hover: #f1f1f1;

    font-family: "Arial";
    font-size: 1em;

    border: 1px solid var(--nyro-select-selected-border-color);
    border-radius: 2px;
    color: var(--nyro-select-selected-color);
    background: var(--nyro-select-selected-background);
}
:host(:not([unique]):hover) {
    color: var(--nyro-select-selected-color-hover);
    background: var(--nyro-select-selected-background-hover);
}
:host(:not([unique])) slot {
    display: inline-block;
    padding: var(--nyro-select-selected-padding);
}
:host(:not([unique])) a {
    align-self: stretch;
    display: inline-flex;
    align-items: center;
    font-size: 0.8em;
    text-decoration: none;
    color: var(--nyro-select-selected-border-remove-color);
    padding: var(--nyro-select-selected-padding);
    border-right: 1px solid var(--nyro-select-selected-border-color);
}
:host(:not([unique])) a:hover {
    background-color: var(--nyro-select-selected-border-remove-bg-color-hover);
    color: var(--nyro-select-selected-border-remove-color-hover);
    outline: none;
}
</style>
<a href="#">X</a>
<slot></slot>
`;

class NyroSelectSelected extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(templateSelected.content.cloneNode(true));
        this._span = this.shadowRoot.querySelector("span");
        this.shadowRoot.querySelector("a").addEventListener("click", (e) => {
            e.preventDefault();
            this.parentElement.removeValue(this.value);
        });
    }

    get value() {
        return this.getAttribute("value");
    }

    set value(value) {
        if (value) {
            this.setAttribute("value", value);
        } else {
            this.removeAttribute("value");
        }
    }

    get unique() {
        return this.hasAttribute("unique");
    }

    set unique(unique) {
        if (unique) {
            this.setAttribute("unique", "");
        } else {
            this.removeAttribute("unique");
        }
    }

    get label() {
        return this._span.innerHTML;
    }

    set label(label) {
        this._span.innerHTML = label;
    }
}

window.customElements.define("nyro-select-selected", NyroSelectSelected);

/////////////////////////////////////////////////////
// END nyro-select-selected
/////////////////////////////////////////////////////

/////////////////////////////////////////////////////
// START nyro-select
/////////////////////////////////////////////////////

const valueMissingMessage = (() => {
    let select = document.createElement("select");
    select.required = true;

    return select.validationMessage;
})();

const template = document.createElement("template");
template.innerHTML = `
<style>
::-webkit-scrollbar {
    width: var(--scrollbar-width, 10px);
    z-index: var(--nyro-select-dropdown-z-index);
}

::-webkit-scrollbar-thumb {
    background: var(--scrollbar-thumb, #fff);
    border-radius: var(--scrollbar-width, 10px);
    border: 2px solid transparent;
    background-clip: padding-box;
}

::-webkit-scrollbar-thumb:hover {
    background-color: var(--scrollbar-thumb-hover, #ddd);
}

:host {
    --nyro-select-search-height: 100%;
    --nyro-select-search-font-size: 14px;
    --nyro-select-search-width: 13em;
    --nyro-select-arrow-width: 2px;
    --nyro-select-arrow-width-right: calc(var(--nyro-select-arrow-width) * 3);
    --nyro-select-arrow-color: currentColor;
    --nyro-select-arrow-focused-opacity: 0;
    --nyro-select-color: currentColor;
    --nyro-select-placeholder-color: #a9a9a9;

    --nyro-select-dropdown-border-width: 1px;
    --nyro-select-dropdown-border-style: solid;
    --nyro-select-dropdown-border-color: #767676;
    --nyro-select-dropdown-border-radius: 2px;
    --nyro-select-dropdown-background-color: #fff;
    --nyro-select-dropdown-box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.3);
    --nyro-select-dropdown-max-width: 50vw;
    --nyro-select-dropdown-max-height: min(27em, 40vh);
    --nyro-select-dropdown-z-index: 9999;

    position: relative;
    display: inline-block;
    font-size: 0.8em;
    font-family: "Arial";
    color: #000;
    background: #fff;
    border: 1px solid #767676;
    border-radius: 2px;
    padding: 1px 2px;
}
:host(:focus) {
    outline: 2px solid #000;
}
:host:after {
    content: '';
    position: absolute;
    top: 50%;
    right: var(--nyro-select-arrow-width-right);
    margin-top: calc(var(--nyro-select-arrow-width) * -1);
    display: inline-block;
    border: solid var(--nyro-select-arrow-color);
    border-width: 0 var(--nyro-select-arrow-width) var(--nyro-select-arrow-width) 0;
    padding: var(--nyro-select-arrow-width);
    transform: translate(0, -50%) rotate(45deg);
}
#searchCont {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    height: 100%;
}
#search {
    flex-grow: 1;
    height: var(--nyro-select-search-height);
    font-family: inherit;
    font-weight: inherit;
    font-style: inherit;
    font-size: var(--nyro-select-search-font-size);
    width: var(--nyro-select-search-width);
    color: var(--nyro-select-color);
    border: none;
    background: transparent;
    padding: 0;
    outline: none;
}
#search::placeholder {
    color: var(--nyro-select-placeholder-color);
    opacity: 1;
}
#search::-webkit-search-decoration,
#search::-webkit-search-cancel-button,
#search::-webkit-search-results-button,
#search::-webkit-search-results-decoration {
    display: none;
}
.dropdown {
    position: fixed;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    overflow: auto;

    border-width: var(--nyro-select-dropdown-border-width);
    border-style: var(--nyro-select-dropdown-border-style);
    border-color: var(--nyro-select-dropdown-border-color);
    border-radius: var(--nyro-select-dropdown-border-radius);
    background-color: var(--nyro-select-dropdown-background-color);
    box-shadow: var(--nyro-select-dropdown-box-shadow);

    max-width: var(--nyro-select-dropdown-max-width);
    max-height: var(--nyro-select-dropdown-max-height);

    z-index: var(--nyro-select-dropdown-z-index);

    opacity: 0;
    visibility: hidden;
    transition: opacity 300ms, visibility 300ms;
}

:host(.hasValue[html]:not([multiple][focused])) #searchCont slot[name="selectedValues"] {
    display: inline-block;
    font-size: var(--nyro-select-search-font-size);
    width: var(--nyro-select-search-width);
    flex-grow: 1;
    height: var(--nyro-select-search-height);
}
:host([html][focused]:not([multiple])) #searchCont slot[name="selectedValues"] {
    display: none !important;
}

:host(.hasValue[html]:not([multiple], [focused])) #searchCont #search {
    display: none;
}

:host([focused]):after {
    opacity: var(--nyro-select-arrow-focused-opacity);
}
:host([focused]) .dropdown {
    opacity: 1;
    visibility: visible;
}

@supports (-webkit-touch-callout: none) {
    #search {
        font-size: calc(max(var(--nyro-select-search-font-size), 16px));
    }
}
</style>
<div id="searchCont">
    <slot name="selectedValues"></slot>
    <input id="search" type="search" />
</div>
<div class="dropdown">
    <slot></slot>
</div>
`;

const normalizeTextReg = /\p{Diacritic}/gu;
const normalizeText = (text) => {
    return text.normalize("NFD").replace(normalizeTextReg, "").toLowerCase().trim();
};

class NyroSelect extends HTMLElement {
    static get formAssociated() {
        return true;
    }

    constructor() {
        super();
        this._internals = this.attachInternals();
    }

    static get observedAttributes() {
        return ["required"];
    }

    get focused() {
        return this.hasAttribute("focused");
    }

    set focused(focused) {
        if (focused) {
            this.setAttribute("focused", "");
            registerScrollFrom(this, () => {
                this._positionDropdown();
            });
        } else {
            this.removeAttribute("focused");
            unregisterScrollFrom(this);
        }
    }

    get required() {
        return this.hasAttribute("required");
    }

    set required(required) {
        if (required) {
            this.setAttribute("required", "");
        } else {
            this.removeAttribute("required");
        }
    }

    get multiple() {
        return this.hasAttribute("multiple");
    }

    set multiple(multiple) {
        if (multiple) {
            this.setAttribute("multiple", "");
        } else {
            this.removeAttribute("multiple");
        }
    }

    get multipleNoCtrl() {
        return this.hasAttribute("multiple-no-ctrl");
    }

    set multipleNoCtrl(multipleNoCtrl) {
        if (multipleNoCtrl) {
            this.setAttribute("multiple-no-ctrl", "");
        } else {
            this.removeAttribute("multiple-no-ctrl");
        }
    }

    get html() {
        return this.hasAttribute("html");
    }

    set html(html) {
        if (html) {
            this.setAttribute("html", "");
        } else {
            this.removeAttribute("html");
        }
    }

    attributeChangedCallback(name, prev, next) {
        if (name === "required") {
            this._setValidity();
        }
    }

    connectedCallback() {
        this.attachShadow({
            mode: "open",
        });
        this.shadowRoot.append(template.content.cloneNode(true));

        const insideStyle = this.querySelector('style[slot="insideStyle"]');
        if (insideStyle) {
            this.shadowRoot.querySelector("style").textContent += insideStyle.textContent;
            insideStyle.remove();
        }

        if (!this.hasAttribute("tabindex")) {
            this.setAttribute("tabindex", "0");
        }

        this._value = undefined;
        this._search = this.shadowRoot.querySelector('input[type="search"]');
        this._dropdown = this.shadowRoot.querySelector(".dropdown");

        this.addEventListener("focus", (e) => {
            if (e.relatedTarget && e.relatedTarget.matches('[type="submit"]')) {
                return;
            }
            this._search.focus();
        });

        this.addEventListener("blur", () => {
            this.focused = false;
            this.querySelectorAll("nyro-select-option[focused]").forEach((optionFocused) => {
                optionFocused.focused = false;
            });
            this._parseSelected();
        });

        this.addEventListener("click", (e) => {
            if (this.focused || e.defaultPrevented) {
                return;
            }
            // Reset validity to hide native error message
            this._internals.setValidity({});
            this.focused = true;
            this._search.focus();
            setTimeout(() => {
                // Re-evaluate validity value later
                this._setValidity();
            }, 150);
        });

        this._search.addEventListener("input", () => {
            this._filter();
        });

        this._search.addEventListener("keydown", (e) => {
            switch (e.key) {
                case "ArrowUp":
                    e.preventDefault();
                    this._moveFocus(-1);
                    break;
                case "ArrowDown":
                    e.preventDefault();
                    this._moveFocus(1);
                    break;
                case "Enter":
                    e.preventDefault();
                    this._actionFocused();
                    break;
                case "Escape":
                    e.preventDefault();
                    this._search.blur();
                    break;
            }
        });

        this._search.addEventListener("focus", (e) => {
            if (e.relatedTarget && e.relatedTarget.matches('[type="submit"]')) {
                return;
            }
            this._search.value = "";
            this.focused = true;
            this._filter();
            this._positionDropdown();
            const currentSelected = this.querySelector("nyro-select-option[selected]");
            if (currentSelected) {
                this._scrollIntoView(currentSelected);
            }
        });

        this._dropdown.addEventListener("click", (e) => {
            const option = e.target.closest("nyro-select-option");
            if (!option) {
                return;
            }

            e.preventDefault();
            if (this.multiple && (this.multipleNoCtrl || e.ctrlKey || e.metaKey)) {
                option.selected = !option.selected;
            } else {
                this._unselectAll();
                option.selected = true;
            }
            this._parseOrBlur();
        });

        this._defaultOption = this.querySelector('nyro-select-option[value=""], nyro-select-option:not([value])');
        if (this._defaultOption) {
            //this._defaultOption.hidden = true;
        }
        this._search.placeholder = this.hasAttribute("placeholder")
            ? this.getAttribute("placeholder")
            : this._defaultOption
            ? this._defaultOption.textLabel
            : "";

        this._parseSelected(true);
    }

    disconnectedCallback() {
        unregisterScrollFrom(this);
    }

    get value() {
        if (!this.multiple || !this._value) {
            return this._value;
        }

        return Array.from(this._value.values());
    }

    set value(value) {
        const isArray = Array.isArray(value);
        if (this.multiple && !isArray) {
            throw new Error("value should be an array in multiple mode");
        } else if (!this.multiple && isArray) {
            throw new Error("value should not be an array in single mode");
        }

        if (!isArray) {
            value = [value];
        }

        this._unselectAll();
        const newSelecteds = this.querySelectorAll('nyro-select-option[value="' + value.join('], nyro-select-option[value="') + '"]');
        newSelecteds.forEach((newSelected) => {
            newSelected.selected = true;
        });

        this._parseSelected();
    }

    search(search) {
        this._search.value = search;
        this._filter();
    }

    _parseOrBlur() {
        if (this.multiple) {
            this._parseSelected();
        } else {
            // Blur will trigger parseSelected
            this.blur();
        }
    }

    removeValue(value) {
        const option = this.querySelector('nyro-select-option[value="' + CSS.escape(value) + '"');
        if (option) {
            option.selected = false;
            this._parseSelected();
        }
    }

    _unselectAll() {
        const currentlySelecteds = this.querySelectorAll("nyro-select-option[selected]");
        if (currentlySelecteds.length === 0) {
            return false;
        }

        currentlySelecteds.forEach((currentlySelected) => {
            currentlySelected.selected = false;
        });

        return true;
    }

    _parseSelected(ignoreDispatch) {
        const currentSelecteds = this.querySelectorAll('nyro-select-option[selected][value]:not([value=""])');
        if (currentSelecteds.length > 1 && !this.multiple) {
            console.error("Multiple selected option found in single mode, only first found will be used");
        }

        if (this.multiple || this.html) {
            this.querySelectorAll('[slot="selectedValues"]').forEach((selectedValue) => {
                selectedValue.remove();
            });
        }

        let value = undefined;
        if (this.html) {
            this._search.value = "";
        }
        currentSelecteds.forEach((currentSelected) => {
            if (this.multiple) {
                if (!value) {
                    value = new FormData();
                }
                value.append(this.name, currentSelected.value);

                this.appendChild(currentSelected.getSelected());
            } else if (!value) {
                value = currentSelected.value;
                this._search.value = currentSelected.textLabel;
                if (this.html) {
                    this.appendChild(currentSelected.getSelected(true));
                }
            }
        });

        this.classList.toggle("hasValue", !!value);
        this._value = value;
        this._internals.setFormValue(this._value);
        this._setValidity();

        if (this.multiple && this.focused) {
            this._positionDropdown();
        }

        if (!ignoreDispatch) {
            this.dispatchEvent(
                new Event("change", {
                    bubbles: true,
                    cancelable: true,
                })
            );
        }
    }

    _positionDropdown() {
        const bounding = this.getBoundingClientRect();

        this._dropdown.style.minWidth = bounding.width + "px";
        this._dropdown.style.top = bounding.top + bounding.height + "px";
        this._dropdown.style.left = bounding.left + "px";
    }

    _filter() {
        const searchVal = normalizeText(this._search.value);
        this.querySelectorAll("nyro-select-option").forEach((option) => {
            const matching = searchVal.length ? normalizeText(option.textLabel).indexOf(searchVal) !== -1 : true;
            option.hidden = !matching;
            if (!matching && option.focused) {
                option.focused = false;
            }
        });
    }

    _moveFocus(direction) {
        let currentlyFocused = this.querySelector("nyro-select-option[focused]:not([hidden])");
        if (!currentlyFocused) {
            currentlyFocused = this.querySelector("nyro-select-option[selected]:not([hidden])");
        }
        if (!currentlyFocused) {
            currentlyFocused = this.querySelector("nyro-select-option:not([hidden])");
            if (direction === 1) {
                // focus it directly, this is the first arrow down
                currentlyFocused.focused = true;
                this._scrollIntoView(currentlyFocused);
                return;
            }
        }
        if (!currentlyFocused) {
            // do nothing
            return;
        }
        let newFocused;
        if (direction > 0) {
            let currentCursor = currentlyFocused;
            // Search through all next sibling
            while (!newFocused && currentCursor.nextElementSibling) {
                currentCursor = currentCursor.nextElementSibling;
                if (currentCursor.matches("nyro-select-option:not([hidden])")) {
                    newFocused = currentCursor;
                }
            }

            if (!newFocused) {
                newFocused = this.querySelector("nyro-select-option:not([hidden])");
            }
        } else {
            let currentCursor = currentlyFocused;
            // Search through all previous sibling
            while (!newFocused && currentCursor.previousElementSibling) {
                currentCursor = currentCursor.previousElementSibling;
                if (currentCursor.matches("nyro-select-option:not([hidden])")) {
                    newFocused = currentCursor;
                }
            }

            if (!newFocused) {
                newFocused = this.querySelector("nyro-select-option:not([hidden]):last-child");
            }
        }

        if (newFocused && newFocused != currentlyFocused) {
            currentlyFocused.focused = false;
            newFocused.focused = true;
            this._scrollIntoView(newFocused);
        }
    }

    _actionFocused() {
        const haveUnselected = this.multiple ? false : this._unselectAll();
        const currentlyFocused = this.querySelector("nyro-select-option[focused]:not([hidden])");

        if (!currentlyFocused) {
            if (haveUnselected) {
                this._parseSelected();
            }
            this.focused = false;
            return;
        }

        if (this.multiple) {
            currentlyFocused.selected = !currentlyFocused.selected;
        } else {
            currentlyFocused.selected = true;
        }

        this._parseOrBlur();
    }

    _scrollIntoView(option, direct) {
        option.scrollIntoView({
            block: "center",
            inline: "center",
            behavior: "instant",
        });
    }

    _setValidity() {
        if (this.required && (this._value === undefined || this._value === "")) {
            this._internals.setValidity(
                {
                    valueMissing: true,
                },
                valueMissingMessage,
                this._search
            );
        } else {
            this._internals.setValidity({});
        }
    }

    checkValidity() {
        return this._internals.checkValidity();
    }

    reportValidity() {
        return this._internals.reportValidity();
    }

    setValidity(flags, message, anchor) {
        return this._internals.setValidity(flags, message, anchor || this._search);
    }

    get form() {
        return this._internals.form;
    }

    get name() {
        return this.getAttribute("name");
    }

    get type() {
        return this.localName;
    }

    get validity() {
        return this.internals_.validity;
    }

    get validationMessage() {
        return this.internals_.validationMessage;
    }

    get willValidate() {
        return this.internals_.willValidate;
    }

    // @todo read and implement more functions described here
    // https://web.dev/more-capable-form-controls/
}

window.customElements.define("nyro-select", NyroSelect);

export { NyroSelectOption, NyroSelectSelected, NyroSelect, normalizeText };

export default NyroSelect;

/////////////////////////////////////////////////////
// END nyro-select
/////////////////////////////////////////////////////
