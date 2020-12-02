import React from 'react';
import {Carousel} from 'react-responsive-carousel';
import Identify from "src/simi/Helper/Identify";
import ImageLightbox from "./ImageLightbox";
import memoize from 'memoize-one';
import isProductConfigurable from 'src/util/isProductConfigurable';
import { resourceUrl } from 'src/simi/Helper/Url'
// import findMatchingVariant from 'src/util/findMatchingProductVariant';
import { transparentPlaceholder } from 'src/shared/images';
// import PlayCircle from 'src/simi/App/Bianca/BaseComponents/Icon/PlayCircle';
require('./style.scss')


const $ = window.$;

class ProductImage extends React.Component {

    constructor(props) {
        super(props);
        this.title = this.props.title || 'Alt';
        this.showThumbs = this.props.showThumbs || true;
        this.showArrows = this.props.showArrows || false;
        this.showIndicators = this.props.showIndicators || false;
        this.autoPlay = this.props.autoPlay || false;
        this.showStatus = this.props.showStatus || false;
        this.itemClick = this.props.itemClick || function (e) {
        };
        this.onChange = this.props.onChange || function (e) {
        };
        this.onClickThumb = this.props.onClickThumb || function (e) {
        };
        this.defaultStatusFormatter = function defaultStatusFormatter(current, total) {
            return Identify.__('%c of %t').replace('%c', current).replace('%t', total);
        };
        this.statusFormatter = this.props.statusFormatter || this.defaultStatusFormatter;
        this.infiniteLoop = this.props.infiniteLoop || false;
        this.isPhone = props.isPhone;
    }

    openImageLightbox = (index) => {
        this.lightbox.showLightbox(index);
    }

    convertEmberVideo = (url) => {
        const vimeoPattern = /(?:http?s?:\/\/)?(?:www\.)?(?:vimeo\.com)\/?(.+)/g;
        const youtubePattern = /(?:http?s?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(.+)/g;
        if (vimeoPattern.test(url)){
            const replacement = "//player.vimeo.com/video/$1";
            return url.replace(vimeoPattern, replacement);
        }
        if (youtubePattern.test(url)){
            const replacementY = "https://www.youtube.com/embed/$1";
            return url.replace(youtubePattern, replacementY);
        }
    }

    renderImageLighboxBlock = () => {
        let images = this.images
        images = images.map((item) => {
            const imageFile = item.file && item.file.url ? item.file.url : item.file;
            const imageLabel = item.file && item.file.label ? item.file.label : item.label;
            return (item.video_content && item.video_content instanceof Object ) 
            ? { url : this.convertEmberVideo(item.video_content.video_url), type: 'video', altTag: item.video_content.video_title} : (imageFile
            ? { url: imageFile.indexOf('http') === 0 ? 
                    resourceUrl(imageFile, { type: 'image-product', width: 640 }) : 
                    window.location.origin+resourceUrl(imageFile, { type: 'image-product', width: 640 }),
                type: 'photo', altTag: imageLabel}
            : { url: window.location.origin+transparentPlaceholder, type: 'photo', altTag: 'no image'})
        });
        return (
            <ImageLightbox ref={(lightbox) => {
                this.lightbox = lightbox
            }} images={images}/>
        );
    }

    renderImage() {
        const width = $('.left-layout.product-media').width();
        const {product} = this.props;
        return this.images.map(function (item) {
            const imageFile = item.file && item.file.url ? item.file.url : item.file;
            const src = imageFile
                ? resourceUrl(imageFile, { type: 'image-product', width: 640 })
                : transparentPlaceholder
            const noImage = imageFile ? 'no-image' : null;
            return (
                <div key={Identify.randomString(5)} style={{cursor: 'pointer', backgroundColor: '#ffffff'}} className="carousel-image-container">
                    {
                        item.video_content ? 
                        <img className={`video-thumb ${noImage}`} width={width} src={src} height={width} alt={product.name}
                                style={{objectFit: 'scale-down'}}
                        /> :
                        <img width={width} src={src} height={width} alt={product.name}
                            style={{objectFit: 'scale-down'}}
                        />
                    }
                </div>
            );
        })
    }

    onChangeItemDefault = () => {
        
    }

    onClickThumbDefault = () => {
        
    }

    sortAndFilterImages = memoize(items =>
        items
            .filter(i => !i.disabled)
            .sort((a, b) => {
                const aPos = isNaN(a.position) ? 9999 : a.position;
                const bPos = isNaN(b.position) ? 9999 : b.position;
                return aPos - bPos;
            })
    );

    findMatchingVariant = ({ optionSelections, product }) => {
        let images = [];
        let products = [];
        let productFiltered = [];
        const configurable_options = product && product.simiExtraField 
            && product.simiExtraField.app_options
            && product.simiExtraField.app_options.configurable_options || null;
        if (configurable_options && configurable_options.attributes) {
                const attributes = configurable_options.attributes;
                optionSelections.forEach((selected_value_index, key) => {
                    if (attributes[key] && attributes[key]['options'] && attributes[key]['options'].length) {
                        const option = attributes[key]['options'].find((option) => {
                            if (parseInt(option.id) === selected_value_index) return true;
                            return false;
                        });
                        if (option) {
                            products.push(option.products);
                        }
                    }

                });
        }
        if (products.length) {
            productFiltered = products[0];
            products.forEach((productArray) => {
                productFiltered = productArray.filter((product_id) => {
                    if (productFiltered.includes(product_id)) return true;
                    return false;
                })
            });
        }

        if (productFiltered && productFiltered.length && configurable_options && configurable_options.images) {
            productFiltered.forEach((productId) => {
                if (configurable_options.images[productId] && configurable_options.images[productId].length){
                    configurable_options.images[productId].forEach((image) => {
                        image.full && images.push({file: image.full, position: 0, disabled: false});
                    });
                }
            });
        }

        return images;
    };

    mediaGalleryEntries = () => {
        const { props } = this;
        const { optionCodes, optionSelections, product } = props;
        const { variants } = product;
        const isConfigurable = isProductConfigurable(product);

        const media_gallery_entries = product.media_gallery_entries ? 
                product.media_gallery_entries :  product.small_image ? 
                    [{file: product.small_image, disabled: false, label: '', position: 1}] : []
                    
        if (
            !isConfigurable ||
            (isConfigurable && optionSelections.size === 0)
        ) {
            return media_gallery_entries;
        }

        const varianImages = this.findMatchingVariant({
            optionSelections,
            product
        });

        if (!varianImages) {
            return media_gallery_entries;
        }

        const images = [
            ...varianImages,
            ...media_gallery_entries
        ];
        const returnedImages = []
        var obj = {};
        images.forEach(image=> {
            if (!obj[image.file]) {
                obj[image.file] = true
                returnedImages.push(image)
            }
        })

        return returnedImages
    }


    sortedImages() {
        const images= this.mediaGalleryEntries();
        return this.sortAndFilterImages(images);
    }

    renderJs = () => {
        $(document).ready(function () {
            const carousel = $('.carousel.carousel-slider');
            const mediaWidth = carousel.width();
            carousel.height(mediaWidth);
            $('.carousel.carousel-slider img').height(mediaWidth);
        });
    }

    render() {
        this.images = this.sortedImages()
        if (!this.images) {
            return null
        }
        let selectedItem = 0;
        let renderImages = this.renderImage()
        if (Identify.isRtl()) {
            renderImages.reverse();
            selectedItem = (renderImages.length - 1)
        }
        const {images} = this
        return (
            <React.Fragment>
                <Carousel className="product-carousel"
                        selectedItem={selectedItem}
                        key={(images && images[0] && images[0].file) ? images[0].file : Identify.randomString(5)}
                        showArrows={this.showArrows}  
                        showThumbs={this.showThumbs}
                        showIndicators={this.showIndicators}
                        showStatus={this.showStatus}
                        onClickItem={(e) => this.openImageLightbox(e)}
                        onClickThumb={(e) => this.onClickThumbDefault(e)}
                        onChange={(e) => this.onChangeItemDefault(e)}
                        infiniteLoop={true}
                        autoPlay={this.autoPlay}
                        thumbWidth={82}
                        statusFormatter={this.statusFormatter}
                    >
                    {renderImages}
                </Carousel>
                {this.renderImageLighboxBlock()}
            </React.Fragment>
        );
    }
}

export default ProductImage;