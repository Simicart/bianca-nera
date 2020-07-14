import Identify from './Identify'
import * as Constants from 'src/simi/Config/Constants';

export const addRequestVars = (variables) => {
    //no need to keep session while calling directly
    // if (window.SMCONFIGS && window.SMCONFIGS.directly_request && window.SMCONFIGS.merchant_url)
    //     return variables
    variables = variables ? variables : {}

    if (window.SMCONFIGS && !window.SMCONFIGS.directly_request){
        const simiSessId = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID)
        if (simiSessId && !variables.hasOwnProperty(simiSessId))
            variables.simiSessId = simiSessId
    }

    const storeConfig = Identify.getStoreConfig();
    const {currency, store_id} = storeConfig && storeConfig.simiStoreConfig || {};
    if (currency) variables.simiCurrency = currency;
    if (store_id) variables.simiStoreId = store_id;
    
    return variables
}

//use to modify resourceUrl in order to call directly to merchant magento site instead of using upward
export const addMerchantUrl = (resouceUrl) => {
    if (
        !resouceUrl.includes('http://') && !resouceUrl.includes('https://') &&
        window.SMCONFIGS && window.SMCONFIGS.directly_request && window.SMCONFIGS.merchant_url
    ) {
        return (window.SMCONFIGS.merchant_url + resouceUrl.replace(/^\//, ''))
    }
    return resouceUrl
}