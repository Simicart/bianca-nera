import React, { useState } from 'react';
import { func, string } from 'prop-types';
import {
    showFogLoading,
    hideFogLoading
} from 'src/simi/BaseComponents/Loading/GlobalLoading';
import { updateCoupon } from 'src/simi/Model/Cart';
import Identify from 'src/simi/Helper/Identify';
import { Colorbtn } from 'src/simi/BaseComponents/Button';
import ArrowDown from 'src/simi/BaseComponents/Icon/TapitaIcons/ArrowDown';
import ArrowUp from 'src/simi/BaseComponents/Icon/TapitaIcons/ArrowUp';
import { showToastMessage } from 'src/simi/Helper/Message';
import { showToastSuccess } from 'src/simi/Helper/MessageSuccess';
require('./style.scss');

const Coupon = props => {
    const { value, getCartDetailsCustom, getCartDetails } = props;
    const [isCouponOpen, setOpen] = useState(false);
    const [coupon, setCoupon] = useState(value || '');
    // const [clearCoupon, setClearCoupon] = useState(false)
    const handleCoupon = (type = '') => {
        if (!coupon && type !== 'clear') {
            showToastMessage(Identify.__('Please enter coupon code'));
            return null;
        }
        // if (type === 'clear') {
        //     setClearCoupon(true)
        // }
        showFogLoading();
        const params = {
            coupon_code: type === 'clear' ? '' : coupon
        };
        updateCoupon(processData, params);
    };

    const processData = data => {
        let text = '';
        let success = false;
        if (data.message) {
            const messages = data.message;
            for (const i in messages) {
                const msg = messages[i];
                text += msg + ' ';
            }
        }
        if (data.total && data.total.coupon_code) {
            success = true;
        }
        // if (clearCoupon) {
        //     setClearCoupon(false)
        //     success = true;
        //     setCoupon('')
        // };

        if (text){
            if(success){
                getCartDetailsCustom ? getCartDetailsCustom() : getCartDetails();
                showToastSuccess(Identify.__(text))
            }else{
                if (text === 'Coupon code was canceled. ') {
                    // setClearCoupon(false);
                    showToastSuccess(Identify.__(text));
                    getCartDetailsCustom ? getCartDetailsCustom() : getCartDetails();
                }else{
                    // setClearCoupon(true)
                    showToastMessage(Identify.__(text));
                    getCartDetailsCustom ? getCartDetailsCustom() : getCartDetails();
                }
            }
        }
        hideFogLoading();
    };

    return (
        <div className="coupon-code">
            <div
                role="button"
                className="coupon-code-title"
                tabIndex="0"
                onClick={() => setOpen(!isCouponOpen)}
                onKeyUp={() => setOpen(!isCouponOpen)}
            >
                <div>{Identify.__('Add a Coupon Code')}</div>
                <div>{isCouponOpen ? <ArrowUp /> : <ArrowDown />}</div>
            </div>
            <div
                className={`coupon-code-area-tablet  ${isCouponOpen?'coupon-open':'coupon-close'}`}
            >
                <input
                    className="coupon_field"
                    type="text"
                    placeholder={Identify.__('Coupon Code')}
                    value={coupon}
                    onChange={e => setCoupon(e.target.value)}
                />
                {value ? (
                    <Colorbtn
                        className={`submit-coupon ${ Identify.isRtl() && 'submit-coupon-rtl' }`}
                        onClick={() => {setCoupon(''); handleCoupon('clear')}}
                        text={Identify.__('Cancel')}
                    />
                ) : (
                    <Colorbtn
                        className={`submit-coupon ${ Identify.isRtl() && 'submit-coupon-rtl' }`}
                        onClick={() => handleCoupon()}
                        text={Identify.__('Apply')}
                    />
                )}
            </div>
        </div>
    );
};

Coupon.propTypes = {
    value: string,
    toggleMessages: func,
    getCartDetailsCustom: func
};
export default Coupon;
