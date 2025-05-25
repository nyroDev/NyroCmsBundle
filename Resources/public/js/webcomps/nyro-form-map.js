const valueMissingMessage = (() => {
    const input = document.createElement("input");
    input.required = true;

    return input.validationMessage;
})();

const template = document.createElement("template");
template.innerHTML = `
<style>
:host {
    display: inline-block;
    font-size: 0.8em;
    font-family: "Arial";
    color: #000;
    background: #fff;
    border: 1px solid #767676;
    border-radius: 2px;
    min-width: 300px;
    height: 0;
    min-height: 300px;
}
</style>
`;

const libraries = {};
const loadLibraries = async () => {
    if (libraries.maps) {
        return Promise.resolve(libraries);
    }

    if (!window.google || !window.google.maps || !window.google.maps.importLibrary) {
        console.error("google maps import library not defined");
        return;
    }

    libraries.core = await window.google.maps.importLibrary("core");
    libraries.maps = await window.google.maps.importLibrary("maps");
    libraries.marker = await window.google.maps.importLibrary("marker");
    libraries.geocoding = await window.google.maps.importLibrary("geocoding");

    return Promise.resolve(libraries);
};

class NyroFormMap extends HTMLElement {
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

        this.value = this.hasAttribute("value") ? this.getAttribute("value") : undefined;

        loadLibraries().then((libraries) => {
            const initPosition = {
                lat: 47.326923,
                lng: 5.239491,
                zoom: 8,
            };
            if (this.value) {
                const tmp = this.value.split(",");
                initPosition.lat = parseFloat(tmp[0]);
                initPosition.lng = parseFloat(tmp[1]);
                initPosition.zoom = 17;
            }
            this._mapEl = new libraries.maps.MapElement();
            this._mapEl.innerMap.setOptions({
                mapId: "DEMO_MAP_ID",
                center: initPosition,
                mapTypeId: libraries.maps.MapTypeId.ROADMAP,
                zoom: initPosition.zoom,
                mapTypeControl: false,
                fullscreenControl: false,
                streetViewControl: false,
                gestureHandling: "cooperative",
            });
            this.shadowRoot.appendChild(this._mapEl);
            this._marker = new libraries.marker.AdvancedMarkerElement({
                map: this._mapEl.innerMap,
                position: initPosition,
                gmpDraggable: true,
            });
            this._mapEl.appendChild(this._marker);
            this._geocoder = new libraries.geocoding.Geocoder();
            this._marker.addListener("dragend", () => {
                this._updateValue();
            });
            this._mapEl.innerMap.addListener("click", (e) => {
                this._marker.position = e.latLng;
                this._updateValue();
            });

            this._bindFields();
        });
    }

    _updateValue() {
        this.value = this._marker.position.lat + "," + this._marker.position.lng;
    }

    _bindFields() {
        if (this._formListener) {
            if (!this.fields) {
                this.form.removeEventListener("change", this._formListener);
                this._formListener = false;
            }
            return;
        }

        this._formListener = (e) => {
            if (!e.target.closest(this.fields)) {
                return;
            }

            this._geocodeFields();
        };
        this.form.addEventListener("change", this._formListener);
    }

    _geocodeFields() {
        if (!this.fields || !this._mapEl || !this._geocoder) {
            return;
        }

        if (this._geocodeTimeout) {
            clearTimeout(this._geocodeTimeout);
        }

        this._geocodeTimeout = setTimeout(() => {
            this._geocodeTimeout = false;
            const values = [];
            this.form.querySelectorAll(this.fields).forEach((field) => {
                values.push(field.value);
            });

            const valueString = values.join(" ").trim();
            if (!valueString || valueString.length < 10) {
                return;
            }

            this._geocoder
                .geocode({
                    address: valueString,
                })
                .then((response) => {
                    if (response.results.length) {
                        this._mapEl.innerMap.fitBounds(response.results[0].geometry.viewport);
                        this._marker.setPosition(response.results[0].geometry.location);
                        this._updateValue();
                    }
                });
        }, 150);
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

    get fields() {
        return this.getAttribute("fields");
    }

    set fields(fields) {
        if (fields) {
            this.setAttribute("fields", fields);
        } else {
            this.removeAttribute("fields");
        }
        this._bindFields();
    }

    get value() {
        return this._value;
    }

    set value(value) {
        this._value = value;
        this._internals.setFormValue(this._value);
        this._setValidity();
    }

    _setValidity() {
        if (this.required && (this._value === undefined || this._value === "")) {
            this._internals.setValidity(
                {
                    valueMissing: true,
                },
                valueMissingMessage
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

window.customElements.define("nyro-form-map", NyroFormMap);

export default NyroFormMap;
