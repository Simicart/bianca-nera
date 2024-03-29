import React, { useEffect } from 'react';
import Identify from "src/simi/Helper/Identify";
import {Link} from 'react-router-dom'
import { Whitebtn } from 'src/simi/BaseComponents/Button';
import OrderHistory from 'src/simi/App/Bianca/Customer/Account/Components/Orders/OrderList';
import Loading from "src/simi/BaseComponents/Loading";
import { simiUseQuery } from 'src/simi/Network/Query' 
import getCustomerInfoQuery from 'src/simi/queries/getCustomerInfo.graphql'
import AddressItem from './AddressItem'; 
import TitleHelper from 'src/simi/Helper/TitleHelper';
import Pencil from 'src/simi/BaseComponents/Icon/Pencil';

const Dashboard = props => {
    const {isPhone, customer} = props;
    const [queryResult, queryApi] = simiUseQuery(getCustomerInfoQuery, false);
    const {data} = queryResult
    const { runQuery } = queryApi;

    const getCustomerInfo = () => {
        runQuery({});
    }

    useEffect(() => {
        if(!data) {
            getCustomerInfo();
        }
    }, [data])

    const renderDefaultAddress = (item, default_billing, default_shipping) => {
        const defaultBilling = item.find(value => {
            return value.id === parseInt(default_billing, 10);
        });

        const defaultShipping = item.find(value => {
            return value.id === parseInt(default_shipping, 10);
        })
        
        return (
            <div className="address-book__container">
                <div className="dash-column-box">
                    <div className="box-title">
                        {Identify.__("Default Billing Address")}
                    </div>
                    {defaultBilling ? (
                        <React.Fragment>
                             <AddressItem
                                addressData={defaultBilling}
                            />
                            <Link 
                                className="edit-item" 
                                to={{pathname: '/addresses.html', 
                                state: { addressEditing: defaultBilling}}}>   
                                <div className="edit-information">
                                    <Pencil style={{width: '16px', height: '16px'}}/>
                                <div>{Identify.__("Edit".toUpperCase())}</div>
                            </div>
                            </Link>
                        </React.Fragment>
                    ) : <div>{Identify.__('You have not set a default billing address.  ')}</div>}
    
                </div>
                <div className="dash-column-box">
                    <div className="box-title">
                        {Identify.__("Default Shipping Address")}
                    </div>
                    {defaultShipping ? (
                        <React.Fragment>
                            <AddressItem
                                addressData={defaultShipping}
                            />
                            <Link className="edit-item" to={{pathname: '/addresses.html', state: { addressEditing: defaultShipping}}}>
                                <div className="edit-information">
                                    <Pencil style={{width: '16px', height: '16px'}}/>
                                    <div>{Identify.__("Edit".toUpperCase())}</div>
                                </div>
                            </Link>
                        </React.Fragment>
                    ) : <div>{Identify.__('You have not set a default shipping address.')}</div>}
                    
                </div>
            </div>
        );
    }

    if(!data) {
        return <Loading />
    }

    return (
        <div className='my-dashboard'>
            {TitleHelper.renderMetaHeader({
                title: Identify.__('Dashboard'),
                desc: Identify.__('Dashboard') 
            })}
            {(data.customerOrders && data.customerOrders.items) &&
                (
                    !isPhone ? (
                        <div className="dashboard-recent-orders">
                            <div className="section-top">
                                <div className="section-title">{Identify.__("Recent Orders")}</div>
                                <Link className="view-all" to='/orderhistory.html'>{Identify.__("View all")}</Link>
                            </div>
                            <OrderHistory data={data.customerOrders.items} showForDashboard={true} />
                        </div>
                        ) : (
                        <Link to="/orderhistory.html">
                            <Whitebtn
                                text={Identify.__("View recent orders")}
                                className="view-recent-orders"
                            />
                        </Link>
                    )
                )
            }
            <div className='dashboard-acc-information'>
                <div className="section-top">
                    <div className='section-title'>
                        {Identify.__("Account Information")}
                    </div>
                </div>
                <div className="acc-information" >
                    <div className="dash-column-box">
                        <div className="white-box-content">
                            <div className="box-title">
                                {Identify.__("Contact information")}
                            </div>
                            <p className="desc email">{customer.email}</p>
                                <div  className="edit-link">
                                    <Link to={{ pathname: '/profile.html', state: {profile_edit: 'password'} }}>
                                    {Identify.__("Change password")}
                                    </Link>
                                </div>
                        </div>
                        <Link to="/profile.html">
                            <div className="edit-information">
                                <Pencil style={{width: '16px', height: '16px'}}/>
                                <div>{Identify.__("Edit".toUpperCase())}</div>
                            </div>
                        </Link>
                        
                    </div>
                    <div className="dash-column-box">
                        {data.hasOwnProperty('customer') && data.customer.hasOwnProperty('is_subscribed') ? (
                            <div className="white-box-content">
                                <div className="box-title">
                                    {Identify.__("Newsletter")}
                                </div>
                                <p className="desc">
                                    {data.customer.is_subscribed === true
                                        ? Identify.__(
                                            "You are subscribed to our newsletter"
                                        )
                                        : Identify.__(
                                            "You are not subscribed to our newsletter"
                                        )}
                                </p>
                            </div>
                        ) : <Loading /> }
                        <Link to="/newsletter.html">
                            <div className="edit-information">
                                <Pencil style={{width: '16px', height: '16px'}}/>
                                <div>{Identify.__("Edit".toUpperCase())}</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
            {data.customer && data.customer.addresses && (
                <div className="dashboard-address-book">
                    <div className="section-top">
                        <div className="section-title">
                            {Identify.__("Address Book")}
                        </div>
                        <Link className="view-all" to="/addresses.html">{Identify.__("Manage addresses".toUpperCase())}</Link>
                    </div>
                    {renderDefaultAddress(data.customer.addresses, data.customer.default_billing, data.customer.default_shipping)}
                </div>
            )} 
        </div>
    )
    
}

export default Dashboard;