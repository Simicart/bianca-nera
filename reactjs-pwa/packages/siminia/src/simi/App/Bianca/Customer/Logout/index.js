import { useEffect } from 'react';
import { connect } from 'src/drivers';
import { simiSignOut } from 'src/simi/Redux/actions/simiactions';
// import Loading from 'src/simi/BaseComponents/Loading';
// import { smoothScrollToView } from 'src/simi/Helper/Behavior';
// import { logout as signOutApi } from 'src/simi/Model/Customer';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';
// import { showToastMessage } from 'src/simi/Helper/Message';
// import Identify from 'src/simi/Helper/Identify';

const Logout = props => {
    const { simiSignOut, history, isSignedIn } = props

    useEffect(() => {
        showFogLoading();
        simiSignOut({ history })
    }, []);

    return ''
}

const mapStateToProps = ({ user }) => {
    const { isSignedIn } = user
    return {
        isSignedIn
    };
}

export default connect(mapStateToProps, { simiSignOut })(Logout);