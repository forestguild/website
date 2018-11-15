$(function () {
    $('table').tablesorter({
        theme: "blackice",
        widthFixed: true,
        widgets: ["filter"],
    })
});

{% if page.layout == 'home' %}
$(function () {
    $('#armory-calendar').tooltip();
    {% if site.rt.setup.modifiable %}$('#raid-setup').tooltip();{% endif %}
    $('#raidform').on('click', function(e){
        $('#navbarCollapse').collapse('hide')
    });
});
{% endif %}

var whTooltips = {
    colorLinks: true,
    iconizeLinks: true,
    renameLinks: true,
    iconSize: 'small'
};

var disqus_config = function () {
    this.page.url = "{{ site.url }}{{ site.baseurl }}{{ page.url | replace:'index.html','' | replace:'.html','' }}";
    this.page.identifier = "{{ page.url | replace:'index.html','' | replace:'.html','' }}";
};

(function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://{{ site.disqus }}.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());(d.head || d.body).appendChild(s);
})();
