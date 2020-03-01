import React from 'react';
import Identify from "src/simi/Helper/Identify";

const Copyright = props => {
    const {isPhone, classes} = props;
    let copyright = `${new Date().getFullYear()} ${Identify.__('SimiCart')}`;
    return (
        <div className={classes["app-copyright"]}>
            <div className="container">
                <div className={`row ${classes['pd-tb-15']}`}>
                    <div className={`col-xs-6 ${classes["app--flex"]}`} style={{ frontSize: '13px'}}>
                        <span>
                            &copy; {copyright}
                        </span>
                    </div>
                    <div
                            className="col-xs-6 text-right"
                            style={{ fontSize: "13px" }}
                        >
                            {Identify.__("eCommerce by ")}
                            <a href="https://www.simicart.com/" style={{ color: "#FC565A" }}>
                                {Identify.__("SimiCart")}
                            </a>
                        </div>
                </div>
            </div>
        </div>
    )
}

export default Copyright;