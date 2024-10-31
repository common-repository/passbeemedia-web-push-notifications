window._pbmCallback = function(data){
    if(data.registered){
        if(data.enabled){
            pbmToken = data.deviceToken;
            pbmEnabled = data.enabled;
        }
    }
}
