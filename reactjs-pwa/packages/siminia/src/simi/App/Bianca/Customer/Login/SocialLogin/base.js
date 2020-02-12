import firebase from "firebase";
import Identify from 'src/simi/Helper/Identify';

const storeConfig = Identify.getStoreConfig();
var firebaseConfigString = null;
if (
    storeConfig &&
    storeConfig.simiStoreConfig &&
    storeConfig.simiStoreConfig.config &&
    storeConfig.simiStoreConfig.config.social_login_config &&
    storeConfig.simiStoreConfig.config.social_login_config.firebase_config
) {
    firebaseConfigString = storeConfig.simiStoreConfig.config.social_login_config.firebase_config
}
var firebaseConfig = JSON.parse(firebaseConfigString)
const firebaseApp = firebase.initializeApp(firebaseConfig)

export default firebaseApp