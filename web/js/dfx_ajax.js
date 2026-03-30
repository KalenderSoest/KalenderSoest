var dfxurl = null;
window.onload = function () {
    if (!document.addEventListener) {
        alert("Sie verwenden einen stark veralteten Browser. Dieser Veranstaltungskalender ist in Technologien programmiert, die von Ihrem Browser nicht dargestellt werden können.");
    }
    var dfxElement = document.getElementById('dfx');
    var dfxAttrUrl = dfxElement ? (dfxElement.getAttribute('dfx_url') || dfxElement.getAttribute('data-dfx-url')) : null;
    if (!dfxAttrUrl) {
        console.warn('dfx_url/data-dfx-url not found on #dfx element');
        return;
    }
    dfxurl = dfxAttrUrl;
    if (dfxurl.indexOf('http') === -1) {
        dfxurl = 'https://' + dfxurl;
    }

    function initDfx() {
        var loaderHtml = '<div style="text-align:center; margin-top: 50px"><img src="' + dfxurl + '/images/loader.gif" alt="loader"></div>';
        var datefixEl = document.getElementById('datefix');
        if (datefixEl) {
            datefixEl.innerHTML = loaderHtml;
        }
        addCss(dfxurl + "/fontawesome/css/all.min.css");
        addCss(dfxurl + "/js/leaflet/leaflet.css");
        addScript(dfxurl + "/js/leaflet/leaflet.js", false);
        addScript(dfxurl + "/js/bootstrap.bundle.min.js", function () {
            if (!history.pushState) {
                alert("Ihr Browser unterstuetzt einige aktuelle HTML5-Spezifikationen nicht. Die Navigation innerhalb des Veranstaltungskalenders ist deshalb nur eingeschraenkt moeglich.");
            }
            addScript(dfxurl + "/js/dfx_ajax_functions.js", false);
            addScript(dfxurl + "/js/dfx_functions.js", false);
        });
    }

    initDfx();
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
 function addCss(file){
    var head=document.getElementsByTagName("head")[0];
    var css=document.createElement('link');
    css.href=file;
    css.rel='stylesheet';
    head.appendChild(css);
}
