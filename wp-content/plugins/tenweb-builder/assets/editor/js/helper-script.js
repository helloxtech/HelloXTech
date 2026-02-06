/*
* sending data to Google Analytics
 */
function analyticsDataPush ( action, eventName = '', info = '', params = {} ) {
    //TODO in future we can change all functions and add all keys to params
    if ( typeof dataLayer != "undefined" ) {
        let dataLayerObject = {
            event: '10web-event',
            'eventName': eventName,
            'eventAction': action,
            'info': info,
            'domain_id': twbb_helper.domain_id
        };
        Object.keys(params).forEach(key => {
            dataLayerObject[key] = params[key];
        });
        dataLayer.push(dataLayerObject);
    }
}

function twbSendEventToPublicRouth( data ){
    try {
        const sendData = Object.keys(data).reduce((newEntities, k) => {
            const newKey = k.split(/(?=[A-Z])/).join('_').toLowerCase();
            newEntities[newKey] = data[k];
            return newEntities;
        }, {});
        sendData.client_id = twbb_helper.clients_id;
        jQuery.ajax({
            type: 'POST',
            headers: {
                Accept: 'application/x.10webcore.v1+json'
            },
            url: twbb_helper.send_ga_event,
            dataType: 'json',
            data: sendData,
            success: function (result) {
            },
            error: function (xhr, status, error) {
                reject(new Error(`AJAX error: ${status} - ${error}`));
            }
        });
    }
    catch (error) {
        console.log('Error sending the events: ', error);
    }
}
