import React, { useEffect } from 'react';
import LoadingSpiner from '/src/simi/core/BaseComponents/Loading/LoadingSpiner'
import { string, number, shape } from 'prop-types';
import { usePagination, useQuery } from '@magento/peregrine';

import { mergeClasses } from 'src/classify';
import categoryQuery from 'src/simi/queries/getCategory.graphql';
import CategoryContent from './categoryContent';
import defaultClasses from './category.css';

const Category = props => {
    const { id, pageSize } = props;

    const [paginationValues, paginationApi] = usePagination();
    const { currentPage, totalPages } = paginationValues;
    const { setCurrentPage, setTotalPages } = paginationApi;

    const pageControl = {
        currentPage,
        setPage: setCurrentPage,
        updateTotalPages: setTotalPages,
        totalPages
    };

    const [queryResult, queryApi] = useQuery(categoryQuery);
    const { data, error, loading } = queryResult;
    const { runQuery, setLoading } = queryApi;
    const classes = mergeClasses(defaultClasses, props.classes);

    useEffect(() => {
        setLoading(true);
        runQuery({
            variables: {
                id: Number(id),
                pageSize: Number(pageSize),
                currentPage: Number(currentPage),
                stringId: String(id),
            }
        });

        window.scrollTo({
            left: 0,
            top: 0,
            behavior: 'smooth'
        });
    }, [id, pageSize, currentPage]);

    const totalPagesFromData = data
        ? data.products.page_info.total_pages
        : null;
    useEffect(() => {
        setTotalPages(totalPagesFromData);
    }, [totalPagesFromData]);

    if (error) return <div>Data Fetch Error</div>;
    // show loading indicator until our data has been fetched and pagination state has been updated
    if (!totalPages) return <LoadingSpiner />;

    // if our data is still loading, we want to reset our data state to null
    return (
        <CategoryContent
            classes={classes}
            pageControl={pageControl}
            data={loading ? null : data}
        />
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
    pageSize: 6
};

export default Category;
