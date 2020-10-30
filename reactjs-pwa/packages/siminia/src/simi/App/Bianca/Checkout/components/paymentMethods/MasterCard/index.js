import React, { useEffect, useState } from 'react';
import Identify from 'src/simi/Helper/Identify';
import { connect } from 'src/drivers';
import { setPaymentInfomation } from 'src/simi/Redux/actions/simiactions';
import CCForm from '../CCForm';
import { addMerchantUrl } from 'src/simi/Helper/Network';
import Modal from 'react-responsive-modal';
import CloseIcon from 'src/simi/App/Bianca/BaseComponents/Icon/Close';
import { Util } from '@magento/peregrine';
const { BrowserPersistence } = Util;
import { showToastMessage } from 'src/simi/Helper/Message';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';

require('./style.scss');

const MasterCard = (props) => {

    const { paymentContent, payment_method, onSuccess, setPaymentInfomation, cart, initialValues } = props;
    const [ sessionId, setSessionId ] = useState(initialValues.session || '');
    const [ savedCard, setSavedCard ] = useState(Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, 'cc_card_data') || '');
    const [ openModal, setOpenModal ] = useState(false);

    const reqHttp = (url, payload, callback, method = 'POST', 
            header = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        ) => {
        const request = new Request(url, {
            'method': method || 'POST',
            'headers': header,
            'body': JSON.stringify(payload)
        });
        return fetch(request).then(response => {
                if (response.ok) {
                    return response.json();
                }
                if (response.ok === false) {
                    let data = {
                        errors: [],
                        status: response.status,
                        statusText: response.statusText
                    };
                    try{
                        return response.text().then((text) => {
                            data.errors = [JSON.parse(text)];
                            return data;
                        });
                    } catch (err){}
                }
            })
            .then((data) => {
                if (data[0]) data = data[0];
                callback && callback(data);
                return data;
            }).catch((error) => {
                console.warn(error);
            });
    }

    const getEndPoint = () => {
        return paymentContent && paymentContent.component_url && paymentContent.component_url.replace('.js', '') || '';
    }

    const createSession = (cb) => {
        if (!paymentContent || !paymentContent.component_url) {
            console.warn('Can not create master card checkout session. Payment data error.');
            return;
        };
        const endPoint = getEndPoint();
        reqHttp('/rest/V1/tns/mastercard/session', {}, cb);
    }

    const applyCard = (card, cb) => {
        const endPoint = getEndPoint()+'/'+sessionId;
        reqHttp('/rest/V1/tns/mastercard/session/'+sessionId, [
            {
                field: 'sourceOfFundsType',
                value: 'card'
            },
            {
                field: 'number',
                value: card.number && card.number.replace(/\s|-/g, '') || ''
            },
            {
                field: 'securityCode',
                value: card.cvc
            },
            {
                field: 'expiryMonth',
                value: card.exp_month
            },
            {
                field: 'expiryYear',
                value: card.exp_year
            }
        ], cb, 'POST');
    }

    const startTransaction = (cb) => {
        const endPoint = getEndPoint()+'/'+sessionId;
        reqHttp('/rest/V1/tns/mastercard/session/'+sessionId, {frameEmbeddingMitigation: ["x-frame-options"]}, cb, 'PUT');
    }

    const checkMethod = (cb) => {
        let headers = {
            'Accept': '*/*',
            'Accept-Encoding': 'gzip, deflate',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
        const storage = new BrowserPersistence();
        const token = storage.getItem('signin_token');
        if (token) headers['authorization'] = `Bearer ${token}`;
        const endPoint = addMerchantUrl('tns/threedsecure/check/method/hpf/quote_id/'+cart.cartId)
        reqHttp(endPoint, {}, (result)=>{
            cb(result);
        }, 'POST', headers);
    }

    const submitCard = (card) => {
        if (sessionId) {
            showFogLoading();
            setTimeout(()=>{hideFogLoading()}, 30000); // no wait forever
            applyCard(card, ({status}) => {
                if (status === 'ok') {
                    startTransaction((data)=>{
                        if (data && data.status === 'system_error') {
                            hideFogLoading();
                            data.errors && showToastMessage(Identify.__(data.errors.message + ' Please try again.'));
                            createSession(({session}) => {
                                setSessionId(session && session.id || 'failed');
                            });
                            return;
                        }
                        if (data && data.session && data.session.updateStatus === 'SUCCESS') {
                            Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'cc_card_data', card);
                            if (paymentContent && paymentContent.three_d_secure) {
                                showFogLoading();
                                setPaymentInfomation({
                                    additional_data: {
                                        session: data.session.id
                                    },
                                    data: {
                                        label: paymentContent.title,
                                        value: payment_method,
                                        session: data.session.id,
                                        cc_token: data.session.id
                                    },
                                    code: 'tns_hpf',
                                }, (response) => {
                                    hideFogLoading();
                                });
                            } else {
                                onSuccess({
                                    cc_token: data.session.id, // pass data to async action additional_data
                                    session: data.session.id
                                });
                                hideFogLoading();
                            }
                        }
                    });
                }
            });
        }
    }

    useEffect(() => {
        if (!initialValues || initialValues && initialValues.setPayment !== 'success') {
            if (!sessionId) {
                createSession(({session}) => {
                    console.log(session)
                    setSessionId(session && session.id || 'failed');
                });
            }
        }
        if (initialValues && initialValues.setPayment === 'success') {
            showFogLoading();
            checkMethod((result)=>{
                hideFogLoading();
                if (result && result.result === 'Y') {
                    setOpenModal(true);
                } else if(result && result.message) { // else show error when check failed
                    showToastMessage(Identify.__(result.message));
                }
            });
        }
        // callback from MasterCard response confirm 3D Secure
        window.tnsThreeDSecureClose = () => {
            setOpenModal(false);
        }
    }, []);

    const onCloseModal = () => {
        setOpenModal(false);
    }

    const returnUrl = window.btoa(window.location.origin+'/tns/threedsecure/response/quote_id/'+cart.cartId+'?simiforceproxy=1');

    return (
        <React.Fragment>
            <CCForm submitCard={submitCard} savedCard={savedCard} noHolderName={true} {...props} />
            <Modal open={openModal} onClose={onCloseModal}
                overlayId={'three_ds_overlay'}
                modalId={'three_ds_modal'}
                closeIconId={'three_ds_modal_close'}
                closeIconSize={16}
                closeIconSvgPath={<CloseIcon style={{ fill: '#101820' }} />}
                classNames={{ overlay: Identify.isRtl() ? "rtl-root" : "" }}
            >
                <iframe data-role="tns-threedsecure-iframe" style={{width: '100%',minHeight: '420px'}}
                    src={addMerchantUrl('tns/threedsecure/form/quote_id/'+cart.cartId+'?simiforceproxy=1&return_url_base64='+returnUrl)} frameBorder="0"></iframe>
            </Modal>
        </React.Fragment>
    );
}

const mapDispatchToProps = {
    setPaymentInfomation,
};

// export default MasterCard;
export default connect(null, mapDispatchToProps)(MasterCard);