/*
Header and Menu items
*/
import React from 'react'
import PropTypes from 'prop-types'
import LeftMenuContent from './LeftMenuContent'
import {itemTypes} from './Consts'
import {configColor} from 'src/simi/Config';

class Dashboardmenu extends React.Component {

    getBrowserHistory = () => {
        return this.props.history
    }

    replaceLink = (link) => {
        this.getBrowserHistory().replace(link);
    }

    handleLink = (link) => {
        this.getBrowserHistory().push(link);
    }

    handleShowMenu = () => {
        if (this.leftMenu)
            this.leftMenu.handleOpenMenu()
    }

    handleClickMenuItem = (item) => {
        const itemTypeId = parseInt(item.type, 10)
        const typeIndex = itemTypeId - 1
        if (itemTypes[typeIndex]) {
            const itemType = itemTypes[typeIndex]
            if (itemType['url']) {
                this.handleLink(itemType['url'])
            } else {
                if (itemTypeId === 13) { //open a category page
                    if (item.category_id)
                        this.handleLink(`/products?cat=${item.category_id}`)
                } else if (itemTypeId  === 18) { //open an url
                    if (item.content) {
                        if (item.content.includes('http://') || item.content.includes('https://'))
                            window.location.href = item.content;
                        else
                            this.handleLink(item.content)
                    }
                } else if (itemTypeId  === 20) { //open menu
                    this.handleShowMenu()
                } else if (itemTypeId  === 24) { //open customization page
                    if (item.content)
                        this.handleLink('/' + item.content)
                }
            }
        }
    }
    
    renderLeftMenu = () => {
        if (this.props.leftMenuItems) {
            return (
                <LeftMenuContent 
                    classes={this.props.classes} 
                    ref={node => this.leftMenu = node} 
                    leftMenuItems={this.props.leftMenuItems} 
                    isPhone={this.props.isPhone}
                    rootCategoryId={this.props.rootCategoryId}
                    handleLink={this.handleLink.bind(this)}
                    parent={this}
                />
            )
        }
        return
    }

    render() {
        return (
            <React.Fragment>
                <aside className={this.props.className} style={{backgroundColor: configColor.menu_background}}>
                    <div>
                        {this.renderLeftMenu()}
                    </div>
                </aside>
            </React.Fragment>
        )
    }
}

Dashboardmenu.contextTypes = {
    className: PropTypes.string,
    leftMenuItems: PropTypes.object,
    router: PropTypes.object,
    classes: PropTypes.object,
    rootCategoryId: PropTypes.string,
    history: PropTypes.object,
};
export default Dashboardmenu
