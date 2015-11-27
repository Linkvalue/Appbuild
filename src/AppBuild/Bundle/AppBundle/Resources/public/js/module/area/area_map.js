'use strict';

var Area = Area || {};

if (!google.maps.Polygon.prototype.getBounds) {
    google.maps.Polygon.prototype.getBounds=function(){
        var bounds = new google.maps.LatLngBounds();
        if(typeof(this.getPath()) != 'undefined') {
            this.getPath().forEach(function(element,index){bounds.extend(element)});
        }
        return bounds;
    }
}
/**
 * area module controller
 */
Area.map = function(mapBuilder, area, config) {
    var _configs = {
        container: null,
        stroke: {
            color: "#E60F39",
            opacity: 0.7,
            weight: 1
        },
        fill: {
            color: "#E60F39",
            opacity: 0.10
        }
    };
    _.merge(_configs, config ? config : {});

    var mapContainerElement = document.getElementById(_configs.container);
    if (!mapContainerElement) {
        throw 'Invalid container id : ' + _configs.container;
    }

    var _map = new mapBuilder.Map(
        mapContainerElement,
        {
            center: area.center,
            zoom: 15,
            disableDoubleClickZoom: true,
            draggable: true,
            scrollwheel: false
        }
    );

    var _mapAreaPolygon = new mapBuilder.Polygon({
        paths: area.bounds,
        map: _map,
        strokeColor: _configs.stroke.color,
        strokeOpacity: _configs.stroke.opacity,
        strokeWeight: _configs.stroke.weight,
        fillColor: _configs.fill.color,
        fillOpacity: _configs.fill.opacity
    });

    var _directionsBuilder = new mapBuilder.DirectionsService();

    var _markers = {};

    /**
     * Display an existing marker under given key
     *
     * @param string key
     */
    var _showPosition = function(key) {
        if (!_markers[key]) {
            throw 'Unknow position key ' + key;
        }
        if(_markers[key].getMap()) {
            return;
        }

        _markers[key].setMap(_map);
    };

    /**
     * Hide a marker under given key
     *
     * @param string key
     */
    var _hidePosition = function(key) {
        if (!_markers[key]) {
            return;
        }

        _markers[key].setMap(null);
    };

    /**
     * Draw a Marker on given location, under "key" identifier
     *
     * @param string key
     * @param Object location
     * @param string icon
     * @param string title
     */
    var _drawPosition = function (key, location, icon, title) {
        if (!location || !location.lat || !location.lng) {
            return;
        }

        if (!_markers[key]) {
            _markers[key] = new mapBuilder.Marker({
                title: title ? title : key,
                icon: icon
            });
        }

        _markers[key].setPosition({
            lat: parseFloat(location.lat),
            lng: parseFloat(location.lng)
        });

        _showPosition(key);

        return _markers[key];
    };

    var _directionsData = {};
    var _renderedDirections = {};

    /**
     * Draw a path from given origin/destination locations
     *
     * @param string key
     * @param Object originLocation
     * @param Object destinationLocation
     * @param string travelMode
     * @param Object options
     *
     * @return Promise
     */
    var _drawPath = function (key, originLocation, destinationLocation, travelMode, options) {
        var directionQuery = {
            origin: new mapBuilder.LatLng(
                originLocation.lat,
                originLocation.lng
            ),
            destination: new mapBuilder.LatLng(
                destinationLocation.lat,
                destinationLocation.lng
            ),
            travelMode: travelMode.toUpperCase()
        };

        var _options = {
            line: {
                color: null,
                opacity: null,
                weight: 3
            }
        };
        _.merge(_options, options ? options : {});

        var ready = _.has(_directionsData, key) && !_.isEmpty(_directionsData, key);

        return new Promise(function (resolve, reject) {
                if (ready) {
                    resolve(key);
                }

                _directionsBuilder.route(directionQuery, function (result, status) {
                    if (status != mapBuilder.DirectionsStatus.OK) {
                        reject(result);
                    }

                    _directionsData[key] = {
                        directions: result,
                        preserveViewport: true,
                        suppressInfoWindows: true,
                        suppressMarkers: true,
                        polylineOptions: {
                            strokeColor: _options.line.color,
                            strokeOpacity: _options.line.opacity,
                            strokeWeight: _options.line.weight
                        }
                    };

                    resolve(key);
                });
            })
            .then(_showPath)
        ;
    };

    /**
     * Displays a created path under given key
     *
     * @param string key
     */
    var _showPath = function(key) {
        if (!_.has(_directionsData, key)) {
            throw 'Unknow direction key ' + key;
        }

        _hidePath(key);

        _renderedDirections[key] = new mapBuilder.DirectionsRenderer(
            _directionsData[key]
        );

        _renderedDirections[key].setMap(_map);
    };

    /**
     * Hide reated path under given key
     *
     * @param string key
     */
    var _hidePath = function(key) {
        if(!_.has(_renderedDirections, key)) {
            return;
        }

        _renderedDirections[key].setMap(null);
        delete _renderedDirections[key];
    };

    var _getMap = function() {
        return _map;
    };

    var _refreshPolygon = function(bounds) {
        _mapAreaPolygon.setPaths(bounds);
    };

    var _getCenterPolygon = function() {
         return _mapAreaPolygon.getBounds().getCenter();
    };

    return {
        get: _getMap,
        position: {
            draw: _drawPosition,
            show: _showPosition,
            hide: _hidePosition,
            refreshPolygon: _refreshPolygon,
            getCenter: _getCenterPolygon,
            clear: function() {
                _.forEach(_markers, function(marker, key) {
                    _hidePosition(key);
                });
            }
        },
        direction: {
            draw: _drawPath,
            show: _showPath,
            hide: _hidePath,
            trash: function (key) {
                if (_.has(_directionsData, key)) {
                    _hidePath(key);
                    delete _directionsData[key];
                }
            },
            clear: function() {
                _.forEach(_directionsData, function(direction, key) {
                    _hidePath(key);
                });
            }
        }
    };
};
