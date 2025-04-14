// Check if we're in a browser environment and have the required dependencies
const validateDependencies = () => {
    if (!window?.wc?.wcSettings || !window?.wp?.htmlEntities || !window?.wp?.i18n || !window?.wp?.element) {
        console.error('Required WooCommerce or WordPress dependencies are missing');
        return false;
    }
    return true;
};

if (!validateDependencies()) {
    // Exit without initializing if dependencies are missing
    throw new Error('Required WooCommerce or WordPress dependencies are missing');
}
const settings = window.wc.wcSettings.getSetting('addi_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('Addi', 'addi');
const icon = settings.icon;
const Content = () => {
    return window.wp.htmlEntities.decodeEntities(settings.description || '');
};

const Block_Gateway = {
    name: 'addi',
    label: window.wp.element.createElement(() =>
        window.wp.element.createElement(
            "span", {
            for: "payment_method_addi",
            style: {
                width: '90%',
                'z-index': '0'
            }
        },
            settings.title + "  ",
            window.wp.element.createElement("img", {
                src: icon,
                alt: settings.title,
                style: {
                    float: 'right',
                    height: '25px'
                }
            }),
        )
    ),
    content: window.wp.element.createElement(
        'div', 
        null,
        window.wp.element.createElement(
            'form', {
                class: 'wc-block-components-text-input wc-block-components-address-form__billing_addi_id is-active',
                style: {
                    margin: '10px'
                }
            }          
        ),
        window.wp.element.createElement('div', {
            dangerouslySetInnerHTML: { __html: settings.description }
        })
    ),
    edit: Object(window.wp.element.createElement)(Content, null),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
