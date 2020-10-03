import React, { useEffect } from 'react';
import ContactForm from './Components/Form';
import Info from './Components/Info';
// import Loading from 'src/simi/BaseComponents/Loading';
import TitleHelper from 'src/simi/Helper/TitleHelper';
import Identify from 'src/simi/Helper/Identify';
import BreadCrumb from "src/simi/BaseComponents/BreadCrumb"

require("./style.scss");

const Contact = (props) => {

    useEffect(() => {
        window.scrollTo(0, 0);
    });

    return (
        <div className="contact-page">
            {TitleHelper.renderMetaHeader({
                title: Identify.__("Contact"),
                desc: Identify.__("Contact")
            })}
            <BreadCrumb breadcrumb={[{name: Identify.__('Home'),link:'/'},{name: Identify.__('Contact Us')}]}/>

            <div className="container">
                <div className="row row-banner">
                    <div className="col-xs-12">
                        <img style={{width: '100%'}} src="/images/contact_banner.png" alt={Identify.__("Contact Us")}/>
                    </div>
                </div>
                <div className="row row-content">
                    <div className="col-xs-12 col-sm-4">
                        <Info/>
                    </div>
                    <div className="col-xs-12 col-sm-8">
                        <ContactForm/>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Contact;