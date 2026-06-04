const SELECTOR = 'select:not([multiple]):not([data-ue-native-select])';
const ENHANCED = 'ueSelectEnhanced';

let listenersBound = false;
let observer = null;

export function initCustomSelects() {
    document.querySelectorAll(SELECTOR).forEach((select) => enhanceSelect(select));

    if (!listenersBound) {
        bindGlobalListeners();
        listenersBound = true;
    }

    if (!observer) {
        observer = new MutationObserver((mutations) => {
            const meaningfulMutations = mutations.filter((mutation) => !mutation.target.closest?.('.ue-select__list'));

            if (meaningfulMutations.length === 0) {
                return;
            }

            if (meaningfulMutations.some((mutation) => mutation.addedNodes.length > 0)) {
                document.querySelectorAll(SELECTOR).forEach((select) => enhanceSelect(select));
            }

            document.querySelectorAll('.ue-select, .ue-select-proxy').forEach((wrapper) => renderSelect(wrapper));
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
}

function enhanceSelect(select) {
    if (select.dataset[ENHANCED] === 'true' || select.closest('.ue-select')) {
        return;
    }

    if (isOverlaySelect(select)) {
        enhanceOverlaySelect(select);
        return;
    }

    if (shouldKeepNativeSelect(select)) {
        select.dataset[ENHANCED] = 'skipped';
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'ue-select';
    wrapper.dataset.state = 'closed';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = resolveButtonClass(select);
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    button.disabled = select.disabled;

    const label = document.createElement('span');
    label.className = 'ue-select__label';

    const chevron = document.createElement('span');
    chevron.className = 'ue-select__chevron';
    chevron.setAttribute('aria-hidden', 'true');
    chevron.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';

    button.append(label, chevron);

    const list = document.createElement('div');
    list.className = 'ue-select__list';
    list.setAttribute('role', 'listbox');
    list.tabIndex = -1;
    list.hidden = true;

    select.parentNode.insertBefore(wrapper, select);
    wrapper.append(select, button, list);
    hideLegacyChevron(wrapper);

    select.dataset[ENHANCED] = 'true';
    select.classList.add('ue-select__native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (select.disabled) {
            return;
        }

        toggleSelect(wrapper, true);
    });

    button.addEventListener('keydown', (event) => handleButtonKeydown(event, wrapper));
    select.addEventListener('change', () => renderSelect(wrapper));

    renderSelect(wrapper);
}

function shouldKeepNativeSelect(select) {
    const className = select.className || '';

    if (select.hidden || select.type === 'hidden') {
        return true;
    }

    if (select.closest('[data-ue-native-select], [data-ue-custom-select-ignore]')) {
        return true;
    }

    if (/\b(sr-only|hidden|fixed)\b/.test(className)) {
        return true;
    }

    const style = window.getComputedStyle(select);

    if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) {
        return true;
    }

    const rect = select.getBoundingClientRect();

    return rect.width < 24 || rect.height < 24;
}

function isOverlaySelect(select) {
    const className = select.className || '';
    const hasOverlayClass = /\b(opacity-0|absolute|inset-0)\b/.test(className);
    const visualTrigger = findOverlayTrigger(select);

    return hasOverlayClass && Boolean(visualTrigger);
}

function enhanceOverlaySelect(select) {
    if (select.dataset[ENHANCED] === 'true') {
        return;
    }

    const host = select.parentElement;
    const trigger = findOverlayTrigger(select);
    const list = document.createElement('div');

    host.classList.add('ue-select-proxy');
    host.dataset.state = 'closed';

    select.dataset[ENHANCED] = 'true';
    select.classList.add('ue-select__native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');

    trigger.classList.remove('pointer-events-none');
    trigger.classList.add('ue-select-proxy__trigger');
    trigger.tabIndex = 0;
    trigger.setAttribute('role', 'button');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    list.className = 'ue-select__list ue-select__list--proxy';
    list.setAttribute('role', 'listbox');
    list.tabIndex = -1;
    list.hidden = true;
    host.append(list);

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        toggleSelect(host, true);
    });

    trigger.addEventListener('keydown', (event) => handleButtonKeydown(event, host));
    select.addEventListener('change', () => renderSelect(host));

    renderSelect(host);
}

function findOverlayTrigger(select) {
    return Array.from(select.parentElement?.children ?? []).find((child) => child !== select && child instanceof HTMLElement);
}

function hideLegacyChevron(wrapper) {
    const legacyChevron = wrapper.nextElementSibling;

    if (!legacyChevron || !legacyChevron.classList.contains('pointer-events-none')) {
        return;
    }

    if (!legacyChevron.querySelector('svg')) {
        return;
    }

    legacyChevron.setAttribute('hidden', 'hidden');
    legacyChevron.dataset.ueSelectLegacyChevron = 'hidden';
}

function resolveButtonClass(select) {
    const hasError = select.getAttribute('aria-invalid') === 'true'
        || select.className.includes('border-red')
        || select.className.includes('ue-input--error');

    return `ue-select__button${hasError ? ' ue-select__button--error' : ''}`;
}

function renderSelect(wrapper) {
    const select = wrapper.querySelector('select');
    const label = wrapper.querySelector('.ue-select__label');
    const list = wrapper.querySelector('.ue-select__list');
    const selected = select.selectedOptions[0] ?? select.options[select.selectedIndex];

    const button = wrapper.querySelector('.ue-select__button');

    if (button) {
        button.className = resolveButtonClass(select);
        button.disabled = select.disabled;
    }

    if (label) {
        label.textContent = selected?.textContent?.trim() || select.getAttribute('placeholder') || 'Chọn';
    }

    list.innerHTML = '';

    Array.from(select.children).forEach((child) => {
        if (child.tagName === 'OPTGROUP') {
            const group = document.createElement('div');
            group.className = 'ue-select__group';
            group.textContent = child.label;
            list.append(group);
            Array.from(child.children).forEach((option) => list.append(createOption(select, option)));
            return;
        }

        if (child.tagName === 'OPTION') {
            list.append(createOption(select, child));
        }
    });
}

function createOption(select, option) {
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'ue-select__option';
    item.dataset.value = option.value;
    item.setAttribute('role', 'option');
    item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
    item.disabled = option.disabled;

    const text = document.createElement('span');
    text.className = 'ue-select__option-text';
    text.textContent = option.textContent.trim();

    const check = document.createElement('span');
    check.className = 'ue-select__check';
    check.setAttribute('aria-hidden', 'true');
    check.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

    item.append(text, check);

    item.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (option.disabled) {
            return;
        }

        select.value = option.value;
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
        closeSelect(select.closest('.ue-select'));
        select.closest('.ue-select').querySelector('.ue-select__button').focus();
    });

    return item;
}

function handleButtonKeydown(event, wrapper) {
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        openSelect(wrapper);
        focusSelectedOrFirstOption(wrapper);
        return;
    }

    if (event.key === 'Escape') {
        closeSelect(wrapper);
    }
}

function handleOptionKeydown(event) {
    const option = event.target.closest('.ue-select__option');

    if (!option) {
        return;
    }

    const wrapper = option.closest('.ue-select');
    const options = getEnabledOptions(wrapper);
    const index = options.indexOf(option);

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        options[Math.min(index + 1, options.length - 1)]?.focus();
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        options[Math.max(index - 1, 0)]?.focus();
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        closeSelect(wrapper);
        wrapper.querySelector('.ue-select__button').focus();
    }
}

function toggleSelect(wrapper, closeOthers = false) {
    if (wrapper.dataset.state === 'open') {
        closeSelect(wrapper);
        return;
    }

    if (closeOthers) {
        closeAllSelects();
    }

    openSelect(wrapper);
}

function openSelect(wrapper) {
    positionList(wrapper);
    wrapper.dataset.state = 'open';
    wrapper.querySelector('.ue-select__list').hidden = false;
    wrapper.querySelector('.ue-select__button, .ue-select-proxy__trigger')?.setAttribute('aria-expanded', 'true');
}

function closeSelect(wrapper) {
    if (!wrapper) {
        return;
    }

    wrapper.dataset.state = 'closed';
    wrapper.querySelector('.ue-select__list')?.setAttribute('hidden', 'hidden');
    wrapper.querySelector('.ue-select__button, .ue-select-proxy__trigger')?.setAttribute('aria-expanded', 'false');
}

function closeAllSelects() {
    document.querySelectorAll('.ue-select[data-state="open"], .ue-select-proxy[data-state="open"]').forEach(closeSelect);
}

function positionList(wrapper) {
    const list = wrapper.querySelector('.ue-select__list');
    const rect = wrapper.getBoundingClientRect();
    const viewportPadding = 16;
    const maxWidth = window.innerWidth - viewportPadding * 2;
    const expectedWidth = Math.min(Math.max(list.scrollWidth, rect.width), maxWidth);

    if (rect.left + expectedWidth > window.innerWidth - viewportPadding) {
        list.style.left = 'auto';
        list.style.right = '0';
        list.style.transformOrigin = 'top right';
        return;
    }

    list.style.left = '0';
    list.style.right = 'auto';
    list.style.transformOrigin = 'top left';
}

function focusSelectedOrFirstOption(wrapper) {
    const selected = wrapper.querySelector('.ue-select__option[aria-selected="true"]:not(:disabled)');
    const first = getEnabledOptions(wrapper)[0];
    (selected || first)?.focus();
}

function getEnabledOptions(wrapper) {
    return Array.from(wrapper.querySelectorAll('.ue-select__option:not(:disabled)'));
}

function bindGlobalListeners() {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.ue-select, .ue-select-proxy')) {
            closeAllSelects();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllSelects();
        }

        handleOptionKeydown(event);
    });
}
