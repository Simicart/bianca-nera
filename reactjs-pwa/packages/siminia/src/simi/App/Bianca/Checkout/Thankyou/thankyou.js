import React, {useState} from 'react';
import {  func, shape, string } from 'prop-types';
import { getOrderInformation } from 'src/selectors/checkoutReceipt';
import { connect } from 'src/drivers';
import actions from 'src/actions/checkoutReceipt';
import Identify from 'src/simi/Helper/Identify';
import TitleHelper from 'src/simi/Helper/TitleHelper'
import { Colorbtn } from 'src/simi/BaseComponents/Button'
import {getOrderDetail} from 'src/simi/Model/Orders'
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';
import { analyticPurchaseGTM } from 'src/simi/Helper/Analytics'

require('./thankyou.scss')

const Thankyou = props => {
    const {  history, order, isSignedIn } = props;
    let padOrderId = null
    const last_cart_info = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, 'last_cart_info');
    let last_order_info = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, 'last_order_info');
    const [orderIncIdFromAPI, setOrderIncFromAPI] = useState(false)
    const [orderData, setOrderData] = useState()

    let isPreOrder = false
    try {
        const items = last_cart_info.cart.totals.items
        items.map(item => {
            if(item.simi_pre_order_option)
                isPreOrder = true
        })
    } catch (err) {

    }

    const debugOrderId = Identify.findGetParameter('debug_order_id');
    if (debugOrderId) {
        last_order_info = debugOrderId;
    }

    if (last_order_info) {
        if (orderIncIdFromAPI)
            padOrderId = orderIncIdFromAPI
        else {
            showFogLoading()
            getOrderDetail(last_order_info, (orderData) => {
                hideFogLoading()
                if (orderData && orderData.order && orderData.order.increment_id) {
                    analyticPurchaseGTM(orderData.order)
                    setOrderIncFromAPI(orderData.order.increment_id)
                    setOrderData(orderData.order)
                }
            })
        }
    }

    const hasOrderId = () => {
        return (order && order.id) ||  Identify.findGetParameter('order_increment_id') || last_order_info;
    }

    const handleViewOrderDetails = () => {
        if (!hasOrderId()) {
            history.push('/');
            return;
        }
        const orderId = '/orderdetails.html/' + padOrderId;
        const orderLocate = {
            pathname: orderId,
            state: {
                orderData: {
                    increment_id: padOrderId
                }
            }
        }
        history.push(orderLocate);
    }

    const getDateFormat = dateData => {
        const date = new Date(dateData.replace(/\s+/g, 'T').concat('.000+00:00'));
        const day = date.getDate();
        const month =
            date.getMonth() + 1 < 10
                ? "0" + (date.getMonth() + 1)
                : date.getMonth() + 1;
        const year = date.getFullYear();
        return day + "/" + month + "/" + year;
    };
    
    return (
        <div className="container thankyou-container" style={{ marginTop: 40 }}>
            {TitleHelper.renderMetaHeader({
                title:Identify.__('Thank you for your purchase!')
            })}
            <div className="thankyou-root">
                <h2 className='header'>{Identify.__('Thank you for your purchase!')}</h2>
                <div  className="email-sending-message">
                    {padOrderId && <div className="order-number">{Identify.__('Order your number is #@').replace('@', padOrderId)}</div>}
                    {orderData && orderData.payment_information && orderData.payment_information.tranid &&
                        <div className="payment_information">
                            {/* <div className="payment_title">{Identify.__('%s').replace('%s', orderData.payment_information.method_title)}</div> */}
                            <div className="payment_info">
                                {orderData.payment_information.method_title} {Identify.__('Transaction Id %s').replace('%s', orderData.payment_information.tranid)}<br/>
                                {Identify.__('Payment Id %s').replace('%s', orderData.payment_information.paymentid)}<br/>
                                {Identify.__('Result %s').replace('%s', orderData.payment_information.result)}<br/>
                                {Identify.__('Pay date %s').replace('%s', getDateFormat(orderData.created_at))}<br/>
                            </div>
                        </div>
                    }
                    {isPreOrder && <div className="order-preorder-note">{Identify.__('Please be aware that this is a preorder. You will be informed once they become available.')}</div>}
                    {Identify.__("We'll email you an order confirmation with details and tracking info.")}
                </div>
                <div className="order-actions">
                    {(isSignedIn && hasOrderId()) && <Colorbtn 
                        onClick={handleViewOrderDetails}
                        style={{ backgroundColor: '#101820', color: '#FFF' }}
                        className="view-order-details"
                        text={Identify.__('View Order Details')} />}
                    <Colorbtn 
                        onClick={()=>history.push('/')}
                        style={{ backgroundColor: '#101820', color: '#FFF' }}
                        className="continue-shopping"
                        text={Identify.__('Continue shopping')} />
                </div>
            </div>
        </div>
    );
};

Thankyou.propTypes = {
    order: shape({
        id: string
    }).isRequired,
    createAccount: func.isRequired,
    reset: func.isRequired
};

Thankyou.defaultProps = {
    order: {},
    reset: () => { },
    createAccount: () => { }
};

const { reset } = actions;

const mapStateToProps = state => {
    const { user} = state;
    const { isSignedIn } = user;
    return ({
        order: getOrderInformation(state),
        isSignedIn
    });
}

const mapDispatchToProps = {
    reset
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Thankyou);
