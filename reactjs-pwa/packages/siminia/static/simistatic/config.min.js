function isIE(){
    if (!window) return false;
    // Opera 8.0+
    var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

    // Firefox 1.0+
    var isFirefox = typeof InstallTrigger !== 'undefined';

    // Safari 3.0+ "[object HTMLElementConstructor]" 
    var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));

    // Internet Explorer 6-11
    var isIE = /*@cc_on!@*/false || !!document.documentMode;

    // Edge 20+
    var isEdge = !isIE && !!window.StyleMedia;

    // Chrome 1 - 79
    var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);

    // Edge (based on chromium) detection
    var isEdgeChromium = isChrome && (navigator.userAgent.indexOf("Edg") != -1);

    // Blink engine detection
    var isBlink = (isChrome || isOpera) && !!window.CSS;

    if (navigator && navigator.userAgent && navigator.userAgent.search('Chrome') !== -1) {
        return false;
    }

    if (isIE || isEdge || isEdgeChromium) {
        return true;
    }
    return false;
}

if (isIE()) {
    // alert("Please download Chrome, Safari or update the to Microsoft Edge.");
    if(confirm("Please download Chrome, Safari or upgrade your IE browser to Microsoft Edge.")){
        window.location.href = "https://www.microsoft.com/en-us/edge";
    }
}

var SMCONFIGS = {
    //magento url, end with slash
    merchant_url: 'https://bianca-nera.com/magento/',
    //for pwastudio, call api directly to merchant instead of calling via upward
    directly_request: true,
    simicart_url: "https://www.simicart.com/appdashboard/rest/app_configs/",
    simicart_authorization: "f95d84b5S2IHxHQxbl3HWg3kGQaw9zQpJVDSZOX",
    notification_api: "/rest/V1/simiconnector/",
    base_name: "",
    logo_url: "/images/logo_footer.png",
    //eg. url is https://codymap.com/magento23 and media url must include pub, value should be 'magento23/pub/'
    media_url_prefix :'pub/'
};

var DEFAULT_COLORS = {
    key_color: '#ff9800',
    top_menu_icon_color: '#ffffff',
    button_background: '#101820',
    button_text_color: '#ffffff',
    menu_background: '#1b1b1b',
    menu_text_color: '#ffffff',
    menu_line_color: '#292929',
    menu_icon_color: '#ffffff',
    search_box_background: '#f3f3f3',
    search_text_color: '#7f7f7f',
    app_background: '#ffffff',
    content_color: '#131313',
    image_border_color: '#f5f5f5',
    line_color: '#e8e8e8',
    price_color: '#ab452f',
    special_price_color: '#ab452f',
    icon_color: '#717171',
    section_color: '#f8f8f9',
    status_bar_background: '#ffffff',
    status_bar_text: '#000000',
    loading_color: '#000000',
};
/*
var DESKTOP_MENU = [
    {
        menu_item_id: 2,
        name: 'Bottom',
        children: [
            {
                name: 'Bottom',
                link: '/venia-bottoms/venia-pants.html'
            },
            {
                name: 'Skirts',
                link: '/venia-bottoms/venia-skirts.html'
            }
        ],
        image_url: 'https://magento23.pwa-commerce.com/pub/media/catalog/category/softwoods-hardwoods-lp-2.jpg',
        link: '/venia-bottoms.html'
    },
    {
        menu_item_id: 3,
        name: 'Top',
        children: [
            {
                name: 'Blouses & Shirts',
                link: '/venia-tops/venia-sweaters.html'
            },
            {
                name: 'Sweaters',
                link: '/venia-tops/venia-blouses.html'
            }
        ],
        link: '/venia-tops.html'
    },
    {
        menu_item_id: 4,
        name: 'Accessories',
        children: [
            {
                name: 'Sub of accessories',
                children: [
                    {
                        name: 'Jewelry',
                        link: '/venia-accessories/venia-jewelry.html'
                    },
                    {
                        name: 'Scarves',
                        link: '/venia-accessories/venia-scarves.html'
                    },
                ]
            },
            {
                name: 'Belts',
                link: '/venia-accessories/venia-belts.html'
            }
        ],
        link: '/venia-accessories.html'
    }
]
*/