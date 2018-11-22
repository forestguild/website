/* WebP check */
(function(window){var html=document.documentElement,isSupported=null;function checkSupport(fn){var WebP=new Image;WebP.onload=WebP.onerror=function(){isSupported=WebP.height===2;if(isSupported){if(html.className.indexOf("no-webp")>=0)html.className=html.className.replace(/\bno-webp\b/,"webp");else html.className+=" webp"}};WebP.src="data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA"}checkSupport()})(window);

$(function () {
    $('table').tablesorter({
        theme: "blackice",
        widthFixed: true,
        widgets: ["filter"],
    })
});

{% if page.path == 'index.html' %}
$(function () {
    $('#armory-calendar').tooltip();
    {% if site.rt.setup.modifiable %}$('#raid-setup').tooltip();{% endif %}
    $('#raidform').on('click', function(e){
        $('#navbarCollapse').collapse('hide')
    });
    $.each($('iframe'), function(key, iframe) {
        $(iframe).attr('src',$(iframe).data('src'));
    });
});
{% endif %}

{% if page.layout == 'wiki' %}
var disqus_config = function () {
    this.page.url = "{{ site.url }}{{ site.baseurl }}{{ page.url | replace:'index.html','' | replace:'.html','' }}";
    this.page.identifier = "{{ page.url | replace:'index.html','' | replace:'.html','' }}";
};

(function() {
    var d = document, s = d.createElement('script');
    s.setAttribute('async', true);
    s.src = 'https://{{ site.disqus }}.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());(d.head || d.body).appendChild(s);
})();

{% endif %}

/* Wowhead tooltips config */
var whTooltips = {
    colorLinks: true,
    iconizeLinks: true,
    renameLinks: true,
    iconSize: 'small'
};

/* Yandex.Metrics goals */
setTimeout('yaCounter{{ site.analytics.yandex }}.reachGoal("1min_pageview");', 60000); //Stay on page for 1 min

