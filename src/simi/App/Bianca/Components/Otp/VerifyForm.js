import React, { Component } from 'react'
import ReactPhoneInput from 'react-phone-input-2'
import Identify from 'src/simi/Helper/Identify';
import { configColor } from 'src/simi/Config'
import CountDown from './CountDown';
import { $CombinedState } from 'redux';
require('./verifyForm.scss')

const $ = window.$;

class VerifyForm extends Component {

    constructor(props) {
        super(props)
    }

    componentDidMount() {
        $('.verify-opt-area .custom-input .react-tel-input input').attr('readonly', true);
    }

    render() {
        const styleActice = {
            backgroundColor: configColor.button_background,
            color: configColor.button_text_color
        }

        const styleInActive = {
            backgroundColor: "#eee",
            color: "#fff",
            cursor: 'not-allowed'
        }

        const showOption = () => {
            $('.arrow').click()
        }

        const updateValue = () => {
            let country_code = $('#verify-opt-area #phone-form-control').val()
            let new_val = $('#real-input-register').val()
            this.props.handleChangePhone(country_code, new_val)
        }

        const storeConfig = Identify.getStoreConfig();
        const countries = storeConfig.simiStoreConfig.config.allowed_countries;
        const listAllowedCountry = [];
        countries.map((country, index) => {
            let code = country.country_code
            listAllowedCountry.push(code.toLowerCase())
        })

        const doNothing = () => {

        }

        return (
            <div id="verify-opt-area" className={`verify-opt-area ${Identify.isRtl() ? 'verify-opt-area-rtl' : ''}`}>
                <div className="label">
                    {Identify.__('phone number*'.toUpperCase())}
                </div>
                <div className="custom-input">
                    <div className="element-1" onClick={() => showOption()}>
                        <div className="custom-arrow"></div>
                    </div>
                    <ReactPhoneInput
                        country={"vn"}
                        onlyCountries={listAllowedCountry}
                        countryCodeEditable={false}
                        onChange={updateValue}
                    />
                    <div className="element-2">
                        <input
                            id="real-input-register"
                            onKeyUp={() => updateValue()}
                            name="real-input"
                            type="number"
                            pattern="[0-9]"
                            placeholder={Identify.__('Phone')}
                        />
                    </div>
                </div>
                <div id="number_phone-required" className="error-message">{Identify.__("Mobile number is required.")}</div>
                <div id="number_phone-invalid" className="error-message">{Identify.__("Invalid Number.")}</div>
                <div id="number_phone-not-exist" className="error-message">{Identify.__("Mobile number don't exist")}</div>
                <div className='phone-otp-desc'>
                    {/* {Identify.__('Mobile No. Without Country Code i.e 9898989898')} */}
                </div>
                <div className="wrap">
                    <div id="must-verify" className="error-message">
                        {Identify.__('You must ')}{Identify.__('verify phone number'.toUpperCase())}{Identify.__(' before ')}{Identify.__('register'.toUpperCase())}
                    </div>
                    <div role="presentation" id="createAccount" className='login-otp' onClick={this.props.openGetModal}>
                        {Identify.__('VERIFY PHONE NUMBER').toUpperCase()}
                    </div>
                </div>
            </div>
        )
    }
}

export default VerifyForm;
