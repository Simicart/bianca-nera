import { connect } from 'src/drivers';
import { simiSignOut } from 'src/simi/Redux/actions/simiactions';
// import Loading from 'src/simi/BaseComponents/Loading';
import { smoothScrollToView } from 'src/simi/Helper/Behavior';
import { logout as signOutApi } from 'src/simi/Model/Customer';
import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';
import { showToastMessage } from 'src/simi/Helper/Message';
import Identify from 'src/simi/Helper/Identify';

var loggingOut = false

const $ = window.$

const Logout = props => {
    const { simiSignOut, history, isSignedIn } = props

    const signOutCallback = (data) => {
        if (data.errors) {
            let errorMsg = '';
            if (data.errors.length) {
                data.errors.map((error) => {
                    errorMsg += error.message;
                });
                showToastMessage(Identify.__(errorMsg));
            }
        } else {
            if (!loggingOut) {
                loggingOut = true;
                // logout from pwa
                simiSignOut({ history })
                smoothScrollToView($("#root"));
                history.push('/')
            } else {
                console.log('Already logging out')
                history.push('/')
            }
        }
    };

    signOutApi(signOutCallback, {});
    showFogLoading();

    return ''
}

const mapStateToProps = ({ user }) => {
    const { isSignedIn } = user
    return {
        isSignedIn
    };
}


export default connect(mapStateToProps, { simiSignOut })(Logout);