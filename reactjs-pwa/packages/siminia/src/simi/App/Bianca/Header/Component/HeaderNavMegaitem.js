import React from 'react';
import { Link } from 'src/drivers';
import { cateUrlSuffix } from 'src/simi/Helper/Url';
import Identify from 'src/simi/Helper/Identify';

const NavMegaitem = props => {
    const storeConfig = Identify.getStoreConfig();
    const baseUrl = storeConfig.storeConfig.base_url

    if (props.itemAndChild) {
        const { classes, parentId } = props;
        const rootItem = props.itemAndChild;
        // const childCol = props.childCol || 2;
        if (rootItem.children) {
            const childCats = rootItem.children.map((item, index) => {
                if (!item.name) return '';
                if (item.include_in_menu !== 1) return '';
                let subChildLevel2 = [];
                if (item.children) {
                    subChildLevel2 = item.children
                        .slice(0, 5)
                        .map((itemlv2, indexlv2) => {
                            if (!itemlv2.name) return '';
                            const path = itemlv2.url_path
                                ? '/' + itemlv2.url_path + cateUrlSuffix()
                                : itemlv2.link;
                            const location = {
                                pathname: path,
                                state: {}
                            };
                            if (itemlv2.children)
                                location.state.category_page_id =
                                    itemlv2.entity_id;
                            return (
                                <Link
                                    className={classes['mega-lv2-name']}
                                    key={indexlv2}
                                    to={location}
                                >
                                    {itemlv2.name}
                                </Link>
                            );
                        });
                }
                const location = {
                    pathname: item.url_path
                        ? '/' + item.url_path + cateUrlSuffix()
                        : item.link,
                    state: {}
                };

                return (
                    <div key={index} className="mega-lv1-list">
                        <Link
                            className={classes['mega-lv1-name']}
                            to={location}
                        >
                            {item.name}
                        </Link>
                        {subChildLevel2.length > 0 &&
                            <div className={classes['mega-lv1-sub-cats']}>
                                {subChildLevel2}
                                {subChildLevel2.length >= 5 && (
                                    <Link
                                        to={location}
                                        style={{
                                            color: '#727272',
                                            textDecoration: 'underline'
                                        }}
                                    >
                                        {Identify.__('See more')}
                                    </Link>
                                )}
                            </div>
                        }
                    </div>
                );
            });
            // const childCatGroups = [];
            // const chunkSize = Math.ceil(childCats.length / childCol);
            // for (var i = 0; i < childCats.length; i += chunkSize) {
            //     childCatGroups.push(
            //         <div className="mega-lv2-list-cats" key={i}>
            //             {childCats.slice(i, i + chunkSize)}
            //         </div>
            //     );
            // }

            const className = `${classes['nav-mega-item']} ${parentId ? 'sub-item-nav-item-container-'+parentId:''}`

            return (
                <div className={className} id={props.id}>
                    <div
                        role="presentation"
                        className={classes['mega-content']}
                        onClick={() => {
                            if (props.toggleMegaItemContainer)
                                props.toggleMegaItemContainer();
                        }}
                    >
                        {childCats}
                    </div>
                    {
                        <div className={`${classes['mega-image']} hidden-xs`}>
                            <img
                                src={
                                    rootItem.image
                                        ? `/pub/media/catalog/category/${rootItem.image}`
                                        : 'https://www.simicart.com/media/simicart/mockup-ps-simipwa.png'
                                }
                                alt={rootItem.name}
                            />
                        </div>
                    }
                </div>
            );
        }
    }
    return '';
};
export default NavMegaitem;
