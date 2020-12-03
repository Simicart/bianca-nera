import React, { useRef, useState } from 'react';
import Identify from "src/simi/Helper/Identify";
import { connect } from 'src/drivers';
import { toggleMessages } from 'src/simi/Redux/actions/simiactions';
import {showToastMessage} from 'src/simi/Helper/Message';
import { SimiMutation } from 'src/simi/Network/Query';
import {validateEmail, validateEmpty} from 'src/simi/Helper/Validation';
import Loading from "src/simi/BaseComponents/Loading";
import MUTATION_GRAPHQL from 'src/simi/queries/guestSubscribe.graphql'

const Subscriber = props => {
    const emailInput = useRef('');
    const mutationRef = useRef('');
    const [invalid, setInvalid] = useState('');
    const [submited, setSubmited] = useState(false);
    const [submitedVer, setSubmitedVer] = useState(1);
    const [waiting, setWaiting] = useState(false);

    const formAction = (e) => {
        e.preventDefault();
        if (validateForm()) {
            $(mutationRef.current).trigger('click');
        }
        setSubmited(true);
    }

    const responseLoaded = (data) => {
        if (data && data.subscribe && data.subscribe.message) {
            setWaiting(false);
            if (data.subscribe.status === 'error') {
                showToastMessage(data.subscribe.message);
            }
            if (data.subscribe.status === '1') {
                if (waiting) {
                    props.toggleMessages([{
                        type: 'success',
                        message: data.subscribe.message,
                        auto_dismiss: true
                    }]);
                    emailInput.current.value = ''; //reset form
                }
            }
            setSubmitedVer(submitedVer + 1);
        }
    }

    const validateForm = () => {
        if (!validateEmail(emailInput.current.value)) {
            setInvalid('error invalid');
            return false;
        }
        setInvalid('');
        return true;
    }

    const validateChange = () => {
        if (submited) {
            validateForm();
        }
    }

    const checkEmpty = () => {
        if(!validateEmpty(emailInput.current.value)){
            setInvalid('');
        }
    }

    const className = props.className ? props.className : '';
    const classForm = `${className} subscriber-form ${invalid}`;
    
    return (
        <React.Fragment>
            <SimiMutation mutation={MUTATION_GRAPHQL} key={submitedVer}>
                {(mutationApi, { loading, data }) => {
                    if (!loading && data && data.subscribe && data.subscribe.message) {
                        responseLoaded(data);
                        return null;
                    }
                    return (
                        <button onClick={e => {
                            mutationApi({variables: {email: emailInput.current.value}});
                            setWaiting(true);
                        }} ref={mutationRef} style={{display: 'none'}}/>
                    )
                }}
            </SimiMutation>
            <div className={classForm}>
                <form className={props.formClassName} onSubmit={formAction}>
                    <label htmlFor="subcriber-email">{Identify.__('Email *')}</label>
                    <input id="subcriber-email" onKeyUp={checkEmpty} onChange={validateChange} ref={emailInput} name="email" className={`email ${invalid}`} />
                    <button type="submit"><i className="icon-arrow-right icons"></i></button>
                </form>
            </div>
            <div className={`message ${invalid}`}>{Identify.__('Your email address is invalid. Please enter a valid address!')}</div>
            { waiting && <Loading/> }
        </React.Fragment>
    )
}

const mapDispatchToProps = {
    toggleMessages,
}

export default connect(
    null,
    mapDispatchToProps
)(Subscriber);