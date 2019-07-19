import React, { Component, Fragment } from 'react';
import { Util } from '@magento/peregrine';
import { compose } from 'redux';
import { connect } from 'src/drivers';
import {
    array,
    bool,
    func,
    number,
    object,
    oneOf,
    shape,
    string
} from 'prop-types';
import {
    getCartDetails
} from 'src/actions/cart';

import {
    beginCheckout,
    cancelCheckout,
    editOrder,
    submitOrder,
    submitShippingMethod,
    submitPaymentMethod
} from 'src/actions/checkout';

import { submitShippingAddress, submitBillingAddress } from 'src/simi/Redux/actions/simiactions';

import classify from 'src/classify'
import defaultClasses from './checkout.css';
import TitleHelper from 'src/simi/Helper/TitleHelper'
import Identify from 'src/simi/Helper/Identify'
import BreadCrumb from "src/simi/BaseComponents/BreadCrumb"
import OrderSummary from "./OrderSummary"
import { configColor } from 'src/simi/Config'
import { Colorbtn } from 'src/simi/BaseComponents/Button'
import { Link } from 'react-router-dom';
import isObjectEmpty from 'src/util/isObjectEmpty';
import EditableForm from './editableForm';
import Panel from 'src/simi/BaseComponents/Panel';
import { toggleMessages, simiSignedIn } from 'src/simi/Redux/actions/simiactions';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';

const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();

const isCheckoutReady = checkout => {
    const {
        billingAddress,
        paymentData,
        shippingAddress,
        shippingMethod
    } = checkout;

    const objectsHaveData = [
        billingAddress,
        paymentData,
        shippingAddress
    ].every(data => {
        return !!data && !isObjectEmpty(data);
    });

    const stringsHaveData = !!shippingMethod && shippingMethod.length > 0;

    return objectsHaveData && stringsHaveData;
};

class Checkout extends Component {
    static propTypes = {
        beginCheckout: func,
        cancelCheckout: func,
        cart: shape({
            details: shape({
                items_count: number
            })
        }),
        checkout: shape({
            availableShippingMethods: array,
            billingAddress: object,
            editing: oneOf(['address', 'billingAddress', 'paymentMethod', 'shippingMethod']),
            invalidAddressMessage: string,
            isAddressInvalid: bool,
            paymentData: object,
            shippingAddress: object,
            shippingMethod: string,
            shippingTitle: string,
            step: oneOf(['cart', 'form', 'receipt']).isRequired,
            submitting: bool
        }).isRequired,
        classes: shape({
            root: string
        }),
        directory: object,
        editOrder: func,
        submitOrder: func,
        submitPaymentMethod: func,
        submitShippingAddress: func,
        submitShippingMethod: func,
        submitBillingAddress: func,
        user: object,
        simiSignedIn: func
    };

    constructor(...args) {
        super(...args)
        const isPhone = window.innerWidth < 1024
        this.state = {
            isPhone: isPhone
        };
    }


    setIsPhone() {
        const obj = this;
        window.onresize = function () {
            const width = window.innerWidth;
            const isPhone = width < 1024
            if (obj.state.isPhone !== isPhone) {
                obj.setState({ isPhone: isPhone })
            }
        }
    }

    async componentDidMount() {
        const { props, setIsPhone } = this;
        setIsPhone();

        const { beginCheckout, getCartDetails } = props;

        try {
            // get cart detail
            const aa = await getCartDetails();
            // The getCartDetails call is now done!
            if (typeof aa === 'undefined') {
                //beginning checkout
                beginCheckout();
            }

            // Do something
        } catch (err) {
            console.log(err)
        }
    }


    get breadcrumb() {
        return <BreadCrumb breadcrumb={[{ name: 'Home', link: '/' }, { name: 'Basket', link: '/cart.html' }, { name: 'Checkout', link: '/checkout.html' }]} />
    }

    handleLink(link) {
        this.props.history.push(link)
    }

    get cartId() {
        const { cart } = this.props;

        return cart && cart.details && cart.details.id;
    }

    get cartDetail() {
        const { cart } = this.props;

        return cart && cart.details && cart.details.items && cart.details.items.length;
    }

    get cartCurrencyCode() {
        const { cart } = this.props;
        return (
            cart &&
            cart.details &&
            cart.details.currency &&
            cart.details.currency.quote_currency_code
        );
    }

    handleBack() {
        this.props.history.goBack()
    }

    handleLink(link) {
        this.props.history.push(link)
    }

    placeOrder = () => {
        const { submitOrder, checkout, toggleMessages } = this.props;
        const { paymentData, shippingAddress, shippingMethod, billingAddress } = checkout;

        if (toggleMessages) {
            if (!shippingAddress || isObjectEmpty(shippingAddress)) {
                Identify.smoothScrollToView($("#id-message"));
                toggleMessages([{ type: 'error', message: Identify.__('Please choose a shipping address'), auto_dismiss: true }])
                return;
            }
            if (!billingAddress || isObjectEmpty(billingAddress)) {
                Identify.smoothScrollToView($("#id-message"));
                toggleMessages([{ type: 'error', message: Identify.__('Please choose a billing address'), auto_dismiss: true }])
                return;
            }
            if (!shippingMethod || !shippingMethod.length) {
                Identify.smoothScrollToView($("#id-message"));
                toggleMessages([{ type: 'error', message: Identify.__('Please choose a shipping method '), auto_dismiss: true }])
                return;
            }
            if (!paymentData || isObjectEmpty(paymentData)) {
                Identify.smoothScrollToView($("#id-message"));
                toggleMessages([{ type: 'error', message: Identify.__('Please choose a payment method'), auto_dismiss: true }])
                return;
            }
        }

        if (isCheckoutReady(checkout)) {
            showFogLoading();
            submitOrder();
        }
        return;
    }

    get btnPlaceOrder() {
        const { classes } = this.props;
        return (
            <div className={defaultClasses['btn-place-order']}>
                <Colorbtn
                    style={{ backgroundColor: configColor.button_background, color: configColor.button_text_color, width: '100%' }}
                    className={classes["go-place_order"]}
                    onClick={() => this.placeOrder()} text={Identify.__('PLACE ORDER')} />
            </div>
        )
    }

    get checkoutEmpty() {
        const { classes } = this.props;

        return <div className={classes['empty-cart-checkout']}>
            <span>{Identify.__("You have no items in your shopping cart.")}</span>
            <br />
            <span>
                {Identify.__('Click')}
                <Link to={'/'} >{Identify.__('here')}</Link>
                {Identify.__('to continue shopping.')}
            </span>
        </div>
    }

    get checkoutInner() {
        const { props, cartCurrencyCode, checkoutEmpty, btnPlaceOrder, cartDetail } = this;
        let { isPhone } = this.state;
        let containerSty = isPhone ? { marginTop: 35 } : {};
        const { classes,
            cart,
            checkout,
            directory,
            editOrder,
            submitShippingMethod,
            submitShippingAddress,
            submitOrder,
            submitPaymentMethod,
            submitBillingAddress,
            user,
            simiSignedIn } = props;
        const { shippingAddress,
            submitting,
            availableShippingMethods,
            shippingMethod,
            billingAddress,
            paymentData,
            invalidAddressMessage,
            isAddressInvalid,
            shippingTitle } = checkout;

        const { paymentMethods, is_virtual } = cart;
        let { editing } = checkout;

        const stepProps = {
            availableShippingMethods,
            billingAddress,
            cancelCheckout,
            cart,
            directory,
            editOrder,
            editing,
            hasPaymentMethod: !!paymentData && !isObjectEmpty(paymentData),
            hasShippingAddress:
                !!shippingAddress && !isObjectEmpty(shippingAddress),
            hasShippingMethod:
                !!shippingMethod && !isObjectEmpty(shippingMethod),
            invalidAddressMessage,
            isAddressInvalid,
            paymentData,
            ready: isCheckoutReady(checkout),
            shippingAddress,
            shippingMethod,
            shippingTitle,
            submitShippingAddress,
            submitOrder,
            submitPaymentMethod,
            submitBillingAddress,
            submitShippingMethod,
            submitting,
            paymentMethods,
            user,
            simiSignedIn
        };

        if (checkout.step && checkout.step === 'receipt') {
            this.handleLink('/thankyou.html');
        }

        hideFogLoading()

        return <React.Fragment>
            {this.breadcrumb}
            <div className={defaultClasses['checkout-page-title']}>{Identify.__("Checkout")}</div>
            {!cartDetail ? checkoutEmpty : <div className={defaultClasses['checkout-column']}>
                <div className={defaultClasses[`checkout-col-1`]}>
                    <Panel title={<div className={defaultClasses['checkout-section-title']}>{Identify.__('Shipping Address')}</div>}
                        renderContent={<EditableForm {...stepProps} editing='address' />}
                        isToggle={true}
                        expanded={true}
                        headerStyle={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}
                    />
                </div>
                <div className={defaultClasses[`checkout-col-2`]}>
                    <Panel title={<div className={defaultClasses['checkout-section-title']}>{Identify.__('Billing Information')}</div>}
                        renderContent={<EditableForm {...stepProps} editing='billingAddress' />}
                        isToggle={true}
                        expanded={true}
                        containerStyle={containerSty}
                        headerStyle={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}
                    />

                    {!is_virtual && <Panel title={<div className={defaultClasses['checkout-section-title']}>{Identify.__('Shipping Method')}</div>}
                        renderContent={<EditableForm {...stepProps} editing='shippingMethod' />}
                        isToggle={true}
                        expanded={true}
                        containerStyle={{ marginTop: 35 }}
                        headerStyle={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}
                    />}

                    <Panel title={<div className={defaultClasses['checkout-section-title']}>{Identify.__('Payment Method')}</div>}
                        renderContent={<EditableForm {...stepProps} editing='paymentMethod' />}
                        isToggle={true}
                        expanded={true}
                        containerStyle={{ marginTop: 35 }}
                        headerStyle={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}
                    />

                </div>
                <div className={defaultClasses[`checkout-col-3`]}>
                    <div className={defaultClasses['col-3-content']}>
                        <OrderSummary parent={this} isPhone={isPhone} cart={cart} cartCurrencyCode={cartCurrencyCode} checkout={checkout} />
                        {btnPlaceOrder}
                    </div>
                </div>
            </div>}
        </React.Fragment>
    }

    render() {
        return (
            <div className="container">
                {TitleHelper.renderMetaHeader({
                    title: Identify.__('Checkout')
                })}
                {this.checkoutInner}
            </div>
        );
    }
}

const mapStateToProps = ({ cart, checkout, directory, user }) => ({
    cart,
    checkout,
    directory,
    user
});

const mapDispatchToProps = {
    getCartDetails,
    beginCheckout,
    cancelCheckout,
    editOrder,
    submitShippingAddress,
    submitOrder,
    submitShippingMethod,
    submitBillingAddress,
    submitPaymentMethod,
    toggleMessages,
    simiSignedIn
};

export default compose(
    classify(defaultClasses),
    connect(
        mapStateToProps,
        mapDispatchToProps
    )
)(Checkout);
