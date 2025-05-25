let scrollRaf = false,
    scrollRegisterd = false;

const registerScrollElements = new Map();

const scrollListenerOptions = {
    capture: true,
    passive: true,
};

const scrollListener = () => {
    if (scrollRaf) {
        cancelAnimationFrame(scrollRaf);
        return;
    }

    scrollRaf = requestAnimationFrame(() => {
        scrollRaf = false;
        registerScrollElements.forEach((callback) => {
            callback();
        });
    });
};

const registerScrollListener = () => {
    if (scrollRegisterd) {
        return;
    }

    scrollRegisterd = true;
    document.addEventListener("scroll", scrollListener, scrollListenerOptions);
};

const unregisterScrollListener = () => {
    if (!scrollRegisterd) {
        return;
    }

    scrollRegisterd = false;
    document.removeEventListener("scroll", scrollListener, scrollListenerOptions);
};

const registerScrollFrom = (element, callback) => {
    if (registerScrollElements.has(element)) {
        return;
    }

    registerScrollElements.set(element, callback);
    registerScrollListener();
};

const unregisterScrollFrom = (element) => {
    if (!registerScrollElements.has(element)) {
        return;
    }

    registerScrollElements.delete(element);
    if (registerScrollElements.size === 0) {
        unregisterScrollListener();
    }
};

export { registerScrollFrom, unregisterScrollFrom };
