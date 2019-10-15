import React, { Component } from 'react';
import defaultClasses from './login.css';
import classify from 'src/classify';
import Identify from 'src/simi/Helper/Identify';
import SignIn from './SignIn';
import VendorLogInForm from './VendorLogin'
import CreateAccount from './CreateAccount';
import ForgotPassword from './ForgotPassword';
import { connect } from 'src/drivers';
import { compose } from 'redux';
import BackIcon from 'src/simi/BaseComponents/Icon/TapitaIcons/Back';
import { withRouter } from 'react-router-dom';
import TitleHelper from 'src/simi/Helper/TitleHelper'
import { toggleMessages } from 'src/simi/Redux/actions/simiactions';
import { simiSignIn as signinApi, vendorLogin } from 'src/simi/Model/Customer'
import {showFogLoading, hideFogLoading} from 'src/simi/BaseComponents/Loading/GlobalLoading'
import  * as Constants from 'src/simi/Config/Constants'
import { Util } from '@magento/peregrine'
import { simiSignedIn } from 'src/simi/Redux/actions/simiactions';
import {showToastMessage} from 'src/simi/Helper/Message';
import VendorRegister from './VendorRegister';

const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();
class Login extends Component {
    state = {
        isCreateAccountOpen: false,
        isSignInOpen: true,
        isForgotPasswordOpen: false,
        isVendorLoginOpen: false,
        isVendorRegisterOpen: false
    };

    stateForgot = () => {
        const {history} = this.props;

        return history.location && history.location.state && history.location.state.forgot;
    }

    componentDidMount(){
        if (this.stateForgot()){
            this.setForgotPasswordForm()
        }

    }

    get signInForm() {
        const { isSignInOpen } = this.state;
        const { classes } = this.props;
        const isOpen = isSignInOpen;
        const className = isOpen ? classes.signIn_open : classes.signIn_closed;

        return (
            <div className={className}>
                <SignIn
                    classes={classes}
                    showCreateAccountForm={this.setCreateAccountForm}
                    onForgotPassword={this.setForgotPasswordForm}
                    onSignIn={this.onSignIn.bind(this)}
                />
            </div>
        );
    }

    get vendorLogInForm() {
        const { isVendorLoginOpen } = this.state;
        const { classes } = this.props;
        const isOpen = isVendorLoginOpen;
        const className = isOpen ? classes.signIn_open : classes.signIn_closed;

        return (
            <div className={className}>
                <VendorLogInForm
                    classes={classes}
                    showVendorRegisterForm={this.setVendorRegisterForm}
                    onForgotPassword={this.setForgotPasswordForm}
                    onSignIn={this.onVendorLogin.bind(this)}
                />
            </div>
        );
    }

    vendorRegister = () => {};
    createAccount = () => {};

    setVendorRegisterForm = () => {
        this.vendorRegister = className => {
            return (
                <div className={className}>
                    <VendorRegister
                        onSignIn={this.onVendorLogin.bind(this)}
                    />
                </div>
            )
        }
        this.showVendorRegisterForm();
    }

    setCreateAccountForm = () => {
        this.createAccount = className => {
            return (
                <div className={className}>
                    <CreateAccount
                        onSignIn={this.onSignIn.bind(this)}
                    />
                </div>
            );
        };
        this.showCreateAccountForm();
    };

    forgotPassword = () => {}

    setForgotPasswordForm = () => {
        this.forgotPassword = className => {
            return (
                <div className={className}>
                    <ForgotPassword
                        onClose={this.closeForgotPassword}
                    />
                </div>
            );
        };
        this.showForgotPasswordForm();
    };

    closeForgotPassword = () => {
        this.hideForgotPasswordForm();
    };

    get vendorRegisterForm(){
        const { isVendorRegisterOpen } = this.state;
        const { classes } = this.props;
        const isOpen = isVendorRegisterOpen;
        const className = isOpen ? classes.form_open : classes.form_closed;

        return this.vendorRegister(className);
    }

    get createAccountForm() {
        const { isCreateAccountOpen } = this.state;
        const { classes } = this.props;
        const isOpen = isCreateAccountOpen;
        const className = isOpen ? classes.form_open : classes.form_closed;

        return this.createAccount(className);
    }

    get forgotPasswordForm() {
        const { isForgotPasswordOpen } = this.state;
        const { classes } = this.props;
        const isOpen = isForgotPasswordOpen;
        const className = isOpen ? classes.form_open : classes.form_closed;
        return this.forgotPassword(className);
    }

    showVendorRegisterForm = () => {
        this.setState(() => ({
            isCreateAccountOpen: false,
            isSignInOpen: false,
            isForgotPasswordOpen: false,
            isVendorLoginOpen: false,
            isVendorRegisterOpen: true
        }));
    }

    showCreateAccountForm = () => {
        this.setState(() => ({
            isCreateAccountOpen: true,
            isSignInOpen: false,
            isForgotPasswordOpen: false,
            isVendorLoginOpen: false,
            isVendorRegisterOpen: false
        }));
    };

    showForgotPasswordForm = () => {
        this.setState(() => ({
            isForgotPasswordOpen: true,
            isSignInOpen: false,
            isCreateAccountOpen: false,
            isVendorLoginOpen: false,
            isVendorRegisterOpen: false
        }));
    };

    showLoginForm = () => {
        this.setState(() => ({
            isForgotPasswordOpen: false,
            isSignInOpen: true,
            isCreateAccountOpen: false,
            isVendorLoginOpen: false,
            isVendorRegisterOpen: false
        }));
    };

    showVendorLoginForm = () => {
        this.setState(() => ({
            isForgotPasswordOpen: false,
            isSignInOpen: false,
            isCreateAccountOpen: false,
            isVendorLoginOpen: true,
            isVendorRegisterOpen: false
        }));
    }

    onSignIn(username, password) {
        Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, null)
        signinApi(this.signinCallback.bind(this), { username, password })
        showFogLoading()
    };

    onVendorLogin(username, password) {
        Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, null)
        vendorLogin(this.vendorLoginCallback.bind(this), { username, password })
        showFogLoading()
    };

    vendorLoginCallback = (data) => {
        hideFogLoading()
        console.log(data);
        window.location.href = data.redirect_url
    }

    signinCallback = (data) => {
        hideFogLoading()
        if (this.props.simiSignedIn) {
            if (data && !data.errors) {
                storage.removeItem('cartId');
                storage.removeItem('signin_token');
                if (data.customer_access_token) {
                    Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, data.customer_identity)
                    setToken(data.customer_access_token)
                    this.props.simiSignedIn(data.customer_access_token)
                } else {
                    Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, null)
                    setToken(data)
                    this.props.simiSignedIn(data)
                }
            }
            else
                showToastMessage(Identify.__('The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.'))
        }
    }

    render() {
        const {
            createAccountForm,
            signInForm,
            forgotPasswordForm,
            showLoginForm,
            vendorLogInForm,
            vendorRegisterForm,
            props,
            state
        } = this;
        const {
            isCreateAccountOpen,
            isForgotPasswordOpen,
            isSignInOpen,
            isVendorLoginOpen,
        } = state;

        const {
            classes,
            isSignedIn,
            firstname,
            history
        } = props;

        if (isSignedIn) {
            if (history.location.hasOwnProperty('pushTo') && history.location.pushTo){
                const {pushTo} = history.location;
                history.push(pushTo)
            }else{
                history.push('/')
            }

            const message = firstname?
                Identify.__("Welcome %s Start shopping now").replace('%s', firstname):
                Identify.__("You have succesfully logged in, Start shopping now")
            if (this.props.toggleMessages)
                this.props.toggleMessages([{type: 'success', message: message, auto_dismiss: true}])
        }
        const showBackBtn = isCreateAccountOpen || isForgotPasswordOpen

        // const title =
        //     isCreateAccountOpen
        //         ? Identify.__('Create Account')
        //         : isForgotPasswordOpen
        //         ? Identify.__('Forgot password')
        //         : Identify.__('Sign In')

        return (
            <React.Fragment>
                {TitleHelper.renderMetaHeader({
                    title:Identify.__('Customer Login')
                })}
                <div className={classes['login-background']} >
                    <div className={classes['login-container']} >
                        <div className={classes['login-tab']}>
                            <div onClick={this.showLoginForm} className={`${classes['buyer-tab']} ${isSignInOpen ? classes["active"]: null}`}>
                                <span>{Identify.__("Buyer".toUpperCase())}</span>
                            </div>
                            <div onClick={this.showVendorLoginForm} className={isVendorLoginOpen ? classes["active"]: null}>
                                {Identify.__("Seller".toUpperCase())}
                            </div>
                        </div>
                        <div className={`${classes['login-header']} ${showBackBtn&&classes['has-back-btn']}`}>
                            {
                                (showBackBtn) &&
                                <div role="presentation"
                                    className={classes['login-header-back']}
                                    onClick={showLoginForm}
                                    >
                                    <BackIcon style={{width: 20, height: 20}}/>
                                </div>
                            }
                            {/* <div className={classes['login-header-title']}>
                                {title}
                            </div> */}
                        </div>
                        {signInForm}
                        {vendorLogInForm}
                        {createAccountForm}
                        {vendorRegisterForm}
                        {forgotPasswordForm}
                    </div>
                </div>
            </React.Fragment>
        );
    }
}

const mapStateToProps = ({ user }) => {
    const { currentUser, isSignedIn, forgotPassword } = user;
    const { firstname, email, lastname } = currentUser;

    return {
        email,
        firstname,
        forgotPassword,
        isSignedIn,
        lastname,
    };
};

const mapDispatchToProps = {
    toggleMessages,
    simiSignedIn
};

export default compose(
    classify(defaultClasses),
    withRouter,
    connect(
        mapStateToProps,
        mapDispatchToProps
    )
)(Login);


async function setToken(token) {
    // TODO: Get correct token expire time from API
    return storage.setItem('signin_token', token, 3600);
}