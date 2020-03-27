import React from 'react';
import PropTypes from 'prop-types';
import { Form } from 'informed';
import Field from 'src/components/Field';
import TextInput from 'src/components/TextInput';
import { isRequired } from 'src/util/formValidators';
import Identify from 'src/simi/Helper/Identify'
import classes from './forgotPasswordForm.css';

const ForgotPasswordForm  =  props => {
    const { history, onSubmit } = props;

    return (
        <Form
            className={`${classes.root} ${Identify.isRtl() ? classes['rtl-rootForm'] : null}`}
            onSubmit={onSubmit}
        >
            <Field label={Identify.__('Email *')} required={true}>
                <TextInput
                    autoComplete="email"
                    field="email"
                    validate={isRequired}
                    validateOnBlur
                    placeholder={Identify.__('Email')}
                />
            </Field>
            <div className={classes.buttonContainer}>
                <button 
                    priority="high" className={classes.submitButton} type="submit" 
                >
                    {Identify.__('Submit')}
                </button>
            </div>
        </Form>
    )
}

ForgotPasswordForm.propTypes = {
    onSubmit: PropTypes.func.isRequired
}

export default ForgotPasswordForm;
