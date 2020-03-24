import React from 'react'
import Identify from "src/simi/Helper/Identify"
import HeaderNavMegaitem from './HeaderNavMegaitem'
import { Link } from 'src/drivers';
import NavTrigger from './navTrigger'
import MenuIcon from 'src/simi/BaseComponents/Icon/Menu'
import {cateUrlSuffix} from 'src/simi/Helper/Url'


class Navigation extends React.Component{

    componentDidMount(){
        // let topPx = 0;
        // if ($('.header-wrapper .container-header') && $('.header-wrapper .container-header').length) {
        //     topPx = $('.header-wrapper .container-header').offset().top + 32;
        // }
        // if ($('.header-wrapper') && $('.header-wrapper').length) {
        //     topPx = $('.header-wrapper').offset().top + $('.header-wrapper').outerHeight();
        // }
        if ($('.app-nav') && $('.app-nav').length) {
            let topPx = 0;
            if ($('.app-nav-main') && $('.app-nav-main').length) {
                topPx = $('.app-nav-main').offset().top;
            }
            let isSticker = false;
            let oldMainpageMargintop = 0;
            if ($('#siminia-main-page') && $('#siminia-main-page').length) {
                oldMainpageMargintop = $('#siminia-main-page')[0].style.marginTop || 0;
            }
            // $('.app-nav .sub-items').css({top: $('.app-nav-main').outerHeight()});
            $(document).scroll(() => {
                if ($(window).scrollTop() >= topPx) {
                    if (isSticker) return;
                    if ($('.header-wrapper') && $('.header-wrapper').length) {
                        $('.header-wrapper').addClass('sticker')
                    }
                    $('.app-nav').addClass('sticker')
                    // if ($('#siminia-main-page') && $('#siminia-main-page').length) {
                    //     $('#siminia-main-page').css({
                    //         marginTop: topPx + $('.header-wrapper .app-nav').outerHeight() + 'px'
                    //     })
                    // }
                    isSticker = true;
                } else {
                    if (!isSticker) return;
                    if ($('.header-wrapper') && $('.header-wrapper').length) {
                        $('.header-wrapper').removeClass('sticker')
                    }
                    $('.app-nav').removeClass('sticker')
                    // if ($('#siminia-main-page') && $('#siminia-main-page').length) {
                    //     $('#siminia-main-page').css({
                    //         marginTop: oldMainpageMargintop + 'px'
                    //     })
                    // }
                    isSticker = false;
                }
            })
        }
    }

    toggleMegaItemContainer() {
        const { classes } = this.props
        $(`.${classes['main-nav']}`).find(`.${classes['nav-item-container']}`).each(function() {
            $(this).removeClass(classes['active'])
        });
    }

    hoverActiveItem = (e) => {
        let id = e.currentTarget.id || '';
        // $(`.app-nav .${id} .sub-item`).css({top: $(e.currentTarget).outerHeight()});
        $(`.app-nav .${id} .sub-item`).addClass('active')
        
    }
    hoverDisableItem = (e) => {
        let id = e.currentTarget.id || '';
        $(`.app-nav .${id} .sub-item`).removeClass('active')
    }

    render() {
        const { classes, addClassNames } = this.props
        let menuItems = []
        const showMenuTrigger = false
        let subItems = []
        if (window.DESKTOP_MENU) {
            var menuItemsData = window.DESKTOP_MENU
            menuItems = menuItems.sort(function(a, b){
                if (a.position && b.position){
                    if (a.position > b.position) return 1; 
                    if (a.position < b.position) return -1;
                }
                return 0
            });
            menuItems = menuItemsData.map((item, index) => {
                var isActive = item.link.indexOf(window.location.pathname) !== -1 ? 'active':'';
                if(!item.include_in_menu){
                    return null
                }
                if (item.children && item.children.length > 0) {
                    let title = item.name
                    if (item.link) {
                        const location = {
                            pathname: item.link,
                            state: {}
                        }
                        title = (
                            <Link 
                            className={classes["nav-item"]+' '+isActive}
                            to={location}
                            >
                                {Identify.__(item.name)}
                            </Link>
                        )
                    }

                    const navItemContainerId = `nav-item-container-${item.menu_item_id}`
                    return (
                        <div
                        key={index} 
                        id={navItemContainerId}
                        role='button'
                        tabIndex='0'
                        onKeyDown={()=>{}}
                        className={classes['nav-item-container']}
                        onFocus={this.hoverActiveItem}
                        onMouseOver={this.hoverActiveItem}
                        onBlur={this.hoverDisableItem}
                        onMouseOut={this.hoverDisableItem}
                        onClick={this.hoverDisableItem}>
                            {title}
                            <div className="sub-item">
                                <div className="container">
                                    <HeaderNavMegaitem 
                                    parentId={item.menu_item_id}
                                    classes={classes}
                                    data={item} 
                                    itemAndChild={item}
                                    toggleMegaItemContainer={()=>this.toggleMegaItemContainer()}
                                />
                                </div>
                            </div>
                        </div>
                    )
                }
                else {
                    
                    if (item.link && item.link.includes('http')) {
                        return (
                            <a 
                                className={classes["nav-item"]+' '+isActive}
                                key={index} 
                                href={item.link ? item.link : '/'}
                                style={{textDecoration: 'none'}}
                            >
                                {Identify.__(item.name)}
                            </a>
                        )
                    }

                    return (
                        <Link 
                            className={classes["nav-item"]+' '+isActive}
                            key={index} 
                            to={item.link?`${item.link}`:'/'}
                            style={{textDecoration: 'none'}}
                        >
                            {Identify.__(item.name)}
                        </Link>
                    )
                }
            })
        } else {
            const storeConfig = Identify.getStoreConfig();
            if (storeConfig && storeConfig.simiRootCate && storeConfig.simiRootCate.children) {
                var rootCateChildren  = storeConfig.simiRootCate.children
                rootCateChildren = rootCateChildren.sort(function(a, b){
                    return a.position - b.position
                });
                menuItems = rootCateChildren.map((item, index) => {
                    var isActive = window.location.pathname.indexOf(item.url_path) === 1 ? 'active':'';
                    
                    if(!item.include_in_menu){
                        return null
                    }
                    if (!item.name)
                        return ''
                    if (item.children && item.children.length > 0) {
                        const location = {
                            pathname: '/' + item.url_path + cateUrlSuffix(),
                            state: {}
                        }
                        const navItemContainerId = `nav-item-container-${item.id}`
                        return (
                            <div
                                key={index} 
                                id={navItemContainerId}
                                role='button'
                                tabIndex='0'
                                onKeyDown={()=>{}}
                                className={`${classes['nav-item-container']} ${navItemContainerId}`}
                                onFocus={this.hoverActiveItem}
                                onMouseOver={this.hoverActiveItem}
                                onBlur={this.hoverDisableItem}
                                onMouseOut={this.hoverDisableItem}
                                onClick={this.hoverDisableItem}
                                >
                                <Link
                                    className={'nav-item '+ isActive}
                                    to={location}
                                    >
                                    {Identify.__(item.name)}
                                </Link>
                                <div className="sub-item">
                                    <div className="container">
                                        <HeaderNavMegaitem 
                                            parentId={item.id}
                                            classes={classes}
                                            data={item} 
                                            itemAndChild={item}
                                            childCol={2}
                                            toggleMegaItemContainer={()=>this.toggleMegaItemContainer()}
                                        />
                                    </div>
                                </div>
                            </div>
                        )
                    } else {
                        return (
                            <Link 
                                className={classes["nav-item"]+' '+isActive}
                                key={index} 
                                to={'/' + item.url_path + cateUrlSuffix()}
                                style={{textDecoration: 'none'}}
                                >
                                {Identify.__(item.name)}
                            </Link>
                        )
                    }
                })
            }
        }
        return (
            <div className={`${classes["app-nav"]} ${addClassNames ? addClassNames:''}`}>
                <div className="container">
                    <div className={classes["main-nav"]}>
                        {
                            showMenuTrigger && 
                            <NavTrigger>
                                <MenuIcon color="white" style={{width:30,height:30}}/>
                            </NavTrigger>
                        }
                        {menuItems}
                    </div>
                </div>
            </div>
        );
    }
}
export default Navigation