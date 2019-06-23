import React, { useEffect } from 'react';
import LoadingSpiner from 'src/simi/BaseComponents/Loading/LoadingSpiner'
import { string, number, shape } from 'prop-types';
import { usePagination } from '@magento/peregrine';
import { simiUseQuery } from 'src/simi/Network/Query';
import { mergeClasses } from 'src/classify';
import categoryQuery from 'src/simi/queries/getCategory.graphql';
import simicntrCategoryQuery from 'src/simi/queries/simiconnector/getCategory.graphql'
import CategoryContent from './categoryContent';
import defaultClasses from './category.css';
import { resourceUrl } from 'src/drivers'
import CategoryHeader from './categoryHeader'
import Identify from 'src/simi/Helper/Identify';
import ObjectHelper from 'src/simi/Helper/ObjectHelper';
import { withRouter } from 'src/drivers';


var sortByData = null
var filterData = null

const Category = props => {
    const { id } = props;
    const pageSize = window.innerWidth < 1024?12:24 
    const [paginationValues, paginationApi] = usePagination();
    const { currentPage, totalPages } = paginationValues;
    const { setCurrentPage, setTotalPages } = paginationApi;

    const pageControl = {
        currentPage,
        setPage: setCurrentPage,
        updateTotalPages: setTotalPages,
        totalPages
    };

    const productListOrder = Identify.findGetParameter('product_list_order')
    const productListDir = Identify.findGetParameter('product_list_dir')
    
    const newSortByData = productListOrder?productListDir?{[productListOrder]: productListDir.toUpperCase()}:{[productListOrder]: 'ASC'}:null
    if (newSortByData && (!sortByData || !ObjectHelper.shallowEqual(sortByData, newSortByData))) {
        sortByData = newSortByData
    }

    const productListFilter = Identify.findGetParameter('filter')
    if (productListFilter) {
        if (JSON.parse(productListFilter)){
            filterData = productListFilter
        }
    }

    const [queryResult, queryApi] = simiUseQuery(Identify.hasConnector()?simicntrCategoryQuery:categoryQuery);
    const { data, error, loading } = queryResult;
    const { runQuery, setLoading } = queryApi;
    const classes = mergeClasses(defaultClasses, props.classes);

    useEffect(() => {
        const variables = {
            id: Number(id),
            pageSize: Number(pageSize),
            currentPage: currentPage?Number(currentPage):1,
            stringId: String(id),
            simiFilter: filterData
        }
        if (sortByData)
            variables.sort = sortByData
        
        setLoading(true);
        runQuery({
            variables: variables
        });
        window.scrollTo({
            left: 0,
            top: 0,
            behavior: 'smooth'
        });
    }, [id, pageSize, currentPage, sortByData, filterData]);
    if (data && data.simiproducts) {
        data.products = data.simiproducts
        if (data.products.simi_filters)
            data.products.filters = data.products.simi_filters
    }
    const totalPagesFromData = data
        ? data.products.page_info.total_pages
        : null;
    useEffect(() => {
        setTotalPages(totalPagesFromData);
    }, [totalPagesFromData]);

    if (error) return <div>Data Fetch Error</div>;
    // show loading indicator until our data has been fetched and pagination state has been updated
    //if (!totalPages) return <LoadingSpiner />;
    if (!data || !data.category) return <LoadingSpiner />;

    // if our data is still loading, we want to reset our data state to null
    return (
        <div className="container">
            {
            (data.category && data.category.name && data.category.image) &&
            <CategoryHeader
                classes={classes}
                name={data.category.name}
                image_url={resourceUrl(data.category.image, { type: 'image-category' })}
            />
            }
            <CategoryContent
                history={props.history}
                classes={classes}
                pageControl={pageControl}
                data={loading ? null : data}
                sortByData={sortByData}
                filterData={filterData?JSON.parse(productListFilter):null}
            />
        </div>
    );
};

Category.propTypes = {
    id: number,
    classes: shape({
        gallery: string,
        root: string,
        title: string
    }),
    pageSize: number
};

Category.defaultProps = {
    id: 3,
    pageSize: 12
};

export default (withRouter)(Category);