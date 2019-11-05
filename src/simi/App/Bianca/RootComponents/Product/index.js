import React from 'react';
import Loading from 'src/simi/BaseComponents/Loading'
import Identify from 'src/simi/Helper/Identify'
import ProductFullDetail from 'src/simi/App/Bianca/Components/Product/ProductFullDetail/productFullDetail';
import getUrlKey from 'src/util/getUrlKey';
import connectorGetProductDetailByUrl from 'src/simi/queries/catalog/getProductDetail.graphql';
import connectorGetProductDetailBySku from 'src/simi/queries/catalog/getProductDetailBySku.graphql'
import { Simiquery } from 'src/simi/Network/Query' 
import {smoothScrollToView} from 'src/simi/Helper/Behavior'
import { saveDataToUrl, productUrlSuffix } from 'src/simi/Helper/Url';

import { LazyComponent } from 'src/simi/BaseComponents/LazyComponent'
const Page404 = (props) => {
    return <LazyComponent component={() => import(/* webpackChunkName: "NoMatch"*/'src/simi/App/core/NoMatch/Page404')} {...props}/>
}

const Product = props => {
    const {preloadedData} = props
    if (preloadedData && !preloadedData.is_dummy_data) { //saved api is full api, then no need api getting anymore
        return (
            <ProductFullDetail
                product={preloadedData}
            />
        )
    }

    smoothScrollToView($('#root'))

    const sku = Identify.findGetParameter('sku') //cases with url like: product.html?sku=ab12
    const productQuery = sku?connectorGetProductDetailBySku:connectorGetProductDetailByUrl
    // console.log(productQuery);
    // console.log(getUrlKey());
    const variables = {onServer: false}
    if (sku)
        variables.sku = sku
    else
        variables.urlKey = getUrlKey()
        
    return (
        <Simiquery
            query={productQuery}
            variables={variables}
            fetchPolicy="no-cache" //cannot save to cache cause of "heuristic fragment matching" from ConfigurableProduct and GroupedProduct
        >
            {({ error, data }) => {
                console.log(data)
                if (error) return <div>{Identify.__('Data Fetch Error')}</div>;
                let product = null
                
                if (data && data.productDetail && data.productDetail.items && !data.productDetail.items.length) {
                    return <Page404 />
                }
                if (data && data.productDetail && data.productDetail.items && data.productDetail.items.length) {
                    //prepare data
                    product = data.productDetail.items[0];
                    let simiExtraField = data.simiProductDetailExtraField
                    simiExtraField = simiExtraField?JSON.parse(simiExtraField):null
                    product.simiExtraField = simiExtraField
                    //save full data to quote
                    if (product.url_key)
                        saveDataToUrl(`/${product.url_key}${productUrlSuffix()}`, product, false)
                } else if (preloadedData) {
                    product = preloadedData
                }
                if (product)
                    return (
                        <ProductFullDetail
                            product={product}
                        />
                    );
                return <Loading />
            }}
        </Simiquery>
    );
}

export default Product;
