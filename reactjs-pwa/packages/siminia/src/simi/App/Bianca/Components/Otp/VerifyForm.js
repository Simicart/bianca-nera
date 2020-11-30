import React, { useEffect, useMemo, useState } from 'react'
import ReactPhoneInput from 'react-phone-input-2';
import { asField } from 'informed';
import Identify from 'src/simi/Helper/Identify';
require('./verifyForm.scss')

const $ = window.$;

const VerifyForm = asField(({ fieldState, fieldApi, ...props }) => {
    const [ phoneCode, setPhoneCode ] = useState('965'); // kw is default
    const { value } = fieldState;
    const { setValue, setTouched } = fieldApi;
    const { onChange, onBlur, initialValue, forwardedRef, 
        handleChangePhone, handleVerify, openGetModal,
        field, ...rest } = props;

    useEffect(()=>{
        $('.verify-opt-area .custom-input .react-tel-input input').attr('readonly', true);
    });

    const showOption = () => {
        $('.arrow').click()
    }

    const updateValue = (phoneCode) => {
        if (handleChangePhone) {
            const country_code = $('.verify-opt-area .custom-input .react-tel-input input').val()
            const new_val = $('#real-input-register').val()
            handleChangePhone(country_code, new_val.replace(/^0+/g, ''))
        } else {
            setPhoneCode(phoneCode)
        }
    }

    const inputKeyUp = () => {
        if (handleChangePhone) {
            const country_code = $('.verify-opt-area .custom-input .react-tel-input input').val()
            const new_val = $('#real-input-register').val()
            handleChangePhone(country_code, new_val.replace(/^0+/g, ''))
        }
    }

    const allowedCountries = useMemo(() => {
        const storeConfig = Identify.getStoreConfig();
        const countries = storeConfig.simiStoreConfig.config.allowed_countries;
        let listAllowedCountry = [];
        countries.map((country) => {
            const code = country.country_code
            listAllowedCountry.push(code.toLowerCase())
        })
        return listAllowedCountry;
    });

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
                    country={"kw"}
                    onlyCountries={allowedCountries}
                    countryCodeEditable={false}
                    onChange={updateValue}
                />
                <div className="element-2">
                    <input
                        {...rest}
                        id="real-input-register"
                        ref={forwardedRef}
                        value={value || initialValue || ''}
                        name={field || "real-input"}
                        type="number"
                        pattern="[0-9]"
                        placeholder={Identify.__('Phone')}
                        onKeyUp={inputKeyUp}
                        onChange={e => {
                            setValue(e.target.value);
                            if (onChange) {
                                onChange(e);
                            }
                        }}
                        onBlur={e => {
                            setTouched(true);
                            if (onBlur) {
                                onBlur(e);
                            }
                        }}
                    />
                </div>
            </div>
            {fieldState.error &&
                <div className="error-message" style={{display: 'block'}}>{Identify.__(fieldState.error)}</div>
            }
            <div id="number_phone-required" className="error-message">{Identify.__('Mobile number is required.')}</div>
            <div id="number_phone-invalid" className="error-message">{Identify.__('Invalid Number.')}</div>
            <div id="number_phone-not-exist" className="error-message">{Identify.__('Mobile number don\'t exist')}</div>
            {/* <div className='phone-otp-desc'>
                {Identify.__('Mobile No. Without Country Code i.e 9898989898')}
            </div> */}
            <div className="wrap">
                <div id="must-verify" className="error-message">
                    {/* {Identify.__('You must ')}{Identify.__('verify phone number').toUpperCase()}{Identify.__(' before ')}{Identify.__('register').toUpperCase()} */}
                    {Identify.__('You must VERIFY PHONE NUMBER before REGISTER')}
                </div>
                <div role="presentation" id="createAccount" className='login-otp' onClick={()=> openGetModal && openGetModal(phoneCode, value)}>
                    {Identify.__('VERIFY PHONE NUMBER').toUpperCase()}
                </div>
            </div>
        </div>
    );
});

// class VerifyForm extends Component {

//     constructor(props) {
//         super(props)
//     }

//     componentDidMount() {
//         $('.verify-opt-area .custom-input .react-tel-input input').attr('readonly', true);
//     }

//     render() {

//         const showOption = () => {
//             $('.arrow').click()
//         }

//         const updateValue = () => {
//             const country_code = $('.verify-opt-area .custom-input .react-tel-input input').val()
//             const new_val = $('#real-input-register').val()
//             this.props.handleChangePhone(country_code, new_val.replace(/^0+/g, ''))
//         }

//         const storeConfig = Identify.getStoreConfig();
//         const countries = storeConfig.simiStoreConfig.config.allowed_countries;
//         const listAllowedCountry = [];
//         countries.map((country) => {
//             const code = country.country_code
//             listAllowedCountry.push(code.toLowerCase())
//         })

//         return (
//             <div id="verify-opt-area" className={`verify-opt-area ${Identify.isRtl() ? 'verify-opt-area-rtl' : ''}`}>
//                 <div className="label">
//                     {Identify.__('phone number*').toUpperCase()}
//                 </div>
//                 <div className="custom-input">
//                     <div role="presentation" className="element-1" onClick={() => showOption()}>
//                         <div className="icon-chevron-down icons"></div>
//                     </div>
//                     <ReactPhoneInput
//                         id={'namnam'}
//                         country={"vn"}
//                         onlyCountries={listAllowedCountry}
//                         countryCodeEditable={false}
//                         onChange={updateValue}
//                     />
//                     <div className="element-2">
//                         <input
//                             id="real-input-register"
//                             onKeyUp={() => updateValue()}
//                             name="real-input"
//                             type="number"
//                             pattern="[0-9]"
//                             placeholder={Identify.__('Phone')}
//                         />
//                     </div>
//                 </div>
//                 <div id="number_phone-required" className="error-message">{Identify.__('Mobile number is required.')}</div>
//                 <div id="number_phone-invalid" className="error-message">{Identify.__('Invalid Number.')}</div>
//                 <div id="number_phone-not-exist" className="error-message">{Identify.__('Mobile number don\'t exist')}</div>
//                 {/* <div className='phone-otp-desc'>
//                     {Identify.__('Mobile No. Without Country Code i.e 9898989898')}
//                 </div> */}
//                 <div className="wrap">
//                     <div id="must-verify" className="error-message">
//                         {/* {Identify.__('You must ')}{Identify.__('verify phone number').toUpperCase()}{Identify.__(' before ')}{Identify.__('register').toUpperCase()} */}
//                         {Identify.__('You must VERIFY PHONE NUMBER before REGISTER')}
//                     </div>
//                     <div role="presentation" id="createAccount" className='login-otp' onClick={()=>this.props.openGetModal()}>
//                         {Identify.__('verify PHONE NUMBER').toUpperCase()}
//                     </div>
//                 </div>
//             </div>
//         )
//     }
// }

export default VerifyForm;
