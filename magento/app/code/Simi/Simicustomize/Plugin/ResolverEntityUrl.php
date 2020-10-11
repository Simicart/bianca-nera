<?php

namespace Simi\Simicustomize\Plugin;


class ResolverEntityUrl
{
    private $simiObjectManager;
    private $request;
    private $storeManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }


    public function afterResolve($entityUrl, $result)
    {
        if (!$result) {
			$contents            = $this->request->getContent();
			$contents_array      = [];
			if ($contents && ($contents != '')) {
				$contents_parser = urldecode($contents);
				$contents_array = json_decode($contents_parser, true);
            }
            
            $requestPath = '';
	        if ( $contents_array && isset( $contents_array['variables']['urlKey'] ) ) {
		        $requestPath = $contents_array['variables']['urlKey'];
	        } else {
		        $contentQuery = $this->request->getQuery();
		        if ( $contentQuery && is_object( $contentQuery ) && isset( $contentQuery->variables ) && $contentQuery->variables ) {
			        $queryVar = json_decode( $contentQuery->variables, true );
			        if ( $queryVar && is_array( $queryVar ) && isset( $queryVar['urlKey'] ) ) {
				        $requestPath = $queryVar['urlKey'];
			        }
		        }
            }
            
			if ($requestPath) {
				$aw_blog = null;
                if ($requestPath[0] === '/') {
                    $requestPath = substr($requestPath, 1);
                }
                $path_rq = explode('/',$requestPath);
                if (count($path_rq) >= 2 && $path_rq[0] == 'blog'){
                    $collection = $this->simiObjectManager
                        ->get('Aheadworks\Blog\Model\Post')
                        ->getCollection()
                        ->addFieldToFilter('url_key', $path_rq[1]);
                    $collection->getSelect()
                        ->joinLeft(
                            ['store' => \Aheadworks\Blog\Model\ResourceModel\Post::BLOG_POST_STORE_TABLE],
                            'store.post_id = id',
                            ['store_id']
                        )->where('store.store_id = ?', $this->storeManager->getStore()->getId());
                    $aw_blog = $collection->getFirstItem();
                    return array(
                        'id' => $aw_blog->getId(),
                        'canonical_url' => $aw_blog->getData('url_key'),
                        'relative_url' => 'simi_blog_page',
                        'type' => 'CMS_PAGE'
                    );
                }
			}
		}
		return $result;
    }
}
