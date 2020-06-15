import React, { Component } from 'react'
import ReactPhoneInput from 'react-phone-input-2'
import Identify from 'src/simi/Helper/Identify';
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

        const showOption = () => {
            $('.arrow').click()
        }

        const updateValue = () => {
            const country_code = $('.verify-opt-area .custom-input .react-tel-input input').val()
            const new_val = $('#real-input-register').val()
            this.props.handleChangePhone(country_code, new_val.replace(/^0+/g, ''))
        }

        const storeConfig = Identify.getStoreConfig();
        const countries = storeConfig.simiStoreConfig.config.allowed_countries;
        const listAllowedCountry = [];
        countries.map((country) => {
            const code = country.country_code
            listAllowedCountry.push(code.toLowerCase())
        })

        return (
            <div id="verify-opt-area" className={`verify-opt-area ${Identify.isRtl() ? 'verify-opt-area-rtl' : ''}`}>
                <div className="label">
                    {Identify.__('phone number*').toUpperCase()}
                </div>
                <div className="custom-input">
                    <div role="presentation" className="element-1" onClick={() => showOption()}>
                        <div className="icon-chevron-down icons"></div>
                    </div>
                    <ReactPhoneInput
                        id={'namnam'}
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
                <div id="number_phone-required" className="error-message">{Identify.__('Mobile number is required.')}</div>
                <div id="number_phone-invalid" className="error-message">{Identify.__('Invalid Number.')}</div>
                <div id="number_phone-not-exist" className="error-message">{Identify.__('Mobile number don\'t exist')}</div>
                <div className='phone-otp-desc'>
                    {/* {Identify.__('Mobile No. Without Country Code i.e 9898989898')} */}
                </div>
                <div className="wrap">
                    <div id="must-verify" className="error-message">
                        {Identify.__('You must ')}{Identify.__('verify phone number').toUpperCase()}{Identify.__(' before ')}{Identify.__('register').toUpperCase()}
                    </div>
                    <div role="presentation" id="createAccount" className='login-otp' onClick={()=>this.props.openGetModal()}>
                        {Identify.__('verify PHONE NUMBER').toUpperCase()}
                    </div>
                </div>
            </div>
        )
    }
}

export default VerifyForm;
