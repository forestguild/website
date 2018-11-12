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

