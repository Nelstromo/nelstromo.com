// ==================== CONSTANTS & CONFIGURATION ====================
const CONFIG = {
    UNIT_PRICE: 10,
    SHIRT_SIZES: {
        small: {
            leftArm: { width: '30px', height: '50px' },
            body: { width: '70px', height: '110px' },
            rightArm: { width: '30px', height: '50px' },
            neck: { width: '40px', height: '40px' },
            logo: { width: '30px', height: '30px' },
            text: { fontSize: '18px' },
            pocket: { width: '20px', height: '20px' }
        },
        medium: {
            leftArm: { width: '50px', height: '70px' },
            body: { width: '130px', height: '180px' },
            rightArm: { width: '50px', height: '50px' },
            neck: { width: '60px', height: '50px' },
            logo: { width: '60px', height: '60px' },
            text: { fontSize: '35px' },
            pocket: { width: '40px', height: '40px' }
        },
        large: {
            leftArm: { width: '60px', height: '100px' },
            body: { width: '170px', height: '230px' },
            rightArm: { width: '60px', height: '100px' },
            neck: { width: '70px', height: '50px' },
            logo: { width: '80px', height: '80px' },
            text: { fontSize: '45px' },
            pocket: { width: '55px', height: '55px' }
        }
    },
    LOGOS: {
        coin: 'img/coin-icon.png',
        golf: 'img/golf-icon.png',
        book: 'img/book-icon.png',
        cat: 'img/cat-icon.png'
    }
};

const REGEX = {
    text: /^[A-Za-z0-9]{1,6}$/,
    phone: /^\d{3}[-.\s]?\d{3}[-.\s]?\d{4}$/,
    zip: /^\d{5}$/
};

// ==================== DOM ELEMENT REFERENCES ====================
const elements = {
    // Main sections
    gettingStarted: document.getElementById('gettingStarted'),
    shirtControls: document.getElementById('shirtControls'),
    shirtPreview: document.getElementById('shirtPreview'),
    cartSection: document.getElementById('shoppingCartSection'),
    purchaseShirt: document.getElementById('purchaseShirt'),
    
    // Buttons
    buildShirtButton: document.getElementById('buildShirtBtn'),
    startOverButton: document.getElementById('startOverBtn'),
    
    // Cart
    cartImg: document.getElementById('shoppingCartImage'),
    cartBadge: document.getElementById('cartBadge'),
    
    // Shirt customization
    colorPicker: document.getElementById('colorPicker'),
    customText: document.getElementById('customText'),
    logoSelect: document.getElementById('logos'),
    
    // Size radios
    sizeSmall: document.getElementById('sizeSmall'),
    sizeMedium: document.getElementById('sizeMedium'),
    sizeLarge: document.getElementById('sizeLarge'),
    
    // Pocket radios
    noPocket: document.getElementById('noPocket'),
    leftPocket: document.getElementById('leftPocket'),
    rightPocket: document.getElementById('rightPocket'),
    
    // Error messages
    errorMessage: document.getElementById('error'),
    errorSize: document.getElementById('error'),
    
    // Menu notifications
    colorNote: document.getElementById('color-note'),
    sizeNote: document.getElementById('size-note'),
    logoNote: document.getElementById('logo-note'),
    textNote: document.getElementById('text-note'),
    pocketNote: document.getElementById('pocket-note'),
    
    // Instruction message
    instructionMsg: document.getElementById('instructionMsg')
};

// Dynamic elements
let dynamicElements = {
    logoPreview: document.getElementById('logoPreview'),
    textPreview: document.getElementById('textPreview'),
    pocketPreview: document.getElementById('pocketPreview')
};

// ==================== STATE MANAGEMENT ====================
const state = {
    orders: [],
    colorSelected: false,
    buyShirt: false
};

// ==================== UTILITY FUNCTIONS ====================
const DOM = {
    show: (el) => {
        if (el) {
            el.classList.remove('hidden');
            el.style.removeProperty('display');
        }
    },
    
    hide: (el) => {
        if (el) {
            el.classList.add('hidden');
            el.style.removeProperty('display');
        }
    },
    
    setElementState: (element, options) => {
        if (!element) return;
        if (options.disabled !== undefined) element.disabled = options.disabled;
        if (options.opacity !== undefined) element.style.opacity = options.opacity;
        if (options.cursor !== undefined) element.style.cursor = options.cursor;
        if (options.placeholder !== undefined) element.placeholder = options.placeholder;
    },
    
    applyStyles: (element, styles) => {
        if (!element) return;
        Object.entries(styles).forEach(([key, value]) => {
            element.style[key] = value;
        });
    }
};

/**
 * Updates a menu notification element with message and type
 */
function setNotification(element, message, type = 'info') {
    if (!element) return;
    
    element.textContent = message;
    element.className = 'menu-notification';
    
    if (message) {
        element.classList.add(type);
    }
}

/**
 * Clears a menu notification
 */
function clearNotification(element) {
    if (element) {
        element.textContent = '';
        element.className = 'menu-notification';
    }
}

// ==================== INPUT STATE MANAGEMENT ====================
/**
 * Enables or disables shirt customization inputs
 */
function setInputsEnabled(enabled) {
    const stateOptions = enabled
        ? { disabled: false, opacity: '1', cursor: enabled ? 'pointer' : 'text' }
        : { disabled: true, opacity: '0.5', cursor: 'not-allowed' };
    
    DOM.setElementState(elements.logoSelect, { ...stateOptions, cursor: stateOptions.cursor });
    
    DOM.setElementState(elements.customText, {
        ...stateOptions,
        cursor: enabled ? 'text' : 'not-allowed',
        placeholder: enabled ? '' : 'Pick a color first'
    });
    
    [elements.noPocket, elements.leftPocket, elements.rightPocket].forEach(radio => {
        if (radio) radio.disabled = !enabled;
    });
    
    const pocketContainer = document.querySelector('.radio-container:has(#noPocket)');
    if (pocketContainer) {
        pocketContainer.style.opacity = enabled ? '1' : '0.5';
        pocketContainer.style.cursor = enabled ? 'default' : 'not-allowed';
    }
}

/**
 * Resets all form inputs and state to initial values
 */
function resetForm() {
    elements.customText.value = '';
    elements.logoSelect.selectedIndex = 0;
    state.buyShirt = false;
    state.colorSelected = false;
    
    document.querySelectorAll('input[name="shirtSize"], input[name="pockets"]').forEach(r => r.checked = false);
    
    if (elements.errorSize) elements.errorSize.textContent = '';
    if (elements.errorMessage) elements.errorMessage.textContent = '';

    setNotification(elements.colorNote, 'Pick a color', 'info');
    setNotification(elements.sizeNote, 'Choose a size', 'info');
    clearNotification(elements.logoNote);
    clearNotification(elements.textNote);
    clearNotification(elements.pocketNote);
    
    updateInstructionMessage();
}

// ==================== INITIALIZATION & STATUS ====================

/**
 * Updates the instruction message (h3) based on current shirt selections
 */
function updateInstructionMessage() {
    if (!elements.instructionMsg) return;
    
    const selections = getSelections();
    const textValid = selections.text === '' || REGEX.text.test(selections.text);
    const hasBranding = isValidLogo(selections.logo) || selections.text.length > 0;
    
    if (!state.colorSelected) {
        elements.instructionMsg.textContent = 'Please choose a color';
        return;
    }
    
    if (!selections.size) {
        elements.instructionMsg.textContent = 'Please choose a size';
        return;
    }
    
    if (!hasBranding) {
        elements.instructionMsg.textContent = 'Please choose a logo or enter custom text';
        return;
    }
    
    if (selections.text && !textValid) {
        elements.instructionMsg.textContent = 'Text must be 1-6 letters/numbers';
        return;
    }
    
    elements.instructionMsg.textContent = 'Drag t-shirt to cart';
}

/**
 * Updates the status message based on current shirt selections
 */
function updateStatus() {
    updateInstructionMessage();
}

/**
 * Gets all current shirt selections
 */
function getSelections() {
    return {
        size: document.querySelector('input[name="shirtSize"]:checked')?.value || '',
        logo: elements.logoSelect.value || '',
        text: elements.customText.value.trim(),
        color: elements.colorPicker.value || '#ffffff',
        pocket: document.querySelector('input[name="pockets"]:checked')?.value || 'No pocket'
    };
}

/**
 * Checks if the logo selection is valid
 */
function isValidLogo(logo) {
    return logo && logo !== '' && logo !== 'Logos' && logo !== 'none';
}

/**
 * Checks if the shirt can be dragged to cart
 */
function canDrag() {
    const selections = getSelections();
    const textValid = selections.text === '' || REGEX.text.test(selections.text);
    const hasBranding = isValidLogo(selections.logo) || selections.text.length > 0;
    return Boolean(selections.size) && textValid && hasBranding;
}

// ==================== SHIRT BUILDER & PREVIEW ====================
/**
 * Rebuilds the shirt preview HTML and reinitializes elements
 */
function rebuildShirt() {
    elements.shirtPreview.innerHTML = `
        <div class="ShirtSection" id="ShirtSection">
            <div class="makeShirt" id="makeShirt">
                <div class="shirtInner">
                    <div class="leftArmShirt shirtColorChange"></div>
                    <div class="bodyShirt shirtColorChange">
                        <div class="topShirt"></div>
                        <div id="logoPreview"></div>
                        <div id="textPreview"></div>
                        <div id="pocketPreview"></div>
                    </div>
                    <div class="rightArmShirt shirtColorChange"></div>
                </div>
            </div>
        </div>
    `;

    dynamicElements.logoPreview = document.getElementById('logoPreview');
    dynamicElements.textPreview = document.getElementById('textPreview');
    dynamicElements.pocketPreview = document.getElementById('pocketPreview');
    
    bindDragHandlers();
    updateStatus();
}

/**
 * Updates the shirt color in the preview
 */
function updateShirtColor(color) {
    document.querySelectorAll('.shirtColorChange').forEach(part => {
        part.style.backgroundColor = color;
    });
}

/**
 * Updates the shirt size and scales all components
 */
function updateShirtSize(size) {
    const sizeConfig = CONFIG.SHIRT_SIZES[size];
    if (!sizeConfig) return;
    
    const partElements = {
        leftArm: document.querySelector('.leftArmShirt'),
        body: document.querySelector('.bodyShirt'),
        rightArm: document.querySelector('.rightArmShirt'),
        neck: document.querySelector('.topShirt')
    };
    
    if (!partElements.leftArm || !partElements.body || !partElements.rightArm) return;
    
    DOM.applyStyles(partElements.leftArm, sizeConfig.leftArm);
    DOM.applyStyles(partElements.body, sizeConfig.body);
    DOM.applyStyles(partElements.rightArm, sizeConfig.rightArm);
    DOM.applyStyles(partElements.neck, sizeConfig.neck);
    DOM.applyStyles(dynamicElements.logoPreview, sizeConfig.logo);
    DOM.applyStyles(dynamicElements.textPreview, sizeConfig.text);
    DOM.applyStyles(dynamicElements.pocketPreview, sizeConfig.pocket);
}

/**
 * Updates the logo displayed on the shirt
 */
function updateLogo(logoName) {
    const logoElement = dynamicElements.logoPreview;
    if (!logoElement) return;
    
    logoElement.classList.remove('logo-pop');
    
    if (logoName === 'none' || !logoName) {
        logoElement.style.backgroundImage = '';
        elements.customText.disabled = false;
        return;
    }
    
    const logoPath = CONFIG.LOGOS[logoName];
    if (logoPath) {
        logoElement.style.backgroundImage = `url('${logoPath}')`;
        elements.customText.disabled = true;
        
        void logoElement.offsetWidth; 
        logoElement.classList.add('logo-pop');
    }
}

/**
 * Updates the pocket position on the shirt
 */
function updatePocket(position) {
    const pocket = dynamicElements.pocketPreview;
    if (!pocket) return;
    
    pocket.className = '';
    
    switch (position) {
        case 'none':
            pocket.style.display = 'none';
            break;
        case 'left':
            pocket.style.display = 'block';
            pocket.classList.add('pocket-left');
            break;
        case 'right':
            pocket.style.display = 'block';
            pocket.classList.add('pocket-right');
            break;
    }
}

/**
 * Updates the custom text displayed on the shirt
 */
function updateTextPreview(text) {
    if (dynamicElements.textPreview) {
        dynamicElements.textPreview.textContent = text;
        dynamicElements.textPreview.style.color = 'white';
    }
}

// ==================== EVENT LISTENERS ====================
elements.colorPicker.addEventListener('input', () => {
    const selectedColor = elements.colorPicker.value;
    state.colorSelected = true;
    setInputsEnabled(true);
    updateShirtColor(selectedColor);
    
    setNotification(elements.colorNote, '✓ Color Selected', 'success');
    
    updateStatus();
});

[elements.sizeSmall, elements.sizeMedium, elements.sizeLarge].forEach(radio => {
    if (!radio) return;
    radio.addEventListener('change', () => {
        updateShirtSize(radio.value);
        if (elements.errorSize) elements.errorSize.textContent = '';
        
        setNotification(elements.sizeNote, '✓ Size Selected', 'success');
        
        updateStatus();
    });
});

elements.logoSelect.addEventListener('change', () => {
    updateLogo(elements.logoSelect.value);
    
    if (elements.logoSelect.value && elements.logoSelect.value !== 'none') {
        setNotification(elements.logoNote, '✓ Logo Selected', 'success');
        setNotification(elements.textNote, '! Logo selected', 'warning');
    } else if (elements.logoSelect.value === 'none') {
        setNotification(elements.logoNote, 'No Logo', 'info');
        clearNotification(elements.textNote);
    }
    
    updateStatus();
});

elements.customText.addEventListener('input', () => {
    const text = elements.customText.value.trim();
    
    elements.logoSelect.disabled = text !== '';
    
    if (text.length > 6) {
        setNotification(elements.textNote, '⚠ Max 6 characters', 'error');
        state.buyShirt = false;
        updateStatus();
        return;
    }
    
    if (text && !REGEX.text.test(text)) {
        setNotification(elements.textNote, '⚠ Letters/numbers only', 'error');
        state.buyShirt = false;
        updateStatus();
        return;
    }
    
    if (text) {
        setNotification(elements.textNote, '✓ Text Added', 'success');
        setNotification(elements.logoNote, '! Text entered', 'warning');
        state.buyShirt = true;
    } else {
        clearNotification(elements.textNote);
        clearNotification(elements.logoNote);
    }
    
    updateTextPreview(text);
    updateStatus();
});

[elements.noPocket, elements.leftPocket, elements.rightPocket].forEach((radio, index) => {
    if (!radio) return;
    const positions = ['none', 'left', 'right'];
    radio.addEventListener('change', () => {
        updatePocket(positions[index]);
        
        setNotification(elements.pocketNote, '✓ Pocket Selected', 'success');
        
        updateStatus();
    });
});

// ==================== CART MANAGEMENT ====================
/**
 * Normalizes text by trimming whitespace
 */
function normalizeText(t) {
    return (t || '').trim();
}

/**
 * Creates a unique key for a shirt order
 */
function shirtKey(order) {
    return [
        order.size || '',
        (order.color || '').toLowerCase(),
        order.pocket || '',
        order.logo || '',
        normalizeText(order.text).toUpperCase()
    ].join('|');
}

/**
 * Calculates the total quantity of all orders
 */
function getTotalQty() {
    return state.orders.reduce((sum, o) => sum + (o.qty || 1), 0);
}

/**
 * Updates the cart badge with current item count
 */
function updateCartBadge() {
    if (!elements.cartBadge) return;
    const qty = getTotalQty();

    elements.cartBadge.textContent = qty;
    
    if (qty > 0) {
        elements.cartBadge.classList.remove('hidden');
        elements.cartBadge.classList.add('show');
        bump(elements.cartBadge);
    } else {
        elements.cartBadge.classList.remove('show', 'bump');
        elements.cartBadge.classList.add('hidden');
        elements.cartBadge.style.transform = 'scale(0)';
    }
}

/**
 * Triggers a bump animation on an element
 */
function bump(el) {
    if (!el) return;
    el.classList.remove('bump');
    void el.offsetWidth;
    el.classList.add('bump');
}

/**
 * Adds a new item to cart or increments existing item quantity
 */
function addOrIncrement(item) {
    const key = shirtKey(item);
    const idx = state.orders.findIndex(x => shirtKey(x) === key);

    if (idx === -1) {
        state.orders.push({ ...item, qty: 1 });
    } else {
        state.orders[idx].qty = (state.orders[idx].qty || 1) + 1;
    }

    updateCartBadge();
}

/**
 * Aggregates orders by combining identical shirts
 */
function aggregateOrders(list) {
    const map = new Map();

    list.forEach(o => {
        const key = shirtKey(o);
        const qty = o.qty || 1;

        if (!map.has(key)) {
            map.set(key, { ...o, qty });
        } else {
            const cur = map.get(key);
            cur.qty += qty;
            map.set(key, cur);
        }
    });

    return Array.from(map.values());
}

elements.cartImg.addEventListener('click', renderCartView);

// ==================== CART VIEW & CHECKOUT ====================
/**
 * Renders the cart view with order details
 */
function renderCartView() {
    DOM.hide(elements.gettingStarted);
    DOM.hide(elements.shirtControls);
    DOM.show(elements.cartSection);
    DOM.show(elements.purchaseShirt);

    const aggregated = aggregateOrders(state.orders);

    if (!aggregated.length) {
        renderEmptyCart();
        return;
    }

    renderCartTable(aggregated);
}

/**
 * Displays the empty cart message and button
 */
function renderEmptyCart() {
    elements.purchaseShirt.innerHTML = `
        <h3 class="emptyCartMessage">Your Cart Is Empty</h3>
        <button id="cartBuildBtn" class="emptyCartBtn">Build Shirt</button>
    `;

    const contactFormEl = document.getElementById('contactForm');
    if (contactFormEl) {
        contactFormEl.classList.add('hidden');
        contactFormEl.setAttribute('hidden', '');
    }

    document.getElementById('cartBuildBtn').addEventListener('click', () => {
        DOM.show(elements.gettingStarted);
        DOM.show(elements.shirtControls);
        DOM.hide(elements.cartSection);
        DOM.hide(elements.purchaseShirt);

        const cf = document.getElementById('contactForm');
        if (cf) {
            cf.classList.add('hidden');
            cf.setAttribute('hidden', '');
        }

        if (elements.buildShirtButton) {
            elements.buildShirtButton.classList.add('hidden');
        }
        updateStatus();
    });

    if (elements.buildShirtButton) {
        elements.buildShirtButton.classList.remove('hidden');
    }
    updateCartBadge();
}

/**
 * Renders the cart table with all orders
 */
function renderCartTable(aggregated) {
    const totalQty = aggregated.reduce((s, o) => s + (o.qty || 1), 0);
    const totalCost = totalQty * CONFIG.UNIT_PRICE;

    const rows = aggregated.map((order, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${order.size}</td>
            <td><span style="display:inline-block;width:16px;height:16px;border:1px solid #999;vertical-align:middle;margin-right:6px;background:${order.color};"></span>${order.color}</td>
            <td>${order.pocket}</td>
            <td>${order.logo}</td>
            <td>${order.text || '—'}</td>
            <td>$${CONFIG.UNIT_PRICE.toFixed(2)}</td>
            <td>${order.qty || 1}</td>
            <td>$${((order.qty || 1) * CONFIG.UNIT_PRICE).toFixed(2)}</td>
        </tr>
    `).join('');

    elements.purchaseShirt.innerHTML = `
        <div id="shirtTableInfo" class="shirtTableInfo">
            <table class="cartTable">
                <thead>
                    <tr><th>#</th><th>Size</th><th>Color</th><th>Pocket</th><th>Logo</th><th>Text</th><th>Cost</th><th>Total</th><th>Total Cost</th></tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr class="totalsRow">
                        <td colspan="6" style="text-align:right;font-weight:700;">TOTALS</td>
                        <td>$${CONFIG.UNIT_PRICE.toFixed(2)}</td>
                        <td>${totalQty}</td>
                        <td>$${totalCost.toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
            <button id="backToBuilderBtn" class="btn primary buildShirtBtn">Build Another Shirt</button>
        </div>
        ${renderCheckoutForm()}
    `;

    setupCartEventListeners();
}

/**
 * Returns the checkout form HTML
 */
function renderCheckoutForm() {
    return `
        <div id="orderFormInfo">
            <form method="post" id="contactForm">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName">
                <small class="errorMessage"></small>

                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName">
                <small class="errorMessage"></small>

                <label for="streetAddress">Street Address:</label>
                <input type="text" id="streetAddress" name="streetAddress">
                <small class="errorMessage"></small>

                <label for="aptNumber">Apt #:</label>
                <input type="text" id="aptNumber" name="aptNumber">

                <label for="city">City:</label>
                <input type="text" id="city" name="city">
                <small class="errorMessage"></small>

                <label for="state">State:</label>
                <input type="text" id="state" name="state">
                <small class="errorMessage"></small>

                <label for="zipCode">Zip Code:</label>
                <input type="text" id="zipCode" name="zipCode">
                <small class="errorMessage"></small>

                <label for="phoneNumber">Phone Number:</label>
                <input type="tel" id="phoneNumber" name="phoneNumber">
                <small class="errorMessage"></small>

                <button type="submit" id="placeOrderBtn">Place Order</button>
            </form>
        </div>
    `;
}

/**
 * Sets up event listeners for cart navigation and form validation
 */
function setupCartEventListeners() {
    const contactFormEl = document.getElementById('contactForm');
    if (contactFormEl) {
        contactFormEl.classList.remove('hidden');
        contactFormEl.removeAttribute('hidden');
    }

    const backBtn = document.getElementById('backToBuilderBtn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            DOM.hide(elements.cartSection);
            DOM.hide(elements.purchaseShirt);
            DOM.show(elements.shirtControls);
            if (elements.buildShirtButton) {
                elements.buildShirtButton.classList.add('hidden');
            }
            resetForm();
            rebuildShirt();
        });
    }

    const backLink = document.getElementById('backToBuilderLink');
    if (backLink) {
        backLink.addEventListener('click', (e) => {
            e.preventDefault();
            DOM.hide(elements.cartSection);
            DOM.hide(elements.purchaseShirt);
            DOM.show(elements.shirtControls);
            if (elements.buildShirtButton) {
                elements.buildShirtButton.classList.add('hidden');
            }
            resetForm();
            rebuildShirt();
        });
    }

    const form = document.getElementById('contactForm');
    const fields = {
        firstName: document.getElementById('firstName'),
        lastName: document.getElementById('lastName'),
        streetAddress: document.getElementById('streetAddress'),
        city: document.getElementById('city'),
        state: document.getElementById('state'),
        zipCode: document.getElementById('zipCode'),
        phoneNumber: document.getElementById('phoneNumber')
    };
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    /**
     * Sets or clears an error message for an input field
     */
    function setError(input, msg) {
        const small = input.nextElementSibling?.classList.contains('errorMessage') ? input.nextElementSibling : null;
        
        if (small) {
            small.textContent = msg || '';
            small.style.color = msg ? 'red' : '';
        }
        
        input.style.borderColor = msg ? 'red' : '';
    }

    /**
     * Validates a single form field
     */
    function validateField(field, fieldName) {
        const value = field.value.trim();
        
        if (!value) {
            setError(field, 'This field is required.');
            return false;
        }

        if (fieldName === 'zipCode' && !REGEX.zip.test(value)) {
            setError(field, 'Use 5 digits.');
            return false;
        }

        if (fieldName === 'phoneNumber' && !REGEX.phone.test(value)) {
            setError(field, 'Use 999-999-9999 or similar.');
            return false;
        }

        setError(field, '');
        return true;
    }

    /**
     * Validates all form fields and enables/disables submit button
     */
    function validateForm() {
        let isValid = true;

        Object.entries(fields).forEach(([name, field]) => {
            if (!validateField(field, name)) {
                isValid = false;
            }
        });

        placeOrderBtn.disabled = !isValid;
        placeOrderBtn.style.opacity = isValid ? '' : '0.5';
        placeOrderBtn.style.pointerEvents = isValid ? '' : 'none';
        placeOrderBtn.style.filter = isValid ? '' : 'grayscale(100%)';

        return isValid;
    }

    form.addEventListener('input', validateForm);
    form.addEventListener('change', validateForm);

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!validateForm()) return;

        handleOrderSubmit(fields);
    });

    placeOrderBtn.disabled = true;
}

/**
 * Processes order submission and displays confirmation
 */
function handleOrderSubmit(fields) {
    const items = aggregateOrders(state.orders);

    const itemsText = items.map(o => {
        const pocketText = (o.pocket || 'no pocket').replace(/([A-Z])/g, ' $1').toLowerCase();
        const detail = (o.logo && o.logo !== 'none (text only)')
            ? `logo: ${o.logo}`
            : `text: "${(o.text || 'none')}"`;
        return `${o.qty || 1} ${o.size} shirt${(o.qty || 1) > 1 ? 's' : ''} with ${pocketText}, ${detail}, Color: ${o.color}`;
    }).join('; ');

    const first = fields.firstName.value.trim();
    const last = fields.lastName.value.trim();
    const apt = document.getElementById('aptNumber')?.value.trim() || '';
    const addr = `${fields.streetAddress.value.trim()}${apt ? ' Apt ' + apt : ''}, ${fields.city.value.trim()}, ${fields.state.value.trim()} ${fields.zipCode.value.trim()}`;
    const phone = fields.phoneNumber.value.trim();

    console.log('Order placed:', {
        customer: { first, last, address: addr, phone },
        items
    });

    state.orders.length = 0;
    updateCartBadge();

    elements.purchaseShirt.innerHTML = `
        <div class="thankYou">
            <h2>Thank you, ${first} ${last}!</h2>
            <p>You ordered ${itemsText || 'no items'}.</p>
            <p>They will arrive at ${addr} within a week.</p>
            <p>If we have any questions, we'll contact you at ${phone}.</p>
        </div>
    `;
}

// ==================== DRAG & DROP ====================
/**
 * Gets the current shirt selection as an object
 */
function getCurrentShirtSelection() {
    const selections = getSelections();
    return {
        size: selections.size || 'Not chosen',
        color: selections.color,
        pocket: selections.pocket,
        logo: selections.text ? 'none (text only)' : (selections.logo || 'No logo'),
        text: selections.text || 'No text'
    };
}

/**
 * Binds drag and drop event handlers to the shirt element
 */
function bindDragHandlers() {
    const makeShirt = document.getElementById('makeShirt');
    if (!makeShirt) return;

    makeShirt.setAttribute('draggable', 'true');

    makeShirt.addEventListener('dragstart', (e) => {
        if (!canDrag()) {
            const sizeChoice = document.querySelector('input[name="shirtSize"]:checked')?.value;
            const sizeMsg = document.querySelector('.sizeRadio small') || elements.errorSize;

            if (!sizeChoice && sizeMsg) {
                sizeMsg.textContent = 'Please choose a size before adding to cart.';
                sizeMsg.style.color = 'red';
            }
            updateStatus();
            e.preventDefault();
            return;
        } else {
            const sizeMsg = document.querySelector('.sizeRadio small') || elements.errorSize;
            if (sizeMsg) sizeMsg.textContent = '';
        }

        makeShirt.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'copy';

        const payload = JSON.stringify(getCurrentShirtSelection());
        e.dataTransfer.setData('application/json', payload);
    });

    makeShirt.addEventListener('dragend', () => {
        makeShirt.classList.remove('dragging');
        elements.cartImg.classList.remove('drag-over');
    });
}
elements.cartImg.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    elements.cartImg.classList.add('drag-over');
});

elements.cartImg.addEventListener('dragleave', () => {
    elements.cartImg.classList.remove('drag-over');
});

elements.cartImg.addEventListener('drop', (e) => {
    e.preventDefault();
    elements.cartImg.classList.remove('drag-over');

    let data;
    try {
        data = JSON.parse(e.dataTransfer.getData('application/json') || '{}');
    } catch {
        data = null;
    }
    if (!data || !data.size) data = getCurrentShirtSelection();

    addOrIncrement(data);
});

// ==================== INITIALIZATION ====================
// Build Shirt button - show shirt designer
if (elements.buildShirtButton) {
    elements.buildShirtButton.addEventListener('click', () => {
        DOM.hide(elements.gettingStarted);
        DOM.show(elements.shirtControls);
        setInputsEnabled(false); 
    });
}

// Start Over / Reset button
if (elements.startOverButton) {
    elements.startOverButton.addEventListener('click', () => {
        rebuildShirt();
        resetForm();
        setInputsEnabled(false);
        state.colorSelected = false;
        updateStatus();
    });
}

bindDragHandlers();
updateStatus();

// Set initial notifications
setNotification(elements.colorNote, 'Pick a color', 'info');
setNotification(elements.sizeNote, 'Choose a size', 'info');

// Set initial instruction message
updateInstructionMessage();
