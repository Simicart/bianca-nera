import React, { useEffect, useMemo, useState } from 'react'
import Identify from "src/simi/Helper/Identify";
import {cateUrlSuffix} from 'src/simi/Helper/Url';
import Scroller from "./Scroller";
import {smoothScrollToView} from 'src/simi/Helper/Behavior';
import { sendRequest } from 'src/simi/Network/RestMagento';

const $ = window.$;

const Designers = props => {
    const { history, isPhone} = props;
    const [ data, setData ] = useState();

    const limitItem = 18;

    const slideSettings = {
        chevronWidth: isPhone ? 16 : 54,
        showChevron: true,
        numberOfCards: isPhone ? 3 : 6,
        slidesToScroll: 3,
        gutter: isPhone ? 12.5 : 16
    }

    // Get vendor list data
    useEffect(()=>{
        sendRequest(`/rest/V1/simiconnector/vendors`, (data)=>{
            setData(data);
        }, 'GET', {limit: limitItem, home: 1});
    }, []);

    let vendors = useMemo(()=>{
        let _data = [];
        data && data.forEach((item, index)=>{
            if (index < limitItem && item.logo) {
                item.url = `/designers/${item.vendor_id}.html`;
                item.image = item.logo;
                item.alt = item.logo && item.logo.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');
                _data.push(item);
            }
        });
        return _data;
    }, [data]);

    const onClickItem = () => {
        smoothScrollToView($('#siminia-main-page'), 350);
    }

    const actionViewAll = () => {
        smoothScrollToView($('#siminia-main-page'), 350);
        setTimeout(()=>{
            history.push('/designers' + cateUrlSuffix());
        }, 350);
    }

    const lastItems = [(
        <div className="last-items" key={Identify.randomString(3)}>
            <div className="btn" onClick={actionViewAll}><span>{Identify.__('View all')}</span></div>
        </div>
    )];

    let startItemIndex = 0;
    if (Identify.isRtl()) {
        vendors.reverse();
        startItemIndex = (vendors.length - 1)
    }

    return (
        <div className={`shop-by-designers-wrap ${isPhone ? 'mobile' : ''}`}>
            <div className={`brand-slider ${isPhone ? 'phone-view':''}`}>
                { data && 
                    <div className="title-box">
                        <h3 className="title">{Identify.__('Shop By Designers')}</h3>
                    </div>
                }
                <Scroller data={vendors} 
                    initItemIndex={startItemIndex} 
                    lastItems={lastItems} 
                    slideSettings={slideSettings} 
                    isPhone={isPhone} history={history} 
                    onClickItem={onClickItem}
                />
            </div>
        </div>
    );
}

export default Designers;