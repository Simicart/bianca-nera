import { addRequestVars, addMerchantUrl } from 'src/simi/Helper/Network'
import Identify from 'src/simi/Helper/Identify'
import { Util, RestApi } from '@magento/peregrine';
// import M2ApiRequest from '@magento/peregrine/RestApi/Magento2/M2ApiRequest';
import CacheHelper from 'src/simi/Helper/CacheHelper';

const { BrowserPersistence } = Util;
const peregrinRequest = RestApi.Magento2.request;
const M2ApiRequest = RestApi.Magento2.default;

const prepareData = (endPoint, getData, method, header, bodyData) => {
    let requestMethod = method
    let requestEndPoint = addMerchantUrl(endPoint)
    const requestHeader = header
    const requestBody = bodyData

    //add session/store/currencies
    getData = addRequestVars(getData)

    //incase no support PUT & DELETE
    try {
        const merchantConfigs = Identify.getStoreConfig();
        if (method.toUpperCase() === 'PUT' 
            && merchantConfigs.simiStoreConfig.config.base.is_support_put !== undefined
            && parseInt(merchantConfigs.simiStoreConfig.config.base.is_support_put, 10) === 0) {
            requestMethod = 'POST';
            getData.is_put = '1'
        }

        if (method.toUpperCase() === 'DELETE' && 
            merchantConfigs.simiStoreConfig.config.base.is_support_delete !== undefined
            && parseInt(merchantConfigs.simiStoreConfig.config.base.is_support_delete, 10) === 0) {
            requestMethod = 'POST';
            getData.is_delete = '1'
        }
        
    } catch (err) {}
    let dataGetString = Object.keys(getData).map(function (key) {
        return encodeURIComponent(key) + '=' +
            encodeURIComponent(getData[key]);
    })
    dataGetString = dataGetString.join('&')
    if (dataGetString) {
        if(requestEndPoint.includes('?')){
            requestEndPoint += "&" + dataGetString;
        } else { 
            requestEndPoint += "?" + dataGetString;
        }
    }

    //header
    const storage = new BrowserPersistence()
    const token = storage.getItem('signin_token')
    if (token)
        requestHeader['authorization'] = `Bearer ${token}`
    requestHeader['accept'] = 'application/json'
    requestHeader['content-type'] = 'application/json'

    return {requestMethod, requestEndPoint, requestHeader, requestBody}
}

/**
 * 
 * @param {string} endPoint 
 * @param {function} callBack 
 * @param {string} method 
 * @param {Object} getData 
 * @param {Object} bodyData 
 */

export async function sendRequest(endPoint, callBack, method='GET', getData= {}, bodyData= {}) {
    const header = {cache: 'default', mode: 'cors'}
    const {requestMethod, requestEndPoint, requestHeader, requestBody} = prepareData(endPoint, getData, method, header, bodyData)
    const requestData = {}
    requestData['method'] = requestMethod
    requestData['headers'] = requestHeader
    requestData['body'] = (requestBody && requestMethod !== 'GET')?JSON.stringify(requestBody):null
    requestData['credentials'] = 'same-origin';
    
    const _request = new Request(requestEndPoint, requestData);
    let result = null

    fetch(_request)
        .then(function (response) {
            if (response.ok) {
                return response.json();
            }
            /** Allow response for status 401 Unauthorized */
            /** Allow response for error */
            if (response.ok === false) {
                let data = {};
                data.errors = [];
                data.status = response.status;
                data.statusText = response.statusText;
                try{
                    return response.text().then(function(text) {
                        data.errors = [JSON.parse(text)];
                        return data;
                    });
                } catch (err){}
            }
        })
        .then(function (data) {
            if (data && data.status === 401 && data.statusText === "Unauthorized") {
                callBack(result);
                CacheHelper.clearCaches();
                // window.location.reload();
                window.location.href = '/login.html';
                setTimeout(()=>{
                    window.location.href = '/login.html';
                }, 1000);
            }
            if (data) {
                if (Array.isArray(data) && data.length === 1 && data[0])
                    result = data[0]
                else
                    result = data
                if (result && typeof result === 'object')
                    result.endPoint = endPoint
            } else
                result =  {'errors' : [{'code' : 0, 'message' : Identify.__('Network response was not ok'), 'endpoint': endPoint}]}
            callBack(result)
        }).catch((error) => {
            console.warn(error);
        });
}


export const request = (resourceUrl, opts) => {
    let newResourceUrl = addMerchantUrl(resourceUrl)
    if (opts && !(opts.method && opts.method!=='GET')) { //only for get method
        const getData = addRequestVars({})
        let dataGetString = Object.keys(getData).map(function (key) {
            return encodeURIComponent(key) + '=' +
                encodeURIComponent(getData[key]);
        })
        dataGetString = dataGetString.join('&')
        if (dataGetString)
            newResourceUrl += "?" + dataGetString;
    }

    // Modify request network to apply auto logout after session timeout
    try{
        const req = new M2ApiRequest(newResourceUrl, {...opts, multicast: false});
        // Replace run M2ApiRequest
        return window.fetch(req.resourceUrl, req.opts).then(response => {
            // WHATWG fetch will only reject in the unlikely event
            // of an error prior to opening the HTTP request.
            // It pays no attention to HTTP status codes.
            // But the response object does have an `ok` boolean
            // corresponding to status codes in the 2xx range.
            // An M2ApiRequest will reject, passing server errors
            // to the client, in the event of an HTTP error code.
            if (!response.ok) {
                let status = response.status;
                try{
                    if (status === 401 && response.statusText === "Unauthorized") {
                        CacheHelper.clearCaches()
                        window.location.reload()
                    }
                } catch (error){}
                return (
                    response
                        // The response may or may not be JSON.
                        // Let M2ApiResponseError handle it.
                        .text()
                        // Throw a specially formatted error which
                        // includes the original context of the request,
                        // and formats the server response.
                        .then(bodyText => {
                            let data = '';
                            try{
                                data = JSON.parse(bodyText);
                            }catch(error){}
                            if (data && data.message && data.message.includes(`The consumer isn't authorized`)) {
                                CacheHelper.clearCaches();
                                // window.location.reload();
                                setTimeout(()=>{
                                    window.location.href = '/login.html';
                                }, 1000);
                            }
                            return data;
                        })
                );
            }
            return response;
        }).then(res => {
            try {
                let response = res.json();
                return response;
            }catch(e){
                if (res && res.message) {
                    throw new Error(res.message);
                }
            }
        });
        
    }catch(error){
        console.warn(error)
        throw new Error(error)
    }
    // return peregrinRequest(newResourceUrl, opts)
}