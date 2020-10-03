import React, { useState } from 'react';
import Identify from 'src/simi/Helper/Identify';
import { isRequired, validateEmail } from 'src/util/formValidators';
import { Form, Text, TextArea } from 'informed';
import { Colorbtn } from 'src/simi/BaseComponents/Button';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading'
import { sendContact } from 'src/simi/Model/Contact';
import { showToastMessage } from 'src/simi/Helper/Message';

require('./Form.scss');

// const $ = window.$;
const ContactForm = (props) => {

    const [formKey, setFormKey] = useState(Identify.randomString(3));

    const submitForm = (values) => {
        showFogLoading();
        sendContact(values, submitComplete);
    }

    const submitComplete = (data) => {
        hideFogLoading();
        if(data && data.errors && data.errors.length){
            const errors = data.errors.map(error => {
                return error.message || '';
            });
            showToastMessage(errors.join(', '));
        } else {
            // resetRef.current.click();
            setFormKey(Identify.randomString(3));
            showToastMessage(Identify.__('Thank you, we will contact you soon'));
        }
    }

    return (
        <div className='form-container'>
            <div className={'contact-title'}>
                <h3>{Identify.__("SEND US A MESSAGE")}</h3>
            </div>
            <Form onSubmit={submitForm} key={formKey}>
                {({ formState: { errors } }) => (
                    <div>
                        <div className='form-group'>
                            <Text field="name" className={`${errors.name ? 'error':''}`} validate={isRequired} placeholder={Identify.__('Your name here') + ' *'} validateOnBlur/>
                            {errors.name && <div className="error-message">{errors.name}</div>}
                        </div>
                        <div className='form-group'>
                            <Text field="email" className={`${errors.email ? 'error':''}`} validate={validateEmail} placeholder={Identify.__('E-mail address') + ' *'} validateOnBlur/>
                            {errors.email && <div className="error-message">{errors.email}</div>}
                        </div>
                        <div className="form-group fg-textarea">
                            <TextArea field="message" className={`${errors.message ? 'error':''}`} validate={isRequired} placeholder={Identify.__('Type your message') + ' *'} validateOnBlur/>
                            {errors.message && <div className="error-message">{errors.message}</div>}
                        </div>
                        <div className='form-group button'>
                            <Colorbtn type="submit" className={'submit-btn'} text={Identify.__("SEND")}/>
                        </div>
                    </div>
                )}
            </Form>
        </div>
    );
}

export default ContactForm;
