import React from 'react'
import Abstract from '../Base'
import { Switch, Route } from 'react-router-dom';
import router from "./RouterConfig";
class AppRouter extends Abstract{

    renderLayout = ()=>{
        return(
            this.renderRoute(router)
        )
    }
    renderRoute =(router = null)=>{
        if(!router) return <div></div>
        let routes = [];
        for(let routeName in router){
            routes.push(<Route exact {...router[routeName]} key={routeName}/>);
        };
        return (
            <Switch>
                {routes}
                {this.renderPbRoute()}
                <Route {...router.noMatch}/>
            </Switch>
        )
    }

    render(){
        return super.render()
    }

}
export default AppRouter;