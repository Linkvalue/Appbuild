'use strict';

var Drop = Drop || {};

/**
 * Websocket client class
 */
Drop.websocket = (function (_) {

    var _config = {
        endpoint: null,
        max_reconnection_retry: 5
    };

    var _handlers;
    var _socket;

    /**
     * initialisation method
     *
     * @param {Object} handlers event => handler key value object
     * @param {Object} config   websocket configurations
     *
     * @return Promise
     */
    var _init = function(handlers, config) {
        _.merge(_config, config);
        _handlers = handlers;

        return _connect();
    };

    /**
     * connect websocket
     */
    var _connect = function() {
        return new Promise(function(success, error) {
            _socket = io.connect(_config.endpoint);

            // connection handler
            _socket.on('connect', function () {
                console.log('Connected to websocket server.');

                // listen events
                _.forEach(_.keys(_handlers), function(event) {
                    _socket.emit('subscribe', event);
                });

                return success();
            });

            // reconnection handler
            _socket.on('reconnect', function (number) {
                console.log('Reconnected to websocket server (' + number + ' tries needed).');

                return success();
            });

            // reconnecting handler
            _socket.on('reconnecting', function (number) {
                console.log('Trying to reconnect to websocket server (' + number + ' tries).');

                // dont try to reconnect anymore after max_reconnection_retry tries
                if(number >= _config.max_reconnection_retry){
                    console.log('Websocket connection aborted due to max reconnection reached (' + _config.max_reconnection_retry + ').');

                    _socket.disconnect();
                    return error();
                }
            });

            // disconnection handler
            _socket.on('disconnect', function () {
                console.log('Disconnected from websocket server.');
            });

            // error connection handler
            _socket.on('error', function (errorInfo) {
                console.log('Error while trying to connect to websocket server.');
                console.log(errorInfo);

                _socket.disconnect();
                return error();
            });

            // Messages received from websocket server
            _socket.on('event', function (event) {
                console.log('Event received: ' + event.event);

                if (!_handlers[event.event]) {
                    return;
                }

                _handlers[event.event](event.data);
            });
        });
    };

    return {
        init: _init
    };
})(_);
