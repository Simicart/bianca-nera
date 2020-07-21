import React, { Component } from 'react'
import ReactPhoneInput from 'react-phone-input-2'
import Identify from 'src/simi/Helper/Identify';
require('./verifyForm.scss')

const $ = window.$;

class PhoneInputNoNeedVerify extends Component {

    constructor(props) {
        super(props)
    }

    componentDidMount() {
        $('.verify-opt-area-address .custom-input .react-tel-input input').attr('readonly', true);
        $('.verify-opt-area-address .form-row input').val(this.props.value);
    }

    render() {
        console.log(this.props)
        const showOption = () => {
            $('.arrow').click()
        }

        const updateValue = () => {
            if(this.props.type === 'new'){
                const country_code = $('#verify-opt-area-address .react-tel-input input').val()
                const new_val = $('#real-input-register').val()
                this.props.handleChangePhone(country_code, new_val)
            }else{
                const new_val = $('#input-edit-phone').val()
                this.setState({phone: new_val})
                this.props.handleUpdatePhone(new_val)
            }
        }

        const storeConfig = Identify.getStoreConfig();
        const countries = storeConfig.simiStoreConfig.config.allowed_countries;
        const listAllowedCountry = [];
        countries.map((country, index) => {
            const code = country.country_code
            listAllowedCountry.push(code.toLowerCase())
        })

        const doNothing = () => {

        }

        return (
            <div id="verify-opt-area-address" className={`verify-opt-area-address ${Identify.isRtl() ? 'verify-opt-area-address-rtl' : ''}`}>
                <div className="label">
                    {Identify.__('Telephone *').toUpperCase()}
                </div>
                {this.props.type === 'new' ? <div className="custom-input">
                    <div role="presentation" className="element-1" onClick={() => showOption()}>
                        <div className="icon-chevron-down icons"></div>
                    </div>
                    <ReactPhoneInput
                        id={'phone-form-control-1'}
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
                            placeholder={Identify.__('Telephone')}
                        />
                    </div>
                </div> :
                    <div className="form-row row-edit-phone">
                        <input
                            id="input-edit-phone"
                            onKeyUp={() => updateValue()}
                            name="phonenumber"
                            placeholder={Identify.__('Telephone')}
                        />
                    </div>}
                <small id="number_phone-invalid" className="error-message">{Identify.__("Please enter a valid phone number.")}</small>
            </div>
        )
    }
}

export default PhoneInputNoNeedVerify;
