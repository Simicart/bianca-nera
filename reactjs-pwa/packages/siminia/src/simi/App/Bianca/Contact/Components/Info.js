import React from 'react';
import Identify from 'src/simi/Helper/Identify';

require('./Info.scss');

const Info = (props) => {

    const storeConfig = Identify.getStoreConfig();
    const { simiStoreConfig } = storeConfig || {};
    const { config } = simiStoreConfig || {};
    const { header_footer_config } = config || {};
    const { bianca_footer_phone, bianca_footer_email } = header_footer_config || {};

    return (
        <div className={'contact-info'}>
            <div className={'contact-title'}>
                <h2 style={{textTransform: 'uppercase'}}>{Identify.__('CONTACT US')}</h2>
            </div>
            <ul>
                {bianca_footer_phone && 
                <li>
                    <span className="label">{Identify.__('Tel')}:</span>
                    <span><a href={`tel:${bianca_footer_phone && bianca_footer_phone.replace('+', '') || ''}`}>+0965 555 5455 731</a></span>
                </li>}
                {bianca_footer_email && 
                <li>
                    <span className="label">{Identify.__('Email')}:</span>
                    <span><a href={`mailto:${bianca_footer_email}`}>{bianca_footer_email}</a></span>
                </li>
                }
                <li>
                    <span className="label">{Identify.__('Website')}:</span>
                    <span>{Identify.__('https://www.bianca-nera.com/')}</span>
                </li>
            </ul>
        </div>
    )
}

export default Info;