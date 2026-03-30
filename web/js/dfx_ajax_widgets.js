var dfxurl = null;
window.onload = function () {
    if(!  document.addEventListener  ){
        alert("Sie verwenden einen stark veralteten Browser. Dieser Veranstaltungskalender ist in Technologien programmiert, die von Ihrem Browser nicht dargestellt werden können.");
    }
    var dfxElement = document.getElementById('dfx-widgets');
    var dfxAttrUrl = dfxElement ? (dfxElement.getAttribute('dfx_url') || dfxElement.getAttribute('data-dfx-url')) : null;
    if (!dfxAttrUrl) {
        console.warn('dfx_url/data-dfx-url not found on #dfx element');
        return;
    }
    dfxurl = dfxAttrUrl;
    if (dfxurl.indexOf('http') === -1) {
        dfxurl = 'https://' + dfxurl;
    }

    function initDfxWidgets() {
        var loaderHtml = '<div style="text-align:center; margin-top: 50px"><img src="' + dfxurl + '/images/loader.gif" alt="loader"></div>';
        var el = document.getElementById('dfx-widgets');
        if (el) {
            el.innerHTML = loaderHtml;
        }
        addScript(dfxurl + "/js/bootstrap.bundle.min.js", false);
        addScript(dfxurl + "/js/dfx_widgets.js", false);
    }

    initDfxWidgets();
};

function addScript(file,callback){
    var head=document.getElementsByTagName("head")[0];
    var script=document.createElement('script');
    script.src=file;
    script.type='text/javascript';
    //real browsers
    if(callback){
        script.onload=callback;
        //Internet explorer
        script.onreadystatechange = function() {
            if (this.readyState === 'complete') {

                callback();
            }
        }
    }
    head.appendChild(script);
}


