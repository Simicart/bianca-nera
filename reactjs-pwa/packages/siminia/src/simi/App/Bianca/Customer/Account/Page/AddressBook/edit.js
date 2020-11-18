import React, { useState } from 'react';
import { connect } from 'src/drivers';
import { Form, Text, BasicText, BasicSelect, Checkbox, Option, useFieldState, asField } from 'informed';
import Identify from 'src/simi/Helper/Identify';
import { validateEmpty } from 'src/simi/Helper/Validation';
import TitleHelper from 'src/simi/Helper/TitleHelper';
import Loading from "src/simi/BaseComponents/Loading";
import { SimiMutation } from 'src/simi/Network/Query';
import CUSTOMER_ADDRESS_UPDATE from 'src/simi/queries/customerAddressUpdate.graphql';
import CUSTOMER_ADDRESS_CREATE from 'src/simi/queries/customerAddressCreate.graphql';
import PhoneInputNoNeedVerify from './PhoneInputNoNeedVerify'
import { toggleMessages } from 'src/simi/Redux/actions/simiactions';
import {showToastMessage} from 'src/simi/Helper/Message';

const SimiText = asField(({ fieldState, ...props }) => (
    <React.Fragment>
        <BasicText
            fieldState={fieldState}
            {...props}
            style={fieldState.error ? { border: 'solid 1px #C53834' } : null}
        />
        {fieldState.error ? (<small style={{ color: '#C53834' }}>{fieldState.error}</small>) : null}
    </React.Fragment>
));

const SimiSelect = asField(({ fieldState, ...props }) => (
    <React.Fragment>
        <BasicSelect
            fieldState={fieldState}
            {...props}
            style={fieldState.error ? { border: 'solid 1px #C53834' } : null}
        />
        {fieldState.error ? (<small style={{ color: '#C53834', marginTop: '2px' }}>{fieldState.error}</small>) : null}
    </React.Fragment>
));

const $ = window.$

const Edit = props => {

    const HOUSE_NUMBER_LABEL = 'House No.';
    const APARTMENT_NUMBER_LABEL = 'Apartment No.';

    const width = window.innerWidth;
    const isSmallPhone = width < 768;

    const { user, addressData, countries, address_fields_config } = props;
    const addressConfig = address_fields_config;
    const [phoneChange, setPhone] = useState(addressData.telephone);

    let pageType = 'new';
    let CUSTOMER_MUTATION = CUSTOMER_ADDRESS_CREATE;
    if (addressData.id) {
        CUSTOMER_MUTATION = CUSTOMER_ADDRESS_UPDATE;
        pageType = 'edit';
    }

    const getFormApi = (formApi) => {
        // formApi.setValue('firstname', addressData.firstname)
    }

    const validate = (value) => {
        return !validateEmpty(value) ? 'This is a required field.' : undefined;
    }

    const validateCondition = (value, opt) => {
        if (opt === 'req') {
            return !validateEmpty(value) ? 'This is a required field.' : undefined;
        }
        return undefined;
    }

    const validateStreet = (value, opt) => {
        if (opt === 'req') {
            if (typeof value === 'array') {
                for (var i in value) {
                    if (!validateEmpty(value[i])) {
                        return 'This is a required field.';
                    }
                }
            } else {
                return !validateEmpty(value) ? 'This is a required field.' : undefined;
            }
        }
        return undefined;
    }

    const validateOption = (value, opt) => {
        if (opt === 'req') {
            return !value || !validateEmpty(value) ? 'Please select an option.' : undefined;
        }
        return undefined;
    }

    const formSubmit = (values) => {
    }

    const formChange = (formState) => {
        if (formState && formState.values && formState.values.default_billing === true) {
            $('#checkbox-billing').css('appearance', 'none');
            $('#checkbox-billing').css('background-color', '#101820');
            $('input#checkbox-billing::before').css('content', '\\e934');
        } else {
            $('#checkbox-billing').css('appearance', 'auto');
            $('#checkbox-billing').css('background-color', '#ffffff');
            $('input#checkbox-billing::before').css('content', 'unset');
        }
        if (formState && formState.values && formState.values.default_shipping === true) {
            $('#checkbox-shipping').css('appearance', 'none');
            $('#checkbox-shipping').css('background-color', '#101820');
            $('input#checkbox-shipping::before').css('content', '\\e934');
        } else {
            $('#checkbox-shipping').css('appearance', 'auto');
            $('#checkbox-shipping').css('background-color', '#ffffff');
            $('input#checkbox-shipping::before').css('content', 'unset');
        }
    }

    const getRegionObject = (country_id, region_id) => {
        let country;
        for (const i in countries) {
            if (countries[i].id === country_id) {
                country = countries[i];
                break;
            }
        }
        if (country && country.available_regions && country.available_regions.length) {
            for (const i in country.available_regions) {
                if (country.available_regions[i].id === parseInt(region_id)) {
                    return country.available_regions[i];
                }
            }
        }
        return null
    }

    let loading = false;

    const buttonSubmitHandle = (mutaionCallback, formApi) => {
        loading = true;
        let values = {...formApi.getValues()};
        formApi.submitForm();
        if (pageType === 'edit') {
            const newPhoneNumber = $('#input-edit-phone').val()
            if (newPhoneNumber.length < 5) {
                $('.row-edit-phone input').css('border', '1px solid #C53834');
                $('.verify-opt-area-address #number_phone-invalid').css({ display: 'block' })
                showToastMessage(Identify.__('Some fields are invalid !'))
                loading = false;
                return null;
            } else {
                $('.row-edit-phone input').css('border', '1px solid #E0E0E0');
                $('.verify-opt-area-address #number_phone-invalid').css({ display: 'none' })
                values.telephone = newPhoneNumber;
            }
        } else {
            if (phoneChange.length < 5) {
                $('.verify-opt-area-address .custom-input').css('border', '1px solid #C53834');
                $('.verify-opt-area-address .error-message').css({ display: 'block' })
                showToastMessage(Identify.__('Some fields are invalid !'))
                loading = false;
                return null;
            } else {
                $('.verify-opt-area-address .custom-input').css('border', '1px solid #E0E0E0');
                $('.verify-opt-area-address .error-message').css({ display: 'none' })
            }
        }
        if (formApi.getState().invalid) {
            loading = false;
            return null; // not submit until form has no error
        }
        if (values.region) {
            var oldRegionValue = values.region;
            var region;
            if (values.region) region = getRegionObject(values.country_id, values.region.region_id);
            if (region) {
                values.region.region = region.name;
                values.region.region_id = region.id;
                values.region.region_code = region.code;
            } else {
                values.region.region = oldRegionValue.region ? oldRegionValue.region : null;
                values.region.region_id = null;
                values.region.region_code = null;
            }
        }
        // Custom Attributes
        if (values.custom_attributes instanceof Object) {
            let attrs = [];
            for (let attrcode in values.custom_attributes) {
                attrs.push({
                    attribute_code: attrcode,
                    value: values.custom_attributes[attrcode]
                });
            }
            values.custom_attributes = attrs;
        }
        // required values
        const config = addressConfig;
        if (config) {
            if (!phoneChange) values.telephone = config.telephone_default || 'NA';
            // if (!values.street) values.street = [config.street_default || 'NA'];
            if (!values.country_id) values.country_id = config.country_id_default || 'US';
            if (!values.city) values.city = config.city_default || 'NA';
            if (!values.postcode) values.postcode = config.zipcode_default || 'NA';
        }

        if (phoneChange) {
            values.telephone = phoneChange
        }

        let newStreet = [];
        if (values.street && values.street[0]) {
            newStreet.push(values.street[0]);
        }
        if (values.street && values.street[1]) {
            newStreet.push(HOUSE_NUMBER_LABEL + values.street[1]);
        }
        if (values.street && values.street[2]) {
            newStreet.push(APARTMENT_NUMBER_LABEL + values.street[2]);
        }

        values.street = newStreet;

        values.id = addressData.id; //address id
        mutaionCallback({ variables: values });
    }

    const StateProvince = (props) => {
        const { showOption } = props;
        const countryFieldState = useFieldState('country_id');
        const country_id = countryFieldState.value;
        // get country
        let country;
        for (const i in countries) {
            if (countries[i].id === country_id) {
                country = countries[i];
                break;
            }
        }
        if (country && country.available_regions && country.available_regions.length) {
            let regionValue = addressData.region.region_id;
            let required = 'opt';
            if (addressData.country_id !== country_id) {
                regionValue = null;
            }
            if (showOption !== 'opt') {
                required = 'req';
            }
            return (
                <div className='state-option'>
                    <label htmlFor="input-state">{Identify.__('State/Province')} {required === 'req' && <span>*</span>}</label>
                    <div>
                        <SimiSelect id="input-state" field="region[region_id]" initialValue={regionValue} key={regionValue}
                            validate={(value) => validateOption(value, required)} validateOnChange >
                            <Option value="" key={-1}>{Identify.__('State/Province')}</Option>
                            {country.available_regions.map((region, index) => {
                                return <Option value={`${region.id}-${index}`} key={index}>{region.name}</Option>
                            })}
                        </SimiSelect>
                    </div>
                </div>
            );
        } else {
            let regionValue = addressData.region.region
            if (addressData.country_id !== country_id && country_id !== undefined) {
                regionValue = null; //reset value region field when change country
            }
            return (
                <div className='state-text'>
                    <label htmlFor="input-state">{Identify.__('State/Province')}{showOption === 'req' && <span>*</span>}</label>
                    <div>
                        <SimiText id="input-state" field="region[region]" initialValue={regionValue}
                            validate={(value) => validateCondition(value, showOption)} validateOnChange />
                    </div>
                </div>
            )
        }
    }

    const onChange = (val1, val2) => {
        $('.verify-opt-area-address .error-message').css({ display: 'none' })
        $('.verify-opt-area-address .custom-input').css('border', '1px solid #E0E0E0');
        const value = val1 + val2
        setPhone(value)
        localStorage.setItem("numberphone_address", value);
        if (val2 === '') {
            localStorage.setItem("numberphone_address", '')
            setPhone('')
        }
    }

    const onChangePhone = (val) => {
        $('.verify-opt-area-address .error-message').css({ display: 'none' })
        localStorage.setItem("numberphone_address", val);
        setPhone(val)
    }

    return (
        <div className='edit-address'>
            {TitleHelper.renderMetaHeader({ title: pageType === 'new' ? Identify.__('Add New Address') : Identify.__('Edit Address') })}
            <div className="title-page-address">{pageType === 'new' ? Identify.__('Add New Address') : Identify.__('Edit Address')}</div>
            <Form id="address-form" getApi={getFormApi} onSubmit={formSubmit} onChange={formChange}>
                {({ formApi }) => (
                    <>
                        <div className="col-left">
                            <div className="form-row">
                                <div className="col-label">{Identify.__('Contact Information')}</div>
                            </div>
                            <div className="form-row">
                                <label htmlFor="input-firstname">{Identify.__('First Name')} <span>*</span></label>
                                <SimiText id="input-firstname" field="firstname" initialValue={addressData.firstname} placeholder={Identify.__('First Name')} validate={validate} validateOnBlur validateOnChange />
                            </div>
                            <div className="form-row">
                                <label htmlFor="input-middlename">{Identify.__('Middle Name')} <span>*</span></label>
                                <SimiText id="input-middlename" field="middlename" initialValue={addressData.middlename} placeholder={Identify.__('Middle Name')} validate={validate} validateOnBlur validateOnChange />
                            </div>
                            <div className="form-row">
                                <label htmlFor="input-lastname">{Identify.__('Last Name')} <span>*</span></label>
                                <SimiText id="input-lastname" field="lastname" initialValue={addressData.lastname} placeholder={Identify.__('Last Name')} validate={validate} validateOnBlur validateOnChange />
                            </div>

                            {(!addressConfig || (addressConfig && addressConfig.company_show)) &&
                                <div className="form-row">
                                    <label htmlFor="input-company">
                                        {Identify.__('Company')} {addressConfig && addressConfig.company_show === 'req' && <span>*</span>}
                                    </label>
                                    <SimiText id="input-company" field="company" initialValue={addressData.company}
                                        validate={(value) => validateCondition(value, addressConfig && addressConfig.company_show || 'opt')}
                                        validateOnBlur validateOnChange
                                        placeholder={Identify.__('Company')}
                                    />
                                </div>
                            }

                            {
                                (!addressConfig || (addressConfig && addressConfig.telephone_show)) &&
                                <PhoneInputNoNeedVerify
                                    handleChangePhone={(val1, val2) => onChange(val1, val2)}
                                    handleUpdatePhone={(val) => onChangePhone(val)}
                                    type={pageType}
                                    value={addressData.telephone}
                                />
                            }
                            <div className="form-row">
                                <label htmlFor="input-email">{Identify.__('Email address')} <span>*</span></label>
                                <SimiText id="input-email" field="email" initialValue={user.email} placeholder={Identify.__('Email Address')} readOnly={true} />
                            </div>

                            {/* <div className="form-row">
                                <label htmlFor="input-house_number">{Identify.__('House Number')}</label>
                                <SimiText id="input-house_number" field="extension_attributes[house_number]" initialValue={addressData.house_number} placeholder={Identify.__('House Number')} />
                            </div>
                            <div className="form-row">
                                <label htmlFor="input-apartment_number">{Identify.__('Apartment Number')}</label>
                                <SimiText id="input-apartment_number" field="extension_attributes[apartment_number]" initialValue={addressData.apartment_number} placeholder={Identify.__('Apartment Number')} />
                            </div> */}
                            
                            {!isSmallPhone ? <div className="form-button">
                                <SimiMutation mutation={CUSTOMER_MUTATION}>
                                    {(mutaionCallback, { data }) => {
                                        if (data) {
                                            let addressResult = null;
                                            if (addressData.id) {
                                                addressResult = data.updateCustomerAddress;
                                            } else {
                                                addressResult = data.createCustomerAddress;
                                            }
                                            props.dispatchEdit({ changeType: addressData.addressType, changeData: addressResult });
                                            if (props.toggleMessages) {
                                                props.toggleMessages([{ type: 'success', message: Identify.__('Save address book successfully !'), auto_dismiss: true }])
                                            }
                                        }
                                        return (
                                            <>
                                                <div className={'btn ' + "btn" + ' ' + "save-address"}>
                                                    <button onClick={() => buttonSubmitHandle(mutaionCallback, formApi)}>
                                                        <span>{Identify.__('Save Address')}</span>
                                                    </button>
                                                </div>
                                                {(data === undefined && loading) && <Loading />}
                                            </>
                                        );
                                    }}
                                </SimiMutation>
                            </div> : ''
                            }
                        </div>
                        <div className="col-right">
                            {
                                (!addressConfig || (addressConfig && (
                                    addressConfig.street_show ||
                                    addressConfig.city_show ||
                                    addressConfig.region_id_show ||
                                    addressConfig.zipcode_show ||
                                    addressConfig.country_id_show
                                ))) ?
                                    <div className="form-row">
                                        <div className="col-label">{Identify.__('Address')}</div>
                                    </div>
                                    :
                                    <></>
                            }

                            {(!addressConfig || (addressConfig && addressConfig.street_show)) &&
                                <div className="form-row">
                                    <label htmlFor="input-street">
                                        {Identify.__('Street Address')} {addressConfig && addressConfig.street_show !== 'req' ? null : <span>*</span>}
                                    </label>
                                    <SimiText id="input-street" field="street[0]" initialValue={addressData.street[0]}
                                        validate={(value) => validateStreet(value, addressConfig && addressConfig.street_show || 'req')}
                                        validateOnBlur validateOnChange placeholder={Identify.__('Address')} />
                                    <SimiText id="input-street1" field="street[1]" initialValue={addressData.street[1] 
                                        && addressData.street[1].replace(new RegExp('^'+HOUSE_NUMBER_LABEL), '') || ''} 
                                        placeholder={Identify.__('House Number')} />
                                    <SimiText id="input-street2" field="street[2]" initialValue={addressData.street[2]
                                        && addressData.street[2].replace(new RegExp('^'+APARTMENT_NUMBER_LABEL), '') || ''} 
                                        placeholder={Identify.__('Apartment Number')} />
                                </div>
                            }

                            <div className="form-row">
                                <label htmlFor="input-block">{Identify.__('Block')}</label>
                                <SimiText id="input-block" field="extension_attributes[block]" initialValue={addressData.block} placeholder={Identify.__('Block')} />
                            </div>

                            {(!addressConfig || (addressConfig && addressConfig.city_show)) &&
                                <div className="form-row">
                                    <label htmlFor="input-city">
                                        {Identify.__('City')} {addressConfig && addressConfig.city_show !== 'req' ? null : <span>*</span>}
                                    </label>
                                    <SimiText id="input-city" field="city" initialValue={addressData.city}
                                        validate={(value) => validateCondition(value, addressConfig && addressConfig.city_show || 'req')}
                                        validateOnBlur validateOnChange placeholder={Identify.__('City')} />
                                </div>
                            }

                            {(!addressConfig || (addressConfig && addressConfig.region_id_show)) &&
                                <div className={"form-row" + ' ' + "state-province"} id="state-province">
                                    <StateProvince showOption={addressConfig && addressConfig.region_id_show || undefined} />
                                </div>
                            }

                            {(!addressConfig || (addressConfig && addressConfig.zipcode_show)) &&
                                <div className="form-row">
                                    <label htmlFor="input-postcode">
                                        {Identify.__('Zip/Postal Code')} {addressConfig && addressConfig.zipcode_show !== 'req' ? null : <span>*</span>}
                                    </label>
                                    <SimiText id="input-postcode" field="postcode" initialValue={addressData.postcode}
                                        validate={(value) => validateCondition(value, addressConfig && addressConfig.zipcode_show || 'req')}
                                        validateOnBlur validateOnChange placeholder={Identify.__('Zip/Postal Code')}
                                    />
                                </div>
                            }

                            {(!addressConfig || (addressConfig && addressConfig.country_id_show)) &&
                                <div className="form-row">
                                    <label htmlFor="input-country">
                                        {Identify.__('Country')} {addressConfig && addressConfig.country_id_show !== 'req' ? null : <span>*</span>}
                                    </label>
                                    <SimiSelect id="input-country" field="country_id" initialValue={addressConfig && addressConfig.country_id_default || addressData.country_id || 'US'}
                                        validate={(value) => validateOption(value, addressConfig && addressConfig.country_id_show || 'req')}
                                        validateOnChange
                                    >
                                        {countries.map((country, index) => {
                                            return country.full_name_locale !== null ?
                                                <Option value={`${country.id}`} key={index} >{country.full_name_locale}</Option> : null
                                        })}
                                    </SimiSelect>
                                </div>
                            }

                            <div className="form-row">
                                <div className="checkbox">
                                    <Checkbox id="checkbox-billing" field="default_billing" initialValue={addressData.default_billing} />
                                    <label htmlFor="checkbox-billing">{Identify.__('Use as my default billing address')}</label>
                                </div>
                                <div className="checkbox">
                                    <Checkbox id="checkbox-shipping" field="default_shipping" initialValue={addressData.default_shipping} />
                                    <label htmlFor="checkbox-shipping">{Identify.__('Use as my default shipping address')}</label>
                                </div>
                            </div>
                        </div>
                        {isSmallPhone ? <div className="form-button">
                                <SimiMutation mutation={CUSTOMER_MUTATION}>
                                    {(mutaionCallback, { data }) => {
                                        if (data) {
                                            let addressResult = null;
                                            if (addressData.id) {
                                                addressResult = data.updateCustomerAddress;
                                            } else {
                                                addressResult = data.createCustomerAddress;
                                            }
                                            props.dispatchEdit({ changeType: addressData.addressType, changeData: addressResult });
                                            if (props.toggleMessages) {
                                                props.toggleMessages([{ type: 'success', message: Identify.__('Save address book successfully !'), auto_dismiss: true }])
                                            }
                                        }
                                        return (
                                            <>
                                                <div className={'btn ' + "btn" + ' ' + "save-address"}>
                                                    <button onClick={() => buttonSubmitHandle(mutaionCallback, formApi)}>
                                                        <span>{Identify.__('Save Address')}</span>
                                                    </button>
                                                </div>
                                                {(data === undefined && loading) && <Loading />}
                                            </>
                                        );
                                    }}
                                </SimiMutation>
                            </div> : ''
                            }
                    </>
                )}
            </Form>
        </div>
    );
}

const mapStateToProps = ({ user }) => {
    const { currentUser } = user
    return {
        user: currentUser
    };
}

const mapDispatchToProps = {
    toggleMessages
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Edit);
