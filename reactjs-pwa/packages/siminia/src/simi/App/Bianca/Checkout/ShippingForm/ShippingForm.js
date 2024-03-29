import React, {useState, useMemo } from 'react';
// import { Form } from 'informed';
import { array, func, string } from 'prop-types';
import { formatLabelPrice } from 'src/simi/Helper/Pricing';
import Identify from 'src/simi/Helper/Identify';
import Checkbox from '../../BaseComponents/Checkbox'
import Loading from 'src/simi/BaseComponents/Loading/ReactLoading';
import { Util } from '@magento/peregrine';
import Shippingproduct from './Shippingproduct'
require('./ShippingForm.scss')

const SHIPPING_METHOD_SELECTED = 'shipping_method_selected';

const ShippingForm = (props) => {
    const { BrowserPersistence } = Util;
    const storage = new BrowserPersistence();
    const {
        availableShippingMethods,
        simiCheckoutUpdating,
        submitting,
        cancel,
        getCartDetails,
        submit,
        cart
    } = props;

    let vendors = false
    const storeConfig = Identify.getStoreConfig()
    if (storeConfig && storeConfig.simiStoreConfig && storeConfig.simiStoreConfig.config && storeConfig.simiStoreConfig.config.vendor_list) {
        vendors = storeConfig.simiStoreConfig.config.vendor_list;
    }

    // load method selected from storage
    const storedShippingMethod = storage.getItem('shippingMethod');
    // const initialValue = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, SHIPPING_METHOD_SELECTED);
    const initialSelected = {}
    if (storedShippingMethod && storedShippingMethod.method_code) {
        const selectedMethodsArray = storedShippingMethod.method_code.replace(/vflatrate_/g, '').split('|_|');
        for(let i = 0; i < selectedMethodsArray.length; i++){
            const vendorIdRate = selectedMethodsArray[i].split('||');
            if (vendorIdRate[1] != undefined) {
                initialSelected[vendorIdRate[1]] = selectedMethodsArray[i];
            }
        }
        // method code selected other
        if (Object.values(initialSelected).length < 1) {
            initialSelected[storedShippingMethod.carrier_code] = storedShippingMethod.method_code;
        }
    }
    const [methodCodesSelected, setMethodCodesSelected] = useState(initialSelected);
    let availableVendorsMethods = [];
    let availableOtherMethods = [];
    const defaultMethod = { value: '', label: Identify.__('Please choose') }

    // convert availableShippingMethods to vendor shipping methods
    availableVendorsMethods = useMemo(() => {
        if (availableShippingMethods.length) {
            availableShippingMethods.map((shippingMethod) => {
                // availableVendorsMethods
                const { carrier_code, carrier_title, method_code, method_title, price_incl_tax } = shippingMethod;
                if (carrier_code === "vendor_multirate") {
                    return false
                }
                // Flat rate, freeshipping for vendor or admin (shop) products
                if (method_code && ['vflatrate', 'freeshipping'].includes(carrier_code)) {
                    const methodCode = method_code.split('||');
                    if (methodCode[1] !== undefined){
                        let index;
                        const vendorMethod = availableVendorsMethods.find((vendor, i) => {
                            if (vendor.vendor_id === methodCode[1]) {
                                index = i;
                                return true;
                            }
                            return false;
                        });
                        let rate = {
                            id: methodCode[0],
                            order: price_incl_tax,
                            value: method_code,
                            label: `${method_title} (${formatLabelPrice(price_incl_tax)})`
                        }
                        if (vendorMethod === undefined) {
                            availableVendorsMethods.push({
                                vendor_id: methodCode[1],
                                carrier_code: carrier_code,
                                carrier_title: carrier_title,
                                rates: [rate]
                            });
                        }
                        if (vendorMethod) {
                            if (vendorMethod.rates) vendorMethod.rates.push(rate);
                            availableVendorsMethods[index].rates = vendorMethod.rates;
                        }
                    } 
                }
                return null;
            });

            if (!availableVendorsMethods) return [];
            return availableVendorsMethods;
        }
        return [];
    }, [availableShippingMethods]);

    // convert availableShippingMethods to other shipping methods
    availableOtherMethods = useMemo(() => {
        if (availableShippingMethods.length) {
            let otherMethods = {};
            availableShippingMethods.map((shippingMethod) => {
                // availableVendorsMethods
                const { carrier_code, carrier_title, method_code, method_title, price_incl_tax } = shippingMethod;
                // Ignore not is other method (is vendor flat rate method)
                if (['vendor_multirate', 'vflatrate'].includes(carrier_code)) {
                    return false;
                }
                // Ignore invalid method
                if (!carrier_code || !method_code) return false;
                // Add other shipping method and all rates, example DHL has multiple rates
                if (!otherMethods[carrier_code]) {
                    otherMethods[carrier_code] = {
                        vendor_id: carrier_code,
                        carrier_code: carrier_code,
                        carrier_title: carrier_title,
                        rates: [{
                            id: method_code,
                            order: price_incl_tax,
                            value: method_code,
                            label: `${method_title} (${formatLabelPrice(price_incl_tax)})`
                        }]
                    }
                } else {
                    // If available method in otherMethods then add to rates
                    otherMethods[carrier_code].rates.push({
                        id: method_code,
                        order: price_incl_tax,
                        value: method_code,
                        label: `${method_title} (${formatLabelPrice(price_incl_tax)})`
                    });
                }
                return null;
            });

            // Add available other methods from Object to array
            availableOtherMethods = Object.values(otherMethods);

            if (!availableOtherMethods) return [];
            return availableOtherMethods;
        }
        return [];
    }, [availableShippingMethods]);

    const handleSubmit2 = (selectedMethods) => {
        // get multi vendor shipping method
        let vendor_multirate = ''; //multiple rates code
        const selecteds = Object.values(selectedMethods); //array
        if (availableVendorsMethods && availableVendorsMethods.length === selecteds.length) {
            vendor_multirate = selecteds.join('|_|');
            // find method object available
            const shippingMethod = availableShippingMethods.find(
                ({ method_code, carrier_code }) => {
                    if (carrier_code !== "vendor_multirate") {
                        return false
                    }
                    if (selecteds.length === method_code.split('|_|').length) {
                        for (let code in selectedMethods) {
                            if (method_code.indexOf(selectedMethods[code]) === -1) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                }
            );
            if (shippingMethod) {
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, SHIPPING_METHOD_SELECTED, shippingMethod.method_code);
                submit({ shippingMethod: shippingMethod });
            }
            else {
                console.warn(
                    `Could not find the selected shipping method ${vendor_multirate} in the list of available shipping methods.`
                );
                cancel();
                return;
            }
            return true;
        }
        return false;
    }

    const handleSubmitOther = (selectedMethods) => {
        const selecteds = Object.values(selectedMethods); //array
        // find method object available
        const shippingMethod = availableShippingMethods.find(
            ({ method_code, carrier_code }) => {
                if (method_code === selectedMethods[carrier_code]) {
                    return true;
                }
                return false;
            }
        );
        if (shippingMethod) {
            Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, SHIPPING_METHOD_SELECTED, shippingMethod.method_code);
            submit({ shippingMethod: shippingMethod });
        }
        else {
            console.warn(
                `Could not find the selected shipping method ${selecteds.join(', ')} in the list of available shipping methods.`
            );
            cancel();
            return;
        }
        return true;
    }

    /**
     * Handle select shipping method
     * @param {*} action 
     */
    const methodSelecteHandle = (action) => {
        let selecteds = {...methodCodesSelected};
        // check no vendor in cart and remove initial state
        if (selecteds) {
            for(let venId in selecteds){
                if (availableVendorsMethods && availableVendorsMethods.length) {
                    const foundVendorMethod = availableVendorsMethods.find((vendorMethod) => {
                        if (vendorMethod.vendor_id === venId) return true;
                        return false;
                    });
                    if (!foundVendorMethod) {
                        selecteds = {}
                    }
                }
            }
        }
        if (action && action.vendor_id && action.method_code)
            selecteds[action.vendor_id] = action.method_code;
        setMethodCodesSelected(selecteds);
        handleSubmit2(selecteds);
    }

    /**
     * Handle select for other shipping methods
     * @param {*} action 
     */
    const methodSelecteHandleOther = (action) => {
        let selecteds = {};
        if (action && action.vendor_id && action.method_code)
            selecteds[action.vendor_id] = action.method_code;
        setMethodCodesSelected(selecteds);
        handleSubmitOther(selecteds);
    }
    
    if ((!availableOtherMethods || !availableOtherMethods.length) && (!availableVendorsMethods || !availableVendorsMethods.length)) {
        return <Loading />
    }

    return (
        <form className="shipping-form">
            <div className="shipping-body">
                {availableOtherMethods && availableOtherMethods.length > 0 &&
                    availableVendorsMethods && availableVendorsMethods.length > 1 &&
                    <div className="group-title">{Identify.__('Please choose shipping method for each product')}</div>
                }
                {
                    availableVendorsMethods.map((vendor, vendor_key) => {
                        const {rates, carrier_title, vendor_id} = vendor;
                        rates.sort((a, b) => (a.order > b.order) ? 1 : -1); //sort
                        {/* rates.unshift(defaultMethod); */}
                        let vendorName = carrier_title && vendor_id !== 'default' && Identify.__(`Vendor ${vendor_id}`) || Identify.__('Default');
                        let designer = null
                        if(vendors)
                            designer = vendors.find(({entity_id}) => parseInt(entity_id) === parseInt(vendor_id))

                        if (!designer) {
                            designer = {entity_id: 'default'}
                            vendorName = Identify.__('Default');
                        } else {
                            vendorName = ((designer.profile && designer.profile.store_name) ? designer.profile.store_name : (designer.firstname + (designer.lastname ? ` ${designer.lastname}` : '')))
                        }
                        return (
                            <div key={vendor_key} className="shipping-vendor">
                                <span className="shipping-vendor-name">{vendorName}</span>
                                <div className="items"><Shippingproduct designer={designer} cart={cart} getCartDetails={getCartDetails} methodSelecteHandle={methodSelecteHandle} /></div>
                                {rates.map((rate) => {
                                    if(!rate.id){
                                        return null;
                                    }
                                    const selected = (methodCodesSelected[vendor_id] === rate.value);
                                    return(
                                        <Checkbox 
                                            key={rate.id} 
                                            label={rate.label} 
                                            value={rate.value} 
                                            onClick={() => methodSelecteHandle({vendor_id, method_code: rate.value})}
                                            // onChange={(value) => handleSubmit(value)}
                                            selected={selected}
                                            className="select-shipping-checkbox"
                                            classes={{
                                                label: 'select_shipping_checkbox_label',
                                                icon: 'select_shipping_checkbox_icon'
                                            }}
                                        />
                                    )
                                })}
                            </div>
                        )
                    })
                }
                { availableOtherMethods && availableOtherMethods.length > 0 &&
                    availableVendorsMethods && availableVendorsMethods.length > 1 &&
                    <div className="group-title">
                        {Identify.__('Or')}<br/>
                        {Identify.__('Use one of the following shipping methods for all products')}
                    </div>
                }
                {
                    availableOtherMethods.map((method, vendor_key) => {
                        const {rates, carrier_title, vendor_id} = method;
                        return (
                            <div key={vendor_key} className="shipping-vendor">
                                <span className="shipping-vendor-name">{Identify.__(carrier_title)}</span>
                                {rates.map((rate) => {
                                    if(!rate.id) return null;
                                    const selected = (methodCodesSelected[vendor_id] === rate.value);
                                    return (
                                        <Checkbox 
                                            key={rate.id} 
                                            label={rate.label} 
                                            value={rate.value} 
                                            onClick={() => methodSelecteHandleOther({vendor_id, method_code: rate.value})}
                                            selected={selected}
                                            className="select-shipping-checkbox"
                                            classes={{
                                                label: 'select_shipping_checkbox_label',
                                                icon: 'select_shipping_checkbox_icon'
                                            }}
                                        />
                                    )
                                })}
                            </div>
                        )
                    })
                }
            </div>
        </form>
    );
};

ShippingForm.propTypes = {
    availableShippingMethods: array.isRequired,
    cancel: func.isRequired,
    shippingMethod: string,
    submit: func.isRequired
};

ShippingForm.defaultProps = {
    availableShippingMethods: [{}]
};

export default ShippingForm;
