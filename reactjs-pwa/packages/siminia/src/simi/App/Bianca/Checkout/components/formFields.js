import React, { useCallback, Fragment, useState } from 'react';
import { Util } from '@magento/peregrine';
import Checkbox from 'src/components/Checkbox';
import Button from 'src/components/Button';
import { Colorbtn } from 'src/simi/BaseComponents/Button'
import Select from 'src/components/Select';
import Identify from 'src/simi/Helper/Identify';
import { checkExistingCustomer, simiSignIn } from 'src/simi/Model/Customer';
import isObjectEmpty from 'src/util/isObjectEmpty';
import { Link } from 'react-router-dom';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';
import * as Constants from 'src/simi/Config/Constants'
import LoadingImg from 'src/simi/BaseComponents/Loading/LoadingImg';
import { smoothScrollToView } from 'src/simi/Helper/Behavior';
import Icon from 'src/components/Icon';
import ChevronDownIcon from 'react-feather/dist/icons/chevron-down';
const arrow = <Icon src={ChevronDownIcon} size={18} />;
const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();
require('./formFields.scss')

const listState = (states) => {
    let html = null;
    if (states && states.length) {
        html = states.map(itemState => {
            return { value: itemState.code, label: itemState.name, key: Identify.randomString(3) };
        });
        const ctoState = { value: '', label: Identify.__('Please choose'), key: Identify.randomString(3) };
        html.unshift(ctoState);
    }
    return html;
}

const renderRegionField = (selectedCountry, initialCountry, countries, configFields, initialValues) => {
    const country_id = (selectedCountry !== -1) ? selectedCountry : initialCountry
    if (!country_id || !countries) return
    const country = countries.find(({ id }) => id === country_id);
    if (!country) return
    const { available_regions: regions } = country;
    return (
        <div className='region_code'>
            <div className={`address-field-label ${configFields.region_id_show === 'req' ? 'req' : ''}`}>{Identify.__("State")}</div>
            {
                (country.available_regions && Array.isArray(regions) && regions.length) ?
                    <Select
                        initialValue={initialValues.region_code}
                        key={Identify.randomString(3) + initialValues.region_code}
                        field="region_code" items={listState(regions)}
                        isrequired={(!configFields || (configFields && configFields.hasOwnProperty('region_id_show') && configFields.region_id_show === 'req')) ? 'isrequired' : ''}
                    /> :
                    <input type="text" id='region_code' name='region_code'
                        className={configFields.region_id_show === 'req' ? 'isrequired' : ''} defaultValue={initialValues.region_code}></input>
            }
        </div>
    )
}

const FormFields = (props) => {
    const {
        formId,
        billingForm,
        validationMessage,
        initialCountry,
        selectableCountries,
        submitting,
        submit,
        user,
        billingAddressSaved,
        submitBilling,
        simiSignedIn,
        countries,
        configFields,
        handleFormReset,
        is_virtual,
        initialValues,
        beginCheckout
    } = props;

    const { isSignedIn, currentUser } = user;

    const { addresses, default_billing, default_shipping } = currentUser;

    const checkCustomer = false;

    const [shippingNewForm, setShippingNewForm] = useState(false);
    const [handlingEmail, setHandlingEmail] = useState(false)
    const [existCustomer, setExistCustomer] = useState(checkCustomer);
    const [selectedCountry, setSelectedCountry] = useState(-1);
    const [usingSameAddress, setUsingSameAddress] = useState(billingForm === true);

    const storageShipping = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, 'shipping_address');
    const storageBilling = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, 'billing_address');

    const initialShipping = !billingForm && isSignedIn && storageShipping ? storageShipping : default_shipping ? default_shipping : null;
    const initialBilling = billingForm && isSignedIn && storageBilling ? storageBilling : default_billing ? default_billing : null;

    const resetForm = useCallback(
        () => {
            handleFormReset()
        },
        [handleFormReset]
    )

    const handleSubmitBillingSameFollowShipping = useCallback(
        () => {
            const billingAddress = {
                sameAsShippingAddress: true
            }
            submitBilling(billingAddress);
        },
        [submitBilling]
    )

    const handleChooseAddedAddress = (defaultValue) => {
        const selected_address_field = $(`#${formId} select[name=selected_address_field]`).val()
        if (selected_address_field !== 'new_address' && addresses) {
            if ((parseInt(selected_address_field) === parseInt(defaultValue)))
                return
            setShippingNewForm(false);
            let shippingFilter = addresses.find(
                ({ id }) => id === parseInt(selected_address_field, 10)
            );
            if (shippingFilter) {
                if (!shippingFilter.email) shippingFilter.email = currentUser.email;

                if (shippingFilter.id) {
                    if (billingForm) {
                        Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'billing_address', shippingFilter.id);
                    } else {
                        Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'shipping_address', shippingFilter.id);
                    }
                }
                shippingFilter.save_in_address_book = 0

                // convert custom_attributes to extension_attributes
                let extensionAttributes = {}
                if (shippingFilter.custom_attributes instanceof Object) {
                    for (let attrCode in shippingFilter.custom_attributes) {
                        const customAttr = shippingFilter.custom_attributes[attrCode];
                        if (customAttr.attribute_code) {
                            extensionAttributes[customAttr.attribute_code] = customAttr.value;
                        }
                    }
                    shippingFilter.extension_attributes = extensionAttributes;
                    delete shippingFilter.custom_attributes;
                }

                handleSubmit(shippingFilter);
                if (!billingForm && !billingAddressSaved) {
                    handleSubmitBillingSameFollowShipping();
                }
            }
        } else {
            if (billingForm) {
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'billing_address', 'new_address');
            } else {
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'shipping_address', 'new_address');
            }
            setShippingNewForm(true);
            resetForm();
        }
    }

    const handleSubmit = useCallback(
        values => {
            if (values.hasOwnProperty('selected_address_field')) delete values.selected_address_field
            if (values.hasOwnProperty('password')) delete values.password
            if (values.hasOwnProperty('default_billing')) delete values.default_billing
            if (values.hasOwnProperty('default_shipping')) delete values.default_shipping
            if (values.save_in_address_book) {
                values.save_in_address_book = 1;
            } else {
                values.save_in_address_book = 0;
            }
            // fix country no region
            if (values.region && !values.region.region_id) {
                values.region = "";
            }
            submit(values);
        },
        [submit]
    );

    const processData = (data) => {
        setHandlingEmail(false);
        if (data.hasOwnProperty('customer') && !isObjectEmpty(data.customer) && data.customer.email) {
            setExistCustomer(true);
        } else {
            setExistCustomer(false);
        }
    }

    const checkMailExist = () => {
        const email = $(`#${formId} input[name=emailaddress]`).val()
        if (!email || !Identify.validateEmail(email)) return;
        setHandlingEmail(true);
        checkExistingCustomer(processData, email)
    }

    const handleActionSignIn = useCallback(
        (value) => {
            simiSignedIn(value);
            beginCheckout();
            //delay 500ms to reload page
            setTimeout(() => window.location.reload(false), 500);

        },
        [simiSignedIn]
    )

    const setDataLogin = (data) => {
        hideFogLoading();
        if (data && !data.errors) {
            if (data.customer_access_token) {
                Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, data.customer_identity)
                setToken(data.customer_access_token)
                handleActionSignIn(data.customer_access_token)
            } else {
                setToken(data)
                handleActionSignIn(data)
            }
        } else {
            smoothScrollToView($("#id-message"));
            if (props.toggleMessages) {
                props.toggleMessages([{ type: 'error', message: Identify.__('The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.'), auto_dismiss: true }])
            }
        }
    }

    const handleSignIn = () => {
        const email = $(`#${formId} input[name=emailaddress]`).val()
        const password = $(`#${formId} input[name=password]`).val()
        if (!email || !password || !email.trim() || !password.trim()) {
            smoothScrollToView($("#id-message"));
            if (props.toggleMessages) {
                props.toggleMessages([{ type: 'error', message: Identify.__('Email and password is required to login!'), auto_dismiss: true }])
            }
            return;
        }
        const username = email;
        simiSignIn(setDataLogin, { username, password })
        showFogLoading()
    }

    const onHandleSelectCountry = () => {
        const country_id = $(`#${formId} select[name=country_id]`).val()
        setSelectedCountry(country_id)
    }

    const forgotPasswordLocation = {
        pathname: '/login.html',
        state: {
            forgot: true
        }
    }

    const listOptionsAddress = (addresses) => {
        let html = null;
        if (addresses) {
            if (addresses.length) {
                html = addresses.map((address, idx) => {
                    const labelA = address.firstname + ' '+ (address.middlename || '') + ' ' + address.lastname + ', ' + address.city + (address.region.region ? (', ' + address.region.region) : '')
                    return <option value={address.id} key={idx}>{labelA}</option>
                });
            }
        } else {
            /* comment due to continuously calling simiSignedIn
            const signin_token = storage.getItem('signin_token')
            if (signin_token) //logged in but not loaded addresses
                simiSignedIn(signin_token) */
        }
        return <Fragment>
            {/*html && <option value="">{Identify.__('Please choose')}</option>*/}
            {html}
            <option value="new_address">{Identify.__('New Address')}</option>
        </Fragment>;
    }

    const isFieldShow = (field_id) => {
        return !configFields || (configFields && !configFields.hasOwnProperty(field_id)) || (configFields && configFields.hasOwnProperty(field_id) && configFields[field_id] !== false)
    }

    //get init value
    let initDefaultValue = null;
    if (billingForm) {
        if (initialBilling) {
            initDefaultValue = initialBilling
            if (!storageBilling) {
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'billing_address', initDefaultValue)
                if (initDefaultValue !== 'new_address' && (!props.initialValues || !props.initialValues.firstname)) {
                    //delay 500ms to set default option
                    setTimeout(() => {
                        handleChooseAddedAddress()
                    }, 500);
                }
            }
        } else {
            if (addresses && addresses.length && addresses[0] && addresses[0].id) {
                let initAddressToSave = addresses[0]
                if (currentUser && currentUser.default_billing) {
                    addresses.map(address => {
                        if (parseInt(address.id) === parseInt(currentUser.default_billing))
                            initAddressToSave = address
                    })
                }
                initDefaultValue = initAddressToSave.id
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'billing_address', initAddressToSave.id)
                if (!initAddressToSave.email) initAddressToSave.email = currentUser.email;
                initAddressToSave.save_in_address_book = 0
                handleSubmit(initAddressToSave);
            }
        }
    } else {
        if (initialShipping) {
            initDefaultValue = initialShipping
            if (!storageShipping) {
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'shipping_address', initDefaultValue)
                if (initDefaultValue !== 'new_address' && (!props.initialValues || !props.initialValues.firstname)) {

                    //delay 500ms to set default option
                    setTimeout(() => {
                        handleChooseAddedAddress()
                    }, 500);
                }
            }
        } else {
            if (addresses && addresses.length && addresses[0] && addresses[0].id) {
                let initAddressToSave = addresses[0]
                if (currentUser && currentUser.default_shipping) {
                    addresses.map(address => {
                        if (parseInt(address.id) === parseInt(currentUser.default_shipping))
                            initAddressToSave = address
                    })
                }
                initDefaultValue = initAddressToSave.id
                Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'shipping_address', initAddressToSave.id)
                if (!initAddressToSave.email) initAddressToSave.email = currentUser.email;
                initAddressToSave.save_in_address_book = 0
                handleSubmit(initAddressToSave);
                if (!billingAddressSaved) {
                    handleSubmitBillingSameFollowShipping();
                }
            }
        }
    }
    
    const addressFieldChangedValue = (e) => {
        if (e && e.target) {
            if (!initialValues[e.target.id] || initialValues[e.target.id] !== e.target.value) {
                if(props.onSubmit && !submitting) {
                    props.onSubmit('formFieldChanged')
                }
            }
        }
    }

    const extension = initialValues.extension_attributes || {};

    const viewFields = (!usingSameAddress || is_virtual) ? (
        <Fragment>
            {isSignedIn &&
                <div className='shipping_address'>
                    <div className={`address-field-label ${(!configFields || configFields.country_id_show === 'req') ? 'req' : ''}`}>{billingForm ? Identify.__("Select Billing") : Identify.__("Select Shipping")}</div>
                    <span className="selectSavedAddress">
                        <span className="selectSavedAddressInput">
                            <select name="selected_address_field"
                                defaultValue={initDefaultValue}
                                onChange={() => handleChooseAddedAddress(initDefaultValue)}
                                // onBlur={() => handleChooseAddedAddress(initDefaultValue)}
                                >
                                {listOptionsAddress(addresses)}
                            </select>
                        </span>
                        <span className="selectSavedAddressAfter">{arrow}</span>
                    </span>
                </div>
            }
            {
                (
                    !isSignedIn || shippingNewForm || //not signed in or signed it and choose to add new address
                    (
                        (billingForm && storageBilling === 'new_address') || //add new billing address
                        (!billingForm && storageShipping === 'new_address') //add new shipping address
                    ) ||
                    (addresses && !addresses.length) //logged in but has no address
                ) ?
                    <Fragment>
                        {!isSignedIn && <div className='email'>
                            <div className={`address-field-label req`}>{Identify.__("Email")}</div>
                            <input
                                type="email" name="emailaddress" className="isrequired" id='email'
                                onBlur={() => (!billingForm || is_virtual) && !user.isSignedIn && checkMailExist()}
                                defaultValue={initialValues.email}
                            />
                            {handlingEmail && <LoadingImg divStyle={{ marginTop: 5 }} />}
                        </div>}
                        {(!isSignedIn && existCustomer) && <Fragment>
                            <div className='password'>
                                <div className={`address-field-label req`}>{Identify.__("Password")}</div>
                                <input id="password" type="password" name="password" className="isrequired" />
                                <span className="existed-account-label">{Identify.__('You already have an account with us. Sign in or continue as guest')}</span>
                            </div>
                            <div className='btn_login_exist'>
                                <Colorbtn
                                    style={{ backgroundColor: '#101820', color: '#FFF' }}
                                    className="address-signin-button"
                                    onClick={() => handleSignIn()}
                                    text={Identify.__('Login')} />
                                <Link to={forgotPasswordLocation} className="address-forgot-pw-link">{Identify.__('Forgot password?')}</Link>
                            </div>
                        </Fragment>
                        }
                        <div className='firstname'>
                            <div className={`address-field-label req`}>{Identify.__("First Name")}</div>
                            <input type="text" id='firstname' name='firstname' className="isrequired" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("First Name")} defaultValue={initialValues.firstname}></input>
                        </div>
                        <div className='middlename'>
                            <div className={`address-field-label req`}>{Identify.__("Middle Name")}</div>
                            <input type="text" id='middlename' name='middlename' className="isrequired" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("Middle Name")} defaultValue={initialValues.middlename}></input>
                        </div>
                        <div className='lastname'>
                            <div className={`address-field-label req`}>{Identify.__("Last Name")}</div>
                            <input type="text" id='lastname' name='lastname' className="isrequired" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("Last Name")} defaultValue={initialValues.lastname}></input>
                        </div>
                        
                        {/* <div className='house_number'>
                            <div className={`address-field-label`}>{Identify.__("House Number")}</div>
                            <input type="text" id='house_number' name='extension_attributes[house_number]' className="" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("House Number")} defaultValue={extension.house_number}></input>
                        </div>
                        <div className='apartment_number '>
                            <div className={`address-field-label`}>{Identify.__("Apartment Number")}</div>
                            <input type="text" id='apartment_number' name='extension_attributes[apartment_number]' className="" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("Apartment Number")} defaultValue={extension.apartment_number}></input>
                        </div> */}

                        {configFields && configFields.hasOwnProperty('company_show') && configFields.company_show ?
                            <div className='company'>
                                <div className={`address-field-label ${configFields.company_show === 'req' ? 'req' : ''}`}>{Identify.__("Company")}</div>
                                <input type="text" id='company' name='company' className={configFields.company_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Company")} defaultValue={initialValues.company}></input>
                            </div>
                            : null}
                        {isFieldShow('street_show') &&
                            <div className='street0'>
                                <div className={`address-field-label ${configFields.street_show === 'req' ? 'req' : ''}`}>{Identify.__("Street")}</div>
                                <input type="text" id='street[0]' name='street[0]' className={configFields.street_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Street")} defaultValue={(initialValues.street && initialValues.street[0]) ? initialValues.street[0] : ''}></input>
                                <input type="text" id='street[1]' name='street[1]'
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("House Number")} defaultValue={(initialValues.street && initialValues.street[1]) ? initialValues.street[1] : ''}></input>
                                <input type="text" id='street[2]' name='street[2]'
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Apartment Number")} defaultValue={(initialValues.street && initialValues.street[2]) ? initialValues.street[2] : ''}></input>
                            </div>}
                        {isFieldShow('city_show') &&
                            <div className='city'>
                                <div className={`address-field-label ${configFields.city_show === 'req' ? 'req' : ''}`}>{Identify.__("City")}</div>
                                <input type="text" id='city' name='city' className={configFields.city_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("City")} defaultValue={initialValues.city}></input>
                            </div>}
                        {isFieldShow('zipcode_show') &&
                            <div className='postcode'>
                                <div className={`address-field-label ${configFields.zipcode_show === 'req' ? 'req' : ''}`}>{Identify.__("ZIP")}</div>
                                <input type="text" id='postcode' name='postcode' className={configFields.zipcode_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("ZIP")} defaultValue={initialValues.postcode}></input>
                            </div>}
                        {isFieldShow('country_id_show') &&
                            <div className='country'>
                                <div className={`address-field-label ${(!configFields || configFields.country_id_show === 'req') ? 'req' : ''}`}>{Identify.__("Country")}</div>
                                <Select
                                    field="country_id"
                                    key={initialCountry /*change key to change initial value*/}
                                    initialValue={initialCountry}
                                    onChange={() => onHandleSelectCountry()} onBlur={() => onHandleSelectCountry()}
                                    items={selectableCountries}
                                    isrequired={(!configFields || (configFields && configFields.hasOwnProperty('country_id_show') && configFields.country_id_show === 'req')) ? 'isrequired' : ''}
                                />
                            </div>}
                        {isFieldShow('region_id_show') && renderRegionField(selectedCountry, initialCountry, countries, configFields, initialValues)}
                        <div className='block'>
                            <div className={`address-field-label`}>{Identify.__("Block")}</div>
                            <input type="text" id='block' name='extension_attributes[block]' className="" onBlur={e => addressFieldChangedValue(e)} 
                                placeholder={Identify.__("Block")} defaultValue={extension.block}></input>
                        </div>
                        {isFieldShow('telephone_show') &&
                            <div className='telephone'>
                                <div className={`address-field-label ${configFields.telephone_show === 'req' ? 'req' : ''}`}>{Identify.__("Phone")}</div>
                                <input type="tel" id='telephone' name='telephone' className={configFields.telephone_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Ex")+": (+965) 5162 - 3283"} defaultValue={initialValues.telephone}></input>
                            </div>}
                        {configFields && configFields.hasOwnProperty('fax_show') && configFields.fax_show ?
                            <div className='fax'>
                                <div className={`address-field-label ${configFields.fax_show === 'req' ? 'req' : ''}`}>{Identify.__("Fax")}</div>
                                <input type="tel" id='fax' name='fax' className={configFields.fax_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Fax")} defaultValue={initialValues.fax}></input>
                            </div>
                            : null}
                        {configFields && configFields.hasOwnProperty('prefix_show') && configFields.prefix_show ?
                            <div className='prefix'>
                                <div className={`address-field-label ${configFields.prefix_show === 'req' ? 'req' : ''}`}>{Identify.__("Prefix")}</div>
                                <input type="text" id='prefix' name='prefix' className={configFields.prefix_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Prefix")} defaultValue={initialValues.prefix}></input>
                            </div>
                            : null}
                        {configFields && configFields.hasOwnProperty('suffix_show') && configFields.suffix_show ?
                            <div className='suffix'>
                                <div className={`address-field-label ${configFields.suffix_show === 'req' ? 'req' : ''}`}>{Identify.__("Suffix")}</div>
                                <input type="text" id='suffix' name='suffix' className={configFields.suffix_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("Suffix")} defaultValue={initialValues.suffix}></input>
                            </div>
                            : null}
                        {configFields && configFields.hasOwnProperty('taxvat_show') && configFields.taxvat_show ?
                            <div className='vat_id'>
                                <div className={`address-field-label ${configFields.taxvat_show === 'req' ? 'req' : ''}`}>{Identify.__("VAT")}</div>
                                <input type="text" id='vat_id' name='vat_id' className={configFields.taxvat_show === 'req' ? 'isrequired' : ''}
                                    onBlur={e => addressFieldChangedValue(e)} 
                                    placeholder={Identify.__("VAT")} defaultValue={initialValues.vat_id}></input>
                            </div>
                            : null}
                        <div className='save_in_address_book'>
                            <Checkbox field="save_in_address_book" label={Identify.__('Save in address book.')}
                                classes={{
                                    label: 'save_in_address_book_label',
                                    icon: 'save_in_address_book_icon'
                                }}  onChange={e => addressFieldChangedValue(e)} 
                                />
                        </div>
                        <div className='validation'>{validationMessage}</div>
                    </Fragment> : null}
        </Fragment>
    ) : null;
    const viewSubmit =
        (
            !usingSameAddress || is_virtual) &&
            (!isSignedIn || shippingNewForm || ((billingForm && storageBilling === 'new_address') || (!billingForm && storageShipping === 'new_address'))
            ) ? (
                <div className='footer'>
                    <Button
                        className='button save-address-btn'
                        style={{ marginTop: 10, float: 'right', height: 48 }}
                        type="submit"
                        priority="high"
                        disabled={submitting}
                    >{Identify.__('Save Address')}</Button>
                </div>
            ) : null;

    const toggleSameShippingAddress = () => {
        const sameAsShippingAddress = !usingSameAddress
        let billingAddress;
        if (sameAsShippingAddress) {
            billingAddress = {
                sameAsShippingAddress
            };
            submit(billingAddress);
        }
        setUsingSameAddress(sameAsShippingAddress)
    }
    return (
        <React.Fragment>
            <div className='body form-fields-body'>
                {(billingForm && !is_virtual) &&
                    <div className="billing-same">
                        {(billingForm && !is_virtual) && <Checkbox
                            fieldState={{ value: usingSameAddress }}
                            field="addresses_same" label={Identify.__("Billing address same as shipping address")}
                            classes={{
                                label: 'addresses_same_label',
                                icon: 'addresses_same_icon'
                            }}
                            onChange={() => toggleSameShippingAddress()} />}
                    </div>}
                {viewFields}
            </div>
            {viewSubmit}
        </React.Fragment>
    )
}

export default FormFields;

async function setToken(token) {
    // TODO: Get correct token expire time from API
    return storage.setItem('signin_token', token, 3600);
}
