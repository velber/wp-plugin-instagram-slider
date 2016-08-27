/**
 * Created by ganstarap on 8/14/16.
 */

$(document).ready(function () {
    $.post(myajax.url, {
            action: "auth_tooken"
        }
    )
        .done(function (result) {
            var data = JSON.parse(result),
                content = '';
            if (data.success) {
                var $slickSlider = $('#instagram-slider');
                data.images.forEach(function (e) {
                    if (e.photo_ulr.length > 0) {
                        content += "<div><a href='" + e.url + "'><img src='" + e.photo_ulr + "'></a></div>";
                    }
                });

                $slickSlider.html(content);
                // $slickSlider.html('');
                $slickSlider.slick({
                    autoplaySpeed: 2000,
                    autoplay: true,
                    variableWidth: true,
                    centerMode: true,
                    arrows: true
                });
            } else {
                console.log(data.error);
            }
        })
        .error(function (result) {
            console.log(result)
        });
});