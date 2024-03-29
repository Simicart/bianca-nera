import React, { Component } from 'react'
import { connect } from 'src/drivers';
import { compose } from 'redux';
import PageTitle from 'src/simi/App/Bianca/Customer/Account/Components/PageTitle';
import ProfileForm from './ProfileForm';
import { toggleMessages } from 'src/simi/Redux/actions/simiactions';
import Identify from 'src/simi/Helper/Identify';

import {
    getUserDetails,
} from 'src/actions/user';

class Profile extends Component {
    render() {
        return (
            <div className='account-information-area'>
                <PageTitle title={Identify.__('edit account information')}/>
                <ProfileForm {...this.props}/>
            </div>
        )
    }
}

const mapDispatchToProps = {
    toggleMessages,
    getUserDetails,
};

export default compose(
    connect(
        null,
        mapDispatchToProps
    )
)(Profile);

// export default Profile;