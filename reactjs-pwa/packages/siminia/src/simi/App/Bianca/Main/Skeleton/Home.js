import React, {useEffect, useState} from 'react';
import Skeleton from 'react-loading-skeleton';
import Identify from 'src/simi/Helper/Identify';
import { getOS } from 'src/simi/App/Bianca/Helper';
require('./Home.scss');
const Homeskeleton = () => {

    const [isPhone, setIsPhone] = useState(window.innerWidth < 1024);
    
    useEffect(() => {
        window.onresize = function () {
            const width = window.innerWidth;
            const newIsPhone = width < 1024;
            if(isPhone !== newIsPhone){
                setIsPhone(newIsPhone);
            }
        }
    },[]);

    return (
        <div className={`home-wrapper ${getOS()}`}>
            <div className={`banner-wrap ${isPhone ? 'mobile':''}`}>
                <div className={`banner-homepage ${Identify.isRtl() ? 'banner-home-rtl' : ''}`} style={{direction: 'ltr'}}>
                    <div className={`container home-container`}>
                        <Skeleton height={438}/>
                    </div>
                </div>
            </div>
            <div className={`section-homepage ${isPhone ? 'mobile':''}`}>
                <div className={`container home-container`}>
                    <div className="homesection">
                        <div className="left-sec">
                            <div className="image-1">
                                <Skeleton height={360}/>
                            </div>
                        </div>
                        <div className="right-sec">
                            {isPhone ?
                                <Skeleton height={339}/>
                                :
                                <>
                                    <Skeleton height={360} width={176}/>
                                    <Skeleton height={360} width={176}/>
                                    <Skeleton height={360} width={176}/>
                                </>
                            }
                        </div>
                    </div>
                    <div className="homesection">
                        <div className="left-sec">
                            <div className="image-1">
                                <Skeleton height={360}/>
                            </div>
                            <div className="image-2">
                                <Skeleton height={360}/>
                            </div>
                        </div>
                        <div className="right-sec">
                            {isPhone ?
                                <Skeleton height={339}/>
                                :
                                <>
                                    <Skeleton height={360} width={176}/>
                                    <Skeleton height={360} width={176}/>
                                    <Skeleton height={360} width={176}/>
                                </>
                            }
                        </div>
                    </div>
                    <div className="homesection">
                        <div className="left-sec no-products">
                            <div className="image-1">
                                <Skeleton height={360}/>
                            </div>
                            <div className="image-2">
                                <Skeleton height={360}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Homeskeleton;