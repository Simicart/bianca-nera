import React from 'react';
import LoadingSpiner from 'src/simi/BaseComponents/Loading/LoadingSpiner'
import getCmsPageQuery from 'src/queries/getCmsPage.graphql';
import Identify from 'src/simi/Helper/Identify';
import { Simiquery } from 'src/simi/Network/Query'
import { smoothScrollToView } from 'src/simi/Helper/Behavior';
import ReactHTMLParser from 'react-html-parser';

require('./cms.scss');

const CMS = (props) => {
    const { id } = props;

    const variables = {
        id,
        onServer: true
    }
    smoothScrollToView($('#root'))

    return <Simiquery query={getCmsPageQuery} variables={variables}>
        {({ loading, error, data }) => {
            if (error) return <div>Data Fetch Error</div>;
            if (!data || loading) return <LoadingSpiner />;
            return <div className="container">
                <div className="cms-page static-page international-page">
                    <div className="page-title-wrapper">
                        <h1 className="page-title">
                            <span className="base" data-ui-id="page-title-wrapper">
                                {data.cmsPage && data.cmsPage.content_heading ? ReactHTMLParser(data.cmsPage.content_heading) : ''}
                            </span>
                        </h1>
                    </div>
                    <div className="columns">
                        <div className="column main">
                        {data.cmsPage && data.cmsPage.content ? ReactHTMLParser(data.cmsPage.content) : Identify.__("Not found content")}
                        </div>
                    </div>
                </div>
            </div>
        }}
    </Simiquery>
}

export default CMS;