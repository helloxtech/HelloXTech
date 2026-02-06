function twbbRequests(method, url, key = false, params = '', contentType = 'application/json'){
  return new Promise((resolve, reject) => {
    try {
      const header = {
        'Content-Type': contentType
      }
      if( twbb_ai_builder.reseller_mode ) {
        if (key) {
          header['x-api-key'] = twbb_ai_builder.builder_kit_api_key;
        }
      } else {
        header['Accept'] = "application/x.10webaiassistantapi.v1+json";
        header['Authorization'] = "Bearer " + twbb_ai_builder.access_token;
      }
      const requester = {
        method: method,
        headers: header,
      }
      if (params !== ''){
        requester.body = params;
      }

      fetch(twbb_ai_builder.builder_api + url, requester)
        .then((response) => response.json())
        .then((data) => {
          if (data.status === 200) {
            resolve(data);
          }
          else {
            reject(new Error("Unexpected response format or request not successful."));
          }
        });
    } catch (err) {
      reject(err);
    }
  });
}

function twbSendEventToRouth( data ){
  try {
    const sendData = Object.keys(data).reduce((newEntities, k) => {
      const newKey = k.split(/(?=[A-Z])/).join('_').toLowerCase();
      newEntities[newKey] = data[k];
      return newEntities;
    }, {});
    header = {
      'Accept': 'application/x.10webcore.v1+json'
    };
    header['x-api-key'] = twbb_ai_builder.builder_kit_api_key
    jQuery.ajax({
      type: 'POST',
      headers: header,
      url: twbb_ai_builder.send_ga_event,
      dataType: 'json',
      data: sendData,
      success: function (result) {
      },
      error: function (xhr, status, error) {
        console.log(`AJAX error: ${status} - ${error}`);
      }
    });
  }
  catch (error) {
    console.log('Error sending the events: ', error);
  }
}
