import React from 'react'
import Modal from 'react-responsive-modal'
import Identify from 'src/simi/Helper/Identify'
import CountDown from './CountDown'
require('./verifyOtpModal.scss');

const $ = window.$;

class VerifyOtpModal extends React.Component {
    constructor(props) {
        super(props)
        this.countDownElement = React.createRef();
        this.state = {
            value1: '',
            value2: '',
            value3: '',
            value4: '',
            value5: '',
            value6: ''
        };
        this.nextInput = 1;
    }

    jumbTo = (num) => {
        $(`#form-verify-otp .otp${num}`) && $(`#form-verify-otp .otp${num}`).focus();
    }

    onChange = (e) => {
        const maxInputs = 6;
        const re = /^[0-9\b]+$/;
        let value = {}
        let i = e.target.attributes['data-index'] && parseInt(e.target.attributes['data-index'].value) || 0;
        if (i < maxInputs) this.nextInput = i;
        // delete by backspace
        if (e.target.value === '') {
            value[`value${i}`] = '';
            if (i > 1) {
                this.jumbTo(i-1);
            }
            if (this.nextInput > maxInputs) this.nextInput = i;
            this.setState(value);
            return;
        }
        // stopped at the highest level index input
        if (this.nextInput > maxInputs) {
            return;
        }
        if (re.test(event.data)) {
            value[`value${i}`] = event.data;
            this.nextInput += 1;
            if (i < maxInputs) {
                this.jumbTo(i+1);
            }
        }
        //  else {
        //     value[`value${i}`] = '';
        // }
        this.setState(value);
    }
    
    componentDidMount() {
        $('#form-verify-otp .otp1').focus();
        if (Identify.isRtl()) {
            $('#verify-otp-modal').addClass('rtl-modal')
        }
    }

    resetForm = () => {
        // reset form
        this.setState = ({
            value1: '',
            value2: '',
            value3: '',
            value4: '',
            value5: '',
            value6: ''
        });
    }

    render() {

        const { openVerifyModal, closeVerifyModal } = this.props

        const closeModalAndClearTimeout = () => {
            // set state time clock = -1
            this.countDownElement.current.time = -1
            // clear time out
            this.countDownElement.current.clearTimeOut()
            // close modal
            this.props.closeVerifyModal()
        }

        const verifyOtp = (e) => {
            e.preventDefault()
            $('.otp-form .error').css('display', 'none')
            let bool1 = $('.otp1').val().length == 1;
            let bool2 = $('.otp2').val().length == 1;
            let bool3 = $('.otp3').val().length == 1;
            let bool4 = $('.otp4').val().length == 1;
            let bool5 = $('.otp5').val().length == 1;
            let bool6 = $('.otp6').val().length == 1;
            let valid = bool1 && bool2 && bool3 && bool4 && bool5 && bool6;
            if (!valid) {
                $('#verify-otp-modal .error').css('display', 'block')
            } else {
                $('.otp-form .error').css('display', 'none')
                let otp1 = $('.otp1').val()
                let otp2 = $('.otp2').val()
                let otp3 = $('.otp3').val()
                let otp4 = $('.otp4').val()
                let otp5 = $('.otp5').val()
                let otp6 = $('.otp6').val()
                let otp = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
                // save otp to localStorage
                localStorage.setItem('login_otp', otp)
                // call api
                this.props.callApi(localStorage.getItem('numberphone_otp'))
                // close modal and clear time out
                closeModalAndClearTimeout()
            }

        }

        return (
            <Modal
                modalId="verify-otp-modal"
                open={openVerifyModal}
                onClose={closeModalAndClearTimeout}
                classNames={{ overlay: Identify.isRtl() ? "rtl-wrap-modal" : "" }}
            >
                <div className="title">
                    {Identify.__('verify your mobile number').toUpperCase()}
                </div>
                <div className="description">
                    {Identify.__('A text message with 6-digit verifycation code has been sent to ')}
                    <span className="bold-number">
                        {localStorage.getItem('numberphone_otp') ? localStorage.getItem('numberphone_otp') : localStorage.getItem('numberphone_register')}
                    </span>
                </div>
                <form onSubmit={verifyOtp} method="post" id="form-verify-otp" className="form-verify-otp">
                    <div className="otp-form">
                        <input className="otp otp1" name="otp1" data-index={1} maxLength="2" value={this.state.value1} onChange={this.onChange} />
                        <input className="otp otp2" name="otp2" data-index={2} maxLength="2" value={this.state.value2} onChange={this.onChange} />
                        <input className="otp otp3" name="otp3" data-index={3} maxLength="2" value={this.state.value3} onChange={this.onChange} />
                        <input className="otp otp4" name="otp4" data-index={4} maxLength="2" value={this.state.value4} onChange={this.onChange} />
                        <input className="otp otp5" name="otp5" data-index={5} maxLength="2" value={this.state.value5} onChange={this.onChange} />
                        <input className="otp otp6" name="otp6" data-index={6} maxLength="2" value={this.state.value6} onChange={this.onChange} />
                    </div>
                    <button type="submit">
                        {Identify.__('verify').toUpperCase()}
                    </button>
                </form>
                <div className="error">
                    {Identify.__('Invalid otp code !')}
                </div>
                <div className="count-down">
                    {Identify.__('Resend after')}<CountDown ref={this.countDownElement} time={120} />
                </div>
            </Modal>
        )
    }
}

export default VerifyOtpModal