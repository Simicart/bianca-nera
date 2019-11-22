import React, {useEffect, useState, useMemo } from 'react';
// import { Form } from 'informed';
import { array, func, shape, string } from 'prop-types';
import { formatLabelPrice } from 'src/simi/Helper/Pricing';
import Identify from 'src/simi/Helper/Identify';
import Checkbox from '../../BaseComponents/Checkbox'
import Loading from 'src/simi/BaseComponents/Loading/ReactLoading';
import { request } from 'src/simi/Network/RestMagento';
import { Util } from '@magento/peregrine';
require('./ShippingForm.scss')

const SHIPPING_METHOD_SELECTED = 'shipping_method_selected';

/**
 * 
 * @param {vendor} ids 
 */
const useLoadVendor = (availableVendorsMethods) => {
    const [vendors, setVendors] = useState();
    const getVendors = async function(vendorIds) {
        const vendorApi = '/rest/V1/simiconnector/vendors';
        const response = await request(vendorApi, {
            method: 'POST',
            body: JSON.stringify({
                ids: vendorIds.join(',')
            })
        });
        return response;
    }
    const loadVendorApi = async () => {
        if (availableVendorsMethods && availableVendorsMethods.length && !vendors) {
            let vendorIds = availableVendorsMethods.map((vendorMethods) => {
                return vendorMethods.vendor_id;
            });
            let response = await getVendors(vendorIds);
            if (response) {
                setVendors(response);
            }
        }
    };
    useEffect(() => {
        loadVendorApi();
        return () => {
            const controller = new AbortController();
            controller.abort(); //cancel requesting
        }
    }, []);
    return vendors;
}

const ShippingForm = (props) => {
    const { BrowserPersistence } = Util;
    const storage = new BrowserPersistence();
    const {
        availableShippingMethods,
        cancel,
        shippingMethod,
        submit
    } = props;

    // load method selected from storage
    const storedShippingMethod = storage.getItem('shippingMethod');
    // const initialValue = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, SHIPPING_METHOD_SELECTED);
    let initialSelected = {}
    if (storedShippingMethod && storedShippingMethod.method_code) {
        let selectedMethodsArray = storedShippingMethod.method_code.replace(/vflatrate_/g, '').split('|_|');
        for(let i = 0; i < selectedMethodsArray.length; i++){
            let vendorIdRate = selectedMethodsArray[i].split('||');
            if (vendorIdRate[1] != undefined) {
                initialSelected[vendorIdRate[1]] = selectedMethodsArray[i];
            }
        }
    }
    const [methodCodesSelected, setMethodCodesSelected] = useState(initialSelected);
    let availableVendorsMethods = [];
    const defaultMethod = { value: '', label: Identify.__('Please choose') }
    const vendors = useLoadVendor(availableVendorsMethods);

    // convert availableShippingMethods to vendor shipping methods
    availableVendorsMethods = useMemo(() => {
        if (availableShippingMethods.length) {
            availableShippingMethods.map(
                (shippingMethod) => {
                    // availableVendorsMethods
                    let { carrier_code, carrier_title, method_code, method_title, price_incl_tax } = shippingMethod;
                    if (carrier_code === "vendor_multirate") {
                        return false
                    }
                    
                    if (method_code) {
                        let methodCode = method_code.split('||');
                        if (methodCode[1] !== undefined){
                            let index;
                            let vendorMethod = availableVendorsMethods.find((vendor, i) => {
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
                    return shippingMethod;
                }
            );
            if (!availableVendorsMethods) return [];
            return availableVendorsMethods;
        }
        return [];
    }, [availableShippingMethods]);

    const handleSubmit2 = (selectedMethods) => {
        // get multi vendor shipping method
        let vendor_multirate = ''; //multiple rates code
        let selecteds = Object.values(selectedMethods); //array
        if (availableVendorsMethods && availableVendorsMethods.length === selecteds.length) {
            vendor_multirate = selecteds.join('|_|');
            // console.log('SELECTED SHIPPING CODE: ', vendor_multirate);
            // find method object available
            let shippingMethod = availableShippingMethods.find(
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
            // console.log('FOUND SHIPPING METHHOD: ', shippingMethod);
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
                    let foundVendorMethod = availableVendorsMethods.find((vendorMethod) => {
                        if (vendorMethod.vendor_id === venId) return true;
                        return false;
                    });
                    if (!foundVendorMethod) {
                        selecteds = {}
                    }
                }
            }
        }
        selecteds[action.vendor_id] = action.method_code;
        setMethodCodesSelected(selecteds);
        handleSubmit2(selecteds);
    }

    if (!availableVendorsMethods || !availableVendorsMethods.length) {
        return <Loading />
    }

    return (
        <form className="shipping-form">
            <div className="shipping-body">
                {
                    availableVendorsMethods.map((vendor, vendor_key) => {
                        let {rates, carrier_title, vendor_id} = vendor;
                        rates.sort((a, b) => (a.order > b.order) ? 1 : -1); //sort
                        rates.unshift(defaultMethod);
                        let vendorName = carrier_title && vendor_id !== 'default' && Identify.__(`Vendor ${vendor_id}`) || Identify.__('Default');
                        if(vendors){
                            const vendor = vendors.find(({entity_id}) => parseInt(entity_id) === parseInt(vendor_id));
                            vendorName = vendor ? (vendor.firstname + (vendor.lastname ? ` ${vendor.lastname}` : '')) : Identify.__('Default');
                        }
                        return <div key={vendor_key}>
                            <span>{vendorName}</span>
                            {rates.map((rate) => {
                                if(!rate.id){
                                    return null;
                                }
                                let selected = (methodCodesSelected[vendor_id] === rate.value);
                                return(
                                    <Checkbox 
                                        key={rate.id} 
                                        label={rate.label} 
                                        value={rate.value} 
                                        onClick={() => methodSelecteHandle({vendor_id, method_code: rate.value})}
                                        // onChange={(value) => handleSubmit(value)}
                                        selected={selected}
                                    />
                                )
                            })}
                        </div>
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
