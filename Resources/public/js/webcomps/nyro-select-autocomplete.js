import { NyroSelect, NyroSelectOption, normalizeText } from "./nyro-select";

const cacheSearchesUrls = new Map();

const getCacheSearchByUrl = (url) => {
    if (!cacheSearchesUrls.has(url)) {
        cacheSearchesUrls.set(url, new Map());
    }

    return cacheSearchesUrls.get(url);
};

const autocompleteRequest = (comp, searchVal) => {
    const cacheSearches = getCacheSearchByUrl(comp.autocompleteUrl);

    if (comp._abortController) {
        comp._abortController.abort();
    }

    const formData = new FormData();
    formData.append("q", searchVal);

    const cacheKey = new URLSearchParams(formData).toString();
    if (cacheSearches.has(cacheKey)) {
        return Promise.resolve(cacheSearches.get(cacheKey));
    }

    comp._abortController = new AbortController();

    return fetch(comp.autocompleteUrl, {
        method: "POST",
        body: formData,
        signal: comp._abortController.signal,
    })
        .then((response) => {
            comp._abortController = false;
            if (response.ok) {
                return response.json();
            } else {
                return {
                    error: true,
                    status: response.status,
                };
            }
        })
        .then((response) => {
            cacheSearches.set(cacheKey, response.options);

            return response.options;
        })
        .catch(() => {
            // Catch here probably for abort
        });
};

const autocompleteRequestTimeout = (comp, searchVal, callback) => {
    if (comp._autocompleteTimeout) {
        clearTimeout(comp._autocompleteTimeout);
    }

    comp._autocompleteTimeout = setTimeout(() => {
        comp._autocompleteTimeout = false;
        autocompleteRequest(comp, searchVal).then(callback);
    }, 250 * (searchVal.length === 0 ? 4 : 1));
};

class NyroSelectAucomplete extends NyroSelect {
    get autocompleteUrl() {
        return this.getAttribute("autocomplete-url");
    }

    set autocompleteUrl(autocompleteUrl) {
        if (autocompleteUrl) {
            this.setAttribute("autocomplete-url", autocompleteUrl);
        } else {
            this.removeAttribute("autocomplete-url");
        }
    }

    connectedCallback() {
        super.connectedCallback();

        this._loadingOption = this.querySelector('nyro-select-option[value="__loading__"]');
        this._noResultOption = this.querySelector('nyro-select-option[value="__noResult__"]');
    }

    _filter() {
        this.querySelectorAll("nyro-select-option").forEach((option) => {
            option.hidden = true;
            option.focused = false;
        });
        this.classList.add("loading");
        if (this._loadingOption) {
            this._loadingOption.hidden = false;
        }

        const searchVal = normalizeText(this._search.value);
        autocompleteRequestTimeout(this, searchVal, (results) => {
            if (!results) {
                return;
            }

            if (this._loadingOption) {
                this._loadingOption.hidden = true;
            }

            if (results.length === 0 || results == {}) {
                if (this._noResultOption) {
                    this._noResultOption.hidden = false;
                }
            } else {
                results.forEach((optionData) => {
                    let option = this.querySelector('nyro-select-option[value="' + CSS.escape(optionData.value) + '"]');
                    if (!option) {
                        option = new NyroSelectOption();
                        option.value = optionData.value;
                        option.label = optionData.label;

                        if (optionData.attrs) {
                            Object.keys(optionData.attrs).forEach((key) => {
                                option.setAttribute(key, optionData.attrs[key]);
                            });
                        }

                        this.appendChild(option);
                    }
                    option.hidden = false;
                });
            }
        });
    }
}

window.customElements.define("nyro-select-autocomplete", NyroSelectAucomplete);

export default NyroSelectAucomplete;
