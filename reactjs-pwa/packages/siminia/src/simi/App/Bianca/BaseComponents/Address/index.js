import React from 'react';
import Identify from 'src/simi/Helper/Identify';

require('./style.scss');

const AddressItem = (props) => {

    const { data } = props;
    const classes = props.classes?props.classes:{}
    let add_ress_1, add_ress_2, add_ress_3 = '';
    let dt_region = data.region ? data.region : '';
    if (data.street && Array.isArray(data.street) && data.street.length > 0) {
        add_ress_1 = data.street[0];
        add_ress_2 = data.street[1];
        add_ress_3 = data.street[2];
    }
    if (data.region && Array.isArray(data.region)) {
        dt_region = data.region.join(', ')
    }
    if (data.region && data.region instanceof Object) {
        if (data.region.hasOwnProperty('region')) {
            dt_region = data.region.region;
        }
    }

    let name = data.firstname;
    if (data.middlename) name += ' ' + data.middlename;
    if (data.lastname) name += ' ' + data.lastname;

    let block;
    if (data.extension_attributes && data.extension_attributes.block) {
        block = data.extension_attributes.block;
    }

    return (data ? data.firstname && <ul className={`${classes["address-item"]} address-item`}>
        <li className={`${classes['customer-name']} customer-name`}>{name}</li>
        <li className={`${classes['street']} street`}>{add_ress_1}</li>
        {add_ress_2 && <li className={`${classes['street']} street`}>{add_ress_2}</li>}
        {add_ress_3 && <li className={`${classes['street']} street`}>{add_ress_3}</li>}
        <li className={`${classes['city']} city`}>{data.city + ", " + dt_region}</li>
        {data.state && <li className={`${classes['state']} state`}>{data.state}</li>}
        <li className={`${classes['country']} country`}>{data.country_name}</li>
        <li className={`${classes['zipcode']} zipcode`}><label>{Identify.__('Post/Zip Code')}: </label><span>{data.postcode}</span></li>
        {block && <li className={`${classes['block']} block`}><label>{Identify.__('Block')}: </label><span>{block}</span></li>}
        <li className={`${classes['telephone']} telephone`}><label>{Identify.__('Phone')}: </label><span>{data.telephone}</span></li>
    </ul> : null)

}

export default AddressItem;
