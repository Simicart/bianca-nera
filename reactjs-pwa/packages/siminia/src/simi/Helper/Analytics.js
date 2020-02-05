import Identify from './Identify';

export const analyticClickGTM = (name, id, price) => {
    try {
        if (window.dataLayer){
            window.dataLayer.push({
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                    'products': [{
                        'name': name,                     
                        'id': id,
                        'price': price,
                        }]
                    }
                    },
                });
        }
    } catch (err) {}
}

export const analyticAddCartGTM = (name, id, price) => {
    try {
        if (window.dataLayer){
            window.dataLayer.push({
                'event': 'addToCart',
                'ecommerce': {
                    'add': {                                
                    'products': [{                        
                        'name': name,
                        'id': id,
                        'price': price,
                        'quantity': 1
                        }]
                    }
                }
                });
        }
    } catch (err) {}
}


export const analyticsViewDetailsGTM = (product) => {
    if (window.dataLayer){
        const {simiExtraField} = product;
        const {attribute_values} = simiExtraField;
        const {entity_id, price, name} = attribute_values;
        dataLayer.push({
            'ecommerce': {
                'event': 'productDetailView',
                'detail': {
                    'products': [{
                    'name': name, 
                    'id': entity_id || 0,
                    'price': price || 0
                    }]
                },
                'event': 'product_detail_view'
            }
        });
    }
}

export const analyticPurchaseGTM = (dataOrder) => {
    try {
        if (window.dataLayer){
            dataLayer.push({
                'ecommerce': {
                    'purchase': {
                        'actionField': {
                            'id': dataOrder.increment_id,
                            'affiliation': 'Bianca Nera Store',
                            'revenue': dataOrder.total.grand_total_incl_tax,
                            'tax':dataOrder.total.tax,
                            'shipping': dataOrder.total.shipping_hand_incl_tax,
                        },
                        'products': dataOrder.order_items
                    }
                }
            });
        }
    } catch (err) {}
}

export const analyticImpressionsGTM = (products, category = '', list_name = '') => {
    console.log('hehehehehe')
    try {
        if (window.dataLayer){
            const storeConfig = Identify.getStoreConfig();
            const config = storeConfig && storeConfig.simiStoreConfig && storeConfig.simiStoreConfig.config || {};
            const currency = storeConfig && storeConfig.simiStoreConfig && storeConfig.simiStoreConfig.currency || 'USD';
            let brands = new Map();
            if (config.brands) {
                config.brands.map((item) => {
                    brands.set(item.option_id, item.name);
                });
            }
            let impressions = [];
            products.map((product, position)=> {
                const extraAttributes = product.simiExtraField && product.simiExtraField.attribute_values || {};
                const price = product.price && (product.price.minimalPrice || product.price.regularPrice) || {};
                let _p_cat = categories && categories.pop() || null;
                const brandId = extraAttributes.brand || '';
                const brand = brands.get(extraAttributes.brand);
                impressions.push({
                    'name': product.name,       // Name or ID is required.
                    'id': product.id,
                    'price': price.amount && price.amount.value,
                    'brand': brand,
                    'category': category || _p_cat && _p_cat.name,
                    'variant': 'Gray',
                    'list': list_name,
                    'position': (position + 1)
                });
                return product;
            });
            console.log(impressions)
            window.dataLayer.push({
                'ecommerce': {
                    'currencyCode': currency,
                    'impressions': impressions
                }
            });
        }
    } catch (err) {}
}