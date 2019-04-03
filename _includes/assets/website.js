{% if page.path != 'mythic.html' %}
$(function () {
    $('table').tablesorter({
        theme: "dark",
        widthFixed: true,
        widgets: ["filter"],
    });
    $('[data-toggle="tooltip"]').tooltip();
    $.ajax('https://api.twitch.tv/helix/streams?user_login={{ site.data.media.stream.users | join: '&user_login=' }}', {
        cache: true,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('Client-ID', '{{ site.data.media.stream.id }}');
        }
    }).done(function(data){
        if(data.data.length > 0) {
            $('.streaming_now').html(data.data.length + '*');
        }
    });
});
{% endif %}

{% if page.layout == 'wiki' or page.path == 'mythic.html' %}
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
setTimeout('yaCounter{{ site.analytics.yandex }}.reachGoal("1min_pageview");', 60000);
