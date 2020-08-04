import React, {useEffect, useState} from 'react'
import Identify from "src/simi/Helper/Identify";
import {sendRequest} from 'src/simi/Network/RestMagento';
import Loading from "src/simi/BaseComponents/Loading";
import {showToastMessage} from 'src/simi/Helper/Message';

const InstagramAuth = (props) => {
    const {history, isPhone} = props;
    const [tokenGot, setTokenGot] = useState(null);

    const getAuth = async () => {
        sendRequest('/rest/V1/simiconnector/instagram/auth', (url) => {
            if (url && !url.errors) {
                window.location.href = url;
            }
        }, 'GET', {}, {});
        return true;
    }

    useEffect(() => {
        if (history.location.search) {
            let params = history.location.search.substr(1);
            let pObject = {}
            params = params.split('&'); // to array
            params.map((p)=>{
                const pvar = p.split('=');
                pObject[pvar[0]] = pvar[1] || '';
                return true;
            });
            if (pObject.code) {
                sendRequest('/rest/V1/simiconnector/instagram/auth', (res) => {
                    if (res) {
                        setTokenGot(true);
                        showToastMessage(Identify.__('Instagram authorize success!'));
                        setTimeout(()=>{
                            window.location.href = '/';
                        }, 1600);
                    }
                    setTokenGot(false);
                }, 'POST', {}, {
                    'code': pObject.code
                });
            }
        } else {
            getAuth();
        }
    }, []);
    
    if (tokenGot === null) {
        return <Loading />
    }

    return (
        <div style={{textAlign: 'center', padding: '10px'}}>
        {tokenGot &&
            Identify.__('Instagram authorize success!')
        }
        {tokenGot === false &&
            Identify.__('Instagram authorize failed!')
        }
        </div>
    );
}
export default InstagramAuth