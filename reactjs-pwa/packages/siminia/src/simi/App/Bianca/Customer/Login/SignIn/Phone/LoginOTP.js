import React, { Component } from 'react'
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading'
import OtpForm from 'src/simi/App/Bianca/Components/Otp/OtpForm';
import { sendOTPForLogin } from 'src/simi/Model/Otp';
import { Util } from '@magento/peregrine';
import Identify from 'src/simi/Helper/Identify'
const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();
const $ = window.$;

const isMobilePhone = (number) => {
    return true;
}

class LoginOTP extends Component {
    constructor(props) {
        super(props);
        this.config = window.SMCONFIGS;
        this.state = {
            phone: '',
            isButtonDisabled: false
        }
        this.merchant = Identify.getStoreConfig();
    }

    onChange = (val1, val2) => {
        const value = val1 + val2
        this.setState({ phone: value });
    }

    handleSendProfile = (data, token) => {
        if (data.customer) {
            this.props.onSignIn(token);
        }
    }

    handleSendOtp = () => {
        let phone = this.state.phone;
        $('#login-opt-area #number_phone-not-exist').css({ display: 'none' });
        $('#login-opt-area #number_phone-invalid').css({ display: 'none' });
        if (!phone && !phone.trim().length === 0) {
            $('#login-opt-area #number_phone-not-exist').css({ display: 'block' })
            return;
        }

        if (!isMobilePhone(phone)) {
            $('#login-opt-area #number_phone-invalid').css({ display: 'block' });
            return;
        }

        showFogLoading()
        phone = phone.replace(/[- )(]/g, '').replace(/\+/g, "").replace(/\,/g, "");
        this.phoneNB = phone
        const params = {
            mobile: phone
        }
        if (this.merchant && this.merchant.hasOwnProperty('storeConfig') && this.merchant.storeConfig) {
            const { website_id } = this.merchant.storeConfig;
            if (website_id) {
                params['website_id'] = website_id;
            }
        }
        sendOTPForLogin(params, this.handleCallBackSendOTP)
    }

    handleCallBackSendOTP = (data) => {
        hideFogLoading();

        if (data.result && data.result === 'true') {
            $('#login-opt-area #number_phone-invalid').css({ display: 'none' });
            $('#login-opt-area #verify-phone-area').removeClass('hidden');
            // save numberphone into localstorage
            localStorage.setItem("numberphone_otp", this.state.phone);
            // Open modal verify otp
            this.props.openVModal();
            // setTimeout() return an id, we pass it into state
            const idValue = setTimeout(() => this.props.closeVModal(), 120000)
            localStorage.setItem('idPopup', idValue)
        } else {
            $('#login-opt-area #number_phone-invalid').css({ display: 'block' })
            $('#login-opt-area #verify-phone-area').addClass('hidden');
        }

    }

    render() {
        const { isButtonDisabled } = this.state;

        return (
            <>
                <OtpForm
                    isButtonDisabled={isButtonDisabled}
                    handleVerify={this.handleVerifyLogin}
                    handleChangePhone={(val1, val2) => this.onChange(val1, val2)}
                    phone={this.state.phone}
                    type={'login'}
                    notShowBtn={true}
                />
                <div role="presentation" className='send-otp-login' onClick={this.handleSendOtp}>
                    <button>
                        {Identify.__('SIGN IN')}
                    </button>
                </div>
            </>
        )
    }
}

export default LoginOTP
