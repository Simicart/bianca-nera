import React from "react";
import Identify from 'src/simi/Helper/Identify'
import GoogleMapReact from "google-map-react";
import CurrentMarker from './CurrentMarker';

class MapBranch extends React.Component {
    constructor(props) {
        super(props);
        this.state = { centerT: props.center, zoom: 0 };
    }

    static defaultProps = {
        center: {
            lat: 29.37,
            lng: 47.98
        },
        zoom: 9
    };

    componentDidMount(){
        let objCenter = this.props.center;
        const {data, multiple, currentLocation} = this.props;
        if (this.props.markerFocus && this.props.markerFocus.center) {
            // return { ...state, centerT: this.props.markerFocus.center };
            this.setState({ centerT: this.props.markerFocus.center, zoom: 9 });
        }
        if (data) {
            if(multiple && data.storelocations.length > 0){
                const arrLngLat = [];
                data.storelocations.map((item) => {
                    const lat = Number(item.latitude);
                    const lng = Number(item.longitude);
                    return arrLngLat.push({ lat, lng });
                });
                if (currentLocation){
                    const crLat = Number(currentLocation.lat);
                    const crLng = Number(currentLocation.lng);
                    arrLngLat.push({ lat: crLat, lng: crLng });
                }
                objCenter = this.averageGeolocation(arrLngLat);
            }
            /* else{
                objCenter = {lat: Number(this.props.center.lat), lng: Number(this.props.center.lng)}
            } */
            // return { ...state, centerT: objCenter };
            this.setState({ centerT: objCenter });
        }
    }
    /* componentWillMount() {
        let objCenter = this.props.center;
        const {data, multiple, currentLocation} = this.props;
        if (data) {
            if(multiple && data.storelocations.length > 0){
                const arrLngLat = [];
                data.storelocations.map((item) => {
                    const lat = Number(item.latitude);
                    const lng = Number(item.longitude);
                    return arrLngLat.push({ lat, lng });
                });
                if (currentLocation){
                    const crLat = Number(currentLocation.lat);
                    const crLng = Number(currentLocation.lng);
                    arrLngLat.push({ lat: crLat, lng: crLng });
                }

                objCenter = this.averageGeolocation(arrLngLat)
            }else{
                objCenter = {lat: Number(data.latitude), lng: Number(data.longitude)}
            }

            this.setState({ centerT: objCenter });
        }
    } */

    /* componentWillReceiveProps(nextProps) {
        if (
            this.props.markerFocus &&
            JSON.stringify(this.props.markerFocus.center) !==
                JSON.stringify(nextProps.markerFocus.center)
        ) {
            this.setState({ centerT: nextProps.markerFocus.center });
        }
    } */

    getAveragePosition = () => {
        let center = this.props.center;
        const {data, multiple} = this.props;
        if (data && multiple && data.storelocations.length > 0) {
            const arrLngLat = [];
            data.storelocations.map((item) => {
                const lat = Number(item.latitude);
                const lng = Number(item.longitude);
                return arrLngLat.push({ lat, lng });
            });
            center = this.averageGeolocation(arrLngLat);
        }
        return center;
    }

    averageGeolocation = coords => {
        if (coords.length === 1) {
            return coords[0];
        }

        let x = 0.0;
        let y = 0.0;
        let z = 0.0;

        for (const coord of coords) {
            const latitude = (coord.lat * Math.PI) / 180;
            const longitude = (coord.lng * Math.PI) / 180;

            x += Math.cos(latitude) * Math.cos(longitude);
            y += Math.cos(latitude) * Math.sin(longitude);
            z += Math.sin(latitude);
        }

        const total = coords.length;

        x = x / total;
        y = y / total;
        z = z / total;

        const centralLongitude = Math.atan2(y, x);
        const centralSquareRoot = Math.sqrt(x * x + y * y);
        const centralLatitude = Math.atan2(z, centralSquareRoot);

        return {
            lat: (centralLatitude * 180) / Math.PI,
            lng: (centralLongitude * 180) / Math.PI
        };
    };

    currentMaker = () => {
        let html = null;
        const {currentLocation} = this.props;
        if (currentLocation){
            html = <CurrentMarker
                    lat={currentLocation.lat}
                    lng={currentLocation.lng}
                    className="current-store-maker"
                    text={''}
                    color="#101820"
                />
        }
        return html;
    }

    // Return the object {sw: {lat, lng}, ne: {lat, lng}}
    getBound = () => {
        const {data, multiple} = this.props;
        if (multiple && data && data.storelocations.length > 0) {
            let minLat = 0;
            let maxLat = 0;
            let minLng = 0;
            let maxLng = 0;
            data.storelocations.map((item, index) => {
                if (index === 0) {
                    minLat = item.latitude;
                    maxLat = item.latitude;
                    minLng = item.longitude;
                    maxLng = item.longitude;
                }
                if (minLat > item.latitude) minLat = item.latitude;
                if (maxLat < item.latitude) maxLat = item.latitude;
                if (minLng > item.longitude) minLng = item.longitude;
                if (maxLng < item.longitude) maxLng = item.longitude;
            });
            return {
                sw: {lat: parseFloat(minLat), lng: parseFloat(minLng)},
                ne: {lat: parseFloat(maxLat), lng: parseFloat(maxLng)}
            }
        }
        return {}
    }

    listMaker = () => {
        let html = null;
        const {data, markerFocus, multiple} = this.props;
        if (data) {
            if(multiple && data.storelocations.length > 0){
                html = data.storelocations.map((item, index) => {
                    const focus =
                        markerFocus.id === item.simistorelocator_id ? "active" : "";
                    return (
                        <MakerComponent
                            key={item.simistorelocator_id}
                            lat={item.latitude}
                            lng={item.longitude}
                            className={`store-maker ${focus}`}
                            text={Identify.__(index + 1)}
                        />
                    );
                });
            }else{
                html = <MakerComponent
                        lat={data.latitude}
                        lng={data.longitude}
                        className="store-maker active"
                        text={''}
                        // color="#101820"
                    />
            }
        }
        return html;
    };

    render() {
        const mapOptions = {
            styles: [
                {
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#f5f5f5"
                        }
                    ]
                },
                {
                    elementType: "labels.icon",
                    stylers: [
                        {
                            visibility: "off"
                        }
                    ]
                },
                {
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#616161"
                        }
                    ]
                },
                {
                    elementType: "labels.text.stroke",
                    stylers: [
                        {
                            color: "#f5f5f5"
                        }
                    ]
                },
                {
                    featureType: "administrative.land_parcel",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#bdbdbd"
                        }
                    ]
                },
                {
                    featureType: "poi",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#eeeeee"
                        }
                    ]
                },
                {
                    featureType: "poi",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#757575"
                        }
                    ]
                },
                {
                    featureType: "poi.park",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#e5e5e5"
                        }
                    ]
                },
                {
                    featureType: "poi.park",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#9e9e9e"
                        }
                    ]
                },
                {
                    featureType: "road",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#ffffff"
                        }
                    ]
                },
                {
                    featureType: "road.arterial",
                    stylers: [
                        {
                            visibility: "off"
                        }
                    ]
                },
                {
                    featureType: "road.arterial",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#757575"
                        }
                    ]
                },
                {
                    featureType: "road.highway",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#dadada"
                        }
                    ]
                },
                {
                    featureType: "road.highway",
                    elementType: "labels",
                    stylers: [
                        {
                            visibility: "off"
                        }
                    ]
                },
                {
                    featureType: "road.highway",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#616161"
                        }
                    ]
                },
                {
                    featureType: "road.local",
                    stylers: [
                        {
                            visibility: "off"
                        }
                    ]
                },
                {
                    featureType: "road.local",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#9e9e9e"
                        }
                    ]
                },
                {
                    featureType: "transit.line",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#e5e5e5"
                        }
                    ]
                },
                {
                    featureType: "transit.station",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#eeeeee"
                        }
                    ]
                },
                {
                    featureType: "water",
                    elementType: "geometry",
                    stylers: [
                        {
                            color: "#c9c9c9"
                        }
                    ]
                },
                {
                    featureType: "water",
                    elementType: "labels.text.fill",
                    stylers: [
                        {
                            color: "#9e9e9e"
                        }
                    ]
                }
            ]
        };
        const { centerT } = this.state;
        const { height, data, markerFocus } = this.props;

        let zoom = this.props.zoom;
        let centerFocus = markerFocus && markerFocus.center || centerT;
        if (markerFocus && markerFocus.center) {
            centerFocus = markerFocus.center;
            zoom = 12;
        } else {
            centerFocus = this.getAveragePosition();
            if (window.mapzoom) {
                zoom = window.mapzoom;
            }
        }

        return (
            <div style={{ height: height, width: "100%" }}>
                <GoogleMapReact
                    bootstrapURLKeys={{
                        key: (data && data.google_api_key) ? data.google_api_key : "AIzaSyDVCRi-1g45uNxpu_VY3o3M9A1UqtezG1k",
                        language: "en"
                    }}
                    center={centerFocus}
                    zoom={zoom}
                    defaultCenter={this.props.center}
                    defaultZoom={this.props.zoom}
                    options={mapOptions}
                    onGoogleApiLoaded={({ map, maps }) => {
                        if (markerFocus && markerFocus.center) return;
                        if (map) {
                            this.map = map;
                            const bound = this.getBound();
                            var bounds = new google.maps.LatLngBounds();
                            bounds.extend(new google.maps.LatLng(bound.sw));
                            bounds.extend(new google.maps.LatLng(bound.ne));
                            map.fitBounds(bounds);
                            window.mapzoom = map.getZoom();
                        } else {
                            console.warn('No map of google map api')
                        }
                    }}
                >
                    {this.listMaker()}
                    {/* {this.currentMaker()} */}
                </GoogleMapReact>
            </div>
        );
    }
}

const MakerComponent = ({ text, className, current = false }) => (
    <div
        style={{
            color: "#fff",
            width: 23,
            height: 23,
            background: !current ? "#101820" : "#101820",
            display: "inline-flex",
            position: "absolute",
            textAlign: "center",
            alignItems: "center",
            justifyContent: "center",
            borderRadius: "50%",
            lineHeight: 23,
            fontSize: 14,
            transition: "all 1s",
            zIndex: 1
        }}
        className={className}
    >
        {text}
    </div>
);

export default MapBranch;
