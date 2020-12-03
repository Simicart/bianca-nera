import React from 'react';
import Identify from 'src/simi/Helper/Identify';

require('./style.scss');

const HOUSE_NUMBER_LABEL = 'House No.';
const APARTMENT_NUMBER_LABEL = 'Apartment No.';

const AddressItem = (props) => {

    const { data } = props;
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

    return (data ? data.firstname && <ul className="address-item">
        <li className="customer-name">{name}</li>
        <li className="street">{add_ress_1}</li>
        {add_ress_2 && <li className="street">{Identify.__(HOUSE_NUMBER_LABEL)}{add_ress_2}</li>}
        {add_ress_3 && <li className="street">{Identify.__(APARTMENT_NUMBER_LABEL)}{add_ress_3}</li>}
        <li className="city">{data.city ? data.city + ", " : ''}{dt_region}</li>
        {data.state && <li className="state">{data.state}</li>}
        <li className="country">{data.country_name}</li>
        <li className="zipcode"><label>{Identify.__('Post/Zip Code')}: </label><span>{data.postcode}</span></li>
        {block && <li className="block"><label>{Identify.__('Block')}: </label><span>{block}</span></li>}
        <li className="telephone"><label>{Identify.__('Phone')}: </label><span>{data.telephone}</span></li>
    </ul> : null)

}

export default AddressItem;
