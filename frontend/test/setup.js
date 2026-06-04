import '@testing-library/jest-dom';

// Mantine relies on matchMedia + ResizeObserver, which jsdom doesn't implement.
window.matchMedia = window.matchMedia || function () {
    return {
        matches: false,
        media: '',
        onchange: null,
        addEventListener: () => {},
        removeEventListener: () => {},
        addListener: () => {},
        removeListener: () => {},
        dispatchEvent: () => false,
    };
};

class ResizeObserverStub {
    observe() {}
    unobserve() {}
    disconnect() {}
}
window.ResizeObserver = window.ResizeObserver || ResizeObserverStub;
