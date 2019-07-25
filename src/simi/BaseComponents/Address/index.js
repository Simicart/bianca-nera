import React from 'react';

const AddressItem = (props) => {

    const { data, classes } = props;

    let add_ress_1, add_ress_2 = '';
    let dt_region = data.region ? data.region : '';
    if (data.street && Array.isArray(data.street) && data.street.length > 0) {
        add_ress_1 = data.street[0];
        add_ress_2 = data.street[1];
    }
    if (data.region && Array.isArray(data.region)) {
        dt_region = data.region.join(', ')
    }
    if (data.region && data.region instanceof Object) {
        if (data.region.hasOwnProperty('region')) {
            dt_region = data.region.region;
        }
    }

    return (data ? data.firstname && <ul className={classes["address-item"]}>
        <li className={classes['customer-name']}>{data.firstname + " " + data.lastname}</li>
        <li className={classes['street']}>{add_ress_1}</li>
        {add_ress_2 && <li className={classes['street']}>{add_ress_2}</li>}
        <li className={classes['city']}>{data.city + ", " + dt_region}</li>
        <li className={classes['zipcode']}>{data.postcode}</li>
        <li className={classes['country']}>{data.country_name}</li>
        <li className={classes['telephone']}>{"T: " + data.telephone}</li>
    </ul> : null)

}

export default AddressItem;

// city: "man"
// country_id: "AE"
// email: "asdf@gmail.com"
// firstname: "alalal"
// lastname: "kksks"
// postcode: "10000"
// region: "Louisiana"
// region_code: "LA"
// region_id: "28"
// street: ["asdkfl"]
// telephone: "0987116226"
