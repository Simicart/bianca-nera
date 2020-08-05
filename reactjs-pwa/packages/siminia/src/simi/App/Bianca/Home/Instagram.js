import React, {useEffect, useState} from 'react'
import Identify from "src/simi/Helper/Identify";
// import Scroller from "./Scroller";
import OwlCarousel from 'react-owl-carousel2';
// import {sendRequest} from 'src/simi/Network/RestMagento';
const Instagram = (props) => {
    const {history, isPhone} = props;
    const instagramStored = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, 'instagram');
    const [insData, setInsData] = useState(instagramStored);
    const limit = 5;

    const getUserInstagram = async () => {
        let response = await fetch(`/rest/V1/simiconnector/proxy/instagram/?limit=${limit}`);
        // let response = await fetch(`https://www.instagram.com/${name}/?__a=1`);
        // let response = {};
        if (response.ok) { // if HTTP-status is 200-299
            // get the response body (the method explained below)
            let resData = {}
            try{
                resData = await response.json();
            }catch(e){}
            if (Array.isArray(resData) && resData[0]){
                resData = resData[0];
            }
            return resData;
        } else if (response.status) {
            console.warn("HTTP-Error: " + response.status);
        }
        return response;
    }

    useEffect(() => {
        const instagramStored = Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, 'instagram');
        if (!instagramStored) {
            getUserInstagram().then(resData => {
                if (resData && resData.data) {
                    Identify.storeDataToStoreage(Identify.SESSION_STOREAGE, 'instagram', resData);
                }
                setInsData(resData);
            });
        }
    }, []);

    const actionViewAll = () => {
        const {data} = props;
        return `https://www.instagram.com/${data}`;
    }

    const nodeItem = (ins) => {
        const { node } = ins;
        return node;
    }

    const renderInsItem = (item, index) => {
        return (
            <div className="item" key={index}>
                <a href={`${item.permalink}`} target="_blank" rel="noopener noreferrer">
                    <img className="img-responsive" src={item.media_url} alt={item.caption || `Instagram image ${index + 1}`} />
                </a>
            </div>
        );
    }

    /**
     * v1
     */
    /* const renderInsView = () => {
        let html = null;
        if (insData && ((insData.graphql && insData.graphql.user) || insData.data && insData.data.user)) {
            const user = insData.data && insData.data.user || insData.graphql && insData.graphql.user || null;
            if (user && user.edge_owner_to_timeline_media) {
                const { edges } = user.edge_owner_to_timeline_media;
                if (edges.length) {
                    let instagramData = [];
                    instagramData = edges.map((ins, index) => {
                        // const limit = isPhone ? 3 : 8;
                        // const limit = 18;
                        if (index < limit) {
                            return renderInsItem(nodeItem(ins), index);
                        }
                        return null;
                    });
                    html = instagramData;
                }
            }
        }
        return html;
    } */

    /**
     * v2
     */
    const renderInsView = () => {
        let html = [];
        if (insData && insData.data && insData.data instanceof Array) {
            insData.data.every((data, index) => {
                if (index >= limit) return false; // break
                if (data.media_type === 'IMAGE') {
                    html.push(renderInsItem(data, index));
                }
                return true; // continue
            });
        }
        return html;
    }

    const items = renderInsView();

    const left = '<svg class="chevron-left" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="display: inline-block; color: rgba(255, 255, 255, 0.87); fill: rgb(0, 0, 0); height: 24px; width: 24px; user-select: none; transition: all 450ms cubic-bezier(0.23, 1, 0.32, 1) 0ms;"><path d="M14 20c0.128 0 0.256-0.049 0.354-0.146 0.195-0.195 0.195-0.512 0-0.707l-8.646-8.646 8.646-8.646c0.195-0.195 0.195-0.512 0-0.707s-0.512-0.195-0.707 0l-9 9c-0.195 0.195-0.195 0.512 0 0.707l9 9c0.098 0.098 0.226 0.146 0.354 0.146z"></path></svg>';
    const right = '<svg class="chevron-right" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="display: inline-block; color: rgba(255, 255, 255, 0.87); fill: rgb(0, 0, 0); height: 24px; width: 24px; user-select: none; transition: all 450ms cubic-bezier(0.23, 1, 0.32, 1) 0ms;"><path d="M5 20c-0.128 0-0.256-0.049-0.354-0.146-0.195-0.195-0.195-0.512 0-0.707l8.646-8.646-8.646-8.646c-0.195-0.195-0.195-0.512 0-0.707s0.512-0.195 0.707 0l9 9c0.195 0.195 0.195 0.512 0 0.707l-9 9c-0.098 0.098-0.226 0.146-0.354 0.146z"></path></svg>';

    const options = {
        // stagePadding: isPhone ? 35 : 41.5,
        autoWidth: isPhone,
        mergeFit: true,
        margin: isPhone ? 15 : 16,
        nav: true,
        autoplay: false,
        navText: Identify.isRtl() ? [right, left] : [left, right],
        responsive:{
            0:{
                items:1
            },
            375:{
                items:2
            },
            1024:{
                items:3
            },
            1366:{
                items:4
            },
            1920:{
                items:5
            }
        },
        startPosition: 0,
        rtl: Identify.isRtl()
    };

    return (
        <div className={`instagram-slider ${isPhone ? 'phone-view':''}`}>
            <div className="title-box">
                <h3 className="title">{Identify.__('Shop Our Instagram')}</h3>
            </div>
            <div className="container instagram-container">
                <div className="carousel-block">
                    { items && items.length && 
                        <OwlCarousel options={options}>
                            {items}
                        </OwlCarousel>
                    }
                </div>
            </div>
            
            <div className="view-all">
                <a href={actionViewAll()} target="_blank" alt="view all">
                    <div className="btn" onClick={actionViewAll}><span>{Identify.__('View all')}</span></div>
                </a>
            </div>
        </div>
    );
}
export default Instagram