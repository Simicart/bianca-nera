import React, { useEffect } from 'react'
import ProductDetail from './ProductDetail';
import { simiUseQuery } from 'src/simi/Network/Query' 
import getCategory from 'src/simi/queries/catalog/getCateProductsNoFilter.graphql'

const ProductList = props => {
    const {classes, homeData, history} = props;
    const renderListProduct = () => {
        if(
            homeData.home.hasOwnProperty('homeproductlists')   
            && homeData.home.homeproductlists.hasOwnProperty('homeproductlists')
            && homeData.home.homeproductlists.homeproductlists instanceof Array
            && homeData.home.homeproductlists.homeproductlists.length > 0
        ) {
            
            const productList = homeData.home.homeproductlists.homeproductlists.map((item, index) => {
                return (
                    <div className={classes["default-productlist-item"]} key={index}>
                        <div className={classes["default-productlist-title"]}>
                            {item.list_title}
                        </div>
                        <ProductDetail dataProduct={item} classes={classes} history={history}/>
                    </div>
                )
            });
            return (
                <div className={classes["productlist-content"]}>
                    {productList}
                </div>
            )
        }
    }

    return (
        <div className={classes['default-home-product-list']}>
            <div className="container">
                {renderListProduct()}
            </div>
        </div>
    );
}

export default ProductList;